<?php






/*-
    modificar la resposta en el cas de API REST 
-*/
add_action("init","pRestFilters");
function pRestFilters() {
    add_filter("rest_pre_echo_response", "pRestFilterResponse",99999999999999,3);
}

/*-
    crear una versió de cache de la consulta actual
-*/
function pRestFilterResponse($result , $t, $request ) {
    //echo "<br>[prest:debug].. filter response...";
    $file = pRest_get_request_cache_file($complete = true);
    //echo "<br>[prest:debug].. filename: $file";
    $result = pRestCacheCreate($file,$result);
    do_action("prest/cache/created",$request);

    return $result;
}


function pRestCache_GetUrlFromRequest($request) {

    $route = false;

    if ( $request ) {
       
       // $route = $request->get_route()."/"; //Si activo "/", he de passar una altra variable
        $route = $request->get_route();

        $params = $request->get_query_params();

        if ($params && is_array($params) and count($params)) {
            $has_params = http_build_query($params);
            if ( $has_params ) {
                $route .="?".$has_params;
            }
        }
    }

    return $route;

}


/*-
    comprova i construeix la carpeta dins del cache necessaria per guardar la consulta actual
-*/

function pRestCacheCreatePath($filename) {

    $total  = $filename;
    $droot = ABSPATH;
    $path = dirname(str_replace($droot,"",$total));
    $str = explode(DIRECTORY_SEPARATOR, $path);
    
    

    $dir = ABSPATH;
    if ( substr_last_char($dir) == DIRECTORY_SEPARATOR ) { $dir = substr($dir,0,strlen($dir)-1);}

    foreach ($str as $part) {
        $dir .= DIRECTORY_SEPARATOR. $part ;
        if (!is_dir($dir) && strlen($dir) > 0) {
            if ( !mkdir($dir)) {
                echo "<br>\n Can't create folder[".$dir."]";
            }
        }

        
    }
}

/*-
    guarda una copia cache de $result a l'arxiu $filename
-*/
function pRestCacheCreate($filename,$result) {

    // Comprova si el directori està creat 
    $create = true;


    $prevent = array(
        "v1/contact-forms/",
        "v2/cache/clean",
        "v2/cache/expire"
    );
    $prevent = apply_filters("rest/cache/prevent",$prevent);
    if ( $prevent && is_array($prevent) && count($prevent)) {
        foreach($prevent as $endpoint ) {
            if ( strpos($filename,$endpoint) !== false) {
                $create=false;
                break;
            }
        }
    }

    
    if ( $create ) {

        $create = apply_filters("rest/cache/create",$create);

        if ( $create ) {


            $json = json_encode($result);
            //$json = substr($json,1,strlen($json)-2);

            pRestCacheCreatePath($filename);
            if(file_put_contents($filename,$json)) {
                if(!pRest_gzCompressFile($filename)) {
                    //No existe "gzwrite" o error
                }
            }

        }

    }

    return $result;
}


/*-
    crea una copia de $source comprimida
-*/
function pRest_gzCompressFile($source, $level = 9){
    if ( !function_exists("gzwrite")) {
        return false;
    }
    $dest = $source . '.gz';
    $mode = 'wb' . $level;
    $error = false;
    if ($fp_out = gzopen($dest, $mode)) { if ($fp_in = fopen($source,'rb')) { while (!feof($fp_in)) { gzwrite($fp_out,fread($fp_in, 1024 * 512)); }fclose($fp_in); } else {$error = true; } gzclose($fp_out); } else { $error = true; }
    if ($error) {return false; } else {return $dest;}
}
