<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card con filtro ID opzionale
 * Recupera sempre tutti i doc_plus collegati dalla pagina corrente,
 * poi se 'ids' è passato filtra quali elementi mostrare.
 * In Italiano mostra solo allegati con lingua aggiuntiva 'italiano',
 * in Inglese mostra solo allegati con lingua diversa da 'italiano'.
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Parsing attributi
    $atts = shortcode_atts( [ 'ids' => '' ], $atts, 'doc_plus' );
    $filter_ids = [];
    if ( ! empty( $atts['ids'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids'] ) as $v ) {
            $i = intval( $v );
            if ( $i > 0 ) $filter_ids[] = $i;
        }
    }
    ob_start();

    // 2) Lingua corrente
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 3) Recupero IDs doc_plus dal raw meta
    $page_id  = get_the_ID();
    $raw = get_post_meta( $page_id, 'doc_plus_inpage', false );
    $related = [];
    foreach ( $raw as $e ) {
        if ( is_array($e) && isset($e['ID']) ) {
            $related[] = ['ID'=>intval($e['ID'])];
        } elseif ( is_string($e) ) {
            $u = @unserialize($e);
            if ( is_array($u) ) {
                foreach ( $u as $id ) if ( is_numeric($id) ) $related[] = ['ID'=>intval($id)];
            } elseif ( is_numeric($e) ) {
                $related[] = ['ID'=>intval($e)];
            }
        }
    }
    echo '<small class="d-block text-center text-info">doc_plus_debug: lang=' . esc_html($current_lang) . '</small>';

    if ( empty($related) ) {
        echo '<small class="d-block text-danger">doc_plus_debug: nessun doc_plus collegato</small>';
        return ob_get_clean();
    }
    // filtro ID pagina
    echo '<small class="d-block text-center text-muted">trovati ' . count($related) . ' doc_plus' .
         ( empty($filter_ids) ? '' : ', filtro IDs=' . implode(',', $filter_ids) ) . '</small>';

    echo '<div class="d-flex justify-content-center my-4">'
       . '<div class="card w-75 shadow-sm"><div class="card-body">';

    // Loop doc_plus
    foreach ( $related as $r ) {
        $doc_id = intval($r['ID']);
        if ( ! empty($filter_ids) && ! in_array($doc_id, $filter_ids, true) ) {
            continue;
        }
        echo '<small class="d-block text-success">doc_plus_debug: processing doc_id=' . esc_html($doc_id) . '</small>';

        // Carica pod current lang con fallback
        $pod = pods('doc_plus', $doc_id, ['lang'=>$current_lang]);
        if ( ! $pod->exists() ) {
            $fb = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            $pod = pods('doc_plus', $fb, ['lang'=>$default_lang]);
        }
        // Titolo & Cover
        echo '<small class="d-block">DOC ID=' . $pod->ID() . ' titolo="' . esc_html( get_the_title($pod->ID()) ) . '"</small>';
        $cover_id = $pod->field('doc_plus_cover.ID');
        $cover_url = $cover_id ? wp_get_attachment_url($cover_id) : '';
        echo '<small class="d-block">cover_id=' . esc_html($cover_id) . '</small>';
        echo '<small class="d-block text-muted">cover_url=' . esc_html($cover_url) . '</small>';

        // Allegati raw meta
        $raw_att = get_post_meta($pod->ID(), 'doc_plus_allegati', false);
        $ids = [];
        foreach($raw_att as $e) {
            if(is_array($e)&&isset($e['ID'])) $ids[] = intval($e['ID']);
            elseif(is_string($e)){
                $u = @unserialize($e);
                if(is_array($u)) foreach($u as $v) if(is_numeric($v)) $ids[] = intval($v);
                elseif(is_numeric($e)) $ids[] = intval($e);
            }
        }
        echo '<small class="d-block text-primary">doc_plus_debug: raw allegati IDs=' . implode(',', $ids) . '</small>';

        // Elaborazione allegati con filtro lingua aggiuntiva e recupero bandierina
        foreach ( $ids as $pdf_id ) {
            // Carica CPT documento
            $pp = pods( 'documenti_prodotto', $pdf_id, ['lang' => $current_lang] );
            if ( ! $pp->exists() ) {
                $fbp = apply_filters( 'wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang ) ?: $pdf_id;
                $pp = pods( 'documenti_prodotto', $fbp, ['lang' => $default_lang] );
            }
            // Recupera lingua aggiuntiva
            $langs = $pp->field( 'lingua_aggiuntiva' );
            $slug  = ! empty( $langs ) ? $langs[0]['slug'] : '';
            $name  = ! empty( $langs ) ? $langs[0]['name'] : '';

            // Filtro per lingua: IT mostra solo 'italiano', EN mostra tutti tranne 'italiano'
            if ( 'it' === $current_lang ) {
                if ( 'italiano' !== $slug ) {
                    continue;
                }
            } else {
                if ( 'italiano' === $slug ) {
                    continue;
                }
            }

            // Recupera HTML bandiera
            $flag_html = function_exists('toroag_get_flag_html') ? toroag_get_flag_html( $slug ) : '';

            // Mostra dati allegato
            $title   = get_the_title( $pp->ID() );
            $file_id = $pp->field( 'documento-prodotto.ID' );
            $url     = $file_id ? wp_get_attachment_url( $file_id ) : '';
            echo '<small class="d-block">PDF ID=' . esc_html( $pp->ID() )
               . ' tit=' . esc_html( $title )
               . ' url=' . esc_html( $url )
               . ' lingua=' . esc_html( $slug . ':' . $name )
               . ' ' . $flag_html
               . '</small>';
        }
        echo '<hr/>';
    }

    echo '</div></div></div>';
    return ob_get_clean();
}
add_shortcode('doc_plus','doc_plus_debug_shortcode');
