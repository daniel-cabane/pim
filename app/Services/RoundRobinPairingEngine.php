<?php

namespace App\Services;

use App\Models\Tournament;
use App\Models\Round;
use App\Models\Game;

class RoundRobinPairingEngine
{
    private Tournament $tournament;

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * Create all rounds at once using the circle method (Berger table).
     *
     * Fix the first player, rotate all others clockwise each round.
     * If odd number of players, a ghost player is added and the player
     * paired against the ghost receives a bye.
     *
     * @return int Number of rounds created
     */
    public function createAllRounds(): int
    {
        $players = $this->tournament->getStandings()->values();
        $playerIds = $players->pluck('id')->toArray();
        $n = count($playerIds);

        if ($n < 2) {
            throw new \InvalidArgumentException('At least 2 players are required');
        }

        // If odd, add a null (ghost) player so everyone gets a bye once
        if ($n % 2 !== 0) {
            $playerIds[] = null;
            $n++;
        }

        $fixed = $playerIds[0];
        $rotating = array_slice($playerIds, 1);
        $numRounds = $n - 1;
        $halfSize = $n / 2;

        for ($r = 0; $r < $numRounds; $r++) {
            $round = $this->tournament->rounds()->create([
                'round_number' => $r + 1,
                'status' => 'pending',
            ]);

            // Top row: fixed player + first half of rotating
            $topRow = array_merge([$fixed], array_slice($rotating, 0, $halfSize - 1));
            // Bottom row: second half of rotating, reversed
            $bottomRow = array_reverse(array_slice($rotating, $halfSize - 1));

            for ($i = 0; $i < $halfSize; $i++) {
                $p1 = $topRow[$i];
                $p2 = $bottomRow[$i];

                if ($p1 === null || $p2 === null) {
                    // Bye game
                    $realPlayer = $p1 ?? $p2;
                    Game::create([
                        'tournament_id' => $this->tournament->id,
                        'round_id' => $round->id,
                        'player1_id' => $realPlayer,
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
                        'player1_id' => $p1,
                        'player2_id' => $p2,
                        'result1' => null,
                        'result2' => null,
                        'status' => 'pending',
                    ]);
                }
            }

            // Rotate: move last element to front
            $last = array_pop($rotating);
            array_unshift($rotating, $last);
        }

        return $numRounds;
    }
}
