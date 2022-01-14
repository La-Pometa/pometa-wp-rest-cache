<?php






function pRest_routes_cache_clean($dir=false) {
    return pRestCacheClean(array(),"CLEAN",$dir);
}

function pRest_routes_cache_expire($dir=false) {
    return pRestCacheClean(array(),"EXPIRE",(!is_object($dir)?$dir:false));
}



function pRestCacheIsExpired($filename) {
    
    $expired = false;
    
    $file_time = filemtime($filename);
    $file_life = pRest_settings_get_file_life_seconds();
    if ( !$file_life ) {
        return false;
    }
    $file_end = $file_time + $file_life;
    if ( $file_end < time()) {
        $expired=true;
    }
    return $expired;

}

function pRestCacheClean($result,$format = "EXPIRE", $dir="") {


    if ( is_object($dir)) {
        $dir = false;
    }

    if ( !$dir ) {
        $dir = pRest_settings_get_path();
    }

    if ( !isset($result["msg"])) {$result["msg"]=$format;}
    if ( !isset($result["files"])) {$result["files"]=0;}
    if ( !isset($result["directories"])) {$result["directories"]=0;}
    if ( !isset($result["info"])) {$result["info"]=array();}

    
    $files=0;
    $directories=0;



    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {

                if ($object != "." && $object != "..") {
                    $file = $dir."/".$object;

                    if (filetype($file) == "dir") {
                        $result=pRestCacheClean($result, $format, $file); 
                    }
                    else {

                        if ( $format == "CLEAN" ||  
                            ($format == "EXPIRE" && pRestCacheIsExpired($file)) 
                        ) {
                            $result["files"]++;
                            unlink($file);
                        }
                        if ( $format == "LIST") {
                            $result["files"]++;
                            $result["info"][]=array("file"=>$file,"time"=>filemtime($file),"size"=>filesize($file));
                        }
                    }
                }
        }
        reset($objects);
        if ( $format == "CLEAN") {
            rmdir($dir);
        }
        $result["directories"]++;

    }

    if ( $format  == "EXPIRE") {
        $result["directories"]=0;
    }

    return $result;
}
