document.addEventListener('DOMContentLoaded', function(){
  // raccogliamo tutti i bottoni
  var buttons = document.querySelectorAll('.filter-flag');
  // funzione che applica il filtro
  function applyFilter(lang) {
    buttons.forEach(function(btn){
      btn.classList.toggle('active', btn.getAttribute('data-lang') === lang);
    });
    document.querySelectorAll('.gruppo-lingua').forEach(function(group){
      // se lang vuoto mostriamo tutto, altrimenti solo il gruppo corrispondente
      if (!lang || group.classList.contains('gruppo-lingua-' + lang)) {
        group.style.display = '';
      } else {
        group.style.display = 'none';
      }
    });
  }

  // click sui bottoni
  buttons.forEach(function(btn){
    btn.addEventListener('click', function(){
      var lang = this.getAttribute('data-lang');
      applyFilter(lang);
    });
  });

  // default on-load: inglese
  applyFilter('inglese');
});
