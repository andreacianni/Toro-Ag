<?php
function toroag_hide_default_user_fields($user_contactmethods) {
    // Rimuovi campi inutili
    unset($user_contactmethods['aim']);
    unset($user_contactmethods['jabber']);
    unset($user_contactmethods['yim']);
    unset($user_contactmethods['url']);
    return $user_contactmethods;
}
add_filter('user_contactmethods', 'toroag_hide_default_user_fields');

function toroag_hide_wp_user_profile_fields() {
    // Aggiungi CSS per nascondere i campi nella pagina profilo
    if (!current_user_can('administrator')) return;
    
    echo '<style id="toroag-hide-user-profile-fields">
            /* Nascondi i campi del profilo utente */
            tr.user-description-wrap,
            tr.user-url-wrap,
            tr.user-rich-editing-wrap,
            tr.user-admin-color-wrap,
            tr.user-language-wrap,
            tr.show-admin-bar,
            tr.user-comment-shortcuts-wrap,       /* Scorciatoie da tastiera */
            tr.user-profile-picture,              /* Immagine profilo */
            .application-passwords,              /* Password applicazioni - blocco completo */
            #application-passwords-section       /* Password applicazioni - sezione compatibilità */
            {
                display: none !important;
            }
        </style>';
    
}
add_action('admin_head-user-edit.php', 'toroag_hide_wp_user_profile_fields');
add_action('admin_head-profile.php', 'toroag_hide_wp_user_profile_fields');


