<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Production's `jobs` / `failed_jobs` tables were created with a NON
     * auto-increment `id` column (INSERTs failed with SQLSTATE[HY000] 1364
     * "Field 'id' doesn't have a default value"). That made every queued job —
     * i.e. every transactional email — silently fail to enqueue, so nothing was
     * ever delivered. The original create_jobs_table migration is already
     * marked as run, so it cannot self-heal; recreate the tables here with the
     * canonical auto-increment schema. They only hold transient queue state, so
     * dropping them loses nothing meaningful.
     */
    public function up(): void
    {
        Schema::dropIfExists('jobs');
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue', 191)->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::dropIfExists('failed_jobs');
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid', 191)->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    public function down(): void
    {
        // No-op: this migration repairs a broken schema; there is nothing
        // meaningful to reverse.
    }
};
