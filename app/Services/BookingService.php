<?php

namespace App\Services;

use App\Jobs\SendBookingStatusEmailJob;
use App\Models\Booking;
use App\Services\Common\BaseService;

class BookingService extends BaseService
{
    public function store($request)
    {
        $data = $request->validated();
        return Booking::create($data);
    }

    public function updateStatus($booking, $request)
    {
        $data = $request->validated();
        $booking->update([
            'status' => $data['status']
        ]);

        // Dispatch email job
        dispatch(new SendBookingStatusEmailJob($booking));

        return $booking;
    }

    public function index($filters = [])
    {
        return Booking::with('property')
            ->filter($filters)
            ->orderBy('id', 'desc')
            ->paginate($this->pagination);
    }
}
