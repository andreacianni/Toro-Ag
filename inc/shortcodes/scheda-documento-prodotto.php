<?php
/**
 * Shortcode: [scheda_prodotto]
 * Recupera schede prodotto associate tramite Pods, con supporto WPML e filtro per lingua_aggiuntive.
 */
function shortcode_scheda_prodotto( $atts ) {
    global $post;

    // Lingua corrente
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);

    // Ottieni relazione pick dal prodotto
    $pods_prod = pods( get_post_type( $post ), $post->ID );
    $relations = $pods_prod->field( 'scheda_prodotto' );

    // Se vuoto, fallback metadata IT
    if ( empty( $relations ) || ! is_array( $relations ) ) {
        $relations = get_post_meta( $post->ID, 'scheda_prodotto', false );
    }

    if ( empty( $relations ) ) {
        return '<p>Nessuna scheda collegata.</p>';
    }

    $output = '';
    foreach ( $relations as $item ) {
        // ID originale
        $orig_id = intval( is_array($item) ? $item['ID'] : $item );
        // Mappa alla lingua corrente
        $trans_id = apply_filters( 'wpml_object_id', $orig_id, 'scheda_prodotto', false, $current_lang );
        if ( ! $trans_id ) {
            continue;
        }

        // Filtro tassonomia lingua_aggiuntive
        $terms = get_the_terms( $trans_id, 'lingue_aggiuntive' );
        $show = false;
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $slugs = wp_list_pluck( $terms, 'slug' );
            if ( 'it' === $current_lang ) {
                $show = in_array( 'italiano', $slugs, true );
            } else {
                $show = ! in_array( 'italiano', $slugs, true );
            }
        } elseif ( 'it' === $current_lang ) {
            // se nessuna tassonomia, in IT mostriamo comunque
            $show = true;
        }

        if ( ! $show ) {
            continue;
        }

        $url   = get_permalink( $trans_id );
        $title = get_the_title( $trans_id );
        $flag  = function_exists('toroag_get_flag_html') ? toroag_get_flag_html( $current_lang ) : '';

        $output .= '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . ' ' . $flag . '</a><br />';
    }

    if ( empty( $output ) ) {
        return '<p>Nessuna scheda disponibile per lingua.</p>';
    }

    return $output;
}
add_shortcode( 'scheda_prodotto', 'shortcode_scheda_prodotto' );


/**
 * Shortcode: [documento_prodotto]
 * Recupera documenti prodotto associati tramite Pods, con supporto WPML e filtro per lingua_aggiuntive.
 */
function shortcode_documento_prodotto( $atts ) {
    global $post;

    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);

    $pods_prod = pods( get_post_type( $post ), $post->ID );
    $relations = $pods_prod->field( 'documenti_prodotto' );

    if ( empty( $relations ) || ! is_array( $relations ) ) {
        $relations = get_post_meta( $post->ID, 'documenti_prodotto', false );
    }

    if ( empty( $relations ) ) {
        return '<p>Nessun documento collegato.</p>';
    }

    $output = '';
    foreach ( $relations as $item ) {
        $orig_id = intval( is_array($item) ? $item['ID'] : $item );
        $trans_id = apply_filters( 'wpml_object_id', $orig_id, 'documenti_prodotto', false, $current_lang );
        if ( ! $trans_id ) {
            continue;
        }

        $terms = get_the_terms( $trans_id, 'lingue_aggiuntive' );
        $show = false;
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $slugs = wp_list_pluck( $terms, 'slug' );
            if ( 'it' === $current_lang ) {
                $show = in_array( 'italiano', $slugs, true );
            } else {
                $show = ! in_array( 'italiano', $slugs, true );
            }
        } elseif ( 'it' === $current_lang ) {
            $show = true;
        }

        if ( ! $show ) {
            continue;
        }

        $url   = get_permalink( $trans_id );
        $title = get_the_title( $trans_id );
        $flag  = function_exists('toroag_get_flag_html') ? toroag_get_flag_html( $current_lang ) : '';

        $output .= '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . ' ' . $flag . '</a><br />';
    }

    if ( empty( $output ) ) {
        return '<p>Nessun documento disponibile per lingua.</p>';
    }

    return $output;
}
add_shortcode( 'documento_prodotto', 'shortcode_documento_prodotto' );
