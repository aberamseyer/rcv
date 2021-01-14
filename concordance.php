<?php
/**
 * Created by Vim.
 * User: user
 * Date: 2021-01-14
 * Time: 12:21
 */

$concordance = true;
$title = "Concordance";
require "head.php";

$q_conc = strtolower($_GET['conc']) ?: 'bible';
$q_letter = strtolower($_GET['start']) ?: '';
if (in_array($q_letter, range('a', 'z'))) {
	$letter = $q_letter;
}

echo "<h2><a href='/bible'>Concordance".($letter ? ": '".strtoupper($letter)."'" : '')."</a></h2>
	<div class='justify'>";

foreach(range('A', 'Z') as $alpha) {
	echo "<a class='button' href='concordance?conc=".$q_conc."&start=$alpha'>$alpha</a>";
}
echo "</div>";
?>
<form style="display: flex; justify-content: space-evenly" onchange='this.submit()'>
  <input type="hidden" name="start" value="<?= $letter ?>">
  <label>
  <input type="radio" name="conc" value="bible" <?= $q_conc === 'bible' ? 'checked' : '' ?>> Bible
  </label>
  <label>
  <input type="radio" name="conc" value="foot" <?= $q_conc === 'foot' ? 'checked' : '' ?>> Footnotes
  </label>
</form>
<?php
echo nav_line(true)."<hr/>";


if ($letter) {
  // get our entries from the bible or footnote tables
	if ($q_conc === 'bible') {
    $rows = select("
      SELECT bc.word, cc.reference, CONCAT('bible?book=', b.name, '&chapter=', c.number, '#verse-', cc.id) href
      FROM bible_concordance bc
      JOIN bible_concordance_to_chapter_contents c2cc ON bc.id = c2cc.concordance_id
      JOIN chapter_contents cc ON cc.id = c2cc.chapter_contents_id
      JOIN chapters c ON cc.chapter_id = c.id
      JOIN books b ON b.id = c.book_id
      WHERE SUBSTR(bc.word, 1, 1) = '$letter'
      ORDER BY b.sort_order, c.number, cc.sort_order");
  }
	else { // $q_conc == 'conc'
    $rows = select("
      SELECT fc.word, cc.reference, CONCAT('bible?book=', b.name, '&chapter=', c.number, '#fn-', f.id) href
      FROM footnote_concordance fc
      JOIN footnote_concordance_to_footnotes fc2f ON fc.id = fc2f.footnote_concordance_id
      JOIN footnotes f ON f.id = fc2f.footnotes_id
      JOIN chapter_contents cc ON cc.id = f.verse_id
      JOIN chapters c ON cc.chapter_id = c.id
      JOIN books b ON b.id = c.book_id
      WHERE SUBSTR(fc.word, 1, 1) = '$letter'
      ORDER BY b.sort_order, c.number, cc.sort_order");
  }

  // print them
  $count = 0;
  foreach($rows as $row)
    $arr[$row['word']][] = $row;
  foreach($arr as $word => $refs) {
		if (++$count && $count % 50 == 0 && count($rows) - $count > 30) {
			echo "<hr />";
			echo nav_line();
		}
    echo "<details>
      <summary><b>$word</b>: ".number_format(count($refs))."</summary>
      ".implode(", ",  array_map(function($row) { return "<a href='$row[href]' target='_blank'>$row[reference]</a>"; }, $refs))."
    </details>";
	}
}
echo "</div>";
if ($letter) {
	echo "<hr />".nav_line();
	echo copyright;
}


require "foot.php";
