(() => {
	const overlay = document.getElementById('search-input-overlay');
	const searchInput = document.getElementById('search-input');
	const searchResults = document.getElementById('search-results');

	overlay.onclick = () => {
		searchInput.innerHTML = '';
		searchResults.innerHTML = '';
		overlay.classList.add('hidden');
	}
	searchInput.onclick = e => e.stopPropagation(); 
	searchResults.onlclick = e => e.stopPropagation();

	let timer = 0;
	document.querySelector('body').onkeydown = e => {
		const { key, target } = e;

		if ([ 'INPUT', 'SELECT' ].includes(target.nodeName))
			return;

		if (key == 'Escape') {
			searchInput.innerHTML = '';
		}
		else if (key == ';') {
			searchInput.innerHTML += ':';
		}
		else if (key == 'Backspace') {
			searchInput.innerHTML = searchInput.innerHTML.split('')
				.reverse()
				.slice(1)
				.reverse()
				.join('');
		}
		else if (key === 'Enter') {
			if (searchInput.innerHTML.length > 2)
				window.location = `/search?q=${searchInput.innerHTML}`;
			return;
		}
		else if (!e.metaKey && !e.ctrlKey && key.match(/^[0-9a-zA-Z \.\,:"'!\-\?]$/)) {
			searchInput.innerHTML += key;
			if (key === ' ')
				e.preventDefault();
		}
		else {
			return;
		}

		overlay.classList[
			searchInput.innerHTML.length
				? 'remove'
				: 'add'
		]('hidden');

		if (searchInput.innerHTML.length > 1) {
			const formData = new FormData();
			formData.append('q', searchInput.innerHTML);

			const request = new XMLHttpRequest();
			request.open("POST", "/ajax?action=verse");

			request.onloadend = () => {
				if (request.status === 200) {
					const { results, count, q } = JSON.parse(request.response);
					searchResults.innerHTML = results
						.map((res, i) => 
							`<div class='verse-result'><a href='/bible/${res.book}/${res.chapter}?verse=${res.verse}' tabindex='${i+1}'>
								<small><b>${res.abbr} ${res.chapter}:${res.verse}</b>: ${res.text}</small></a>
							</div>`)
						.join('');
					if (count > results.length)
						searchResults.innerHTML += `<div class='verse-result'><a href='/search?q=${q}' tabindex='21'>
							<small>...and ${count - results.length} more</small>
						</a></div>`;
					if (results.length === 0)
						searchResults.innerHTML = `<small><em>No results</em></small>`;
				}
			}

			clearTimeout(timer);
			timer = setTimeout(() => request.send(formData), 300);
		}
	}
})();
