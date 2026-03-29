<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Round;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoundController extends Controller
{
    public function show(Round $round): JsonResponse
    {
        $round->load('games');
        return response()->json($round);
    }

    public function startRound(Round $round): JsonResponse
    {
        if (!$round->canStart()) {
            return response()->json(['error' => 'Round cannot be started'], 422);
        }

        $round->update(['status' => 'in_progress', 'started_at' => now()]);

        return response()->json($round);
    }

    public function completeRound(Round $round): JsonResponse
    {
        if (!$round->allGamesCompleted()) {
            return response()->json(['error' => 'Not all games completed'], 422);
        }

        $round->update(['status' => 'completed', 'ended_at' => now()]);

        return response()->json($round);
    }

    public function getGames(Round $round): JsonResponse
    {
        $games = $round->games()->with(['player1', 'player2'])->get();
        return response()->json($games);
    }
}
