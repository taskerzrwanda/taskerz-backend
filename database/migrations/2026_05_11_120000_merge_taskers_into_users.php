<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // (a) extend users with former taskers columns — each add guarded so the
        // migration can resume after a partial previous run (e.g. row missing
        // from the migrations table on production).
        $newColumns = [
            'status'             => fn (Blueprint $t) => $t->string('status', 20)->nullable()->after('role'),
            'phone'              => fn (Blueprint $t) => $t->string('phone', 50)->nullable(),
            'nationality'        => fn (Blueprint $t) => $t->string('nationality', 191)->nullable(),
            'gender'             => fn (Blueprint $t) => $t->string('gender', 50)->nullable(),
            'education'          => fn (Blueprint $t) => $t->string('education', 191)->nullable(),
            'profession'         => fn (Blueprint $t) => $t->string('profession', 191)->nullable(),
            'work_experience'    => fn (Blueprint $t) => $t->text('work_experience')->nullable(),
            'city'               => fn (Blueprint $t) => $t->string('city', 191)->nullable(),
            'district'           => fn (Blueprint $t) => $t->string('district', 191)->nullable(),
            'latitude'           => fn (Blueprint $t) => $t->decimal('latitude', 10, 8)->nullable(),
            'longitude'          => fn (Blueprint $t) => $t->decimal('longitude', 11, 8)->nullable(),
            'skills'             => fn (Blueprint $t) => $t->text('skills')->nullable(),
            'verification_code'  => fn (Blueprint $t) => $t->string('verification_code')->nullable(),
            'password_set_token' => fn (Blueprint $t) => $t->string('password_set_token')->nullable(),
            'completed_tasks'    => fn (Blueprint $t) => $t->unsignedInteger('completed_tasks')->default(0),
            'rating'             => fn (Blueprint $t) => $t->decimal('rating', 3, 2)->nullable(),
        ];

        Schema::table('users', function (Blueprint $t) use ($newColumns) {
            foreach ($newColumns as $name => $add) {
                if (! Schema::hasColumn('users', $name)) {
                    $add($t);
                }
            }
        });

        // Indexes (idempotent via information_schema lookup — MySQL has no IF NOT EXISTS for ADD INDEX).
        $this->addIndexIfMissing('users', 'users_role_index', 'role');
        $this->addIndexIfMissing('users', 'users_role_status_index', 'role, status');
        $this->addIndexIfMissing('users', 'users_role_city_index', 'role, city');

        // (b) password nullable to accept pre-verify taskers (no-op if already nullable)
        $passwordCol = DB::selectOne("SHOW COLUMNS FROM users WHERE Field = 'password'");
        if ($passwordCol && strtoupper($passwordCol->Null) !== 'YES') {
            DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');
        }

        // (c) copy taskers rows into users — only if the legacy table is still present.
        $lookup = [];
        if (Schema::hasTable('taskers')) {
            $defaultPw = Hash::make(config('app.tasker_default_password', env('TASKER_SEED_PASSWORD', 'ChangeMe!2026')));

            DB::transaction(function () use (&$lookup, $defaultPw) {
                DB::table('taskers')->orderBy('id')->chunkById(200, function ($rows) use (&$lookup, $defaultPw) {
                    foreach ($rows as $r) {
                        $existing = DB::table('users')->where('email', $r->email)->first();

                        $payload = [
                            'role'               => 'tasker',
                            'status'             => $r->status,
                            'phone'              => $r->phone,
                            'nationality'        => $r->nationality,
                            'gender'             => $r->gender,
                            'education'          => $r->education,
                            'profession'         => $r->profession,
                            'work_experience'    => $r->work_experience,
                            'city'               => $r->city,
                            'district'           => $r->district,
                            'latitude'           => $r->latitude,
                            'longitude'          => $r->longitude,
                            'skills'             => $r->skills,
                            'verification_code'  => $r->verification_code,
                            'password_set_token' => $r->password_set_token,
                            'completed_tasks'    => $r->completed_tasks,
                            'rating'             => $r->rating,
                            'email_verified_at'  => $r->email_verified_at,
                            'updated_at'         => now(),
                        ];

                        if ($existing) {
                            DB::table('users')->where('id', $existing->id)->update($payload);
                            $lookup[$r->id] = $existing->id;
                            continue;
                        }

                        $newId = DB::table('users')->insertGetId(array_merge($payload, [
                            'name'       => $r->name,
                            'email'      => $r->email,
                            'password'   => $r->password ?: $defaultPw,
                            'created_at' => $r->created_at,
                        ]));
                        $lookup[$r->id] = $newId;
                    }
                });
            });
        }

        // (d) rename task_requests.tasker_id -> user_id (only if not already renamed),
        // remap values, re-add FK + index. The FK/index may have already been dropped
        // by a prior partial run, so check information_schema before issuing the drops.
        if (Schema::hasColumn('task_requests', 'tasker_id')) {
            // Alias columns to lowercase explicitly: MySQL 8 returns information_schema
            // column names in uppercase, which breaks $row->lower_case access.
            $fk = DB::selectOne(
                "SELECT CONSTRAINT_NAME AS name FROM information_schema.key_column_usage
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task_requests'
                    AND COLUMN_NAME = 'tasker_id' AND REFERENCED_TABLE_NAME IS NOT NULL
                  LIMIT 1"
            );
            if ($fk) {
                DB::statement("ALTER TABLE `task_requests` DROP FOREIGN KEY `{$fk->name}`");
            }

            $idx = DB::selectOne(
                "SELECT INDEX_NAME AS name FROM information_schema.statistics
                  WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'task_requests'
                    AND COLUMN_NAME = 'tasker_id' AND INDEX_NAME <> 'PRIMARY'
                  LIMIT 1"
            );
            if ($idx) {
                DB::statement("ALTER TABLE `task_requests` DROP INDEX `{$idx->name}`");
            }

            DB::statement('ALTER TABLE task_requests CHANGE tasker_id user_id BIGINT UNSIGNED NULL');
        }

        foreach ($lookup as $oldId => $newId) {
            if ($oldId !== $newId) {
                DB::table('task_requests')->where('user_id', $oldId)->update(['user_id' => $newId]);
            }
        }

        if (Schema::hasColumn('task_requests', 'user_id')) {
            $this->addForeignIfMissing('task_requests', 'user_id', 'users', 'id');
            $this->addIndexIfMissing('task_requests', 'task_requests_user_id_index', 'user_id');
        }

        // (e) drop the legacy taskers table
        Schema::dropIfExists('taskers');
    }

    private function addIndexIfMissing(string $table, string $indexName, string $columns): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(1) AS c FROM information_schema.statistics
              WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $indexName]
        );
        if (! $exists || (int) $exists->c === 0) {
            DB::statement("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columns})");
        }
    }

    private function addForeignIfMissing(string $table, string $column, string $refTable, string $refColumn): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(1) AS c FROM information_schema.key_column_usage
              WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?
                AND referenced_table_name = ? AND referenced_column_name = ?',
            [$table, $column, $refTable, $refColumn]
        );
        if (! $exists || (int) $exists->c === 0) {
            DB::statement(
                "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_{$column}_foreign`
                 FOREIGN KEY (`{$column}`) REFERENCES `{$refTable}` (`{$refColumn}`) ON DELETE SET NULL"
            );
        }
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'merge_taskers_into_users is irreversible. Restore from backup if you need to roll back.'
        );
    }
};
