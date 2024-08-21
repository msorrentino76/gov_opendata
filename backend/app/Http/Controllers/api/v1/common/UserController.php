<?php

namespace App\Http\Controllers\api\v1\common;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Exceptions\ResponseException;

use Carbon\Carbon;

use App\Notifications\Registration;
use App\Notifications\ResetPassword;

use Illuminate\Validation\Rule;
use App\Rules\NoSpaces;

class UserController extends Controller
{
    
    private $len_pwd = 8;
    
    public function index(){
        return response(User::where(['created_by' => Auth::user()->id])->orderBy('surname')->get(), 200);
    }
    
    public function create(Request $request){
        
        $request->validate([
            'name'     => 'required',
            'surname'  => 'required',
            'username' => ['required', 'unique:users', new NoSpaces],
            'email'    => 'required|unique:users|email',
        ]);
        
        $data = $request->only('name', 'surname', 'username', 'email');
        
        $user = null;
                
        try {
            
            $password         = bin2hex(random_bytes($this->len_pwd / 2));        
            $data['password'] = bcrypt($password);
            
            // system:admin       -> CREA legal_entity:admin
            // legal_entity:admin -> CREA ou:user
            if(Auth::user()->tokenCan('system:admin')) {
                $data['abilities'] = json_encode(["legal_entity:admin"]);
            }
            
            if(Auth::user()->tokenCan('legal_entity:admin')) {
                $data['abilities'] = json_encode(["ou:user"]);
            }
            
            $data['created_by'] = Auth::user()->id;
            $data['enabled']    = true;
            
            DB::beginTransaction();
            
            $user = User::create($data);            
            $user->notifY(new Registration((string)$user, $data['username'], $password));
            
            DB::commit();
            return $user;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User: ' . Auth::user()->id . ' - Exception message: ' . $e->getMessage());
            return response(['message' => 'Errore durante la creazione dell\'utente', 'error' => $e->getMessage()], 500);
        }
        
    }
    
    public function read(string $id){
        return $this->getResourceIfOwner($id);
    }
    
    public function update(Request $request, string $id){
        
        $user = $this->getResourceIfOwner($id);
        
        $request->validate([
            'name'     => 'required',
            'surname'  => 'required',
            'username' => ['required', Rule::unique('users')->ignore($user->id), new NoSpaces],
            'email'    => ['required', Rule::unique('users')->ignore($user->id), 'email'],
        ]);
                
        $data = $request->only('name', 'surname', 'username', 'email');
        
        $user->update($data);
        return $user;
    }
    
    public function destroy(string $id) {
        $user = $this->getResourceIfOwner($id);
        $user->delete();        
        return response(['id' => $id], 200);
    }
    
    public function resetPwd(Request $request, string $id) {
        $user = $this->getResourceIfOwner($id);
        $new_pwd = bin2hex(random_bytes($this->len_pwd / 2)); 
        try {
            DB::beginTransaction();
            $user->password = bcrypt($new_pwd);
            $user->update();
            $user->notifY(new ResetPassword((string)$user, $user->username, $new_pwd));
            DB::commit();
            return $user;
        } catch (Exception $ex) {
            DB::rollBack();
            Log::error('User: ' . Auth::user()->id . ' - Exception message: ' . $e->getMessage());
            return response(['message' => 'Errore durante la creazione dell\'utente', 'error' => $e->getMessage()], 500);
        }         
    }
    
    public function toggle(Request $request, string $id){        
        $user = $this->getResourceIfOwner($id);        
        $user->enabled = !$user->enabled;        
        $user->save();        
        return response($user, 200);        
    }
    
    public function userActivities(string $id) {
        
        $user = $this->getResourceIfOwner($id);
        $hist = $user->histories()->orderBy('performed_at', 'asc')->get();
        
        $activities = [];
        
        foreach ($hist as $h){
            $owner = (string)User::find($h->user_id);
            $timestamp = Carbon::parse($h->performed_at)->locale('it')->isoFormat('D/MM/YYYY HH:mm');
            if($h->message == 'CREATE') {
                $activities[] = ['content' => 'Creato da ' . $owner, 'timestamp' => $timestamp, 'type' => 'success'];
            }
            if($h->message == 'UPDATE') {
                
                $data_update = false;
                
                foreach($h->meta as $row){                    
                    if($row['key'] == 'enabled'){
                        $activities[] = ['content' => ($row['new'] ? 'Riabilitato da ' : 'Disabilitato da ') . $owner, 'timestamp' => $timestamp, 'type' => $row['new'] ? 'warning' : ''];
                    } elseif($row['key'] == 'password'){
                        $activities[] = ['content' => 'Password resettata da ' . $owner, 'timestamp' => $timestamp, 'type' => 'warning'];
                    }else {
                        $data_update = true;                        
                    }                    
                } 
                
                if($data_update){
                    $extra_content = [];
                    foreach($h->meta as $row){
                        $key = __('validation.attributes.' . $row['key']);
                        $extra_content[] = "$key da: '{$row['old']}' a '{$row['new']}'";
                    }
                    $activities[] = ['content' => 'Modificato da ' . $owner . ': ' . implode(' - ', $extra_content), 'timestamp' => $timestamp, 'type' => 'primary'];
                }
            }
        }
        
        return $activities;
        
        /*
        return [
            ['content' => 'sdas'  , 'timestamp' => '2023-01-12', 'type' => 'success'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'primary'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'warning'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'info'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13', 'type' => 'danger'],
            ['content' => 'sddsas', 'timestamp' => '2023-01-13'],
        ];
         * 
         */
        
    }
    
    private function getResourceIfOwner($id) {
        
        $user = User::where(
                [
                    'id'         => $id,
                    'created_by' => Auth::user()->id
                ])->first();
        
        if(is_null($user)){
            throw new ResponseException(response('Utente non trovato', 404));
        }
        
        return $user;
        
    } 
    
}
