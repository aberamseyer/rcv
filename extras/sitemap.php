<?php

error_reporting(E_ALL^E_NOTICE);

$time = microtime(true);

$db = mysqli_connect('127.0.0.1',  'rcv_app', '0XgOQnAKU6Mz6ja6', 'rcv');

require "../functions.php";

$books = select("
  SELECT REPLACE(b.name, ' ', '_') name, COUNT(*) chps 
  FROM books b
  JOIN chapters c ON c.book_id = b.id
  GROUP BY b.name
  ORDER BY b.sort_order");

$fh = fopen('sitemap.txt', 'w');
fputs($fh, "https://rcv.ramseyer.dev/concordance\nhttps://rcv.ramseyer.dev/search\nhttps://rcv.ramseyer.dev/help\nhttps://rcv.ramseyer.dev/verse\n");

foreach($books as $book)
  foreach(range(1, $book[chps]) as $chp)
    fputs($fh, "https://rcv.ramseyer.dev/bible/$book[name]/$chp\n");

fclose($fh);
