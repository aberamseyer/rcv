<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-16
 * Time: 18:01
 */

$search = true;
$title = "Search";
$meta_description = "Search in the Holy Bible Recovery Version for text, footnotes, cross-references, and outline points.";
$meta_canonical = "https://rcv.ramseyer.dev/search";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

$old = select("SELECT id, name, chapters FROM books WHERE testament = 0 ORDER BY sort_order");
$new = select("SELECT id, name, chapters FROM books WHERE testament = 1 ORDER BY sort_order");

$q = $_GET['q'];
$also = is_array($_GET['also']) ? $_GET['also'] : [ ];

$results = [];
if ($q) {
	$books = array_column(array_merge($old, $new), null, 'name');
	$book = $books[ $_GET['book'] ];
	if ($book) {
		$q_chp = (int) $_GET['chapter'];
		if ($q_chp > 0 && $q_chp <= $book['chapters']) {
			$chapter = row("SELECT * FROM chapters WHERE book_id = $book[id] AND number = $q_chp");
		}
	}

	$q_like = "'%".db_esc_like(strtolower($q))."%'";
	$only_old = $_GET['book'] === 'old';
	$only_new = $_GET['book'] === 'new';

	// keys of $results are used as headings in results
	$results['Verses'] = select("
		SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href, cc.reference a_tag, cc.content content
		FROM chapter_contents cc
		JOIN chapters c ON c.id = cc.chapter_id
		JOIN books b ON b.id = c.book_id
		WHERE LOWER(content) LIKE $q_like
		    AND cc.number > 0
			AND ".($book ? "b.id = '$book[id]'" : 1)."
			AND ".($only_old ? "b.testament = '0'" : 1)."
			AND ".($only_new ? "b.testament = '1'" : 1)."
			AND ".($chapter ? "c.id = '$chapter[id]'" : 1)."
        ORDER BY b.sort_order, c.number, cc.number");
	$num_results = count($results['Verses']);
	if ($also['out']) {
		$results['Outline'] = select("
		SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href, b.abbreviation || ' ' || c.number a_tag, cc.content content
		FROM chapter_contents cc
		JOIN chapters c ON c.id = cc.chapter_id
		JOIN books b ON b.id = c.book_id
		WHERE LOWER(content) LIKE $q_like
			AND reference IS NULL
			AND ".($book ? "b.id = '$book[id]'" : 1)."
			AND ".($only_old ? "b.testament = '0'" : 1)."
			AND ".($only_new ? "b.testament = '1'" : 1)."
			AND ".($chapter ? "c.id = '$chapter[id]'" : 1)."
        ORDER BY b.sort_order, c.number, cc.number, cc.sort_order");
		$num_results += count($results['Outline']);
    }
	if ($also['fn']) {
		$results['Footnote'] = select("
			SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#fn-' || f.id href, cc.reference a_tag, f.note content
			FROM footnotes f
			JOIN chapter_contents cc ON cc.id = f.verse_id
			JOIN chapters c ON c.id = cc.chapter_id
			JOIN books b ON b.id = c.book_id
			WHERE LOWER(note) LIKE $q_like
				AND ".($book ? "b.id = '$book[id]'" : 1)."
				AND ".($only_old ? "b.testament = '0'" : 1)."
				AND ".($only_new ? "b.testament = '1'" : 1)."
				AND ".($chapter ? "c.id = '$chapter[id]'" : 1)."
            ORDER BY b.sort_order, c.number, cc.number");
		$num_results += count($results['Footnote']);
	}
	if ($also['cr']) {
		$results['Cross Reference'] = select("
			SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href, cc.reference a_tag, f.cross_reference content
			FROM footnotes f
			JOIN chapter_contents cc ON cc.id = f.verse_id
			JOIN chapters c ON c.id = cc.chapter_id
			JOIN books b ON b.id = c.book_id
			WHERE LOWER(cross_reference) LIKE $q_like
				AND ".($book ? "b.id = '$book[id]'" : 1)."
				AND ".($only_old ? "b.testament = '0'" : 1)."
				AND ".($only_new ? "b.testament = '1'" : 1)."
				AND ".($chapter ? "c.id = '$chapter[id]'" : 1)."
            		ORDER BY b.sort_order, c.number, cc.number");
		$num_results += count($results['Cross Reference']);
	}
	if ($also['subj']) {
		$results['Book Subject'] = select("
			SELECT '/bible/' || LOWER(REPLACE(name, ' ', '-')) href, name a_tag, details content 
			FROM books
			WHERE LOWER(details) LIKE $q_like
				AND ".($book ? "id = '$book[id]'" : "1"));
		$num_results += count($results['Book Subject']);
	}
}

echo "<h1><a href='/bible'>Search".($q ? ": '".html($q)."'" : '')."</a></h1>";
?>
<style>
	.match {
		padding: 0 2px;
		-webkit-border-radius: 2px;
		-moz-border-radius: 2px;
		border-radius: 2px;
		color: #222222;
		background: #c9c9c9;
	}
	.result {
		margin: 0.8rem 0;
		line-height: 2.3rem;
	}
    input[name=q] {
        width: 100%;
    }
</style>
<form>
	Search <select name="book"><?php
		echo "<option value=''>Any Book</option>";
		echo "<option value='old' ".($only_old ? 'selected' : '').">Old Testament</option>";
		echo "<option value='new' ".($only_new ? 'selected' : '').">New Testament</option>";
		echo "<optgroup label='Old Testament'>";
		foreach($old as $book_opt) {
			echo "<option value='$book_opt[name]' ".($book_opt['id'] == $book['id'] ? 'selected' : '').">$book_opt[name]</option>";
		}
		echo "</optgroup><optgroup label='New Testament'>";
		foreach($new as $book_opt) {
			echo "<option value='$book_opt[name]' ".($book_opt['id'] == $book['id'] ? 'selected' : '').">$book_opt[name]</option>";
		}
		echo "</optgroup>";
?></select> chapter <input name='chapter' placeholder="Any Chapter" type="number" min="1" value="<?= $chapter['number'] ?>"> for
	<input type="search" name="q" minlength="3" maxlength="2000" placeholder="this phrase..." value="<?= htmlentities($q, ENT_HTML5) ?>">
	<fieldset>
		<legend>Also search in</legend>
		<ul style="list-style:none;">
			<li><label><input name="also[fn]" type="checkbox" value="true" <?= $also['fn'] ? 'checked' : '' ?>> Footnotes</label></li>
			<li><label><input name="also[cr]" type="checkbox" value="true" <?= $also['cr'] ? 'checked' : ''?>> Cross References</label></li>
			<li><label><input name="also[out]" type="checkbox" value="true" <?= $also['out'] ? 'checked' : ''?>> Outline headings</label></li>
			<li><label><input name="also[subj]" type="checkbox" value="true" <?= $also['subj'] ? 'checked' : ''?>> Book Details</label></li>
		</ul>		
	</fieldset>
	<button type="submit">Search</button>
	<hr />
</form>
<?php
echo nav_line();
if ($q) {
	echo "<h4>$num_results Results</h4>";
	foreach(array_keys($results) as $cat) {
		if ($results[ $cat ]) {
			echo "<h5>$cat matches</h5>";
            $count = 0;
			foreach ($results[ $cat ] as $result) {
				if (++$count && $count % 30 == 0 && count($results[ $cat ]) - $count > 30) {
                    echo "<hr />";
			        echo nav_line();
			        $count = 0;
                }
				echo "<div class='result'><a target='_blank' href='$result[href]'>$result[a_tag]</a>: ".
					str_replace("\n", " / ", preg_replace("/($q)/i", "<span class='match'>\$1</span>", trim($result['content']))).
					"</div>";
			}
		}
	}
    if ($num_results > 10) {
        echo "<hr />".nav_line();
    }
    if ($num_results > 0) {
        echo copyright;
    }
}

require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
