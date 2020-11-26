<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-15
 * Time: 15:39
 */
require_once "init.php";
?>
<!doctype html>
<html lang="en-US"<?= $light_theme ? " style='filter: invert(1)'" : "" ?>>
<head>
	<title><?= $title ?> - Recovery Version</title>
	<meta content="width=device-width, initial-scale=1" name="viewport" />
    <meta charset="utf-8">
    <meta name="description" content="Read the Holy Bible Recovery Versions complete with outlines, footnotes, cross-references, and book details.">
    <link rel="canonical" href="https://rcv.ramseyer.dev/bible">
	<link rel="manifest" href="res/manifest.json">
	<link rel="stylesheet" href="res/sakura-dark.css" type="text/css">
	<link rel="stylesheet" href="res/style.css" type="text/css">
</head>
<body id="top">
<?php
    echo '<div id="menu">';
        echo '<span>&#8942;</span>';
        echo '<ul>';
            echo '<li><a href="?'. http_build_query($_GET) .'&set_theme='.($light_theme ? 'dark' : 'light').'">Switch to '.($light_theme ? 'dark' : 'light').' theme</a></li>';
            echo '<li><a href="?'. http_build_query($_GET) .'&set_minimal='.($minimal_layout ? 'false' : 'true').'">'.($minimal_layout ? 'Show' : 'Hide').' notes</a></li>';
	    echo '<li><a href="/help">Help</a></li>';
        echo '</ul>';
    echo '</div>';
?>
