<?php
/**
 * File: area-agenti-utils.php
 * Versione tema: toro-ag-template V0.9.5
 */

function is_agente_attivo($user_id) {
    $attivo = get_user_meta($user_id, 'agente_attivo', true);
    return !empty($attivo) && $attivo !== '0' && $attivo !== 'false' && $attivo !== false;
}

function get_dati_agenteOLD($user_id) {
    // debug
    $territori_raw = get_user_meta($user_id, 'territori', true);

    // Recupero dei singoli campi per l'indirizzo
    $via = get_user_meta($user_id, 'via', true);
    $numero = get_user_meta($user_id, 'numero_civico', true);
    $cap = get_user_meta($user_id, 'cap', true);
    $citta = get_user_meta($user_id, 'citta', true);
    $provincia = get_user_meta($user_id, 'provincia', true);

    // Composizione dell'indirizzo, evitando spazi extra
    $parti_indirizzo = array_filter([
        trim($via . ' ' . $numero),
        trim($cap . ' ' . $citta),
        $provincia
    ]);
    $indirizzo = implode(', ', $parti_indirizzo);

    // Recupero territori
    $territori_raw = get_user_meta($user_id, 'territori', true);
    if (!is_array($territori_raw)) {
        $try_unserialize = maybe_unserialize($territori_raw);
        if (is_array($try_unserialize)) {
            $territori = $try_unserialize;
        } else {
            $territori = array_map('trim', explode(',', $territori_raw));
        }
    } else {
        $territori = $territori_raw;
    }

    return [
        'provincia' => $provincia,
        'territori' => $territori,
        'indirizzo' => $indirizzo,
        'telefono' => get_user_meta($user_id, 'telefono_fisso', true),
        'cellulare' => get_user_meta($user_id, 'cellulare', true),
        'user' => get_userdata($user_id),
    ];
}

function get_dati_agente($user_id) {
    
    // Recupero dei singoli campi per l'indirizzo
    $via = get_user_meta($user_id, 'via', true);
    $numero = get_user_meta($user_id, 'numero_civico', true);
    $cap = get_user_meta($user_id, 'cap', true);
    $citta = get_user_meta($user_id, 'citta', true);
    $provincia = get_user_meta($user_id, 'provincia', true);

    // Composizione dell'indirizzo, evitando spazi extra
    $parti_indirizzo = array_filter([
        trim($via . ' ' . $numero),
        trim($cap . ' ' . $citta),
        $provincia
    ]);
    $indirizzo = implode(', ', $parti_indirizzo);

    // Costruzione stringa territori
    $territori_array = get_user_meta($user_id, 'territori', false);
    $territori = implode(', ', array_map('trim', $territori_array));

    return [
        'provincia' => $provincia,
        'territori' => $territori,
        'indirizzo' => $indirizzo,
        'telefono'  => get_user_meta($user_id, 'telefono_fisso', true),
        'cellulare' => get_user_meta($user_id, 'cellulare', true),
        'user'      => get_userdata($user_id),
    ];
}


function render_view($view, $data = []) {
    extract($data);
    $view_file = __DIR__ . '/../views/' . $view . '.php';

    echo '<!-- DEBUG view: ' . esc_html($view_file) . ' -->';

    if (file_exists($view_file)) {
        include $view_file;
    } else {
        echo '<div style="color: red;"><strong>Errore:</strong> vista "' . esc_html($view) . '" non trovata.<br>';
        echo 'Path risolto: <code>' . $view_file . '</code></div>';
    }
}

if ( ! function_exists( 'get_agente_maps_link' ) ) {
    /**
     * Restituisce il link Google Maps per un agente Pods
     *
     * @param Pods $pod Oggetto Pods dell'agente
     * @return string URL di Google Maps
     */
    function get_agente_maps_link( $pod ) {
        // Prelevo i singoli campi
        $via           = $pod->field( 'via' );
        $numero        = $pod->field( 'numero_civico' );
        $cap           = $pod->field( 'cap' );
        $citta         = $pod->field( 'citta' );
        $provincia_raw = $pod->field( 'provincia' );

        // Estraggo il codice provincia fra parentesi, se presente
        if ( preg_match( '/\(([^)]+)\)/', $provincia_raw, $m ) ) {
            $provincia = $m[1];
        } else {
            $provincia = $provincia_raw;
        }

        // Composizione dell’indirizzo
        $indirizzo = trim( "{$via} {$numero}, {$cap} {$citta} {$provincia}" );

        // Encoding e costruzione URL
        $encoded  = rawurlencode( $indirizzo );
        $maps_url = "https://www.google.com/maps/search/?api=1&query={$encoded}";

        return $maps_url;
    }
}