function copyToClip(copyText, element) {
	const html = element.innerHTML;
	element.innerText = `Copied!`;
	navigator.clipboard.writeText(copyText);
	setTimeout(() => element.innerHTML = html, 1500);
}

function doRequest(method, url, body, onsuccess) {
	const request = new XMLHttpRequest();
	request.open(method, url);
	request.onloadend = function() {
		if (request.status === 200) {
			onsuccess(request);
		}
	};
	request.send(body);
}

(() => {
	// init menu
	window.addEventListener('load', () => {
	  document.querySelector('body').classList.add('has-js');
	  document.getElementById('menu-href').remove();
	});

    // menu show/hide on scroll
    const menu = document.getElementById('menu');
    let scrollPos = 0;
	window.addEventListener("ontouchstart" in window
        ? 'touchmove'
        : 'wheel',
    () => {
        const top = document.documentElement.scrollTop;
        if (top < scrollPos)
            menu.classList.remove('hide');
        else
            menu.classList.add('hide');
        scrollPos = top;
    });

    // menu changing settings
    let settings;
    const html = document.getElementsByTagName(`HTML`)[0];
    const jsMenuEls = document.querySelectorAll('#menu-js span');
	settings = JSON.parse(localStorage.getItem('settings'));
	if (!settings) {
    	settings = {
    		theme: 'dark',
    		notes: true,
    		serif: true,
    	};
    }
	
	function saveSettings() {
    	localStorage.setItem('settings', JSON.stringify(settings));
    }
    function setTheme(newVal) {
		if (newVal === `light`) {
			html.classList.add(`light`);
			settings.theme = `light`;
			jsMenuEls[0].innerText = `Switch to dark theme`;
		}
		else {
			html.classList.remove(`light`);
			settings.theme = `dark`;
			jsMenuEls[0].innerText = `Switch to light theme`;
		}
    }
    function setNotes(newVal) {
		if (newVal) {
			html.classList.remove(`hide-notes`);
			settings.notes = true;
			jsMenuEls[1].innerText = `Hide notes`;
		}
		else {
			html.classList.add(`hide-notes`);
			settings.notes = false;
			jsMenuEls[1].innerText = `Show notes`;
		}
    }
    function setFont(newVal) {
    	if (newVal) {
			html.classList.add(`serif`);
			settings.serif = true;
			jsMenuEls[2].innerText = `Use sans-serif font`;
		}
		else {
			html.classList.remove(`serif`);
			settings.serif = false;
			jsMenuEls[2].innerText = `Use serif font`;
		}
    }
    setTheme(settings.theme);
    setNotes(settings.notes);
    setFont(settings.serif);

    // js menu at top right
    document.querySelectorAll(`[data-toggle]`).forEach(el => {
    	el.addEventListener('click', () => {
    		const toggle = el.dataset.toggle;
    		if (toggle === `theme`)
    			setTheme(settings.theme === `dark` ? `light` : `dark`);
    		else if (toggle === `layout`)
    			setNotes(!settings.notes);
    		else if (toggle === `font`)
    			setFont(!settings.serif);
    		else if (toggle === `random`)
    			window.location = `/bible?random`;
    		else if (toggle === `help`)
    			window.location = `/help`;
    		else if (toggle === `release`)
    			window.location = `/release-notes`;
    		saveSettings();
    	});
    });

    // global shortcut to verse lookup page
    document.querySelector('body').addEventListener('keydown', e => {
    	if (e.key === 'e' && (e.metaKey || e.ctrlKey)) {
    		if (window.location.pathname != '/verse')
    			window.location = '/verse';
    	}
    });

    // check for update
    const ignoreUpdate = localStorage.getItem('ignore_update');
    if (!ignoreUpdate ||  Date.now() - parseInt(ignoreUpdate) > 1000*60*60*24*3) { // ignore updates for 3 days before re-prompting
	    doRequest("POST", "/ajax?action=check_update", null, function(request) {
			const t1 = JSON.parse(request.response).last_update;
			doRequest("POST", "https://rcv-eba.herokuapp.com/ajax?action=check_update", null, function(request) {
				const t2 = JSON.parse(request.response).last_update;
				if (t1.localeCompare(t2) < 0) {
					if (confirm(`Download update?`)) {
						const a = document.createElement('a');
						a.href = `https://s3.us-west-002.backblazeb2.com/rcv-eba/archives/${t2}`;
						a.target = `_blank`;
						a.click();
						alert(`1. Extract the files over top your current files, replacing them\n2. refresh the page\n3. clear browser cache if things seem weird`);
					}
					else {
						localStorage.setItem('ignore_update', Date.now());
					}
				}
			});
	    });
	}

	const dontShowReleaseNotes = localStorage.getItem('dont_show_release_notes');
	if (!dontShowReleaseNotes) {
		localStorage.setItem('dont_show_release_notes', "1");
		window.location = '/release-notes';

	}
})();