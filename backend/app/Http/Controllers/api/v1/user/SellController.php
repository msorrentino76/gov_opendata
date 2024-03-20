<?php

namespace App\Http\Controllers\api\v1\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Sell;
use App\Models\Document;

use Carbon\Carbon;

use App\Notifications\ActionNotification;

class SellController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $from = explode('-', $request->from);
        $to   = explode('-', $request->to);
        
        $annoInizio = isset($from[0]) && isset($from[1]) ? $from[0] : '1900';
        $meseInizio = isset($from[0]) && isset($from[1]) ? $from[1] : '01';
        $annoFine   = isset($to[0])   && isset($to[1])   ? $to[0]   : '2100';
        $meseFine   = isset($to[0])   && isset($to[1])   ? $to[1]   : '12';
        
        $data_from = Carbon::createFromDate($annoInizio, $meseInizio)->firstOfMonth()->toDateString();
        $data_to   = Carbon::createFromDate($annoFine  , $meseFine)->lastOfMonth()->toDateString();
        
        $prepare_resp = [];
        
        foreach (User::all() as $u){
            
            //$sells = array();
            
            $prepare_resp[] = [
                'id'      => $u->id,
                'subject' => $u->name . ' ' . $u->surname,
                'sells'   => $u->sells()->with('documents')->whereBetween('sells.data', [$data_from, $data_to])->orderBy('sells.id')->get(),
                //'sells'   => $u->with('sells.documents')->/*whereBetween('sells.data', [$data_from, $data_to])->orderBy('sells.id')->*/get(),
                ];
        }
        
        return response($prepare_resp, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'data'        => 'required|date|before_or_equal:today',
            'importo'     => 'required|decimal:2',
            'descrizione' => 'required|string|max:512',
        ]);
        
        $user = Auth::user();
        
        $data = $request->only('data', 'importo', 'descrizione', 'allegati');
        
        if( !isset($data['allegati']) || count($data['allegati']) == 0 ){
            return response()->json([
                'errors' => ['allegati' => 'E\' necessario caricare almeno un allegato'],
            ], 422); 
        }
        
        $data['user_id'] = $user->id;
        
        $sell = Sell::create($data);
        
        /*
         *  COLLEGO GLI ALLEGATI
         */
        foreach($data['allegati'] as $allegato){
            $documento = Document::where(['id' => $allegato['response']['id'], 'user_id' => $user->id])->first();
            if(!is_null($documento)) {
                $documento->toMorph($sell);
            }
        }
        
        /*
         * INVIO NOTIFICHE
         */
        try{
            foreach(User::all() as $u){
                if($u->id != $user->id){
                    $u->notify(new ActionNotification($user, $u, ActionNotification::VENDITA, $sell->id)   
                     );
                }
            }
        } catch (Exception $ex) {
            // do nothing
        }
        
        return Sell::with('documents')->where(['sells.id' => $sell->id, 'sells.user_id' => $user->id])->first();

    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
