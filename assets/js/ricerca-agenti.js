(function($){
  
  // Gestione cambio regione
  $('#regione-select').on('change', function(e){
    var regione = $(this).val();
    var $provinciaSelect = $('#territorio-select');
    var $submitBtn = $('.btn-search');
    
    // Reset provincia select
    $provinciaSelect.empty().append('<option value="" disabled selected>— Seleziona la provincia —</option>');
    
    if (regione && window.regioniProvince[regione]) {
      // Popola le province della regione selezionata
      var province = window.regioniProvince[regione];
      var provinceConAgenti = window.provinceConAgenti || [];
      
      // Filtra solo le province che hanno agenti attivi
      var provinceDisponibili = province.filter(function(provincia) {
        return provinceConAgenti.includes(provincia);
      });
      
      if (provinceDisponibili.length > 0) {
        provinceDisponibili.forEach(function(provincia) {
          $provinciaSelect.append('<option value="' + provincia + '">' + provincia + '</option>');
        });
        $provinciaSelect.prop('disabled', false);
      } else {
        $provinciaSelect.append('<option value="" disabled>— Nessun agente in questa regione —</option>');
        $provinciaSelect.prop('disabled', true);
      }
    } else {
      $provinciaSelect.prop('disabled', true);
    }
    
    // Disabilita il pulsante finché non viene selezionata una provincia
    $submitBtn.prop('disabled', true);
  });
  
  // Gestione cambio provincia
  $('#territorio-select').on('change', function(e){
    var provincia = $(this).val();
    var $submitBtn = $('.btn-search');
    
    // Abilita/disabilita il pulsante in base alla selezione
    $submitBtn.prop('disabled', !provincia);
  });
  
  // Gestione submit form
  $('#ricerca-agenti-form').on('submit', function(e){
    e.preventDefault();
    var territorio = $('#territorio-select').val();
    var $container = $('#risultati-agenti');
    var $submitBtn = $('.btn-search');
    
    // Feedback visivo durante il caricamento
    $submitBtn.prop('disabled', true).html('🔄 Ricerca in corso...');
    $container.html('<div class="text-center p-4"><div class="spinner-border text-danger" role="status"><span class="visually-hidden">Caricamento...</span></div><p class="mt-2">Ricerca agenti in corso...</p></div>');
    
    $.ajax({
      url: ricercaAgenti.ajax_url,
      method: 'POST',
      data: {
        action:   'ricerca_agenti',
        nonce:    ricercaAgenti.nonce,
        territorio: territorio
      },
      success: function(res) {
        // Ripristina il pulsante
        $submitBtn.prop('disabled', false).html('🔍 Cerca Agenti');
        
        if (!res.success) {
          $container.html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Errore nella ricerca. Riprova.</div>');
          return;
        }
        
        var list = res.data;
        if (list.length === 0) {
          $container.html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> Nessun agente attivo trovato per <strong>' + territorio + '</strong>.</div>');
          return;
        }
        
        // Costruisci la tabella dei risultati
        var html = ''
        + '<div class="results-header mb-3">'
        +   '<h4><i class="bi bi-people-fill text-danger"></i> Agenti trovati in <strong>' + territorio + '</strong> (' + list.length + ')</h4>'
        + '</div>'
        + '<table id="risultato-ricerca-territorio" class="table align-middle table-striped table-hover shadow-sm">'
        +   '<thead class="table-dark">'
        +     '<tr>'
        +       '<th><i class="bi bi-person-badge"></i> Denominazione</th>'
        +       '<th><i class="bi bi-telephone"></i> Contatti</th>'
        +       '<th><i class="bi bi-geo-alt"></i> Indirizzo</th>'
        +       '<th class="text-center"><i class="bi bi-map"></i> Mappa</th>'
        +     '</tr>'
        +   '</thead>'
        +   '<tbody>';
        
        list.forEach(function(a){
          // Costruisco i contatti su più righe
          var contacts = '';
          if (a.cellulare) {
            // rimuovo tutto tranne cifre per creare il tel:+39...
            var cellNum = a.cellulare.replace(/\D/g, '');
            contacts += '<div><strong><i class="bi bi-phone"></i> Cell:</strong> '
            + '<a href="tel:+39' + cellNum + '" class="text-decoration-none">'
            + a.cellulare
            + '</a></div>';
          }
          if (a.telefono) {
            var telNum = a.telefono.replace(/\D/g, '');
            contacts += '<div><strong><i class="bi bi-telephone"></i> Tel:</strong> '
            + '<a href="tel:+39' + telNum + '" class="text-decoration-none">'
            + a.telefono
            + '</a></div>';
          }
          if (a.user_email) {
            contacts += '<div><strong><i class="bi bi-envelope"></i> Email:</strong> '
            + '<a href="mailto:' + a.user_email + '" class="text-decoration-none">'
            + a.user_email
            + '</a></div>';
          }
          
          // Icona/link mappa (centrata)
          var mapIcon = a.maps_link
          ? '<a href="' + a.maps_link + '" target="_blank" rel="noopener" class="btn btn-outline-danger btn-sm" title="Visualizza su Google Maps"><i class="bi bi-pin-map-fill"></i></a>'
          : '<span class="text-muted">—</span>';
          
          html += ''
          + '<tr>'
          +   '<td><strong>' + a.display_name + '</strong></td>'
          +   '<td>' + contacts + '</td>'
          +   '<td>' + (a.indirizzo || '<span class="text-muted">Non specificato</span>') + '</td>'
          +   '<td class="text-center">' + mapIcon + '</td>'
          + '</tr>';
        });
        
        html += ''
        +   '</tbody>'
        + '</table>';
        
        $container.html(html);
      },
      error: function(){
        // Ripristina il pulsante
        $submitBtn.prop('disabled', false).html('🔍 Cerca Agenti');
        $container.html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Errore nella richiesta. Verifica la connessione e riprova.</div>');
      }
    });
  });
  
})(jQuery);