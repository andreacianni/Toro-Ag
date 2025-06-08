<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con fallback in stile video_prodotto_v2
 * Usa Pods + 'lang' e se vuoto fa fallback a raw post meta.
 */
function doc_plus_debug_shortcode() {
    // Setup lingue
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // ID pagina originale
    $orig_page_id = get_the_ID();

    // 1) Provo con Pods nella lingua corrente
    $page_pod = pods('page', $orig_page_id, ['lang' => $current_lang]);
    $related = $page_pod->field('doc_plus_inpage');

    // Debug header
    echo '<div class="d-flex justify-content-center my-4">';
      echo '<div class="card shadow-sm" style="max-width:600px; width:100%;">';
        echo '<div class="card-header text-center">';
          echo esc_html("doc_plus_debug: eseguito lang={$current_lang} con Pods");
        echo '</div>';
        echo '<div class="card-body p-3">';

    // 2) Se Pods non restituisce nulla, fallback a raw post meta della pagina default
    if (empty($related)) {
        // Trovo ID pagina nella lingua default
        $fallback_id = apply_filters('wpml_object_id', $orig_page_id, 'page', true, $default_lang) ?: $orig_page_id;
        // Carico raw meta dal DB
        $raw = get_post_meta($fallback_id, 'doc_plus_inpage', true);
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $raw = $decoded;
            }
        }
        // Ricostruisco array di relazioni
        $related = [];
        if (is_array($raw)) {
            foreach ($raw as $item) {
                if (!empty($item['ID'])) {
                    $related[] = ['ID' => intval($item['ID'])];
                }
            }
        }
        echo '<small class="d-block text-center text-muted mb-2">';
        echo esc_html("doc_plus_debug: fallback raw meta da pagina {$fallback_id}, elementi=" . count($related));
        echo '</small>';
    }

    // 3) Se ancora vuoto, esco
    if (empty($related)) {
        echo '<small class="d-block text-center text-muted">';
        echo esc_html("doc_plus_debug: nessun doc_plus trovato");
        echo '</small>';
        echo '</div></div></div>';
        return;
    }

    // 4) Mostro quanti documenti
    echo '<small class="d-block mb-2 text-center text-muted">';
    echo esc_html("trovati " . count($related) . " doc_plus");
    echo '</small>';

    // 5) Ciclo ogni doc_plus
    foreach ($related as $rel) {
        $doc_id = intval($rel['ID']);
        // Potrei anche tradurre ogni doc_plus ID, ma assumiamo che il contenuto sia valido
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

        // Allegati
        $allegati = $pod->field('doc_plus_allegati');
        if (empty($allegati)) {
            echo '<small class="d-block text-muted">';
            echo esc_html("nessun allegato per doc_plus {$doc_id}");
            echo '</small>';
            continue;
        }
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

    // Chiudo card
    echo '</div></div></div>';
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
