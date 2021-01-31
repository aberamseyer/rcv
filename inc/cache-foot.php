<?php
if (!isset($_GET['no_cache'])) {
	if ($cachefile) {
		$fh = fopen($cachefile, 'w');
		fwrite($fh, ob_get_contents());
		fclose($fh);
		ob_end_flush();
	}
}
