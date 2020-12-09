(() => {
	const verseContainer = document.getElementById('verses');
	const recognizedVerses = document.getElementById('recognized-verses');
	const exceededMax = document.getElementById('exceeded-max');
	const verseInput = document.getElementById('verse-input');

	let requestTimer = 0;
	verseInput.onkeyup = e => {

		const { key } = e;

		if (verseInput.value.length > 3 && /^[\w:;\-,\.]$/.test(key) || [ 'Backspace', 'Unidentified' ].includes(key)) {
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
							`<div><a target='_blank' href='${res.href}'>
								<small><b>${res.reference}</b>: ${res.text}</small></a>
							</div>`)
						.join('');
					recognizedVerses.innerText = results.map(res => res.reference).join('; ');

					if (results.length) {
						recognizedVerses.innerHTML += `<small>
							<a href='' onclick='this.innerText = "Copied!"; setTimeout(() => this.innerHTML = "&#128279; to these verses", 1500); navigator.clipboard.writeText("https://rcv.ramseyer.dev/verse?verses=${encodeURIComponent(recognizedVerses.innerText)}"); return false;'>&#128279  to these verses</a></small>`;
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
