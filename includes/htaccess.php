<?php 

$file_runtime = dirname(__FILE__)."/compatlib.php";
if ( !file_exists($file_runtime) ){exit;}
require_once($file_runtime);


$droot = $_SERVER["DOCUMENT_ROOT"];
$data = "";
$slug = "";
$path = "";
$withPath = true;

$filter_post = true;
if  ($_SERVER["REQUEST_METHOD"] == "POST" && $filter_post) {
	$post_data = json_decode(file_get_contents('php://input'), true);
     $_POST = array_merge($_POST, $post_data);

}

if (isset($_GET["prdebug"])) {
	$prcache_debug=true;
	unset($_GET["prdebug"]);
}
if (isset($_GET["__path"])) {
	$path=$_GET["__path"];
}

if ($prcache_debug){
	echo "<br> DROOT: $droot";
}

$filename = pRestCache_GetUrlFromHTAccess()."/index.json";

if ( $withPath ) {
	$filename = $droot . $path . $filename;
}

//header("PRCache-file: $filename");
if ( $prcache_debug ) {
	echo "\n<br> localURL: ".$filename;
}
if ( file_exists($filename) ){
	$content = file_get_contents($filename);
	// header("PRCache-state: found");
	// header("PRCache-size: ".strlen( $content )." bytes");

	header("Access-Control-Allow-Origin: " . $prcache_origin);
	header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
	header("Access-Control-Allow-Credentials: true");
	header('Access-Control-Allow-Headers: Origin, X-Requested-With, Redirect, X-WP-Nonce, Content-Type, Accept, Authorization');


	echo $content;

	//Retornat cache, acabar

	die();
}


$uri = $_SERVER["REQUEST_URI"];
$uri_array = explode("?",$uri);
$uri = $uri_array[0];
if ($prcache_debug ) {
	echo "<br> POST: <pre>".print_r($_POST,true)."</pre>";
	$uri = str_replace("&prdebug=1","",$uri);
}

//Retornar a index.php
$_GET["rest_route"]=str_replace("/".$prcache_apiroute."/","/",$uri);;

