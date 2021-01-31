<?php
//die("no\n");

echo "Deleting cache files..\n";
shell_exec("rm -f ".__DIR__."/cache/*.html");

require __DIR__."/../vendor/autoload.php";
$redis_client = new Predis\Client([ 'host' => '127.0.0.1' ]);
$page_views = $redis_client->keys("rcv.ramseyer.dev/cache*");
echo "Deleting cache keys in redis..\n";
foreach($page_views as $k) {
  echo "\t$k\n";
  $redis_client->del($k);
}
unset($view);

echo "done!\n";
