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
            'result' => 'required|in:win,loss,draw',
        ]);

        $userId = auth()->id();

        if ($userId !== $game->player1_id && $userId !== $game->player2_id) {
            return response()->json([
                'message' => ['text' => 'You are not a player in this game', 'type' => 'error']
            ], 403);
        }

        if ($game->status === Game::STATUS_COMPLETED) {
            return response()->json([
                'message' => ['text' => 'Game result is already final', 'type' => 'error']
            ], 422);
        }

        if ($validated['result'] === 'draw' && $game->tournament->format === 'knockout') {
            return response()->json([
                'message' => ['text' => 'Draws are not allowed in knockout tournaments', 'type' => 'error']
            ], 422);
        }

        $game->reportResult($userId, $validated['result']);

        return response()->json([
            'message' => ['text' => 'Result reported', 'type' => 'success'],
            'game' => $game->fresh()->load(['player1', 'player2']),
        ]);
    }

    public function setResult(Request $request, Game $game): JsonResponse
    {
        $this->authorize('startGame', $game); // reuse organiser check

        $validated = $request->validate([
            'result1' => 'required|in:win,loss,draw',
            'result2' => 'required|in:win,loss,draw',
        ]);

        if (($validated['result1'] === 'draw' || $validated['result2'] === 'draw') && $game->tournament->format === 'knockout') {
            return response()->json([
                'message' => ['text' => 'Draws are not allowed in knockout tournaments', 'type' => 'error']
            ], 422);
        }

        $game->setResultByOrganiser($validated['result1'], $validated['result2']);

        return response()->json([
            'message' => ['text' => 'Result set', 'type' => 'success'],
            'game' => $game->fresh()->load(['player1', 'player2']),
        ]);
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
