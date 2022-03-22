// runs when the hebrew/greek letter is clicked next to each verse
async function toggleOriginalText(event, id, el) {
  const parentEl = el.closest('.verse');
  const textEl = parentEl.querySelector('.verse-content');
  const showOrig = parentEl.children.length < 4;
  if (showOrig) {
    doRequest("POST", `/ajax?action=original_text&id=${encodeURIComponent(id)}`, null, function(request) {
      const data = JSON.parse(request.response)
      const newEl = document.createElement('span');
      newEl.classList.add('original-text');
      newEl.innerHTML = data.map(word => 
        `<span>
            <span>${word.word}</span>
            <a target='_blan' href='/language-concordance?strongs=${word.number}&word=${encodeURIComponent(word.word)}' data-orig-text>${word.number}</a>
          </span>`).join('');
      parentEl.insertBefore(newEl, textEl);
      parentEl.querySelector('.link').classList.remove('hidden');
      textEl.classList.add('hidden');
    });
  }
  else {
    textEl.classList.remove('hidden');
    parentEl.querySelector('.original-text').remove();
    parentEl.querySelector('.link').classList.add('hidden');
  }
  event.stopImmediatePropagation();
}

// audio reading and functions
let menu, audio1, audio2, thingsToSpeak,
menuEL = document.createElement('li');
menuEL.innerHTML = `<a onclick='stopReading();' style="cursor:pointer;">Stop Reading</a><div class='emoji'>🛑</div>`;

window.addEventListener('load', () => {
  menu = document.getElementById('menu');
  audio1 = document.createElement('audio');
  audio2 = document.createElement('audio');
  document.body.appendChild(audio1);
  document.body.appendChild(audio2);
});
function playAudio(src, nextSrc) {
  return new Promise(resolve => {
    audio1.src = src;
    audio2.src = nextSrc;

    const unload = () => {
      audio1.pause();
      [ audio1, audio2 ].forEach(el => {
        el.src = '';
        el.load();
      });
    };

    if (!src)  {
      // for some reason there's nothing to speak. stop reading, remove the source
      unload();
      resolve();
    } else {
      if (!audio1.paused && audio1.src.length) {
        // we are reading currently. stop, and reset with the new content
        unload();
      }
      audio1.src = src;
      audio1.play().then(() => {
        audio1.addEventListener("ended", () => {
          resolve();
        }, { capture: false, once: true });
      }).catch(err => {
        resolve();
        console.log('playback error: ' + err);
      });
    }
  });
}
function startReading(event, id) {
  event.stopPropagation();
  stopReading().then(() => {
    const verseEl = document.getElementById(`verse-${id}`);
    const ref = verseEl.getAttribute('data-ref');
    const matches = ref.match(/.+ (?:\d+:)?(\d+)/);
    const verseNum = parseInt(matches[1]);
    if (verseNum) {
      // translate this verse into an id that files.ramseyer.dev can match
        thingsToSpeak = [ ...Array(window.verses - verseNum + 1).keys() ].map(i =>
          `https://files.ramseyer.dev/tts/rcv-ref/${window.location.pathname.replace('/bible/', '').replace('/', '_')}_${i + verseNum}.ogg`);
  
      // play each track synchronously
      menu.querySelector('ul').prepend(menuEL);
      menu.classList.add('show');
  
      for (let i = 0, p = Promise.resolve(); i < thingsToSpeak.length; i++) {
        p = p.then(() => {
          return new Promise(resolve => {
            const el = Array.from(document.querySelectorAll('.verse')).slice(verseNum - 1)[ i ];
            el.classList.add('highlight');
            el.scrollIntoView({ behavior: "smooth", block: "center" });
            playAudio(thingsToSpeak[i],
                thingsToSpeak[i+1] ? thingsToSpeak[i+1] : ''
            ).then(() => {
              el.classList.remove('highlight');
              resolve();
              if (i === thingsToSpeak.length - 1) {
                menuEL.remove();
                menu.classList.remove('show');
              }
            });
          });
        });
      }
    }
  });
}
function stopReading() {
  return new Promise(res => {
    thingsToSpeak = [ ];
    
    this.playAudio('')
    .then(() => {
      document.querySelectorAll('.verse').forEach(x => {
        x.classList.remove('highlight');
      });
      menuEL.remove();
      menu.classList.remove('show');
      res();
    });
  });
}

