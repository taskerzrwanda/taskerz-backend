<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'phone',
        'nationality',
        'gender',
        'education',
        'profession',
        'work_experience',
        'city',
        'district',
        'latitude',
        'longitude',
        'skills',
        'verification_code',
        'password_set_token',
        'completed_tasks',
        'rating',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
        'password_set_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'skills'            => 'array',
            'latitude'          => 'decimal:8',
            'longitude'         => 'decimal:8',
            'rating'            => 'decimal:2',
            'completed_tasks'   => 'integer',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
            'role'   => $this->role,
            'status' => $this->status,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isTasker(): bool
    {
        return $this->role === 'tasker';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'user';
    }

    public function taskRequests()
    {
        return $this->hasMany(TaskRequest::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(TaskRequest::class)->where('status', 'approved');
    }

    public function completedTaskRequests()
    {
        return $this->hasMany(TaskRequest::class)->where('status', 'completed');
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeTaskers($query)
    {
        return $query->where('role', 'tasker');
    }

    public function scopeCustomers($query)
    {
        return $query->where('role', 'user');
    }

    public function scopeApprovedTaskers($query)
    {
        return $query->where('role', 'tasker')->where('status', 'approved');
    }

    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeWithSkill($query, $skill)
    {
        return $query->whereJsonContains('skills', $skill);
    }

    public function hasSkill($skill): bool
    {
        if (!$this->skills) {
            return false;
        }
        return in_array(strtolower($skill), array_map('strtolower', $this->skills));
    }

    public function calculateDistance($latitude, $longitude)
    {
        if (!$this->latitude || !$this->longitude) {
            return null;
        }

        $earthRadius = 6371;

        $dLat = deg2rad($latitude - $this->latitude);
        $dLon = deg2rad($longitude - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
