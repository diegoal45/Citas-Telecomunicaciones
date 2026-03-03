<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id', 'materials', 'labor_hours',
        'required_staff', 'price', 'approved_at', 'rejected_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'labor_hours' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function isApproved()
    {
        return !is_null($this->approved_at);
    }

    public function isRejected()
    {
        return !is_null($this->rejected_at);
    }
}