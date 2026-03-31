<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Round;
use App\Models\Game;
use Illuminate\Support\Collection;

class SwissPairingEngine
{
    private Tournament $tournament;
    private Collection $standings;
    private array $previousOpponents;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
        $this->standings = $tournament->getStandings();
        $this->previousOpponents = $this->buildPreviousOpponents();
    }

    /**
     * Build a map of player_id => [opponent_ids] from all past games.
     */
    private function buildPreviousOpponents(): array
    {
        $opponents = [];
        $games = $this->tournament->games()->get();

        foreach ($games as $game) {
            if ($game->player1_id && $game->player2_id) {
                $opponents[$game->player1_id][] = $game->player2_id;
                $opponents[$game->player2_id][] = $game->player1_id;
            }
        }

        return $opponents;
    }

    /**
     * Check if two players have already played each other.
     */
    private function havePlayed(int $p1, int $p2): bool
    {
        return in_array($p2, $this->previousOpponents[$p1] ?? []);
    }

    /**
     * Generate pairings using the Dutch system.
     *
     * 1. Group players by score (score brackets).
     * 2. Within each bracket, sort by rating descending.
     * 3. Split into top half (S1) and bottom half (S2).
     * 4. Pair S1[0] vs S2[0], S1[1] vs S2[1], etc.
     * 5. If a pairing would be a rematch, try transposing within S2.
     * 6. Leftover (unpaired) players from a bracket float down to the next bracket.
     * 7. Odd player out at the end receives a bye (lowest-ranked player who hasn't had one).
     */
    public function generatePairings(): array
    {
        $players = $this->standings->values();

        // Group by score into brackets (highest score first)
        $brackets = $players->groupBy('points')->sortKeysDesc();

        $pairings = [];
        $floaters = collect(); // players carried down from previous bracket

        foreach ($brackets as $score => $bracketPlayers) {
            // Merge floaters into this bracket, re-sort by rating desc
            $pool = $floaters->merge($bracketPlayers)
                ->sortByDesc(function ($p) {
                    return $p->rating ?? $p->pivot_rating ?? 0;
                })
                ->values();

            $floaters = collect();

            $paired = $this->pairBracket($pool);

            foreach ($paired['pairings'] as $p) {
                $pairings[] = $p;
            }

            // Unpaired players float down to the next bracket
            $floaters = $paired['unpaired'];
        }

        // Remaining floaters: try to pair among themselves
        if ($floaters->count() >= 2) {
            $paired = $this->pairBracket($floaters);
            foreach ($paired['pairings'] as $p) {
                $pairings[] = $p;
            }
            $floaters = $paired['unpaired'];
        }

        // Handle bye: one remaining player
        if ($floaters->count() === 1) {
            $pairings[] = [
                'player1_id' => $floaters->first()->id,
                'player2_id' => null,
            ];
        }

        return $pairings;
    }

    /**
     * Pair a score bracket using the Dutch system's S1/S2 split.
     *
     * Players should already be sorted by rating descending.
     * Returns ['pairings' => [...], 'unpaired' => Collection].
     */
    private function pairBracket(Collection $pool): array
    {
        $pool = $pool->values();
        $count = $pool->count();

        if ($count < 2) {
            return ['pairings' => [], 'unpaired' => $pool];
        }

        $halfSize = intdiv($count, 2);

        $s1 = $pool->slice(0, $halfSize)->values();  // top half
        $s2 = $pool->slice($halfSize)->values();      // bottom half (may have 1 extra if odd)

        $pairings = [];
        $usedFromS2 = [];

        foreach ($s1 as $i => $p1) {
            $bestMatch = null;

            // Try to find best S2 opponent: prefer no rematch
            foreach ($s2 as $j => $p2) {
                if (in_array($j, $usedFromS2)) {
                    continue;
                }

                if (!$this->havePlayed($p1->id, $p2->id)) {
                    $bestMatch = $j;
                    break;
                }
            }

            // If no non-rematch found, accept a rematch (fallback)
            if ($bestMatch === null) {
                foreach ($s2 as $j => $p2) {
                    if (!in_array($j, $usedFromS2)) {
                        $bestMatch = $j;
                        break;
                    }
                }
            }

            if ($bestMatch !== null) {
                $pairings[] = [
                    'player1_id' => $p1->id,
                    'player2_id' => $s2[$bestMatch]->id,
                ];
                $usedFromS2[] = $bestMatch;
            }
        }

        // Collect unpaired players from S2
        $unpaired = collect();
        foreach ($s2 as $j => $p) {
            if (!in_array($j, $usedFromS2)) {
                $unpaired->push($p);
            }
        }

        return ['pairings' => $pairings, 'unpaired' => $unpaired];
    }

    /**
     * Determine which player should get the bye.
     * Prefer the lowest-ranked player who hasn't had a bye yet.
     */
    private function getByePlayer(Collection $players): ?object
    {
        $byePlayers = $this->tournament->games()
            ->whereNull('player2_id')
            ->pluck('player1_id')
            ->toArray();

        // Try lowest-ranked first (end of list)
        $reversed = $players->reverse();
        foreach ($reversed as $p) {
            if (!in_array($p->id, $byePlayers)) {
                return $p;
            }
        }

        // Everyone has had a bye, give it to the lowest-ranked
        return $reversed->first();
    }

    /**
     * Create round and games in database.
     */
    public function createRound(): Round
    {
        $roundNumber = $this->tournament->rounds()->max('round_number') + 1;
        
        $round = $this->tournament->rounds()->create([
            'round_number' => $roundNumber,
            'status' => 'pending',
        ]);

        // Handle bye assignment before pairing
        $players = $this->standings->values();
        $byePlayer = null;

        if ($players->count() % 2 === 1) {
            $byePlayer = $this->getByePlayer($players);

            // Create bye game
            Game::create([
                'tournament_id' => $this->tournament->id,
                'round_id' => $round->id,
                'player1_id' => $byePlayer->id,
                'player2_id' => null,
                'result1' => 'win',
                'result2' => 'loss',
                'status' => 'completed',
                'ended_at' => now(),
            ]);

            // Remove bye player from standings before pairing
            $this->standings = $this->standings->filter(fn($p) => $p->id !== $byePlayer->id)->values();
        }

        $pairings = $this->generatePairings();

        foreach ($pairings as $pairing) {
            if ($pairing['player2_id'] === null) {
                // Should not happen since we handled bye above, but just in case
                continue;
            }

            Game::create([
                'tournament_id' => $this->tournament->id,
                'round_id' => $round->id,
                'player1_id' => $pairing['player1_id'],
                'player2_id' => $pairing['player2_id'],
                'result1' => null,
                'result2' => null,
                'status' => 'pending',
            ]);
        }

        return $round;
    }
}
