(() => {
	// init menu
	window.addEventListener('load', () => {
	  document.querySelector('body').classList.add('has-js');
	  document.getElementById('menu-href').remove();
	});

    // menu show/hide on scroll
    const menu = document.getElementById('menu');
    let scrollPos = 0, throttle = false;
    window.addEventListener('wheel', () => {
        if (!throttle) {
            const rect = document.body.getBoundingClientRect();
            if (rect.top > scrollPos)
                menu.classList.remove('hide');
            else
                menu.classList.add('hide');

            scrollPos = rect.top;
            throttle = true;
            setTimeout(() => throttle = false, 250);
        }
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
    		saveSettings();
    	});
    });
})();