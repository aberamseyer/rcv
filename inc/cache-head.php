<?php
if (!isset($_GET['no_cache'])) {
	$url = str_replace('/', '-',
		strtok(
			substr(
				$_SERVER['REQUEST_URI'], 1
			), '?'
		)
	);
	$cachekey = $_SERVER['DOCUMENT_ROOT']."/extras/cache/".$url."_".($serif_text ? 1 : 0)."-".($light_theme ? 1 : 0)."-".($minimal_layout ? 1 : 0).".html";

	if (file_exists($cachekey)) {
		readfile($cachekey);
		echo "\n<!-- Cached copy, generated ".date('Y-m-d H:g:i', filemtime($cachekey))." -->";
		exit;
	}

	ob_start();
}