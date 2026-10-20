<?php
// Usage: php extras/add-user.php <username>  (creates or resets the password)

if (PHP_SAPI !== 'cli' || $argc !== 2) {
	fwrite(STDERR, "usage: php extras/add-user.php <username>\n");
	exit(1);
}

$user = $argv[1];
fwrite(STDOUT, "Password for $user: ");
system('stty -echo');
$password = trim(fgets(STDIN));
system('stty echo');
fwrite(STDOUT, "\n");
if ($password === '') {
	fwrite(STDERR, "empty password\n");
	exit(1);
}

$db = new SQLite3(__DIR__."/sqlite3/sessions.db");
$hash = password_hash($password, PASSWORD_DEFAULT);
$existing = $db->querySingle("SELECT id FROM users WHERE user = '".$db->escapeString($user)."'");
$stmt = $existing
	? $db->prepare("UPDATE users SET password = :hash WHERE id = :id")
	: $db->prepare("INSERT INTO users (user, password) VALUES (:user, :hash)");
$stmt->bindValue(':hash', $hash);
$existing ? $stmt->bindValue(':id', $existing) : $stmt->bindValue(':user', $user);
$stmt->execute();
fwrite(STDOUT, ($existing ? "updated" : "added")." $user\n");
