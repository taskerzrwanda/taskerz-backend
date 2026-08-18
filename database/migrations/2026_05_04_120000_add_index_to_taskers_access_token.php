<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('taskers', function (Blueprint $table) {
            $table->index('access_token', 'taskers_access_token_index');
        });
    }

    public function down(): void
    {
        Schema::table('taskers', function (Blueprint $table) {
            $table->dropIndex('taskers_access_token_index');
        });
    }
};
