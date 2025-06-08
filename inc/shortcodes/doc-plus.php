<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con parametri ID e fallback completo
 * Se passi `ids="1,2,3"` mostra solo quei doc_plus, altrimenti recupera tutti con fallback.
 */
function doc_plus_debug_shortcode( $atts ) {
    // Shortcode attributes
    $atts = shortcode_atts( array(
        'ids' => '',
    ), $atts, 'doc_plus' );

    // Start output buffering
    ob_start();

    // Language setup
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // Parse provided IDs
    $provided = array();
    if ( ! empty( $atts['ids'] ) ) {
        $raw = preg_split('/\s*,\s*/', $atts['ids'] );
        foreach ( $raw as $r ) {
            $i = intval( $r );
            if ( $i > 0 ) {
                $provided[] = array( 'ID' => $i );
            }
        }
    }
    $using_provided = ! empty( $provided );

    // Card container and header
    echo '<div class="d-flex justify-content-center my-4">';
      echo '<div class="card shadow-sm" style="max-width:600px; width:100%;">';
        echo '<div class="card-header text-center">';
          if ( $using_provided ) {
              echo esc_html("doc_plus_debug: eseguito lang={$current_lang}, metodo=Param IDs, count=" . count($provided));
          } else {
              echo esc_html("doc_plus_debug: eseguito lang={$current_lang}, metodo=Pods pag-relazione");
          }
        echo '</div>';
        echo '<div class="card-body p-3">';

    // Determine related docs
    if ( $using_provided ) {
        // Use exactly provided IDs
        $related = $provided;
        if ( empty( $related ) ) {
            echo '<small class="d-block text-center text-muted">';
            echo esc_html("doc_plus_debug: nessun ID valido nei parametri");
            echo '</small></div></div></div>';
            return ob_get_clean();
        }
    } else {
        // Retrieve relation from page via Pods
        $page_id  = get_the_ID();
        $page_pod = pods('page', $page_id, ['lang' => $current_lang]);
        $related  = (array) $page_pod->field('doc_plus_inpage');
        // Fallback relation if empty
        if ( empty( $related ) ) {
            $fallback_page_id = apply_filters('wpml_object_id', $page_id, 'page', true, $default_lang)
                                ?: $page_id;
            $all_meta = get_post_meta($fallback_page_id, 'doc_plus_inpage', false);
            $related = [];
            foreach ( $all_meta as $entry ) {
                if ( ! is_array($entry) ) {
                    $un = @unserialize($entry);
                    if ( is_array($un) ) {
                        foreach ( $un as $id_val ) {
                            $related[] = ['ID' => intval($id_val)];
                        }
                        continue;
                    }
                    if ( is_numeric($entry) ) {
                        $related[] = ['ID' => intval($entry)];
                        continue;
                    }
                } elseif ( isset($entry['ID']) ) {
                    $related[] = ['ID' => intval($entry['ID'])];
                }
            }
            echo '<small class="d-block text-center text-muted mb-2">'
               . esc_html("doc_plus_debug: fallback raw pag-relazione da pagina {$fallback_page_id}, elementi=" . count($related))
               . '</small>';
        }
    }

    // If still empty
    if ( empty( $related ) ) {
        echo '<small class="d-block text-center text-muted">';
        echo esc_html("doc_plus_debug: nessun doc_plus trovato");
        echo '</small></div></div></div>';
        return ob_get_clean();
    }

    // Count
    echo '<small class="d-block mb-2 text-center text-muted">';
    echo esc_html("trovati " . count($related) . " doc_plus");
    echo '</small>';

    // Loop through doc_plus items
    foreach ( $related as $rel ) {
        $doc_id = intval( $rel['ID'] );
        // Load pod for doc_plus
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);
        // Always fallback doc_plus if not exists
        if ( ! $pod->exists() ) {
            $fallback_doc_id = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang)
                                ?: $doc_id;
            echo '<small class="d-block text-center text-muted">'
               . esc_html("doc_plus_debug: fallback doc_plus da {$doc_id} a {$fallback_doc_id}")
               . '</small>';
            $pod = pods('doc_plus', $fallback_doc_id, ['lang' => $default_lang]);
        }

        // Title
        $title = get_the_title($pod->ID());
        echo '<small class="d-block">' . esc_html("DOC ID={$pod->ID()} lang={$current_lang} titolo=\"{$title}\"") . '</small>';
        // Cover ID
        $cover_id = $pod->field('doc_plus_cover.ID');
        echo '<small class="d-block">' . esc_html("cover_id={$cover_id}") . '</small>';

        // Attachments
        $allegati = (array) $pod->field('doc_plus_allegati');
        echo '<small class="d-block text-center text-muted mb-1">'
           . esc_html("metodo allegati: Pods, count=" . count($allegati))
           . '</small>';
        // Fallback attachments if empty
        if ( empty( $allegati ) ) {
            $fb_doc_id = $doc_id;
            $fb_doc_id = apply_filters('wpml_object_id', $fb_doc_id, 'doc_plus', true, $default_lang)
                         ?: $fb_doc_id;
            $all_att = get_post_meta($fb_doc_id, 'doc_plus_allegati', false);
            $allegati = [];
            foreach ( $all_att as $entry2 ) {
                if ( ! is_array($entry2) ) {
                    $un2 = @unserialize($entry2);
                    if ( is_array($un2) ) {
                        foreach ( $un2 as $id_val2 ) {
                            $allegati[] = ['ID' => intval($id_val2)];
                        }
                        continue;
                    }
                    if ( is_numeric($entry2) ) {
                        $allegati[] = ['ID' => intval($entry2)];
                        continue;
                    }
                } elseif ( isset($entry2['ID']) ) {
                    $allegati[] = ['ID' => intval($entry2['ID'])];
                }
            }
            echo '<small class="d-block text-center text-muted mb-2">'
               . esc_html("doc_plus_debug: fallback raw allegati da doc_plus {$fb_doc_id}, elementi=" . count($allegati))
               . '</small>';
        }

        if ( empty( $allegati ) ) {
            echo '<small class="d-block text-muted">' . esc_html("nessun allegato per doc_plus {$pod->ID()}") . '</small>';
            continue;
        }

        // Display attachments
        foreach ( $allegati as $att ) {
            $pdf_id = intval($att['ID']);
            $pod_pdf = pods('documenti_prodotto', $pdf_id, ['lang' => $current_lang]);
            if ( ! $pod_pdf->exists() ) {
                $pdf_fallback = apply_filters('wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang)
                                ?: $pdf_id;
                $pod_pdf = pods('documenti_prodotto', $pdf_fallback, ['lang' => $default_lang]);
            }
            $pdf_title = get_the_title($pod_pdf->ID());
            $langs = $pod_pdf->field('lingua_aggiuntiva');
            if ( ! empty($langs) ) {
                $t = $langs[0]; $slug = $t['slug']; $name = $t['name'];
            } else { $slug = $name = 'n.d.'; }
            echo '<small class="d-block">'
               . esc_html("ALLEGATO PDF_ID={$pod_pdf->ID()} titolo_pdf=\"{$pdf_title}\" lingua_aggiuntiva={$slug}:{$name}")
               . '</small>';
        }
    }

    // Close card
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
