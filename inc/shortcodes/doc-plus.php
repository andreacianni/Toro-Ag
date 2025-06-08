<?php
/**
 * Shortcode [doc_plus] – debug HTML comments
 * Stampa solo commenti HTML con tutte le info (ID, titolo, cover, allegati, lingua).
 */
function doc_plus_debug_shortcode() {
    $page_id  = get_the_ID();
    $page_pod = pods( 'page', $page_id );
    if ( ! $page_pod->exists() ) {
        return '<!-- doc_plus_debug: pagina non trovata -->';
    }

    $related_docs = $page_pod->field( 'doc_plus_inpage' );
    if ( empty( $related_docs ) ) {
        return '<!-- doc_plus_debug: nessun doc_plus collegato a pagina ' . $page_id . ' -->';
    }

    $out = "\n<!-- doc_plus_debug: trovati " . count( $related_docs ) . " doc_plus per pagina {$page_id} -->\n";
    foreach ( $related_docs as $item ) {
        $doc_id  = (int) $item['ID'];
        $pod     = pods( 'doc_plus', $doc_id );

        // Titolo e lingua WPML
        $title = get_the_title( $doc_id );
        $lang  = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'n.d.';

        // Cover
        $cover_id  = $pod->field( 'doc_plus_cover.ID' );
        $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';

        $out .= "<!-- doc_plus_debug: DOC ID={$doc_id} lingua={$lang} titolo=\"{$title}\" cover_id={$cover_id} cover_url=\"{$cover_url}\" -->\n";

        // Allegati
        $allegati = $pod->field( 'doc_plus_allegati' );
        if ( empty( $allegati ) ) {
            $out .= "<!-- doc_plus_debug: nessun allegato per doc_plus {$doc_id} -->\n";
            continue;
        }

        foreach ( $allegati as $att ) {
            $pdf_id  = (int) $att['ID'];
            $pod_pdf = pods( 'documenti_prodotto', $pdf_id );

            // File PDF
            $file_id  = $pod_pdf->field( 'documento-prodotto.ID' );
            $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';

            // Titolo PDF e lingua aggiuntiva
            $pdf_title = get_the_title( $pdf_id );
            $lingue    = $pod_pdf->field( 'lingua_aggiuntiva' );
            if ( ! empty( $lingue ) ) {
                $term      = $lingue[0];
                $slug      = $term['slug'];
                $name      = $term['name'];
            } else {
                $slug = $name = 'n.d.';
            }

            $out .= "<!-- doc_plus_debug:   ALLEGATO PDF_ID={$pdf_id} file_id={$file_id} file_url=\"{$file_url}\" titolo_pdf=\"{$pdf_title}\" lingua_aggiuntiva={$slug}:{$name} -->\n";
        }
    }

    return $out;
}
add_shortcode( 'doc_plus', 'doc_plus_debug_shortcode' );
