<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MainApiController;
use App\Http\Requests\Booking\UpdateBookingStatusRequest;
use App\Http\Resources\Booking\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends MainApiController
{
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
        parent::__construct();
    }

    public function index(Request $request)
    {
        $bookings = $this->bookingService->index($request);
        return $this->response->success(
            BookingResource::collection($bookings)
        );
    }
    public function updateStatus(UpdateBookingStatusRequest $request, Booking $booking)
    {
        $booking = $this->bookingService->updateStatus($booking, $request);

        return $this->response->success(
            new BookingResource($booking)
        );
    }
}
