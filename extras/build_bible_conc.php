<?php
/**
 * Created by Vim.
 * User: user
 * Date: 2021-01-13
 * Time: 16:34
 */
if (php_sapi_name() != 'cli') die("no");

error_reporting(E_ALL^E_NOTICE);
ini_set('memory_limit', '-1'); // glhf

$time = microtime(true);
$db = mysqli_connect('127.0.0.1',  'docker', 'docker', 'rcv_backup');

require "../inc/functions.php";

echo "Truncating tables..";
query("TRUNCATE TABLE bible_concordance");
query("TRUNCATE TABLE bible_concordance_to_chapter_contents");
//query("ALTER TABLE `bible_concordance` DROP INDEX `word`");
//query("ALTER TABLE `bible_concordance_to_chapter_contents` DROP INDEX `concordance_id`");
//query("ALTER TABLE `bible_concordance_to_chapter_contents` DROP INDEX `chapter_contents_id`");
echo "done!\n";

// all verses in order in the Bible
echo "Getting verses..";
$verses = select("
	SELECT cc.id, cc.content, cc.reference
	FROM chapter_contents cc
	JOIN chapters c ON c.id = cc.chapter_id
	JOIN books b ON b.id = c.book_id
	WHERE number > 0
	ORDER BY b.sort_order, cc.id, cc.sort_order");
echo "done!\n";

echo "Building concordance..";
$conc = [ ];
foreach($verses as $verse) {
	foreach(
		array_unique( // prevents duplicate words in a single verse from adding the same reference multiple times
			preg_split('/\s/', // all whitespace
				preg_replace('/[^\s\-a-zA-Z]/', "", $verse['content']) // remove anything that's not whitespace, a hyphen, or a letter so punctuation doesn't affect it
			)
		) as $word
	) {
		$word = strtolower(trim($word, " \n\r\t\v\0-")); // case-insensitive
		if (!$conc [ $word ])
			$conc [ $word ] = [ 'count' => 0, 'refs' => [ ] ];
		$conc[ $word ]['count']++;
		$conc[ $word ]['refs'][] = $verse['id'];
	}
}
echo "done!\n";

echo "Inserting list of words..";
$query = "INSERT INTO bible_concordance (word) VALUES ";
$arr = [ ];
foreach($conc as $word => $entry) {
	$arr[]= "('$word')";
}
$query .= implode(',', $arr);
query($query);
echo "done!\n";

echo "Inserting verse ids..";
$map = array_column(select("SELECT id, word FROM bible_concordance"), "id", "word");
$query = "INSERT INTO bible_concordance_to_chapter_contents (concordance_id, chapter_contents_id) VALUES ";
$arr = [ ];
foreach($conc as $word => $entry) {
	$new_id = $map[ $word ];
	foreach($entry['refs'] as $id)
		$arr[]= "($new_id, $id)";
}
$query .= implode(',', $arr);
query($query);
echo "done!\n";

echo "Adding indecies..";
query("ALTER TABLE `bible_concordance` ADD INDEX(`word`)");
query("ALTER TABLE `bible_concordance_to_chapter_contents` ADD INDEX(`concordance_id`)");
query("ALTER TABLE `bible_concordance_to_chapter_contents` ADD INDEX(`chapter_contents_id`)");
echo "done!\n";

echo "\nFinished in ".(microtime(true)-$time)." seconds.\n";

// SELECT bc.word, cc.content FROM `bible_concordance` bc JOIN bible_concordance_to_chapter_contents c2v ON c2v.concordance_id = bc.id JOIN chapter_contents cc ON cc.id = c2v.chapter_contents_id where word like '%-'
