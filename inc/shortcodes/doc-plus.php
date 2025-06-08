<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con filtro ID opzionale
 * Recupera sempre tutti i doc_plus collegati dalla pagina corrente,
 * poi se 'ids' è passato filtra quali elementi mostrare.
 * Aggiunti debug visibili per tracciare il flusso tra i vari if/else.
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Parsing attributi
    $atts = shortcode_atts( array( 'ids' => '' ), $atts, 'doc_plus' );
    $filter_ids = array();
    if ( ! empty( $atts['ids'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids'] ) as $v ) {
            $i = intval( $v );
            if ( $i > 0 ) {
                $filter_ids[] = $i;
            }
        }
    }

    // 2) Avvio buffering
    ob_start();

    // 3) Lingua corrente e default
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 4) Recupero raw meta doc_plus_inpage direttamente dalla pagina corrente
    $page_id  = get_the_ID();
    echo '<small class="d-block text-center text-warning">doc_plus_debug: STEP relation raw meta from page_id=' . esc_html($page_id) . '</small>';
    $raw_list = get_post_meta( $page_id, 'doc_plus_inpage', false );
    $related = array();
    foreach ( $raw_list as $entry ) {
        if ( is_array( $entry ) && isset( $entry['ID'] ) ) {
            $related[] = array( 'ID' => intval( $entry['ID'] ) );
        } elseif ( is_string( $entry ) ) {
            $un = @unserialize( $entry );
            if ( is_array( $un ) ) {
                foreach ( $un as $val ) {
                    $related[] = array( 'ID' => intval( $val ) );
                }
            } elseif ( is_numeric( $entry ) ) {
                $related[] = array( 'ID' => intval( $entry ) );
            }
        }
    }
    echo '<small class="d-block text-center text-warning">doc_plus_debug: related IDs=' . esc_html( implode( ',', wp_list_pluck( $related, 'ID' ) ) ) . '</small>';

    // 5) Se vuoto, nessun doc_plus
    if ( empty( $related ) ) {
        echo '<small class="d-block text-center text-danger">doc_plus_debug: no doc_plus linked</small>';
        return ob_get_clean();
    }

    // 6) Header
    echo '<small class="d-block text-center text-info">doc_plus_debug: lang=' . esc_html($current_lang)
       . ( empty( $filter_ids ) ? '' : ', filter IDs=' . esc_html( implode( ',', $filter_ids ) ) )
       . '</small>';

    // 7) Count
    $total = count( $related );
    $shown = 0;
    foreach ( $related as $r ) {
        if ( empty( $filter_ids ) || in_array( intval( $r['ID'] ), $filter_ids, true ) ) {
            $shown++;
        }
    }
    echo '<small class="d-block text-center text-muted">trovati ' . esc_html($total)
       . ( empty( $filter_ids ) ? '' : ', mostrati ' . esc_html($shown) )
       . '</small>';

    // 8) Loop doc_plus
    foreach ( $related as $rel ) {
        $doc_id = intval( $rel['ID'] );
        if ( ! empty( $filter_ids ) && ! in_array( $doc_id, $filter_ids, true ) ) {
            echo '<small class="d-block text-center text-secondary">doc_plus_debug: skip doc_id=' . esc_html($doc_id) . ' (filtered)</small>';
            continue;
        }
        echo '<small class="d-block text-success">doc_plus_debug: processing doc_id=' . esc_html($doc_id) . '</small>';

                        // Load Pod for doc_plus in the current language
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);
        if (!$pod->exists()) {
            echo '<small class="d-block text-warning">doc_plus_debug: pod not found in ' . esc_html($current_lang) . ' for doc_id=' . esc_html($doc_id) . '</small>';
            // Fallback to default language
            $fallback_id = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            $pod = pods('doc_plus', $fallback_id, ['lang' => $default_lang]);
            echo '<small class="d-block text-warning">doc_plus_debug: using fallback pod lang=' . esc_html($default_lang) . ' for doc_id=' . esc_html($fallback_id) . '</small>';
        }

        // Title & Cover
        $title = get_the_title( $pod->ID() );
        echo '<small class="d-block">DOC ID=' . esc_html($pod->ID()) . ' titolo="' . esc_html($title) . '"</small>';
        $cover_id = $pod->field( 'doc_plus_cover.ID' );
        echo '<small class="d-block">cover_id=' . esc_html($cover_id) . '</small>';

        // 9) ALLEGATI debug – raw post_meta
        $raw_meta_att = get_post_meta( $pod->ID(), 'doc_plus_allegati', false );
        $raw_ids = array();
        foreach ( $raw_meta_att as $e2 ) {
            if ( is_array( $e2 ) && isset( $e2['ID'] ) ) {
                $raw_ids[] = intval( $e2['ID'] );
            } elseif ( is_string( $e2 ) && is_numeric( $e2 ) ) {
                $raw_ids[] = intval( $e2 );
            } elseif ( is_string( $e2 ) ) {
                $u2 = @unserialize( $e2 );
                if ( is_array( $u2 ) ) {
                    foreach ( $u2 as $val2 ) {
                        if ( is_numeric( $val2 ) ) { $raw_ids[] = intval( $val2 ); }
                    }
                }
            }
        }
        echo '<small class="d-block text-primary">doc_plus_debug: raw meta attachment IDs=' . esc_html( implode( ',', $raw_ids ) ) . '</small>';

        // Parsed attachments for display
        $attachments = array_map( function( $id ) { return array( 'ID' => $id ); }, $raw_ids );
        echo '<small class="d-block text-primary">doc_plus_debug: parsed allegati count=' . esc_html(count($attachments)) . '</small>';

        // Show attachments
        foreach ( $attachments as $att ) {
            $pdf_id = intval( $att['ID'] );
            echo '<small class="d-block text-info">doc_plus_debug: processing attachment ID=' . esc_html($pdf_id) . '</small>';
            $pp = pods( 'documenti_prodotto', $pdf_id, array( 'lang' => $current_lang ) );
            if ( ! $pp->exists() ) {
                echo '<small class="d-block text-warning">doc_plus_debug: fallback pp for pdf_id=' . esc_html($pdf_id) . '</small>';
                $fbp = apply_filters( 'wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang ) ?: $pdf_id;
                $pp = pods( 'documenti_prodotto', $fbp, array( 'lang' => $default_lang ) );
            }
            $pdf_title = get_the_title( $pp->ID() );
            $file_id   = $pp->field( 'documento-prodotto.ID' );
            $file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';
            $langs     = $pp->field( 'lingua_aggiuntiva' );
            $lang_term = ! empty( $langs ) ? $langs[0] : array( 'slug' => 'n.d.', 'name' => 'n.d.' );

            echo '<small class="d-block">PDF ID=' . esc_html($pp->ID())
               . ' tit=' . esc_html($pdf_title)
               . ' url=' . esc_html($file_url)
               . ' lingua=' . esc_html($lang_term['slug'] . ':' . $lang_term['name'])
               . '</small>';
        }
    }

    // 10) Close
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode( 'doc_plus', 'doc_plus_debug_shortcode' );
