<?php
if ($cachekey) {
	// $fh = fopen($cachefile, 'w');
	// fwrite($fh, ob_get_contents());
	// fclose($fh);
	$redis_client->set($cachekey, ob_get_contents());
	$redis_client->set($cachekey."-date", date("Y-m-d H:i:s", time()));
	ob_end_flush();
}