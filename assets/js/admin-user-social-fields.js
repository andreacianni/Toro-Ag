/**
 * Nasconde i campi social nella pagina di editing utente
 * quando l'utente ha il checkbox "Modalità semplificata" attivato
 * 
 * TORO AG - Feature: Hide Social Fields for Users
 */

jQuery(document).ready(function($) {
    
    // Verifica che siamo nella pagina di editing utente
    if (!$('body').hasClass('user-edit-php') && !$('body').hasClass('user-new-php')) {
        return;
    }
    
    // Lista dei campi social da nascondere
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
    
    /**
     * Funzione per nascondere/mostrare i campi social
     * @param {boolean} hide - true per nascondere, false per mostrare
     */
    function toggleSocialFields(hide) {
        socialFieldsToHide.forEach(function(fieldClass) {
            const $field = $('.' + fieldClass);
            if ($field.length > 0) {
                $field.css('display', hide ? 'none' : '');
            }
        });
    }
    
    /**
     * Controlla il checkbox e nasconde/mostra i campi
     * Aspetta che il checkbox sia disponibile (anche dopo DOM manipulation)
     */
    function checkToggleAndToggleFields() {
        const $hideCheckbox = $('#hide_social_fields');
        
        // Se il checkbox non è ancora disponibile, riprova
        if ($hideCheckbox.length === 0) {
            setTimeout(checkToggleAndToggleFields, 100);
            return;
        }
        
        const shouldHide = $hideCheckbox.is(':checked');
        toggleSocialFields(shouldHide);
    }
    
    // Esegui il check iniziale con delay per il DOM manipulation
    setTimeout(function() {
        checkToggleAndToggleFields();
        
        // Monitora i cambiamenti del checkbox per comportamento dinamico
        $(document).on('change', '#hide_social_fields', function() {
            checkToggleAndToggleFields();
        });
    }, 200);
    
});