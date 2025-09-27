<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Property;
use App\Models\User;
use App\Services\Common\BaseService;
use Illuminate\Http\Resources\Json\JsonResource;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JsonResponseService
 * @package App\Services
 */
class PropertyService extends BaseService
{
    public function index($request)
    {
        $properties = Property::filter($request)
            ->orderBy('id', 'desc')
            ->paginate($this->pagination);
        return $properties;
    }

    public function store($request)
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $property = Property::create($data);
        return $property;
    }

    public function update($request, $property)
    {
        $data = $request->validated();
        $property->update($data);
        return $property;
    }

    public function destroy($property)
    {
        $property->delete();
        return 'Deleted';
    }
}
