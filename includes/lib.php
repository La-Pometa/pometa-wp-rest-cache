<?php


/*- Settings: get options -*/
function pRest_settings_get() {
   $pRest_options = get_option( 'pRest_settings' ); // Array of All Options
   return $pRest_options;
}

/*- Settings: get option value -*/
function pRest_settings_get_option($option,$default=false) {
   $settings = pRest_settings_get();
   return get_array_value($settings,$option,$default);
}

/*- Settings: cache path placeholder -*/
function pRest_settings_get_path_placeholder() {
    return apply_filters("pRest/path/placeholder","rest/");
}


/*- Settings: get common options value -*/

function pRest_settings_get_enabled() {
    $settings = pRest_settings_get();
    return get_array_value($settings,"enabled",false);
}
function pRest_settings_get_htaccess_patch_403_enabled() {
    $settings = pRest_settings_get();
    return get_array_value($settings,"htaccess_patch_403_enabled",false);
}
function pRest_settings_get_compress_enabled() {
    $settings = pRest_settings_get();
    return get_array_value($settings,"gzenabled",false);
}

function pRest_settings_get_file_life(){
    $settings = pRest_settings_get();
    return get_array_value($settings,"expire_minutes",false);
}
function pRest_settings_get_file_life_seconds(){
    $minutes = pRest_settings_get_file_life();
    if ( !$minutes ) {
        return false;
    }
    $seconds = ($minutes ? $minutes * 60: 60);
    return $seconds;
}

function pRest_settings_get_autoupdate() {
    $settings = pRest_settings_get();
    return get_array_value($settings,"autoupdate_enabled",false);
}

function pRest_settings_get_autoupdate_minuts() {
    $settings = pRest_settings_get();
    $minutes_default = apply_filters("prest/autoupdate/minutes",120);
    $m = get_array_value($settings,"autoupdate_minutes",$minutes_default);
    if ( $m == 0 ) {
        $m = $minutes_default;
    }
    return $m;
}

function pRest_settings_get_path() {
    $path = WP_CONTENT_DIR ."/cache";

    $settings = pRest_settings_get();
    $folder = get_array_value($settings,"path",false);
    if ( !$folder ) {
        $folder = pRest_settings_get_path_placeholder();
    }
    if ( substr_first_char($folder) != "/" ) { $folder="/".$folder; }
    if ( substr_last_char($folder)  != "/" ) { $folder.="/"; }
    $dir = $path.$folder;
    return $dir;
}


/*- 
    nom arxiu de cache de la consulta actual: modificació del nom per accedir des de htaccess 
-*/

function pRest_get_request_cache_file($request, $complete = false) {


    //list ( $uri, $directory )= pRest_get_request_uri();
    $uri = pRestCache_GetUrlFromRequest($request);
    //$params = "";
    if(substr_last_char($uri) == "/"){$uri.="/";}
    //if($directory && substr_last_char($directory) != "/"){$directory.="/";}
    //echo "\n<br> REQUEST: <pre>".print_r($request,true)."</pre>";

   // echo "\n<br> URI: $uri";

    //$in = array("/","?","&","=");
    //$out = array("-","-","-","-");
    $in = array("?");
    $out=array("/");
    
    $in=array_merge(array("=","&"),$in);
    $out = array_merge(array("-","-"),$out);

    $uri = str_replace($in,$out,$uri);
    
    if ( $complete ) {
        $uri = pRest_settings_get_path()."/wp-json/".$uri;
    }
    $uri.="/index.json";
    $uri = str_replace("//","/",$uri);

    // echo "\n<br> URI LAST: $uri";
    //echo "\n<br> URI PARAMS: $params";

    return $uri;
}

/*- 
    url i directori de la consulta actual
-*/

function pRest_get_request_uri() {
    global $_SERVER;

    $uri = get_array_value($_SERVER,"REQUEST_URI",false);
    $uri_params = explode("/wp-json/",$uri);
    
    

    if ( count($uri_params) == 1 ) {

        //No hi ha wp-json a la consulta
        return array("", "");
    }

    $uri_array = explode("?",$uri);
    $uri_start = get_array_value($uri_array,0,"");
    $uri_params = get_array_value($uri_array,1,"");
    $uri_params_array = ($uri_params ? explode("&",$uri_params) : array());
    $uri_final = "";
    if ( $uri_params_array && is_array($uri_params_array)) {
        foreach($uri_params_array as $param_key) {
            $get_array = explode("=",$param_key);
            $get_key = get_array_value($get_array,0);
            $get_value = get_array_value($get_array,1,"");
            $uri_final .= ($uri_final ? "-" : ""). $get_key . "-" . $get_value;
        }
    }

  
    $directory = get_array_value($uri_params,"0","no-subfolder");
    if ( $directory == "no-subfolder" ) { $directory = ""; }
    
    $uri = get_array_value($uri_params,"1","no-domain");
    if ( $uri == "no-domain" ) { $uri = ""; }
    echo "<br> URI : [".$uri."]";
    return array($uri , $directory);

}



/*-
    convertir temps que falta per expirar a text
-*/

function pRest_get_expire_time_to($time,$cache_time = 0) {
    $ara = time();
    $compara = ($time+$cache_time);

    if ( $cache_time && $ara > $compara ) {
        return false;
    }

    $ara = new DateTime(date("Y-m-d H:i:s",time()));//fecha inicial
    $fecha2 = new DateTime(date("Y-m-d H:i:s",$compara));//fecha de cierre
    
    $intervalo = $ara->diff($fecha2);

    $dies = $intervalo->format("%d");
    $hores = $intervalo->format("%h");
    $minuts = $intervalo->format("%i");
    $segons = $intervalo->format("%s");

    if ( $dies == "0") {$dies = ""; }
    if ( $hores == "0" ) {$hores = ""; }
    if ( $minuts == "0") { $minuts = "";}
    if ( $segons == "0") {$segons = "";}

    if ( $dies) { $dies = sprintf(__("%s dies","pometaRestltd"),$dies);}
    if ( $hores ) { $hores = sprintf(__("%s hores","pometaRestltd"),$hores);}
    if ( $minuts ) { $minuts = sprintf(__("%s minuts","pometaRestltd"),$minuts);}
    if ( $segons ) { $segons = sprintf(__("%s segons","pometaRestltd"),$segons);}

    $total = ($dies?$dies:"");
    $total .= ($total?" ":"").($hores?$hores:"");
    $total .= ($total?" ":"").($minuts?$minuts:"");
    $total .= ($total?" ":"").($segons?$segons:"");

    if ( !$total ) {
        $total = __("S'està actualitzant ara","pometaRestltd");
    }

    return $total;
    
}