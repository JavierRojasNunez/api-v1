<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class emailVerified 
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        if (!Auth::user()->hasVerifiedEmail()) {

            return response()->json(['apiResponse' => false , 'message' => 'You must verify your email.'], 406);

        }
        
        return $next($request);
    }
}
