<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-15
 * Time: 15:39
 */
?>
<!doctype html>
<html lang="en-US" class="<?= $light_theme ? 'light' : '' ?>">
<head>
  <title><?= $title ?> - Recovery Version</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta charset="utf-8">
  <meta name="description" content="<?= $meta_description ?>">
  <link rel="canonical" href="<?= $meta_canonical ?>">
  <link rel="shortcut icon" type="image/png" href="/res/site/favicon.png">
  <link rel="manifest" href="/res/site/manifest.json">
  <link rel="stylesheet" href="/res/css/sakura-dark.css" type="text/css">
  <link rel="stylesheet" href="/res/css/style.css" type="text/css">
</head>
<body id="top" class="<?= $serif_text ? 'serif' : '' ?>">
<?php
    echo '<div id="menu">';
        echo '<span>&#8942;</span>';
        echo '<ul>';
            echo '<li><a href="?'. http_build_query($_GET) .'&set_theme='.($light_theme ? 'dark' : 'light').'">Switch to '.($light_theme ? 'dark' : 'light').' theme</a></li>';
            echo '<li><a href="?'. http_build_query($_GET) .'&set_minimal='.($minimal_layout ? 'false' : 'true').'">'.($minimal_layout ? 'Show' : 'Hide').' notes</a></li>';
            echo '<li><a href="?'. http_build_query($_GET) .'&set_serif='.($serif_text ? 'false' : 'true').'">Use '.($serif_text ? 'sans-' : '').'serif font</a></li>';
	    echo '<li><a href="/help">Help</a></li>';
        echo '</ul>';
    echo '</div>';
?>
