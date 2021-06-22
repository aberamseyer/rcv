<?php
/**
 * Created by Vim.
 * User: user
 * Date: 2021-01-14
 * Time: 12:21
 */

$concordance = true;
$title = "Concordance";
$meta_description = "Browse concordances of the Holy Bible Recovery Version and the footnotes.";
$meta_canonical = "https://".getenv("DOMAIN")."/concordance";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

$q_conc = strtolower($_GET['conc']) ?: 'bible';
$q_letter = strtolower($_GET['start']) ?: '';
if (in_array($q_letter, range('a', 'z'))) {
	$letter = $q_letter;
}

echo "<h1><a href='/bible'>Concordance".($letter ? ": '".strtoupper($letter)."'" : '')."</a></h1>
	<div class='justify'>";

foreach(range('A', 'Z') as $alpha) {
	echo "<a class='button' href='/concordance?conc=".$q_conc."&start=$alpha'>$alpha</a>";
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
      SELECT bc.id, bc.word, COUNT(*) refs
      FROM bible_concordance bc
      JOIN bible_concordance_to_chapter_contents c2cc ON bc.id = c2cc.concordance_id
      WHERE SUBSTR(bc.word, 1, 1) = '$letter' AND bc.status = 1
      GROUP BY bc.id, bc.word
      ORDER BY bc.word");
  }
	else { // $q_conc == 'foot'
    $rows = select("
      SELECT fc.id, fc.word, COUNT(*) refs
      FROM footnote_concordance fc
      JOIN footnote_concordance_to_footnotes fc2f ON fc.id = fc2f.footnote_concordance_id
      WHERE SUBSTR(fc.word, 1, 1) = '$letter' AND fc.status = 1
      GROUP BY fc.id, fc.word
      ORDER BY fc.word");
  }

  // print them
  $count = 0;
  foreach($rows as $row) {
		if (++$count && $count % 50 == 0 && count($rows) - $count > 30) {
			echo "<hr />";
			echo nav_line();
		}
    echo "<details ontoggle='getRefs($row[id], this)'>
      <summary><b>$row[word]</b>: ".number_format($row['refs'])."</summary>
      <small></small>
    </details>";
	}
}

if ($letter) {
	echo "<hr />".nav_line();
	echo copyright;
}

?>
<script>
function getRefs(id, details) {
  const container = details.querySelector('small');
  if (details.open && container.innerHTML === '') {
	  const formData = new FormData();
    formData.append('type', '<?= $q_conc ?>');
	  formData.append('id', id); 

	  const request = new XMLHttpRequest();
	  request.open("POST", "/ajax?action=conc");

	  request.onloadend = () => {
	  	if (request.status === 200) {
	  		const results = JSON.parse(request.response);

        if (results.length) {
          container.innerHTML = 
            results.map(ref => 
              `<a target='_blank' href='${ref.href}'>${ref.reference}${+ref.number ? '<sup>' + ref.number + '</sup>' : ''}</a>`
            ).join(`&nbsp; &nbsp;`);
        }
	  	}
    }
    request.send(formData); 
  }
}
</script>
<?php
require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
