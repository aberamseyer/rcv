<?php
/**
 * Created by Vim
 * User: user
 * Date: 2020-12-05
 * Time: 12:44
 */

// TODO fix parsing, permalink to list of verses

$title = "Verse Requester";
require "init.php";
require "head.php";
?>

<h2><a href='/bible'>Verse Requester</a></h2>

<input id='verse-input' name='q' type='text' style='width: 100%;' placeholder='e.g., Gen. 1:26; John 1:1, 14; 2 Cor. 3:18; Jude 20-21'>
<small>Note: you can request a maximum of 200 verses at a time</small>
<span>Recognized verses: <small id='recognized-verses' style="display: inline"></small></span>

<div style="margin-top: 12px;" id='verses'></div>

<script type='text/javascript' src='/res/verse.js'></script>

<hr />
<?php

echo nav_line();
echo copyright;

require "foot.php";
