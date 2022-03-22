<?php
/**
 * Created by Sumblime Text 3
 * User: user
 * Date: 2021-02-14
 * Time: 13:19
 */

http_response_code(404); // must be sent before other output

$title = "Not Found";
$meta_description = "The page you are looking for does not exist on this website";
$meta_canonical = "https://".getenv("DOMAIN")."/404";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

?>
<style>
	pre {
		display: inline;
		padding: 0.6rem;
	}
</style>
<h1><a href='/bible'>Not Found</a></h1>
<div>The page you are looking for <?= isset($_GET['uri']) ? "<pre>".html($_GET['uri'])."</pre>" : "" ?> does not exist on this website. Make sure the url looks right!</div>

<?php

echo "<hr>".nav_line();

require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";