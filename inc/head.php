<?php
/**
 * Created by PhpStorm.
 * User: user
 * Date: 2020-07-15
 * Time: 15:39
 */
?>
<!doctype html>
<html lang="en-US" class="<?= $light_theme ? 'light' : '' ?> <?= $minimal_layout ? 'hide-notes' : '' ?> <?= $serif_text ? 'serif' : '' ?>">
<head>
  <title><?= $title ?> - Recovery Version</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta charset="utf-8">
  <meta name="description" content="<?= $meta_description ?>">
  <link rel="canonical" href="<?= $meta_canonical ?>">
  <link rel="shortcut icon" type="image/png" href="/res/site/favicon.png?v=<?= COMMIT_HASH ?>">
  <link rel="manifest" href="/res/site/manifest.json?v=<?= COMMIT_HASH ?>">
  <link rel="search" type="application/opensearchdescription+xml" title="Recovery Version" href="/res/site/opensearch.xml?v=<?= COMMIT_HASH ?>">
  <link rel="stylesheet" href="/res/css/sakura-dark.css?v=<?= COMMIT_HASH ?>" type="text/css">
  <link rel="stylesheet" href="/res/css/font-awesome.min.css">
  <link rel="stylesheet" href="/res/css/style.css?v=<?= COMMIT_HASH ?>" type="text/css">
</head>
<body id="top">
  <div id="menu">
    <span class='dots'>&#8942;</span>
      <ul id='menu-js'>
        <li>
          <span data-toggle='theme'>Switch to light theme</span>
          <div class="emoji">🌗</div>
        </li>
        <li>
          <span data-toggle='layout'>Hide notes</span>
          <div class="emoji">📝</div>
        </li>
        <li>
          <span data-toggle='font'>Use sans-serif font</span>
          <div class="emoji">🆎</div>
        </li>
        <li>
          <span data-toggle='random'>Random Verse </span>
          <div class="emoji">🎲</div>
        </li>
        </li>
      </ul>
  </div>
  <div id="sidebar">
    <ul id="navigation">
      <li><a tabindex='-1' href='/bible'><i class='fa fa-book'></i>&nbsp;&nbsp;Books</a></li>
      <li><a tabindex='-1' href='/search'><i class='fa fa-search'></i>&nbsp;&nbsp;Search</a></li>
      <li><a tabindex='-1' href='/concordance'><i class='fa fa-map-signs'></i>&nbsp;&nbsp;Concordance</a></li>
      <li><a tabindex='-1' href='/language-concordance'><i class='fa fa-language'></i>&nbsp;&nbsp;Language Concordance</a></li>
      <li><a tabindex='-1' href='/verse'><i class='fa fa-crosshairs'></i>&nbsp;&nbsp;Verse Lookup</a></li>
      <li><a tabindex='-1' href='/help'><i class='fa fa-question-circle-o'></i>&nbsp;&nbsp;Help</a></li>
      <li><a tabindex='-1' href='/release-notes'><i class='fa fa-rocket'></i>&nbsp;&nbsp;Release Notes</a></li>
    </ul>
  </div>
