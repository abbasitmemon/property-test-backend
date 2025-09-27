<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MainApiController;
use App\Http\Requests\Availability\AddAvailabilityRequest;
use App\Http\Resources\Availability\AvailabilityResource;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;

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
}
