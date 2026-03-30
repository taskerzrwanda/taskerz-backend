<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'image',
        'title',
        'status',
        'tags'
    ];

    protected $casts = [
        'tags' => 'array',
        'status' => 'string'
    ];


    // Relationships
    public function subTasks()
    {
        return $this->hasMany(SubTask::class);
    }

    public function activeSubTasks()
    {
        return $this->hasMany(SubTask::class)->where('status', 'active');
    }

    // Accessors
    public function getSubTaskIdsAttribute()
    {
        return $this->subTasks()->pluck('id')->toArray();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    public function scopeWithActiveSubTasks($query)
    {
        return $query->with(['subTasks' => function ($q) {
            $q->where('status', 'active');
        }]);
    }
}
