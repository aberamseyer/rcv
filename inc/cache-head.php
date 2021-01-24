<?php
$url = str_replace('/', '-',
	strtok(
		substr(
			$_SERVER['REQUEST_URI'], 1
		), '?'
	)
);
$cachekey = "rcv.ramseyer.dev/cache/".$url;

if ($redis_client->exists($cachekey)) {
	echo "<!-- Cached copy, generated ".$redis_client->get($cachekey."-date")." -->\n";
	echo $redis_client->get($cachekey);
	exit;
}

ob_start();