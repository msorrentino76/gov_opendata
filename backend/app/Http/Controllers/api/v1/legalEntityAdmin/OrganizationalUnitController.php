<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;

use App\Models\OrganizativeUnit;

use Carbon\Carbon;

class OrganizationalUnitController extends Controller
{

    private $user    = null;
    private $licence = null;
    private $ente    = null;
    
    
    public function index() {
        $this->setLicence();
        return response(OrganizativeUnit::orderBy('des_ou')->where(['legal_entity_id' => $this->ente->id])->get(), 200);
    }
    
    public function create(Request $request){
        
    }
    
    public function read(string $id){
        
    }
    
    public function update(Request $request, string $id){
        
    }
    
    public function destroy(string $id) {
        
        $this->setLicence();
        
        try {
            $ou = OrganizativeUnit::where(['id' => $id, 'legal_entity_id' => $this->ente->id])->first();
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Unità Organizzativa non trovata', 404);
        }
        
        $ou->delete();
        
        return response(['id' => $id], 200);
        
    }
    
    public function ouAutofill() {
        
        $this->setLicence();

        $ous = [];
        
        if(isset($this->ente->cod_amm)){                
            $res = json_decode(wsIpa('WS03_OU.php', array('COD_AMM' => $this->ente->cod_amm)));
            if(isset($res->data)) {
                $ous = $res->data;
            }  
        }
        
        foreach ($ous as $ou) {
            $ou->legal_entity_id = $this->ente->id;
            OrganizativeUnit::updateOrCreate(['cod_uni_ou' => $ou->cod_uni_ou], (array)$ou);
        }
        
        return response(json_encode($ous), 200);
    }
    
    public function ouActivities(string $id) { 
        
        $this->setLicence();
        
        try {
            $ou = OrganizativeUnit::where(['id' => $id, 'legal_entity_id' => $this->ente->id])->first();
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Unità Organizzativa non trovata', 404);
        }
        
        return modelActivities($ou); 
        
    }
    
    private function setLicence(){
        $this->user = Auth::user();
        $this->licence = $this->user->licence;
        $this->ente    = $this->user->notExpiredLicenceFor();
    }
}
