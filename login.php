<?php
/**
 * Created by VSCode.
 * User: user
 * Date: 2021-06-24
 * Time: 12:35
 */

$login = true;

require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

// only same-site paths, so ?thru= can't send users to another host
$thru = $_REQUEST['thru'];
if (!is_string($thru) || !preg_match('#^/(?![/\\\\])#', $thru)) {
    $thru = "/bible";
}

if ($_SESSION['user']) {
    redirect($thru);
}

if ($_POST['user'] && $_POST['password']) {
    $user_row = row("SELECT * FROM users WHERE user = '".db_esc($_POST['user'], s_db())."'", s_db());
    if ($user_row && password_verify($_POST['password'], $user_row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $user_row['user'];
        session_write_close();
        redirect($thru);
    }
    $error = "Wrong username or password.";
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
    <?php if ($error): ?><p><?= $error ?></p><?php endif; ?>
    <form action='' method='post'>
    <input type='hidden' name='thru' value='<?= htmlspecialchars($thru, ENT_QUOTES) ?>'>
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