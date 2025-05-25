<?php
/**
 * Shortcode: [scheda_prodotto]
 * Visualizza i link alle Schede Prodotto collegate tramite relazione Pods (type=pick), con badge e bandierina della lingua.
 */
function shortcode_scheda_prodotto( $atts ) {
    global $post;

    // Ottieni oggetto Pods del prodotto corrente
    $pods = pods( get_post_type( $post ), $post->ID );
    $schede = $pods->field( 'scheda_prodotto' );

    if ( empty( $schede ) || ! is_array( $schede ) ) {
        return '';
    }

    $output = '';
    foreach ( $schede as $item ) {
        $scheda_id   = intval( $item['ID'] );
        $scheda_post = get_post( $scheda_id );
        if ( ! $scheda_post ) {
            continue;
        }

        $url   = get_permalink( $scheda_post );
        $title = get_the_title( $scheda_post );

        // Recupera la tassonomia Lingue Aggiuntive
        $terms = get_the_terms( $scheda_id, 'lingue_aggiuntive' );
        $flag_html = '';
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $lang = reset( $terms );
            $slug = $lang->slug;
            // Usa la funzione helper per ottenere l'HTML della bandiera
            if ( function_exists( 'toroag_get_flag_html' ) ) {
                $flag_html = toroag_get_flag_html( $slug );
            }
        }

        // Costruisci badge
        $output .= '<a href="' . esc_url( $url ) . '" class="badge badge-info me-1">'
                 . esc_html( $title )
                 . $flag_html
                 . '</a>';
    }

    return $output;
}
add_shortcode( 'scheda_prodotto', 'shortcode_scheda_prodotto' );


/**
 * Shortcode: [documento_prodotto]
 * Visualizza i link ai Documenti Prodotto collegati tramite relazione Pods (type=pick), con badge e bandierina della lingua.
 */
function shortcode_documento_prodotto( $atts ) {
    global $post;

    $pods = pods( get_post_type( $post ), $post->ID );
    $docs = $pods->field( 'documenti_prodotto' );

    if ( empty( $docs ) || ! is_array( $docs ) ) {
        return '';
    }

    $output = '';
    foreach ( $docs as $item ) {
        $doc_id   = intval( $item['ID'] );
        $doc_post = get_post( $doc_id );
        if ( ! $doc_post ) {
            continue;
        }

        $url   = get_permalink( $doc_post );
        $title = get_the_title( $doc_post );

        // Recupera la tassonomia Lingue Aggiuntive
        $terms = get_the_terms( $doc_id, 'lingue_aggiuntive' );
        $flag_html = '';
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $lang = reset( $terms );
            $slug = $lang->slug;
            // Usa la funzione helper per ottenere l'HTML della bandiera
            if ( function_exists( 'toroag_get_flag_html' ) ) {
                $flag_html = toroag_get_flag_html( $slug );
            }
        }

        $output .= '<a href="' . esc_url( $url ) . '" class="badge badge-success me-1">'
                 . esc_html( $title )
                 . $flag_html
                 . '</a>';
    }

    return $output;
}
add_shortcode( 'documento_prodotto', 'shortcode_documento_prodotto' );
