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
define("LOCAL", $_SERVER['HTTP_HOST'] !== getenv("DOMAIN"));
define("STATS", !$no_stats && !isset($_GET['no_track']));
define("COMMIT_HASH", `git log -1 --pretty=format:%h`);

function db() {
    static $db;
    if (!$db) {
		$db = new SQLite3($_SERVER['DOCUMENT_ROOT']."/extras/sqlite3/rcv.db");
    }
    return $db;
}

require $_SERVER['DOCUMENT_ROOT']."/inc/functions.php";

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
	$serif_text = $_SESSION['sans'] != 'true';
	if ($_GET['set_sans'] == 'true') {
		$_SESSION['sans'] = true;
		$serif_text = false;
	}
	if ($_GET['set_sans'] == 'false') {
		unset($_SESSION['sans']);
		$serif_text = true;
	}
	if (isset($_GET['set_sans']) || isset($_GET['set_minimal']) || isset($_GET['set_theme'])) {
		// redirect to same page
		redirect(strtok($_SERVER['REQUEST_URI'], '?'));
	}

	// random page
	if (isset($_GET['random'])) {
		/*
		 * select a book at 'random':
		 *   1. weight NT books + Psalms a little higher
		 *   2. pick random chapter from book in step 1
		 *   3. pick random verse from chapter in step 2
		 * result: verses in shorter books have a much higher chance of getting selected, but whatever
		 */
		$book = row("
			SELECT id, b.name, 
				CASE WHEN b.testament = 1 OR b.id = 19 THEN 1.08 ELSE 1 END * RANDOM() weight
			FROM books b
			ORDER BY weight DESC
			LIMIT 1");
		$chapter = row("SELECT number, id FROM chapters WHERE book_id = $book[id] ORDER BY RANDOM() LIMIT 1");
		$verse = row("SELECT id FROM chapter_contents WHERE chapter_id = $chapter[id] AND number ORDER BY RANDOM() LIMIT 1");
		redirect("/bible/".link_book($book['name'])."/$chapter[number]#verse-$verse[id]");
	}

	unset($_GET['set_theme'], $_GET['set_serif'], $_GET['set_minimal']);
	session_write_close();
}

require $_SERVER['DOCUMENT_ROOT']."/inc/url.php";
