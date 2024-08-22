<?php

namespace App\Http\Controllers\api\v1\sysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Licence;
use App\Models\User;
use App\Models\LegalEntity;

use Illuminate\Support\Facades\Auth;

use Carbon\Carbon;

class LicenseController extends Controller
{

    public function index(){
        return response(Licence::orderBy('valida_a')->get(), 200);
    }
    
    public function available(){
        return response(
            [
                'users'  => User::doesntHave('licence')->select('id', 'name', 'surname', 'email')->where(['created_by' => Auth::user()->id])->orderBy('surname')->get(),
                'legals' => LegalEntity::doesntHave('licence')->select('id', 'des_amm', 'cf')->orderBy('des_amm')->get(),
            ], 200);
    }
    
    public function create(Request $request){
        
    }
    
    public function read(string $id){
        
    }
    
    public function update(Request $request, string $id){
        
    }
    
    public function destroy(string $id) {
        
    }
    
    public function licenceActivities($id) {
        
        try {
            //$le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Licenza non trovato', 404);
        }
        
        return modelActivities($le);    
    }
}
