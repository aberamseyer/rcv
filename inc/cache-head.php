<?php

$url = strtok(
	substr(
		strtolower($_SERVER['REQUEST_URI']), 1
	), '?'
);

if (!isset($_GET['no_cache']) && !LOCAL) {
	$cachekey = str_replace('/', '-', $url)."_".($serif_text ? 1 : 0)."-".($light_theme ? 1 : 0)."-".($minimal_layout ? 1 : 0)."-".COMMIT_HASH.".html";
	$cachefile = $_SERVER['DOCUMENT_ROOT']."/extras/cache/".$cachekey;

	if (file_exists($cachefile)) {
		$fp = fopen($cachefile, 'r');
		while (!feof($fp))
			echo fread($fp, 1024*10);
		fclose($fp);
		echo "<!-- Cached copy: ".array_pop(explode("/", $cachefile)).", generated ".date('Y-m-d H:g:i', filemtime($cachefile))." sent in ".number_format(microtime(true) - $time, 4)." sec -->";
		exit;
	}

	ob_start();
}
