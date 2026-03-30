<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tasker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'nationality', 'gender', 'education', 'email',
        'phone', 'profession', 'work_experience',
        'city', 'district', 'latitude', 'longitude', 'skills',
        'verification_code', 'access_token', 'email_verified_at',
        'password_set_token', 'status', 'completed_tasks', 'rating'
    ];

    protected $hidden = [
        'remember_token',
        'verification_code',
        'access_token',
        'password_set_token'
    ];

    protected $casts = [
        'status' => 'string',
        'skills' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'rating' => 'decimal:2',
        'completed_tasks' => 'integer'
    ];

    // Relationships
    public function taskRequests()
    {
        return $this->hasMany(TaskRequest::class);
    }

    public function assignedTasks()
    {
        return $this->hasMany(TaskRequest::class)
            ->where('status', 'approved');
    }

    public function completedTaskRequests()
    {
        return $this->hasMany(TaskRequest::class)
            ->where('status', 'completed');
    }

    // Scopes
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeWithSkill($query, $skill)
    {
        return $query->whereJsonContains('skills', $skill);
    }

    // Methods
    public function hasSkill($skill)
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

        // Haversine formula
        $earthRadius = 6371; // km

        $dLat = deg2rad($latitude - $this->latitude);
        $dLon = deg2rad($longitude - $this->longitude);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
