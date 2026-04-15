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

            $table->string('name', 191);
            $table->string('nationality', 191);
            $table->string('gender', 50);
            $table->string('education', 191);

            $table->string('email', 191)->unique(); // ✅ FIX
            $table->string('phone', 50);

            $table->string('profession', 191);
            $table->text('work_experience')->nullable();

            // Location
            $table->string('city', 191)->nullable();
            $table->string('district', 191)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Meta
            $table->text('skills')->nullable(); // ✅ FIX (json → text)
            $table->integer('completed_tasks')->default(0);
            $table->decimal('rating', 3, 2)->nullable();

            // Status
            $table->enum('status', ['approved', 'pending', 'rejected'])->default('pending');

            $table->timestamps();

            // Indexes
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