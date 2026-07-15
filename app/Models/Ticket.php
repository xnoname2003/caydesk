<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\SlaRule;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Ticket extends Model
{
    use HasFactory, HasUuids, SoftDeletes, LogsActivity;

    protected $casts = [
        'due_at' => 'datetime',
        'response_due_at' => 'datetime',
        'first_responded_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $fillable = [
        'ticket_number',
        'title',
        'description',
        'status',
        'priority_id',
        'category_id',
        'created_by',
        'assigned_agent_id',
        'due_at',
        'resolved_at',
        'closed_at',
        'response_due_at',
        'first_responded_at',
    ];

    public function getRouteKeyName()
    {
        return 'ticket_number';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    protected static function booted()
    {
        static::creating(function ($ticket) {
            if ($ticket->priority_id) {
                $sla = SlaRule::where('priority_id', $ticket->priority_id)->first();
                if ($sla) {
                    if ($sla->resolution_hours) {
                        $ticket->due_at = now()->addHours($sla->resolution_hours);
                    }
                    if ($sla->response_time_hours) {
                        $ticket->response_due_at = now()->addHours($sla->response_time_hours);
                    }
                }
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'status', 
                'priority.name',
                'category.name',
                'assignedAgent.name'
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn(string $eventName) => "Ticket has been {$eventName}");
    }
}
