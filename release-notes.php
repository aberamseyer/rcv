<?php

require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";

$title = "Release Notes";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

?>
<h1><a href='/bible'>Release Notes</a></h1>
<small>Press "Enter" to return to the home page</small>
<hr>
<p class='serif-text'>
<?= file_get_contents($_SERVER['DOCUMENT_ROOT']."/extras/release_notes.html") ?>
</p>
<script>
	document.documentElement.addEventListener('keyup', ({ key }) => {
		if (key === 'Enter')
			window.location = '/bible';
	});
</script>
<?php

echo "<hr />".nav_line();

require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
