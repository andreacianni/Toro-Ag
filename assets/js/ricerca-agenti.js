(function($){
  
  // Gestione cambio regione (selezione singola)
  $('#regione-select').on('change', function(e){
    var regioneSelezionata = $(this).val();
    var $provinciaSelect = $('#territorio-select');
    var $submitBtn = $('.btn-search');
    
    // Reset provincia select
    $provinciaSelect.empty().append('<option value="" disabled selected>— Seleziona la provincia —</option>');
    
    if (regioneSelezionata) {
      var tutteLeProvince = [];
      var provinceConAgenti = window.provinceConAgenti || [];
      
      // Se è selezionato "tutte"
      if (regioneSelezionata === 'tutte') {
        // Aggiungi tutte le province di tutte le regioni
        Object.values(window.regioniProvince).forEach(function(province) {
          tutteLeProvince = tutteLeProvince.concat(province);
        });
      } else {
        // Aggiungi solo le province della regione selezionata
        if (window.regioniProvince[regioneSelezionata]) {
          tutteLeProvince = window.regioniProvince[regioneSelezionata];
        }
      }
      
      // Rimuovi duplicati e filtra solo le province che hanno agenti
      var provinceUniche = [...new Set(tutteLeProvince)];
      var provinceDisponibili = provinceUniche.filter(function(provincia) {
        return provinceConAgenti.includes(provincia);
      });
      
      // Popola il dropdown delle province
      if (provinceDisponibili.length > 0) {
        // Aggiungi opzione "Tutte" solo se ci sono più province
        if (provinceDisponibili.length > 1) {
          $provinciaSelect.append('<option value="tutte">Tutte le province' + (regioneSelezionata === 'tutte' ? '' : ' di ' + regioneSelezionata) + '</option>');
        }
        
        provinceDisponibili.sort().forEach(function(provincia) {
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
  
  // Gestione cambio provincia (selezione singola)
  $('#territorio-select').on('change', function(e){
    var provinciaSelezionata = $(this).val();
    var $submitBtn = $('.btn-search');
    
    // Abilita/disabilita il pulsante in base alla selezione
    $submitBtn.prop('disabled', !provinciaSelezionata);
  });
  
  // Gestione submit form
  $('#ricerca-agenti-form').on('submit', function(e){
    e.preventDefault();
    var provinciaSelezionata = $('#territorio-select').val();
    var $container = $('#risultati-agenti');
    var $submitBtn = $('.btn-search');
    
    // Feedback visivo durante il caricamento
    $submitBtn.prop('disabled', true).html('🔄 Ricerca in corso...');
    $container.html('<div class="text-center p-4"><div class="spinner-border text-danger" role="status"><span class="visually-hidden">Caricamento...</span></div><p class="mt-2">Ricerca rivenditori in corso...</p></div>');
    
    // Se è selezionato "tutte", ottieni tutte le province disponibili del dropdown
    var territoriDaCercare = [];
    if (provinciaSelezionata === 'tutte') {
      // Prendi tutte le opzioni del select tranne "tutte"
      $('#territorio-select option').each(function() {
        var val = $(this).val();
        if (val && val !== 'tutte' && !$(this).prop('disabled')) {
          territoriDaCercare.push(val);
        }
      });
    } else {
      territoriDaCercare = [provinciaSelezionata];
    }
    
    // Fai una richiesta per ogni territorio e unisci i risultati
    var promiseArray = territoriDaCercare.map(function(territorio) {
      return $.ajax({
        url: ricercaAgenti.ajax_url,
        method: 'POST',
        data: {
          action: 'ricerca_agenti',
          nonce: ricercaAgenti.nonce,
          territorio: territorio
        }
      });
    });
    
    $.when.apply($, promiseArray).done(function() {
      // Ripristina il pulsante
      $submitBtn.prop('disabled', false).html('Cerca Rivenditori');
      
      var allAgents = [];
      var agentIds = new Set(); // Per evitare duplicati
      
      // Se c'è una sola richiesta, arguments non è un array
      var responses = territoriDaCercare.length === 1 ? [arguments] : Array.from(arguments);
      
      responses.forEach(function(response) {
        var res = response[0]; // La risposta è il primo elemento
        if (res.success && res.data) {
          res.data.forEach(function(agent) {
            if (!agentIds.has(agent.ID)) {
              agentIds.add(agent.ID);
              allAgents.push(agent);
            }
          });
        }
      });
      
      if (allAgents.length === 0) {
        var territoriText = provinciaSelezionata === 'tutte' ? 'le aree selezionate' : provinciaSelezionata;
        $container.html('<div class="alert alert-info"><i class="bi bi-info-circle"></i> Nessun agente attivo trovato per <strong>' + territoriText + '</strong>.</div>');
        return;
      }
      
      // Ordina gli agenti per nome
      allAgents.sort(function(a, b) {
        return a.display_name.localeCompare(b.display_name);
      });
      
      // Costruisci la tabella dei risultati
      var territoriText = provinciaSelezionata === 'tutte' ? 'tutte le aree selezionate' : provinciaSelezionata;
      var html = ''
      + '<div class="results-header mb-3">'
      +   '<h4><i class="bi bi-people-fill text-danger"></i> Rivenditori trovati in <strong>' + territoriText + '</strong> (' + allAgents.length + ')</h4>'
      + '</div>'
      + '<table id="risultato-ricerca-territorio" class="table align-middle table-striped table-hover shadow-sm">'
      +   '<thead class="table-light">'
      +     '<tr>'
      +       '<th><i class="bi bi-person-badge"></i> Denominazione</th>'
      +       '<th><i class="bi bi-telephone"></i> Contatti</th>'
      +       '<th><i class="bi bi-geo-alt"></i> Indirizzo</th>'
      +       '<th class="text-center"><i class="bi bi-map"></i> Mappa</th>'
      +     '</tr>'
      +   '</thead>'
      +   '<tbody>';
      
      allAgents.forEach(function(a){
        // Costruisco i contatti su più righe
        var contacts = '';
        if (a.cellulare) {
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
      
    }).fail(function(){
      // Ripristina il pulsante
      $submitBtn.prop('disabled', false).html('Cerca rivenditori');
      $container.html('<div class="alert alert-danger"><i class="bi bi-exclamation-triangle"></i> Errore nella richiesta. Verifica la connessione e riprova.</div>');
    });
  });
  
})(jQuery);