<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con fallback pulito
 */
function doc_plus_debug_shortcode() {
    // 1) Identifico lingua corrente e default
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '';
    $default_lang = apply_filters( 'wpml_default_language', null );

    // 2) ID pagina originale in lingua corrente
    $orig_page_id = get_the_ID();

    // 3) Se siamo in EN (o altra lingua) e non default, cerco il corrispondente in default
    if ( $current_lang && $current_lang !== $default_lang ) {
        $fallback_page_id = apply_filters( 'wpml_object_id', $orig_page_id, 'page', false, $default_lang );
    } else {
        $fallback_page_id = $orig_page_id;
    }

    // 4) Carico la pagina da cui prelevare i doc_plus
    $load_page_id = $fallback_page_id;
    $page_pod     = pods( 'page', $load_page_id );

    // 5) Bootstrap card
    echo '<div class="card mb-4">';
      echo '<div class="card-header">';
        echo esc_html( "doc_plus_debug: shortcode eseguito lingua={$current_lang} carico pagina={$load_page_id}" );
      echo '</div>';
      echo '<div class="card-body">';

        if ( ! $page_pod->exists() ) {
            echo '<small class="d-block">doc_plus_debug: pagina non trovata (ID=' . esc_html($load_page_id) . ')</small>';
            echo '</div></div>';
            return;
        }

        // 6) Recupero relazione
        $related = $page_pod->field( 'doc_plus_inpage' );
        if ( empty( $related ) ) {
            echo '<small class="d-block">doc_plus_debug: nessun doc_plus collegato a pagina ' . esc_html($load_page_id) . '</small>';
            echo '</div></div>';
            return;
        }

        // 7) Mostro quanti documenti
        echo '<small class="d-block">doc_plus_debug: trovati '
           . esc_html( count($related) ) . ' doc_plus per pagina ' . esc_html($load_page_id)
           . '</small>';

        // 8) Ciclo ogni doc_plus
        foreach ( $related as $item ) {
            $doc_id = (int) $item['ID'];
            $pod    = pods( 'doc_plus', $doc_id );

            // Titolo
            $title = get_the_title( $doc_id );
            echo '<small class="d-block">'
               . esc_html( "DOC ID={$doc_id} lingua={$current_lang} titolo=\"{$title}\"" )
               . '</small>';

            // Cover ID
            $cover_id = $pod->field( 'doc_plus_cover.ID' );
            echo '<small class="d-block">'
               . esc_html( "cover_id={$cover_id}" )
               . '</small>';

            // Allegati
            $allegati = $pod->field( 'doc_plus_allegati' );
            if ( empty( $allegati ) ) {
                echo '<small class="d-block">nessun allegato per doc_plus ' . esc_html($doc_id) . '</small>';
                continue;
            }
            foreach ( $allegati as $att ) {
                $pdf_id  = (int) $att['ID'];
                $pod_pdf = pods( 'documenti_prodotto', $pdf_id );
                $pdf_title = get_the_title( $pdf_id );

                // Lingua aggiuntiva
                $terms = $pod_pdf->field( 'lingua_aggiuntiva' );
                if ( ! empty($terms) ) {
                    $t = $terms[0];
                    $slug = $t['slug'];
                    $name = $t['name'];
                } else {
                    $slug = $name = 'n.d.';
                }

                echo '<small class="d-block">'
                   . esc_html( "ALLEGATO PDF_ID={$pdf_id} titolo_pdf=\"{$pdf_title}\" lingua_aggiuntiva={$slug}:{$name}" )
                   . '</small>';
            }
        }

      echo '</div>';   // .card-body
    echo '</div>';     // .card
}
add_shortcode( 'doc_plus', 'doc_plus_debug_shortcode' );
