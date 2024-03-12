<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificaController extends Controller
{
    public function all(){
        return Auth::user()->notifications->take(25);
    }
    
    public function unread(){
        return Auth::user()->unreadNotifications;
    }
    
    public function markOne(){
        return Auth::user()->unreadNotifications;
    }

    public function markAsRead(Request $request, string $id){
        if($id == 'all') {
            return Auth::user()->unreadNotifications->markAsRead();
        } else {
            foreach (Auth::user()->unreadNotifications as $notification) {
                if($notification->id == $id) $notification->markAsRead();
            }
        }
    }    
}
