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
			formData.append('action', 'verse');
			formData.append('q', searchInput.innerHTML);

			const request = new XMLHttpRequest();
			request.open("POST", "/ajax");

			request.onloadend = () => {
				if (request.status === 200) {
					const { results, count, q } = JSON.parse(request.response);
					searchResults.innerHTML = results
						.map(res => 
							`<div class='verse-result'><a href='/bible/${res.book.replaceAll(/ /g, '_')}/${res.chapter}?verse=${res.verse}'>
								<small><b>${res.abbr} ${res.chapter}:${res.verse}</b>: ${res.text}</small></a>
							</div>`)
						.join('');
					if (count > results.length)
						searchResults.innerHTML += `<div class='verse-result'><a href='/search?q=${q}'>
							<small>...and ${count - results.length} more</small>
						</a></div>`;
				}
			}

			clearTimeout(timer);
			timer = setTimeout(() => request.send(formData), 300);
		}
	}
})();
