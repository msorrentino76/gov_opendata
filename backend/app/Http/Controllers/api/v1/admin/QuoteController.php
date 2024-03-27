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
        $periodo_da = !is_null($periodo_da) ? $periodo_da : '2023-12-01 00:00:00';
        $periodo_da = \DateTime::createFromFormat('Y-m-d h:s:i', $periodo_da)->modify('+1 months')->format('Y-m');
        
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

        $from = explode('-', $data['quote']['data_value'][0]);
        $to   = explode('-', $data['quote']['data_value'][1]);
        
        $data_from = Carbon::createFromDate($from[0], $from[1])->firstOfMonth()->toDateString();
        $data_to   = Carbon::createFromDate(  $to[0],   $to[1])->lastOfMonth()->toDateString();
        
        $quote = Quote::create([
            'periodo_da' => $data_from,
            'periodo_a'  => $data_to,
            'importo_totale'            => $data['quote']['totals']['amount'],
            'dividendo_attivita_totale' => $data['quote']['totals']['amount_acts'],
            'dividendo_vendita_totale'  => $data['quote']['totals']['amount_sell'],
            'importo_residuo_cassa'     => $data['quote']['totals']['amount_cash'],
        ]);
        
        foreach ($data['details'] as $detail) {
            QuoteDetail::create([
                'quote_id' => $quote->id,
                'user_id'  => $detail['id'],
                'percentuale_vendita'  => $detail['sell_perc'] ,
                'dividendo_vendita'    => $detail['sell_amount']  ,
                'percentuale_attivita' => $detail['acts_perc'],
                'dividendo_attivita'   => $detail['acts_amount'] ,
            ]);
        }

        return response(['id' => $quote->id], 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function historical()
    {
        return Quote::with('details')->with('details.user')->get();
    }
 
}
