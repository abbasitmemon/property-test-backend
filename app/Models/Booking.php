<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'guest_name',
        'guest_email',
        'start_date',
        'end_date',
        'status',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function scopeFilter($query, $filters)
    {
        return $query->when(isset($filters['status']), function ($q) use ($filters) {
            $q->where('status', $filters['status']);
        });
    }
}
