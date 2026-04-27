<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tournament;
use App\Models\User;
use App\Services\KnockoutPairingEngine;
use App\Services\RoundRobinPairingEngine;
use App\Services\SwissPairingEngine;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TournamentController extends Controller
{
    protected function loadTournamentRelations(Tournament $tournament): Tournament
    {
        $tournament->load(['rounds.games.player1', 'rounds.games.player2', 'players', 'creator', 'organisers']);

        $tournament->players->each(function (User $player) {
            $player->append('formal_name');
        });

        $tournament->creator?->append('formal_name');

        $tournament->organisers->each(function (User $organiser) {
            $organiser->append('formal_name');
        });

        $tournament->rounds->each(function ($round) {
            $round->games->each(function ($game) {
                $game->player1?->append('formal_name');
                $game->player2?->append('formal_name');
            });
        });

        return $tournament;
    }

    public function index(): JsonResponse
    {
        $tournaments = Tournament::with(['rounds', 'players'])
            ->latest()
            ->paginate(15);

        return response()->json($tournaments);
    }

    public function show(Tournament $tournament): JsonResponse
    {
        $this->loadTournamentRelations($tournament);
        $tournament->isOrganiser(auth()->user());
        return response()->json($tournament);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Tournament::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'format' => 'required|in:swiss,round_robin,knockout',
        ]);

        $tournament = Tournament::create(array_merge($validated, [
            'status' => 'draft',
            'rounds_count' => 0,
            'players_count' => 0,
            'created_by' => auth()->id(),
        ]));

        return response()->json($tournament, 201);
    }

    public function update(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'format' => 'required|in:swiss,round_robin,knockout',
        ]);

        $tournament->update($validated);

        return response()->json([
            'message' => [
                'text' => 'Tournament updated',
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament)
        ]);
    }

    public function destroy(Tournament $tournament): JsonResponse
    {
        $this->authorize('delete', $tournament);

        $tournament->delete();

        return response()->json(null, 204);
    }

    public function join(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('joinTournament', $tournament);

        if ($tournament->status !== 'preparation') {
            return response()->json([
                'message' => [
                    'text' => 'Players can only join during preparation phase',
                    'type' => 'error'
                ]
            ], 422);
        }

        $validated = $request->validate([
            'rating' => 'required|integer|min:200|max:2800',
        ]);

        $tournament->players()->attach(auth()->id(), [
            'score' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'rating' => $validated['rating'],
            'banned' => false,
        ]);

        $tournament->increment('players_count');

        return response()->json([
            'message' => [
                'text' => 'Joined tournament',
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament->fresh())
        ]);
    }

    public function leave(Tournament $tournament): JsonResponse
    {
        $tournament->players()->detach(auth()->id());
        $tournament->decrement('players_count');

        return response()->json([
            'message' => [
                'text' => 'Left tournament',
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament->fresh())
        ]);
    }

    public function start(Tournament $tournament): JsonResponse
    {
        $this->authorize('start', $tournament);

        $tournament->update(['status' => 'ongoing', 'started_at' => now()]);

        if ($tournament->format === 'round_robin') {
            $engine = new RoundRobinPairingEngine($tournament);
            $roundsCreated = $engine->createAllRounds();
            $tournament->increment('rounds_count', $roundsCreated);
        } elseif ($tournament->format === 'knockout') {
            $engine = new KnockoutPairingEngine($tournament);
            $engine->createFirstRound();
            $tournament->increment('rounds_count');
        } else {
            $engine = new SwissPairingEngine($tournament);
            $engine->createRound();
            $tournament->increment('rounds_count');
        }

        return response()->json([
            'tournament' => $this->loadTournamentRelations($tournament->fresh()),
            'message' => [
                'text' => 'Tournament started',
                'type' => 'success'
            ],
        ]);
    }

    public function standings(Tournament $tournament): JsonResponse
    {
        return response()->json($tournament->getStandings());
    }

    public function getRounds(Tournament $tournament): JsonResponse
    {
        $rounds = $tournament->rounds()->with('games')->get();
        return response()->json($rounds);
    }

    public function createNextRound(Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $lastRound = $tournament->rounds()->latest('round_number')->first();
        
        if ($lastRound && !$lastRound->allGamesCompleted()) {
            return response()->json([
                'message' => [
                    'text' => 'Current round not completed',
                    'type' => 'error'
                ]
            ], 422);
        }

        if ($tournament->format === 'round_robin') {
            return response()->json([
                'message' => [
                    'text' => 'All rounds are pre-generated for Round Robin tournaments',
                    'type' => 'error'
                ]
            ], 422);
        }

        $tournament->recalculateStandings();

        if ($tournament->format === 'knockout') {
            $engine = new KnockoutPairingEngine($tournament);
            $round = $engine->createNextRound();

            if ($round === null) {
                $tournament->update(['status' => 'completed', 'ended_at' => now()]);
                $this->loadTournamentRelations($tournament);

                return response()->json([
                    'tournament' => $tournament,
                    'message' => ['text' => 'Tournament completed — champion decided', 'type' => 'success'],
                ]);
            }
        } else {
            $engine = new SwissPairingEngine($tournament);
            $round = $engine->createRound();
        }

        $tournament->increment('rounds_count');
        $this->loadTournamentRelations($tournament);

        return response()->json([
            'tournament' => $tournament,
            'message' => ['text' => 'New round created', 'type' => 'success'],
        ], 201);
    }

    public function searchUsers(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $query = $validated['query'];
        
        // Search users by name or email
        $users = User::where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email', 'preferences', 'level', 'section')
            ->limit(10)
            ->get();

        $users->each(function (User $user) {
            $user->append('formal_name');
        });

        return response()->json($users);
    }

    public function addOrganiser(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if user is already an organiser
        if ($tournament->isOrganiser(User::find($validated['user_id']))) {
            return response()->json([
                'message' => [
                    'text' => 'User is already an organiser',
                    'type' => 'error'
                ],
                'tournament' => $tournament
            ]);
        }

        $tournament->organisers()->attach($validated['user_id']);

        return response()->json([
            'message' => [
                'text' => 'Organiser added',
                'type' => 'success'
            ]
        ], 201);
    }

    public function removeOrganiser(Tournament $tournament, User $user): JsonResponse
    {
        $this->authorize('update', $tournament);

        // Don't allow removing the creator
        if ($tournament->created_by === $user->id) {
            return response()->json([
                'message' => [
                    'text' => 'Cannot remove the tournament creator',
                    'type' => 'error'
                ]
            ], 422);
        }

        $tournament->organisers()->detach($user->id);

        return response()->json([
            'message' => [
                'text' => 'Organiser removed',
                'type' => 'success'
            ]
        ]);
    }

    public function getOrganisers(Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $organisers = $tournament->organisers()
            ->select('users.id', 'users.name', 'users.email', 'users.preferences', 'users.level', 'users.section')
            ->get();

        $organisers->each(function (User $organiser) {
            $organiser->append('formal_name');
        });

        return response()->json($organisers);
    }

    public function searchPlayers(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'query' => 'required|string|min:3',
        ]);

        $query = $validated['query'];
        
        // Get existing player IDs to exclude them
        $existingPlayerIds = $tournament->players()->pluck('users.id')->toArray();
        
        // Search users by name or email, excluding already added players
        $users = User::where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%");
            })
            ->whereNotIn('id', $existingPlayerIds)
            ->select('id', 'name', 'email', 'preferences', 'level', 'section')
            ->limit(10)
            ->get();

        $users->each(function (User $user) {
            $user->append('formal_name');
        });

        return response()->json($users);
    }

    public function addPlayer(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if user is already a player
        if ($tournament->players()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json([
                'message' => [
                    'text' => 'User is already a player',
                    'type' => 'error'
                ],
                'tournament' => $tournament
            ]);
        }

        // Add player with default stats
        $tournament->players()->attach($validated['user_id'], [
            'score' => 0,
            'wins' => 0,
            'draws' => 0,
            'losses' => 0,
            'banned' => false,
        ]);

        $tournament->increment('players_count');

        return response()->json([
            'message' => [
                'text' => 'Player added',
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament)
        ], 201);
    }

    public function removePlayer(Tournament $tournament, User $user): JsonResponse
    {
        $this->authorize('update', $tournament);

        // Check if player exists
        if (!$tournament->players()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => [
                    'text' => 'Player not found in tournament',
                    'type' => 'error'
                ]
            ], 404);
        }

        $tournament->players()->detach($user->id);
        $tournament->decrement('players_count');

        return response()->json([
            'message' => [
                'text' => 'Player removed',
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament)
        ]);
    }

    public function banPlayer(Request $request, Tournament $tournament, User $user): JsonResponse
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'banned' => 'required|boolean',
        ]);
        

        // Check if player exists
        if (!$tournament->players()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => [
                    'text' => 'Player not found in tournament',
                    'type' => 'error'
                ]
            ], 404);
        }

        // Update ban status
        $tournament->players()->updateExistingPivot($user->id, [
            'banned' => $validated['banned'],
        ]);
        if($validated['banned']){
            $tournament->decrement('players_count');
        } else {
            $tournament->increment('players_count');
        }

        $status = $validated['banned'] ? 'banned' : 'unbanned';

        return response()->json([
            'message' => [
                'text' => "Player {$status}",
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament)
        ]);
    }

    public function editPlayerRating(Request $request, Tournament $tournament, User $user)
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'rating' => 'required|integer',
        ]);

        // Check if player exists
        if (!$tournament->players()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => [
                    'text' => 'Player not found in tournament',
                    'type' => 'error'
                ]
            ], 404);
        }

        // Update ban status
        $tournament->players()->updateExistingPivot($user->id, [
            'rating' => $validated['rating'],
        ]);

        return response()->json([
            'message' => [
                'text' => "Player's rating updated",
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament)
        ]);
    }

    public function updateStatus(Request $request, Tournament $tournament): JsonResponse
    {
        $this->authorize('update', $tournament);

        $validated = $request->validate([
            'status' => 'required|in:draft,preparation,ongoing,completed',
        ]);

        $newStatus = $validated['status'];
        $currentStatus = $tournament->status;

        $allowedTransitions = [
            'draft' => ['preparation'],
            'preparation' => ['draft', 'ongoing'],
            'ongoing' => ['completed'],
        ];

        if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'message' => [
                    'text' => "Cannot change status from {$currentStatus} to {$newStatus}",
                    'type' => 'error'
                ]
            ], 422);
        }

        if ($newStatus === 'ongoing') {
            if ($tournament->players_count < 2) {
                return response()->json([
                    'message' => [
                        'text' => 'At least 2 players are required to start the tournament',
                        'type' => 'error'
                    ]
                ], 422);
            }

            $tournament->update(['status' => 'ongoing', 'started_at' => now()]);

            if ($tournament->format === 'round_robin') {
                $engine = new RoundRobinPairingEngine($tournament);
                $roundsCreated = $engine->createAllRounds();
                $tournament->increment('rounds_count', $roundsCreated);
            } elseif ($tournament->format === 'knockout') {
                $engine = new KnockoutPairingEngine($tournament);
                $engine->createFirstRound();
                $tournament->increment('rounds_count');
            } else {
                $engine = new SwissPairingEngine($tournament);
                $engine->createRound();
                $tournament->increment('rounds_count');
            }
        } elseif ($newStatus === 'completed') {
            $tournament->update(['status' => 'completed', 'ended_at' => now()]);
        } else {
            $tournament->update(['status' => $newStatus]);
        }

        return response()->json([
            'message' => [
                'text' => 'Tournament status updated',
                'type' => 'success'
            ],
            'tournament' => $this->loadTournamentRelations($tournament->fresh())
        ]);
    }
}
