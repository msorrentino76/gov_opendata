<?php

namespace App\Http\Controllers\api\v1\sysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\LegalEntity;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Document;

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
    
    public function index(){
        return response(LegalEntity::with('logo')->orderBy('des_amm')->get(), 200);
    }
      
    public function create(Request $request){
        
        $request->validate([
            'des_amm' => 'required',
            'cf'      => 'required|unique:legal_entities|string|size:11',
            'cod_amm' => 'nullable|unique:legal_entities',
            'sito_istituzionale' => 'nullable|url',
            'mail1' => 'nullable|email',
            'mail2' => 'nullable|email',
            'mail3' => 'nullable|email',
            'mail4' => 'nullable|email',
            'mail5' => 'nullable|email',
        ]);
        
        //$user = Auth::user();
        
        $data = $request->only(
                'logo_img',
                'cod_amm', 'acronimo', 'des_amm', 'regione', 'provincia', 'comune', 'cap', 'indirizzo',
                'titolo_resp', 'nome_resp', 'cogn_resp', 'sito_istituzionale', 'liv_access', 'mail1',
                'mail2', 'mail3', 'mail4', 'mail5', 'tipologia', 'categoria', 'data_accreditamento', 'cf', 'telefono', 'fax'
        );

        try{
            
            DB::beginTransaction();

            $le = LegalEntity::create($data);

            /*
             *  COLLEGO GLI ALLEGATI: FACOLTATIVI! POTREBBE NON ESSERCI
            */

            $data['logo_img'] = isset($data['logo_img']) ? $data['logo_img'] : [];

            foreach($data['logo_img'] as $allegato){
                $documento = Document::where(['id' => $allegato['response']['id'], 'user_id' => Auth::user()->id])->first();
                if(!is_null($documento)) {
                    $documento->toMorph($le, true);
                }
            }
                
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User: ' . Auth::user()->id . ' - Exception message: ' . $e->getMessage());
            return response(['message' => 'Errore durante la creazione dell\'Ente', 'error' => $e->getMessage()], 500);
        }
        
        return response(LegalEntity::with('logo')->where(['legal_entities.id' => $le->id])->first(), 200);
                
    }
    
    public function read(string $id){
        
        try {
            $le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Ente non trovato', 404);
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
                'logo_img',
                /*'cod_amm',*/ 'acronimo', 'des_amm', 'regione', 'provincia', 'comune', 'cap', 'indirizzo',
                'titolo_resp', 'nome_resp', 'cogn_resp', 'sito_istituzionale', 'liv_access', 'mail1',
                'mail2', 'mail3', 'mail4', 'mail5', 'tipologia', 'categoria', 'data_accreditamento', /*'cf',*/ 'telefono', 'fax'
        );

        try {
            $le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Ente non trovato', 404);
        }
                
        try{
            
            DB::beginTransaction();

            $le->update($data);

            /*
             *  COLLEGO GLI ALLEGATI: FACOLTATIVI! POTREBBE NON ESSERCI
            */

            $data['logo_img'] = isset($data['logo_img']) ? $data['logo_img'] : [];

            foreach($data['logo_img'] as $allegato){
                $documento = Document::where(['id' => $allegato['response']['id'], 'user_id' => Auth::user()->id])->first();
                if(!is_null($documento)) {
                    $documento->toMorph($le, true);
                }
            }
                
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User: ' . Auth::user()->id . ' - Exception message: ' . $e->getMessage());
            return response(['message' => 'Errore durante la creazione dell\'Ente', 'error' => $e->getMessage()], 500);
        }
        
        return response(LegalEntity::with('logo')->where(['legal_entities.id' => $le->id])->first(), 200);
        
    }
    
    public function destroy(string $id) {
        
        try {
            $le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Ente non trovato', 404);
        }
        
        $le->cod_amm = 'del_' . $le->id . '_' . $le->cod_amm;
        $le->cf      = 'del_' . $le->id . '_' . $le->cf;
        $le->save();
        
        $le->delete();
        
        return response(['id' => $id], 200);
        
    }
    
    public function legalSuggestions(Request $request) {
        
        $results = [];
        $data = $request->only('looking_for');
        $looking_for = $data['looking_for'];                
        $res = json_decode(wsIpa('WS16_DES_AMM.php', array('DESCR' => $looking_for)));
        
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
            $res = json_decode(wsIpa('WS05_AMM.php', array('COD_AMM' => $data['cod_amm'])));
            if(isset($res->data)) {
                return response(json_encode($res->data), 200);
            }  
        }
        /*
         * 422 VA BENE PER I FORM
         * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
         */
        return response('Ente non trovato su Indice PA', 404);
    }
    
    public function legalActivities($id) {
        
        try {
            $le = LegalEntity::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Ente non trovato', 404);
        }
        
        return modelActivities($le);    
    }
    
}
