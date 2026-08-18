<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('task_requests', function (Blueprint $t) {
            // Drop and re-add the sub_task_id FK as nullable so requests can
            // exist without a catalogued sub-task (see is_custom below).
            $t->dropForeign(['sub_task_id']);
            $t->unsignedBigInteger('sub_task_id')->nullable()->change();
            $t->foreign('sub_task_id')
                ->references('id')
                ->on('sub_tasks')
                ->onDelete('cascade');

            $t->string('custom_task_title', 255)->nullable()->after('sub_task_id');
            $t->boolean('is_custom')->default(false)->after('custom_task_title');
            $t->index('is_custom');
        });
    }

    public function down(): void
    {
        // down() is destructive if custom requests exist in the wild — any
        // row with sub_task_id NULL will block the NOT NULL re-add.
        Schema::table('task_requests', function (Blueprint $t) {
            $t->dropIndex(['is_custom']);
            $t->dropColumn(['is_custom', 'custom_task_title']);

            $t->dropForeign(['sub_task_id']);
            $t->unsignedBigInteger('sub_task_id')->nullable(false)->change();
            $t->foreign('sub_task_id')
                ->references('id')
                ->on('sub_tasks')
                ->onDelete('cascade');
        });
    }
};
