<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card
 * Emette tutte le info di debug in una card Bootstrap, testo in <small>.
 */
function doc_plus_debug_shortcode() {
    $page_id  = get_the_ID();
    $lang     = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'n.d.';

    // Apriamo la card
    echo '<div class="card mb-4">';
      // Header con primo messaggio
      echo '<div class="card-header">';
        echo esc_html( "doc_plus_debug: shortcode eseguito lingua={$lang}" );
      echo '</div>';

      // Body con tutti i dettagli
      echo '<div class="card-body">';
        // Carico il pod pagina
        $page_pod = pods( 'page', $page_id );
        if ( ! $page_pod->exists() ) {
            echo '<small class="d-block">doc_plus_debug: pagina non trovata (ID=' . esc_html( $page_id ) . ')</small>';
            echo '</div></div>';
            return;
        }

        // Leggo relazione corrente
        $related_docs = $page_pod->field( 'doc_plus_inpage' );

        // Fallback WPML
        if ( empty( $related_docs ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
            $default_lang = apply_filters( 'wpml_default_language', null );
            if ( ICL_LANGUAGE_CODE !== $default_lang ) {
                $orig_id      = apply_filters( 'wpml_object_id', $page_id, 'page', false, $default_lang );
                $orig_pod     = pods( 'page', $orig_id );
                $related_docs = $orig_pod->field( 'doc_plus_inpage' );
                echo '<small class="d-block">'
                   . esc_html( "doc_plus_debug: fallback lingua {$default_lang} page_id={$orig_id}, trovati " . count( (array)$related_docs ) . " doc_plus" )
                   . '</small>';
            }
        }

        if ( empty( $related_docs ) ) {
            echo '<small class="d-block">doc_plus_debug: nessun doc_plus collegato a pagina ' . esc_html( $page_id ) . '</small>';
            echo '</div></div>';
            return;
        }

        echo '<small class="d-block">'
           . esc_html( "doc_plus_debug: trovati " . count( $related_docs ) . " doc_plus per pagina {$page_id}" )
           . '</small>';

        // Ciclo i doc_plus e stampo i dettagli
        foreach ( $related_docs as $item ) {
            $doc_id = (int) $item['ID'];
            $pod    = pods( 'doc_plus', $doc_id );

            // Titolo e lingua
            $title = get_the_title( $doc_id );
            echo '<small class="d-block">'
               . esc_html( "DOC ID={$doc_id} lingua={$lang} titolo=\"{$title}\"" )
               . '</small>';

            // Cover (solo ID, non URL)
            $cover_id = $pod->field( 'doc_plus_cover.ID' );
            echo '<small class="d-block">'
               . esc_html( "cover_id={$cover_id}" )
               . '</small>';

            // Allegati
            $allegati = $pod->field( 'doc_plus_allegati' );
            if ( empty( $allegati ) ) {
                echo '<small class="d-block">nessun allegato per doc_plus ' . esc_html( $doc_id ) . '</small>';
                continue;
            }

            foreach ( $allegati as $att ) {
                $pdf_id  = (int) $att['ID'];
                $pod_pdf = pods( 'documenti_prodotto', $pdf_id );

                // Solo titolo PDF e lingua aggiuntiva
                $pdf_title = get_the_title( $pdf_id );
                $lingue    = $pod_pdf->field( 'lingua_aggiuntiva' );
                if ( ! empty( $lingue ) ) {
                    $term = $lingue[0];
                    $slug = $term['slug'];
                    $name = $term['name'];
                } else {
                    $slug = $name = 'n.d.';
                }

                echo '<small class="d-block">'
                   . esc_html( "ALLEGATO PDF_ID={$pdf_id} titolo_pdf=\"{$pdf_title}\" lingua_aggiuntiva={$slug}:{$name}" )
                   . '</small>';
            }
        }

      echo '</div>'; // .card-body
    echo '</div>';   // .card
}
add_shortcode( 'doc_plus', 'doc_plus_debug_shortcode' );
