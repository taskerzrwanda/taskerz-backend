<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PopularTask;
use App\Models\Task;
use App\Models\SubTask;

class MigratePopularTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This migrates data from the old popular_tasks structure
     * to the new tasks and sub_tasks structure
     */
    public function run(): void
    {
        $this->command->info('Starting migration from popular_tasks to new structure...');

        // Get all popular tasks
        $popularTasks = PopularTask::all();

        foreach ($popularTasks as $popularTask) {
            $this->command->info("Migrating: {$popularTask->title}");

            // Create or find task by title (to avoid duplicates)
            $task = Task::firstOrCreate(
                ['title' => $popularTask->title],
                [
                    'image' => $popularTask->image,
                    'status' => $popularTask->status ? 'active' : 'inactive',
                    'tags' => $popularTask->tags
                ]
            );

            // Create sub-task for this popular task
            $subTask = SubTask::create([
                'task_id' => $task->id,
                'name' => $popularTask->title,
                'price' => $popularTask->price,
                'duration' => $popularTask->duration,
                'description' => $popularTask->description,
                'status' => $popularTask->status ? 'active' : 'inactive'
            ]);

            $this->command->info("Created Task ID: {$task->id}, SubTask ID: {$subTask->id}");
        }

        $this->command->info('Migration completed!');
        $this->command->info('Total Tasks created: ' . Task::count());
        $this->command->info('Total SubTasks created: ' . SubTask::count());

        // Note: Old task_requests would need manual review for migration
        $this->command->warn('NOTE: Old task_requests table needs manual review for data migration');
    }

    /**
     * Reverse the migration (optional - for testing)
     */
    public function reverse(): void
    {
        $this->command->warn('Reversing migration - deleting all tasks and sub-tasks...');

        SubTask::truncate();
        Task::truncate();

        $this->command->info('Reversal completed');
    }
}
