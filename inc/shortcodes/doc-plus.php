<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card migliorata
 * Visualizza in una card centrata e compatta tutte le info di debug.
 */
function doc_plus_debug_shortcode() {
    // 1) Lingua corrente e default
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '';
    $default_lang = apply_filters('wpml_default_language', null);

    // 2) ID pagina corrente e fallback
    $orig_page_id = get_the_ID();
    $load_page_id = ($current_lang && $current_lang !== $default_lang)
        ? apply_filters('wpml_object_id', $orig_page_id, 'page', false, $default_lang)
        : $orig_page_id;

    // 3) Leggi raw meta e decodifica JSON se necessario
    $raw_meta = get_post_meta($load_page_id, 'doc_plus_inpage', true);
    if (is_string($raw_meta)) {
        $decoded = json_decode($raw_meta, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $raw_meta = $decoded;
        }
    }

    // 4) Costruisci array di relazioni con solo gli ID
    $related = [];
    if (is_array($raw_meta)) {
        foreach ($raw_meta as $r) {
            if (!empty($r['ID'])) {
                $related[] = ['ID' => intval($r['ID'])];
            }
        }
    }

    // 5) Inizio card centrata e compatta
    echo '<div class="d-flex justify-content-center my-4">';
      echo '<div class="card shadow-sm" style="max-width:600px; width:100%;">';
        echo '<div class="card-header text-center">';
          echo esc_html("doc_plus_debug: eseguito lang={$current_lang} pagina={$load_page_id}");
        echo '</div>';
        echo '<div class="card-body p-3">';

          // Nessun doc_plus collegato
          if (empty($related)) {
              echo '<small class="d-block text-center text-muted">';
              echo esc_html("doc_plus_debug: nessun doc_plus per pagina {$load_page_id}");
              echo '</small>';
              echo '</div></div></div>';
              return;
          }

          // Header conteggio
          echo '<small class="d-block mb-2 text-center text-muted">';
          echo esc_html("trovati " . count($related) . " doc_plus per pagina {$load_page_id}");
          echo '</small>';

          // Loop documenti
          foreach ($related as $item) {
              $doc_id = intval($item['ID']);
              $pod    = pods('doc_plus', $doc_id);

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
                  $pdf_id    = intval($att['ID']);
                  $pdf_title = get_the_title($pdf_id);
                  $pod_pdf   = pods('documenti_prodotto', $pdf_id);
                  $langs     = $pod_pdf->field('lingua_aggiuntiva');
                  if (!empty($langs)) {
                      $t    = $langs[0];
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

        echo '</div>';   // card-body
      echo '</div>';     // card
    echo '</div>';       // container
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
