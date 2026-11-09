<?php

namespace App\Services;

use App\Models\Game;
use App\Models\Round;
use App\Models\Tournament;
use Illuminate\Support\Facades\DB;

class SwissRoundRepair
{
    public const BREAK_REJECTED = 'Only an unset game in the current Swiss round can be broken';

    public const PAIR_REJECTED = 'Choose two unpaired players in the current Swiss round';

    public const UNPAIRED_REJECTED = 'This row cannot be changed';

    public function giveBye(Tournament $tournament, int $userId): bool
    {
        if (!$this->isLiveSwiss($tournament)) {
            return false;
        }

        $round = $this->latestRound($tournament);
        if (!$round || $this->playerHasGame($round, $userId)) {
            return false;
        }

        Game::createBye($tournament->id, $round->id, $userId);

        return true;
    }

    public function deleteLatestByes(Tournament $tournament, int $userId): void
    {
        $round = $this->latestRound($tournament);
        if (!$round) {
            return;
        }

        $round->games()
            ->where('player1_id', $userId)
            ->whereNull('player2_id')
            ->delete();
    }

    public function breakGame(Game $game, string $player1Outcome, string $player2Outcome): void
    {
        if (!$this->isOutcome($player1Outcome) || !$this->isOutcome($player2Outcome)) {
            throw new \DomainException(self::BREAK_REJECTED);
        }

        DB::transaction(function () use ($game, $player1Outcome, $player2Outcome) {
            $locked = Game::query()->whereKey($game->id)->lockForUpdate()->first();
            if (!$locked || !$this->canBreak($locked)) {
                throw new \DomainException(self::BREAK_REJECTED);
            }

            $playerIds = [$locked->player1_id, $locked->player2_id];
            $extra = Game::query()
                ->where('round_id', $locked->round_id)
                ->where('id', '!=', $locked->id)
                ->where(function ($query) use ($playerIds) {
                    $query->whereIn('player1_id', $playerIds)
                        ->orWhereIn('player2_id', $playerIds);
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->exists();

            if ($extra) {
                throw new \DomainException(self::BREAK_REJECTED);
            }

            $tournamentId = $locked->tournament_id;
            $roundId = $locked->round_id;
            $locked->delete();

            $this->createUnpaired($tournamentId, $roundId, $playerIds[0], $player1Outcome);
            $this->createUnpaired($tournamentId, $roundId, $playerIds[1], $player2Outcome);
        });
    }

    public function setUnpairedKind(Game $game, string $kind): void
    {
        if (!$this->isOutcome($kind)) {
            throw new \DomainException(self::UNPAIRED_REJECTED);
        }

        DB::transaction(function () use ($game, $kind) {
            $locked = Game::query()->whereKey($game->id)->lockForUpdate()->first();
            if (!$locked || !$this->canRetype($locked)) {
                throw new \DomainException(self::UNPAIRED_REJECTED);
            }

            if ($kind === 'absent') {
                $locked->result1 = null;
                $locked->result2 = null;
                $locked->status = Game::STATUS_ABSENT;
            } else {
                $locked->result1 = 'win';
                $locked->result2 = 'loss';
                $locked->status = Game::STATUS_COMPLETED;
            }
            $locked->ended_at = now();
            $locked->save();
        });
    }

    public function pairByes(Tournament $tournament, Round $round, int $playerAId, int $playerBId): void
    {
        DB::transaction(function () use ($tournament, $round, $playerAId, $playerBId) {
            $latest = $this->latestRound($tournament);
            if (
                $playerAId === $playerBId
                || !$this->isLiveSwiss($tournament)
                || !$latest
                || $latest->id !== $round->id
                || $round->tournament_id !== $tournament->id
                || !$this->isActivePlayer($tournament, $playerAId)
                || !$this->isActivePlayer($tournament, $playerBId)
            ) {
                throw new \DomainException(self::PAIR_REJECTED);
            }

            $byes = Game::query()
                ->where('round_id', $round->id)
                ->whereNull('player2_id')
                ->whereIn('player1_id', [$playerAId, $playerBId])
                ->where(function ($query) {
                    $query->where('status', Game::STATUS_ABSENT)
                        ->orWhere(function ($query) {
                            $query->where('status', Game::STATUS_COMPLETED)
                                ->where('result1', 'win');
                        });
                })
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($byes->count() !== 2) {
                throw new \DomainException(self::PAIR_REJECTED);
            }

            foreach ([$playerAId, $playerBId] as $playerId) {
                $count = Game::query()
                    ->where('round_id', $round->id)
                    ->where(function ($query) use ($playerId) {
                        $query->where('player1_id', $playerId)
                            ->orWhere('player2_id', $playerId);
                    })
                    ->lockForUpdate()
                    ->count();

                if ($count !== 1) {
                    throw new \DomainException(self::PAIR_REJECTED);
                }
            }

            foreach ($byes as $bye) {
                $bye->delete();
            }

            $playerAIsWhite = random_int(0, 1) === 1;
            Game::create([
                'tournament_id' => $tournament->id,
                'round_id' => $round->id,
                'player1_id' => $playerAIsWhite ? $playerAId : $playerBId,
                'player2_id' => $playerAIsWhite ? $playerBId : $playerAId,
                'result1' => null,
                'result2' => null,
                'status' => Game::STATUS_PENDING,
            ]);
        });
    }

    private function canBreak(Game $game): bool
    {
        $tournament = $game->tournament;
        if (!$tournament || !$this->isLiveSwiss($tournament)) {
            return false;
        }

        $latest = $this->latestRound($tournament);
        if (!$latest || $game->round_id !== $latest->id) {
            return false;
        }

        return $game->player2_id !== null
            && $game->result1 === null
            && $game->result2 === null;
    }

    private function canRetype(Game $game): bool
    {
        $tournament = $game->tournament;
        if (!$tournament || !$this->isLiveSwiss($tournament) || $game->player2_id !== null) {
            return false;
        }

        $latest = $this->latestRound($tournament);
        if (!$latest || $game->round_id !== $latest->id) {
            return false;
        }

        return $game->status === Game::STATUS_ABSENT
            || ($game->status === Game::STATUS_COMPLETED && $game->result1 === 'win');
    }

    private function isOutcome(string $kind): bool
    {
        return $kind === 'bye' || $kind === 'absent';
    }

    private function createUnpaired(int $tournamentId, int $roundId, int $playerId, string $kind): void
    {
        if ($kind === 'absent') {
            Game::createAbsent($tournamentId, $roundId, $playerId);
            return;
        }

        Game::createBye($tournamentId, $roundId, $playerId);
    }

    private function isLiveSwiss(Tournament $tournament): bool
    {
        return $tournament->format === 'swiss' && $tournament->status === 'ongoing';
    }

    private function latestRound(Tournament $tournament): ?Round
    {
        return $tournament->rounds()->latest('round_number')->first();
    }

    private function playerHasGame(Round $round, int $userId): bool
    {
        return $round->games()
            ->where(function ($query) use ($userId) {
                $query->where('player1_id', $userId)
                    ->orWhere('player2_id', $userId);
            })
            ->exists();
    }

    private function isActivePlayer(Tournament $tournament, int $userId): bool
    {
        return $tournament->players()
            ->where('users.id', $userId)
            ->wherePivot('banned', false)
            ->exists();
    }
}
