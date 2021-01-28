<?php
/**
 * This file validates urls. If they're not valid, redirect back to /bible
 */

// to keep things sane in redis, make all our urls lowercase
$uri = strtok($_SERVER['REQUEST_URI'], '?');
if ($uri !== strtolower($uri)) {
	redirect(strtolower($uri));
}

$parts = explode('/', strtok($_SERVER['REQUEST_URI'], '?'));
if (!in_array(
	$parts[1],
	[ 'ajax', 'bible', 'search', 'admin', 'login', 'help', 'verse', 'concordance' ])
) {
	redirect("/bible");
}
else if ($parts[1] === 'bible') {
	if (count($parts) > 4) {
		redirect("/bible");
	}
	else if ($parts[3] && intval($parts[3]) != $parts[3]) {
		redirect("/bible");
	}

	$bible_page = valid_bible_page($parts[2], $parts[3]);
	if (!$bible_page) {
		redirect("/bible");
	}

	// now you have $page[ 'book' => book_name, 'chapter' => chapter_if_it_exists ]
}

// ..otherwise we're good