// scroll to verse
if (!window.location.hash) {
  const matches = window.location.search.match(/verse=(\d+)/);
  if (matches) {
    window.addEventListener('load', () => {
      setTimeout(()  => {
        const el = document.querySelectorAll('.verse')[ parseInt(matches[1]) - 1 ];
        el.classList.add('highlight');
        el.scrollIntoView();
        setTimeout(() => el.classList.remove('highlight'), 1000);
      }, 250);
    });
  }
} else {
  const el = document.getElementById(`verse-${window.location.hash.replace('#verse-', '')}`);
  if (el) {
    el.classList.add('highlight');
    setTimeout(() => el.classList.remove('highlight'), 1000);
  }
}

// verse highlight on click
function removeAllPopups() {
  document.querySelectorAll('.hover-verse').forEach(el => el.remove());
}
document.querySelectorAll('.verse').forEach(v => {
  v.addEventListener('click', e => {
    document.querySelectorAll('.verse').forEach(el => {
      if (!el.isEqualNode(v))
        el.classList.remove('highlight')
    });
    v.classList[
      v.classList.contains('highlight')
        ? 'remove' : 'add'
    ]('highlight');
    e.stopPropagation();

    removeAllPopups();
  });
});
// deselect verse on outside click
const htmlNode = document.querySelector('html');
htmlNode.addEventListener('click', e => {
  document.querySelectorAll('.verse').forEach(el => {
    el.classList.remove('highlight');
  });
  removeAllPopups();
});
document.querySelectorAll('.tooltip').forEach(v => {
  v.addEventListener('click', e =>
    e.stopPropagation());
});

// navigate using ← and →
const leftArrow = document.querySelector(`a[rel=prev]`);
const rightArrow = document.querySelector(`a[rel=next]`);

htmlNode.addEventListener('keyup', e => {
  const checkReferrer = href => {
    if (document.referrer.includes(href))
      window.history.back();
  }

  if (leftArrow && e.key === `ArrowLeft`) {
    checkReferrer(leftArrow.getAttribute('href'));
    window.location = leftArrow.getAttribute('href');
  }
  else if (rightArrow && e.key === `ArrowRight`) {
    checkReferrer(rightArrow.getAttribute('href'));
    window.location = rightArrow.getAttribute('href');
  }
});

// verse popup for a-tags
document.querySelectorAll('[verse-hover]').forEach(aEl => {
  let newEl = document.createElement('div');
  const handleMouseEnter = e => {
    if (aEl.querySelectorAll('.hover-verse').length === 0) {
      const matches = aEl.href.match(/\w+\/\d+#verse-(\d+)/);
      const verseRange = aEl.innerText;
      if (matches.length) {
        const formData = new FormData();
        formData.append('range', verseRange);
        formData.append('id', +matches[1]);

        doRequest("POST", "/ajax?action=a-verse", formData, request => {
          const results = JSON.parse(request.response);
          if (results.length) {
            newEl.innerHTML = results.map(res => 
              `<div>
                <b><a href='${res.href}' target='_blank'>${res.reference}</a></b>
                  &nbsp;&nbsp;
                  <span>${res.content}</span>
                </div>`
            ).join('');
            document.querySelectorAll('.hover-verse').forEach(el => el.remove());
            
            newEl.classList.add('hover-verse');
            newEl.addEventListener('mouseleave', () => newEl.remove());

            const rect = aEl.getBoundingClientRect();
            
            newEl.style.left = rect.x + 200 > window.innerWidth // 200 bc that's the width of a .hover-verse element
              ? `-200px`
              : ``;

            aEl.appendChild(newEl);
          }
        });
      }
    }
  };
  aEl.addEventListener('click', e => {
    e.stopPropagation();
    if (e.target.isSameNode(aEl))
      e.preventDefault();
  });
  aEl.addEventListener('mouseenter', handleMouseEnter);
});