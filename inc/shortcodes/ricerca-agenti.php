<?php
require_once get_stylesheet_directory() . '/inc/helpers/area-agenti-utils.php';
// inc/shortcodes/ricerca-agenti.php

// 1. Registra lo shortcode [ricerca_agenti]
add_shortcode('ricerca_agenti', function($atts) {
    // Gestione parametri shortcode
    $atts = shortcode_atts([
        'ordinamento' => 'A-Z'  // Valori possibili: 'A-Z' (alfabetico) o 'N-S' (Nord-Sud)
    ], $atts, 'ricerca_agenti');
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
    
    // Mappatura Regioni -> Province italiane (ordinata Nord-Sud)
    $regioni_province_nord_sud = [
        'Valle d\'Aosta' => [
            'Aosta (AO)'
        ],
        'Piemonte' => [
            'Alessandria (AL)', 'Asti (AT)', 'Biella (BI)', 'Cuneo (CN)', 'Novara (NO)', 'Torino (TO)', 'Verbano-Cusio-Ossola (VB)', 'Vercelli (VC)'
        ],
        'Lombardia' => [
            'Bergamo (BG)', 'Brescia (BS)', 'Como (CO)', 'Cremona (CR)', 'Lecco (LC)', 'Lodi (LO)', 'Mantova (MN)', 'Milano (MI)', 'Monza e Brianza (MB)', 'Pavia (PV)', 'Sondrio (SO)', 'Varese (VA)'
        ],
        'Liguria' => [
            'Genova (GE)', 'Imperia (IM)', 'La Spezia (SP)', 'Savona (SV)'
        ],
        'Trentino-Alto Adige' => [
            'Bolzano (BZ)', 'Trento (TN)'
        ],
        'Veneto' => [
            'Belluno (BL)', 'Padova (PD)', 'Rovigo (RO)', 'Treviso (TV)', 'Venezia (VE)', 'Verona (VR)', 'Vicenza (VI)'
        ],
        'Friuli-Venezia Giulia' => [
            'Gorizia (GO)', 'Pordenone (PN)', 'Trieste (TS)', 'Udine (UD)'
        ],
        'Emilia-Romagna' => [
            'Bologna (BO)', 'Ferrara (FE)', 'Forlì-Cesena (FC)', 'Modena (MO)', 'Parma (PR)', 'Piacenza (PC)', 'Ravenna (RA)', 'Reggio Emilia (RE)', 'Rimini (RN)'
        ],
        'Toscana' => [
            'Arezzo (AR)', 'Firenze (FI)', 'Grosseto (GR)', 'Livorno (LI)', 'Lucca (LU)', 'Massa-Carrara (MS)', 'Pisa (PI)', 'Pistoia (PT)', 'Prato (PO)', 'Siena (SI)'
        ],
        'Umbria' => [
            'Perugia (PG)', 'Terni (TR)'
        ],
        'Marche' => [
            'Ancona (AN)', 'Ascoli Piceno (AP)', 'Fermo (FM)', 'Macerata (MC)', 'Pesaro e Urbino (PU)'
        ],
        'Lazio' => [
            'Frosinone (FR)', 'Latina (LT)', 'Rieti (RI)', 'Roma (RM)', 'Viterbo (VT)'
        ],
        'Abruzzo' => [
            'Chieti (CH)', 'L\'Aquila (AQ)', 'Pescara (PE)', 'Teramo (TE)'
        ],
        'Molise' => [
            'Campobasso (CB)', 'Isernia (IS)'
        ],
        'Campania' => [
            'Avellino (AV)', 'Benevento (BN)', 'Caserta (CE)', 'Napoli (NA)', 'Salerno (SA)'
        ],
        'Puglia' => [
            'Bari (BA)', 'Barletta-Andria-Trani (BT)', 'Brindisi (BR)', 'Foggia (FG)', 'Lecce (LE)', 'Taranto (TA)'
        ],
        'Basilicata' => [
            'Matera (MT)', 'Potenza (PZ)'
        ],
        'Calabria' => [
            'Catanzaro (CZ)', 'Cosenza (CS)', 'Crotone (KR)', 'Reggio Calabria (RC)', 'Vibo Valentia (VV)'
        ],
        'Sicilia' => [
            'Agrigento (AG)', 'Caltanissetta (CL)', 'Catania (CT)', 'Enna (EN)', 'Messina (ME)', 'Palermo (PA)', 'Ragusa (RG)', 'Siracusa (SR)', 'Trapani (TP)'
        ],
        'Sardegna' => [
            'Cagliari (CA)', 'Nuoro (NU)', 'Oristano (OR)', 'Sassari (SS)', 'Sud Sardegna (SU)'
        ]
    ];
    
    // Mappatura alfabetica A-Z
    $regioni_province_alfabetiche = [
        'Abruzzo' => [
            'Chieti (CH)', 'L\'Aquila (AQ)', 'Pescara (PE)', 'Teramo (TE)'
        ],
        'Basilicata' => [
            'Matera (MT)', 'Potenza (PZ)'
        ],
        'Calabria' => [
            'Catanzaro (CZ)', 'Cosenza (CS)', 'Crotone (KR)', 'Reggio Calabria (RC)', 'Vibo Valentia (VV)'
        ],
        'Campania' => [
            'Avellino (AV)', 'Benevento (BN)', 'Caserta (CE)', 'Napoli (NA)', 'Salerno (SA)'
        ],
        'Emilia-Romagna' => [
            'Bologna (BO)', 'Ferrara (FE)', 'Forlì-Cesena (FC)', 'Modena (MO)', 'Parma (PR)', 'Piacenza (PC)', 'Ravenna (RA)', 'Reggio Emilia (RE)', 'Rimini (RN)'
        ],
        'Friuli-Venezia Giulia' => [
            'Gorizia (GO)', 'Pordenone (PN)', 'Trieste (TS)', 'Udine (UD)'
        ],
        'Lazio' => [
            'Frosinone (FR)', 'Latina (LT)', 'Rieti (RI)', 'Roma (RM)', 'Viterbo (VT)'
        ],
        'Liguria' => [
            'Genova (GE)', 'Imperia (IM)', 'La Spezia (SP)', 'Savona (SV)'
        ],
        'Lombardia' => [
            'Bergamo (BG)', 'Brescia (BS)', 'Como (CO)', 'Cremona (CR)', 'Lecco (LC)', 'Lodi (LO)', 'Mantova (MN)', 'Milano (MI)', 'Monza e Brianza (MB)', 'Pavia (PV)', 'Sondrio (SO)', 'Varese (VA)'
        ],
        'Marche' => [
            'Ancona (AN)', 'Ascoli Piceno (AP)', 'Fermo (FM)', 'Macerata (MC)', 'Pesaro e Urbino (PU)'
        ],
        'Molise' => [
            'Campobasso (CB)', 'Isernia (IS)'
        ],
        'Piemonte' => [
            'Alessandria (AL)', 'Asti (AT)', 'Biella (BI)', 'Cuneo (CN)', 'Novara (NO)', 'Torino (TO)', 'Verbano-Cusio-Ossola (VB)', 'Vercelli (VC)'
        ],
        'Puglia' => [
            'Bari (BA)', 'Barletta-Andria-Trani (BT)', 'Brindisi (BR)', 'Foggia (FG)', 'Lecce (LE)', 'Taranto (TA)'
        ],
        'Sardegna' => [
            'Cagliari (CA)', 'Nuoro (NU)', 'Oristano (OR)', 'Sassari (SS)', 'Sud Sardegna (SU)'
        ],
        'Sicilia' => [
            'Agrigento (AG)', 'Caltanissetta (CL)', 'Catania (CT)', 'Enna (EN)', 'Messina (ME)', 'Palermo (PA)', 'Ragusa (RG)', 'Siracusa (SR)', 'Trapani (TP)'
        ],
        'Toscana' => [
            'Arezzo (AR)', 'Firenze (FI)', 'Grosseto (GR)', 'Livorno (LI)', 'Lucca (LU)', 'Massa-Carrara (MS)', 'Pisa (PI)', 'Pistoia (PT)', 'Prato (PO)', 'Siena (SI)'
        ],
        'Trentino-Alto Adige' => [
            'Bolzano (BZ)', 'Trento (TN)'
        ],
        'Umbria' => [
            'Perugia (PG)', 'Terni (TR)'
        ],
        'Valle d\'Aosta' => [
            'Aosta (AO)'
        ],
        'Veneto' => [
            'Belluno (BL)', 'Padova (PD)', 'Rovigo (RO)', 'Treviso (TV)', 'Venezia (VE)', 'Verona (VR)', 'Vicenza (VI)'
        ]
    ];
    
    // Scegli l'ordinamento in base al parametro
    $regioni_province = ($atts['ordinamento'] === 'N-S') ? $regioni_province_nord_sud : $regioni_province_alfabetiche;
    
    // Preleva tutti i valori distinti di "territori" da usermeta per verificare quali province hanno agenti
    global $wpdb;
    $province_con_agenti = $wpdb->get_col(
        "SELECT DISTINCT meta_value
         FROM {$wpdb->usermeta}
         WHERE meta_key = 'territori' AND meta_value != ''"
    );
    $province_con_agenti = array_filter($province_con_agenti, function($t){
        return '' !== trim($t);
    });
    
    // Costruisci il form e il contenitore risultati
    ob_start(); ?>
    
    <style>
    .ricerca-agenti-form {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        margin-bottom: 2rem;
    }
    
    .form-row {
        display: flex;
        gap: 1rem;
        align-items: end;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }
    
    .form-group {
        flex: 1;
        min-width: 200px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #495057;
    }
    
    .form-control {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #dee2e6;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background-color: white;
    }
    
    .form-control:focus {
        outline: none;
        border-color: #dc3545;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }
    
    .form-control:disabled {
        background-color: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }
    
    .btn-search {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 6px rgba(220, 53, 69, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .btn-search:hover {
        background: linear-gradient(135deg, #c82333 0%, #a71e2a 100%);
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(220, 53, 69, 0.4);
    }
    
    .btn-search:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(220, 53, 69, 0.3);
    }
    
    .btn-search:disabled {
        background: #6c757d;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
        }
        
        .form-group {
            min-width: 100%;
        }
        
        .btn-search {
            width: 100%;
            margin-top: 1rem;
        }
    }
    </style>
    
    <form id="ricerca-agenti-form" class="ricerca-agenti-form">
        <div class="form-row">
            <div class="form-group">
                <label for="regione-select">Seleziona Regione</label>
                <select id="regione-select" name="regione" class="form-control" required>
                    <option value="" disabled selected>— Scegli la regione —</option>
                    <option value="tutte">Tutte le regioni</option>
                    <?php foreach (array_keys($regioni_province) as $regione): ?>
                        <option value="<?php echo esc_attr($regione) ?>">
                            <?php echo esc_html($regione) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="form-text text-muted">Seleziona una regione specifica o "Tutte" per visualizzare tutte le province</small>
            </div>
            
            <div class="form-group">
                <label for="territorio-select">Seleziona Provincia</label>
                <select id="territorio-select" name="territorio" class="form-control" required disabled>
                    <option value="" disabled selected>— Prima seleziona la regione —</option>
                </select>
                <small class="form-text text-muted">Le province si aggiorneranno in base alla regione selezionata</small>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-search" disabled>
                    🔍 Cerca Agenti
                </button>
            </div>
        </div>
    </form>
    
    <script type="text/javascript">
        // Mappatura regioni-province disponibile in JavaScript
        window.regioniProvince = <?php echo json_encode($regioni_province); ?>;
        window.provinceConAgenti = <?php echo json_encode($province_con_agenti); ?>;
    </script>
    
    <div id="risultati-agenti" class="row mt-4"></div>
    <?php
    return ob_get_clean();
});

// 2. Handler AJAX (rimane uguale)
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
    ];
    $q = new WP_User_Query($args);

    $users = $q->get_results();
    
    $agents = [];
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