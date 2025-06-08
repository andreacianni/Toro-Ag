<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con filtro ID opzionale
 * Recupera sempre tutti i doc_plus collegati con fallback completo,
 * poi se 'ids' è passato filtra quali elementi mostrare.
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Gestione attributi
    $atts = shortcode_atts( array(
        'ids' => '',
    ), $atts, 'doc_plus' );

    // 2) Parse di eventuali ID di filtro in array di interi
    $filter_ids = array();
    if ( ! empty( $atts['ids'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids']) as $v ) {
            $n = intval( $v );
            if ( $n > 0 ) {
                $filter_ids[] = $n;
            }
        }
    }

    // 3) Output buffering
    ob_start();

    // 4) Setup lingue
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 5) Recupero relazione doc_plus_inpage dalla pagina in lingua corrente
    $page_id  = get_the_ID();
    $page_pod = pods('page', $page_id, ['lang' => $current_lang]);
    $related  = (array) $page_pod->field('doc_plus_inpage');

    // 6) Fallback raw meta se vuoto
    if ( empty( $related ) ) {
        $fallback_page_id = apply_filters('wpml_object_id', $page_id, 'page', false, $default_lang) ?: $page_id;
        $raw_list = get_post_meta( $fallback_page_id, 'doc_plus_inpage', false );
        $related = array();
        foreach ( $raw_list as $entry ) {
            if ( is_array( $entry ) && isset( $entry['ID'] ) ) {
                $related[] = array('ID' => intval($entry['ID']));
            } elseif ( is_string( $entry ) ) {
                $un = @unserialize($entry);
                if ( is_array($un) ) {
                    foreach ( $un as $id_val ) {
                        $related[] = array('ID' => intval($id_val));
                    }
                } elseif ( is_numeric($entry) ) {
                    $related[] = array('ID' => intval($entry));
                }
            }
        }
    }

    // 7) Se non ci sono doc_plus, esco
    if ( empty( $related ) ) {
        echo '<div class="d-flex justify-content-center my-4">'
           . '<div class="card shadow-sm" style="max-width:600px;width:100%;">'
           . '<div class="card-header text-center">'
           . esc_html("doc_plus_debug: lang={$current_lang}, nessun doc_plus collegato")
           . '</div><div class="card-body p-3">'
           . '<small class="d-block text-center text-muted">Nessun doc_plus da mostrare</small>'
           . '</div></div></div>';
        return ob_get_clean();
    }

    // 8) Stampa card e header con eventuale filtro
    echo '<div class="d-flex justify-content-center my-4">'
       . '<div class="card shadow-sm" style="max-width:600px;width:100%;">'
       . '<div class="card-header text-center">'
       . esc_html(
           "doc_plus_debug: lang={$current_lang}" 
           . (!empty($filter_ids) ? ", filtro IDs=".implode(',', $filter_ids) : '')
         )
       . '</div><div class="card-body p-3">';

    // 9) Conteggio totali e visualizzati
    $total = count($related);
    // Se filtro applicato, calcolo quanti rimangono
    $shown = 0;
    foreach ($related as $r) {
        if ( !empty($filter_ids) && !in_array(intval($r['ID']), $filter_ids, true) ) {
            continue;
        }
        $shown++;
    }
    echo '<small class="d-block mb-2 text-center text-muted">'
       . esc_html("trovati {$total} doc_plus" . (!empty($filter_ids) ? ", mostrati {$shown}" : ''))
       . '</small>';

    // 10) Ciclo su ogni doc_plus e applico filtro di visualizzazione
    foreach ( $related as $entry ) {
        $doc_id = intval($entry['ID']);
        if ( !empty($filter_ids) && !in_array($doc_id, $filter_ids, true) ) {
            continue;
        }
        // Carico pod doc_plus con fallback lingua
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);
        if ( ! $pod->exists() ) {
            $fb = apply_filters('wpml_object_id', $doc_id, 'doc_plus', false, $default_lang) ?: $doc_id;
            $pod = pods('doc_plus', $fb, ['lang' => $default_lang]);
        }
        // Titolo e cover
        echo '<small class="d-block">'
           . esc_html("DOC ID={$pod->ID()} titolo=\"".get_the_title($pod->ID())."\"") . '</small>';
        echo '<small class="d-block">'
           . esc_html("cover_id=".$pod->field('doc_plus_cover.ID'))
           . '</small>';
        // Allegati con fallback raw meta
        $atts_list = (array)$pod->field('doc_plus_allegati');
        if ( empty($atts_list) ) {
            foreach ( get_post_meta($pod->ID(), 'doc_plus_allegati', false) as $e2 ) {
                if ( is_array($e2) && isset($e2['ID']) ) {
                    $atts_list[] = ['ID'=>intval($e2['ID'])];
                } elseif ( is_string($e2) ) {
                    $u2 = @unserialize($e2);
                    if ( is_array($u2) ) {
                        foreach ($u2 as $v2) {
                            $atts_list[] = ['ID'=>intval($v2)];
                        }
                    } elseif ( is_numeric($e2) ) {
                        $atts_list[] = ['ID'=>intval($e2)];
                    }
                }
            }
        }
        echo '<small class="d-block text-center text-muted mb-1">allegati count=' . count($atts_list) . '</small>';
        foreach ( $atts_list as $a2 ) {
            $aid = intval($a2['ID']);
            $pp = pods('documenti_prodotto', $aid, ['lang'=>$current_lang]);
            if ( ! $pp->exists() ) {
                $fbp = apply_filters('wpml_object_id', $aid, 'documenti_prodotto', false, $default_lang) ?: $aid;
                $pp = pods('documenti_prodotto', $fbp, ['lang'=>$default_lang]);
            }
            echo '<small class="d-block">PDF ID=' . $pp->ID() . ' tit=' . esc_html(get_the_title($pp->ID())) . '</small>';
        }
    }

    // 11) Chiusura card
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
