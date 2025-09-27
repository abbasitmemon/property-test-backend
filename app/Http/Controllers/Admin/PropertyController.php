<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MainApiController;
use App\Http\Requests\Property\AddPropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\DefaultResource;
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

    public function store(AddPropertyRequest $request)
    {
        $property = $this->propertyService->store($request);
        return $this->response->success(
            new PropertyResource($property)
        );
    }

    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $property = $this->propertyService->update($request, $property);
        return $this->response->success(
            new PropertyResource($property)
        );
    }

    public function destroy(Property $property)
    {
        $message = $this->propertyService->destroy($property);
        return $this->response->success(
            new DefaultResource(['message' => $message])
        );
    }
}
