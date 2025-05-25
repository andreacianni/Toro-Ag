<?php
/**
 * Shortcode: [scheda_prodotto]
 * Visualizza i link alle Schede Prodotto collegate al prodotto,
 * con fallback WPML robusto e debug HTML.
 */
function shortcode_scheda_prodotto( $atts ) {
    global $post;

    // Versioning per debug cache
    $version = filemtime( __FILE__ );

    // Lingue correnti e default WPML
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);
    // Debug iniziale con versione
    $output = "<!-- Debug [scheda_prodotto] v{$version}: current_lang={$current_lang}, default_lang={$default_lang} -->";

    if ( ! function_exists('pods') ) {
        return $output . "<!-- DEBUG v{$version}: Pods non disponibile -->";
    }

    // Ottieni relazione pick dal prodotto
    $pods_prod = pods( get_post_type( $post ), $post->ID );
    $relations = $pods_prod->field( 'scheda_prodotto' );
    $count_raw = is_array( $relations ) ? count( $relations ) : 0;
    $output .= "<!-- Debug [scheda_prodotto] v{$version}: relations raw count={$count_raw} -->";

    if ( empty( $relations ) || ! is_array( $relations ) ) {
        return $output . '<p>Nessuna scheda collegata.</p>';
    }

    // Prova a ottenere traduzioni nella lingua corrente
    $translated_ids = array();
    foreach ( $relations as $item ) {
        $orig_id = intval( $item['ID'] );
        $trans_id = apply_filters( 'wpml_object_id', $orig_id, 'scheda_prodotto', false, $current_lang );
        $output .= "<!-- Debug [scheda_prodotto] v{$version}: orig_id={$orig_id}, trans_id={$trans_id} -->";
        if ( $trans_id ) {
            $translated_ids[] = $trans_id;
        }
    }

    // Fallback alla lingua default se non trovate schede
    if ( empty( $translated_ids ) && $current_lang !== $default_lang ) {
        $output .= "<!-- Debug [scheda_prodotto] v{$version}: fallback to default_lang={$default_lang} -->";
        foreach ( $relations as $item ) {
            $orig_id = intval( $item['ID'] );
            $trans_id = apply_filters( 'wpml_object_id', $orig_id, 'scheda_prodotto', false, $default_lang );
            $output .= "<!-- Debug [scheda_prodotto] v{$version}: fallback orig_id={$orig_id}, trans_id={$trans_id} -->";
            if ( $trans_id ) {
                $translated_ids[] = $trans_id;
            }
        }
    }

    $output .= "<!-- Debug [scheda_prodotto] v{$version}: final translated count=" . count( $translated_ids ) . " -->";

    if ( empty( $translated_ids ) ) {
        return $output . '<p>Nessuna scheda nella lingua selezionata.</p>';
    }

    // Costruisci output
    foreach ( $translated_ids as $scheda_id ) {
        $post_obj = get_post( $scheda_id );
        $url   = get_permalink( $post_obj );
        $title = get_the_title( $post_obj );
        $output .= "<!-- Debug [scheda_prodotto] v{$version}: schema output ID={$scheda_id} -->";

        // Bandierina lingua corrente
        $flag_html = function_exists( 'toroag_get_flag_html' ) ? toroag_get_flag_html( $current_lang ) : '';

        $output .= '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . ' ' . $flag_html . '</a><br />';
    }

    return $output;
}
add_shortcode( 'scheda_prodotto', 'shortcode_scheda_prodotto' );


/**
 * Shortcode: [documento_prodotto]
 * Visualizza i link ai Documenti Prodotto collegati al prodotto,
 * con fallback WPML robusto e debug HTML.
 */
function shortcode_documento_prodotto( $atts ) {
    global $post;

    // Versioning per debug cache
    $version = filemtime( __FILE__ );

    // Lingue correnti e default WPML
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);
    $output = "<!-- Debug [documento_prodotto] v{$version}: current_lang={$current_lang}, default_lang={$default_lang} -->";

    if ( ! function_exists('pods') ) {
        return $output . "<!-- DEBUG v{$version}: Pods non disponibile -->";
    }

    // Ottieni relazione pick dal prodotto
    $pods_prod = pods( get_post_type( $post ), $post->ID );
    $relations = $pods_prod->field( 'documenti_prodotto' );
    $count_raw = is_array( $relations ) ? count( $relations ) : 0;
    $output .= "<!-- Debug [documento_prodotto] v{$version}: relations raw count={$count_raw} -->";

    if ( empty( $relations ) || ! is_array( $relations ) ) {
        return $output . '<p>Nessun documento collegato.</p>';
    }

    // Prova a ottenere traduzioni nella lingua corrente
    $translated_ids = array();
    foreach ( $relations as $item ) {
        $orig_id = intval( $item['ID'] );
        $trans_id = apply_filters( 'wpml_object_id', $orig_id, 'documenti_prodotto', false, $current_lang );
        $output .= "<!-- Debug [documento_prodotto] v{$version}: orig_id={$orig_id}, trans_id={$trans_id} -->";
        if ( $trans_id ) {
            $translated_ids[] = $trans_id;
        }
    }

    // Fallback alla lingua default se non trovati documenti
    if ( empty( $translated_ids ) && $current_lang !== $default_lang ) {
        $output .= "<!-- Debug [documento_prodotto] v{$version}: fallback to default_lang={$default_lang} -->";
        foreach ( $relations as $item ) {
            $orig_id = intval( $item['ID'] );
            $trans_id = apply_filters( 'wpml_object_id', $orig_id, 'documenti_prodotto', false, $default_lang );
            $output .= "<!-- Debug [documento_prodotto] v{$version}: fallback orig_id={$orig_id}, trans_id={$trans_id} -->";
            if ( $trans_id ) {
                $translated_ids[] = $trans_id;
            }
        }
    }

    $output .= "<!-- Debug [documento_prodotto] v{$version}: final translated count=" . count( $translated_ids ) . " -->";

    if ( empty( $translated_ids ) ) {
        return $output . '<p>Nessun documento nella lingua selezionata.</p>';
    }

    // Costruisci output
    foreach ( $translated_ids as $doc_id ) {
        $post_obj = get_post( $doc_id );
        $url   = get_permalink( $post_obj );
        $title = get_the_title( $post_obj );
        $output .= "<!-- Debug [documento_prodotto] v{$version}: documento output ID={$doc_id} -->";

        // Bandierina lingua corrente
        $flag_html = function_exists( 'toroag_get_flag_html' ) ? toroag_get_flag_html( $current_lang ) : '';

        $output .= '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . ' ' . $flag_html . '</a><br />';
    }

    return $output;
}
add_shortcode( 'documento_prodotto', 'shortcode_documento_prodotto' );
