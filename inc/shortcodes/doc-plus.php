<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con filtro ID opzionale
 * Recupera sempre tutti i doc_plus collegati dalla pagina corrente,
 * poi se 'ids' è passato filtra quali elementi mostrare.
 * Per ogni doc_plus mostra cover unica e per ogni allegato titolo, URL PDF e lingua aggiuntiva.
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Gestione attributi e parsing filtro IDs
    $atts = shortcode_atts( array( 'ids' => '' ), $atts, 'doc_plus' );
    $filter_ids = array();
    if ( ! empty( $atts['ids'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids']) as $v ) {
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
    $raw_list = get_post_meta($page_id, 'doc_plus_inpage', false);
    $related = array();
    foreach ($raw_list as $entry) {
        if (is_array($entry) && isset($entry['ID'])) {
            $related[] = ['ID' => intval($entry['ID'])];
        } elseif (is_string($entry)) {
            $un = @unserialize($entry);
            if (is_array($un)) {
                foreach ($un as $id_val) {
                    $related[] = ['ID' => intval($id_val)];
                }
            } elseif (is_numeric($entry)) {
                $related[] = ['ID' => intval($entry)];
            }
        }
    }

    // 5) Se vuoto, nessun doc_plus
    if (empty($related)) {
        echo '<div class="d-flex justify-content-center my-4">'
           . '<div class="card w-75 shadow-sm">'
           . '<div class="card-body text-center">'
           . '<small class="text-muted">doc_plus_debug: lang=' . esc_html($current_lang) . ', nessun doc_plus collegato</small>'
           . '</div></div></div>';
        return ob_get_clean();
    }

    // 6) Header card con filtro opzionale
    echo '<div class="d-flex justify-content-center my-4">'
       . '<div class="card w-75 shadow-sm">'
       . '<div class="card-header text-center">'
       . esc_html(
           'doc_plus_debug: lang=' . $current_lang
           . (!empty($filter_ids) ? ', filtro IDs=' . implode(',', $filter_ids) : '')
         )
       . '</div><div class="card-body">';

    // 7) Conteggio totale e mostrati
    $total = count($related);
    $shown = 0;
    foreach ($related as $r) {
        if (!empty($filter_ids) && !in_array(intval($r['ID']), $filter_ids, true)) {
            continue;
        }
        $shown++;
    }
    echo '<small class="d-block mb-2 text-center text-muted">'
       . esc_html("trovati {$total} doc_plus" . (!empty($filter_ids) ? ", mostrati {$shown}" : ''))
       . '</small>';

    // 8) Loop doc_plus con filtro
    foreach ($related as $entry) {
        $doc_id = intval($entry['ID']);
        if (!empty($filter_ids) && !in_array($doc_id, $filter_ids, true)) {
            continue;
        }
        // Carico il Pod doc_plus
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);
        if (!$pod->exists()) {
            $fb = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            $pod = pods('doc_plus', $fb, ['lang' => $default_lang]);
        }
        // TITOLO
        echo '<small class="d-block">DOC ID=' . $pod->ID() . ' titolo="' . esc_html(get_the_title($pod->ID())) . '"</small>';
        // COVER
        $cover_id = $pod->field('doc_plus_cover.ID');
        $cover_url = $cover_id ? wp_get_attachment_url($cover_id) : '';
        echo '<small class="d-block">cover_id=' . esc_html($cover_id) . '</small>';
        echo '<small class="d-block text-muted">cover_url=' . esc_html($cover_url) . '</small>';

        // 9) ALLEGATI
        $attachments = (array) $pod->field('doc_plus_allegati');
        // fallback raw meta allegati
        if (empty($attachments)) {
            foreach (get_post_meta($pod->ID(), 'doc_plus_allegati', false) as $e2) {
                if (is_array($e2) && isset($e2['ID'])) {
                    $attachments[] = ['ID' => intval($e2['ID'])];
                } elseif (is_string($e2)) {
                    $u2 = @unserialize($e2);
                    if (is_array($u2)) {
                        foreach ($u2 as $v2) {
                            $attachments[] = ['ID' => intval($v2)];
                        }
                    } elseif (is_numeric($e2)) {
                        $attachments[] = ['ID' => intval($e2)];
                    }
                }
            }
        }
        echo '<small class="d-block mb-1 text-center text-muted">allegati count=' . count($attachments) . '</small>';
        foreach ($attachments as $att) {
            $pdf_id = intval($att['ID']);
            $pp = pods('documenti_prodotto', $pdf_id, ['lang' => $current_lang]);
            if (!$pp->exists()) {
                $fbp = apply_filters('wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang) ?: $pdf_id;
                $pp = pods('documenti_prodotto', $fbp, ['lang' => $default_lang]);
            }
            // titolo, url, lingua aggiuntiva
            $pdf_title = get_the_title($pp->ID());
            $file_id = $pp->field('documento-prodotto.ID');
            $file_url = $file_id ? wp_get_attachment_url($file_id) : '';
            $langs = $pp->field('lingua_aggiuntiva');
            $lang_term = !empty($langs) ? $langs[0] : ['slug'=>'n.d.','name'=>'n.d.'];
            echo '<small class="d-block">PDF ID=' . esc_html($pp->ID())
               . ' tit=' . esc_html($pdf_title)
               . ' url=' . esc_html($file_url)
               . ' lingua=' . esc_html($lang_term['slug'] . ':' . $lang_term['name'])
               . '</small>';
        }
    }

    // 10) Chiusura card
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
