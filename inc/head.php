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
  <link rel="stylesheet" href="/res/css/style.css?v=<?= COMMIT_HASH ?>" type="text/css">
</head>
<body id="top">
  <div id="menu">
    <?php
      $emoji = [
        '<div class="emoji">🌗</div>',
        '<div class="emoji">📝</div>',
        '<div class="emoji">🆎</div>',
        '<div class="emoji">🎲</div>',
        '<div class="emoji">🙋‍♂️</div>',
        '<div class="emoji">📦</div>'
      ];
    ?>
    <span class='dots'>&#8942;</span>
      <ul id='menu-href'>
        <li>
          <a rel="nofllow" tabindex="-1" href="?<?= http_build_query($_GET) ?>&set_theme=<?= $light_theme ? 'dark' : 'light' ?>">Switch to <?= $light_theme ? 'dark' : 'light' ?> theme</a>
          <?= $emoji[0] ?>
        </li>
        <li>
          <a rel="nofllow" tabindex="-1" href="?<?= http_build_query($_GET) ?>&set_minimal=<?= $minimal_layout ? 'false' : 'true' ?>"><?= $minimal_layout ? 'Show' : 'Hide' ?> notes</a>
          <?= $emoji[1] ?>
        </li>
        <li>
          <a rel="nofllow" tabindex="-1" href="?<?= http_build_query($_GET) ?>&set_sans=<?= $serif_text ? 'true' : 'false' ?>">Use <?= $serif_text ? 'sans-' : ''?>serif font</a>
          <?= $emoji[2] ?>
        </li>
        <li>
          <a rel="nofllow" tabindex="-1" href="?random">Random Verse </a>
          <?= $emoji[3] ?>
        </li>
        <li>
          <a href="/help" tabindex="-1">Help</a>
          <?= $emoji[4] ?>
        </li>
        <li>
          <a href="/release-notes" tabindex="-1">Release Notes</a>
          <?= $emoji[5] ?>
        </li>
      </ul>
      <ul id='menu-js'>
        <li>
          <span data-toggle='theme'>Switch to light theme</span>
          <?= $emoji[0] ?>
        </li>
        <li>
          <span data-toggle='layout'>Hide notes</span>
          <?= $emoji[1] ?>
        </li>
        <li>
          <span data-toggle='font'>Use sans-serif font</span>
          <?= $emoji[2] ?>
        </li>
        <li>
          <span data-toggle='random'>Random Verse </span>
          <?= $emoji[3] ?>
        </li>
        <li>
          <span data-toggle='help'>Help</span>
          <?= $emoji[4] ?>
        </li>
        <li>
          <span data-toggle='release'>Release Notes</span>
          <?= $emoji[5] ?>
        </li>
      </ul>
  </div>
