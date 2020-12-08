<?php
/**
 * Created by Vim
 * User: user
 * Date: 2020-11-26
 * Time: 08:32
 */

$title = "Help";
require "head.php";
?>

<h2><a href='/bible'>Help</a></h2>
<p>A few tips for using this website:</p>

<ol>
  <li>Use your browser's "Back" and "Forward" navigation to jump between footnotes and pages that you have visited</li>
  <li>Click the title of any page to go back to the list of books</li>
  <li>Click a verse number to go to the list of chapters for the current book</li>
  <li>Type anywhere to begin searching for a verse by reference or keyword
    <ul>
      <li>Click any verse to open it</li>
      <li>Pressing 'Enter' from the quick search will navigate to the advanced search page</li>
      <li>Typing a ';' will insert a ':' to make entering references faster</li>
      <li>Pressing 'Esc' or clicking off a verse will dismiss the search tool</li>
    </ul>
  </li>
  <li>The 'Play' button that appears when hovering over each verse to the right will begin playing audio of the chapter from that verse until the end of the chapter
    <ul>
      <li>Stop the speaking using the option in the menu at the top-right of the page</li>
    </ul>
  </li>
  <li>You can link to this website, directly to a verse using the format: <pre>https://rcv.ramseyer.dev/bible?book={BOOK NAME or BOOK ABBREVIATION}&amp;chapter={CHAPTER NUMBER}&amp;number={VERSE NUMBER}</pre>
    <ul>
      <li>Ommit the verse number or chapter number if you want to link to a book or chapter</li>
      <li>John 1:1 <pre>https://rcv.ramseyer.dev/bible?book=John&amp;chapter=1&amp;verse=1</pre></li>
      <li>2 Cor. 4 <pre>https://rcv.ramseyer.dev/bible?book=2 Cor.&amp;chapter=4</pre></li>
      <li>Philemon<pre>https://rcv.ramseyer.dev/bible?book=Philemon</pre></li>
    </ul>
  </li>
  <li>You can use the <a href='/verse' target='_blank'>Verse Lookup</a> page to find a set list of verses.
    <ul>
      <li>Separate verses with a semicolon, i.e., Gen. 1:26; 2:14</li>
      <li>verses can have a range, i.e., Dan. 2:4-7</li>
      <li>Separate verses in the same chapter with a comma, i.e., Rom. 8:2, 6, 10</li>
      <li>Click the link that appears below the set of returned verses to copy a link to that set of verses to your clipboard</li>
  </li>
</ol> 

<p>
There might be some outline points interspersed with the verses or missing footnotes, if you find them, you can email <a href='mailto:abe%40ramseyer.dev'>here</a>.
</p>
<?php

echo "<hr />".nav_line();

require "foot.php";
