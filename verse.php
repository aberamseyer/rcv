<?php
/**
 * Created by Vim
 * User: user
 * Date: 2020-12-05
 * Time: 12:44
 */

$title = "Verse Lookup";
$meta_description = "Request sets of verses in the Holy Bible Recovery Version.";
$meta_canonical = "https://rcv.ramseyer.dev/verse";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

$permalink = $_GET['verses'];
?>
<h1><a href='/bible'>Verse Lookup</a></h1>

<input id='verse-input' name='q' type='text' maxlength='2000' style='width: 100%;' placeholder='e.g., Gen. 1:26; John 1:1, 14; 2 Cor. 3:18; Jude 20-21' value='<?= $permalink ?: ''?>' title='You can request a maximum of 200 verses at a time'>
<small>Recognized verses: <span id='recognized-verses' style="display: inline"></span></small>
<hr />

<div style="margin-top: 12px;" id='verses'></div>
<noscript>This page only works with JavaScript enabled. You can search manually using the <a href='/search'>Search</a> page.</noscript>
<script type='text/javascript'>
const verseContainer = document.getElementById('verses');
function copyToClip(copyText, element) {
	const html = element.innerHTML;
	element.innerText = `Copied!`;
	navigator.clipboard.writeText(copyText);
	setTimeout(() => element.innerHTML = html, 1500);
}

(() => {
	const recognizedVerses = document.getElementById('recognized-verses');
	const exceededMax = document.getElementById('exceeded-max');
	const verseInput = document.getElementById('verse-input');

	let requestTimer = 0;
	verseInput.onkeyup = e => {

		const { key } = e;

		if (verseInput.value.length > 3 && /^[\w:;\-,\.]$/.test(key) || [ 'Control', 'Meta', 'Backspace', 'Unidentified' ].includes(key)) {
			const formData = new FormData();
			formData.append('action', 'request');
			formData.append('q', verseInput.value.trim());

			const request = new XMLHttpRequest();
			request.open("POST", "/ajax");

			request.onloadend = () => {
				if (request.status === 200) {
					const { results, q } = JSON.parse(request.response);
					verseContainer.innerHTML = results
						.map(res => 
							`<div>
								<a target='_blank' href='${res.href}'><b>${res.reference}</b></a>
								${res.text}
							</div>`)
						.join('');
					recognizedVerses.innerText = results.map(res => res.reference).join('; ');

					if (results.length) {
						recognizedVerses.innerHTML += `<br>
							<a href='' onclick='copyToClip("https://rcv.ramseyer.dev/verse?verses=${encodeURIComponent(recognizedVerses.innerText)}", this); return false;'>&#128203 link to verses</a> <br>
							<a href='' onclick='copyToClip(verseContainer.innerText, this); return false;'>&#128203 verse text</a>`;
					}
				}
			}

			clearTimeout(requestTimer);
			requestTimer = setTimeout(() => request.send(formData), 200);
		}
	}

	if (verseInput.value.length)
		verseInput.onkeyup({ key: 'a' }); // manually trigger on load
	verseInput.focus();
})();
</script>
<hr />
<?php

echo nav_line();
echo copyright;
require $_SERVER['DOCUMENT_ROOT']."/inc/foot.php";
