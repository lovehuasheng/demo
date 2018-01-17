<?php
$res = $_POST['args'];
if (!empty($res)) {
	$urls = json_decode($res,true);
	if ($urls) {
		$api = 'http://data.zz.baidu.com/urls?site=www.ichunt.com&token=yqXCDForI31Jv1ny';
		$ch = curl_init();
		$options =  array(
			CURLOPT_URL => $api,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => implode("\n", $urls),
			CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
		);
		curl_setopt_array($ch, $options);
		$result = curl_exec($ch);
		echo $result;
	}
	
}
