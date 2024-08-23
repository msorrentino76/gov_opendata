<?php

namespace App\Http\Controllers\api\v1\sysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Licence;
use App\Models\User;
use App\Models\LegalEntity;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Notifications\NuovaLicenza;
use App\Notifications\ModificaLicenza;

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
        
        $data = $request->only('user', 'legal', 'valida_da', 'valida_a');

        $save['user_id']         = $data['user'];
        $save['legal_entity_id'] = $data['legal'];
        $save['valida_da']       = Carbon::createFromFormat('d/m/Y', $data['valida_da'])->format('Y-m-d H:i:s');
        $save['valida_a']        = Carbon::createFromFormat('d/m/Y', $data['valida_a'])->format('Y-m-d H:i:s');

        try {
            
            $user = User::findOrFail($save['user_id']);
        
            DB::beginTransaction();
            $licence = Licence::create($save);
            $user->notifY(new NuovaLicenza((string)$user, $licence));
            DB::commit();
            
            return $licence;
            
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('User: ' . Auth::user()->id . ' - Exception message: ' . $e->getMessage());
            return response(['message' => 'Errore durante la creazione della licenza', 'error' => $e->getMessage()], 500);
        }

    }
    
    public function read(string $id){
        
        try {
            $licence = Licence::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Licenza non trovata', 404);
        }
        
        return response($licence, 200);
    }
    
    public function update(Request $request, string $id){
        
        try {
            $licence = Licence::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Licenza non trovata', 404);
        }
        
        $data = $request->only('valida_da', 'valida_a');

        $save['valida_da']       = Carbon::createFromFormat('d/m/Y', $data['valida_da'])->format('Y-m-d H:i:s');
        $save['valida_a']        = Carbon::createFromFormat('d/m/Y', $data['valida_a'])->format('Y-m-d H:i:s');

        try {
            
            $user = User::findOrFail($licence->user_id);
        
            DB::beginTransaction();
            $licence->update($save);
            $user->notifY(new ModificaLicenza((string)$user, $licence));
            DB::commit();
            
            return $licence;
            
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('User: ' . Auth::user()->id . ' - Exception message: ' . $e->getMessage());
            return response(['message' => 'Errore durante l\'aggiornamento della licenza', 'error' => $e->getMessage()], 500);
        }
        
    }
    
    public function destroy(string $id) {
        
        try {
            $licence = Licence::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Licenza non trovata', 404);
        }
        
        $licence->delete();
        
        return response(['id' => $id], 200);
        
    }
    
    public function licenceActivities($id) {
        
        try {
            $licence = Licence::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            /*
             * 422 VA BENE PER I FORM
             * return response()->json(['errors' => ['des_amm' => 'Ente non trovato'],], 422);
             */
            return response('Licenza non trovato', 404);
        }
        
        return modelActivities($licence);    
    }
}
