document.addEventListener('DOMContentLoaded', function() {
  var cfg = window.toroagFilterConfig || {};

  // Se siamo in italiano, niente filtro
  if (cfg.currentLang === 'it') {
    var gf = document.getElementById('global-filter');
    if (gf) gf.style.display = 'none';
    return;
  }

  // Raccogliamo tutti i bottoni flag
  var buttons = document.querySelectorAll('.filter-flag');

  function applyFilter(lang) {
    // 1) Mostra/nascondi i gruppi di lingua
    document.querySelectorAll('.gruppo-lingua').forEach(function(group) {
      if (!lang || group.classList.contains('gruppo-lingua-' + lang)) {
        group.style.display = '';
      } else {
        group.style.display = 'none';
      }
    });

    // 2) Evidenzia il bottone attivo
    buttons.forEach(function(btn) {
      btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
    });

    // 3) Nascondi le card che non contengono gruppi visibili
    document.querySelectorAll('.documenti-download-grid > .col-12, .documenti-download-grid > [class*="col-"]').forEach(function(card) {
      var hasVisible = card.querySelector('.gruppo-lingua:not([style*="display: none"])');
      card.style.display = hasVisible ? '' : 'none';
    });

    // 4) Nascondi le righe della lista che non contengono gruppi visibili
    document.querySelectorAll('.documenti-download-list > li').forEach(function(item) {
      var hasVisible = item.querySelector('.gruppo-lingua:not([style*="display: none"])');
      item.style.display = hasVisible ? '' : 'none';
    });

    // 5) Nascondi titoli di "tipo di prodotto" senza prodotti visibili
    document.querySelectorAll('h5.text-bg-dark').forEach(function(header) {
      // individua il container subito dopo l'H5
      var next = header.nextElementSibling;
      while (next && next.nodeType !== 1) {
        next = next.nextSibling;
      }
      if (!next) return;

      var hasVisibleItem = false;
      // CARD layout?
      if (next.matches('.documenti-download-grid')) {
        hasVisibleItem = !!next.querySelector('.col-12:not([style*="display: none"]), [class*="col-"]:not([style*="display: none"])');
      }
      // GRID layout?
      else if (next.matches('.documenti-download-list')) {
        hasVisibleItem = !!next.querySelector('li:not([style*="display: none"])');
      }

      if (!hasVisibleItem) {
        header.style.display = 'none';
        next.style.display   = 'none';
      } else {
        header.style.display = '';
        next.style.display   = '';
      }
    });
  }

  // 6) Bind dei click sui bottoni
  buttons.forEach(function(btn) {
    btn.addEventListener('click', function() {
      applyFilter(this.getAttribute('data-lang'));
    });
  });

  // 7) All’avvio, filtro su “inglese”
  applyFilter('inglese');
});