<?php
/**
 * Created by Vim
 * User: user
 * Date: 2020-12-05
 * Time: 12:44
 */

$title = "Verse Lookup";
$meta_description = "Request sets of verses in the Holy Bible Recovery Version.";
$meta_canonical = "https://".getenv("DOMAIN")."/verse";
require $_SERVER['DOCUMENT_ROOT']."/inc/init.php";
require $_SERVER['DOCUMENT_ROOT']."/inc/head.php";

$permalink = htmlentities($_GET['verses'], ENT_HTML5);
?>
<h1><a href='/bible'>Verse Lookup</a></h1>

<input id='verse-input' name='q' type='search' maxlength='2000' style='width: 100%' placeholder='e.g., Gen. 1:26; John 1:1, 14; 2 Cor. 3:18; Jude 20-21' value='<?= $permalink ?: ''?>' title='You can request a maximum of 200 unique verses at a time'>
<details class="mobile">
	<summary tabindex='-1'><small class='smaller' style='display: inline;'>Quick add:</small></summary>
	<style>
		.button {
			width: 15%;
		    height: 40px;
		    margin: 4px;
		    font-size: 2.2rem;
		    font-weight: bold;
		}
		#recognized-verses {
			display: inline;
			font-size: 1.2rem;
		}
	</style>
	<div class='justify'>
		<?php foreach([ ',', '-', ':', ';' ] as $c): ?>
			<button type="button" class="button"><?= $c ?></button>
		<?php endforeach; ?>
	</div> <br>
	<div class='justify'>
		<?php foreach(range(1, 10) as $i): ?>
			<button type="button" class="button"><?= $i % 10 ?></button>
		<?php endforeach; ?>
	</div>
</details>
<small class='smaller'>Recognized verses: <span id='recognized-verses'></span></small>
<hr id='hr' class='hidden' />

<div style="margin-top: 12px;" id='verses'></div>
<noscript>This page only works with JavaScript enabled. You can search manually using the <a href='/search'>Search</a> page.</noscript>
<script>
const verseContainer = document.getElementById('verses');

(() => {
	const recognizedVerses = document.getElementById('recognized-verses');
	const exceededMax = document.getElementById('exceeded-max');
	const verseInput = document.getElementById('verse-input');
	const hr = document.getElementById('hr');

	// mobile quick-add buttons
	document.querySelectorAll('.button').forEach(el => {
		el.addEventListener('click', () => {
			verseInput.value += el.innerText;
			verseInput.onkeyup({ key: el.innerText });
			verseInput.focus();
		});
	});

	// search on input
	let requestTimer = 0;
	verseInput.onkeyup = e => {

		const { key } = e;

		if (verseInput.value.length > 3 && /^[\w:;\-,\.]$/.test(key) || [ 'Control', 'Meta', 'Backspace', 'Unidentified' ].includes(key)) {
			const formData = new FormData();
			formData.append('q', verseInput.value.trim());

			const request = new XMLHttpRequest();
			request.open("POST", "/ajax?action=request");

			request.onloadend = () => {
				if (request.status === 200) {
					const { results, q, requested } = JSON.parse(request.response);
					verseContainer.innerHTML = results
						.map(res => 
							`<div>
								<a target='_blank' href='${res.href}'><b>${res.reference}</b></a>
								${res.text}
							</div>`)
						.join('');
					recognizedVerses.innerText = requested;

					if (results.length) {
						recognizedVerses.innerHTML += `<br>
							<a href='' onclick='copyToClip(verseContainer.innerText, this); return false;'><div class="emoji">&#128203</div>&nbsp;&nbsp;verses with refs</a> <br>
							<a href='' onclick='copyToClip("${results.map(r => r.text).join(' ')}\\n${requested}", this); return false;'><div class="emoji">&#128203</div>&nbsp;&nbsp;joined verse text</a> <br>
							<a href='' onclick='copyToClip("<?= $_SERVER['REQUEST_SCHEME'] ?>://<?= getenv("DOMAIN") ?: $_SERVER['HTTP_HOST'] ?>/verse?verses=${encodeURIComponent(recognizedVerses.innerText)}", this); return false;'><div class="emoji">&#128203</div>&nbsp;&nbsp;link to verses</a>`;
					}
					hr.classList[results.length === 0 ? 'add' : 'remove']('hidden'); // hide <hr> if no results
				}
			}

			clearTimeout(requestTimer);
			const lastInput = verseInput.value.split(';').pop().trim();
			requestTimer = setTimeout(() => request.send(formData), /\d+[:\-]?$/.test(lastInput) ? 500 : 200);
		}
	}

	// focus verse bar on left bracket press
	document.querySelector('body').addEventListener('keyup', ({ key }) => {
		if (key === "[") {
			if (!searchInput.value.length)
				verseInput.focus();
		}
	});

	// init
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
