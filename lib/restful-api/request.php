<?php
//Load Wordpress
define('WP_USE_THEMES', false);
global $wp, $wp_query, $wp_the_query, $wp_rewrite, $wp_did_header;
require dirname(__FILE__).'/../../../../../wp-load.php';

//Load the API
require_once 'labelgen-api.php';

//Requests from the same server don't have a HTTP_ORIGIN header
if (!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

try {
    $API = new labelgen_api(
        $_REQUEST['request'],
        WP_Session::get_instance(),
        $_SERVER['HTTP_ORIGIN']
    );
	echo $API->processAPI();
	exit;
} catch (Exception $e) {
    echo json_encode(
    	array(
    		'success'	=>	false, 
    		'message'	=>	$e->getMessage(),
			'line'		=>	$e->getLine()
    	)
    );
	exit;
}

?>