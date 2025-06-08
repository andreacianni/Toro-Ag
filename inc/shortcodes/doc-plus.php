<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con filtro ID
 * Recupera sempre tutti i doc_plus collegati, quindi se 'ids' è passato filtra i risultati.
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

    // Build full related list from page
    $page_id  = get_the_ID();
    $page_pod = pods('page', $page_id, ['lang' => $current_lang]);
    $related  = (array) $page_pod->field('doc_plus_inpage');
    if ( empty($related) ) {
        // Fallback relation raw meta
        $fallback_page_id = apply_filters('wpml_object_id', $page_id, 'page', true, $default_lang) ?: $page_id;
        foreach ( get_post_meta($fallback_page_id, 'doc_plus_inpage', false) as $entry ) {
            if ( ! is_array($entry) ) {
                $un = @unserialize($entry);
                if ( is_array($un) ) {
                    foreach ($un as $id_val) {
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
    }

    // Parse filter IDs if provided
    $filter_ids = [];
    if ( ! empty($atts['ids']) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids']) as $v ) {
            $n = intval($v);
            if ( $n > 0 ) {
                $filter_ids[] = $n;
            }
        }
    }

    // Apply filtering
    if ( ! empty($filter_ids) ) {
        $related = array_filter($related, function($item) use ($filter_ids) {
            return in_array(intval($item['ID']), $filter_ids, true);
        });
    }

    // Card container
    echo '<div class="d-flex justify-content-center my-4">'
       . '<div class="card shadow-sm" style="max-width:600px; width:100%;">'
       . '<div class="card-header text-center">'
       . esc_html("doc_plus_debug: lang={$current_lang}" . (!empty($filter_ids) ? ", filtro IDs=".implode(',', $filter_ids) : ""))
       . '</div><div class="card-body p-3">';

    // No related
    if ( empty($related) ) {
        echo '<small class="d-block text-center text-muted">'
           . esc_html("doc_plus_debug: nessun doc_plus da mostrare")
           . '</small></div></div></div>';
        return ob_get_clean();
    }

    // Count
    echo '<small class="d-block mb-2 text-center text-muted">'
       . esc_html("trovati " . count($related) . " doc_plus")
       . '</small>';

    // Loop items
    foreach ( $related as $rel ) {
        $doc_id = intval($rel['ID']);
        // Load doc_plus pod with fallback
        $pod = pods('doc_plus', $doc_id, ['lang' => $current_lang]);
        if ( ! $pod->exists() ) {
            $fallback_doc = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            $pod = pods('doc_plus', $fallback_doc, ['lang' => $default_lang]);
        }
        // Title
        echo '<small class="d-block">'
           . esc_html("DOC ID={$pod->ID()} titolo=\"".get_the_title($pod->ID())."\"")
           . '</small>';
        // Cover ID
        echo '<small class="d-block">'
           . esc_html("cover_id=".$pod->field('doc_plus_cover.ID'))
           . '</small>';
        // Attachments
        $atts_list = (array) $pod->field('doc_plus_allegati');
        if ( empty($atts_list) ) {
            // fallback raw meta
            $raw = get_post_meta($pod->ID(), 'doc_plus_allegati', false);
            foreach ( $raw as $e ) {
                if ( ! is_array($e) ) {
                    $u = @unserialize($e);
                    if ( is_array($u) ) {
                        foreach ($u as $idv) {
                            $atts_list[] = ['ID'=>intval($idv)];
                        }
                        continue;
                    }
                    if ( is_numeric($e) ) {
                        $atts_list[] = ['ID'=>intval($e)];
                        continue;
                    }
                } elseif(isset($e['ID'])){
                    $atts_list[]=['ID'=>intval($e['ID'])];
                }
            }
        }
        echo '<small class="d-block text-center text-muted mb-1">'
           . esc_html("allegati count=".count($atts_list))
           . '</small>';
        foreach ( $atts_list as $a ) {
            $aid = intval($a['ID']);
            $pp = pods('documenti_prodotto',$aid,['lang'=>$current_lang]);
            if(!$pp->exists()){
                $fb = apply_filters('wpml_object_id',$aid,'documenti_prodotto',true,$default_lang)?:$aid;
                $pp = pods('documenti_prodotto',$fb,['lang'=>$default_lang]);
            }
            echo '<small class="d-block">'
               . esc_html("PDF ID={$pp->ID()} tit=".get_the_title($pp->ID()))
               . '</small>';
        }
    }

    // Close card
    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus', 'doc_plus_debug_shortcode');
