<?php
// File: inc/back-nuovo-agente.php

// Nasconde i campi Sito Web e Lingua e deseleziona invio notifiche
add_action('admin_head-user-new.php', function () {
    echo '<style>#url, .user-language-wrap { display: none !important; }</style>';
});

add_action('admin_footer-user-new.php', function () {
    echo '<script>document.getElementById("send_user_notification").checked = false;</script>';
});

// Imposta ruolo "agente" come default per nuovi utenti creati da admin
add_filter('pre_option_default_role', function () {
    return 'agente';
});

// Dopo la creazione utente, reindirizza alla schermata di modifica utente
add_action('user_register', function ($user_id) {
    if (is_admin() && current_user_can('edit_users')) {
        wp_safe_redirect(admin_url("user-edit.php?user_id=$user_id"));
        exit;
    }
});
