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

    public function __construct(Tournament $tournament)
    {
        $this->tournament = $tournament;
        $this->standings = $tournament->getStandings();
    }

    /**
     * Generate pairings for the next round using Swiss system
     */
    public function generatePairings(): array
    {
        $players = $this->standings->pluck('id')->toArray();
        $pairings = [];

        // Simple Swiss pairing: sort by score, then pair adjacent players
        for ($i = 0; $i < count($players) - 1; $i += 2) {
            $pairings[] = [
                'player1_id' => $players[$i],
                'player2_id' => $players[$i + 1],
            ];
        }

        // Handle odd number of players (bye)
        if (count($players) % 2 === 1) {
            $pairings[] = [
                'player1_id' => $players[count($players) - 1],
                'player2_id' => null, // bye
            ];
        }

        return $pairings;
    }

    /**
     * Create round and gamees in database
     */
    public function createRound(): Round
    {
        $roundNumber = $this->tournament->rounds()->max('round_number') + 1;
        
        $round = $this->tournament->rounds()->create([
            'round_number' => $roundNumber,
            'status' => 'pending',
        ]);

        $pairings = $this->generatePairings();

        foreach ($pairings as $pairing) {
            Game::create([
                'tournament_id' => $this->tournament->id,
                'round_id' => $round->id,
                'player1_id' => $pairing['player1_id'],
                'player2_id' => $pairing['player2_id'],
                'status' => 'pending',
            ]);
        }

        return $round;
    }

    /**
     * Validate pairings (used after manual adjustments)
     */
    public function validatePairings(Round $round): bool
    {
        $gamees = $round->gamees()->get();
        $pairedPlayers = new Collection();

        foreach ($gamees as $game) {
            if ($game->player1_id && $pairedPlayers->contains($game->player1_id)) {
                return false; // Player paired twice
            }
            if ($game->player2_id && $pairedPlayers->contains($game->player2_id)) {
                return false;
            }
            
            if ($game->player1_id) {
                $pairedPlayers->push($game->player1_id);
            }
            if ($game->player2_id) {
                $pairedPlayers->push($game->player2_id);
            }
        }

        return true;
    }
}
