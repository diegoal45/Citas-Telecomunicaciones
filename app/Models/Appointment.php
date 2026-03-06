<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'team_id', 'appointment_type', 'status',
        'scheduled_date', 'duration_minutes', 'address', 'description',
        'cancelled_at', 'cancelled_by', 'cancellation_reason'
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function quotation()
    {
        return $this->hasOne(Quotation::class);
    }
}