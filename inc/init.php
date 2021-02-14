<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-17
 * Time: 09:05
 */

error_reporting(E_ALL^E_NOTICE);
ini_set('post_max_size', '512K');
ini_set('upload_max_filesize', '512K');

$time = microtime(true);
define("LOCAL", $_SERVER['HTTP_HOST'] !== 'rcv.ramseyer.dev');
define("STATS", !$no_stats && !isset($_GET['no_track']));

$db = LOCAL || isset($_GET['abe'])
	? mysqli_connect('database', 'docker', 'docker', $_GET['db'] ?: 'rcv', '3306')
	: mysqli_connect('127.0.0.1',  'rcv_app', '0XgOQnAKU6Mz6ja6', 'rcv');

require $_SERVER['DOCUMENT_ROOT']."/inc/functions.php";

// session starts out here so admin page can close the session on its own terms
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 2);
session_start();
if (!$_POST['action']) {
	// theme
	$light_theme = $_SESSION['theme'] == 'light';
	if ($_GET['set_theme'] == 'light') {
		$_SESSION['theme'] = 'light';
		$light_theme = true;
	}
	if ($_GET['set_theme'] == 'dark') {
		unset($_SESSION['theme']);
		$light_theme = false;
	}

	// show/hide notes
	$minimal_layout = $_SESSION['minimal'] == 'true';
	if ($_GET['set_minimal'] == 'true') {
		$_SESSION['minimal'] = true;
		$minimal_layout = true;
	}
	if ($_GET['set_minimal'] == 'false') {
		unset($_SESSION['minimal']);
		$minimal_layout = false;
	}

	// serif/sans font
	$serif_text = $_SESSION['serif'] == 'true';
	if ($_GET['set_serif'] == 'true') {
		$_SESSION['serif'] = true;
		$serif_text = true;
	}
	if ($_GET['set_serif'] == 'false') {
		unset($_SESSION['serif']);
		$serif_text = false;
	}
	if (isset($_GET['set_serif']) || isset($_GET['set_minimal']) || isset($_GET['set_theme'])) {
		// redirect to same page
		redirect(strtok($_SERVER['REQUEST_URI'], '?'));
	}

	// random page
	if (isset($_GET['random'])) {
		$book = row("
			SELECT id, name
			FROM books
			ORDER BY RAND()
			LIMIT 1");
		$chapter = row("
			SELECT id, number
			FROM chapters
			WHERE book_id = $book[id]
			ORDER BY RAND()
			LIMIT 1");
		$verse = row("
			SELECT id, number
			FROM chapter_contents
			WHERE chapter_id = $chapter[id] and reference IS NOT NULL
			ORDER BY RAND()
			LIMIT 1");
		redirect("/bible/".link_book($book['name'])."/$chapter[number]?verse=$verse[number]");
	}

	unset($_GET['set_theme'], $_GET['set_serif'], $_GET['set_minimal']);
	if (!$admin)
		session_write_close();
}

require $_SERVER['DOCUMENT_ROOT']."/inc/url.php";

// load redis after the customization and url.php so we don't track simple state changes and invalid url redirects
require "vendor/autoload.php";
$redis_client = new Predis\Client([ 'host' => LOCAL ? 'redis' : '127.0.0.1' ]);
if (STATS) {
	// page views
	$redis_client->incr("rcv.ramseyer.dev/stats/monthly-views/".date('Y-m'));
	$redis_client->incr("rcv.ramseyer.dev/stats/weekly-views/".date('Y')."-week-".date('W'));
	$redis_client->incr("rcv.ramseyer.dev/stats/daily-views/".date('Y-m-d'));

	// unique visitors
	$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?: $_SERVER['REMOTE_ADDR']; // ip comes through cloudflare or not
	$redis_client->hset("rcv.ramseyer.dev/stats/monthly-unique/".date('Y-m'), $ip, 1);
	$redis_client->hset("rcv.ramseyer.dev/stats/weekly-unique/".date('Y').'-week-'.date('W'), $ip, 1);
	$redis_client->hincrby("rcv.ramseyer.dev/stats/daily-unique/".date('Y-m-d'), $ip, 1);
}
