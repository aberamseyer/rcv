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
        $notes = select("SELECT * FROM footnotes WHERE verse_id IN(".implode(',', $verse_ids).") ORDER BY number");

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
        // show chapter names to select
        echo "<p><small>";
        foreach(explode("\n", str_replace("\n\n", "\n", $book['details'])) as $detail_line) {
            $parts = explode(": ", $detail_line);
            echo count($parts) === 2
                ? "<b>".html($parts[0]).": </b>".html($parts[1])."<br/>"
                : "<b>".html($detail_line)."</b><br/>";
        }
        echo "</small></p>";

        echo "<h6>Chapters</h6>";
        echo "<div class='justify'>";
        foreach($chapters as $chapter_option) {
            echo "<a class='button' href='/bible/".link_book($book['name'])."/$chapter_option[number]'>$chapter_option[number]</a>";
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
                echo "<a href='/bible/".link_book($book['name'])."/$outline_point[chapter]#verse-$outline_point[id]'>".format_verse($outline_point)."</a>";
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
        echo "<div class='copy'><a href='/login'>You</a> can email me any questions, comments, or concerns <a href='mailto:%61%62%65%40%72%61%6d%73%65%79%65%72%2e%64%65%76'>here</a>.</div>";
    }
    if ($footnotes) {
        // allows scrolling past the last footnote so the links can always focus a footnote at the top of the screen
        echo "<div style='height: 90vh;'></div>";
    }
?>
<script type="text/javascript">
    if (!window.location.hash) {
        const matches = window.location.search.match(/verse=(\d+)/);
        if (matches) {
            window.addEventListener('load', function() {
              setTimeout(()  => {
                const el = document.querySelectorAll('.verse')[ parseInt(matches[1]) - 1 ];
                el.classList.add('highlight');
                el.scrollIntoView({ block: "center" });
                setTimeout(() => el.classList.remove('highlight'), 1000);
              }, 250);
            });   
        }
    }
</script>
<?php
    echo "<script type='text/javascript'>window.book = '".$book['name']."', window.chapter = '".$chapter['number']."'; </script>";
    echo '<script type="text/javascript" src="/res/read.js"></script>';
    require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
    require $_SERVER['DOCUMENT_ROOT']."/inc/cache-foot.php";
?>
