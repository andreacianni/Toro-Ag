document.addEventListener('DOMContentLoaded', function() {
  // Prendiamo tutti i <sup> in pagina
  var allSup = document.querySelectorAll('sup');

  allSup.forEach(function(el) {
    var txt = el.textContent.trim();

    if ( txt === '™' ) {
      el.classList.add('tm');
    }
    else if ( txt === '®' ) {
      el.classList.add('r');
    }
  });
});
