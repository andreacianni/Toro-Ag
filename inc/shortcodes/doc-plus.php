<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con fallback completo e filtro opzionale
 * Recupera sempre tutti i doc_plus collegati (con fallback per pagina, doc_plus e allegati),
 * poi se il parametro `ids` è passato filtra i risultati.
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Gestione attributi
    $atts = shortcode_atts( array(
        'ids' => '',
    ), $atts, 'doc_plus' );

    // 2) Avvio buffering
    ob_start();

    // 3) Setup lingue
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 4) Recupero relazione doc_plus_inpage dalla pagina (Pods)
    $page_id  = get_the_ID();
    $page_pod = pods('page', $page_id, ['lang' => $current_lang]);
    $related  = (array) $page_pod->field('doc_plus_inpage');

    // 5) Fallback raw meta se vuoto
    if ( empty($related) ) {
        $fallback_page_id = apply_filters('wpml_object_id', $page_id, 'page', false, $default_lang) ?: $page_id;
        $raw = get_post_meta($fallback_page_id, 'doc_plus_inpage', false);
        $related = array();
        foreach ( $raw as $r ) {
            if ( is_array($r) && isset($r['ID']) ) {
                $related[] = array('ID' => intval($r['ID']));
            } elseif ( is_string($r) ) {
                $u = @unserialize($r);
                if ( is_array($u) ) {
                    foreach ( $u as $idval ) {
                        $related[] = array('ID' => intval($idval));
                    }
                } elseif ( is_numeric($r) ) {
                    $related[] = array('ID' => intval($r));
                }
            }
        }
    }

    // 6) Se non ci sono doc_plus, esco
    if ( empty($related) ) {
        echo '<div class="d-flex justify-content-center my-4">'
           . '<div class="card shadow-sm" style="max-width:600px;width:100%;">'
           . '<div class="card-header text-center">'
           . esc_html("doc_plus_debug: lang={$current_lang}, nessun doc_plus collegato alla pagina")
           . '</div><div class="card-body p-3">'
           . '<small class="d-block text-center text-muted">Nessun doc_plus da mostrare</small>'
           . '</div></div></div>';
        return ob_get_clean();
    }

    // 7) Parse filtro IDs se forniti
    $filter_ids = array();
    if ( ! empty($atts['ids']) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids']) as $v ) {
            $n = intval($v);
            if ( $n > 0 ) {
                $filter_ids[] = $n;
            }
        }
        if ( ! empty($filter_ids) ) {
            $related = array_filter($related, function( $itm ) use ( $filter_ids ) {
                return in_array( intval($itm['ID']), $filter_ids, true );
            });
        }
    }

    // 8) Header card
    echo '<div class="d-flex justify-content-center my-4">'
       . '<div class="card shadow-sm" style="max-width:600px;width:100%;">'
       . '<div class="card-header text-center">'
       . esc_html(
           "doc_plus_debug: lang={$current_lang}" 
           . (!empty($filter_ids) ? ", filtro IDs=" . implode(',', $filter_ids) : '')
         )
       . '</div><div class="card-body p-3">';

    // 9) Se filtro rimuove tutto
    if ( empty($related) ) {
        echo '<small class="d-block text-center text-muted">Nessun doc_plus corrispondente al filtro</small>';
        echo '</div></div></div>';
        return ob_get_clean();
    }

    // 10) Conteggio risultati
    echo '<small class="d-block mb-2 text-center text-muted">'
       . esc_html("trovati " . count($related) . " doc_plus")
       . '</small>';

    // 11) Ciclo su ogni doc_plus con fallback Pod e fallback allegati
    foreach ( $related as $ent ) {
        $doc_id = intval($ent['ID']);
        // Carico Pod doc_plus
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);
        if ( ! $pod->exists() ) {
            $fbdoc = apply_filters('wpml_object_id', $doc_id, 'doc_plus', false, $default_lang) ?: $doc_id;
            $pod = pods('doc_plus', $fbdoc, ['lang' => $default_lang]);
        }
        // Titolo e Cover ID
        echo '<small class="d-block">'
           . esc_html("DOC ID={$pod->ID()} titolo=\"" . get_the_title($pod->ID()) . "\"")
           . '</small>';
        echo '<small class="d-block">'
           . esc_html("cover_id=" . $pod->field('doc_plus_cover.ID'))
           . '</small>';

        // Allegati Pods
        $atts_arr = (array) $pod->field('doc_plus_allegati');
        if ( empty($atts_arr) ) {
            // fallback raw allegati
            foreach ( get_post_meta($pod->ID(), 'doc_plus_allegati', false) as $e2 ) {
                if ( is_array($e2) && isset($e2['ID']) ) {
                    $atts_arr[] = ['ID'=>intval($e2['ID'])];
                } elseif ( is_string($e2) ) {
                    $u2 = @unserialize($e2);
                    if ( is_array($u2) ) {
                        foreach ( $u2 as $val2 ) {
                            $atts_arr[] = ['ID'=>intval($val2)];
                        }
                    } elseif ( is_numeric($e2) ) {
                        $atts_arr[] = ['ID'=>intval($e2)];
                    }
                }
            }
        }
        echo '<small class="d-block text-center text-muted mb-1">allegati count=' . count($atts_arr) . '</small>';
        foreach ( $atts_arr as $a2 ) {
            $pdf_id = intval($a2['ID']);
            $ppod = pods('documenti_prodotto', $pdf_id, ['lang' => $current_lang]);
            if ( ! $ppod->exists() ) {
                $fbpdf = apply_filters('wpml_object_id', $pdf_id, 'documenti_prodotto', false, $default_lang) ?: $pdf_id;
                $ppod = pods('documenti_prodotto', $fbpdf, ['lang' => $default_lang]);
            }
            echo '<small class="d-block">PDF ID=' . $ppod->ID() . ' tit=' . esc_html(get_the_title($ppod->ID())) . '</small>';
        }
    }

    // 12) Chiudo card e restituisco
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
