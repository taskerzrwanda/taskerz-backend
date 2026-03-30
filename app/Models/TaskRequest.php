<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskRequest extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'task_requests';

    protected $fillable = [
        'sub_task_id',
        'tasker_id',
        'full_name',
        'phone',
        'email',
        'location',
        'description',
        'status',
        'assigned_at',
        'completed_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'completed_at' => 'datetime',
        'status' => 'string'
    ];

    // Relationships
    public function subTask()
    {
        return $this->belongsTo(SubTask::class);
    }

    public function tasker()
    {
        return $this->belongsTo(Tasker::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('tasker_id');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('tasker_id');
    }

    // Methods
    public function assignToTasker($taskerId)
    {
        $this->update([
            'tasker_id' => $taskerId,
            'status' => 'approved',
            'assigned_at' => now()
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        // Update tasker stats
        if ($this->tasker) {
            $this->tasker->increment('completed_tasks');
        }
    }

    public function cancel()
    {
        $this->update([
            'status' => 'cancelled'
        ]);
    }
}
