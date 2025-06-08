<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con fallback in stile video_prodotto_v2, esteso per fallback allegati
 */
function doc_plus_debug_shortcode() {
    // 1) Setup lingue
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 2) ID pagina originale
    $orig_page_id = get_the_ID();

    // 3) Leggo relazione con Pods nella lingua corrente
    $page_pod = pods('page', $orig_page_id, ['lang' => $current_lang]);
    $related = $page_pod->field('doc_plus_inpage');

    // Inizio card Bootstrap
    echo '<div class="d-flex justify-content-center my-4">';
      echo '<div class="card shadow-sm" style="max-width:600px; width:100%;">';
        echo '<div class="card-header text-center">';
          echo esc_html("doc_plus_debug: eseguito lang={$current_lang}, metodo=Pods pag-relazione");
        echo '</div>';
        echo '<div class="card-body p-3">';

    // 4) Fallback relazione se Vuoto
    if (empty($related)) {
        $fallback_id = apply_filters('wpml_object_id', $orig_page_id, 'page', true, $default_lang) ?: $orig_page_id;
        $all_meta = get_post_meta($fallback_id, 'doc_plus_inpage', false);
        $related = [];
        foreach ($all_meta as $entry) {
            if (!is_array($entry)) {
                $maybe = @unserialize($entry);
                if ($maybe !== false && is_array($maybe)) {
                    foreach ($maybe as $id_val) {
                        $related[] = ['ID' => intval($id_val)];
                    }
                    continue;
                }
                if (is_numeric($entry)) {
                    $related[] = ['ID' => intval($entry)];
                    continue;
                }
            }
            if (is_array($entry) && isset($entry['ID'])) {
                $related[] = ['ID' => intval($entry['ID'])];
            }
        }
        echo '<small class="d-block text-center text-muted mb-2">';
        echo esc_html("doc_plus_debug: fallback raw pag-relazione da pagina {$fallback_id}, elementi=" . count($related));
        echo '</small>';
    }

    if (empty($related)) {
        echo '<small class="d-block text-center text-muted">';
        echo esc_html("doc_plus_debug: nessun doc_plus trovato");
        echo '</small>';
        echo '</div></div></div>';
        return;
    }

    // 5) Conteggio
    echo '<small class="d-block mb-2 text-center text-muted">';
    echo esc_html("trovati " . count($related) . " doc_plus");
    echo '</small>';

    // 6) Loop doc_plus
    foreach ($related as $rel) {
        $doc_id = intval($rel['ID']);
        // Carico Pod doc_plus in current lang
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);

        // Titolo
        $title = get_the_title($doc_id);
        echo '<small class="d-block">';
        echo esc_html("DOC ID={$doc_id} lang={$current_lang} titolo=\"{$title}\"");
        echo '</small>';

        // Cover ID
        $cover_id = $pod->field('doc_plus_cover.ID');
        echo '<small class="d-block">';
        echo esc_html("cover_id={$cover_id}");
        echo '</small>';

        // 7) Allegati Pods
        $allegati = $pod->field('doc_plus_allegati');
        echo '<small class="d-block text-center text-muted mb-1">';
        echo esc_html("metodo allegati: Pods, count=" . count($allegati));
        echo '</small>';

        // Fallback allegati se vuoto
        if (empty($allegati)) {
            $fallback_doc_id = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            $all_att = get_post_meta($fallback_doc_id, 'doc_plus_allegati', false);
            $allegati = [];
            foreach ($all_att as $entry2) {
                if (!is_array($entry2)) {
                    $maybe2 = @unserialize($entry2);
                    if ($maybe2 !== false && is_array($maybe2)) {
                        foreach ($maybe2 as $id_val2) {
                            $allegati[] = ['ID' => intval($id_val2)];
                        }
                        continue;
                    }
                    if (is_numeric($entry2)) {
                        $allegati[] = ['ID' => intval($entry2)];
                        continue;
                    }
                }
                if (is_array($entry2) && isset($entry2['ID'])) {
                    $allegati[] = ['ID' => intval($entry2['ID'])];
                }
            }
            echo '<small class="d-block text-center text-muted mb-2">';
            echo esc_html("doc_plus_debug: fallback raw allegati da doc_plus {$fallback_doc_id}, elementi=" . count($allegati));
            echo '</small>';
        }

        if (empty($allegati)) {
            echo '<small class="d-block text-muted">';
            echo esc_html("nessun allegato per doc_plus {$doc_id}");
            echo '</small>';
            continue;
        }

        // 8) Loop allegati
        foreach ($allegati as $att) {
            $pdf_id = intval($att['ID']);
            $pdf_title = get_the_title($pdf_id);
            $pod_pdf = pods('documenti_prodotto', $pdf_id, ['lang' => $current_lang]);
            $langs = $pod_pdf->field('lingua_aggiuntiva');
            if (!empty($langs)) {
                $t = $langs[0];
                $slug = $t['slug'];
                $name = $t['name'];
            } else {
                $slug = $name = 'n.d.';
            }
            echo '<small class="d-block">';
            echo esc_html("ALLEGATO PDF_ID={$pdf_id} titolo_pdf=\"{$pdf_title}\" lingua_aggiuntiva={$slug}:{$name}");
            echo '</small>';
        }
    }

    // 9) Chiusura card
    echo '</div></div></div>';
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
