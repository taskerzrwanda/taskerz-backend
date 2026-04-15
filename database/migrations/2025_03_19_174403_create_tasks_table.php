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
    if (!Schema::hasTable('tasks')) {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->text('title');
            $table->string('status')->default('active');
            $table->text('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('status');
        });
    }
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
