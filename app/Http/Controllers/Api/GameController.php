<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    public function show(Game $game): JsonResponse
    {
        $game->load(['player1', 'player2', 'round', 'tournament']);
        return response()->json($game);
    }

    public function startGame(Game $game): JsonResponse
    {
        $this->authorize('startGame', $game);

        if ($game->status !== Game::STATUS_PENDING) {
            return response()->json(['error' => 'Game already started'], 422);
        }

        $game->update(['status' => Game::STATUS_IN_PROGRESS, 'started_at' => now()]);

        return response()->json($game);
    }

    public function recordResult(Request $request, Game $game): JsonResponse
    {
        $this->authorize('recordResult', $game);

        $validated = $request->validate([
            'result' => 'required|in:player1_win,player2_win,draw',
            'notes' => 'nullable|string',
        ]);

        $game->notes = $validated['notes'] ?? null;
        $game->recordResult($validated['result']);

        return response()->json($game);
    }

    public function getGameResults(Request $request): JsonResponse
    {
        $games = Game::where('status', Game::STATUS_COMPLETED)
            ->with(['player1', 'player2', 'tournament'])
            ->latest()
            ->paginate(20);

        return response()->json($games);
    }
}
