<?php

namespace App\Http\Controllers\api\v1\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class LogsController extends Controller
{

    public function index(){
        $logs = File::files(storage_path('logs'));
        $resp = [];
        foreach($logs as $log){
            $resp[] = ['filename' =>$log->getFilename()];
        }
        return response()->json($resp); 
    }

    public function content(Request $request, $filename){
        $path_file =  storage_path('logs') . '\\' . $filename;
        return response()->json([ ['content' => nl2br(file_get_contents($path_file))]]);
    }
    
    public function delete(Request $request, $filename){
        $path_file =  storage_path('logs') . '\\' . $filename;
        unlink($path_file);
        return response(null, 200);
    }
    
}
