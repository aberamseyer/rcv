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
$meta_canonical = "https://".getenv("DOMAIN")."/search";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

$old = select("SELECT id, name, chapters FROM books WHERE testament = 0 ORDER BY sort_order");
$new = select("SELECT id, name, chapters FROM books WHERE testament = 1 ORDER BY sort_order");

$q = $_GET['q'];
$also = is_array($_GET['also']) ? $_GET['also'] : [ ];
$book_filters = [
	"old" =>  [
		"label" => "Old Testament",
		"sql" => "b.testament = '0'"
	],
	"new" =>  [
		"label" => "New Testament",
		"sql" => "b.testament = '1'"
	],
	"pentateuch" =>  [
		"label" => "Pentateuch",
		"sql" => "b.id IN (1, 2, 3, 4, 5)"
	],
	"history" =>  [
		"label" => "History",
		"sql" => "b.id IN (10, 6, 7, 8, 9, 11, 12, 13, 14, 15, 16, 18)"
	],
	"poetry" =>  [
		"label" => "Poetry",
		"sql" => "b.id IN (17, 19, 20, 21, 22)"
	],
	"major" =>  [
		"label" => "Major Prophets",
		"sql" => "b.id IN (23, 24, 25, 26, 28)"
	],
	"minor" =>  [
		"label" => "Minor Prophets",
		"sql" => "b.id IN (27, 29, 33, 30, 32, 34, 37, 35, 31, 36, 38, 39)"
	],
	"gospels" =>  [
		"label" => "Gospels",
		"sql" => "b.id IN (40, 41, 42, 43)"
	],
	"epistles" =>  [
		"label" => "Epistles",
		"sql" => "b.id IN (46, 44, 45, 48, 51, 52, 49, 50, 55, 56, 53, 58, 63, 66, 54, 59, 60, 65, 64, 57, 61)"
	]
];

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
	$book_filt_str = "";
	foreach($book_filters as $filt => $val)
		if ($_GET['book'] === $filt)
			$book_filt_str = $val['sql'];

	$only_old = $_GET['book'] === 'old';
	$only_new = $_GET['book'] === 'new';

	$only_epistles = $_GET['book'] === 'epistles';

	// keys of $results are used as headings in results
	if (!$also['no_verses']) {
		$results['Verses'] = select("
			SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href, cc.reference a_tag, cc.content content
			FROM chapter_contents cc
			JOIN chapters c ON c.id = cc.chapter_id
			JOIN books b ON b.id = c.book_id
			WHERE LOWER(content) LIKE $q_like
					AND cc.number > 0
				AND ".($book ? "b.id = '$book[id]'" : 1)."
				AND ".($book_filt_str ?: 1)."
				AND ".($chapter ? "c.id = '$chapter[id]'" : 1)."
					ORDER BY b.sort_order, c.number, cc.number");
		$num_results = count($results['Verses']);
	}
	if ($also['out']) {
		$results['Outline'] = select("
		SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href, b.abbreviation || ' ' || c.number a_tag, cc.content content
		FROM chapter_contents cc
		JOIN chapters c ON c.id = cc.chapter_id
		JOIN books b ON b.id = c.book_id
		WHERE LOWER(content) LIKE $q_like
			AND cc.number = 0
			AND ".($book ? "b.id = '$book[id]'" : 1)."
			AND ".($book_filt_str ?: 1)."
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
				AND ".($book_filt_str ?: 1)."
				AND ".($chapter ? "c.id = '$chapter[id]'" : 1)."
            ORDER BY b.sort_order, c.number, cc.number");
		$num_results += count($results['Footnote']);
	}
	if ($also['cr']) {
		$results['Cross Reference'] = select("
			SELECT '/bible/' || LOWER(REPLACE(b.name, ' ', '-')) || '/' || c.number || '#verse-' || cc.id href, cc.reference a_tag,
				f.cross_reference || '<br>' || cc.content content
			FROM footnotes f
			JOIN chapter_contents cc ON cc.id = f.verse_id
			JOIN chapters c ON c.id = cc.chapter_id
			JOIN books b ON b.id = c.book_id
			WHERE LOWER(cross_reference) LIKE $q_like
				AND ".($book ? "b.id = '$book[id]'" : 1)."
				AND ".($book_filt_str ?: 1)."
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
		echo "<optgroup label='Collections'>";
		foreach($book_filters as $key => $filt)
			echo "<option value='$key' ".($_GET['book'] === $key ? 'selected' : '').">$filt[label]</option>";
		echo "</optgroup>
		<optgroup label='Old Testament'>";
		foreach($old as $book_opt) {
			echo "<option value='$book_opt[name]' ".($book_opt['id'] == $book['id'] ? 'selected' : '').">$book_opt[name]</option>";
		}
		echo "</optgroup><optgroup label='New Testament'>";
		foreach($new as $book_opt) {
			echo "<option value='$book_opt[name]' ".($book_opt['id'] == $book['id'] ? 'selected' : '').">$book_opt[name]</option>";
		}
		echo "</optgroup>";

		if ($also['fn'] || $also['cr'] || $also['out'] || $also['subj'])
			$checked = true;
?></select> chapter <input name='chapter' placeholder="Any Chapter" type="number" min="1" value="<?= $chapter['number'] ?>"> for
	<input type="search" name="q" minlength="3" maxlength="2000" placeholder="this phrase..." value="<?= htmlentities($q, ENT_HTML5) ?>" autocomplete="off">
	<fieldset>
		<legend>Also search in</legend>
		<ul style="list-style:none;">
			<li><label><input name="also[fn]" type="checkbox" value="true" <?= $also['fn'] ? 'checked' : '' ?>> Footnotes</label></li>
			<li><label><input name="also[cr]" type="checkbox" value="true" <?= $also['cr'] ? 'checked' : ''?>> Cross References</label></li>
			<li><label><input name="also[out]" type="checkbox" value="true" <?= $also['out'] ? 'checked' : ''?>> Outline headings</label></li>
			<li><label><input name="also[subj]" type="checkbox" value="true" <?= $also['subj'] ? 'checked' : ''?>> Book Details</label></li>
			<li class="<?= $checked ? '' : 'hidden' ?>">
				<label><input id='no-verses' name="also[no_verses]" type="checkbox" value="true" <?= $also['no_verses'] ? 'checked' : ''?>> Omit verses?</label></li>
		</ul>
	</fieldset>
	<button type="submit">Search</button>
	<hr>
</form>
<?php
echo nav_line();
if ($q) {
	echo "<h4>$num_results Results</h4>";

	/*
		$cat = one of:
			Verses
			Outline
			Footnote
			Cross Reference
			Book Subject
	*/
	foreach(array_keys($results) as $cat) {
		if ($results[ $cat ]) {
			echo "<h5>$cat matches</h5>";
            $count = 0;
			foreach ($results[ $cat ] as $result) {
				if (++$count && $count % 30 == 0 && count($results[ $cat ]) - $count > 30) {
					echo "<hr>";
					echo nav_line();
					$count = 0;
				}
				$formatted_result = trim(add_dots($result['content']));
				if ($cat != "Cross Reference") {
					$formatted_result = preg_replace("/($q)/i", "<span class='match'>\$1</span>", $formatted_result);
				}
				echo "<div class='result'><a target='_blank' href='$result[href]'>$result[a_tag]</a>: "
					.($cat == "Verses"
						? str_replace("\n", " / ", $formatted_result)
						: $formatted_result).
					"</div>";
			}
		}
	}
    if ($num_results > 10) {
        echo "<hr>".nav_line();
    }
    if ($num_results > 0) {
        echo copyright;
    }
}

?>
<script>
const checkboxes = [ ...document.querySelectorAll('li input:not(#no-verses)')];
const noVersesEl = document.getElementById('no-verses');
checkboxes.forEach(el =>
	el.addEventListener('change', () => {
		const hide = checkboxes.reduce((acc, curr) => !!curr.checked || acc, false);
		if (!hide)
			noVersesEl.checked = false;
		noVersesEl.parentElement.parentElement.classList[ hide ? 'remove' : 'add' ]('hidden');
	}));
</script>
<?php
require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
