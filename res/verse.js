const verseContainer = document.getElementById('verses');
const recognizedVerses = document.getElementById('recognized-verses');
const exceededMax = document.getElementById('exceeded-max');
const verseInput = document.getElementById('verse-input');

let requestTimer = 0;
verseInput.onkeyup = e => {

	const { key } = e;

	if (verseInput.value.length > 3 && /^\w$/.test(key) || 1) { // TODO make better
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
				recognizedVerses.innerText = results.map(res => res.reference).join(', ');
			}
		}

		clearTimeout(requestTimer);
		requestTimer = setTimeout(() => request.send(formData), 200);
	}
}
