<?php
/**
 * Shortcode [doc_plus] – output in Bootstrap card
 */
function doc_plus_card_shortcode() {
    // Lingua corrente e default WPML
    $lang        = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'n.d.';
    $default_lang = apply_filters( 'wpml_default_language', null );

    // Carico la pagina corrente
    $page_id  = get_the_ID();
    $page_pod = pods( 'page', $page_id );
    if ( ! $page_pod->exists() ) {
        return '<div class="alert alert-warning">doc_plus: pagina non trovata (ID=' . esc_html($page_id) . ')</div>';
    }

    // Leggo il field di relazione
    $related_docs = (array) $page_pod->field( 'doc_plus_inpage' );

    // Fallback WPML: se vuoto e non siamo nella lingua di default
    if ( empty( $related_docs ) && $lang !== $default_lang ) {
        $orig_id      = apply_filters( 'wpml_object_id', $page_id, 'page', false, $default_lang );
        $orig_pod     = pods( 'page', $orig_id );
        $related_docs = (array) $orig_pod->field( 'doc_plus_inpage' );
    }

    if ( empty( $related_docs ) ) {
        return '<div class="alert alert-info">doc_plus: nessun documento collegato a questa pagina.</div>';
    }

    // Costruiamo le card
    $out = '';
    foreach ( $related_docs as $item ) {
        $doc_id = (int) $item['ID'];
        $pod    = pods( 'doc_plus', $doc_id );
        if ( ! $pod->exists() ) {
            continue;
        }

        // Titolo
        $title = get_the_title( $doc_id );

        // Cover
        $cover_id  = $pod->field( 'doc_plus_cover.ID' );
        $cover_url = $cover_id ? esc_url( wp_get_attachment_url( $cover_id ) ) : '';

        // Allegati
        $allegati = (array) $pod->field( 'doc_plus_allegati' );

        // Card start
        $out .= '<div class="card mb-4">';
        $out .=   '<div class="card-header">';
        $out .=     'doc_plus_debug: shortcode eseguito (lingua=' . esc_html($lang) . ')';
        $out .=   '</div>';
        $out .=   '<div class="card-body">';

        // Titolo
        $out .=     '<h5 class="card-title">' . esc_html( $title ) . '</h5>';

        // Cover
        if ( $cover_url ) {
            $out .= '<img src="' . $cover_url . '" alt="' . esc_attr( $title ) . '" class="card-img-top mb-3">';
        }

        // Allegati list
        if ( ! empty( $allegati ) ) {
            $out .= '<ul class="list-group list-group-flush">';
            foreach ( $allegati as $att ) {
                $pdf_id  = (int) $att['ID'];
                $pod_pdf = pods( 'documenti_prodotto', $pdf_id );
                if ( ! $pod_pdf->exists() ) {
                    continue;
                }

                $file_id   = $pod_pdf->field( 'documento-prodotto.ID' );
                $file_url  = $file_id ? esc_url( wp_get_attachment_url( $file_id ) ) : '';
                $pdf_title = get_the_title( $pdf_id );

                // Lingua aggiuntiva
                $lingue = $pod_pdf->field( 'lingua_aggiuntiva' );
                if ( ! empty( $lingue ) ) {
                    $term   = $lingue[0];
                    $flag   = esc_html( $term['name'] );
                } else {
                    $flag   = 'n.d.';
                }

                $out .= '<li class="list-group-item">';
                $out .=   '<strong>[' . esc_html($flag) . ']</strong> ';
                if ( $file_url ) {
                    $out .= '<a href="' . $file_url . '" target="_blank">' . esc_html( $pdf_title ) . '</a>';
                } else {
                    $out .= esc_html( $pdf_title );
                }
                $out .= '</li>';
            }
            $out .= '</ul>';
        }

        $out .=   '</div>'; // .card-body
        $out .= '</div>';   // .card
    }

    return $out;
}
add_shortcode( 'doc_plus', 'doc_plus_card_shortcode' );
