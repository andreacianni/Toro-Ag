<?php
// Registrazione shortcode
add_shortcode( 'coltura_docs', 'render_coltura_docs' );

function render_coltura_docs( $atts ) {
    // 1) Parametri: opzionale term_id
    $atts = shortcode_atts( [
        'term_id' => 0,
    ], $atts, 'coltura_docs' );

    // 2) Determina l'ID del termine 'coltura'
    if ( $atts['term_id'] ) {
        $term_id = intval( $atts['term_id'] );
    }
    elseif ( is_tax( 'coltura' ) ) {
        $term    = get_queried_object();
        $term_id = $term->term_id;
    }
    else {
        return ''; // non siamo in un contesto 'coltura' e non è passato term_id
    }

    // 3) Carica il Pod
    $pods = pods( 'coltura', $term_id );
    if ( ! $pods->exists() ) {
        return '';
    }

    // 4) Prendi i file dal campo pod_colture_brochure_allegata
    $raw = $pods->field( 'pod_colture_brochure_allegata' );  // single o multi :contentReference[oaicite:0]{index=0}&#8203;:contentReference[oaicite:1]{index=1}

    // Normalizza in array di ID
    $file_ids = [];
    if ( is_array( $raw ) ) {
        // se multi, array di file-array; se single, array con chiave 'ID'
        if ( isset( $raw['ID'] ) ) {
            $file_ids[] = intval( $raw['ID'] );
        } else {
            foreach ( $raw as $item ) {
                if ( is_array( $item ) && isset( $item['ID'] ) ) {
                    $file_ids[] = intval( $item['ID'] );
                } elseif ( is_numeric( $item ) ) {
                    $file_ids[] = intval( $item );
                }
            }
        }
    } elseif ( is_numeric( $raw ) ) {
        $file_ids[] = intval( $raw );
    }

    if ( empty( $file_ids ) ) {
        return ''; // niente PDF allegati
    }

    // 5) Costruisci l'output
    $out = '<ul class="coltura-brochures">';
    foreach ( $file_ids as $fid ) {
        $url   = wp_get_attachment_url( $fid );
        $title = get_post_field( 'post_title', $fid );
        if ( $url && $title ) {
            $out .= sprintf(
                '<li><a href="%s" target="_blank">%s</a></li>',
                esc_url( $url ),
                esc_html( $title )
            );
        }
    }
    $out .= '</ul>';

    return $out;
}
