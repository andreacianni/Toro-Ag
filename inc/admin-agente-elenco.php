<?php
/**
 * Codice per aggiungere toggle Attivo/Disattivo per utenti con ruolo "agente"
 * con colonna ordinabile nella pagina utenti (back-alluser.php)
 */

// 0. Aggiungi stile inline per evidenziare il link
add_action('admin_head-users.php', function () {
    echo '<style id="toggle-stato-agente">
        .column-agente_attivo a {
            padding: 3px 8px;
            border-radius: 4px;
            /* font-weight: bold; */
            display: inline-block;
            transition: background 0.2s, color 0.2s;
        }
        .column-agente_attivo a:hover {
            background: #0073aa;
            color: #fff;
            text-decoration: none;
        }
    </style>';
});

// 1. Aggiungi la colonna "Attivo"
add_filter('manage_users_columns', 'aggiungi_colonna_agente_attivo_toggle');
function aggiungi_colonna_agente_attivo_toggle($columns) {
    $columns['agente_attivo'] = 'Attivo';
    return $columns;
}

// 1b. Rendi la colonna ordinabile
add_filter('manage_users_sortable_columns', 'colonna_agente_attivo_ordinabile');
function colonna_agente_attivo_ordinabile($columns) {
    $columns['agente_attivo'] = 'agente_attivo';
    return $columns;
}

// 2. Popola la colonna solo per gli utenti "agente"
add_action('manage_users_custom_column', 'popola_colonna_agente_attivo_toggle', 10, 3);
function popola_colonna_agente_attivo_toggle($value, $column_name, $user_id) {
    if ($column_name === 'agente_attivo') {
        $user = get_userdata($user_id);
        if (!in_array('agente', $user->roles)) {
            return ''; // Colonna vuota se non è un agente
        }

        $pods = pods('user', $user_id);
        $attivo = $pods->field('agente_attivo');
        $nonce = wp_create_nonce('toggle_attivo_' . $user_id);
        $url = add_query_arg([
            'toggle_attivo' => $user_id,
            '_wpnonce' => $nonce
        ], admin_url('users.php'));

        $tooltip = 'Clicca per ' . ($attivo ? 'disattivare' : 'attivare') . ' questo agente';

        if ($attivo) {
            return '<a href="' . esc_url($url) . '" title="' . esc_attr($tooltip) . '">✅ Attivo</a>';
        } else {
            return '<a href="' . esc_url($url) . '" title="' . esc_attr($tooltip) . '">❌ Disattivo</a>';
        }
    }
    return $value;
}

// 3. Gestisci il toggle
add_action('admin_init', 'gestisci_toggle_agente_attivo');
function gestisci_toggle_agente_attivo() {
    if (
        isset($_GET['toggle_attivo']) &&
        current_user_can('edit_users') &&
        isset($_GET['_wpnonce']) &&
        wp_verify_nonce($_GET['_wpnonce'], 'toggle_attivo_' . $_GET['toggle_attivo'])
    ) {
        $user_id = intval($_GET['toggle_attivo']);
        $pods = pods('user', $user_id);
        $attuale = $pods->field('agente_attivo');
        $pods->save('agente_attivo', !$attuale);

        wp_redirect(remove_query_arg(['toggle_attivo', '_wpnonce']));
        exit;
    }
}

// 4. Modifica la query degli utenti solo per ordinamento
add_filter('pre_get_users', 'filtra_utenti_per_agente_attivo');
function filtra_utenti_per_agente_attivo($query) {
    global $pagenow;

    if ($pagenow === 'users.php' && is_admin() && $query->is_main_query()) {
        // Ordinamento
        if (isset($_GET['orderby']) && $_GET['orderby'] === 'agente_attivo') {
            $query->set('meta_key', 'agente_attivo');
            $query->set('orderby', 'meta_value');
        }
    }
}
