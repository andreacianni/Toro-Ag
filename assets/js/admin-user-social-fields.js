/**
 * Nasconde i campi social nella pagina di editing utente
 * quando l'utente ha il ruolo "agente"
 * 
 * TORO AG - Feature: Hide Social Fields for Agents
 */

jQuery(document).ready(function($) {
    
    console.log('🔧 TORO AG: Script hide social fields caricato');
    console.log('🔧 Body classes:', $('body').attr('class'));
    console.log('🔧 Campo #role trovato:', $('#role').length > 0);
    
    // Verifica che siamo nella pagina di editing utente
    if (!$('body').hasClass('user-edit-php') && !$('body').hasClass('user-new-php')) {
        console.log('❌ Non siamo nella pagina user-edit-php o user-new-php');
        return;
    }
    
    if (!$('#role').length) {
        console.log('❌ Campo #role non trovato');
        // Proviamo altri selettori possibili
        console.log('🔧 Altri campi role trovati:', $('select[name="role"]').length);
        console.log('🔧 Tutti i select:', $('select').length);
        $('select').each(function(i, el) {
            console.log('Select ' + i + ':', $(el).attr('name'), $(el).attr('id'));
        });
        return;
    }
    
    // Lista dei campi social da nascondere per gli agenti
    const socialFieldsToHide = [
        'user-facebook-wrap',
        'user-instagram-wrap', 
        'user-linkedin-wrap',
        'user-myspace-wrap',
        'user-pinterest-wrap',
        'user-soundcloud-wrap',
        'user-tumblr-wrap',
        'user-wikipedia-wrap',
        'user-twitter-wrap',
        'user-youtube-wrap'
    ];
    
    console.log('🔧 Campi social da nascondere:', socialFieldsToHide);
    
    // Verifica che i campi esistano
    socialFieldsToHide.forEach(function(fieldClass) {
        const $field = $('.' + fieldClass);
        console.log('🔧 Campo .' + fieldClass + ' trovato:', $field.length > 0);
    });
    
    /**
     * Funzione per nascondere/mostrare i campi social
     * @param {boolean} hide - true per nascondere, false per mostrare
     */
    function toggleSocialFields(hide) {
        console.log('🔧 toggleSocialFields chiamata con hide:', hide);
        
        socialFieldsToHide.forEach(function(fieldClass) {
            const $field = $('.' + fieldClass);
            if ($field.length > 0) {
                if (hide) {
                    $field.css('display', 'none');
                    console.log('✅ Nascosto campo:', fieldClass);
                } else {
                    $field.css('display', '');
                    console.log('✅ Mostrato campo:', fieldClass);
                }
            }
        });
    }
    
    /**
     * Controlla il checkbox e nasconde/mostra i campi
     */
    function checkToggleAndToggleFields() {
        const $hideCheckbox = $('#hide_social_fields');
        const shouldHide = $hideCheckbox.is(':checked');
        
        console.log('🔧 Checkbox "Modalità semplificata" trovato:', $hideCheckbox.length > 0);
        console.log('🔧 Checkbox è checked:', shouldHide);
        
        // Nasconde i campi se checkbox è checked
        toggleSocialFields(shouldHide);
        
        console.log('🎯 RISULTATO: Modalità semplificata:', shouldHide, '| Campi nascosti:', shouldHide);
    }
    
    // Esegui il check iniziale al caricamento della pagina
    console.log('🚀 Eseguo check iniziale...');
    checkToggleAndToggleFields();
    
    // Monitora i cambiamenti del checkbox per comportamento dinamico
    $(document).on('change', '#hide_social_fields', function() {
        console.log('🔄 Checkbox cambiato, eseguo nuovo check...');
        checkToggleAndToggleFields();
    });
    
});