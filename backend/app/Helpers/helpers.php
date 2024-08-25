<?php

    use App\Models\User;
    use Carbon\Carbon;

    function wsIpa($service, $data) {
        $url = config('app.ipa_ws_base_url') . $service;
        $data['AUTH_ID'] = config('app.ipa_ws_auth_id');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    function modelActivities($model){
        
        $hist = $model->histories()->orderBy('performed_at', 'asc')->get();
        
        $activities = [];
        
        foreach ($hist as $h){
            $owner = (string)User::find($h->user_id);
            $timestamp = Carbon::parse($h->performed_at)->locale('it')->isoFormat('D/MM/YYYY HH:mm');
            if($h->message == 'CREATE') {
                $activities[] = ['content' => 'Creato da ' . $owner, 'timestamp' => $timestamp, 'type' => 'success'];
            }
            if($h->message == 'UPDATE') {
                $extra_content = [];
                foreach($h->meta as $row){
                    if(!empty($row['old']) && !empty($row['new'])){
                        $key = __('validation.attributes.' . $row['key']);
                        $extra_content[] = "$key da: '{$row['old']}' a '{$row['new']}'";
                    }
                }
                if(!empty($extra_content))
                $activities[] = ['content' => 'Modificato da ' . $owner . ': ' . implode(' - ', $extra_content), 'timestamp' => $timestamp, 'type' => 'primary'];
            }
        }
        
        return $activities;
        
    }
    
?>