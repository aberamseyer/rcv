<?php

$title = "Language Concordance";
$meta_description = "Browse concordances of the Holy Bible Recovery Version and the footnotes.";
$meta_canonical = "https://".getenv("DOMAIN")."/concordance";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

if ($_REQUEST['action'] == 'typeahead') {
	print_json(select("
		SELECT translit, word, strongs
		FROM lexicon
		WHERE word LIKE '%".db_esc_like($_REQUEST['value'])."%' OR
			translit LIKE '%".db_esc_like($_REQUEST['value'])."%'", l_db()));
}


require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";
$strongs = $_REQUEST['strongs'];
$list_usages = $_REQUEST['list-usages'];
$passed_word = $_REQUEST['word'];
$q_conc = strtolower($_GET['conc']) ?: 'greek';
$letter = strtolower($_GET['start']) ?: '';


if ($_REQUEST['search-word']) {
	$data = row("
		SELECT strongs, word
		FROM lexicon
		WHERE word LIKE '%".db_esc_like($_REQUEST['search-word'])."%'", l_db());
	$strongs = $data['strongs'];
	$passed_word = $data['word'];
}

function print_list($arr) {
	echo "<ul>";
	foreach($arr as $i)
		if (is_array($i))
			print_list($i);
		else
			echo "<li>$i</li>";
	echo "</ul>";
}
function replace_links($str) {
	return preg_replace_callback("/(?:\{\{)([gh]\d+)(?:\}\})/i", function($matches) {
		return "<a href='/language-concordance?strongs=$matches[1]'>$matches[1]</a>";
	}, $str);
}

if ($strongs &&
	$data = row("SELECT * FROM lexicon WHERE strongs = '".db_esc($strongs)."'", l_db())) {
	ob_start();
	echo "<h3><a href='/bible'>Strong's $strongs".($passed_word ? ": $passed_word" : "")."</a></h3>";
	echo "<p class='smaller'><a href='/language-concordance'>↩ Back</a> to search</p>";
	row("SELECT * FROM books");
	
	echo "<p><b>Base word</b>: $data[word] <em>$data[pronunciation]</em></p>";
	echo "<p><b>Transliteration</b>: $data[translit]</p>";
	echo "<p><b>Definition</b>: $data[definition]</p>";
	echo "<p><b>Usages</b>:";

	print_list(json_decode($data['usages'], true));

	$arr = explode(',', $data['occurances']);
	echo "<p><a href='/language-concordance?list-usages=$strongs'>Occurs approximately $data[frequency] time(s) in  about ".count($arr)." variation(s)</a>: <br> ".implode(', ', $arr)."</p>";

	echo replace_links(ob_get_clean());
}
else if ($list_usages) {
	echo "<h3><a href='/bible'>Usages of $list_usages</a></h3>";
	echo "<p class='smaller'><a href='/language-concordance?strongs=$list_usages'>↩ Back</a> to the definition</p>";
	$q_like = "'%\"".db_esc_like(strtolower($list_usages))."\"%'";

	$ids = select("SELECT id, text_content FROM original_text WHERE text_content LIKE $q_like", l_db());
	$data = $ids ? select("
		SELECT cc.id, b.name book, c.number chapter, cc.id verse_id, cc.reference, cc.content
		FROM chapter_contents cc
		JOIN chapters c ON c.id = cc.chapter_id
		JOIN books b ON b.id = c.book_id
		WHERE cc.id IN(".implode(",", array_column($ids, "id")).")
		ORDER BY b.sort_order, c.number, cc.number") : [];


	foreach($data as &$datum)
		foreach($ids as $id)
			if ($id['id'] == $datum['id'])
				$datum['original_text'] = $id['text_content'];

	$links = [];
	foreach($data as &$result) {
		$json = json_decode($result['original_text'], true);
		$text = "
		<li>
			<a target='_blank' href='/bible/".link_book($result['book'])."/$result[chapter]#verse-$result[verse_id]'><b>$result[reference]</b></a>";
		$matching_words = [];
		foreach($json as $original_word)
			if ($original_word['number'] == $list_usages)
				$matching_words[] = $original_word['word'];
		$links[]= $text."–".implode(", ", $matching_words)."
			<small>".str_replace("\n", ' / ', $result['content'])."</small>
			<small".(strpos($list_usages, 'h') === 0 ? ' style="text-align: right;"' : "").">".preg_replace('/('.implode('|', $matching_words).')/i', '<b>$0</b>', html(implode(' ', array_column($json, 'word'))))."</small>
		</li>";
	}
	unset($result);
	echo "<ol>".implode('', $links)."</ol>";
}
else {
	$entry = $_REQUEST['search-word'] ?: $strongs;
	?>
	<h1><a href='/bible'>Language Concordance</a></h1>
	<?php if ($entry): ?>
		<p>No info found for <em><?= html($entry) ?></em></p>
	<?php endif; ?>
	<form action='' method='get'>
		<label class='sans-text'>
			Search by Strong's number
			<input type='text' name='strongs' placeholder="g#### or h####" pattern="[gh]\d+">
		</label>
		<label class='sans-text'>
			Search by word
			<input type='text' name='search-word' placeholder='אֱלֹהִ֑ים or λογος' id='word-input' list='word-list'>
			<datalist id='word-list'></datalist>
		</label>
		<button type='submit' style='visibility: hidden'>Search</button>
	</form>
	<hr>
	<div class='justify'>
	<?php
	$alphabet = $q_conc == 'greek'
		? ['α', 'β', 'γ', 'δ', 'ε', 'ζ', 'η', 'θ', 'ι', 'κ', 'λ', 'μ', 'ν', 'ξ', 'ο', 'π', 'ρ', 'σ', 'τ', 'υ', 'φ', 'χ', 'ψ', 'ω']
		: ['א', 'ב', 'ג', 'ד', 'ה', 'ו', 'ז', 'ח', 'ט', 'י', 'כ', 'ך', 'ל', 'מ', 'ם', 'נ', 'ן', 'ס', 'ע', 'פ', 'ף', 'צ', 'ץ', 'ק', 'ר', 'ש', 'ת'];
	foreach($alphabet as $alpha) {
		echo "<a class='button' href='/language-concordance?conc=".$q_conc."&start=$alpha'>$alpha</a>";
	}
	echo "</div>";
	?>
	<form style="display: flex; justify-content: space-evenly" onchange='this.submit()'>
		<input type="hidden" name="start" value="<?= $letter ?>">
		<label>
		<input type="radio" name="conc" value="hebrew" <?= $q_conc === 'hebrew' ? 'checked' : '' ?>> Hebrew
		</label>
		<label>
		<input type="radio" name="conc" value="greek" <?= $q_conc === 'greek' ? 'checked' : '' ?>> Greek
		</label>
	</form>
	<?php
	if ($letter) {
		if ($q_conc == 'greek') {
			$rows = select("
				SELECT strongs, translit, word
				FROM lexicon
				WHERE SUBSTR(word, 1, 1) = '$letter'
				ORDER BY strongs", l_db());
		}
		else { // $q_conc == 'hebrew'
			$rows = select("
				SELECT strongs, translit, word
				FROM lexicon
				WHERE SUBSTR(word, -1, 1) = '$letter'
				ORDER BY strongs", l_db());
		}

		// print them
		foreach($rows as $row) {
			echo "<div>
				<a href='/language-concordance?strongs=$row[strongs]'><b>$row[word]</b>: <i>$row[translit]</i>, $row[strongs]</a>
			</div>";
		}
	}
	?>
	<script>
		(() => {
			const input = document.getElementById('word-input');
			const list = document.getElementById('word-list');
			
			let timer = 0;
			input.addEventListener('keydown', ({ key }) => {
				clearTimeout(timer);
				timer = setTimeout(() => {
					if (input.value.length > 2) {
						doRequest("GET", `?action=typeahead&value=${encodeURIComponent(input.value)}`, null, request => {
							const results = JSON.parse(request.response);
							list.innerHTML = results.map(res => `<option value="${res.word}">${res.strongs}: ${res.translit}</option>`).join('');
						});
					}
				});
			}, 300);
		})();
	</script>
	<?php
}

?>
<hr>
<?php
echo nav_line();
require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";