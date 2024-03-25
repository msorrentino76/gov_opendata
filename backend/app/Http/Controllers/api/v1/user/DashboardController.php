<?php

namespace App\Http\Controllers\api\v1\user;

use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Act;
use App\Models\Sell;
use App\Models\Document;

class DashboardController extends Controller
{
    public function index() {
             
        $act_total_count = (int)Act::count();
        $act_total_hours = (int)Act::sum('ore');

        $sells_total_count  = (int)Sell::count();
        $sells_total_amount = (float)Sell::sum('importo');
        
        $acts['total'] = [
            'count' => $act_total_count,
            'hours' => $act_total_hours,
        ];
        
        $sells['total'] = [
            'count'  => $sells_total_count,
            'amount' => $sells_total_amount,
        ];
        
        $last_stats = [];
        
        $acts['users']  = array();
        
        $sells['users'] = array();
        
        foreach (User::all() as $u){
                       
            $last_stats[] = [
                'id'      => $u->id,
                'subject' => $u->name . ' ' . $u->surname,
                'last_logins' => $u->storico()->limit(3)->offset(1)->orderBy('data_ora', 'desc')->get(),
                'last_acts'   => $u->acts()->limit(3)->orderBy('data', 'desc')->get(),
                'last_sells'  => $u->sells()->limit(3)->orderBy('data', 'desc')->get(),
            ];
            
            $acts_users_stats_count = (int)Act::where(['user_id' => $u->id])->count();
            $acts_users_stats_hours = (int)Act::where(['user_id' => $u->id])->sum('ore');
            
            $acts['users'][] = [
                'id'      => $u->id,
                'subject' => $u->name . ' ' . $u->surname,
                'stats' => [
                    'count' => $acts_users_stats_count,
                    'hours' => $acts_users_stats_hours,
                    'count_perc' => $act_total_count != 0 ? round(($acts_users_stats_count / $act_total_count) * 100, 2) : '-',
                    'hours_perc' => $act_total_hours != 0 ? round(($acts_users_stats_hours / $act_total_hours) * 100, 2) : '-',
                ],
            ];
            
            $sells_users_stats_count  = (int)Sell::where(['user_id' => $u->id])->count();
            $sells_users_stats_amount = (float)Sell::where(['user_id' => $u->id])->sum('importo');
            
            if($u->numieRole() == 'seller') {
                
                $sells['users'][] = [
                    'id'      => $u->id,
                    'subject' => $u->name . ' ' . $u->surname,
                    'stats' => [
                        'count'  => $sells_users_stats_count,
                        'amount' => $sells_users_stats_amount,
                        'count_perc'  => $sells_total_count  != 0 ? round(($sells_users_stats_count  / $sells_total_count)  * 100, 2) : '-',
                        'amount_perc' => $sells_total_amount != 0 ? round(($sells_users_stats_amount / $sells_total_amount) * 100, 2) : '-'
                    ],  
                ]; 
            
            }
            
        }
        
        $disk_usage['orphan'] = Document::whereNull('documentable_id')->sum('size');
        $disk_usage['usage']  = Document::whereNotNull('documentable_id')->sum('size');
        $disk_usage['total']  = Document::sum('size'); //$disk_usage['orphan'] + $disk_usage['usage'];
        
        // Spazio occupato (totale di cui file orfani...)
        
        return response(
            [
                'last_stats' => $last_stats,
                'acts'       => $acts,
                'sells'      => $sells,
                'disk_usage' => $disk_usage,
            ], 200);
        
    }
}
