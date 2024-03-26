<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\Quote;
use App\Models\QuoteDetail;

use Carbon\Carbon;

class QuoteController extends Controller
{
    public function period()
    {
        $periodo_da = Quote::max('periodo_a'); // Il nuovo periodo comincia dove finisce l'ultimo
        $periodo_da = !is_null($periodo_da) ? $periodo_da : '2024-01-01 00:00:00';
        $periodo_da = \DateTime::createFromFormat('Y-m-d h:s:i', $periodo_da)->format('Y-m');
        
        $periodo_a = \DateTime::createFromFormat('Y-m', $periodo_da)->modify('+6 months')->format('Y-m');

        return response([$periodo_da, $periodo_a], 200);
    }
    
    /**
     * Display a listing of the resource.
     */
    public function new(Request $request)
    {
        $from = explode('-', $request->from);
        $to   = explode('-', $request->to);
        
        $annoInizio = isset($from[0]) && isset($from[1]) ? $from[0] : '1900';
        $meseInizio = isset($from[0]) && isset($from[1]) ? $from[1] : '01';
        $annoFine   = isset($to[0])   && isset($to[1])   ? $to[0]   : '2100';
        $meseFine   = isset($to[0])   && isset($to[1])   ? $to[1]   : '12';
        
        $data_from = Carbon::createFromDate($annoInizio, $meseInizio)->firstOfMonth()->toDateString();
        $data_to   = Carbon::createFromDate($annoFine  , $meseFine)->lastOfMonth()->toDateString();
        
        $users        = [];        
        $hours_tot    = 0;
        $amount_tot   = 0;
        $prepare_resp = [];
        
        foreach (User::all() as $u){
            $hours_per_user  = (int)$u->acts()->whereBetween('data', [$data_from, $data_to])->sum('ore');
            $amount_per_user = (float)$u->sells()->whereBetween('data', [$data_from, $data_to])->sum('importo');
            $users[] = [
                'id'       => $u->id,
                'role'     => $u->numieRole(),
                'subject'  => $u->name . ' ' . $u->surname,
                'hours'    => $hours_per_user,
                'amount'   => $amount_per_user,
                'activities' => $u->acts()->whereBetween('data', [$data_from, $data_to])->orderBy('id')->get(),
            ];
            $hours_tot  += $hours_per_user;
            $amount_tot += $amount_per_user;
        }
        
        foreach ($users as &$u){
            if($u['role']    == 'developer'){
                $u['amount'] = $amount_tot;
            }
        }
        
        $prepare_resp['users']  = $users;
        $prepare_resp['totals'] = ['hours' => $hours_tot, 'amount' => $amount_tot];
        
        return response($prepare_resp, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->all();
        return;
    }

    /**
     * Display the specified resource.
     */
    public function historical(string $id)
    {
        //
    }

}
