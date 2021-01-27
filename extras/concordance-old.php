<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-16
 * Time: 17:40
 */

$concordance = true;
$title = "Concordance";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

$q_letter = strtolower($_GET['start'] ?: '');
if (in_array($q_letter, range('a', 'z'))) {
	$letter = $q_letter;
}

echo "<h2><a href='/bible'>Concordance".($letter ? ": '".strtoupper($letter)."'" : '')."</a></h2>
	<div class='justify'>";

foreach(range('A', 'Z') as $alpha) {
	echo "<a class='button' href='concordance?start=$alpha'>$alpha</a>";
}
echo "</div>".nav_line(true)."<hr/>";


if ($letter) {
	$count = 0;
	$rows = select("SELECT * FROM concordance WHERE SUBSTR(word, 1, 1) LIKE '$letter' ORDER BY word");
	foreach($rows as $row) {
		if (++$count && $count % 30 == 0 && count($rows) - $count > 30) {
			echo "<hr />";
			echo nav_line();
		}
		echo "<div>
			<b>$row[word]</b> ($row[matches]) ".format_note($row['references']).
		"</div>";
	}
}
echo "</div>";
if ($letter) {
	echo "<hr />".nav_line();
	echo copyright;
}


require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
