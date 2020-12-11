<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-17
 * Time: 09:05
 */
$time = microtime(true);
error_reporting(E_ALL^E_NOTICE);
define("LOCAL", $_SERVER['HTTP_HOST'] !== 'rcv.ramseyer.dev');

$db = LOCAL || isset($_GET['abe'])
	? mysqli_connect(LOCAL ? '10.0.1.23' : '127.0.0.1', 'bible_test', 'SAaJ53xzPwpLgvl4', 'bible_test')
	: mysqli_connect('127.0.0.1',  'rcv_app', '0XgOQnAKU6Mz6ja6', 'rcv');

require "functions.php";

if (!LOCAL) {
	if (!$no_stats || isset($_GET['no_track'])) {
		require "vendor/autoload.php";
		$redis_client = new Predis\Client([
			'host' => '127.0.0.1'
		]);

		$redis_client->incr("rcv.ramseyer.dev/ips/".$_SERVER['REMOTE_ADDR']);
		$redis_client->incr("rcv.ramseyer.dev/stats/monthly-views/".date('Y-m'));
		$redis_client->incr("rcv.ramseyer.dev/stats/weekly-views/".date('Y')."-week-".date('W'));
		$redis_client->incr("rcv.ramseyer.dev/stats/daily-views/".date('Y-m-d'));
	}
}

$old = select("SELECT *, UPPER(name) ucName, UPPER(abbreviation) ucAbbr FROM books WHERE testament = 0 ORDER BY sort_order");
$new = select("SELECT *, UPPER(name) ucName, UPPER(abbreviation) ucAbbr FROM books WHERE testament = 1 ORDER BY sort_order");
$books = array_merge($old, $new);

$book = null;
$chapter = null;
$footnotes = null;


$q_book = strtoupper($_GET['book']);
if ($q_book && ($index = array_search($q_book, array_column($books, 'ucName'), true)) !== false) {
	$book = $books[$index];
}
else if ($q_book && ($index = array_search($q_book, array_column($books, 'ucAbbr'))) !== false) {
	$book = $books[$index];
}

if ($book) {
	$chapters = select("SELECT * FROM chapters WHERE book_id = $book[id]");
	$q_chapter = $_GET['chapter'];
	if ($q_chapter && in_array($q_chapter, array_column($chapters, 'number'))) {
		$chapter = row("SELECT * FROM chapters WHERE book_id = $book[id] AND number = ".db_esc($q_chapter));
	}
}
if ($chapter) {
	$contents = array_column(
		select("SELECT * FROM chapter_contents WHERE chapter_id = $chapter[id] ORDER BY sort_order"),
		null,
		'id'
	);
	$verse_ids = array_column($contents, 'id');
	$notes = select("SELECT * FROM footnotes WHERE verse_id IN(".implode(',', $verse_ids).") ORDER BY number");

	$footnotes = $cross_refs = [];
	foreach($contents as &$content) {
		$content['notes'] = [
			'cr' => [],
			'fn' => []
		];
	}
	unset($content);
	foreach($notes as $note) {
		if ($note['cross_reference']) {
			$contents[ $note['verse_id'] ]['notes']['cr'][] = $note;
			$cross_refs[] = $note;
		}
		if ($note['note']) {
			$contents[ $note['verse_id'] ]['notes']['fn'][] = $note;
			$footnotes[] = $note;
		}
	}
}

if (!$_POST['action']) {
	// server should keep session data for AT LEAST 1 hour
	ini_set('session.gc_maxlifetime', 86400);

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
	session_write_close();
	unset($_GET['set_theme']);
}
