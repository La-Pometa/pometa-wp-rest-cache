<?php


/*-

Parts principals:

1.- Creació de cron
2.- Marca estat en database

-*/


//add_action('pRestCacheCron', 'pRest_Cache_Cron_AutoUpdate');

function pRest_Cache_Cron_AutoUpdate() {

    //Realitzar $number consultes per generar arxiu cache 
    $number = apply_filters("prest/cache/autoupdate/num",5);



    $msg = "";


    $elements = pRest_AutoUpdate_get_expired($number);

    $msg = "<h3>pRest_Cache_Cron_AutoUpdate[".$number."]</h3>";


    $apipath = apply_filters("prest/cache/apipath","wp-json");

    if ( $elements && count($elements)) {
        foreach($elements as $element_pos => $element_data) {
            $url_request = get_object_value($element_data,"request",false);
            if ( $url_request) {
                $url_real = site_url("/".($apipath?$apipath:"wp-json")).$url_request;
                $msg.="<br> Request[".$url_real."]";
                $res = wp_remote_get($url_real);

            }
        }
    }
    else {
        $msg.="\n<br> No elements to update";

    }

    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail("suport@lapometa.com","Cron Update[prest]", $msg, $headers);


}

function pRest_AutoUpdate_get_expired($number = 5) {

    $elements = array();

    $delay = intval( pRest_settings_get_autoupdate_minuts()) * 60 ;
    $now = time();

    global $wpdb;
    $sSQL = "SELECT * FROM `".$wpdb->prefix."prest_cache` WHERE ((SELECT `time` + ".$delay.") < ".$now." ) ORDER BY `time` ASC";

    if ( $number ) {
        $sSQL.=" LIMIT ".$number;
    }

    $data=array();
    $ras = $wpdb->get_results($sSQL);
    if ( $ras ) {
        foreach($ras as $r) {
           $elements[]=$r;

        }
    }

    return $elements;
}

function pRest_AutoUpdate_get($number = 5) {


    $elements = array();

    global $wpdb;
    $sSQL = "SELECT * FROM `".$wpdb->prefix."prest_cache`";

    $data=array();
    $ras = $wpdb->get_results($sSQL);
    if ( $ras ) {
        foreach($ras as $r) {
           $elements[]=$r;

        }
    }

    return $elements;

}


//Prest acaba de crear una versió en cache de una request:
//actualitzar el temps de creació la request a la base de dades

//add_action("prest/cache/created","pRest_AutoUpdate_cache_created");

function pRest_AutoUpdate_cache_created($request) {

        $object_slug = "";
        $object_id = "";

        $url = pRestCache_GetUrlFromRequest($request);
        $actions = array(
            array(
                "action"=>"update",
                "url"=>$url,
                "data"=>array(
                    "request" => $url,
                    "object_slug"=> $object_slug,
                    "object_id"=> $object_id,
                )
            )
        );

        pPrestAutoUpdate_process_actions($actions);

}


function pPrestAutoUpdate_process_actions($actions) {


    if ( is_array($actions) && count($actions) > 0 ) {

        global $wpdb;

        foreach($actions as $action) {
            $this_action = get_array_value($action,"action",false);
            if ( $this_action ) {

                $data = get_array_value($action,"data",array());
                $url = get_array_value($action,"data",array());

                if ( $this_action == "update") {
                    $request = get_array_value($data,"request",false);
                    $object_slug = get_array_value($data,"object_slug","post");
                    $object_id = get_array_value($data,"object_id","1");
                    $object_status = 1;
                    
                    $sSQL = '
                        INSERT INTO `'.$wpdb->prefix.'prest_cache` 
                        (`ID`, `request`, `object_slug`, `object_id`, `status`, `path`, `time`)
                        VALUES
                        (NULL, "'.$request.'", "'.$object_slug.'", "'.$object_id.'", "'.$object_status.'", "'.$url.'" , "'.time().'" )
                        ON DUPLICATE KEY UPDATE 
                            `time`="'.time().'"
                    ';
                    

                    $ras = $wpdb->get_results($sSQL);

                }
            }
        }
    }
}

