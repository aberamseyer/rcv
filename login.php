<?php
/**
 * Created by VSCode.
 * User: user
 * Date: 2021-06-24
 * Time: 12:35
 */

if ($_SESSION['user']) {
    redirect("/bible");
}

$login = true;
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

if ($_POST['user'] && $_POST['password']) {
    $user_row = row("SELECT * FROM users WHERE user = '".db_esc($_POST['user'])."'");
    if ($user_row) {
        if (password_verify($_POST['password'], $user_row['password'])) {
            $_SESSION['user'] = $user_row;
            session_write_close();
            redirect("/bible");
        }
    }
}


$title = "Login";
$meta_description = "Login to the admin portal.";
$meta_canonical = "https://".getenv("DOMAIN")."/login";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";
?>
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
<?php

require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
