<?php
    $bible = true;
    require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
    require $_SERVER['DOCUMENT_ROOT']."/inc/cache-head.php";

    $footnotes = null;

    if ($bible_page) {
        $book = row("SELECT * FROM books WHERE id = $bible_page[book]");
    }
    // determine what book and/or chapter we need to navigate to. it's already a valid page bc of url.php, included at the bottom of init.php
    if ($book) {
        $chapters = select("SELECT * FROM chapters WHERE book_id = $book[id]");
        if ($bible_page['chapter']) {
            $chapter = row("SELECT * FROM chapters WHERE book_id = $book[id] AND number = ".db_esc($bible_page['chapter']));
        }
    }
    if ($chapter) {
        $contents = array_column(
            select("SELECT * FROM chapter_contents WHERE chapter_id = $chapter[id] ORDER BY sort_order"),
            null,
            'id'
        );
        $verse_ids = array_column($contents, 'id');
        $notes = select("SELECT * FROM footnotes WHERE verse_id IN(".implode(',', $verse_ids).") ORDER BY CAST(number AS UNSIGNED)");

        $footnotes = $cross_refs = [];
        foreach($contents as &$content) {
            $content['notes'] = [
                'cr' => [],
                'fn' => []
            ];
        }
        unset($content);
        foreach($notes as $note) {
            if ($note['cross_reference']) {
                $contents[ $note['verse_id'] ]['notes']['cr'][] = $note;
                $cross_refs[] = $note;
            }
            if ($note['note']) {
                $contents[ $note['verse_id'] ]['notes']['fn'][] = $note;
                $footnotes[] = $note;
            }
        }
    }

    $title = "Holy Bible";
    if ($book) {
        $title = $book['name'];
        if ($chapter) {
            $title .= " ".$chapter['number'];
        }
    }


    $meta_description = "Read the Holy Bible Recovery Version complete with outlines, footnotes, cross-references, and book details.";
    $meta_canonical = "https://rcv.ramseyer.dev/bible";
    if ($book) {
        $meta_description = "Read $book[name] from the Holy Bible Recovery Version complete with outlines, footnotes, cross-references, and book details.";
        $meta_canonical = "https://rcv.ramseyer.dev/bible/".link_book($book['name']);
        if ($chapter) {
            $meta_description = "Read $book[name] chapter $chapter[number] from the Holy Bible Recovery Version complete with outlines, footnotes, cross-references, and book details.";
            $meta_canonical = "https://rcv.ramseyer.dev/bible/".link_book($book['name'])."/".$chapter['number'];
        }
    }
    require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

    if ($book) {
        echo "<h1><a href='/bible'>".html(
            $book['name'].(
                $book['chapters'] > 1 && $chapter
                    ? " ".$chapter['number'] : ""
            )
        )."</a></h1>";
    }
    else {
        echo "<h1 style='margin-bottom: 3rem;'>Holy Bible - Recovery Version</h1>";
    }

    if (!$book) {
        // show book names to select
        echo "<div class='justify'>";
        foreach(select("SELECT name FROM books WHERE testament = 0 ORDER BY sort_order") as $i => $book_option) {
            echo "<a class='button' href='/bible/".link_book($book_option['name'])."'>".html($book_option['name'])."</a>";
        }
        echo "</div>";
        echo "<div class='justify' style='margin-top: 1rem;'>";
        foreach(select("SELECT name FROM books WHERE testament = 1 ORDER BY sort_order") as $i => $book_option) {
            echo "<a class='button' href='/bible/".link_book($book_option['name'])."'>".html($book_option['name'])."</a>";
        }
        echo "</div>";
    }
    else if (!$chapter) {
        // book details
        echo "<p><small>";
        $details = explode("\n", $book['details']);
        foreach($details as $detail_line) {
            $parts = explode(": ", $detail_line);
            if (count($parts) === 2)
                echo "<b>".html($parts[0]).": </b>".html($parts[1]);
            else
                echo html($detail_line);
            echo "<br />";
        }
        echo "</p></small>";

        // show chapter names to select
        echo "<h6>Chapters</h6>";
        echo "<div class='justify'>";
        foreach($chapters as $chapter_option) {
            echo "<a class='button' href='/bible/".link_book($book['name'])."/$chapter_option[number]'>$chapter_option[number]</a>";
        }
        echo "</div><hr />";
        echo nav_line(true);

        // book outline
        echo "<h6>Outline</h6>";
        $outline = select("
    	  SELECT cc.*, c.number chapter
    	  FROM chapter_contents cc
    	  JOIN chapters c ON cc.chapter_id = c.id
    	  WHERE c.book_id = $book[id] AND tier IS NOT NULL
    	  ORDER BY outline_order");
        foreach($outline as $outline_point) {
	       if (strpos($outline_point['content'], "cont'd") === false)
                echo "<a data-show href='/bible/".link_book($book['name'])."/$outline_point[chapter]#verse-$outline_point[id]'>".format_verse($outline_point)."</a>";
        }
    }
    else {
        // list of verses links
        echo "<h6>Verses</h6><div class='justify' style='margin-bottom: 0.75rem;'>";
        $i = 1;
        foreach($contents as $id => $element) {
            if ((int)$element['number']) {
                echo "<a class='button' href='#verse-$id'>".($i++)."</a>";
            }
        }
        echo "</div>".nav_line(true)."<hr />";
	    echo "<div id='chp-$chapter[id]'>";
        // the actual content
        foreach($contents as $element) {
            echo format_verse($element);
        }
	   echo "</div>";
       // the footnotes
        if ($footnotes) {
            echo nav_line(null, "data-note");
            echo "<hr data-note />";
        }
        foreach($contents as $content) {
            foreach($content['notes']['fn'] as $i => $note) {
                echo "<small id='fn-$note[id]' class='footnote'>";
                echo "<a href='#verse-$note[verse_id]' class='no-select'>".($content['number'] ?: 'Title')."<sup>$note[number]</sup></a>&nbsp;";
                echo format_note($note['note'])." <a href='#verse-$note[verse_id]' class='no-select'>↩</a>";
                echo "</small>";
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
        echo "<div class='copy'><a href='/login'>You</a> can email me any questions, comments, or concerns <a href='mailto:%61%62%65%40%72%61%6d%73%65%79%65%72%2e%64%65%76'>here</a>.</div>";
    }
    // allows scrolling past the last footnote so the links can always focus a footnote at the top of the screen
    echo "<div style='height: 90vh;'></div>";
    echo "<script>window.book = '".$book['name']."', window.chapter = '".$chapter['number']."'; </script>";
    echo '<script src="/res/js/bible.js"></script>';
    require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
    require $_SERVER['DOCUMENT_ROOT']."/inc/cache-foot.php";
?>
