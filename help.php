<?php
/**
 * Created by Vim
 * User: user
 * Date: 2020-11-26
 * Time: 08:32
 */

$title = "Help";
$meta_description = "Get help for navigating the website and using all of its features.";
$meta_canonical = "https://".getenv("DOMAIN")."/help";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";
?>

<h1><a href='/bible'>Help</a></h1>
<p>A few tips for using this website:</p>

<ol>
  <li>Use your browser's "Back" and "Forward" navigation to jump between footnotes and pages that you have visited</li>
  <li>Use the ← and → arrow keys to navigate between chapters and books</li>
  <li>Click the title of any page to go back to the list of books</li>
  <li>Click a verse number to go to the list of chapters for the current book</li>
  <li>Type anywhere to begin searching for a verse by reference or keyword
    <ul>
      <li>Click any verse to open it</li>
      <li>Use the mouse, arrow keys up/down, or tab/shift+tab to select a verse in the list
        <ul>
          <li>With a verse selected, 'Enter' will open that verse</li>
          <li>Ctrl/Cmd+Enter will open the advanced search page</li>
          <li>Shift+Enter will copy the verse text to your clipboard</li>
          <li>Without a selected verse, pressing 'Enter' from the quick search will open the advanced search page</li>
        </ul>
      </li>
      <li>Typing a ';' will insert a ':' to make entering references faster</li>
      <li>Pressing 'Esc' or clicking off a verse will dismiss the search tool</li>
    </ul>
  </li>
  <li>The 'Play' button that appears to the right of a highlighted verse will begin playing audio of the chapter from that verse until the end of the chapter
    <ul>
      <li>Stop the speaking using the option in the menu at the top-right of the page</li>
    </ul>
  </li>
  <li>The symbol below the play button will open a link to the interlinear text on <a href='https://biblehub.com'>BibleHub.com</a></li>
  <li>You can link to this website, directly to a verse using the format: <pre>https://<?= getenv("DOMAIN") ?>/bible/{BOOK NAME}/{CHAPTER NUMBER}?verse={VERSE NUMBER}</pre>
    <ul>
      <li>Ommit the verse number or chapter number if you want to link to a book or chapter</li>
      <li>John 1:1 <pre>https://<?= getenv("DOMAIN") ?>/bible/john/1?verse=1</pre></li>
      <li>2 Corinthians 4 - note that spaces should be replaced with a '-' <pre>https://<?= getenv("DOMAIN") ?>/bible/2-corinthians/4</pre></li>
      <li>Philemon<pre>https://<?= getenv("DOMAIN") ?>/bible/philemon</pre></li>
    </ul>
  </li>
  <li>You can use the <a href='/verse' target='_blank'>Verse Lookup</a> (Cmd/Ctrl+u anywhere) page to find a set list of verses.
    <ul>
      <li>Separate verses with a semicolon: Gen. 1:26; 2:14</li>
      <li>verses can have a range: Dan. 2:4-7</li>
      <li>Separate verses in the same chapter with a comma: Rom. 8:2, 6, 10</li>
      <li>Three links appear below the set of returned verses.
        <ul>
          <li>The first link copies <em>the text of the verses with references</em> to your clipboard.</li>
          <li>The second link copies <em>the text of all the verses joined together</em> with a single reference at the end.</li>
          <li>The third link copies a <em>link</em> to that set of verses to your clipboard.</li>
        </ul>
      </li>
    </ul>
  </li>
</ol> 
<?php

echo "<hr>".nav_line();

require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
