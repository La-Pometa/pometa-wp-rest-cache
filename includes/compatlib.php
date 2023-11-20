<?php 


function pRestCache_GetUrlFromHTAccess($args = array()) {

    $route = $_SERVER["REQUEST_URI"];
    $route_arr = explode("?", $route);

    $route = $route_arr[0];
    $params = $_GET;
    $post = $_POST;
   // echo "<br> SERVER: <pre>".print_r($_SERVER,true)."</pre>";

    return pRestCache_GetUrlFromParams($route, $params, $post, $args);
}

function pRestCache_GetUrlFromRequest($request, $args = array()) {

    $route = $request->get_route();
    $params = $request->get_query_params();
    $post = $request->get_json_params();

    return pRestCache_GetUrlFromParams($route, $params, $post, $args);
}


function pRestCache_GetUrlFromParams($route, $params = array(), $post = array(),$args = array()) {

    $has_params = "";

   // echo "<br> ROUTE: [".$route."]";
//     echo "<br> PARAMS: [<pre>".print_r($params,true)."</pre>]";
//     echo "<br> POST: [<pre>".print_r($post,true)."</pre>]";
//    echo "<br> POST: [<pre>".print_r($_SERVER,true)."</pre>]";
     
    if ( isset($params["slug"]) && !$params["slug"] )  {$params["slug"] = "-";}

    if ( isset($params["slug"]) && substr($params["slug"],0,1) == "/") {
        $params["slug"] = substr($params["slug"],1,strlen($params["slug"])-1);
    }
    
    if ( isset($params["rest_route"]))  { unset($params["rest_route"]); } 
    if ( isset($params["__path"]))  { unset($params["__path"]); } 
    if ( isset($params["__dir"]))  {unset($params["__dir"]);} 
    if ( isset($params["POST"]))  { $post = $params["POST"]; unset($params["POST"]);} 

    if ($params && is_array($params) and count($params)) {
        foreach($params as $get_key => $get_value) {
            if ( is_array($get_value)) {
                foreach($get_value as $get_vkey => $get_vvalue) {
                    if ( $get_vvalue){
                        $has_params .= ( $has_params ? "&":"") . $get_key ."[".$get_vkey."]=".$get_vvalue;
                    }
                    else {
                        $has_params .= ( $has_params ? "&":"") . $get_key ."=-";
                    }
                }

            }
            else {
                $has_params .= ( $has_params ? "-":"") . $get_key ."-".$get_value;
            }
        }
    }
    if ( $has_params ) {
        $route .="/".$has_params;
    }


    if ($post && is_array($post) and count($post)) {
        $has_params="";
        foreach($post as $post_key => $post_value) {
            if ( $post_value) {
                if ( is_array($post_value)) {
                    foreach($post_value as $post_vkey => $post_vvalue) {
                        $has_params .= ( $has_params ? "&":"") . $post_key ."[".$post_vkey."]=".$post_vvalue;
                    }
                }
                else {
                    $has_params .= ( $has_params ? "&":"") . $post_key ."=".$post_value;
                }
            }
        }
       // echo "<br> PARAMS: <pre>".print_r($has_params,true)."</pre>";

        if ( $has_params ) {
          $route .= "/POST/".md5($has_params);
        }
    }

    //echo "<br> ROUTE FILENAME:[".$route."]";

    return $route;

}
