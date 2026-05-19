<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('task_requests', function (Blueprint $t) {
            $t->foreignId('customer_id')
                ->nullable()
                ->after('sub_task_id')
                ->constrained('users')
                ->nullOnDelete();
            $t->index('customer_id');
        });

        // (a) Existing rows where user_id points to a customer were really
        //     "submitted by" links, not "assigned tasker" links. Move them.
        DB::statement("
            UPDATE task_requests tr
            JOIN users u ON u.id = tr.user_id
            SET tr.customer_id = tr.user_id,
                tr.user_id     = NULL
            WHERE tr.customer_id IS NULL
              AND u.role = 'user'
        ");

        // (b) Anything still missing a customer — match by email against
        //     verified-or-not customer accounts.
        DB::statement("
            UPDATE task_requests tr
            JOIN users u
              ON u.email = tr.email
             AND u.role  = 'user'
             AND u.deleted_at IS NULL
            SET tr.customer_id = u.id
            WHERE tr.customer_id IS NULL
              AND tr.email IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::table('task_requests', function (Blueprint $t) {
            $t->dropForeign(['customer_id']);
            $t->dropIndex(['customer_id']);
            $t->dropColumn('customer_id');
        });
    }
};
