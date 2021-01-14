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
