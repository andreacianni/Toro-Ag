/**
 * Nasconde i campi social nella pagina di editing utente
 * quando l'utente ha il ruolo "agente"
 * 
 * TORO AG - Feature: Hide Social Fields for Agents
 */

jQuery(document).ready(function($) {
    
    // Verifica che siamo nella pagina di editing utente
    if (!$('body').hasClass('users-php') || !$('#role').length) {
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
    
    /**
     * Funzione per nascondere/mostrare i campi social
     * @param {boolean} hide - true per nascondere, false per mostrare
     */
    function toggleSocialFields(hide) {
        socialFieldsToHide.forEach(function(fieldClass) {
            const $field = $('.' + fieldClass);
            if (hide) {
                $field.fadeOut(300);
            } else {
                $field.fadeIn(300);
            }
        });
    }
    
    /**
     * Controlla il ruolo corrente e nasconde/mostra i campi
     */
    function checkRoleAndToggleFields() {
        const selectedRole = $('#role').val();
        const isAgent = (selectedRole === 'agente');
        
        // Nasconde i campi se è un agente, li mostra altrimenti
        toggleSocialFields(isAgent);
        
        // Debug info (rimuovere in produzione)
        if (window.console) {
            console.log('Ruolo selezionato:', selectedRole, '| Agente:', isAgent);
        }
    }
    
    // Esegui il check iniziale al caricamento della pagina
    checkRoleAndToggleFields();
    
    // Monitora i cambiamenti del ruolo per comportamento dinamico
    $('#role').on('change', function() {
        checkRoleAndToggleFields();
    });
    
    // Fallback: monitora anche eventuali altri select di ruolo
    $('select[name="role"]').on('change', function() {
        checkRoleAndToggleFields();
    });
    
});