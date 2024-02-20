<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\User;

use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    
    public function register(Request $request): Response {
        
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:5|confirmed', // password_confirmation            
        ]);
        
        $data = $request->only('email', 'name');
        $data['password'] = bcrypt($request->password);
        
        $user = User::create($data);        
        return response($user, 201);
        
    }
    
    public function login(Request $request): Response {
        
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:5', // password_confirmation            
        ]);
        
        $credentials = $request->all();
        
        if(Auth::attempt($credentials)){
            return response(Auth::user(), 200);
        }
        
        abort('401', 'login error');
    }
    
    public function token(Request $request) {
        
        $request->validate([
            /**
             * @todo cf validator!!!!!
             */
            //'cf' => 'required|cf',
            'cf'       => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->post();
        
        if (Auth::attempt($credentials)) {
            
            $user = Auth::user();
            
            /*
            $tokens = $user->tokens;           
            foreach($tokens as $token) {
                $abilities = $token->abilities;
            }*/
            
            $user->tokens()->delete();

            return ['user' => $user, 'token' => $user->createToken($request->cf, json_decode($user->abilities))->plainTextToken];
            
        }

        abort('401', 'login error');

    }
    
    public function revoke(): Response {
        //$user = $request->user();
        Auth::user()->tokens()->delete();
        return response(null, 200);
    }
    
    public function logout(): Response {
        Auth::logout();
        return response(null, 200);
    }
    
}
