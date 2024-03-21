<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use Jenssegers\Agent\Agent;

class StoricoLogin extends Model
{
    use HasFactory;
    
    protected $table = 'storico_login';
    
    protected $fillable = ['user_id', 'data_ora', 'ip', 'isp', 'org', 'as', 'country', 'zip', 'regionName', 'city', 'lat', 'lon', 'timezone', 'so', 'browser', 'device'];
	
    protected $hidden = [
        'id',
        'user_id',
    ];
    
    public    $timestamps = false;
        
    public static function store($request, $user) {

        $now = Carbon::now()->toDateTimeString();
	$ip  = $request->getClientIp();
                
        try {
            $ipapi = json_decode(file_get_contents('http://ip-api.com/json/' . $ip));		
            $success_call = (json_last_error() === JSON_ERROR_NONE) && ($ipapi->status === 'success');
        } catch(\Exception $e) {
            $success_call = false;
        }
        
        //https://github.com/jenssegers/agent
        $agent = new Agent();
        $browser          = $agent->browser();
        $version_browser  = $agent->version($browser);
        $platform         = $agent->platform();
        $version_platform = $agent->version($platform);
        $device = '';
        if($agent->isDesktop()){$device = 'Desktop';}
        if($agent->isPhone())  {$device = 'Phone';}
        if($agent->isTablet()) {$device = 'Tablet';}
        if($agent->isRobot())  {$device = 'Robot:' . $agent->robot();}
                
        $track_data = array(

            'user_id' => $user->id,
            
            'data_ora' => $now,
            'ip' => $ip,

            'isp' => $success_call && isset($ipapi->isp) ? $ipapi->isp : '',
            'org' => $success_call && isset($ipapi->org) ? $ipapi->org : '',
            'as'  => $success_call && isset($ipapi->as) ? $ipapi->as  : '',
            'country'     => $success_call && isset($ipapi->country) ? $ipapi->country    : '',
            'regionName'  => $success_call && isset($ipapi->regionName) ? $ipapi->regionName : '',
            'city'     => $success_call && isset($ipapi->city) ? $ipapi->city     : '',
            'zip'      => $success_call && isset($ipapi->zip) ? $ipapi->zip      : '',
            'lat'      => $success_call && isset($ipapi->lat) ? $ipapi->lat      : '',
            'lon'      => $success_call && isset($ipapi->lon) ? $ipapi->lon      : '',
            'timezone' => $success_call && isset($ipapi->timezone) ? $ipapi->timezone : '',

            'so'       => "$platform $version_platform",
            'browser'  => "$browser $version_browser",
            'device'   => $device,

        );

        StoricoLogin::create($track_data);
                
    }
    
    public function user(){
        return $this->belongsTo(User::class);
    }
    
}
