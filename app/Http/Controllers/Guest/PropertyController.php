<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\MainApiController;
use App\Http\Resources\Property\PropertyResource;
use App\Models\Property;
use App\Services\PropertyService;
use Illuminate\Http\Request;

class PropertyController extends MainApiController
{
    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
        parent::__construct();
    }

    public function index(Request $request)
    {
        $properties = $this->propertyService->index($request);
        return $this->response->success(
            PropertyResource::collection($properties)
        );
    }

    public function show(Property $property)
    {
        return $this->response->success(
            new PropertyResource($property->load('availability'))
        );
    }
}
