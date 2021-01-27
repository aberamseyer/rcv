<?php
$url = str_replace('/', '-',
	strtok(
		substr(
			strtolower($_SERVER['REQUEST_URI']), 1
		), '?'
	)
);
// track individual page views
$redis_client->hincrby("rcv.ramseyer.dev/page-views", $url, 1);

if (!isset($_GET['no_cache']) && !LOCAL) {
	$filename = $url."_".($serif_text ? 1 : 0)."-".($light_theme ? 1 : 0)."-".($minimal_layout ? 1 : 0).".html";
	$cachekey = $_SERVER['DOCUMENT_ROOT']."/extras/cache/".$filename;

	if (file_exists($cachekey)) {
		readfile($cachekey);
		echo "\n<!-- Cached copy $filename, generated ".date('Y-m-d H:g:i', filemtime($cachekey))." -->";
		exit;
	}

	ob_start();
}