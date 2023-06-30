<?php
/**
 * Created by VSCode.
 * User: user
 * Date: 2021-06-24
 * Time: 12:35
 */

$login = true;

require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

if ($_SESSION['user']) {
    redirect("/bible");
}

if ($_POST['user'] && $_POST['password']) {
    $user_row = row("SELECT * FROM users WHERE user = '".db_esc($_POST['user'])."'", s_db());
    if ($user_row) {
        if (password_verify($_POST['password'], $user_row['password'])) {
            $_SESSION['user'] = $user_row;
            session_write_close();
            if ($_REQUEST['thru'])
                redirect($_REQUEST['thru']);
            else
                redirect("/bible");
        }
    }
}
?><!doctype html>
<html lang="en-US">
<head>
  <title>Login - Recovery Version</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta charset="utf-8">
  <link rel="shortcut icon" type="image/png" href="/res/site/favicon.png?v=<?= COMMIT_HASH ?>">
  <link rel="manifest" href="/res/site/manifest.json?v=<?= COMMIT_HASH ?>">
  <link rel="stylesheet" href="/res/css/sakura-dark.css?v=<?= COMMIT_HASH ?>" type="text/css">
  <link rel="stylesheet" href="/res/css/style.css?v=<?= COMMIT_HASH ?>" type="text/css">
</head>
<body>
    <h1>Login</h1>
    <form action='' method='post'>
    <p>
        <input name='user' type='text' maxlength="32" placeholder="Username">
    </p>
    <p>
        <input name='password' type='password' placeholder="Password" maxlength="32">
    </p>
    <button type="submit">Submit</button>
    </form>
</body>
</html>