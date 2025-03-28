<?php

if ($_REQUEST['action'] == 'check_update') {
	$insecure = true;
}

require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

function adjust_requested_elements($book, $chapter, $prev_chapter, $verses = "") {
	global $requested_elements, $whole_chapter, $is_single_book;
	static $prev_book;

	$separator = "; ";
	if ($book == $prev_book || !$book)
		$book = "";
	if ($prev_chapter == $chapter) {
		$chapter = "";
		$separator = ", ";
	}
	if ($is_single_book)
		$chapter = "";
	if ($verses && !$is_single_book && $chapter && !$whole_chapter)
		$chapter .= ":";
	if ($whole_chapter)
		$verses = "";

	$requested_elements .= ($requested_elements ? $separator : "").ltrim("$book ")."$chapter$verses";

	$prev_book = $book ?: $prev_book;
}

/**
 * $id: int the id of the verse we're retrieving
 * $type: foot|false whether this is an id of the footnote concordance or bible concordance
 */
function ajax_conc($id, $type) {
	if ($type === 'bible') {
		$rows = select("
			SELECT cc.reference, 0 number, '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number ||'#verse-' || cc.id href
			FROM bible_concordance_to_chapter_contents c2cc
			JOIN chapter_contents cc ON cc.id = c2cc.chapter_contents_id
			JOIN chapters c ON cc.chapter_id = c.id
			JOIN books b ON b.id = c.book_id
			WHERE c2cc.concordance_id = $id
			ORDER BY b.sort_order, c.number, cc.sort_order");
	}
	else { // $type === 'foot'
		$rows = select("
			SELECT cc.reference, f.number, '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#fn-' || f.id href
			FROM footnote_concordance_to_footnotes fc2f
			JOIN footnotes f ON f.id = fc2f.footnotes_id
			JOIN chapter_contents cc ON cc.id = f.verse_id
			JOIN chapters c ON cc.chapter_id = c.id
			JOIN books b ON b.id = c.book_id
			WHERE fc2f.footnote_concordance_id = $id
			ORDER BY b.sort_order, c.number, cc.sort_order");
	}

	return $rows;
}

/**
 * $q: string a verse string to parse and retrieve verses for
 */
function ajax_request_verse($q) {
	global $requested_elements;
	if ($q = ucwords(strtolower(trim($q)))) {
		$single_books = ["Obad.","3 John","Jude","Philem.","2 John"];
		$books = [
			[ 'name' => 'Genesis','abbreviation' => 'Gen.' ],
			[ 'name' => 'Exodus','abbreviation' => 'Exo.' ],
			[ 'name' => 'Leviticus','abbreviation' => 'Lev.' ],
			[ 'name' => 'Numbers','abbreviation' => 'Num.' ],
			[ 'name' => 'Deuteronomy','abbreviation' => 'Deut.' ],
			[ 'name' => 'Joshua','abbreviation' => 'Josh.' ],
			[ 'name' => 'Judges','abbreviation' => 'Judg.' ],
			[ 'name' => 'Ruth','abbreviation' => 'Ruth' ],
			[ 'name' => '1 Samuel','abbreviation' => '1 Sam.' ],
			[ 'name' => '2 Samuel','abbreviation' => '2 Sam.' ],
			[ 'name' => '1 Kings','abbreviation' => '1 Kings' ],
			[ 'name' => '2 Kings','abbreviation' => '2 Kings' ],
			[ 'name' => '1 Chronicles','abbreviation' => '1 Chron.' ],
			[ 'name' => '2 Chronicles','abbreviation' => '2 Chron.' ],
			[ 'name' => 'Ezra','abbreviation' => 'Ezra' ],
			[ 'name' => 'Nehemiah','abbreviation' => 'Neh.' ],
			[ 'name' => 'Esther','abbreviation' => 'Esth.' ],
			[ 'name' => 'Job','abbreviation' => 'Job' ],
			[ 'name' => 'Psalms','abbreviation' => 'Psa.' ],
			[ 'name' => 'Proverbs','abbreviation' => 'Prov.' ],
			[ 'name' => 'Ecclesiastes','abbreviation' => 'Eccl.' ],
			[ 'name' => 'Song of Songs','abbreviation' => 'S.S.' ],
			[ 'name' => 'Isaiah','abbreviation' => 'Isa.' ],
			[ 'name' => 'Jeremiah','abbreviation' => 'Jer.' ],
			[ 'name' => 'Lamentations','abbreviation' => 'Lam.' ],
			[ 'name' => 'Ezekiel','abbreviation' => 'Ezek.' ],
			[ 'name' => 'Daniel','abbreviation' => 'Dan.' ],
			[ 'name' => 'Hosea','abbreviation' => 'Hosea' ],
			[ 'name' => 'Joel','abbreviation' => 'Joel' ],
			[ 'name' => 'Amos','abbreviation' => 'Amos' ],
			[ 'name' => 'Obadiah','abbreviation' => 'Obad.' ],
			[ 'name' => 'Jonah','abbreviation' => 'Jonah' ],
			[ 'name' => 'Micah','abbreviation' => 'Micah' ],
			[ 'name' => 'Nahum','abbreviation' => 'Nahum' ],
			[ 'name' => 'Habakkuk','abbreviation' => 'Hab.' ],
			[ 'name' => 'Zephaniah','abbreviation' => 'Zeph.' ],
			[ 'name' => 'Haggai','abbreviation' => 'Hag.' ],
			[ 'name' => 'Zechariah','abbreviation' => 'Zech.' ],
			[ 'name' => 'Malachi','abbreviation' => 'Mal.' ],
			[ 'name' => 'Matthew','abbreviation' => 'Matt.' ],
			[ 'name' => 'Mark','abbreviation' => 'Mark' ],
			[ 'name' => 'Luke','abbreviation' => 'Luke' ],
			[ 'name' => 'John','abbreviation' => 'John' ],
			[ 'name' => 'Acts','abbreviation' => 'Acts' ],
			[ 'name' => 'Romans','abbreviation' => 'Rom.' ],
			[ 'name' => '1 Corinthians','abbreviation' => '1 Cor.' ],
			[ 'name' => '2 Corinthians','abbreviation' => '2 Cor.' ],
			[ 'name' => 'Galatians','abbreviation' => 'Gal.' ],
			[ 'name' => 'Ephesians','abbreviation' => 'Eph.' ],
			[ 'name' => 'Philippians','abbreviation' => 'Phil.' ],
			[ 'name' => 'Colossians','abbreviation' => 'Col.' ],
			[ 'name' => '1 Thessalonians','abbreviation' => '1 Thes.' ],
			[ 'name' => '2 Thessalonians','abbreviation' => '2 Thes.' ],
			[ 'name' => '1 Timothy','abbreviation' => '1 Tim.' ],
			[ 'name' => '2 Timothy','abbreviation' => '2 Tim.' ],
			[ 'name' => 'Titus','abbreviation' => 'Titus' ],
			[ 'name' => 'Philemon','abbreviation' => 'Philem.' ],
			[ 'name' => 'Hebrews','abbreviation' => 'Heb.' ],
			[ 'name' => 'James','abbreviation' => 'James' ],
			[ 'name' => '1 Peter','abbreviation' => '1 Pet.' ],
			[ 'name' => '2 Peter','abbreviation' => '2 Pet.' ],
			[ 'name' => '1 John','abbreviation' => '1 John' ],
			[ 'name' => '2 John','abbreviation' => '2 John' ],
			[ 'name' => '3 John','abbreviation' => '3 John' ],
			[ 'name' => 'Jude','abbreviation' => 'Jude' ],
			[ 'name' => 'Revelation','abbreviation' => 'Rev.' ]
		];

		$books_by_name = array_column($books, 'abbreviation', 'name');
		$books_by_abbr = array_column($books, 'abbreviation', 'abbreviation');

		$parsed_verses = [ ]; $ordered_verses = [ ];

		// split into separate books/verses
		$prev_book = null;
		$verses = explode(';', $q);
		$i = -1;
		foreach($verses as $verse) {
			$i++;
			$verse = trim($verse);
				
			preg_match('/((?>\d? )?\w+\.?) (.*)/i', $verse, $matches);
			list( , $book, $section_str ) = $matches;
			$book = trim($book);

			if (!$book && !$prev_book)
				continue;
				
			if ($books_by_abbr[$book] || $books_by_name[$book]) {
				$book = $books_by_abbr[$book] ?: $books_by_name[$book];
			}
			else {
				foreach($books as $to_check) {
					if (
						$book && // stripos was returning '0' for an empty string: php v8.2.3
							(stripos($to_check['name'], $book) === 0 ||
							stripos($to_check['abbreviation'], $book) === 0)
					) {
						$book = $to_check['abbreviation'];
					}
				}
			}

			if (!$book)
				$book = $prev_book;
			if (!$section_str)
				$section_str = $verse;
			

			$is_single_book = in_array($book, $single_books, true);

			$sections = explode(',', $section_str);

			$prev_chapter = null;

			// split into separate ranges of verses within a chapter
			foreach($sections as $section) {
					$section = trim($section);
					$whole_chapter = false;

					list( $chapter, $verse ) = explode(':', $section);
					if ($chapter && $verse) {
						// e.g., Rev. 3:5, 3:8
					}
					if (!$verse) {
						if ($prev_chapter) {
							// e.g., "Rev. 3:5, 8"
							$verse = $chapter;
							$chapter = $prev_chapter;
						}
						else {
								if ($is_single_book) {
									// e.g., "Jude 1"
									$verse = $chapter;
									$chapter = 1;
								}
								else {
									// e.g., "Rev. 3" - get all the verses
									if (
										$verses_in_chapter = col("
											SELECT verses
											FROM chapters c
											JOIN books b ON b.id = c.book_id
											WHERE number = ".intval($chapter)." AND b.abbreviation = '$book'")
									) {
										$whole_chapter = true;
										$verse = '1-'.$verses_in_chapter;
									}
								}
						}
					}
					$chapter = trim($chapter);
					$verse = trim($verse);
					if (!$verse) {
						$verse = $chapter;
						$chapter = $prev_chapter;
					}

					if (!$chapter && !$prev_chapter)
							continue;

					// detect a range of verses
					preg_match('/(\d+)-(\d+)/i', $verse, $matches);
					if ($matches && count($matches) === 3) {
						$low = (int) $matches[1];
						$high = (int) $matches[2];
						
						 // the longest chapter is 176 verses
						if ($low >= $high || $high - $low > 176)
							continue;

						adjust_requested_elements($book, $chapter, $prev_chapter, "$low-$high");

						while ($low <= $high) {
							$v = db_esc("$book ".($is_single_book ? '' : $chapter.":").$low++);
							$parsed_verses[] = "'$v'";
							$ordered_verses[] = $v;
						}
					}
					else {
						$v = db_esc("$book ".($is_single_book ? '' : (int)$chapter.":").(int)$verse);
						$parsed_verses[] = "'$v'";
						$ordered_verses[] = $v;
						adjust_requested_elements($book, $chapter, $prev_chapter, $verse);
					}
					
					$prev_chapter = $chapter;
			}

			$prev_book = $book;
		}

		// go get 'em
		$raw_verses = !count($parsed_verses) ? [ ] : array_column(
			select("
				SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href, cc.reference, REPLACE(cc.content, '\n', ' / ') text
				FROM chapter_contents cc
				JOIN chapters c ON c.id = cc.chapter_id
				JOIN books b ON b.id = c.book_id
				WHERE reference IN(".implode(',', $parsed_verses).")
				LIMIT 200"),
			null, 'reference');

		// this loop is so we get the verses in the same order that they were requested in
		$final_verses = [ ];
		foreach($ordered_verses as $ordered_verse) {
			if ($raw_verses[ $ordered_verse ])
				$final_verses[] = $raw_verses[ $ordered_verse ];
		}
	}
	return [
		'q' => $q,
		'requested' => $requested_elements,
		'results' => $final_verses ?: []
	];
}

function ajax_original_text($id) {
	return @json_decode(
		col("SELECT text_content FROM original_text WHERE id = ".$id,
			l_db()
		), true) ?: [];
}

switch($_REQUEST['action']) {
  // concordance to request list of verses that contain a word
	case 'conc':
		$rows = ajax_conc((int)$_POST['id'], $_POST['type'] === 'foot' ? 'foot' : 'bible');
		print_json($rows);
    break;
	// verse lookup page that parses a set of verses
	case 'request':
		$requested_elements = null;
		print_json(ajax_request_verse($_POST['q']));
		break;
	// hover a-tag link
	case 'a-verse':
		$id = (int) $_POST['id'];
		$start = $_POST['range'];
		$ids = [ $id ];
		if (preg_match('/\d+-\d+/', $_POST['range'])) {
			$numbers = range(...explode('-', $_POST['range']));
			$chp_id = col("SELECT chapter_id FROM chapter_contents WHERE id = ".$id);
			$ids = cols("SELECT id FROM chapter_contents WHERE chapter_id = ".$chp_id." AND number IN(".implode(',', $numbers).")");
		}

		$csl = implode(',', $ids);
		print_json($ids ? select("
			SELECT REPLACE(cc.content, '\n', ' / ') content, cc.reference, '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href
			FROM chapter_contents cc
			JOIN chapters c ON c.id = cc.chapter_id
			JOIN books b ON b.id = c.book_id
			WHERE cc.id IN($csl) ORDER BY c.number") : [ ]);
		break;
	// global verse search that pops up when you start typing
	case 'verse':
		$q = strtolower($_POST['q']);
		$ref_like = db_esc_like($q)."%";

		$results = select("
			SELECT cc.id verse_id, b.abbreviation abbr, LOWER(REPLACE(b.name, ' ', '-')) book, c.number chapter, cc.number verse, REPLACE(cc.content, '\n', ' / ') text
			FROM chapter_contents cc
			JOIN chapters c ON c.id = cc.chapter_id
			JOIN books b ON b.id = c.book_id
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

		$book_results = select("
			SELECT b.id book_id, LOWER(REPLACE(b.name, ' ', '-')) book_url, b.name book
			FROM books b
			WHERE book LIKE '%$ref_like'
			ORDER BY b.sort_order");

		print_json([
			"q" => $_POST['q'],
			"count" => $count,
			"results" => $results,
			"book_results" => $book_results
		]);
		break;
	// client checks to see if its running the latest local server code
	case 'check_update':
		if (strpos($_REQUEST['domain'], 'fly') === false) { // the javascript requester is running the code locally
			$local_version = trim(file_get_contents($_SERVER['DOCUMENT_ROOT']."/extras/date"));
			$release_version = '';
			if (!HEROKU) { // only a localhost machine should try to figure out the latest version
				$release_url = trim(
					@json_decode(
						@file_get_contents("https://rcv-eba.fly.dev/ajax?action=check_update&domain=localhost"), true)
						['url']
				);
				if ($release_url)
					$release_version = array_pop(explode('/', $release_url));

				if (strnatcmp($local_version, $release_version) >= 0) { // the version of the code running on this localhost is the same (or greater?) than the latest version as defined by a request to the heroku instance
					$release_version = '';
				}
			}
			else { // running on heroku, define the release_version as whatever we have
				$release_version = $local_version;
			}

			print_json([ 'url' => $release_version
				? 'https://s3.us-west-002.backblazeb2.com/rcv-eba/archives/'.$release_version
				: ''
			]);
		}
		else {	// the requester is making this from the herokuapp website
			print_json([ 'url' => '' ]);
		}

		break;
	// gets hebrew/greek text for a verse
	case 'original_text':
		print_json(ajax_original_text((int)$_REQUEST['id']));
		break;
}
