<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class WSR_Auth
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();
        if (!is_null($token)) {
            /** @var User $trying_user */
            $trying_user = User::cursor()->filter(function ($user) use ($token) {
                return Hash::check($token, $user->wsr_token);
            })->first();
            if (!is_null($trying_user)) {
                Auth::onceUsingId($trying_user->id);
                return $next($request);
            }
        }
        return response()->json(['message' => 'You need authorization'], 403);

    }
}
