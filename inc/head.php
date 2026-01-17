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
        <li style='display: none;'>
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
      </ul>
  </div>
