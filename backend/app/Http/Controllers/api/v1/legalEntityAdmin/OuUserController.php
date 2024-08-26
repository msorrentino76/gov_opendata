<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


use Carbon\Carbon;

class OuUserController extends Controller
{

    private $user    = null;
    private $licence = null;
    private $ente    = null;
    
    public function index() {
    }
    
    public function create(Request $request){
        
    }
    
    public function read(string $id){
        
    }
    
    public function update(Request $request, string $id){
        
    }
    
    public function destroy(string $id) {
        
    }
    
    private function setLicence(){
        $this->user = Auth::user();
        $this->licence = $this->user->licence;
        $this->ente    = $this->user->notExpiredLicenceFor();
        if(is_null($this->ente)){
            throw new ResponseException(response('Nessuna licenza attiva', 404));
        }
    }
    
}
