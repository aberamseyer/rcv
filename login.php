<?php
/**
 * Created by Sublime Text 3.
 * User: user
 * Date: 2021-01-27
 * Time: 13:23
 */

$no_stats = true;
$admin = true;
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

if ($_SESSION['user']) {
	redirect("/admin");
}
if ($_POST['user'] && $_POST['password']) {
	$user_row = row("SELECT * FROM admin.users WHERE username = '".db_esc($_POST['user'])."'");
	if ($user_row) {
		if (password_verify($_POST['password'], $user_row['password'])) {
			$_SESSION['user'] = $user_row;
			redirect("/admin");
		}
	}
}
session_write_close();

$title = "Login";
$meta_description = "Login to access the admin page";
$meta_canonical = "https://rcv.ramseyer.dev/login";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";
?>
<h1><a href='/bible'>Login</a></h1>
<form action='' method='post'>
	<p>
		<input name='user' type='text' maxlength="64" placeholder="Username">
	</p>
	<p>
		<input name='password' type='password' placeholder="Password">
	</p>
	<button type="submit">Submit</button>
</form>
<?php

require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";