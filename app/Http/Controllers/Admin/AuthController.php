<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\User\LoginRequest;
use Illuminate\Http\Request;
use App\Http\Resources\DefaultResource;
use App\Http\Controllers\MainApiController;
use App\Services\AdminService;

class AuthController extends MainApiController
{
    private $adminService;

    public function __construct(AdminService $adminService)
    {
        parent::__construct();
        $this->adminService = $adminService;
    }
    public function login(LoginRequest $request)
    {
        $data = $this->adminService->checkLogin($request, 'admin');
        return $this->response->success(
            new DefaultResource($data)
        );
    }


    public function logout(Request $request)
    {
        $this->adminService->logout($request, 'admin');
        return $this->response->success(
            new DefaultResource(['message' => 'Logout Successful!'])
        );
    }

    public function profile(Request $request)
    {

        $admin = auth()->guard()->user();
        return $this->response->success(
            new DefaultResource($admin)
        );
    }
}
