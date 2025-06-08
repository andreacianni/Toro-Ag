<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con fallback completo
 * Fallback per pagina, doc_plus e allegati, emette debug in card.
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

    // 4) Fallback relazione se vuoto
    if (empty($related)) {
        $fallback_page_id = apply_filters('wpml_object_id', $orig_page_id, 'page', true, $default_lang) ?: $orig_page_id;
        // leggo raw postmeta
        $all_meta = get_post_meta($fallback_page_id, 'doc_plus_inpage', false);
        $related = [];
        foreach ($all_meta as $entry) {
            // unserialize se necessario
            if (!is_array($entry)) {
                $un = @unserialize($entry);
                if ($un !== false && is_array($un)) {
                    foreach ($un as $id_val) {
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
        echo esc_html("doc_plus_debug: fallback raw pag-relazione da pagina {$fallback_page_id}, elementi=" . count($related));
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
        // Provo Pods doc_plus in current lang
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);
        // Fallback doc_plus se non esiste in current lang
        if (!$pod->exists()) {
            $fallback_doc_id = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            echo '<small class="d-block text-center text-muted">';
            echo esc_html("doc_plus_debug: fallback doc_plus da {$doc_id} a {$fallback_doc_id}");
            echo '</small>';
            $pod = pods('doc_plus', $fallback_doc_id, ['lang' => $default_lang]);
        }

        // Titolo
        $title = get_the_title($pod->ID());
        echo '<small class="d-block">';
        echo esc_html("DOC ID={$pod->ID()} lang={$current_lang} titolo=\"{$title}\"");
        echo '</small>';

        // Cover ID
        $cover_id = $pod->field('doc_plus_cover.ID');
        echo '<small class="d-block">';
        echo esc_html("cover_id={$cover_id}");
        echo '</small>';

        // Allegati Pods
        $allegati = $pod->field('doc_plus_allegati');
        echo '<small class="d-block text-center text-muted mb-1">';
        echo esc_html("metodo allegati: Pods, count=" . count((array)$allegati));
        echo '</small>';

        // Fallback allegati se vuoto
        if (empty($allegati)) {
            $fallback_doc_id = $pod->ID();
            // fallback base doc id in default lang
            $fallback_doc_id = apply_filters('wpml_object_id', $fallback_doc_id, 'doc_plus', true, $default_lang) ?: $fallback_doc_id;
            $all_att = get_post_meta($fallback_doc_id, 'doc_plus_allegati', false);
            $allegati = [];
            foreach ($all_att as $entry2) {
                if (!is_array($entry2)) {
                    $un2 = @unserialize($entry2);
                    if ($un2 !== false && is_array($un2)) {
                        foreach ($un2 as $id_val2) {
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
            echo esc_html("nessun allegato per doc_plus {$pod->ID()}");
            echo '</small>';
            continue;
        }

        // 7) Loop allegati
        foreach ($allegati as $att) {
            $pdf_id = intval($att['ID']);
            $pod_pdf = pods('documenti_prodotto', $pdf_id, ['lang' => $current_lang]);
            // fallback PDF item if not exists
            if (!$pod_pdf->exists()) {
                $pdf_fallback = apply_filters('wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang) ?: $pdf_id;
                $pod_pdf = pods('documenti_prodotto', $pdf_fallback, ['lang' => $default_lang]);
            }
            $pdf_title = get_the_title($pod_pdf->ID());
            $langs = $pod_pdf->field('lingua_aggiuntiva');
            if (!empty($langs)) {
                $t = $langs[0];
                $slug = $t['slug'];
                $name = $t['name'];
            } else {
                $slug = $name = 'n.d.';
            }
            echo '<small class="d-block">';
            echo esc_html("ALLEGATO PDF_ID={$pod_pdf->ID()} titolo_pdf=\"{$pdf_title}\" lingua_aggiuntiva={$slug}:{$name}");
            echo '</small>';
        }
    }

    // 8) Chiusura card
    echo '</div></div></div>';
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
