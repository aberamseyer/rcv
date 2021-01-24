<?php
$url = str_replace('/', '-',
	strtok(
		substr(
			$_SERVER['REQUEST_URI'], 1
		), '?'
	)
);
$cachefile = $_SERVER['DOCUMENT_ROOT']."/extras/cache/cached-".$url.".html";

if (file_exists($cachefile)) {
	echo "<!-- Cached copy, generated ".date("Y-m-d H:i:s", filemtime($cachefile))." -->\n";
	readfile($cachefile);
	exit;
}

ob_start();