<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\User;
use App\Models\StoricoLogin;

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
            'username' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->post();
        
        if (Auth::attempt($credentials)) {
            
            $user = Auth::user();
            
            StoricoLogin::store($request, $user);

            /*
            $tokens = $user->tokens;           
            foreach($tokens as $token) {
                $abilities = $token->abilities;
            }*/
            
            $user->tokens()->delete();
         
            if($user->enabled) {
            
                $abilities = json_decode($user->abilities);
                
                // Chiaramente per ragioni di sicurezza il token per un 'system:admin' dovrebbe scadere prima possibile
                // Tuttavia questo ruolo prevede elaborazioni molto impegnative e lunghe, per cui...
                if(in_array('system:admin', $abilities)){
                    $token = $user->createToken($request->username, $abilities, now()->addWeek())->plainTextToken;
                } else {
                    $token = $user->createToken($request->username, $abilities)->plainTextToken;
                }
                
                return [
                    'user'        => $user,
                    'licence_for' => $user->notExpiredLicenceFor(),
                    'token'       => $token,
                    ];
                }
            
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
