<?php

namespace App\Http\Controllers\api\v1\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Notifications\Assistenza;

class AssistenzaController extends Controller
{
    public function send(Request $request){
        
        $request->validate([
            'oggetto' => 'required',
            'testo'   => 'required|string|max:640',
        ]);
        
        $user = Auth::user();
        
        $data = $request->only('oggetto', 'testo');
        
        $admins = \App\Models\User::
                    whereJsonContains('abilities', 'system:admin')
                ->orWhereJsonContains ('abilities', 'system:assistence')
                ->get();
        
        foreach($admins as $admin){
            $admin->notify(new Assistenza($data['oggetto'], $data['testo'], $user));
        }
        
        return response('ok', 200);
    }    
}
