<?php

namespace App\Http\Controllers\api\v1\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Act;

use Carbon\Carbon;

class ActController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $from = explode('-', $request->from);
        $to   = explode('-', $request->to);
        
        $annoInizio = isset($from[0]) ? $from[0] : '1900';
        $meseInizio = isset($from[1]) ? $from[1] : '01';
        $annoFine   = isset($to[0])   ? $to[0]   : '2100';
        $meseFine   = isset($to[1])   ? $to[1]   : '12';
        
        $data_from = Carbon::createFromDate($annoInizio, $meseInizio)->firstOfMonth()->toDateString();
        $data_to   = Carbon::createFromDate($annoFine  , $meseFine)->lastOfMonth()->toDateString();
        
        $prepare_resp = [];
        
        foreach (User::all() as $u){
            $prepare_resp[] = [
                'id'       => $u->id,
                'subject'  => $u->name . ' ' . $u->surname,
                'activities' => $u->acts()->whereBetween('data', [$data_from, $data_to])->orderBy('data')->get(),
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
            'data' => 'required|date|before_or_equal:today',
            'ore' => 'required|integer|min:1|max:24',
            'descrizione' => 'required|string|max:512',
        ]);
        
        $data = $request->only('data', 'ore', 'descrizione');
        $data['user_id'] = Auth::user()->id;
        return Act::create($data);
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
