<?php

namespace App\Http\Controllers\api\v1\sysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

use App\Models\StoricoLogin;

class StatisticheController extends Controller
{

    public function index(){
        $x = [];
        $y = [];
        $months = [
            1 => 'Gen',
            2 => 'Feb',
            3 => 'Mar',
            4 => 'Apr',
            5 => 'Mag',
            6 => 'Giu',
            7 => 'Lug',
            8 => 'Ago',
            9 => 'Set',
           10 => 'Ott',
           11 => 'Nov',
           12 => 'Dic', 
        ];
        $storico_login = StoricoLogin::selectRaw('YEAR(data_ora) as year, MONTH(data_ora) as month, COUNT(DISTINCT user_id) as total')
                ->groupBy('year' , 'month')
                ->orderBy('year' , 'asc')
                ->orderBy('month', 'asc')
                ->limit(12)
                ->get();
        foreach($storico_login as $s){
            $x[] = $months[$s->month]; // "{$s->month}/{$s->year}";
            $y[] = $s->total;
        }
        return response()->json(['xAxis' => $x, 'yAxis' => $y]); 
    }

    public function last() {
        //return StoricoLogin::orderBy('data_ora', 'desc')->limit(64)->get();
        return response()->json(StoricoLogin::orderBy('data_ora', 'desc')->limit(64)->get()); 
    }
}
