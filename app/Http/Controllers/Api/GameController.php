<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\SwissRoundRepair;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    protected function loadGameRelations(Game $game): Game
    {
        $game->load(['player1', 'player2', 'round', 'tournament']);

        $game->player1?->append('formal_name');
        $game->player2?->append('formal_name');

        return $game;
    }

    public function show(Game $game): JsonResponse
    {
        $this->loadGameRelations($game);
        return response()->json($game);
    }

    public function breakGame(Request $request, Game $game): JsonResponse
    {
        $this->authorize('startGame', $game);

        $validated = $request->validate([
            'player1_outcome' => 'required|in:bye,absent',
            'player2_outcome' => 'required|in:bye,absent',
        ]);

        try {
            app(SwissRoundRepair::class)->breakGame(
                $game,
                $validated['player1_outcome'],
                $validated['player2_outcome']
            );
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $tournament = $game->tournament;

        return response()->json([
            'message' => [
                'text' => 'Game broken',
                'type' => 'success',
            ],
            'tournament' => app(TournamentController::class)->loadTournamentRelations($tournament),
        ]);
    }

    public function setUnpairedKind(Request $request, Game $game): JsonResponse
    {
        $this->authorize('startGame', $game);

        $validated = $request->validate([
            'kind' => 'required|in:bye,absent',
        ]);

        try {
            app(SwissRoundRepair::class)->setUnpairedKind($game, $validated['kind']);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => [
                'text' => 'Row updated',
                'type' => 'success',
            ],
            'game' => $this->loadGameRelations($game->fresh()),
        ]);
    }

    public function startGame(Game $game): JsonResponse
    {
        $this->authorize('startGame', $game);

        if ($response = $this->rejectBye($game)) {
            return $response;
        }

        if ($game->status !== Game::STATUS_PENDING) {
            return response()->json(['error' => 'Game already started'], 422);
        }

        $game->update(['status' => Game::STATUS_IN_PROGRESS, 'started_at' => now()]);

        return response()->json($game);
    }

    public function recordResult(Request $request, Game $game): JsonResponse
    {
        $this->authorize('recordResult', $game);

        if ($response = $this->rejectBye($game)) {
            return $response;
        }

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
            'game' => $this->loadGameRelations($game->fresh()),
        ]);
    }

    public function setResult(Request $request, Game $game): JsonResponse
    {
        $this->authorize('startGame', $game); // reuse organiser check

        if ($response = $this->rejectBye($game)) {
            return $response;
        }

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
            'game' => $this->loadGameRelations($game->fresh()),
        ]);
    }

    public function resetGame(Game $game): JsonResponse
    {
        $this->authorize('startGame', $game);

        if ($response = $this->rejectBye($game)) {
            return $response;
        }

        if ($game->result1 === null && $game->result2 === null) {
            return response()->json(['message' => 'This game is already pending'], 422);
        }

        $game->resetToPending();

        return response()->json([
            'message' => [
                'text' => 'Game reset',
                'type' => 'success',
            ],
            'game' => $this->loadGameRelations($game->fresh()),
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

    private function rejectBye(Game $game): ?JsonResponse
    {
        if ($game->player2_id !== null) {
            return null;
        }

        return response()->json(['message' => 'A bye has no result to set'], 422);
    }
}
