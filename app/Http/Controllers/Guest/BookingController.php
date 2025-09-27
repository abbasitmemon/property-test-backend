<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MainApiController;
use App\Http\Requests\Booking\AddBookingRequest;
use App\Http\Resources\Booking\BookingResource;
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

    public function store(AddBookingRequest $request)
    {
        $booking = $this->bookingService->store($request);
        return $this->response->success(
            new BookingResource($booking)
        );
    }
}
