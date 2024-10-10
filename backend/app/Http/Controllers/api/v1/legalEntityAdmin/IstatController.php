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

use Illuminate\Support\Facades\DB;

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

    /* OLD SOLUTION */
    /*
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
        
            // NON HA SENSO FILTRARE PER UN CAMPO CHE HA SOLO UNA OPTION
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
    */
    
    public function datafilter($flow_ref){        
        
        $results = DB::select("
                    SELECT
                    CONCAT('posix_', ds.position) as name,
                    cl.name as label,
                    ac.json_value as available_options,
                    T.code_options,
                    ds.data_struct
                    FROM data_structures ds
                    JOIN codelists cl on cl.codelist = ds.codelist
                    JOIN available_constraints ac on ac.key = ds.data_struct and ac.flow_ref = ds.flow_ref
                    JOIN (
                                    SELECT
                                            codes.codelist_id,
                                            JSON_ARRAYAGG(
                                                    #JSON_OBJECT( 'value', code,'label', name)
                                                    JSON_OBJECT( 
                                                        'code', code,
                                                        'name', name
                                                        )
                                    ) AS code_options FROM codes GROUP BY codes.codelist_id
                        )T on T.codelist_id = cl.id
                    where ds.flow_ref = '$flow_ref'"
                );
        
        $filters_json = [];
        
        foreach($results as $r){
            
            $available_options = json_decode($r->available_options);
            $code_options      = json_decode($r->code_options);
            
            $code_to_name = [];
            
            foreach ($code_options as $c) {
                $code_to_name[$c->code] = $c->name;
            }
            
            $options = [];
            
            // combino $available_options e $available_options
            foreach ($available_options as $ao) {
                $options[] = [
                    'value' => $ao,
                    'label' => $code_to_name[$ao],
                ];
            }
            
            usort($options, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
            
            //if(count($options) > 1){
                $filters_json[] = [
                    'name'    => $r->name,
                    'label'   => $r->label,
                    'type'    => $r->data_struct == 'ITTER107' ? 'territory'        : 'select',
                    'options' => $r->data_struct == 'ITTER107' ? $available_options : $options,
                ];
            //}
        }

        return response()->json([
            'nPos'                  => count($results),
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
        
        $resp = $this->_http($url);
        
        if($resp['error']){
            Log::error("IstatController:index - $url - " . $resp['httpCode'] . ' ' . $resp['errorMessage'] );
            return response()->json(['error' =>'Errore durante l\'interrogazione ISTAT. Riprovare più tardi'], 200);
        }   
        
        file_put_contents('resp.json', $resp['content']);
        
        $series_transcod = [];
        
        if($resp['httpCode'] != 200) {
            
            $empty = true;
            
        } else {
        
            $empty = false;
            
            $content = json_decode($resp['content']);

            $dataSets   = $content->dataSets;
            $structures = $content->structure->dimensions->series;

            $series = $dataSets[0] ? $dataSets[0]->series : [];            

            $filter_name  = [];
            $filter_value = [];        
            
            foreach ($structures as $s) {                
                $values = [];
                foreach($s->values as $v) {
                    $values[] = $v->name;
                }
                // notare come tutto sia posizionale! questo è essenziale per lo split della
                // key di $series.
                // P.Es. 0:0:1:2 => posizione 0 -> valore "0"; posizione 1 -> valore "0"; posizione 2 -> valore "1"; posizione 3 -> valore "2"
                
                $filter_name[$s->keyPosition]  = $s->name;
                $filter_value[$s->keyPosition] = $values;
            }
            
            foreach($series as $k => $v){
                
                // $k va transcodificato tramite $structure come da commento precedente!
                // per ottenere i title che sarà un oggetto del tipo:
                // [ {'label': label, 'value': value}, {'label': label, 'value': value}, ... ]
                // Es:
                // [ {'label': 'Territorio', 'value': 'Altofonte'}, ... ]
                
                $titles = [];
                foreach (explode(':', $k) as $position => $value){
                    $titles[] = [
                        'label' => $filter_name[$position], 
                        'value' => $filter_value[$position][$value],
                    ];
                }
                
                $series_transcod[] = [
                    'titles'       => $titles,
                    'observations' => $this->observationsToMultidimensionaAxis($v->observations),
                    'annotations'  => $v->annotations,
                    'attributes'   => $v->attributes,
                ];
            }
        
        }
        
        $datasets = [
            'url'      => $resp['url'],
            'empty'    => $empty,
            'status'   => $resp['httpCode'],
            'isTest'   => $empty ? null : $content->header->test,            
            'datasets' => $series_transcod,
        ];        
        
        return response()->json($datasets, 200);
        
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

    
    private function observationsToMultidimensionaAxis($observations) {
        
        $axis = [];
        
        foreach ($observations as $x => $y_values){
            
            $y_axis = [];
            
            foreach ($y_values as $k => $y){
                $y_axis['y_' . $k] = $y;
            }
            
            $axis[] = array_merge(['x' => $x], $y_axis);
        }
        
        return $axis;
        
    }
}
