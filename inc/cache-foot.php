<?php

if (!isset($_GET['no_cache'])) {
	if ($cachefile) {
		$contents = ob_get_clean();
		echo $contents;

		// cache in file
		file_put_contents($cachefile, $contents);
	}
}
