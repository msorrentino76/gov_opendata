<?php

namespace App\Http\Controllers\api\v1\legalEntityAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

use App\Models\Codelist;
use App\Models\Code;
use App\Models\Dataflow;
use App\Models\Categories;

use App\Models\DataStructure;
use App\Models\AvailableConstraints;

class IstatController extends Controller
{

    /**
     * STUB!!!!!!
     * @return type
     */
    public function index(){
        
        $territory_key      = 'ITTER107';
        $territory_codelist = 'CL_ITTER107';
        
        $available_territory_constrains = [];
        foreach (AvailableConstraints::where('key', $territory_key)->get() as $avc){
            $available_territory_constrains = array_unique( array_merge($available_territory_constrains, $avc->json_value) );
        }
        
        $code = Code::select('code as value', 'name as label')->where('codelist', $territory_codelist)->whereIn('code', $available_territory_constrains)->get()->toArray();

            usort($code, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
            
        /* available_territory_filter ha 11.000 righe... pesante per lo stub */
 
        return response()->json(
                [
                    'dataflow'   => Dataflow::with('available_territory')->orderBy('name')->get(), /* Dataflow::with('availableConstraints')->orderBy('name')->get(),*/
                    'categories' => Categories::getAll(),
                    'available_territory_filter' => $code,
                ], 200); 
    }

    public function datafilter(Request $request){
        
        $data = $request->only('id_datastructure', 'flow_ref'); 
        
        // VEDIAMO CHE VALORI CI SONO DISPONIBILI: con flow_ref interrogo availableconstraint
        
        $availables = [];
        foreach(AvailableConstraints::where('flow_ref', $data['flow_ref'])->get() as $avc){
            $availables[$avc->key] = $avc->json_value;
        }

        
        // LEGO LE DUE STRUTTURE TRAMITE I MODEL CODELIST E CODE PER LE TRADUZIONI E FORMATTO LE OPTIONS:
        
        $data_struct  = [];
        $filters_json = [];
        
        foreach(DataStructure::where('flow_ref', $data['flow_ref'])->get() as $ds){
            
            $ds_key   = $ds->data_struct;
            $codelist = $ds->codelist;
            $position = $ds->position;
                    
            $data_struct[$ds_key] = [
                'position' => $position,
                'codelist' => $codelist,
            ];
            
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
                    'name'  => 'posix_' . $position,
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
