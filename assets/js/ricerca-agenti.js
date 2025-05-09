(function($){
  $('#ricerca-agenti-form').on('submit', function(e){
    e.preventDefault();
    var territorio = $('#territorio-select').val();
    var $container = $('#risultati-agenti');
    
    $container.html('<p>Caricamento in corso…</p>');
    
    $.ajax({
      url: ricercaAgenti.ajax_url,
      method: 'POST',
      data: {
        action:   'ricerca_agenti',
        nonce:    ricercaAgenti.nonce,
        territorio: territorio
      },
      success: function(res) {
        if (!res.success) {
          $container.html('<p>Errore nella ricerca.</p>');
          return;
        }
        var list = res.data;
        if (list.length === 0) {
          $container.html('<p>Nessun agente attivo per questo territorio.</p>');
          return;
        }
        /*
        var html = '<div class="row">';
        
        list.forEach(function(a){
        html +=
        '<div class="col-md-4 mb-4">'+
        '<div class="card shadow-sm">'+
        '<div class="card-body">'+
        '<h5 class="card-title">'+ a.display_name +'</h5>'+
        (a.cellulare? '<p><strong>Cell:</strong> '+ a.cellulare +'</p>' : '')+
        (a.telefono?  '<p><strong>Tel:</strong> '+ a.telefono  +'</p>' : '')+
        '<p><strong>Email:</strong> '+ a.user_email +'</p>'+
        (a.indirizzo? '<p><strong>Indirizzo:</strong> '+ a.indirizzo +'</p>' : '')+
        (a.maps_link
        ? '<p><a href="' + a.maps_link + '" target="_blank" rel="noopener">Visualizza la Mappa</a></p>'
        : ''
        ) +
        '</div>'+
        '</div>'+
        '</div>';
        });
        html += '</div>';
        $container.html(html);
        */
        // Inizio tabella Bootstrap con allineamento verticale al centro
        var html = ''
        + '<table id="risultato-ricerca-territorio" class="table align-middle table-striped">'
        +   '<thead>'
        +     '<tr>'
        +       '<th>Denominazione</th>'
        +       '<th>Contatti</th>'
        +       '<th>Indirizzo</th>'
        +       '<th class="text-center">Mappa</th>'
        +     '</tr>'
        +   '</thead>'
        +   '<tbody>';
        +
        list.forEach(function(a){
          // Costruisco i contatti su più righe
          var contacts = '';
          if (a.cellulare) {
            // rimuovo tutto tranne cifre per creare il tel:+39...
            var cellNum = a.cellulare.replace(/\D/g, '');
            contacts += '<strong>Cell:</strong> '
            + '<a href="tel:+39' + cellNum + '">'
            + a.cellulare
            + '</a><br>';
          }
          if (a.telefono) {
            var telNum = a.telefono.replace(/\D/g, '');
            contacts += '<strong>Tel:</strong> '
            + '<a href="tel:+39' + telNum + '">'
            + a.telefono
            + '</a><br>';
          }
          if (a.user_email) {
            contacts += '<strong>Email:</strong> '
            + '<a href="mailto:' + a.user_email + '">'
            + a.user_email
            + '</a><br>';
          }
          
          // Icona/link mappa (centrata)
          var mapIcon = a.maps_link
          ? '<a href="' + a.maps_link + '" target="_blank" rel="noopener" ><i class="fs-5 bi bi-pin-map-fill"></i></a>'
          : '';
          
          html += ''
          + '<tr>'
          +   '<td>' + a.display_name + '</td>'
          +   '<td>' + contacts + '</td>'
          +   '<td>' + (a.indirizzo || '') + '</td>'
          +   '<td class="text-center">' + mapIcon + '</td>'
          + '</tr>';
        });
        
        html += ''
        +   '</tbody>'
        + '</table>';
        $container.html(html);
        
      },
      error: function(){
        $container.html('<p>Errore nella richiesta AJAX.</p>');
      }
    });
  });
})(jQuery);
