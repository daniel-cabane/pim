<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Round;
use App\Models\Game;
use Illuminate\Support\Collection;

class KnockoutPairingEngine
{
    private Tournament $tournament;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * Generate the standard bracket seed ordering for a given bracket size.
     *
     * For size 8: [0, 7, 3, 4, 1, 6, 2, 5]
     * This ensures seed 1 vs seed 2 can only happen in the final.
     *
     * @return array 0-indexed seed positions
     */
    private function getBracketOrder(int $size): array
    {
        if ($size === 1) {
            return [0];
        }

        $half = $this->getBracketOrder($size / 2);
        $result = [];

        foreach ($half as $seed) {
            $result[] = $seed;
            $result[] = $size - 1 - $seed;
        }

        return $result;
    }

    /**
     * Create the first round of a knockout bracket.
     *
     * Players are seeded by rating. If the number of players is not a power of 2,
     * top seeds receive byes (auto-wins). The bracket ordering ensures the highest
     * seeds are placed on opposite sides.
     */
    public function createFirstRound(): Round
    {
        $players = $this->tournament->getStandings()->values();
        $n = $players->count();

        // Find next power of 2
        $bracketSize = 1;
        while ($bracketSize < $n) {
            $bracketSize *= 2;
        }

        $order = $this->getBracketOrder($bracketSize);

        $round = $this->tournament->rounds()->create([
            'round_number' => 1,
            'status' => 'pending',
        ]);

        // Create games for each pair in bracket order
        for ($i = 0; $i < $bracketSize; $i += 2) {
            $seed1 = $order[$i];
            $seed2 = $order[$i + 1];

            $p1 = $seed1 < $n ? $players[$seed1] : null;
            $p2 = $seed2 < $n ? $players[$seed2] : null;

            if ($p1 === null && $p2 === null) {
                // Both are beyond player count — skip (shouldn't happen)
                continue;
            }

            if ($p2 === null) {
                // Seed 1 gets a bye
                Game::create([
                    'tournament_id' => $this->tournament->id,
                    'round_id' => $round->id,
                    'player1_id' => $p1->id,
                    'player2_id' => null,
                    'result1' => 'win',
                    'result2' => 'loss',
                    'status' => 'completed',
                    'ended_at' => now(),
                ]);
            } elseif ($p1 === null) {
                // Seed 2 gets a bye
                Game::create([
                    'tournament_id' => $this->tournament->id,
                    'round_id' => $round->id,
                    'player1_id' => $p2->id,
                    'player2_id' => null,
                    'result1' => 'win',
                    'result2' => 'loss',
                    'status' => 'completed',
                    'ended_at' => now(),
                ]);
            } else {
                Game::create([
                    'tournament_id' => $this->tournament->id,
                    'round_id' => $round->id,
                    'player1_id' => $p1->id,
                    'player2_id' => $p2->id,
                    'result1' => null,
                    'result2' => null,
                    'status' => 'pending',
                ]);
            }
        }

        return $round;
    }

    /**
     * Create the next knockout round from the winners of the previous round.
     *
     * Winners of consecutive game pairs are matched together, preserving
     * the bracket structure. Returns null if the tournament is over
     * (only one player remains).
     */
    public function createNextRound(): ?Round
    {
        $lastRound = $this->tournament->rounds()->latest('round_number')->first();

        if (!$lastRound) {
            return $this->createFirstRound();
        }

        // Get winners in bracket order (by game creation order)
        $games = $lastRound->games()->orderBy('id')->get();
        $winners = [];

        foreach ($games as $game) {
            if ($game->player2_id === null) {
                // Bye — player1 advances
                $winners[] = $game->player1_id;
            } elseif ($game->result1 === 'win') {
                $winners[] = $game->player1_id;
            } elseif ($game->result1 === 'loss') {
                $winners[] = $game->player2_id;
            } else {
                // Draw in knockout — shouldn't normally happen, default to player1
                $winners[] = $game->player1_id;
            }
        }

        if (count($winners) < 2) {
            // Tournament is over — only the champion remains
            return null;
        }

        $roundNumber = $lastRound->round_number + 1;
        $round = $this->tournament->rounds()->create([
            'round_number' => $roundNumber,
            'status' => 'pending',
        ]);

        // Pair winners consecutively to maintain bracket structure
        for ($i = 0; $i < count($winners) - 1; $i += 2) {
            Game::create([
                'tournament_id' => $this->tournament->id,
                'round_id' => $round->id,
                'player1_id' => $winners[$i],
                'player2_id' => $winners[$i + 1],
                'result1' => null,
                'result2' => null,
                'status' => 'pending',
            ]);
        }

        // Odd winner count (shouldn't happen in a proper bracket)
        if (count($winners) % 2 === 1) {
            Game::create([
                'tournament_id' => $this->tournament->id,
                'round_id' => $round->id,
                'player1_id' => $winners[count($winners) - 1],
                'player2_id' => null,
                'result1' => 'win',
                'result2' => 'loss',
                'status' => 'completed',
                'ended_at' => now(),
            ]);
        }

        return $round;
    }
}
