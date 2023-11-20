<?php

require_once("compatlib.php");


//Afegir filtre a l'acabar la resposta
add_action("init","pRestFilters");
function pRestFilters() {
    add_filter("rest_pre_echo_response", "pRestFilterResponse",99999999999999,3);
}


//Crear una versió de cache de la consulta actual
function pRestFilterResponse($result , $t, $request ) {

    $file = pRest_get_request_cache_file($request,$complete = true);
    $result = pRestCacheCreate($file,$result, $request);

    do_action("prest/cache/created",$request);

    return $result;
}


//Comprova i construeix la carpeta dins del cache necessaria per guardar la consulta actual
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

//Crea una copia cache de $result a l'arxiu $filename
function pRestCacheCreate($filename,$result, $request) {

    $create = false;

    // // Endpoints que no s'han de guardar
    // $prevent = array(
    //     "v1/contact-forms/",
    //     "v2/cache/clean",
    //     "v2/cache/expire"
    // );
    // $prevent = apply_filters("rest/cache/prevent",$prevent);

    // if ( $prevent && is_array($prevent) && count($prevent)) {
    //     foreach($prevent as $endpoint ) {
    //         if ( strpos($filename,$endpoint) !== false) {
    //             $create=false;
    //             break;
    //         }
    //     }
    // }

    $route = $request->get_route();
    $create = wphl_cache__route_is_enabled($route);


    //Parametre per a desactivar cache _GET['wprest-no-cache']
    if ( isset($_GET["wprest-no-cache"])){
        $create = false;
    }


    //Filtrar si guardar o no arxiu
    $create = apply_filters("rest/cache/create",$create);
    
    if ( $create ) {

        //Filtrar resposta final
        $result = apply_filters("rest/cache/create/response",$result);

        //Filtrar nom de l'arxiu de cache
        $filename = apply_filters("rest/cache/create/filename",$filename);

        //Resultat en json
        $json = json_encode($result);

        // Comprova si el directori està creat 
        pRestCacheCreatePath($filename);

        // Guardar arxiu cache 
        if(file_put_contents($filename,$json)) {

            //Si està la compressió GZ habilitada
            if (pRest_settings_get_compress_enabled()) {
                if(!pRest_gzCompressFile($filename)) {
                    //No existe "gzwrite" o error
                }

            }
        }


    }

    return $result;
}


//Crea una copia de comprimida
function pRest_gzCompressFile($filename, $level = 9){
    if ( !function_exists("gzwrite")) {
        return false;
    }
    $dest = $filename . '.gz';
    $mode = 'wb' . $level;
    $error = false;
    if ($fp_out = gzopen($dest, $mode)) { if ($fp_in = fopen($filename,'rb')) { while (!feof($fp_in)) { gzwrite($fp_out,fread($fp_in, 1024 * 512)); }fclose($fp_in); } else {$error = true; } gzclose($fp_out); } else { $error = true; }
    if ($error) {return false; } else {return $dest;}
}
