<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\MainApiController;
use App\Http\Requests\Availability\AddAvailabilityRequest;
use App\Http\Requests\Availability\UpdateAvailabilityRequest;
use App\Http\Resources\Availability\AvailabilityResource;
use App\Models\Availability;
use App\Services\AvailabilityService;

class AvailabilityController extends MainApiController
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
        parent::__construct();
    }

    public function index($property_id)
    {
        $availabilities = $this->availabilityService->index($property_id);
        return $this->response->success(
            AvailabilityResource::collection($availabilities)
        );
    }

    public function store(AddAvailabilityRequest $request)
    {
        $availability = $this->availabilityService->store($request);
        return $this->response->success(
            new AvailabilityResource($availability->load('property'))
        );
    }
    public function update(UpdateAvailabilityRequest $request, Availability $availability)
    {
        $availability = $this->availabilityService->update($request, $availability);
        return $this->response->success(
            new AvailabilityResource($availability->load('property'))
        );
    }
}
