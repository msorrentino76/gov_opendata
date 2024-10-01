<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class IstatController extends Controller
{

    public function index(){
        
        $resp = $this->_http('https://sdmx.istat.it/SDMXWS/rest/dataflow/IT1');
        
        if($resp['error']){
            Log::error("IstatController:index - " . $resp['httpCode'] . ' ' . $resp['errorMessage'] );
            return response()->json('Errore durante l\'interrogazione ISTAT', 500);
        }
        
        $i = strpos($resp['content'], '{');
        
        $array = json_decode(substr($resp['content'], $i));

        $dataset = [];
        
        foreach($array->references as $reference){
            $id            = $reference->id;
            $name          = $reference->name;
            $structure_urn = $reference->structure->urn;
            $dataset[] = ['id' => $id, 'name' => $name];
        }
        
        //file_put_contents('output.txt', $resp['content']);
        
        /**
         * @todo
         * 1- questo processo andrà ad alimentare il DB dataset STORICIZZATO
         * 2- questa action leggerà dal DB e chiamata al login alimentando uno STUB (se lo stub è avuto)
         * 3- il processo va avviato a mano da un Admin/BOT-Cronjobs
         * 4- l'admin avrà contezza dell'esito dei processi (se e cosa andrà aggiornato)
         * 5- in caso di aggiornamenti/creazione partono notifiche
         */
        return response()->json($dataset, 200); 
    }

    private function _http($url) {
        $resp = [
            'error'        => false,
            'errorMessage' => '',
            'httpCode'     => 200,
            'content'      => ''
        ];
        $curlSES=curl_init(); 
        curl_setopt($curlSES, CURLOPT_URL, $url);
        curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSES, CURLOPT_HEADER, false);
        // Imposta l'header per accettare il formato JSON
        curl_setopt($curlSES, CURLOPT_HTTPHEADER, array('Accept: application/json','Accept-Language: it'));
        curl_setopt($curlSES, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36");
        $resp['content']  = curl_exec($curlSES);
        $resp['httpCode'] = curl_getinfo($curlSES, CURLINFO_HTTP_CODE);
        if (curl_errno($curlSES)) {
            $resp['error']        = true;
            $resp['errorMessage'] = curl_error($curlSES);
        }
        curl_close($curlSES);
        return $resp;
    }
        
}
