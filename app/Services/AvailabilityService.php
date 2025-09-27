<?php

namespace App\Services;

use App\Models\Availability;
use App\Models\Category;


use App\Models\User;
use App\Services\Common\BaseService;
use Illuminate\Http\Resources\Json\JsonResource;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JsonResponseService
 * @package App\Services
 */
class AvailabilityService extends BaseService
{
    public function index($request)
    {
        $properties = Availability::with('property')
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

    public function update($request, $id)
    {
        $Availability = Availability::findOrFail($id);
        $data = $request->validated();
        $Availability->update($data);
        return $Availability;
    }

    public function destroy($id)
    {
        $Availability = Availability::findOrFail($id);
        $Availability->delete();
        return 'Deleted';
    }
}
