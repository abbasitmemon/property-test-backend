<?php

namespace App\Services;

use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Services\Common\BaseService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

/**
 * Class JsonResponseService
 * @package App\Services
 */
class AdminService extends BaseService
{
    public function checkLogin($request, $type = 'user')
    {
        $user = $this->getUserByEmail($request->email);
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->type !== $type) {
            throw new AuthenticationException(
                "You're not valid user to access this route"
            );
        }
        if ($user->email_verified_at == null) {
            throw new AuthenticationException(
                "Your account is not verified"
            );
        }
        if ($user->status == 0) {
            throw new AuthenticationException(
                "Your account has been deactivated"
            );
        }
        return ['token' => $user->createToken('api-token', ['account:verified'])->plainTextToken, 'user' => new UserResource($user)];
    }

    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function logout()
    {
        $user = Auth::user();
        $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();
    }
}
