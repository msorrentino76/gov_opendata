<?php

namespace App\Http\Controllers\api\v1\sysAdmin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use App\Models\Codelist;
use App\Models\Code;
use App\Models\Dataflow;
use App\Models\Categories;

use Carbon\Carbon;

class MainteinanceController extends Controller
{

    private $status;
    
    public function codelist() {
        
        set_time_limit(0);

        // curl -kL "http://sdmx.istat.it/SDMXWS/rest/codelist" >./codelist.json
        
        $path_file = 'manteinance/codelist/';
        $name_file = 'codelist.xml';
        
        if (Storage::disk('local')->exists($path_file . $name_file)) {
            $this->_l("File $name_file esistente");
        } else {
            $this->_l("File $name_file non presente: avvio il download...");
            $contents = Http::timeout(0)->get('http://sdmx.istat.it/SDMXWS/rest/codelist');
            Storage::disk('local')->put($path_file . $name_file, $contents);
            $this->_l('Download completato');
        }
        
        $this->_l('Inizio elaborazione...');
        
        $xml = simplexml_load_string(Storage::disk('local')->get($path_file . $name_file));
        
        if ($xml === false) {
            foreach (libxml_get_errors() as $error) {
                $this->_l("Errore: " . $error->message);
            }
            return response()->json($this->status, 200);
        }
        
        foreach ($xml->xpath('//structure:Codelist') as $codelist) {
            
            $id_codelist = (string)$codelist['id'];           
            $urn         = (string)$codelist['urn'];
            $version     = (string)$codelist['version'];
            $name        = isset($codelist->xpath('.//common:Name[@xml:lang="it"]')[0]) ? (string)($codelist->xpath('.//common:Name[@xml:lang="it"]')[0]) : $id_codelist;

            $model_codelist = Codelist::where('codelist', $id_codelist)->first();
            
            if(is_null($model_codelist)){
                // inserisci
                $model_codelist = Codelist::create([
                    'codelist' => $id_codelist,
                    'urn'      => $urn,
                    'version'  => $version,
                    'name'     => $name
                ]);
                $this->_l("Inserito codelist {$model_codelist->codelist} - id: {$model_codelist->id}");
            } else {
                // aggiorna
                $model_codelist->urn     = $urn;
                $model_codelist->version = $version;
                $model_codelist->name    = $name;
                $model_codelist->save();
                if($model_codelist->wasChanged()){
                    $this->_l("Aggiornato codelist {$model_codelist->codelist} - id: {$model_codelist->id}");
                }
            }
            
            foreach ($codelist->xpath('.//structure:Code') as $code) {
                
                $id_code = (string)$code['id'];
                $name    = isset($code->xpath('.//common:Name[@xml:lang="it"]')[0]) ? (string)($code->xpath('.//common:Name[@xml:lang="it"]')[0]) : $id_code;
                
                $model_code = Code::where('code', $id_code)->where('codelist', $id_codelist)->first();
            
                if(is_null($model_code)){
                    // inserisci
                    $model_code = Code::create([
                        'codelist_id' => $model_codelist->id,
                        'codelist'    => $model_codelist->codelist,
                        'code'        => $id_code,
                        'name'        => $name
                    ]);
                    $this->_l("   ->   Inserito code {$model_code->code} - id: {$model_code->id}");
                } else {
                    // aggiorna
                    $model_code->codelist_id = $model_codelist->id;
                    $model_code->name        = $name;
                    $model_code->save();
                    if($model_code->wasChanged()){
                        $this->_l("   ->   Aggiornato code {$model_code->code} - id: {$model_code->id}");
                    }
                }
            }
    
        }
        
        $this->_l('Fine elaborazione...');
        
        // se l'elaborazione va a buon fine rinomino e archivio il file:
        $p = explode('.', $name_file);
        Storage::move($path_file . $name_file, $path_file . $p[0] . '_' . Carbon::now()->format('Ymd') . '.' . $p[1]);
        
        $this->_l("File $name_file archiviato");
        
        return response()->json($this->status, 200); 
    }

