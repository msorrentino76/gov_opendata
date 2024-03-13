<?php

namespace App\Http\Controllers\api\v1\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function account(Request $request){
        
        return Auth::user()->notifications->take(25);
    }
    
    public function password(Request $request, string $id){
        
        $request->validate([
            'password'             => 'required',
            'new_password'         => 'required',
            'confirm_new_password' => 'required',
        ]);                
        
        $user = Auth::user();

        $erroreVue3 = [];
        
        if (! Hash::check($request->password, $user->password)) {
            $erroreVue3['password'] = 'Password attuale incorretta';
        }
        
        if ($request->password == $request->new_password) {
            $erroreVue3['new_password'] = 'La nuova password non può essere uguale a quella attualmente in uso';
        }
        
        if ($request->new_password != $request->confirm_new_password) {
            $erroreVue3['confirm_new_password'] = 'La nuova password non coincide con il campo di conferma';
        }
        
        if(count($erroreVue3) != 0) {
            return response()->json([
                'errors' => $erroreVue3,
            ], 422); 
        }
        
        $user->password = bcrypt($request->new_password);
        $user->password_changed = true;
        $user->save();
        
        return response($user, 200);
    }
  
}
