<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
class VerificationController extends Controller
{
    

public function verify($user_id, Request $request) {

    if (!$request->hasValidSignature()) {
        return response()->json(["msg" => "Invalid/Expired url provided."], 401);
    }
   
    $user = User::find($user_id);

    if(!$user){

        return response()->json(['apiResponse' => false, 'message' => 'Bad User credentials.'], 401);

    }
    
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }
   
    if(!$user->hasVerifiedEmail()){
        return response()->json(['apiResponse' => false, 'message' => 'Problems whith this verifycation, contact the support service.'], 406);
    }

    return response()->json(['apiResponse' => true, 'message' => 'Your email was verified.'], 200);
}

    public function resend(Request $request) {

        

        $verify = Validator::make($request->only(['email']), [

            'email' => ['required', 'string', 'email', 'max:255'],

        ]);

    
        if ($verify->fails()) { 

            return response()->json(['apiResponse' => false, 'errors'=> $verify->errors() ], 401);     
                
        }

        $user = User::where('email', $request->email)->first();
               
        if(!$user){
    
            return response()->json(['apiResponse' => false, 'message' => 'Bad User credentials.'], 401);
            
        }
        

        if ($user->hasVerifiedEmail()) {

            return response()->json(['apiResponse' => false, 'message' => 'Email already verified.'], 400);

        }

        $user->sendEmailVerificationNotification();

        return response()->json(['apiResponse' => true, 'message' => 'Email verification link sent on your email']);
        
    }


}
