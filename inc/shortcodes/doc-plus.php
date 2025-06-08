<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con filtro ID opzionale
 * Recupera sempre tutti i doc_plus collegati (fallback pagina e raw meta),
 * poi se 'ids' è passato filtra quali elementi mostrare.
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Gestione attributi e parsing filtro IDs
    $atts = shortcode_atts( array( 'ids' => '' ), $atts, 'doc_plus' );
    $filter_ids = array();
    if ( ! empty( $atts['ids'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids']) as $v ) {
            $i = intval( $v ); if ( $i > 0 ) $filter_ids[] = $i;
        }
    }

    // 2) Buffering output
    ob_start();

    // 3) Lingua corrente e default
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 4) ID pagina e fallback a default per raw meta
    $page_id = get_the_ID();
    $fallback_page_id = ( $current_lang && $current_lang !== $default_lang )
        ? apply_filters('wpml_object_id', $page_id, 'page', true, $default_lang )
        : $page_id;

    // 5) Recupero raw meta doc_plus_inpage
    $raw_list = get_post_meta( $fallback_page_id, 'doc_plus_inpage', false );
    $related = array();
    foreach ( $raw_list as $entry ) {
        if ( is_array( $entry ) && isset( $entry['ID'] ) ) {
            $related[] = array( 'ID' => intval( $entry['ID'] ) );
        } elseif ( is_string( $entry ) ) {
            $un = @unserialize( $entry );
            if ( is_array( $un ) ) {
                foreach ( $un as $id_val ) {
                    $related[] = array( 'ID' => intval( $id_val ) );
                }
            } elseif ( is_numeric( $entry ) ) {
                $related[] = array( 'ID' => intval( $entry ) );
            }
        }
    }

    // 6) Se vuoto, nessun doc_plus
    if ( empty( $related ) ) {
        echo '<div class="d-flex justify-content-center my-4">'
           . '<div class="card w-75 shadow-sm">'
           . '<div class="card-body text-center">'
           . '<small class="text-muted">doc_plus_debug: lang=' . esc_html($current_lang) . ', nessun doc_plus collegato</small>'
           . '</div></div></div>';
        return ob_get_clean();
    }

    // 7) Card header con filtro opzionale
    echo '<div class="d-flex justify-content-center my-4">'
       . '<div class="card w-75 shadow-sm">'
       . '<div class="card-header text-center">'
       . esc_html(
           'doc_plus_debug: lang=' . $current_lang
           . ( ! empty( $filter_ids ) ? ', filtro IDs=' . implode(',', $filter_ids ) : '' )
         )
       . '</div><div class="card-body">';

    // 8) Loop e filtro
    foreach ( $related as $rel ) {
        $doc_id = intval( $rel['ID'] );
        if ( ! empty( $filter_ids ) && ! in_array( $doc_id, $filter_ids, true ) ) {
            continue;
        }

        // 9) Caricamento Pod doc_plus con fallback lingua
        $pod = pods('doc_plus', $doc_id, array( 'lang' => $current_lang ) );
        if ( ! $pod->exists() ) {
            $fb = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang ) ?: $doc_id;
            $pod = pods('doc_plus', $fb, array( 'lang' => $default_lang ) );
        }

        // Titolo e cover
        echo '<small class="d-block">' . esc_html(
            'DOC ID=' . $pod->ID()
            . ' titolo="' . get_the_title( $pod->ID() ) . '"'
        ) . '</small>';
        echo '<small class="d-block">' . esc_html(
            'cover_id=' . $pod->field('doc_plus_cover.ID')
        ) . '</small>';

        // 10) Allegati con fallback raw meta
        $atts_list = (array) $pod->field('doc_plus_allegati');
        if ( empty( $atts_list ) ) {
            foreach ( get_post_meta( $pod->ID(), 'doc_plus_allegati', false ) as $e2 ) {
                if ( is_array( $e2 ) && isset( $e2['ID'] ) ) {
                    $atts_list[] = array( 'ID' => intval( $e2['ID'] ) );
                } elseif ( is_string( $e2 ) ) {
                    $u2 = @unserialize( $e2 );
                    if ( is_array( $u2 ) ) {
                        foreach ( $u2 as $v2 ) {
                            $atts_list[] = array( 'ID' => intval( $v2 ) );
                        }
                    } elseif ( is_numeric( $e2 ) ) {
                        $atts_list[] = array( 'ID' => intval( $e2 ) );
                    }
                }
            }
        }
        echo '<small class="d-block text-center text-muted mb-1">allegati count=' . count( $atts_list ) . '</small>';
        foreach ( $atts_list as $a2 ) {
            $pdf_id = intval( $a2['ID'] );
            $pp = pods('documenti_prodotto', $pdf_id, array( 'lang' => $current_lang ) );
            if ( ! $pp->exists() ) {
                $fbp = apply_filters('wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang ) ?: $pdf_id;
                $pp = pods('documenti_prodotto', $fbp, array( 'lang' => $default_lang ) );
            }
            echo '<small class="d-block">PDF ID=' . esc_html( $pp->ID() )
               . ' tit=' . esc_html( get_the_title( $pp->ID() ) )
               . '</small>';
        }
    }

    // 11) Chiusura card
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
