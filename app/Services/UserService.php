<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Services\Common\BaseService;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class JsonResponseService
 * @package App\Services
 */
class UserService extends BaseService
{
    public function index($type = 'user')
    {
        $users = User::where('type', '=', $type)
            ->when(request()->filled('status') && request('status') != 2, function ($q) {
                $q->whereStatus(request('status'));
            })
            ->when(request()->filled('search'), function ($q) {
                $q->where(function ($q) {
                    $q->where(DB::raw('concat(first_name," ",last_name)'), 'like', '%' . request("search") . '%')
                        ->orWhere('email', 'like', '%' . request("search") . '%');
                });
            })
            ->when(request()->filled('from'), function ($q) {
                $q->whereDate('created_at', '>=', request('from'))->whereDate('created_at', '<=', request('to'));
            })
            ->when(request()->filled('sort'), function ($q) {

                $q->where('status', '=', request('sort'));

            })
            ->orderBy('id', 'desc')
            ->paginate($this->pagination);
        return $users;
    }

    public function show($id)
    {
        $user = User::where('id', $id)->first();
        return $user;
    }

    public function accountStatus($userId)
    {
        $user = User::find($userId);
        $user->status = ($user->status == 1) ? 0 : 1;
        $user->save();
        return $user;
    }
}
