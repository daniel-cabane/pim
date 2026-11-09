<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class SwissByeAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_a_player_to_a_live_swiss_round_creates_a_bye(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner);
        $round = $this->round($tournament);
        $player = $this->user();

        $response = $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/players", ['user_id' => $player->id]);

        $response->assertCreated();
        $response->assertJsonPath('message.text', 'Player added to the current round as a bye');

        $tournament->refresh();
        $this->assertSame(1, $tournament->players_count);
        $this->assertEquals(0.0, (float) $tournament->players()->where('users.id', $player->id)->first()->pivot->score);

        $bye = Game::where('round_id', $round->id)->where('player1_id', $player->id)->first();
        $this->assertNotNull($bye);
        $this->assertNull($bye->player2_id);
        $this->assertSame('win', $bye->result1);
        $this->assertSame('loss', $bye->result2);
        $this->assertSame('completed', $bye->status);
    }

    public function test_adding_a_player_outside_a_live_swiss_round_does_not_create_a_game(): void
    {
        $owner = $this->user();
        $cases = [
            ['status' => 'preparation', 'format' => 'swiss'],
            ['status' => 'completed', 'format' => 'swiss'],
            ['status' => 'ongoing', 'format' => 'knockout'],
        ];

        foreach ($cases as $case) {
            $tournament = $this->tournament($owner, $case);
            $round = $this->round($tournament);
            $player = $this->user();

            $this->actingAs($owner, 'sanctum')
                ->postJson("/api/tournaments/{$tournament->slug}/players", ['user_id' => $player->id])
                ->assertCreated()
                ->assertJsonPath('message.text', 'Player added');

            $this->assertTrue($tournament->players()->where('users.id', $player->id)->exists());
            $this->assertSame(0, Game::where('round_id', $round->id)->count());
        }
    }

    public function test_adding_an_enrolled_player_is_rejected(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner, ['players_count' => 1]);
        $round = $this->round($tournament);
        $player = $this->user();
        $this->enroll($tournament, $player);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/players", ['user_id' => $player->id])
            ->assertOk()
            ->assertJsonPath('message.type', 'error')
            ->assertJsonPath('message.text', 'User is already a player');

        $this->assertSame(1, $tournament->players()->where('users.id', $player->id)->count());
        $this->assertSame(0, Game::where('round_id', $round->id)->count());
    }

    public function test_pairing_two_byes_creates_one_pending_game(): void
    {
        [$owner, $tournament, $round, $white, $black] = $this->twoByes();

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/rounds/{$round->id}/pair-byes", [
                'player_a_id' => $white->id,
                'player_b_id' => $black->id,
            ])
            ->assertOk()
            ->assertJsonPath('message.text', 'Game created');

        $this->assertSame(0, Game::where('round_id', $round->id)->whereNull('player2_id')->count());
        $game = Game::where('round_id', $round->id)->first();
        $this->assertNotNull($game);
        $this->assertSame('pending', $game->status);
        $this->assertNull($game->result1);
        $this->assertNull($game->result2);
        $this->assertEqualsCanonicalizing(
            [$white->id, $black->id],
            [$game->player1_id, $game->player2_id]
        );
    }

    public function test_pairing_rejects_invalid_byes_and_non_organisers(): void
    {
        [$owner, $tournament, $round, $white, $black] = $this->twoByes();
        $older = $this->round($tournament, 1);
        $this->round($tournament, 2);
        Game::createBye($tournament->id, $older->id, $white->id);
        Game::createBye($tournament->id, $older->id, $black->id);

        $playing = $this->user();
        $opponent = $this->user();
        $this->enroll($tournament, $playing);
        $this->enroll($tournament, $opponent);
        Game::create([
            'tournament_id' => $tournament->id,
            'round_id' => $round->id,
            'player1_id' => $playing->id,
            'player2_id' => $opponent->id,
            'status' => 'pending',
        ]);

        $before = Game::count();

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/rounds/{$round->id}/pair-byes", [
                'player_a_id' => $playing->id,
                'player_b_id' => $opponent->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Choose two unpaired players in the current Swiss round');

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/rounds/{$round->id}/pair-byes", [
                'player_a_id' => $white->id,
                'player_b_id' => $white->id,
            ])
            ->assertStatus(422);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/rounds/{$older->id}/pair-byes", [
                'player_a_id' => $white->id,
                'player_b_id' => $black->id,
            ])
            ->assertStatus(422);

        $this->actingAs($white, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/rounds/{$round->id}/pair-byes", [
                'player_a_id' => $white->id,
                'player_b_id' => $black->id,
            ])
            ->assertForbidden();

        $this->assertSame($before, Game::count());
    }

    public function test_breaking_an_unset_game_gives_both_players_a_bye(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner, ['players_count' => 2]);
        $round = $this->round($tournament);
        $white = $this->user();
        $black = $this->user();
        $this->enroll($tournament, $white);
        $this->enroll($tournament, $black);
        $game = Game::create([
            'tournament_id' => $tournament->id,
            'round_id' => $round->id,
            'player1_id' => $white->id,
            'player2_id' => $black->id,
            'status' => 'pending',
        ]);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$game->id}/break", [
                'player1_outcome' => 'bye',
                'player2_outcome' => 'bye',
            ])
            ->assertOk()
            ->assertJsonPath('message.text', 'Game broken');

        $this->assertNull(Game::find($game->id));
        $this->assertSame(2, Game::where('round_id', $round->id)->whereNull('player2_id')->count());

        $tournament->recalculateStandings();

        foreach ([$white, $black] as $player) {
            $pivot = $tournament->players()->where('users.id', $player->id)->first()->pivot;
            $this->assertEquals(1.0, (float) $pivot->score);
            $this->assertSame(1, (int) $pivot->wins);
        }
    }

    public function test_breaking_rejects_a_game_that_is_not_an_unset_game_in_the_current_round(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner);
        $older = $this->round($tournament, 1);
        $current = $this->round($tournament, 2);
        $players = User::factory()->count(9)->create(['password' => 'password']);
        foreach ($players as $player) {
            $this->enroll($tournament, $player);
        }

        $games = [
            'older' => $this->game($tournament, $older, $players[0], $players[1]),
            'partial' => $this->game($tournament, $current, $players[2], $players[3], ['result1' => 'win', 'status' => 'pending']),
            'completed' => $this->game($tournament, $current, $players[4], $players[5], [
                'result1' => 'win',
                'result2' => 'loss',
                'status' => 'completed',
            ]),
            'cancelled' => $this->game($tournament, $current, $players[6], $players[7], [
                'result1' => 'loss',
                'result2' => 'loss',
                'status' => 'cancelled',
            ]),
            'bye' => Game::createBye($tournament->id, $current->id, $players[8]->id),
        ];

        foreach ($games as $game) {
            $this->actingAs($owner, 'sanctum')
                ->postJson("/api/games/{$game->id}/break", [
                    'player1_outcome' => 'bye',
                    'player2_outcome' => 'absent',
                ])
                ->assertStatus(422)
                ->assertJsonPath('message', 'Only an unset game in the current Swiss round can be broken');
            $this->assertNotNull(Game::find($game->id));
        }
    }

    public function test_a_bye_cannot_be_given_a_result(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner);
        $round = $this->round($tournament);
        $player = $this->user();
        $this->enroll($tournament, $player);
        $bye = Game::createBye($tournament->id, $round->id, $player->id);

        $this->actingAs($player, 'sanctum')
            ->postJson("/api/games/{$bye->id}/result", ['result' => 'loss'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'A bye has no result to set');

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$bye->id}/set-result", ['result1' => 'loss', 'result2' => 'loss'])
            ->assertStatus(422)
            ->assertJsonPath('message', 'A bye has no result to set');

        $bye->refresh();
        $this->assertSame('win', $bye->result1);
        $this->assertSame('loss', $bye->result2);
        $this->assertSame('completed', $bye->status);
        $this->assertNull($bye->player2_id);
    }

    public function test_breaking_can_mark_each_player_bye_or_absent(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner);
        $round = $this->round($tournament);
        $white = $this->user();
        $black = $this->user();
        $this->enroll($tournament, $white);
        $this->enroll($tournament, $black);
        $game = $this->game($tournament, $round, $white, $black);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$game->id}/break", [
                'player1_outcome' => 'bye',
                'player2_outcome' => 'absent',
            ])
            ->assertOk();

        $bye = Game::where('player1_id', $white->id)->whereNull('player2_id')->first();
        $absent = Game::where('player1_id', $black->id)->whereNull('player2_id')->first();
        $this->assertSame('completed', $bye->status);
        $this->assertSame('win', $bye->result1);
        $this->assertSame('absent', $absent->status);
        $this->assertNull($absent->result1);

        $tournament->recalculateStandings();
        $this->assertEquals(1.0, (float) $tournament->players()->where('users.id', $white->id)->first()->pivot->score);
        $this->assertEquals(0.0, (float) $tournament->players()->where('users.id', $black->id)->first()->pivot->score);
        $this->assertSame(0, (int) $tournament->players()->where('users.id', $black->id)->first()->pivot->losses);
    }

    public function test_an_absent_player_can_be_paired_with_a_bye(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner);
        $round = $this->round($tournament);
        $byePlayer = $this->user();
        $absentPlayer = $this->user();
        $this->enroll($tournament, $byePlayer);
        $this->enroll($tournament, $absentPlayer);
        Game::createBye($tournament->id, $round->id, $byePlayer->id);
        Game::createAbsent($tournament->id, $round->id, $absentPlayer->id);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/rounds/{$round->id}/pair-byes", [
                'player_a_id' => $byePlayer->id,
                'player_b_id' => $absentPlayer->id,
            ])
            ->assertOk();

        $this->assertSame(0, Game::where('round_id', $round->id)->whereNull('player2_id')->count());
        $paired = Game::where('round_id', $round->id)->first();
        $this->assertSame('pending', $paired->status);
        $this->assertEqualsCanonicalizing([$byePlayer->id, $absentPlayer->id], [$paired->player1_id, $paired->player2_id]);
    }

    public function test_an_organiser_can_switch_a_bye_and_an_absence(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner);
        $round = $this->round($tournament);
        $player = $this->user();
        $this->enroll($tournament, $player);
        $bye = Game::createBye($tournament->id, $round->id, $player->id);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$bye->id}/unpaired", ['kind' => 'absent'])
            ->assertOk()
            ->assertJsonPath('game.status', 'absent');

        $bye->refresh();
        $this->assertNull($bye->result1);
        $tournament->recalculateStandings();
        $this->assertEquals(0.0, (float) $tournament->players()->where('users.id', $player->id)->first()->pivot->score);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$bye->id}/unpaired", ['kind' => 'bye'])
            ->assertOk()
            ->assertJsonPath('game.status', 'completed')
            ->assertJsonPath('game.result1', 'win');
    }

    public function test_an_absence_does_not_count_as_a_pairing_bye(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner, ['players_count' => 3]);
        $round = $this->round($tournament);
        $hadBye = $this->user();
        $wasAbsent = $this->user();
        $other = $this->user();
        $tournament->players()->attach($hadBye->id, ['score' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0, 'banned' => false, 'rating' => 2000]);
        $tournament->players()->attach($wasAbsent->id, ['score' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0, 'banned' => false, 'rating' => 1500]);
        $tournament->players()->attach($other->id, ['score' => 0, 'wins' => 0, 'draws' => 0, 'losses' => 0, 'banned' => false, 'rating' => 1200]);
        Game::createBye($tournament->id, $round->id, $hadBye->id);
        Game::createAbsent($tournament->id, $round->id, $wasAbsent->id);
        $this->game($tournament, $round, $other, $hadBye, [
            'result1' => 'loss',
            'result2' => 'win',
            'status' => 'completed',
        ]);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/next-round")
            ->assertCreated();

        $nextRound = $tournament->rounds()->orderByDesc('round_number')->first();
        $bye = Game::where('round_id', $nextRound->id)->whereNull('player2_id')->first();
        $this->assertNotNull($bye);
        $this->assertSame($wasAbsent->id, $bye->player1_id);
    }

    public function test_an_organiser_can_reset_a_recorded_result_to_pending(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner);
        $round = $this->round($tournament);
        $white = $this->user();
        $black = $this->user();
        $this->enroll($tournament, $white);
        $this->enroll($tournament, $black);
        $game = $this->game($tournament, $round, $white, $black, [
            'result1' => 'win',
            'result2' => 'loss',
            'status' => 'completed',
            'ended_at' => now(),
        ]);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$game->id}/reset")
            ->assertOk()
            ->assertJsonPath('message.text', 'Game reset')
            ->assertJsonPath('game.status', 'pending');

        $game->refresh();
        $this->assertNull($game->result1);
        $this->assertNull($game->result2);
        $this->assertSame('pending', $game->status);
        $this->assertNull($game->ended_at);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$game->id}/reset")
            ->assertStatus(422)
            ->assertJsonPath('message', 'This game is already pending');

        $bye = Game::createBye($tournament->id, $round->id, $white->id);
        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/games/{$bye->id}/reset")
            ->assertStatus(422)
            ->assertJsonPath('message', 'A bye has no result to set');

        $this->actingAs($white, 'sanctum')
            ->postJson("/api/games/{$game->id}/reset")
            ->assertForbidden();
    }

    public function test_removing_a_bye_player_deletes_the_bye_and_does_not_reattach_them(): void
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner, ['players_count' => 1]);
        $round = $this->round($tournament);
        $player = $this->user();
        $this->enroll($tournament, $player);
        $bye = Game::createBye($tournament->id, $round->id, $player->id);

        $this->actingAs($owner, 'sanctum')
            ->deleteJson("/api/tournaments/{$tournament->slug}/players/{$player->id}")
            ->assertOk();

        $this->assertNull(Game::find($bye->id));
        $this->assertFalse($tournament->players()->where('users.id', $player->id)->exists());

        $tournament->recalculateStandings();

        $this->assertFalse($tournament->players()->where('users.id', $player->id)->exists());
    }

    public function test_the_next_round_waits_for_a_paired_game_and_scores_a_remaining_bye(): void
    {
        [$owner, $tournament, $round, $white, $black] = $this->twoByes();

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/rounds/{$round->id}/pair-byes", [
                'player_a_id' => $white->id,
                'player_b_id' => $black->id,
            ])
            ->assertOk();

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/next-round")
            ->assertStatus(422);

        $this->assertSame(1, $tournament->rounds()->count());

        Game::where('round_id', $round->id)->delete();
        $bye = Game::createBye($tournament->id, $round->id, $white->id);
        Game::createBye($tournament->id, $round->id, $black->id);

        $this->actingAs($owner, 'sanctum')
            ->postJson("/api/tournaments/{$tournament->slug}/next-round")
            ->assertCreated();

        $this->assertSame(2, $tournament->rounds()->count());
        $pivot = $tournament->players()->where('users.id', $white->id)->first()->pivot;
        $this->assertEquals(1.0, (float) $pivot->score);
        $this->assertNotNull($bye);
    }

    private function user(): User
    {
        return User::factory()->create(['password' => 'password']);
    }

    private function twoByes(): array
    {
        $owner = $this->user();
        $tournament = $this->tournament($owner, ['players_count' => 2]);
        $round = $this->round($tournament, 3);
        $white = $this->user();
        $black = $this->user();
        $this->enroll($tournament, $white);
        $this->enroll($tournament, $black);
        Game::createBye($tournament->id, $round->id, $white->id);
        Game::createBye($tournament->id, $round->id, $black->id);

        return [$owner, $tournament, $round, $white, $black];
    }

    private function tournament(User $owner, array $overrides = []): Tournament
    {
        return Tournament::create(array_merge([
            'name' => 'Swiss cup',
            'slug' => 'swiss-'.Str::lower(Str::random(8)),
            'format' => 'swiss',
            'status' => 'ongoing',
            'rounds_count' => 1,
            'players_count' => 0,
            'created_by' => $owner->id,
        ], $overrides));
    }

    private function enroll(Tournament $tournament, User $player): void
    {
        $tournament->players()->attach($player->id, [
            'score' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'banned' => false,
        ]);
    }

    private function round(Tournament $tournament, int $number = 1): Round
    {
        return $tournament->rounds()->create([
            'round_number' => $number,
            'status' => 'pending',
        ]);
    }

    private function game(Tournament $tournament, Round $round, User $white, User $black, array $overrides = []): Game
    {
        return Game::create(array_merge([
            'tournament_id' => $tournament->id,
            'round_id' => $round->id,
            'player1_id' => $white->id,
            'player2_id' => $black->id,
            'status' => 'pending',
        ], $overrides));
    }
}
