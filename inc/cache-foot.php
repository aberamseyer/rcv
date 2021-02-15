<?php

if (!isset($_GET['no_cache'])) {
	if ($cachefile) {
		$contents = ob_get_clean();
		echo $contents;

		// cache in file and redis
		file_put_contents($cachefile, $contents);
		$redis_client->set("rcv.ramseyer.dev/cache/".$cachekey, $contents);
		$redis_client->expire("rcv.ramseyer.dev/cache/".$cachekey, 60 * 60); // cache pages for 1 hour in memory
	}
}
