<?php
require_once get_stylesheet_directory() . '/inc/helpers/area-agenti-utils.php';
// inc/shortcodes/ricerca-agenti.php

// 1. Registra lo shortcode [ricerca_agenti]
add_shortcode('ricerca_agenti', function($atts) {
    // Enqueue script (solo qui, se lo shortcode è presente)
    wp_enqueue_script(
        'ricerca-agenti-js',
        get_stylesheet_directory_uri() . '/assets/js/ricerca-agenti.js',
        ['jquery'],
        filemtime(get_stylesheet_directory() . '/assets/js/ricerca-agenti.js'),
        true
    );
    wp_localize_script('ricerca-agenti-js', 'ricercaAgenti', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('ricerca_agenti_nonce'),
    ]);
    
    // Preleva tutti i valori distinti di "territori" da usermeta
    global $wpdb;
    // prendi tutti i meta_value di “territori”
    $raw = $wpdb->get_col(
        "SELECT DISTINCT meta_value
       FROM {$wpdb->usermeta}
       WHERE meta_key = 'territori'"
    );
    // rimuovi eventuali stringhe vuote
    $territori = array_filter( $raw, function($t){
        return '' !== trim( $t );
    } );
    
    // Costruisci il form e il contenitore risultati
    ob_start(); ?>
    <form id="ricerca-agenti-form" class="ricerca-agenti-form">
    <select id="territorio-select" name="territorio" required>
    <option value="" disabled selected>— Seleziona territorio —</option>
    <?php foreach( $territori as $t ): ?>
        <option value="<?php echo esc_attr($t) ?>">
        <?php echo esc_html($t) ?>
        </option>
        <?php endforeach; ?>
        </select>
        <button type="submit">Cerca</button>
        </form>
        
        <div id="risultati-agenti" class="row mt-4"></div>
        <?php
        return ob_get_clean();
    });
    
    // 2. Handler AJAX
    function ajax_ricerca_agenti() {
        check_ajax_referer('ricerca_agenti_nonce', 'nonce');
        
        $territorio = isset($_POST['territorio'])
        ? sanitize_text_field($_POST['territorio'])
        : '';
        
        // Se nessun filtro, restituisci array vuoto
        if (empty($territorio)) {
            wp_send_json_success([]);
        }
        
        // Query utenti con ruolo "agente" e meta attivo = "Sì" + territorio match
        $args = [
            // 'role'       => 'agente',
            'meta_query' => [
                [
                    'key'     => 'agente_attivo',
                    'value'   => '1',
                    'compare' => '=',
                ],
                [
                    'key'     => 'territori',
                    'value'   => $territorio,
                    'compare' => 'LIKE',   // Pods multi-pick stoccato come meta multipli
                ],
            ],
            'orderby'    => 'display_name',
            'order'      => 'ASC',
            // 'fields'     => ['ID'],
        ];
        $q = new WP_User_Query($args);

        $users = $q->get_results();
        
        $agents = [];
        /* vecchio ciclo
        foreach($users as $user) {
            $d = get_dati_agente($user->ID);
            
            $agents[] = [
                'ID'           => $user->ID,
                'display_name' => $user->display_name,
                'user_email'   => $user->user_email,
                'cellulare'    => $d['cellulare'],
                'telefono'     => $d['telefono'],
                'indirizzo'    => $d['indirizzo'],
            ];
        }
            */
            foreach ( $users as $user ) {
                // prendi i dati base
                $d = get_dati_agente( $user->ID );
            
                // crea un Pods per estrarre i campi indirizzo
                $pod = pods( 'user', $user->ID );
            
                // genera il link Google Maps
                $maps_link = get_agente_maps_link( $pod );
            
                // aggiungi anche maps_link nell'array di output
                $agents[] = [
                    'ID'           => $user->ID,
                    'display_name' => $user->display_name,
                    'user_email'   => $user->user_email,
                    'cellulare'    => $d['cellulare'],
                    'telefono'     => $d['telefono'],
                    'indirizzo'    => $d['indirizzo'],
                    'maps_link'    => $maps_link,
                ];
            }
        
        wp_send_json_success($agents);
    }
    add_action('wp_ajax_ricerca_agenti',    'ajax_ricerca_agenti');
    add_action('wp_ajax_nopriv_ricerca_agenti', 'ajax_ricerca_agenti');
    