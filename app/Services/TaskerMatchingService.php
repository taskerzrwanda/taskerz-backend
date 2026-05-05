<?php

namespace App\Services;

use App\Models\Tasker;
use App\Models\TaskRequest;
use App\Models\SubTask;

class TaskerMatchingService
{
    public function findSuitableTaskers(TaskRequest $taskRequest, int $limit = 10)
    {
        $taskRequest->loadMissing('subTask.task');
        $subTask = $taskRequest->subTask;
        $task = $subTask->task;

        $location    = strtolower($taskRequest->location);
        $subTaskName = strtolower($subTask->name ?? '');
        $taskTitle   = strtolower($task->title ?? '');

        // Pre-filter at DB: keep only candidates whose city/district appears in the
        // request location, or whose profession appears in subTask.name / task.title.
        // Skill-only matchers (score 20) are intentionally excluded — they rarely
        // outrank location/profession matches (40+).
        $candidates = Tasker::approved()
            ->where(function ($q) use ($location, $subTaskName, $taskTitle) {
                $q->whereRaw('? LIKE CONCAT("%", LOWER(city), "%")', [$location])
                  ->orWhereRaw('? LIKE CONCAT("%", LOWER(district), "%")', [$location])
                  ->orWhereRaw('? LIKE CONCAT("%", LOWER(profession), "%")', [$subTaskName])
                  ->orWhereRaw('? LIKE CONCAT("%", LOWER(profession), "%")', [$taskTitle]);
            })
            ->limit(50)
            ->get();

        return $candidates
            ->map(function ($tasker) use ($taskRequest, $subTask, $task) {
                $tasker->match_score = $this->score($tasker, $taskRequest, $subTask, $task);
                return $tasker;
            })
            ->filter(fn ($tasker) => $tasker->match_score > 0)
            ->sortByDesc('match_score')
            ->take($limit)
            ->values();
    }

    protected function score(Tasker $tasker, TaskRequest $taskRequest, SubTask $subTask, $task): int
    {
        $score = 0;

        // 1. Location match (40)
        if ($this->locationMatches($tasker, $taskRequest->location)) {
            $score += 40;
        }

        // 2. Profession match (40)
        if ($this->professionMatches($tasker, $subTask, $task)) {
            $score += 40;
        }

        // 3. Skill match (20)
        if ($this->skillMatches($tasker, $subTask, $task)) {
            $score += 20;
        }

        return $score;
    }

    protected function locationMatches(Tasker $tasker, string $location): bool
    {
        $location = strtolower($location);

        return str_contains($location, strtolower($tasker->city)) ||
               str_contains($location, strtolower($tasker->district));
    }

    protected function professionMatches(Tasker $tasker, SubTask $subTask, $task): bool
    {
        $profession = strtolower($tasker->profession);

        return str_contains(strtolower($subTask->name), $profession) ||
               str_contains(strtolower($task->title), $profession);
    }

    protected function skillMatches(Tasker $tasker, SubTask $subTask, $task): bool
    {
        if (!$tasker->skills) {
            return false;
        }

        $skills = is_array($tasker->skills)
            ? $tasker->skills
            : json_decode($tasker->skills, true);

        $haystack = strtolower(
            $subTask->name . ' ' . $subTask->description . ' ' . $task->title
        );

        foreach ($skills as $skill) {
            if (str_contains($haystack, strtolower($skill))) {
                return true;
            }
        }

        return false;
    }

    public function getTaskerRecommendations(TaskRequest $taskRequest, int $limit = 10): array
    {
        return $this->findSuitableTaskers($taskRequest, $limit)
            ->map(fn ($tasker) => [
                'id' => $tasker->id,
                'name' => $tasker->name,
                'profession' => $tasker->profession,
                'city' => $tasker->city,
                'district' => $tasker->district,
                'skills' => $tasker->skills,
                'match_score' => $tasker->match_score,
                'phone' => $tasker->phone,
            ])
            ->toArray();
    }
}
