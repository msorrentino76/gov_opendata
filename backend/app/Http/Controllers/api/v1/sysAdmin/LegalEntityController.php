<?php

namespace App\Http\Controllers\api\v1\sysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\LegalEntity;
use Carbon\Carbon;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class LegalEntityController extends Controller
{

    /*
     * index(): Visualizza un elenco di risorse.
     * - create(): Mostra il modulo per creare una nuova risorsa.
     * store(): Salva una nuova risorsa nel database.
     * show($id): Visualizza una singola risorsa.
     * - edit($id): Mostra il modulo per modificare una risorsa esistente.
     * update(Request $request, $id): Aggiorna una risorsa esistente nel database.
     * destroy($id): Elimina una risorsa dal database.
     */
    
    public function index(Request $request)
    {
        return response(LegalEntity::orderBy('des_amm')->get(), 200);
    }
      
    public function create(Request $request){
        
        $request->validate([
            'des_amm' => 'required',
            'cf'      => 'required|unique:legal_entities|string|size:11',
            'cod_amm' => 'unique:legal_entities',
            'sito_istituzionale' => 'nullable|url',
            'mail1' => 'nullable|email',
            'mail2' => 'nullable|email',
            'mail3' => 'nullable|email',
            'mail4' => 'nullable|email',
            'mail5' => 'nullable|email',
        ]);
        
        //$user = Auth::user();
        
        $data = $request->only(
                'cod_amm', 'acronimo', 'des_amm', 'regione', 'provincia', 'comune', 'cap', 'indirizzo',
                'titolo_resp', 'nome_resp', 'cogn_resp', 'sito_istituzionale', 'liv_access', 'mail1',
                'mail2', 'mail3', 'mail4', 'mail5', 'tipologia', 'categoria', 'data_accreditamento', 'cf', 'telefono', 'fax'
        );

        $le = LegalEntity::create($data);
        
        /*
         *  COLLEGO GLI ALLEGATI: FACOLTATIVI! POTREBBE NON ESSERCI
        
        $data['allegati'] = isset($data['allegati']) ? $data['allegati'] : [];
        
        foreach($data['allegati'] as $allegato){
            $documento = Document::where(['id' => $allegato['response']['id'], 'user_id' => $user->id])->first();
            if(!is_null($documento)) {
                $documento->toMorph($act);
            }
        }
        */
        
        //return Act::with('actDocuments')/*->with('partecipants')*/->where(['activities.id' => $act->id])->first();
        
        return $le;
        
    }
    
    public function read(string $id){
        
        try {
            $le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
        }
        
        return response($le, 200);
        
    }
    
    public function update(Request $request, string $id){
        
        $request->validate([
            //'des_amm' => 'required',
            //'cf'      => 'required|unique:legal_entities|string|size:11',
            //'cod_amm' => 'unique:legal_entities',
            'sito_istituzionale' => 'nullable|url',
            'mail1' => 'nullable|email',
            'mail2' => 'nullable|email',
            'mail3' => 'nullable|email',
            'mail4' => 'nullable|email',
            'mail5' => 'nullable|email',
        ]);
        
        //$user = Auth::user();
        
        $data = $request->only(
                /*'cod_amm',*/ 'acronimo', 'des_amm', 'regione', 'provincia', 'comune', 'cap', 'indirizzo',
                'titolo_resp', 'nome_resp', 'cogn_resp', 'sito_istituzionale', 'liv_access', 'mail1',
                'mail2', 'mail3', 'mail4', 'mail5', 'tipologia', 'categoria', 'data_accreditamento', /*'cf',*/ 'telefono', 'fax'
        );

        try {
            $le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
        }
        
        $le->update($data);
        
        /*
         *  COLLEGO GLI ALLEGATI: FACOLTATIVI! POTREBBE NON ESSERCI
        
        $data['allegati'] = isset($data['allegati']) ? $data['allegati'] : [];
        
        foreach($data['allegati'] as $allegato){
            $documento = Document::where(['id' => $allegato['response']['id'], 'user_id' => $user->id])->first();
            if(!is_null($documento)) {
                $documento->toMorph($act);
            }
        }
        */
        
        //return Act::with('actDocuments')/*->with('partecipants')*/->where(['activities.id' => $act->id])->first();
        
        return $le;
        
    }
    
    public function destroy(string $id) {
        
        try {
            $le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
        }
        
        $le->delete();
        
        return response(['id' => $id], 200);
        
    }
    
    public function legalSuggestions(Request $request) {
        
        $results = [];
        $data = $request->only('looking_for');
        $looking_for = $data['looking_for'];                
        $res = json_decode($this->wsIpa('WS16_DES_AMM.php', array('DESCR' => $looking_for)));
        
        if(isset($res->data)) {
            foreach ($res->data as $item) {
                $results[] = ['value' => $item->des_amm, 'cod_amm' => $item->cod_amm];
            }
        }
         
        return response($results, 200);
    }
    
    public function legalIPADetails(Request $request) {
        $data = $request->only('cod_amm');
        if(isset($data['cod_amm'])){                
            $res = json_decode($this->wsIpa('WS05_AMM.php', array('COD_AMM' => $data['cod_amm'])));
            if(isset($res->data)) {
                return response(json_encode($res->data), 200);
            }  
        }
        return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
    }
    
    private function wsIpa($service, $data) {
        $url = config('app.ipa_ws_base_url') . $service;
        $data['AUTH_ID'] = config('app.ipa_ws_auth_id');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
    
}
