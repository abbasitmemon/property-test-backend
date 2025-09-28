<?php

namespace App\Services;

use App\Models\Availability;

use App\Services\Common\BaseService;

/**
 * Class JsonResponseService
 * @package App\Services
 */
class AvailabilityService extends BaseService
{
    public function index($property_id)
    {
        $properties = Availability::where('property_id', $property_id)->with('property')
            ->orderBy('id', 'desc')
            ->paginate($this->pagination);
        return $properties;
    }


    public function store($request)
    {
        $data = $request->validated();
        $availability = Availability::create($data);
        return $availability;
    }

    public function update($request, $availability)
    {
        $data = $request->validated();
        $availability->update($data);
        return $availability;
    }

    public function destroy($id)
    {
        $Availability = Availability::findOrFail($id);
        $Availability->delete();
        return 'Deleted';
    }
}
