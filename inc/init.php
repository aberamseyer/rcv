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

$db = LOCAL || isset($_GET['abe'])
	? mysqli_connect('database', 'docker', 'docker', $_GET['db'] ?: 'rcv', '3306')
	: mysqli_connect('127.0.0.1',  'rcv_app', '0XgOQnAKU6Mz6ja6', 'rcv');

require $_SERVER['DOCUMENT_ROOT']."/inc/functions.php";

if (!$no_stats || isset($_GET['no_track'])) {
	require "vendor/autoload.php";
	$redis_client = new Predis\Client([ 'host' => LOCAL ? 'redis' : '127.0.0.1' ]);

	$redis_client->incr("rcv.ramseyer.dev/stats/monthly-views/".date('Y-m'));
	$redis_client->incr("rcv.ramseyer.dev/stats/weekly-views/".date('Y')."-week-".date('W'));
	$redis_client->incr("rcv.ramseyer.dev/stats/daily-views/".date('Y-m-d'));
}

if (!$_POST['action']) {
	ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 2);
	session_start();

	$light_theme = $_SESSION['theme'] == 'light';
	if ($_GET['set_theme'] == 'light') {
		$_SESSION['theme'] = 'light';
		$light_theme = true;
	}
	if ($_GET['set_theme'] == 'dark') {
		unset($_SESSION['theme']);
		$light_theme = false;
	}

	$minimal_layout = $_SESSION['minimal'] == 'true';
	if ($_GET['set_minimal'] == 'true') {
		$_SESSION['minimal'] = true;
		$minimal_layout = true;
	}
	if ($_GET['set_minimal'] == 'false') {
		unset($_SESSION['minimal']);
		$minimal_layout = false;
	}

	$serif_text = $_SESSION['serif'] == 'true';
	if ($_GET['set_serif'] == 'true') {
		$_SESSION['serif'] = true;
		$serif_text = true;
	}
	if ($_GET['set_serif'] == 'false') {
		unset($_SESSION['serif']);
		$serif_text = false;
	}

	session_write_close();
	unset($_GET['set_theme'], $_GET['set_serif'], $_GET['set_minimal']);
}
