<?php
header("Content-type: text/html; charset=utf-8");
proxy_type($_GET['ip']);

function proxy_type($ip = '')
{	
	$ips = explode(":",$ip);
	$ip  = $ips[1];
	$ip  = str_replace("//",'',$ip);
	if (!empty($_SERVER['HTTP_VIA'])) {
	//使用了代理
		if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			die(json_encode(array('dl'=>$ip,'lx'=>1))); //普通匿名代理
		} else {
			die(json_encode(array('dl'=>$ip,'lx'=>2))); //透明代理
		}
	} else {
		die(json_encode(array('dl'=>$ip,'lx'=>3))); //高匿代理
	}
}
?>