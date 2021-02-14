<?php
$url = strtok(
	substr(
		strtolower($_SERVER['REQUEST_URI']), 1
	), '?'
);

// track individual page views.
// these keys will be valid urls bc the urls are validated in url.php, included at the end of init.php
if (STATS) {
	$redis_client->hincrby("rcv.ramseyer.dev/page-views", str_replace("-", "/", $url), 1);
}

if (!isset($_GET['no_cache']) && !LOCAL) {
	$cachekey = str_replace('/', '-', $url)."_".($serif_text ? 1 : 0)."-".($light_theme ? 1 : 0)."-".($minimal_layout ? 1 : 0).".html";
	$cachefile = $_SERVER['DOCUMENT_ROOT']."/extras/cache/".$cachekey;

	if (
		($output = $redis_client->get("rcv.ramseyer.dev/cache/".$cachekey)) ||
		file_exists($cachefile)
	) {
		if (!$output) {
			// file exists, cache it in redis
			$output = file_get_contents($cachefile);
			$redis_client->set("rcv.ramseyer.dev/cache/".$cachekey, $output);
			$redis_client->expire("rcv.ramseyer.dev/cache/".$cachekey, 60 * 60); // cache pages for 1 hour in memory
		}
		echo $output;
		echo "<!-- Cached copy: ".array_pop(explode("/", $cachefile)).", generated ".date('Y-m-d H:g:i', filemtime($cachefile))." -->";
		exit;
	}

	ob_start();
}
