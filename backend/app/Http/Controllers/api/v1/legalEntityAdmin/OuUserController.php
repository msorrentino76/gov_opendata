<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;

use App\Exceptions\ResponseException;

use App\Models\User;
use App\Models\OrganizativeUnit;
use App\Models\OuUser;

class OuUserController extends Controller
{

    private $user    = null;
    private $licence = null;
    private $ente    = null;
    
    public function index() {
        
        $this->setLicence();
        
        $results = [];
        foreach ($this->ente->organizativeUnits as $ou) {
            $results[] = ['ou_id' => $ou->id, 'userids' => $ou->userids()];
        }
        
        return response($results, 200);
    }
    
    /*
    public function create(Request $request){
        
    }
    
    public function read(string $id){
        
    }
    */
    
    public function update(Request $request, string $id){

        $this->setLicence();
        
        // E' dell'utente loggato questa OU
        if(!$this->ente->organizativeUnits->contains('id', $id)){
            throw new ResponseException(response('OU non trovata o non in carico all\Ente per il quale la licenza è attiva', 404));
        }
        
        // Sono dell'utente loggato questi Utenti?
        $new_users = $request->only('users');
        $new_users = $new_users['users'];

        $all_users = User::where(['created_by' => Auth::user()->id])->orWhere(['id' => Auth::user()->id])->select('id')->get();
        
        $allowed_user_ids = [];
        foreach($all_users as $au){
            $allowed_user_ids[] = $au->id;
        }
       
        $new_users      = array_intersect($allowed_user_ids, $new_users);
        $previuos_users = OrganizativeUnit::find($id)->userids();
        
        // Trova gli ID da rimuovere
        $user_ids_to_remove = array_diff($previuos_users, $new_users);

        // Trova gli ID da aggiungere
        $user_ids_to_add    = array_diff($new_users, $previuos_users);

        try{
            
            DB::beginTransaction();
            
            foreach ($user_ids_to_remove as $idx){
                $ouu = OuUser::where(
                        ['organizative_unit_id' => $id,
                         'user_id' => $idx]
                        )->first();
                $ouu->delete();
            }
            
            foreach ($user_ids_to_add as $idx){
                OuUser::create(
                        ['organizative_unit_id' => $id,
                         'user_id' => $idx]
                        );
            }
            
            DB::commit();
            
        } catch (\Exception $ex) {
            DB::rollBack();
            Log::error('User: ' . Auth::user()->id . ' - Exception message: ' . $ex->getMessage());
            return response(['message' => 'Errore durante l\'associazione dele utenze', 'error' => $ex->getMessage()], 500);
        }
        
        return response('ok', 200);
        
    }
    
    /*
    public function destroy(string $id) {
        
    }
    */
    
    public function ouuserActivities($id) {
        
        $this->setLicence();
        
        // E' dell'utente loggato questa OU
        if(!$this->ente->organizativeUnits->contains('id', $id)){
            throw new ResponseException(response('OU non trovata o non in carico all\Ente per il quale la licenza è attiva', 404));
        }

        $ou = OrganizativeUnit::find($id);
        
        return modelActivities($ou);
        
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
