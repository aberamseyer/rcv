const searchInput = document.getElementById('search-input');

(() => {
	const overlay = document.getElementById('search-input-overlay');
	const searchResults = document.getElementById('search-results');

	overlay.onclick = () => {
		searchInput.value = '';
		searchResults.innerHTML = '';
		overlay.classList.add('hidden');
	}
	searchInput.onclick = e => e.stopPropagation(); 
	searchResults.onlclick = e => e.stopPropagation();

	let timer = 0;
	let selectedIndex = -1;
	let lastQuery = '';
	document.querySelector('body').onkeydown = ({ key, target, metaKey, ctrlKey, altKey }) => {
		if ([ 'INPUT', 'SELECT' ].includes(target.nodeName))
			return;
		if (metaKey || ctrlKey || altKey || !key.match(/^[0-9a-zA-Z \.\,:"'!\-\?]$/))
			return;
		if (searchInput.value.length === 0) {
			searchInput.value += key;
			overlay.classList.remove('hidden');
			setTimeout(() => searchInput.focus(), 0);
		}
	};
	searchInput.onkeydown = e => {
		const { key, target, shiftKey, ctrlKey, metaKey } = e;

		if (key == 'Escape') {
			searchInput.value = '';
		}
		else if (key == ';') {
			searchInput.value += ':';
			e.preventDefault();
		}
		else if (key === 'Enter') {
			// searchInput.value.length restricts keystrokes in the following conditions to only when the overlay is visible
			if (searchInput.value.length && ~selectedIndex && shiftKey)
				copyToClip(searchResults.children[selectedIndex].firstChild.text.trim(), searchResults.children[selectedIndex].firstChild);
			else if (searchInput.value.length && (~selectedIndex && !(metaKey || ctrlKey)))
				window.location = searchResults.children[selectedIndex].firstChild.href;
			else if (searchInput.value.length > 2)
				window.location = `/search?q=${searchInput.value}`;
			return;
		}
		else if (searchInput.value.length && (key === 'ArrowUp' || (key === 'Tab' && shiftKey))) {
			if (~selectedIndex)
				searchResults.children[selectedIndex].classList.remove('selected');
			if (--selectedIndex <= -1)
				selectedIndex = searchResults.children.length - 1;
			if (~selectedIndex)
				searchResults.children[selectedIndex].classList.add('selected');
			return false;
		}
		else if (searchInput.value.length && (key === 'ArrowDown' || (key === 'Tab' && !shiftKey))) {
			if (~selectedIndex)
				searchResults.children[selectedIndex].classList.remove('selected');
			// selectedIndex = (selectedIndex + 1) % searchResults.children.length;
			if (++selectedIndex > searchResults.children.length - 1)
				selectedIndex = 0;
			if (~selectedIndex)
				searchResults.children[selectedIndex].classList.add('selected');
			return false;
		}

		if (!searchInput.value.length || searchInput.value.length === 1 /* current length before character pressed is added */ && key === 'Backspace') {
			overlay.classList.add('hidden');
			searchInput.value = '';
		}

		clearTimeout(timer);
		timer = setTimeout(() => {
			if (searchInput.value.length > 1 && lastQuery != searchInput.value) {
				const formData = new FormData();
				formData.append('q', searchInput.value);
				lastQuery = searchInput.value;
				doRequest("POST", "/ajax?action=verse", formData, function(request) {
					const { book_results, results, count, q } = JSON.parse(request.response);
					searchResults.innerHTML = 
						book_results.map((res, i) => 
							`<div class='verse-result'><a href='/bible/${res.book_url}'>
								<small><b>Book</b>: ${res.book}</small></a>
							</div>`).concat(
							results.map((res, i) => 
								`<div class='verse-result'><a href='/bible/${res.book}/${res.chapter}#verse-${res.verse_id}' tabindex='${i+1}'>
									<small><b>${res.abbr} ${
										["Obad.","3 John","Jude","Philem.","2 John"].includes(res.abbr)
										? res.verse
										: `${res.chapter}:${res.verse}`
									}</b> ${res.text}</small></a>
								</div>`)
							).join('');
					if (count > results.length)
						searchResults.innerHTML += `<div class='verse-result'><a href='/search?q=${q}' tabindex='21'>
							<small>...and ${count - results.length} more</small>
						</a></div>`;
					if (results.length === 0)
						searchResults.innerHTML = `<small><em>No results</em></small>`;

					selectedIndex = -1;
				});
			}
		}, 300);
	}
})();
