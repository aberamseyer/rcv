<?php
if ($cachefile) {
	$fh = fopen($cachefile, 'w');
	fwrite($fh, ob_get_contents());
	fclose($fh);
	ob_end_flush();
}