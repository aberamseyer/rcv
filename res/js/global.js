function copyToClip(copyText, element, decode) {
	if (decode)
		copyText = decodeURIComponent(copyText);
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
	// init sidebar
	window.addEventListener('load', () => {
	  document.querySelector('body').classList.add('has-js');
		const sidebar = document.getElementById('sidebar');
		
		let mouseStartedMoving = false;
		let mouseMoved = false;
		const MINIMUM_MOUSE_MOVE_TIME = 100;
		
		setInterval(() => { 
			if (!mouseMoved && mouseStartedMoving) {
				sidebar.classList.remove('active')
				mouseStartedMoving = false;
			}
			mouseMoved = false;
		}, MINIMUM_MOUSE_MOVE_TIME);
		
		let startTime = 0;
		window.onmousemove = function(e) {
			if (e.clientX < Math.min(window.innerWidth / 4, 75) &&
				Date.now() - startTime > MINIMUM_MOUSE_MOVE_TIME) {
				sidebar.classList.add('active');
				startTime = Date.now();
			}
			mouseStartedMoving = true;
			mouseMoved = true;
		}

		document.querySelectorAll('#navigation a').forEach(el => {
			if (window.location.href.includes(el.href)) {
				el.classList.add('active');
			}
		});
	});
	document.querySelector('html').addEventListener("touchstart", startTouch, false);
  document.querySelector('html').addEventListener("touchmove", moveTouch, false);

	// Swipe Up / Down / Left / Right
  let initialX = null;
  let initialY = null;

  function startTouch(e) {
    initialX = e.touches[0].clientX;
    initialY = e.touches[0].clientY;
  };

  function moveTouch(e) {
    if (initialX === null) {
      return;
    }

    if (initialY === null) {
      return;
    }

    let currentX = e.touches[0].clientX;
    let currentY = e.touches[0].clientY;

    let diffX = initialX - currentX;
    let diffY = initialY - currentY;

    if (Math.abs(diffX) > Math.abs(diffY)) {
      // sliding horizontally
      if (diffX > 10) {
        // swiped left
        console.log("swiped left");
				sidebar.classList.remove('swiped');
      } else if (diffX < -10) {
				// swiped right
        console.log("swiped right");
				sidebar.classList.add('swiped');
      }  
    } else {
      // sliding vertically
      if (diffY > 0) {
        // swiped up
      } else {
        // swiped down
      }  
    }

    initialX = null;
    initialY = null;
  };

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
			saveSettings();
		});
	});

	// global shortcut to verse lookup page
	document.querySelector('body').addEventListener('keydown', e => {
		if (e.key === 'u' && (e.metaKey || e.ctrlKey)) {
			if (window.location.pathname != '/verse')
				window.location = '/verse';
		}
	});

	// check for update
	const ignoreUpdate = localStorage.getItem('ignore_update');
	if (!ignoreUpdate ||  Date.now() - parseInt(ignoreUpdate) > 1000*60*60*24*3) { // ignore updates for 3 days before re-prompting
		doRequest("GET", "/ajax?action=check_update&domain=" + encodeURIComponent(window.location.hostname), null, request => {
			const url = JSON.parse(request.response).url;
			if (url && confirm(`Download update?`)) {
				const a = document.createElement('a');
				a.href = url; a.target = `_blank`;
				a.click();
				alert(`1. Extract the files over top your current files, replacing them\n2. Refresh the page\n3. Clear browser cache if things seem weird`);
			}
			else {
				localStorage.setItem('ignore_update', Date.now());
			}
		});
	}

	const dontShowReleaseNotes = localStorage.getItem('dont_show_release_notes');
	if (!dontShowReleaseNotes) {
		localStorage.setItem('dont_show_release_notes', "1");
		window.location = '/release-notes';
	}

	// reveal "jump to top" button
	if (window.innerHeight * 3 <= document.querySelector('html').getBoundingClientRect().height) {
		document.getElementById('to-top').classList.remove('hidden');
	}
})();
