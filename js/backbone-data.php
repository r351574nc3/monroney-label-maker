<?php
//error_reporting (E_ALL);
$user = "admin";
$pw = "Wh005eAfra!d0fV!rginaW0Olf?";
$api_url = "{$_SERVER['HTTP_HOST']}/monroney/addendum-generator/api/backbone/all";

$headers = array(
	//"Authentication: hmac {$user}:{$nonce}:{$digest}",
	"Content Type: application/json; charset=utf-8",
	"Accept: application/json",
);

$curl = curl_init();

echo $api_url;

$options = array(
	CURLOPT_URL				=>	$api_url,
	CURLOPT_RETURNTRANSFER	=>	1,
	CURLOPT_HTTPHEADER 		=>	$headers
);

curl_setopt_array($curl, $options);
$data = curl_exec($curl); 
curl_close($curl);   

echo $data;
?>