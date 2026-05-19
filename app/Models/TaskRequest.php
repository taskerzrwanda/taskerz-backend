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
        'user_id',
        'customer_id',
        'full_name',
        'phone',
        'email',
        'location',
        'description',
        'status',
        'assigned_at',
        'completed_at',
    ];

    protected $casts = [
        'assigned_at'  => 'datetime',
        'completed_at' => 'datetime',
        'status'       => 'string',
    ];

    public function subTask()
    {
        return $this->belongsTo(SubTask::class);
    }

    public function tasker()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

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
        return $query->whereNull('user_id');
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function assignToTasker($taskerId)
    {
        $this->update([
            'user_id'     => $taskerId,
            'status'      => 'approved',
            'assigned_at' => now(),
        ]);
    }

    public function markAsCompleted()
    {
        $this->update([
            'status'       => 'completed',
            'completed_at' => now(),
        ]);

        if ($this->tasker) {
            $this->tasker->increment('completed_tasks');
        }
    }

    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
