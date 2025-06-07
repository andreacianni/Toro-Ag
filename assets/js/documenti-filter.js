document.addEventListener('DOMContentLoaded', function() {
  // Se WP ci ha passato la config
  var cfg = window.toroagFilterConfig || {};
  if (cfg.currentLang === 'it') {
    // in italiano niente filtro: nascondiamo il blocco e usciamo
    var gf = document.getElementById('global-filter');
    if (gf) gf.style.display = 'none';
    return;
  }

  // Altrimenti, normale funzionamento:

  // Seleziona tutti i bottoni flag
  var buttons = document.querySelectorAll('.filter-flag');

  function applyFilter(lang) {
    buttons.forEach(function(btn) {
      btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
    });
    document
      .querySelectorAll('.gruppo-lingua')
      .forEach(function(group) {
        if (!lang || group.classList.contains('gruppo-lingua-' + lang)) {
          group.style.display = '';
        } else {
          group.style.display = 'none';
        }
      });
  }

  buttons.forEach(function(btn) {
    btn.addEventListener('click', function() {
      applyFilter(this.getAttribute('data-lang'));
    });
  });

  // Default: inglese
  applyFilter('inglese');
});
