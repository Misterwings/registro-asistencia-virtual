<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'date',
        'topic',
        'start_time',
        'end_time',
        'place',
        'reason',
        'directed_by_id',
        'directed_by_position',
        'attachment_path',
        'slug',
        'has_expiration',
        'expiration_date',
    ];

    protected $casts = [
        'date' => 'date',
        'has_expiration' => 'boolean',
        'expiration_date' => 'date',
    ];

    public function directedBy()
    {
        return $this->belongsTo(User::class, 'directed_by_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}
