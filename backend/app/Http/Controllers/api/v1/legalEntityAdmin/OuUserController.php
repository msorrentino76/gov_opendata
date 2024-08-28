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
use App\Models\StoricoOuUser;

use App\Notifications\UserAddedRemovedFromOu;

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
        
        $auth_user     = Auth::user();
        $auth_user_id  = $auth_user->id;
        $auth_user_sub = (string)$auth_user;
        
        // E' dell'utente loggato questa OU
        if(!$this->ente->organizativeUnits->contains('id', $id)){
            throw new ResponseException(response('OU non trovata o non in carico all\Ente per il quale la licenza è attiva', 404));
        }
        
        // Sono dell'utente loggato questi Utenti?
        $new_users = $request->only('users');
        $new_users = $new_users['users'];

        $all_users = User::where(['created_by' => $auth_user_id])->orWhere(['id' => $auth_user_id])->select('id')->get();
        
        $allowed_user_ids = [];
        foreach($all_users as $au){
            $allowed_user_ids[] = $au->id;
        }
       
        $new_users      = array_intersect($allowed_user_ids, $new_users);
        $ou_work        = OrganizativeUnit::find($id);
        $previuos_users = $ou_work->userids();
        
        // Trova gli ID da rimuovere
        $user_ids_to_remove = array_diff($previuos_users, $new_users);

        // Trova gli ID da aggiungere
        $user_ids_to_add    = array_diff($new_users, $previuos_users);
        
        try{
            
            DB::beginTransaction();
            
            foreach ($user_ids_to_remove as $idx){
                
                $ouu = OuUser::where(['organizative_unit_id' => $id, 'user_id' => $idx])->first();
                $ouu->delete();
                
                User::find($idx)->notify(new UserAddedRemovedFromOu($auth_user_sub, 'rem', $ou_work->des_ou));
                
            }
            
            foreach ($user_ids_to_add as $idx){
                OuUser::create(['organizative_unit_id' => $id, 'user_id' => $idx]);
                User::find($idx)->notify(new UserAddedRemovedFromOu($auth_user_sub, 'add', $ou_work->des_ou));
            }
            
            // se invio tutti gli utenti $user_ids_to_remove o $user_ids_to_add gli array hanno chiave continua (0, 1, 2 ...)
            // e il json_encode torna [value1, value2, value3...]
            // viceversa { '1' => value1, '3' => 'value2' ... }
            // raggiun per cui aggiungo array_values
            
            if(count($user_ids_to_remove)){
                StoricoOuUser::create([
                    'user_id'              => $auth_user_id,
                    'organizative_unit_id' => $id,
                    'action'               => 'rem',
                    'users'                => json_encode(array_values($user_ids_to_remove)),
                    'performed_at'         => Carbon::now()->toDateTimeString(),
                ]);
            }
            
            if(count($user_ids_to_add)){
                StoricoOuUser::create([
                    'user_id'              => $auth_user_id,
                    'organizative_unit_id' => $id,
                    'action'               => 'add',
                    'users'                => json_encode(array_values($user_ids_to_add)),
                    'performed_at'         => Carbon::now()->toDateTimeString(),
                ]);
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
        
        $hist = $ou->storicoOuUser;
        
        $activities = [];
        
        foreach ($hist as $h){
            
            $owner = (string)User::find($h->user_id);
            $timestamp = Carbon::parse($h->performed_at)->locale('it')->isoFormat('D/MM/YYYY HH:mm');
            
            $content = $owner;
            $type    = '';
            
            if($h->action == 'add'){
                $content .= ' ha aggiunto i seguenti utenti: ';
                $type = 'success';
            }
            
            if($h->action == 'rem'){
                $content .= ' ha rimosso i seguenti utenti: ';
                $type = 'danger';
            }            
            
            $array_users = [];
            
            foreach (json_decode($h->users) as $u) {
                $array_users[] = (string)User::find($u);
            }
            
            $activities[] = ['content' => $content . implode(', ', $array_users), 'timestamp' => $timestamp, 'type' => $type];
        }
        
        return response($activities, 200);
        
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
