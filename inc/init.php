<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-17
 * Time: 09:05
 */

error_reporting(E_ALL^E_NOTICE^E_WARNING);

$time = microtime(true);
if (strpos(getenv("DOMAIN"), $_SERVER['HTTP_HOST']) !== false) {
	define("LOCAL", true);
	apache_setenv("DOMAIN", $_SERVER['HTTP_HOST']);
}
else {
	define("LOCAL", false);
}

define("HEROKU", strpos(getenv("DOMAIN"), "heroku") !== false);
define("COMMIT_HASH", HEROKU
	? `git log -1 --pretty=format:%h`
	: "");

require $_SERVER['DOCUMENT_ROOT']."/inc/functions.php";

# phpinfo();
# die;
ini_set('session.gc_maxlifetime', 60*60*24*14); // seconds in 2 weeks
require_once "session.php";
session_set_save_handler(new MySessionHandler(), true);
session_start();

if (!LOCAL && !$_SESSION['user'] && !$login && !$insecure) {
	redirect('/login?thru='.urlencode($_SERVER['REQUEST_URI']));
}

if (!$_POST['action']) {

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

	if (!$login)
		session_write_close();
}

require $_SERVER['DOCUMENT_ROOT']."/inc/url.php";