    public function dataflow() {
        
        set_time_limit(0);
        
        $path_file = 'manteinance/dataflow/';
        $name_file = 'dataflow.json';
        
        if (Storage::disk('local')->exists($path_file . $name_file)) {
            $this->_l("File $name_file esistente");
        } else {
            $this->_l("File $name_file non presente: avvio il download...");
            $contents = Http::withHeaders([
                            'Accept'          => 'application/json',
                            'Accept-Language' => 'it'
                        ])->timeout(0)->get('https://sdmx.istat.it/SDMXWS/rest/dataflow/IT1');
            $i = strpos($contents, '{');
            $contents = substr($contents, $i);
            Storage::disk('local')->put($path_file . $name_file, $contents);
            $this->_l('Download completato');
        }
        
        $this->_l('Inizio elaborazione...');
        
        $json = Storage::disk('local')->get($path_file . $name_file);
        $array = json_decode($json);

        $dataflow = [];
        
        foreach($array->references as $reference){
            $id            = $reference->id;
            $name          = ucfirst($reference->name);
            $version       = $reference->version;
            $is_final      = $reference->isFinal;
            $structure_urn = $reference->structure->urn;
            $data_struct   = $this->removeTextBetweenParentheses(explode(':', $structure_urn)[3]);
            $category      = explode('_', $reference->id)[0];
            
            $df = Dataflow::where('flow_ref', $id)->first();
            
            if(is_null($df)){
                
                // inserisci
                $df = Dataflow::create([
                    'flow_ref'    => $id,
                    'category'    => $category,
                    'data_struct' => $data_struct,
                    'is_final'    => $is_final,
                    'name'        => $name,
                    'version'     => $version, 
                ]);
                
                $this->_l("Inserito dataflow {$df->codelist} - id: {$df->id}");
                
            } else {
                
                // aggiorna
                $df->category    = $category;
                $df->data_struct = $data_struct;
                $df->is_final    = $is_final;
                $df->name        = $name;
                $df->version     = $version; 
                $df->save();
                
                if($df->wasChanged()){
                    $this->_l("Aggiornato dataflow {$df->codelist} - id: {$df->id}");
                }
            }

        }
        
        $this->_l('Fine elaborazione...');
        
        // se l'elaborazione va a buon fine rinomino e archivio il file:
        $p = explode('.', $name_file);
        Storage::move($path_file . $name_file, $path_file . $p[0] . '_' . Carbon::now()->format('Ymd') . '.' . $p[1]);
        
        $this->_l("File $name_file archiviato");
        
        return response()->json($this->status, 200); 
    }
    
    public function categories() {
        
        set_time_limit(0);

        $path_file = Categories::PATH_FILE;
        $name_file = Categories::NAME_FILE;
        
        if (Storage::disk('local')->exists($path_file . $name_file)) {
            $p = explode('.', $name_file);
            Storage::move($path_file . $name_file, $path_file . $p[0] . '_' . Carbon::now()->format('Ymd') . '.' . $p[1]);
            $this->_l("File precedente $name_file archiviato");
        }
        
        $this->_l("File $name_file: avvio il download...");
        $contents = Http::withHeaders([
                        'Accept'          => 'application/json',
                        'Accept-Language' => 'it'
                    ])->timeout(0)->get(Categories::DOWNLOAD_URL);
        $i = strpos($contents, '{');
        $contents = substr($contents, $i);
        Storage::disk('local')->put($path_file . $name_file, $contents);
        $this->_l('Download completato');
                       
        return response()->json($this->status, 200); 
        
    }
    
    public function dataflowToAvailable() {
        return response()->json(Dataflow::orderBy('id')->get(), 200); 
    }
    
    private function _l($msg) {
        $this->status[] = ['timestamp' => Carbon::now(), 'message' => $msg ];
    }
    
    private function removeTextBetweenParentheses($string) {
        return preg_replace('/\s*\(.*?\)\s*/', '', $string);
    }
    
}
