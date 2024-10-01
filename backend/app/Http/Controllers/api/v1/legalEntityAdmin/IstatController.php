<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class IstatController extends Controller
{

    public function index(){
        
        $url = 'https://sdmx.istat.it/SDMXWS/rest/dataflow/IT1';
        
        $resp = $this->_http($url);
        
        if($resp['error']){
            Log::error("IstatController:index - $url - " . $resp['httpCode'] . ' ' . $resp['errorMessage'] );
            return response()->json('Errore durante l\'interrogazione ISTAT', 500);
        }
        
        $i = strpos($resp['content'], '{');
        
        $array = json_decode(substr($resp['content'], $i));

        $dataflow = [];
        
        foreach($array->references as $reference){
            $id            = $reference->id;
            $name          = $reference->name;
            $version       = $reference->version;
            $is_final      = $reference->isFinal;
            $structure_urn = $reference->structure->urn;
            $data_struct   = $this->removeTextBetweenParentheses(explode(':', $structure_urn)[3]);
            $category      = explode('_', $reference->id)[0];
            $dataflow[] = ['id' => $id, 'category' => $category, 'data_struct' => $data_struct, 'is_final' => $is_final, 'name' => $name . " (ver. $version)"];
        }
        
        //file_put_contents('output.txt', $resp['content']);
        
        $url = 'https://sdmx.istat.it/SDMXWS/rest/categoryscheme/IT1';
        
        $resp = $this->_http($url);
        
        if($resp['error']){
            Log::error("IstatController:index - $url - " . $resp['httpCode'] . ' ' . $resp['errorMessage'] );
            return response()->json('Errore durante l\'interrogazione ISTAT', 500);
        }
        
        $i = strpos($resp['content'], '{');
        
        $array = json_decode(substr($resp['content'], $i));
        
        $category_schema = 'urn:sdmx:org.sdmx.infomodel.categoryscheme.CategoryScheme=IT1:ISTAT_DW(1.0)';
        
        $categories = $this->_recursive_category($array->references->{$category_schema}->items);

        /**
         * @todo
         * 1- questo processo andrà ad alimentare il DB dataset STORICIZZATO
         * 2- questa action leggerà dal DB e chiamata al login alimentando uno STUB (se lo stub è avuto)
         * 3- il processo va avviato a mano da un Admin/BOT-Cronjobs
         * 4- l'admin avrà contezza dell'esito dei processi (se e cosa andrà aggiornato)
         * 5- in caso di aggiornamenti/creazione partono notifiche
         */
        
        usort($categories, function($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return response()->json(['dataflow' => $dataflow, 'categories' => $categories], 200); 
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
        
    private function _recursive_category($items) {
        $cats = [];
        foreach ($items as $item) {            
            $res['value']   = $item->id;
            $res['label'] = $item->name;
            if(isset($item->items)){
                $res['children'] = $this->_recursive_category($item->items);
            }
            $cats[] = $res;
        }
        return $cats;
    }
    
    private function removeTextBetweenParentheses($string) {
        return preg_replace('/\s*\(.*?\)\s*/', '', $string);
    }
}
