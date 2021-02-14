<?php
	const copyright = "<div class='copy'>All content accessed from the Holy Bible Recovery Version &copy; 2003 Living Stream Ministry <a target='_blank' rel='nofollow' href='https://www.lsm.org'>www.lsm.org</a></div>";
	const verse_regex = '/(Gen\.|Exo\.|Num\.|Lev\.|Deut\.|Judg\.|Ruth|1 Sam\.|2 Sam\.|Josh\.|1 Kings|2 Kings|1 Chron\.|2 Chron\.|Ezra|Neh\.|Job|Esth\.|Psa\.|Prov\.|Eccl\.|S\.S\.|Isa\.|Jer\.|Lam\.|Ezek\.|Hosea|Dan\.|Joel|Obad\.|Zeph\.|Jonah|Amos|Micah|Hab\.|Hag\.|Nahum|Zech\.|Mal\.|Matt\.|Mark|Luke|John|1 Cor\.|2 Cor\.|Rom\.|Acts|Gal\.|Col\.|1 Thes\.|Eph\.|Phil\.|2 Tim\.|James|2 Thes\.|1 Tim\.|3 John|Titus|1 Pet\.|2 Pet\.|Jude|Rev\.|Philem\.|2 John|1 John|Heb\.) (\d+):(\d+)/';

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
		global $db;
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
		global $db;
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
		global $book, $minimal_layout;

		$content = $element['content'];
		$arr = str_split($content);
		$heading_class = 'verse';
		if ($element['number'] == 0) {
			if ($element['tier']) {
				$heading_class = 'h'.$element['tier'];
			}
			else {
				$heading_class = 'title';
			}
		}

		if (!$minimal_layout && $element['notes']) {
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
		$content = explode("\n", implode('', $arr));
		foreach($content as &$el) {
			$style = "";
			if ($element['tier'])
				$style = "style='padding-left: ".max(0, $element['tier'])."rem;'";
			$el = "<span class='verse-line' $style>".$el."</span>";
		}
		unset($el);
		$content = implode('', $content);

		return "<p id='verse-$element[id]' class='$heading_class' data-ref='$element[reference]'>".
			($element['number'] ? "<a href='/bible/".link_book($book['name'])."' class='verse-number'>$element[number]</a>
			    <a class='play' onclick='startReading($element[id])'>&#8227;</a>" : "").$content."</p>";
	}

	function format_note($note, $break = true) {
		global $books_by_abbr; // inc/books.php

		$note = html($note);
		$content = preg_replace_callback(verse_regex, function($matches) use ($books_by_abbr) {
			return "<a href='/bible/".link_book($books_by_abbr[ $matches[1] ])."/$matches[2]?verse=$matches[3]'>$matches[0]</a>";
		}, $note);
		return $break ? nl2br($content) : $content;
	}

	function nav_line($no_top = false) {
		global $book, $chapter, $search, $concordance;
		static $i;
		$next = $prev = '';

		$parts = [
			"<a href='/bible'>Books</a>",
			"<a href='/search'>Search</a>",
		];
		if (!$no_top) {
			$parts[] = "<a href='#top'>Top</a>";
		}
		// if (!$book && !$concordance) {
			$parts[] = "<a href='/concordance'>Concordance</a>";
		  $parts[] = "<a href='/verse'>Verse Lookup</a>";
		// }
		$parts[] = "<a href='/help'>Help</a>";
		if ($book) {
			if ($chapter) {
				$parts[] = "<a href='/bible/".link_book($book['name'])."'>Chapters</a>";
			}

			if (!$search) {
				if ($chapter) {
					// viewing chapter, nav arrows change chapter
					if ($book['chapters'] > $chapter['number']) {
						$next = "<a class='nav-arr' href='/bible/".link_book($book['name'])."/".($chapter['number']+1)."' rel='next'>&raquo;</a>";
					}
					if ($chapter['number'] > 1) {
						$prev = "<a class='nav-arr' href='/bible/".link_book($book['name'])."/".($chapter['number']-1)."' rel='prev'>&laquo;</a>";
					}
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
							$next = "<a class='nav-arr' href='/bible/".link_book($prev_book['name'])."'>&raquo;</a>";
						}
						else {
							$prev = "<a class='nav-arr' href='/bible/".link_book($prev_book['name'])."'>&laquo;</a>";
						}
					}
					else {
						$next = "<a class='nav-arr' href='/bible/".link_book($next_book['name'])."'>&raquo;</a>";
						$prev = "<a class='nav-arr' href='/bible/".link_book($prev_book['name'])."'>&laquo;</a>";
					}
				}
			}
		}
		return "<nav id='nav-".($i++)."' class='justify'>$prev <div>".implode(" | ", $parts)."</div> $next</nav>";
	}

	function not_found() {
		redirect("/404?uri=https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
	}

	function link_book($book) {
		return str_replace(' ', '-', strtolower($book));
	}
