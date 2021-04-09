<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\RegistersUsers;


class AuthApiController extends Controller
{
    use RegistersUsers;


    private $HttpstatusCode = 201;

    public function signup(Request $request)
    {

        
        if(!$request->isMethod('post') || !$request->has(['name', 'email', 'password', 'password_confirmation'])){
            return response()->json(['apiResponse' => false, 'message' => 'you have not sent any data or data is missing'], 401);
        }

        $userExists = User::where("email", $request->email)->exists();

        if($userExists){
            return response()->json(['apiResponse' => false, 'message' => 'Not created, user allready exist!.'], 406);
        }

        $verify = Validator::make($request->only(['name', 'email', 'password', 'password_confirmation']), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

  

        if ($verify->fails()) { 
            return response()->json(['apiResponse' => false, 'errors'=> $verify->errors() ], 401);            
        }
       

        try
        {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'surname' => 'apiUser',
                'password' => Hash::make($request->password),

            ])->sendEmailVerificationNotification();

        }catch(Exception $e){

            return response()->json(['apiResponse' => false , 'message' => 'Not created, Problems whith data base, please try again!.'], 406);
            
        }
        
        return response()->json(['apiResponse' => true, 'message' => 'Successfully created user! You can login After confirm your email.'], $this->HttpstatusCode);

        
            
    }

    public function login(Request $request)
    {
        
        
        $verify = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
            'remember_me' => ['boolean'],
        ]);

  

        if ($verify->fails()) { 
            return response()->json(['apiResponse' => false , 'error'=>$verify->errors()], 401);            
        }


        $credentials = $request->only(['email', 'password']);

        if (!Auth::attempt($credentials)) {
            return response()->json([
                'apiResponse' => false , 
                'message' => 'Unauthorized'], 401);
        }

        if (!Auth::user()->hasVerifiedEmail()) {
            return response()->json(['apiResponse' => false , 'message' => 'You must verify your email.'], 406);
        }
        
       

        $user = $request->user();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }
        $token->save();
        return response()->json([
            'apiResponse' => true , 
            'access_token' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at)
                ->toDateTimeString(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json(['apiResponse' => true , 'message' =>
            'Successfully logged out']);
    }

    public function user(Request $request)
    {
        return response()->json($request->user());
    }
}
