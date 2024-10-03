<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Models\Codelist;
use App\Models\Code;
use App\Models\Dataflow;
use App\Models\Categories;

class IstatController extends Controller
{

    public function index(){
        return response()->json(['dataflow' => Dataflow::all(), 'categories' => Categories::getAll()], 200); 
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
            
            if(isset($availables[$ds_key])){
                foreach ($availables[$ds_key] as $key_option){
                    $options[] = [
                        'value' => $key_option,
                        'label' => Code::where('code', $key_option)->where('codelist', $codelist)->first()->name,
                    ];
                }
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
        
        // costruisco i filtri:
        $posix = $data['selectedfilter'];
        
        $filterArray = [];
        
        for($i = 1; $i <= $data['nPos']; $i++){
            if(isset($posix['posix_' . $i])){
                $filterArray[] = implode('+', $posix['posix_' . $i]);
            }else{
                //filtro vuoto
                $filterArray[] = '';
            }
        }
        
        $filter_query = implode('.', $filterArray);
        
        // For test
        //return response()->json(['loopback' => true, 'submit' => $data, 'filter' => $filter_query], 200);
        $url = "https://sdmx.istat.it/SDMXWS/rest/data/{$data['flow_ref']}/$filter_query";
        
        $resp = $this->_http($url, false);
        
        if($resp['error']){
            Log::error("IstatController:index - $url - " . $resp['httpCode'] . ' ' . $resp['errorMessage'] );
            return response()->json('Errore durante l\'interrogazione ISTAT', 500);
        }
        
        return response()->json($resp, 200);
        
    }
    
    private function _http($url, $json = true) {
        
        $resp = [
            'url'          => $url,
            'error'        => false,
            'errorMessage' => '',
            'httpCode'     => 200,
            'content'      => '',
            'headers'      => '',
        ];
        
        $curlSES=curl_init(); 
        curl_setopt($curlSES, CURLOPT_URL, $url);
        curl_setopt($curlSES, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlSES, CURLOPT_HEADER, true);
        
        $headers = ['Accept-Language: it'];        
        if($json) {
            // Imposta l'header per accettare il formato JSON
            $headers[] = 'Accept: application/json';
        }
        curl_setopt($curlSES, CURLOPT_HTTPHEADER, $headers);
        
        curl_setopt($curlSES, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.103 Safari/537.36");
        
        $response         = curl_exec($curlSES);
        
        $resp['httpCode'] = curl_getinfo($curlSES, CURLINFO_HTTP_CODE);        
        $header_size      = curl_getinfo($curlSES, CURLINFO_HEADER_SIZE);
        
        $headers = substr($response, 0, $header_size);
        $body    = substr($response, $header_size);
            
        $resp['content'] = $body;
        $resp['headers'] = $headers;
        
        if (curl_errno($curlSES)) {
            $resp['error']        = true;
            $resp['errorMessage'] = curl_error($curlSES);
        }
        
        curl_close($curlSES);
        
        // Gestione redirect
        if($resp['httpCode'] == 302 || $resp['httpCode'] == 301){
            if (preg_match('/Location:\s*(.+)\s*/i', $headers, $matches)) {
                $location = trim($matches[1]);
                return $this->_http($location, $json);
            }
        }

        return $resp;

    }        

}
