<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Models\Codelist;
use App\Models\Code;

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

    public function datafilter(Request $request){
        
        $data = $request->only('id_datastructure', 'flow_ref'); 
        
        // con id_datastructure interrogo datastructure
        
        $url = 'https://sdmx.istat.it/SDMXWS/rest/datastructure/IT1/' . $data['id_datastructure'];
        
        $resp = $this->_http($url, false);
        
        if($resp['error']){
            Log::error("IstatController:index - $url - " . $resp['httpCode'] . ' ' . $resp['errorMessage'] );
            return response()->json('Errore durante l\'interrogazione ISTAT', 500);
        }
        
        $xml = simplexml_load_string($resp['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        
        $data_struct = [];
        
        foreach ($xml->xpath('//structure:Dimension') as $dimension) {
            $position = (string)$dimension['position'];
            $ds_key = (string)$dimension['id'];
            $codelist = $dimension->xpath('.//structure:Enumeration/Ref[@class="Codelist"]');
            $refId = false;
            if ($codelist) {
                $refId = (string) $codelist[0]['id'];  // Recupera l'ID del Ref
            } 
            // LEGO DS_KEY (chiave del datastructure) con CODELIST
            $data_struct[$ds_key] = [
                'position' => $position,
                'codelist' => $refId,
            ];
        }
        
        // VEDIAMO CHE VALORI CI SONO DISPONIBILI: con flow_ref interrogo availableconstraint
        
        $url = 'https://sdmx.istat.it/SDMXWS/rest/availableconstraint/' . $data['flow_ref'];
        
        $resp = $this->_http($url, false);
        
        if($resp['error']){
            Log::error("IstatController:index - $url - " . $resp['httpCode'] . ' ' . $resp['errorMessage'] );
            return response()->json('Errore durante l\'interrogazione ISTAT', 500);
        }
        
        $xml = simplexml_load_string($resp['content'], 'SimpleXMLElement', LIBXML_NOCDATA);
        
        $availables = [];
        
        foreach ($xml->xpath('//common:KeyValue') as $key) {
            $ds_key = (string) $key['id'];
            $value  = [];
            foreach ($key->xpath('.//common:Value') as $v) {
                $value[] = (string)$v[0];
            }
            $availables[$ds_key] = $value;
        }
        
        // A QUESTO PUNTO MI TROVO:
        // $data_struct ds_key -> codelist
        // $availables  ds_key -> ARRAY DI OPTIONS
        
        // LEGO LE DUE STRUTTURE TRAMITE I MODEL CODELIST E CODE PER LE TRADUZIONI E FORMATTO LE OPTIONS:
        
        $filters_json = [];
        
        foreach($data_struct as $ds_key => $ds){
            
            $availableForCurrentLe = false;
            
            // CHECK TERRITORIO: E SE CI SONO AREE NON IDENTICHE AL COMUNE?
            /*
            if($ds_key == 'ITTER107'){
                
                // cerco il codice istat dell'Ente licenziato tra quelli disponibili:
                $array_codice_istat_available = $availables['ITTER107'];
                Auth::user()->notExpiredLicenceFor();
                if(!in_array($array_codice_istat_available)){
                    
                }
                
            }
            */
                
            $codelist = $ds['codelist'];

            $options = [];
            
            foreach ($availables[$ds_key] as $key_option){
                $options[] = [
                    'value' => $key_option,
                    'label' => Code::where('code', $key_option)->where('codelist', $codelist)->first()->name,
                ];
            }
            
            usort($options, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
        
            /*
             * NON HA SENSO FILTRARE PER UN CAMPO CHE HA SOLO UNA OPTION
             */
            if(count($options) > 1) {
                $filters_json[] = [
                    'name'  => 'posix_' . $ds['position'],
                    'label' => Codelist::where('codelist', $codelist)->first()->name,
                    //'dsKey' => $ds_key,
                    //'codeList' => $codelist,
                    'type'  => 'select',
                    'options' => $options,
                ];
            }
        }
        
        //file_put_contents('output.txt', $resp['content']);
        
        return response()->json([
            'nPos'                  => count($data_struct),
            'filtersJson'           => $filters_json,
            'availableForCurrentLe' => $availableForCurrentLe,
            ], 200);
    }
    
    public function dataquery(Request $request){
        
        $data = $request->only('nPos', 'flow_ref', 'selectedfilter');
        
        return response()->json(['loopback' => true, 'submit' => $data], 200);
        
    }
    
    private function _http($url, $json = true) {
        
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
        
        $headers = ['Accept-Language: it'];        
        if($json) {
            // Imposta l'header per accettare il formato JSON
            $headers[] = 'Accept: application/json';
        }
        curl_setopt($curlSES, CURLOPT_HTTPHEADER, $headers);
        
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
