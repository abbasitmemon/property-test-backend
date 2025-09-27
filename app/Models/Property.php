<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use Auditable;

    protected $fillable = [
        'title',
        'description',
        'price_per_night',
        'location',
        'amenities',
        'images',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
    ];

    public function availability()
    {
        return $this->hasMany(Availability::class);
    }
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeFilter($query, $request)
    {
        return $query
            ->when($request->filled('location'), function ($q) use ($request) {
                $q->where('location', 'like', "%{$request->location}%");
            })
            ->when($request->filled('min_price'), function ($q) use ($request) {
                $q->where('price_per_night', '>=', $request->min_price);
            })
            ->when($request->filled('max_price'), function ($q) use ($request) {
                $q->where('price_per_night', '<=', $request->max_price);
            })
            ->when($request->filled('start_date') && $request->filled('end_date'), function ($q) use ($request) {
                $q->whereHas('availability', function ($subQuery) use ($request) {
                    $subQuery->where('start_date', '<=', $request->start_date)
                        ->where('end_date', '>=', $request->end_date);
                });
            });
    }
}
