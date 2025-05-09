<?php
/**
 * File: area-agenti-frontend.php
 * Versione tema: toro-ag-template V0.9.5
 */

 require_once __DIR__ . '/helpers/area-agenti-utils.php';

// CREAZIONE RUOLO "AGENTE"
add_action('init', function () {
    add_role('agente', 'Agente', [
        'read' => true,
        'edit_posts' => false,
        'delete_posts' => false,
    ]);
});

// Blocco totale accesso admin per tutti gli utenti NON amministratori
add_action('init', function () {
    $user = wp_get_current_user();

    $allowed_roles = ['administrator'];

    if (!array_intersect($allowed_roles, $user->roles)) {
        show_admin_bar(false);

        if (is_admin() && !defined('DOING_AJAX')) {
            wp_redirect(site_url('/area-agenti/risorse/'));
            exit;
        }
    }
});

// Shortcode: pagina unica area agenti con login, accesso, cambio password e dati
add_shortcode('area_agenti_unificato', function () {
    ob_start();

    if (!is_user_logged_in()) {
        // echo '<p>DEBUG: non sei loggato</p>';
        render_view('area-login');
        return ob_get_clean();
    }

    $user = wp_get_current_user();
    $user_id = $user->ID;

    if (!in_array('agente', (array) $user->roles) && !in_array('administrator', (array) $user->roles)) {
        render_view('area-accesso-negato');
        return ob_get_clean();
    }

    if (!is_agente_attivo($user_id)) {
        $dati = get_dati_agente($user_id);
        // var_dump($dati); exit;
        render_view('area-disattivato', array_merge(['user' => $user], $dati));
        return ob_get_clean();
    }
    
    $dati = get_dati_agente($user_id);
    render_view('area-dati-agente', array_merge(['user' => $user], $dati));
    return ob_get_clean();    
});
