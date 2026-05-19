<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // (a) extend users with former taskers columns
        Schema::table('users', function (Blueprint $t) {
            $t->string('status', 20)->nullable()->after('role');
            $t->string('phone', 50)->nullable();
            $t->string('nationality', 191)->nullable();
            $t->string('gender', 50)->nullable();
            $t->string('education', 191)->nullable();
            $t->string('profession', 191)->nullable();
            $t->text('work_experience')->nullable();
            $t->string('city', 191)->nullable();
            $t->string('district', 191)->nullable();
            $t->decimal('latitude', 10, 8)->nullable();
            $t->decimal('longitude', 11, 8)->nullable();
            $t->text('skills')->nullable();
            $t->string('verification_code')->nullable();
            $t->string('password_set_token')->nullable();
            $t->unsignedInteger('completed_tasks')->default(0);
            $t->decimal('rating', 3, 2)->nullable();

            $t->index('role');
            $t->index(['role', 'status']);
            $t->index(['role', 'city']);
        });

        // (b) password nullable to accept pre-verify taskers (MySQL-only raw to avoid doctrine/dbal)
        DB::statement('ALTER TABLE users MODIFY password VARCHAR(255) NULL');

        // (c) copy taskers rows into users, build [old_tasker_id => new_user_id] lookup
        $lookup = [];
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

        // (d) rename task_requests.tasker_id -> user_id, remap values, re-add FK + index
        Schema::table('task_requests', function (Blueprint $t) {
            $t->dropForeign(['tasker_id']);
            $t->dropIndex(['tasker_id']);
        });

        DB::statement('ALTER TABLE task_requests CHANGE tasker_id user_id BIGINT UNSIGNED NULL');

        foreach ($lookup as $oldId => $newId) {
            if ($oldId !== $newId) {
                DB::table('task_requests')->where('user_id', $oldId)->update(['user_id' => $newId]);
            }
        }

        Schema::table('task_requests', function (Blueprint $t) {
            $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $t->index('user_id');
        });

        // (e) drop the legacy taskers table
        Schema::dropIfExists('taskers');
    }

    public function down(): void
    {
        throw new \RuntimeException(
            'merge_taskers_into_users is irreversible. Restore from backup if you need to roll back.'
        );
    }
};
