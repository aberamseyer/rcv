<?php
die("no\n");

require "../vendor/autoload.php";
$redis_client = new Predis\Client([ 'host' => '127.0.0.1' ]);

$page_views = $redis_client->hgetall("rcv.ramseyer.dev/page-views");
foreach($page_views as $k => $view) {
	if (strpos($k, ".") !== false)
    $redis_client->hdel("rcv.ramseyer.dev/page-views", $k);
}
unset($view);
