<?php

namespace App\Http\Controllers\api\v1\sysAdmin;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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

            $this->_l("$id_codelist $urn $version $name");
            
            foreach ($codelist->xpath('.//structure:Code') as $code) {
                
                $id_code = (string)$code['id'];
                $name    = isset($code->xpath('.//common:Name[@xml:lang="it"]')[0]) ? (string)($code->xpath('.//common:Name[@xml:lang="it"]')[0]) : $id_code;
                
                $this->_l(" ------------------- $id_code $name");
            }
    
        }
        
        $this->_l('Fine elaborazione...');
        
        // se l'elaborazione va a buon fine rinomino e archivio il file:
        $p = explode('.', $name_file);
        //Storage::move($path_file . $name_file, $path_file . $p[0] . '_' . Carbon::now()->format('Ymd') . '.' . $p[1]);
        
        $this->_l("File $name_file archiviato");
        
        return response()->json($this->status, 200); 
    }

    private function _l($msg) {
        $this->status[] = ['timestamp' => Carbon::now(), 'message' => $msg ];
    }
    
}
