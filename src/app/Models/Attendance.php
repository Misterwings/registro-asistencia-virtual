<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'event_id',
        'full_name',
        'id_number',
        'position_id',
        'position_custom',
        'headquarter_id',
        'headquarter_custom',
        'signature',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function getPositionLabelAttribute(): ?string
    {
        return $this->position?->name ?? $this->position_custom;
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarter::class);
    }

    public function getHeadquarterLabelAttribute(): ?string
    {
        return $this->headquarter?->name ?? $this->headquarter_custom;
    }
}
