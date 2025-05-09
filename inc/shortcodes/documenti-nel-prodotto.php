<?php
/**
 * Shortcode: product_docs
 * Versione: toro-ag-template V0.9.5
 *
 * Questo shortcode mostra i documenti associati a un prodotto.
 * 
 * Uso:
 * [product_docs]
 *
 * @return string HTML dei documenti del prodotto
 */

// 1. Registra lo shortcode
add_shortcode( 'product_docs', function( $atts ) {
    // ID del post corrente
    $post_id = get_the_ID();
    if ( ! $post_id ) {
        return '';
    }

    // Carica il Pod “prodotto”
    $pods = pods( 'prodotto', $post_id );
    if ( ! $pods->exists() ) {
        return '';
    }

    $output = '';

    // ————————————————————————————
    // 1) Brochure singola (file PDF)
    // ————————————————————————————
    $bro = $pods->field( 'pod_brochure' ); 
    // esiste e contiene almeno l’ID del file?
    if ( is_array( $bro ) && ! empty( $bro['ID'] ) ) {
        // URL
        $src   = $pods->field( 'pod_brochure._src' );
        // Titolo del file (nome assegnato in WP, non _img)
        $title = $pods->field( 'pod_brochure.post_title' );
        $output .= '<div class="product-brochure">';
        $output .= '<h4>Brochure:</h4>';
        $output .= '<ul>';
        $output .= sprintf(
            '<li><a href="%s" target="_blank">%s</a></li>',
            esc_url( $src ),
            esc_html( $title )
        );
        $output .= '</ul></div>';
    }

    // ————————————————————————————
    // 2) Documentazione multipla (file PDF)
    // ————————————————————————————
    $docs = $pods->field( 'pod_documentazione' );
    if ( is_array( $docs ) && count( $docs ) ) {
        $output .= '<div class="product-docs">';
        $output .= '<h4>Documentazione:</h4>';
        $output .= '<ul>';
        foreach ( $docs as $doc ) {
            // URL del singolo file
            $doc_src   = ! empty( $doc['_src'] ) ? $doc['_src'] : '';
            // Titolo: usa post_title, altrimenti fallback al nome file
            $doc_title = ! empty( $doc['post_title'] )
                       ? $doc['post_title']
                       : basename( $doc_src );
            $output   .= sprintf(
                '<li><a href="%s" target="_blank">%s</a></li>',
                esc_url( $doc_src ),
                esc_html( $doc_title )
            );
        }
        $output .= '</ul></div>';
    }

    // ————————————————————————————
    // 3) Tassonomia “coltura”
    // ————————————————————————————
    // Pods potrebbe restituirti array di ID o WP_Term
    $terms = $pods->field( 'coltura' );
    // fallback: prendi sempre i WP_Term col nome “coltura”
    if ( ! is_array( $terms ) || ( isset( $terms[0] ) && ! is_object( $terms[0] ) ) ) {
        $terms = wp_get_post_terms( $post_id, 'coltura' );
    }
    if ( is_array( $terms ) && count( $terms ) ) {
        $output .= '<div class="product-colture">';
        $output .= '<h4>Culture:</h4>';
        $output .= '<ul>';
        foreach ( $terms as $term ) {
            $term_obj  = is_object( $term ) ? $term : get_term( $term, 'coltura' );
            if ( ! is_wp_error( $term_obj ) ) {
                $output .= sprintf(
                    '<li><a href="%s">%s</a></li>',
                    esc_url( get_term_link( $term_obj ) ),
                    esc_html( $term_obj->name )
                );
            }
        }
        $output .= '</ul></div>';
    }

    return $output;
} );
