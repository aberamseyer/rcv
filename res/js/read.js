let menu, audio1, audio2, reading = false, thingsToSpeak,
menuEL = document.createElement('li');
menuEL.innerHTML = `<a onclick='stopReading();' style="cursor:pointer;">Stop Reading</a>`;

window.addEventListener('load', function() {
  document.querySelector('body').classList.add('has-js');
  menu = document.getElementById('menu');
  audio1 = document.createElement('audio');
  audio2 = document.createElement('audio');
  document.body.appendChild(audio1);
  document.body.appendChild(audio2);
});

function playAudio(src, nextSrc) {
  return new Promise(function(resolve) {
    reading = true;
    audio1.src = src;
    audio2.src = nextSrc;

    const unload = function() {
      audio1.pause();
      [audio1, audio2].forEach(function(el) {
        el.src = '';
        el.load();
      });
    };

    const preloadNextVerse = function() {
      audio2.src = nextSrc;
      audio2.load();
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
      audio1.load();
      audio1.play().then(function() {
        reading = true;
        preloadNextVerse();
        audio1.addEventListener("ended", function() {
          reading = false;
          resolve();
        }, { capture: false, once: true });
      }).catch(err => {
        reading = false;
        resolve();
        console.log('playback error: ' + err);
      });
    }
  });
}

function startReading(id) {
  stopReading();
  const verseEl = document.getElementById(`verse-${id}`);
  const ref = verseEl.getAttribute('data-ref');
  const matches = ref.match(/.+ (?:\d+:)?(\d+)/);
  const verseNum = parseInt(matches[1]);
  if (verseNum) {
    // translate this verse into an id that file.ramseyer.dev can match
    fetch(`https://bible-api.ramseyer.dev/v2/chapter.php?key=${
        encodeURIComponent('rcv.ramseyer.dev')
      }&book=${
      window.book.replace(/(\d) /, '$1')
    }&chapter_num=${window.chapter}`)
    .then(function(response) {
      return response.json();
    })
    .then(function(obj) {
      thingsToSpeak = obj.data.versesArr.slice(verseNum - 1).map(function(verse, i) {
        return {
          id: verse.id,
          number: verseNum + i,
          src: `https://files.ramseyer.dev/tts/rcv/${verse.id}.ogg`
        };
      });

      // play each track synchronously
      menu.querySelector('ul').prepend(menuEL);
      menu.classList.add('show');

      for (let i = 0, p = Promise.resolve(); i < thingsToSpeak.length; i++) {
        p = p.then(function() {
          return new Promise(function(resolve) {
            const el = document.querySelectorAll('.verse')[ thingsToSpeak[i].number - 1 ];
            el.classList.add('highlight');
            el.scrollIntoView({ behavior: "smooth", block: "center" });
            playAudio(thingsToSpeak[i].src,
                thingsToSpeak.length[i+1] ? thingsToSpeak[i+1].src : ''
            ).then(function() {
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
    });
  }
}

function stopReading() {
  thingsToSpeak = [ ];
  document.querySelectorAll('.verse').forEach(function(x) {
    x.classList.remove('highlight');
  });
  this.playAudio('');
  menuEL.remove();
  menu.classList.remove('show');
}