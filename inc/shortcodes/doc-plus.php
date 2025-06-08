<?php
/**
 * Shortcode [doc_plus] – debug visibile
 * Emette testo a schermo con tutte le info.
 */
function doc_plus_debug_shortcode() {
    echo "<div class='doc-plus-debug'>";
    echo "<p>doc_plus_debug: shortcode eseguito (lingua=" 
       . esc_html( defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'n.d.' )
       . ")</p>";

    $page_id  = get_the_ID();
    $page_pod = pods( 'page', $page_id );
    if ( ! $page_pod->exists() ) {
        echo "<p>doc_plus_debug: pagina non trovata (ID={$page_id})</p>";
        echo "</div>";
        return;
    }

    $related_docs = $page_pod->field( 'doc_plus_inpage' );

    // FALLOBACK WPML
    if ( empty( $related_docs ) && defined('ICL_LANGUAGE_CODE') ) {
        $default_lang = apply_filters( 'wpml_default_language', null );
        if ( ICL_LANGUAGE_CODE !== $default_lang ) {
            $orig_id      = apply_filters( 'wpml_object_id', $page_id, 'page', false, $default_lang );
            $orig_pod     = pods( 'page', $orig_id );
            $related_docs = $orig_pod->field( 'doc_plus_inpage' );
            echo "<p>doc_plus_debug: fallback lingua={$default_lang} page_id={$orig_id}, trovati "
               . esc_html( count( (array)$related_docs ) ) . " doc_plus</p>";
        }
    }

    if ( empty( $related_docs ) ) {
        echo "<p>doc_plus_debug: nessun doc_plus collegato a pagina {$page_id}</p>";
        echo "</div>";
        return;
    }

    echo "<p>doc_plus_debug: trovati " . esc_html( count( $related_docs ) )
       . " doc_plus per pagina {$page_id}</p>";

    foreach ( $related_docs as $item ) {
        $doc_id = (int) $item['ID'];
        $pod    = pods( 'doc_plus', $doc_id );

        $title = get_the_title( $doc_id );
        $lang  = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'n.d.';
        echo "<p>DOC ID={$doc_id} lingua={$lang} titolo=\""
           . esc_html( $title ) . "\"</p>";

        $cover_id  = $pod->field( 'doc_plus_cover.ID' );
        $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';
        echo "<p>Cover ID={$cover_id} URL="
           . esc_url( $cover_url ) . "</p>";

        $allegati = $pod->field( 'doc_plus_allegati' );
        if ( empty( $allegati ) ) {
            echo "<p>nessun allegato per doc_plus {$doc_id}</p>";
            continue;
        }

        foreach ( $allegati as $att ) {
            $pdf_id  = (int) $att['ID'];
            $pod_pdf = pods( 'documenti_prodotto', $pdf_id );

            $file_id  = $pod_pdf->field( 'documento-prodotto.ID' );
            $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
            $pdf_title= get_the_title( $pdf_id );
            $lingue   = $pod_pdf->field( 'lingua_aggiuntiva' );
            if ( ! empty( $lingue ) ) {
                $term = $lingue[0];
                $slug = $term['slug'];
                $name = $term['name'];
            } else {
                $slug = $name = 'n.d.';
            }

            echo "<p>ALLEGATO PDF_ID={$pdf_id} file_id={$file_id} URL="
               . esc_url( $file_url ) . " titolo=\""
               . esc_html( $pdf_title ) . "\" lingua_aggiuntiva="
               . esc_html( "{$slug}:{$name}" ) . "</p>";
        }
    }

    echo "</div>";
}
add_shortcode( 'doc_plus', 'doc_plus_debug_shortcode' );
