<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
class VerificationController extends Controller
{
    

public function verify($user_id, Request $request) {

    if (!$request->hasValidSignature()) {
        return response()->json(["msg" => "Invalid/Expired url provided."], 401);
    }
   
    $user = User::findOrFail($user_id);

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }
   
    if(!$user->hasVerifiedEmail()){
        return response()->json(['apiResponse' => false, 'message' => 'Problems whith this verifycation, contact the support service.'], 400);
    }

    return response()->json(['apiResponse' => true, 'message' => 'Your email was verified.'], 200);
}

public function resend() {


    if (Auth::user()->hasVerifiedEmail()) {
        return response()->json(['apiResponse' => false, 'message' => 'Email already verified.'], 400);
    }

    Auth::user()->sendEmailVerificationNotification();

    return response()->json(['apiResponse' => true, 'message' => 'Email verification link sent on your email']);
}


}
