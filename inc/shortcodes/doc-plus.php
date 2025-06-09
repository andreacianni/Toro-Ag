<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card with external view template
 *
 * Esempi d'uso:
 * [doc_plus ids="123,456" layout="grid"]  // Layout a griglia
 * [doc_plus ids="789" layout="carousel"] // Layout carosello
 * [doc_plus layout="list"]               // Tutti i documenti, con layout list
 *
 * Recupera tutti i doc_plus collegati e passa i dati al template doc-plus-view.php
 * Esteso con parametro "layout" per indicare quale layout usare nella view
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Parsing attributi (con nuovo parametro "layout")
    $atts = shortcode_atts( array(
        'ids'    => '',
        'layout' => 'default',
    ), $atts, 'doc_plus' );

    $filter_ids = [];
    if ( ! empty( $atts['ids'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids'] ) as $v ) {
            if ( $i = intval( $v ) ) {
                $filter_ids[] = $i;
            }
        }
    }

    // 2) Lingua corrente e default
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 3) Recupero raw meta doc_plus_inpage
    $page_id  = get_the_ID();
    $raw_list = get_post_meta( $page_id, 'doc_plus_inpage', false );
    $related = [];
    foreach ( $raw_list as $entry ) {
        if ( is_array( $entry ) && isset( $entry['ID'] ) ) {
            $related[] = intval( $entry['ID'] );
        } elseif ( is_string( $entry ) ) {
            $u = @unserialize( $entry );
            if ( is_array( $u ) ) {
                foreach ( $u as $id ) {
                    if ( is_numeric( $id ) ) {
                        $related[] = intval( $id );
                    }
                }
            } elseif ( is_numeric( $entry ) ) {
                $related[] = intval( $entry );
            }
        }
    }

    // 4) Apply filter_ids if provided
    if ( ! empty( $filter_ids ) ) {
        $related = array_intersect( $related, $filter_ids );
    }

    // 5) Build data array
    $data = [];
    foreach ( $related as $doc_id ) {
        $pod = pods( 'doc_plus', $doc_id, array( 'lang' => $current_lang ) );
        if ( ! $pod->exists() ) {
            $fb = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            $pod = pods( 'doc_plus', $fb, array( 'lang' => $default_lang ) );
        }
        $cover_id  = $pod->field('doc_plus_cover.ID');
        $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';
        echo '<!-- Debug: Processing doc_plus ID ' . $pod->ID() . ' with cover ID ' . $cover_id . ' -->';

        // attachments raw meta
        $raw_meta = get_post_meta( $pod->ID(), 'doc_plus_allegati', false );
        $attach_ids = [];
        foreach ( $raw_meta as $e ) {
            if ( is_array( $e ) && isset( $e['ID'] ) ) {
                $attach_ids[] = intval( $e['ID'] );
            } elseif ( is_string( $e ) ) {
                $u2 = @unserialize( $e );
                if ( is_array( $u2 ) ) {
                    foreach ( $u2 as $v ) {
                        if ( is_numeric( $v ) ) {
                            $attach_ids[] = intval( $v );
                        }
                    }
                } elseif ( is_numeric( $e ) ) {
                    $attach_ids[] = intval( $e );
                }
            }
        }

        $attachments = [];
        foreach ( $attach_ids as $pdf_id ) {
            $pp = pods('documenti_prodotto', $pdf_id, ['lang' => $current_lang]);
            if ( ! $pp->exists() ) {
                $fbp = apply_filters('wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang) ?: $pdf_id;
                $pp = pods('documenti_prodotto', $fbp, ['lang' => $default_lang]);
            }
            $file_id   = $pp->field('documento-prodotto.ID');
            $file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';
            $langs     = $pp->field('lingua_aggiuntiva');
            $lang_slug = ! empty( $langs ) ? $langs[0]['slug'] : '';
            $lang_name = ! empty( $langs ) ? $langs[0]['name'] : '';
            $flag_html = function_exists('toroag_get_flag_html') ? toroag_get_flag_html( $lang_slug ) : '';

            $attachments[] = [
                'id'       => $pp->ID(),
                'title'    => get_the_title( $pp->ID() ),
                'url'      => $file_url,
                'lang'     => [ 'slug' => $lang_slug, 'name' => $lang_name ],
                'flag'     => $flag_html,
            ];
        }

        $data[] = [
            'id'          => $pod->ID(),
            'title'       => get_the_title( $pod->ID() ),
            'cover_id'    => $cover_id,
            'cover_url'   => $cover_url,
            'attachments' => $attachments,
        ];
    }

    // 6) Include view template – solo se ho dati
    if ( ! empty( $data ) ) {
        // usa il nostro helper per caricare la view, passarle i dati e layout
        return toroag_load_view( 'doc-plus-view', [
            'doc_plus_data' => $data,
            'layout'        => $atts['layout'],
        ] );
    }

    // 7) Fallback: simple var_export
    return '';
}
add_shortcode( 'doc_plus', 'doc_plus_debug_shortcode' );
