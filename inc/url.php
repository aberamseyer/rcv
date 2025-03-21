<?php
/**
 * This file validates urls. If they're not valid, redirects to 404 page
 */

// to keep permalinks normal, make all our urls lowercase and with '-'
// this is for you, Google
$uri = strtok($_SERVER['REQUEST_URI'], '?');
if ($uri !== strtolower($uri) || $uri !== str_replace('_', '-', $uri)) {
	perm_redirect(str_replace('_', '-', strtolower($uri)));
}

$parts = explode('/', strtok($_SERVER['REQUEST_URI'], '?'));
if (!in_array(
	$parts[1],
	[ '', 'ajax', 'bible', 'search', 'login', 'help', 'verse', 'concordance', '404', 'release-notes', 'language-concordance' ], true)
) {
	not_found();
}
else if ($parts[1] === 'bible') {
	if (count($parts) > 4) {
		not_found();
	}
	else if ($parts[3] && intval($parts[3]) != $parts[3]) {
		not_found();
	}

	$bible_page = valid_bible_page($parts[2], $parts[3]);
	if (!$bible_page) {
		not_found();
	}

	// now you have $bible_page[ 'book' => book_name, 'chapter' => chapter_if_it_exists ]
}
else if ($parts[1] === '') {
	perm_redirect('/bible');
}

// ..otherwise we're good