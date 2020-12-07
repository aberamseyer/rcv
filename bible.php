<?php
    $bible = true;
    require "init.php";

    $title = "Holy Bible";
    if ($book) {
        $title = $book['name'];
        if ($chapter) {
            $title .= " ".$chapter['number'];
        }
    }

    require "head.php";

    if ($book) {
        echo "<h1><a href='bible'>".$book['name'].(
                $book['chapters'] > 1 && $chapter
                    ? " ".$chapter['number'] : ""
            )."</a></h1>";
    }
    else {
        echo "<h1 style='margin-bottom: 3rem;'>Holy Bible - Recovery Version</h1>";
    }
    if (!$book) {
        // show book names to select
        echo "<div class='justify'>";
        foreach($old as $i => $book_option) {
            echo "<a class='button' href='bible?book=$book_option[name]'>$book_option[name]</a>";
        }
        echo "</div>";
        echo "<div class='justify' style='margin-top: 1rem;'>";
        foreach($new as $i => $book_option) {
            echo "<a class='button' href='bible?book=$book_option[name]'>$book_option[name]</a>";
        }
        echo "</div>";
    }
    else if (!$chapter) {
        // show chapter names to select
        echo "<p><small>";
        foreach(explode("\n", str_replace("\n\n", "\n", $book['details'])) as $detail_line) {
            $parts = explode(": ", $detail_line);
            echo count($parts) === 2
                ? "<b>".$parts[0].": </b>".$parts[1]."<br/>"
                : "<b>".$detail_line."</b><br/>";
        }
        echo "</small></p>";
        // echo "<p><small>".nl2br(str_replace("\n\n", "\n", $book['details']))."</small></p>";
        echo "<h6>Chapters</h6>";
        echo "<div class='justify'>";
        foreach($chapters as $chapter_option) {
            echo "<a class='button' href='bible?book=$book[name]&chapter=$chapter_option[number]'>$chapter_option[number]</a>";
        }
        echo "</div><hr />";
        echo nav_line(true);
        echo "<h6>Outline</h6>";
        $outline = select("
	  SELECT cc.*, c.number chapter
	  FROM chapter_contents cc
	  JOIN chapters c ON cc.chapter_id = c.id
	  WHERE c.book_id = $book[id] AND tier IS NOT NULL
	ORDER BY outline_order");
        foreach($outline as $outline_point) {
	    if (strpos($outline_point['content'], "cont'd") === false)
                echo "<a href='/bible?book=$book[abbreviation]&chapter=$outline_point[chapter]#verse-$outline_point[id]'>".format_verse($outline_point)."</a>";
        }
    }
    else {
	echo "<div id='chp-$chapter[id]'>";
        foreach($contents as $element) {
            if ($minimal_layout && $element['tier'])
                continue;
            echo format_verse($element);
        }
	echo "</div>";
        if ($footnotes && !$minimal_layout) {
            echo nav_line();
            echo "<hr/>";
        }
        if (!$minimal_layout) {
            foreach($contents as $content) {
                foreach($content['notes']['fn'] as $i => $note) {
                    echo "<small id='fn-$note[id]' class='footnote'>";
                    echo "<a href='#verse-$note[verse_id]' class='no-select'>".($content['number'] ?: 'Title')."<sup>$note[number]</sup></a>&nbsp;";
                    echo format_note($note['note'])." <a href='#verse-$note[verse_id]' class='no-select'>↩</a>";
                    echo "</small>";
                }
            }
        }
    }
?>
<hr/>
<?= nav_line() ?>
<?php
    if ($book) {
        echo copyright;
    }
    else {
        echo "<div class='copy'>You can email me any questions, comments, or concerns <a href='mailto:%61%62%65%40%72%61%6d%73%65%79%65%72%2e%64%65%76'>here</a>.</div>";
    }
    if ($footnotes) {
        // allows scrolling past the last footnote so the links can always focus a footnote at the top of the screen
        echo "<div style='height: 90vh;'></div>";
    }

// verse scroll into view for links
$verse = (int)$_GET['verse'];
if ($verse):
?>
<script type="text/javascript">
    if (!window.location.hash)
        window.addEventListener('load', function() {
          setTimeout(function() {
            const el = document.querySelectorAll('.verse')[<?= $verse - 1 ?>];
            el.classList.add('highlight');
            el.scrollIntoView({ block: "center" });
            setTimeout(function() {
                el.classList.remove('highlight');
            }, 1000);
          }, 250);
        });
</script>
<?php
endif;
    echo "<script type='text/javascript'>window.book = '".$book['name']."', window.chapter = '".$chapter['number']."'; </script>";
    echo '<script type="text/javascript" src="res/read.js"></script>';
    require "foot.php";
?>
