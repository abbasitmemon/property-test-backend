<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user || $user->type !== "admin") {
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
        return $next($request);
    }
}
