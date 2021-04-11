<?php
	const copyright = "<div class='copy'><a rel='nofollow' href='/login' tabindex='-1'>All</a> content accessed from the Holy Bible Recovery Version &copy; 2003 Living Stream Ministry <a target='_blank' rel='nofollow' href='https://www.lsm.org'>www.lsm.org</a></div>";

	define('books', str_replace(' ', '[ ]', 'Gen\.|Exo\.|Num\.|Lev\.|Deut\.|Judg\.|Ruth|1 Sam\.|2 Sam\.|Josh\.|1 Kings|2 Kings|1 Chron\.|2 Chron\.|Ezra|Neh\.|Job|Esth\.|Psa\.|Prov\.|Eccl\.|S\.S\.|Isa\.|Jer\.|Lam\.|Ezek\.|Hosea|Dan\.|Joel|Obad\.|Zeph\.|Jonah|Amos|Micah|Hab\.|Hag\.|Nahum|Zech\.|Mal\.|Matt\.|Mark|Luke|John|1 Cor\.|2 Cor\.|Rom\.|Acts|Gal\.|Col\.|1 Thes\.|Eph\.|Phil\.|2 Tim\.|James|2 Thes\.|1 Tim\.|3 John|Titus|1 Pet\.|2 Pet\.|Jude|Rev\.|Philem\.|2 John|1 John|Heb\.'));
	define('book_re', 
	'/
	(?<book>'.books.')?        # book name
	(?:
	  [ \xA0]?      # semi-colon separating chapter\verse fields or a space\nbsp
	  (?<chapter>\d+): # non-optional chapter number
	  (?:
	    (?<vStart>\d+)[a-z]*      # verse start
	    (?:-
			(?<vEnd>\d+)[a-z]* # verse end
			(?!\d*:)			# dont match the next chapter in "11:2-12:3"
		)?
	    (?:,[ \xA0]+)?      # comma
	  )+
	)+
	/ix');
	define('s_book_re', 
	'/
	(?<book>\bv\.|\bvv\.|Obad\.|3[ ]John|Jude|Philem\.|2[ ]John) # book name
	(?:
	  [ \xA0]?      # semi-colon separating chapter\verse fields or a space\nbsp
	  (?:	# no chapter ":" here
	    (?<vStart>\d+)[a-z]*      # verse start
		(?:-
			(?<vEnd>\d+)[a-z]* # verse end
			(?!\d*:)			# dont match the next chapter in "11:2-12:3"
		)?
		(?:,[ \xA0]+)?      # comma
	  )+
	)+
	/ix');

	require_once __DIR__."/../inc/books.php";

	function valid_bible_page($book, $chapter = null) {
		global $books; // inc/books.php

		$book = link_book($book);
		$chapter = max($chapter, 0);
		foreach($books as $opt) {
			if ($opt['name'] == $book) {
				if ($chapter) {
					return $chapter <= $opt['chapters']
						? [ 'book' => $opt['id'], 'chapter' => $chapter ]
						: false;
				}
				else {
					return [ 'book' => $opt['id'], 'chapter' => 0 ];
				}
			}
		}
		return $book ? false : [ 'book' => 0, 'chapter' => 0 ];
	}
	
	function query ($query, $return = "") {
		$db = db();
		$result = mysqli_query($db, $query);
		if (!$result) {
			echo "<p><b>Warning:</b> A mysqli error occurred: <b>" . mysqli_error($db) . "</b></p>";
			debug($query);
		}
		if ($return == "insert_id")
			return mysqli_insert_id($db);
		if ($return == "num_rows")
			return mysqli_affected_rows($db);
		return $result;
	}

	function select ($query) {
		$rows = query($query);
		for ($result = []; $row = mysqli_fetch_assoc($rows); $result[] = $row);
		return $result;

	}

	function row ($query) {
		$results = select($query);
		return $results[0];
	}

	function col ($query) {
		$row = mysqli_fetch_row(query($query));
		return $row ? $row[0] : null;
	}

	function cols ($query) {
		$rows = query($query);
		if ($rows) {
			$results = [];
			while ($row = mysqli_fetch_array($rows))
				$results[] = $row[0];
			return $results;
		}
		return null;
	}

	function format_db_vals ($db_vals, array $options = []) {
		$options = array_merge([
			"source" => $_POST
		], $options);
		return map_assoc(function ($col, $val) use ($options) {

			// Was a value provided for this column ("col" => "val") or not ("col")?
			$no_value_provided = is_int($col);
			if ($no_value_provided)
				$col = $val;

			// The modifiers should not contain regex special characters. If they do, then we will have to use preg_quote().
			$modifiers = [
				"nullable" => "__",
				"literal" => "##"
			];

			// Check for column modifiers
			if (preg_match("/^(" . implode("|", $modifiers) . ")/", $col,$matches))
				$col = substr($col, 2);

			// Keep track of whether each modifier is present (true) or not
			$modifiers = map_assoc(function ($name, $symbol) use ($matches) {
				return [$name => $matches && $matches[1] == $symbol];
			}, $modifiers);

			$val = $no_value_provided ? $options["source"][$col] : $val;
			// If it's not literal, then transform the value
			if (!$modifiers["literal"])
				$val = $modifiers["nullable"] && ($val === null || $val === false || $val === 0 || !strlen($val))
					? "NULL"
					: ("'" . db_esc($val) . "'");

			return [ $col => $val ];
		}, $db_vals);
	}

	function get_num_params (callable $callback) {
		try {
			return (new ReflectionFunction($callback))->getNumberOfParameters();
		}
		catch (ReflectionException $e) {}
	}

	function map_assoc (callable $callback, array $arr) {
		$ret = [];
		foreach($arr as $k => $v) {
			$u =
				get_num_params($callback) == 1
					? $callback($v)
					: $callback($k, $v);
			$ret[key($u)] = current($u);
		}
		return $ret;
	}

	/**
	 * @param $table
	 * @param $vals	array	An associative array of columns and values to update.
	 * 						Each value will be converted to a string UNLESS its
	 * 						corresponding column name begins with "__", in which
	 *						case its literal value will be used.
	 * @param $where
	 */
	function update ($table, $vals, $where) {
		$SET = array();
		foreach (format_db_vals($vals) as $col => $val) {
			$col = preg_replace("/^__/", "", $col, 1, $use_literal);
			$SET[] = "$col = $val";
		}

		query("
			UPDATE $table
			SET " . implode(",", $SET) . "
			WHERE $where
		");
	}

	function insert ($table, array $db_vals, array $options = []) {
		$db_vals = format_db_vals($db_vals, $options);
		return query("
			INSERT INTO $table (" . implode(", ", array_keys($db_vals)) . ")
			VALUES (" . implode(", ", array_values($db_vals)) . ")
		", "insert_id");
	}

	function num_rows ($query) {
		return mysqli_num_rows(query($query));
	}

	function html ($str, $lang_flag = ENT_HTML5) {
		return htmlspecialchars($str, ENT_QUOTES|$lang_flag);
	}

	function debug() {
		$args = func_get_args();
		$num_args = count($args);
		if (!$num_args)
			die("<pre><b>No arguments passed to debug()!</b></pre>");

		$output = [];
		$die = $args[0] !== "NO_DIE";
		if (!$die) {
			array_shift($args);
			--$num_args;
		}

		// Loop through arguments
		foreach ($args as $i => $arg) {
			$var_log_msg = "<pre><b>" . ($num_args > 1 ? "Argument <i>" . ($i + 1) . "</i> of <i>$num_args</i><br>" : "") . "Type: <i>(" . gettype($arg) . ")</i></b><br>";
			if (is_bool($arg))
				$var_log_msg .= $arg ? "TRUE" : "FALSE";
			else
				$var_log_msg .= html(print_r($arg, 1));
			$var_log_msg .= "</pre>";
			$output[] = $var_log_msg;
		}
		if ($die)
			$output[] = "<pre><b>Ending script execution</b></pre>";
		else
			$output[] = "<pre><b>NO_DIE passed as the first argument to debug(); continuing execution now</b></pre>";

		echo "
			<div style='border:2px dashed red;margin:20px 0;padding:0 10px'>
				<pre style='font-size:18px;font-weight:bold'>debug() output beginning:</pre>
				<hr>
				" . implode("<hr>", $output) . "
				<hr>
				<pre style='font-size:18px;font-weight:bold'>Stack trace:</pre>
				<pre>";
		debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
		echo "
				</pre>
			</div>
		";

		if ($die)
			die;
	}

	function db_esc ($string) {
		$db = db();
		return mysqli_real_escape_string($db, $string);
	}

	function db_esc_like ($string) {
		return db_esc(str_replace(
			["\\", "_", "%"],
			["\\\\", "\\_", "\\%"],
			$string
		));
	}

	function redirect($url) {
		header("Location: $url");
		die;
	}

	function perm_redirect($url) {
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: $url");
		die;
	}

	function format_verse($element) {
		global $book;

		// split verse into individual characters
		$content = $element['content'];
		$arr = str_split(str_replace("\r\n", "\n", $content));
		$heading_class = 'verse';

		// adding a heading class to outline points
		if ($element['number'] == 0) {
			if ($element['tier']) {
				$heading_class = 'h'.$element['tier'];
			}
			else {
				$heading_class = 'title';
			}
		}

		// splice in footnotes and CFs
		if ($element['notes']) {
			foreach($element['notes']['cr'] as $i => $note) {
				$pos = $note['position'];
				foreach(explode(',', $note['position']) as $pos) {
					array_splice($arr, $pos, 1,
						"<sup class='tooltip' data-content='$note[letter]' tabindex='-1'><span class='right'>".format_note($note['cross_reference'], false)."</span></sup>".
						$arr[ $pos ]);
				}
			}
			foreach($element['notes']['fn'] as $i => $note) {
				foreach(explode(',', $note['position']) as $pos) {
					array_splice($arr, $pos, 1,
						"<sup><span class='right'><a href='#fn-$note[id]' class='fn' data-content='$note[number]' tabindex='-1'></a></span></sup>".
						$arr[ $pos ]);
				}
			}
		}
		// 'verse-line' creates separate lines in the verse
		$content = explode("\n", implode('', $arr));
		foreach($content as &$el) {
			$attr = "";
			if ($element['tier'])
				$attr = "data-note";
			$el = "<span class='verse-line' $attr>".$el."</span>";
		}
		unset($el);
		$content = implode('', $content);

		// on regular verses, add a verse number and play button
		$parts = [ "<p id='verse-$element[id]' class='$heading_class' data-ref='$element[reference]'>" ];
		if ($element['number'])
			$parts[] = "<span><a href='/bible/".link_book($book['name'])."' class='verse-number'>$element[number]</a></span>";
		$parts[] = "<span>$content</span>";
		if ($element['number'])
			$parts[] = "<span><a class='play' onclick='startReading($element[id])'>&#8227;</a></span>";
		$parts[] = "</p>";

		// right-align "Selah"s, including the footnotes attached to them
		$with_right_aligned_selahs = preg_replace_callback('/(?:<sup>.*<\/sup>)?Selah/i', function($matches) {
			return "<span style='float: right;'>".$matches[0]."</span>";
		}, implode('', $parts));

		// wrap words with a matching footnote/cf in a span to keep words attached to their superscript
		$with_no_breaks = preg_replace_callback('/<sup.*?<\/sup>[a-zA-Z"\'(]+(?:[.,;:!?\'"()])?/i', function($matches) {
			return "<span class='no-break'>".$matches[0]."</span>";
		}, $with_right_aligned_selahs);

		return $with_no_breaks;
	}

	function format_note($note, $break = true) {
		$note = html($note);
		$content = add_links($note);

		return $break ? nl2br($content) : $content;
	}

	function add_links($note) {
		global $book, $chapter, $books_by_abbr /* inc/books.php */;
		$prev_book = $book['name'];
		$prev_chp = $chapter['number'];

		$work = []; // holds all the matches we find
		preg_match_all(book_re, $note, $book_matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
		preg_match_all(s_book_re, $note, $s_book_matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
		$book_matches = array_merge($book_matches, $s_book_matches);
		usort($book_matches, fn($a, $b) => $a[0][1] <=> $b[0][1]); // sort by position in the note ascending 
		foreach($book_matches as $i => $book_match) {
			if ($i && 
				!preg_match('/^[ ;\xA0,]+$/', 
					substr($note,
						$pos = (
							$book_matches[$i-1][0][1] + strlen($book_matches[$i-1][0][0]) // position at end of prev match
						),
						$book_match[0][1] - $pos // length of characters in between curr and prev match
					)
				)
			) {
				// aka "if not the characters in between the matches are not strictly whitespace or certain punctuation"
				// then we should not default back to the page's book and chapter number
				$prev_book = $book['name'];
				$prev_chp = $chapter['number'];
			}
			$book_str_with_chp_and_verses = $book_match[0][0];
			preg_match('/(?<book>'.books.').*/i', $book_str_with_chp_and_verses, $capture_book_match);
			if (!$capture_book_match) {
				if (in_array($book_match['book'][0], ['vv.', 'v.'], true)) // special case here with vv. and v.
					$curr_book = $book['name'];
				else
					$curr_book = $prev_book;
			}
			else {
				$curr_book = $books_by_abbr[ $capture_book_match['book'] ];
			}
			$prev_book = $curr_book;
			$book_offset = $book_match[0][1];

			preg_match_all(
				'/
				(?:
				  (?:'.books.')
				  [ \xA0]+
				)?
				(?:
					(?<chp>\d+):|
					(?<sngl>\d+)|vv\.)
					(?<verses>[^;\n]*
				)
				/ix', $book_str_with_chp_and_verses, $chp_matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
			foreach($chp_matches as $chapter_match) {
				$curr_chapter = $chapter_match['chp'][0];
				$chp_offset = $chapter_match['chp'][1] + strlen($curr_chapter);
				$verse_str = $chapter_match['verses'][0];

				if ($chapter_match['sngl'][0]) {
					if (in_array($book_match['book'][0], ['vv.', 'v.'], true)) { // special case here with vv. and v.
						$curr_chapter = $chapter['number'];
					}
					else {
						$curr_chapter = 1;
					}
					$verse_str = $chapter_match['sngl'][0];
					$chp_offset = $chapter_match['sngl'][1];
				}
				else {
					$chp_offset++; // accounts for ':' or a -1 offset
				}
				if (!$curr_chapter) {
					$curr_chapter = $prev_chp;
				}
				$prev_chp = $curr_chapter;

				preg_match_all('/(?<start>\d+)(?:-(?<end>\d+))?/', $verse_str, $verse_range_matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
				foreach($verse_range_matches as $verse_match) {
					$rough_offset = $book_offset + $chp_offset + $verse_match[0][1];
					$offset = strpos($note, $verse_match[0][0], $rough_offset);
					$text = $verse_match[0][0];
					$work[] = [
						'offset' => $offset,
						'text' => $text,
						'link' => "<a href='/bible/".link_book($curr_book)."/".$curr_chapter."?verse=".$verse_match['start'][0]."'>".$text."</a>",
					];
				}
			}
		}

		// work backward to maintiain offsets
		foreach(array_reverse($work) as $item) {
			$note = substr_replace($note, $item['link'], $item['offset'], strlen($item['text']));
		}
		
		static $href_id_map;
		if (!$href_id_map) {
			$href_id_map = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT']."/extras/href_id_map.json"), true);
		}
		$note = preg_replace_callback( // replaces query-string ?verse={number} with the #verse-{id}
			'/[\'"](?<href>\/bible\/(?:\d-)?[a-z\-]+\/\d+)\?verse=\d+[\'"]/i',
			function($matches) use ($href_id_map) {
				if ($id = $href_id_map[ preg_replace('/[\'"]/', '', $matches[0]) ])
					return "'".$matches['href'].'#verse-'.$id."' verse-hover ";
				else
					return $matches[0]; // default to no replacement (should never happen)
			}, $note);

		return $note;
	}

	function nav_line($no_top = false, $attr = "") {
		global $book, $chapter, $search, $concordance;
		$next = $prev = '';

		$parts = [
			"<a href='/bible'>Books</a>",
			"<a href='/search'>Search</a>",
		];
		if (!$no_top) {
			$parts[] = "<a href='#top'>Top</a>";
		}
		$parts[] = "<a href='/concordance'>Concordance</a>";
	  	$parts[] = "<a href='/verse'>Verse Lookup</a>";
		$parts[] = "<a href='/help'>Help</a>";
		if ($book) {
			if ($chapter) {
				$parts[] = "<a href='/bible/".link_book($book['name'])."'>Chapters</a>";
			}

			if (!$search) {
				if ($chapter) {
					// viewing chapter, nav arrows change chapter
					$next = $book['chapters'] > $chapter['number']
						? "<a class='nav-arr' href='/bible/".link_book($book['name'])."/".($chapter['number']+1)."' rel='next'>&raquo;</a>"
						: "<div></div>";
					$prev = $chapter['number'] > 1
						? "<a class='nav-arr' href='/bible/".link_book($book['name'])."/".($chapter['number']-1)."' rel='prev'>&laquo;</a>"
						: "<div></div>";
				}
				else {
					list($prev_book, $next_book) = select("
						SELECT * FROM books
						WHERE sort_order IN(".($book['sort_order']-1).",".($book['sort_order']+1).")
						ORDER BY sort_order");
					// viewing book, nav arrows change book
					if (!$next_book) {
						// genesis or revelation
						if ($book['name'] == 'Genesis') {
							$next = "<a class='nav-arr' href='/bible/".link_book($prev_book['name'])."' rel='next'>&raquo;</a>";
							$prev = "<div></div>";
						}
						else {
							$prev = "<a class='nav-arr' href='/bible/".link_book($prev_book['name'])."' rel='prev'>&laquo;</a>";
							$next = "<div></div>";
						}
					}
					else {
						$next = "<a class='nav-arr' href='/bible/".link_book($next_book['name'])."' rel='next'>&raquo;</a>";
						$prev = "<a class='nav-arr' href='/bible/".link_book($prev_book['name'])."' rel='prev'>&laquo;</a>";
					}
				}
			}
		}
		return "<nav class='justify' $attr>$prev <div>".implode(" | ", $parts)."</div> $next</nav>";
	}

	function not_found() {
		redirect("/404?uri=https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
	}

	function link_book($book) {
		return str_replace(' ', '-', strtolower($book));
	}

	function print_json($arr) {
		header("Content-type: application/json");
		echo json_encode($arr);
		die;
	}