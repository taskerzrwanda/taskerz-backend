<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
      Schema::create('taskers', function (Blueprint $table) {
    $table->id();

    $table->string('name');
    $table->string('nationality');
    $table->string('gender');
    $table->string('education');

    $table->string('email')->unique();
    $table->string('phone');

    $table->string('profession');
    $table->text('work_experience')->nullable();

    // Location
    $table->string('city')->nullable();
    $table->string('district')->nullable();
    $table->decimal('latitude', 10, 8)->nullable();
    $table->decimal('longitude', 11, 8)->nullable();

    // Meta
    $table->json('skills')->nullable();
    $table->integer('completed_tasks')->default(0);
    $table->decimal('rating', 3, 2)->nullable();

    // Optional but recommended
    $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');

    $table->timestamps();

    // Indexes (AFTER columns exist)
    $table->index(['city', 'status']);
    $table->index('status');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('taskers');
    }
};
