<?php
$no_stats = true;
require "init.php";

switch($_POST['action']) {
	case 'verse':
		$q = strtolower($_POST['q']);
		$ref_like = db_esc_like($q)."%";

		$results = select("
			SELECT b.abbreviation book, c.number chapter, cc.number verse, cc.content text
			FROM rcv.chapter_contents cc
			JOIN rcv.chapters c ON c.id = cc.chapter_id
			JOIN rcv.books b ON b.id = c.book_id
			WHERE cc.number != 0 AND (
				cc.reference LIKE '$ref_like' OR 
				REPLACE(cc.reference, '.', '') LIKE '$ref_like' OR
				LOWER(cc.content) LIKE '%$ref_like'
			)
			ORDER BY b.sort_order, c.id");
		$count = count($results);
		$results = array_slice($results, 0, 20);

		foreach($results as &$result) {
			if (strpos(strtolower($result['text']), $q) !== false) {
				$result['text'] = preg_replace("/(.*)($q)(.*)/is", "$1<span class='match'>$2</span>$3", $result['text']);
			}
		}
		unset($result);

		header("Content-type: application/json");
		echo json_encode([
			"q" => $_POST['q'],
			"count" => $count,
			"results" => $results
		]);
		die;
		break;
}
