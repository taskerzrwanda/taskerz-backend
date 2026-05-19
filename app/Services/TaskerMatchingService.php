<?php

namespace App\Services;

use App\Models\SubTask;
use App\Models\TaskRequest;
use App\Models\User;

class TaskerMatchingService
{
    /**
     * Safety cap on the candidate pool pulled from the DB before in-PHP scoring.
     * Prevents pathological memory use if the approved-tasker base grows large.
     */
    protected const CANDIDATE_HARD_CAP = 200;

    /**
     * Number of concurrent in-flight (approved) assignments at which a tasker
     * starts being penalised in the score.
     */
    protected const WORKLOAD_PENALTY_THRESHOLD = 5;

    public function findSuitableTaskers(TaskRequest $taskRequest, int $limit = 10)
    {
        $taskRequest->loadMissing('subTask.task');
        $subTask = $taskRequest->subTask;
        $task    = $subTask?->task;

        $location    = strtolower($taskRequest->location ?? '');
        $subTaskName = strtolower($subTask->name ?? '');
        $taskTitle   = strtolower($task->title ?? '');

        $candidates = User::approvedTaskers()
            ->withCount(['assignedTasks as active_count'])
            ->where(function ($q) use ($location, $subTaskName, $taskTitle) {
                $q->where(function ($qq) use ($location) {
                    $qq->whereNotNull('city')->where('city', '<>', '')
                       ->whereRaw('? LIKE CONCAT("%", LOWER(city), "%")', [$location]);
                })
                ->orWhere(function ($qq) use ($location) {
                    $qq->whereNotNull('district')->where('district', '<>', '')
                       ->whereRaw('? LIKE CONCAT("%", LOWER(district), "%")', [$location]);
                })
                ->orWhere(function ($qq) use ($subTaskName) {
                    $qq->whereNotNull('profession')->where('profession', '<>', '')
                       ->whereRaw('? LIKE CONCAT("%", LOWER(profession), "%")', [$subTaskName]);
                })
                ->orWhere(function ($qq) use ($taskTitle) {
                    $qq->whereNotNull('profession')->where('profession', '<>', '')
                       ->whereRaw('? LIKE CONCAT("%", LOWER(profession), "%")', [$taskTitle]);
                });
            })
            ->limit(self::CANDIDATE_HARD_CAP)
            ->get();

        return $candidates
            ->map(function ($tasker) use ($taskRequest, $subTask, $task) {
                $tasker->match_score = $this->score($tasker, $taskRequest, $subTask, $task);
                return $tasker;
            })
            ->filter(fn ($tasker) => $tasker->match_score > 0)
            ->sortBy([
                ['match_score',     'desc'],
                ['active_count',    'asc'],
                ['completed_tasks', 'desc'],
                ['id',              'asc'],
            ])
            ->take($limit)
            ->values();
    }

    protected function score(User $tasker, TaskRequest $taskRequest, ?SubTask $subTask, $task): int
    {
        $score = 0;

        if ($this->locationMatches($tasker, $taskRequest->location ?? '')) {
            $score += 40;
        }

        if ($subTask && $this->professionMatches($tasker, $subTask, $task)) {
            $score += 40;
        }

        if ($subTask && $this->skillMatches($tasker, $subTask, $task)) {
            $score += 20;
        }

        // Workload penalty — overloaded taskers drop in the ranking.
        if ((int) ($tasker->active_count ?? 0) >= self::WORKLOAD_PENALTY_THRESHOLD) {
            $score -= 10;
        }

        // Track-record bonus for taskers with at least one completed task.
        if ((int) ($tasker->completed_tasks ?? 0) > 0) {
            $score += 5;
        }

        return max(0, $score);
    }

    protected function locationMatches(User $tasker, string $location): bool
    {
        $location = strtolower($location);
        if ($location === '') {
            return false;
        }

        $city     = strtolower($tasker->city ?? '');
        $district = strtolower($tasker->district ?? '');

        return ($city !== '' && str_contains($location, $city)) ||
               ($district !== '' && str_contains($location, $district));
    }

    protected function professionMatches(User $tasker, SubTask $subTask, $task): bool
    {
        $profession = strtolower($tasker->profession ?? '');
        if ($profession === '') {
            return false;
        }

        $subTaskName = strtolower($subTask->name ?? '');
        $taskTitle   = strtolower($task->title ?? '');

        return ($subTaskName !== '' && str_contains($subTaskName, $profession)) ||
               ($taskTitle   !== '' && str_contains($taskTitle,   $profession));
    }

    protected function skillMatches(User $tasker, SubTask $subTask, $task): bool
    {
        if (!$tasker->skills) {
            return false;
        }

        $skills = is_array($tasker->skills)
            ? $tasker->skills
            : json_decode($tasker->skills, true);

        if (!is_array($skills) || $skills === []) {
            return false;
        }

        $haystack = strtolower(trim(
            ($subTask->name ?? '') . ' ' .
            ($subTask->description ?? '') . ' ' .
            ($task->title ?? '')
        ));

        if ($haystack === '') {
            return false;
        }

        foreach ($skills as $skill) {
            $skill = strtolower(trim((string) $skill));
            if ($skill !== '' && str_contains($haystack, $skill)) {
                return true;
            }
        }

        return false;
    }

    public function getTaskerRecommendations(TaskRequest $taskRequest, int $limit = 10): array
    {
        return $this->findSuitableTaskers($taskRequest, $limit)
            ->map(fn ($tasker) => [
                'id'              => $tasker->id,
                'name'            => $tasker->name,
                'profession'      => $tasker->profession,
                'city'            => $tasker->city,
                'district'        => $tasker->district,
                'skills'          => $tasker->skills,
                'match_score'     => $tasker->match_score,
                'active_count'    => (int) ($tasker->active_count ?? 0),
                'completed_tasks' => (int) ($tasker->completed_tasks ?? 0),
                'phone'           => $tasker->phone,
            ])
            ->toArray();
    }
}
