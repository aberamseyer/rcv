<?php
if (!isset($_GET['no_cache'])) {
	if ($cachekey) {
		$fh = fopen($cachekey, 'w');
		fwrite($fh, ob_get_contents());
		fclose($fh);
		ob_end_flush();
	}
}