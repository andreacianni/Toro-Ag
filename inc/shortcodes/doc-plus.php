<?php
/**
 * Shortcode [doc_plus] – debug Bootstrap card with external view template
 *
 * Esempi d'uso:
 * [doc_plus ids="123,456" layout="grid" title="My Title" columns="2"]  // Layout a griglia, 2 colonne, titolo personalizzato
 * [doc_plus ids="789" layout="carousel" title="Documenti" columns="1"] // Layout carosello, 1 colonna, titolo "Documenti"
 * [doc_plus layout="list" title="" columns="3"]                       // Tutti i documenti, layout list, 3 colonne, senza titolo
 * [doc_plus]                                                         // Nessun parametro: layout default, nessun titolo, 3 colonne (default)
 *
 * Recupera tutti i doc_plus collegati e passa i dati al template doc-plus-view.php
 * Esteso con parametri "layout", "title" e "columns" per indicare layout, titolo e numero di colonne
 */
function doc_plus_debug_shortcode( $atts ) {
    // 1) Parsing attributi (con nuovo parametro "layout")
    $atts = shortcode_atts( array(
        'ids'    => '',
        'slugs'   => '', 
        'layout' => 'default',
        'title'   => '',
        'griglia' => '',
    ), $atts, 'doc_plus' );

    $filter_ids = [];
    if ( ! empty( $atts['ids'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['ids'] ) as $v ) {
            if ( $i = intval( $v ) ) {
                $filter_ids[] = $i;
            }
        }
    }
    if ( ! empty( $atts['slugs'] ) ) {
        foreach ( preg_split('/\s*,\s*/', $atts['slugs'] ) as $slug ) {
            $slug = trim( $slug );
            if ( ! empty( $slug ) ) {
                // Cerca il post tramite slug
                $post = get_page_by_path( $slug, OBJECT, 'doc_plus' );
                if ( $post ) {
                    $filter_ids[] = $post->ID;
                }
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
        echo '<!-- debug.'.$pod->ID().' -->';
        if ( ! $pod->exists() ) {
            $fb = apply_filters('wpml_object_id', $doc_id, 'doc_plus', true, $default_lang) ?: $doc_id;
            $pod = pods( 'doc_plus', $fb, array( 'lang' => $default_lang ) );
        }
        $cover_id  = $pod->field('doc_plus_cover.ID');
        $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';
        // echo '<!-- Debug: Processing doc_plus ID ' . $pod->ID() . ' with cover ID ' . $cover_id . ' -->';

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
            'title'         => $atts['title'],
            'griglia'          => $atts['griglia'],
        ] );
    }

    // 7) Fallback: simple var_export
    return '';
}
add_shortcode( 'doc_plus', 'doc_plus_debug_shortcode' );

/**
 * Shortcode [documenti_pagina] – per documenti associati alle pagine
 * Layout compatto come lista semplice
 * 
 * Esempi d'uso:
 * [documenti_pagina title="Download"]
 * [documenti_pagina title="Documenti tecnici"]
 * [documenti_pagina] // Senza titolo
 */
function documenti_pagina_shortcode( $atts ) {
    // 1) Parsing attributi
    $atts = shortcode_atts( array(
        'title'  => '',
        'layout' => 'list', // Solo layout lista per ora
    ), $atts, 'documenti_pagina' );

    if ( ! is_page() ) {
        return '';
    }

    // 2) Lingua corrente e default
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // 3) Recupero documenti dal campo documenti_pagina
    $page_id = get_the_ID();
    $raw_list = get_post_meta( $page_id, 'documenti_pagina', false );
    
    // Fallback WPML se campo vuoto
    if ( empty( $raw_list ) && function_exists('icl_object_id') ) {
        $original_page_id = apply_filters('wpml_object_id', $page_id, 'page', false, $default_lang);
        if ( $original_page_id && $original_page_id != $page_id ) {
            $raw_list = get_post_meta( $original_page_id, 'documenti_pagina', false );
        }
    }

    $related = [];
    foreach ( $raw_list as $entry ) {
        if ( is_numeric( $entry ) ) {
            $related[] = intval( $entry );
        }
    }

    if ( empty( $related ) ) {
        return '';
    }

    // 4) Build data array
    $data = [];
    foreach ( $related as $doc_id ) {
        $pod = pods( 'documenti_prodotto', $doc_id, array( 'lang' => $current_lang ) );
        
        if ( ! $pod->exists() ) {
            $fb = apply_filters('wpml_object_id', $doc_id, 'documenti_prodotto', true, $default_lang) ?: $doc_id;
            $pod = pods( 'documenti_prodotto', $fb, array( 'lang' => $default_lang ) );
        }

        if ( ! $pod->exists() ) {
            continue;
        }

        $file_id = $pod->field('documento-prodotto.ID');
        $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';
        $langs = $pod->field('lingua_aggiuntiva');
        $lang_slug = ! empty( $langs ) ? $langs[0]['slug'] : '';
        $lang_name = ! empty( $langs ) ? $langs[0]['name'] : '';
        $flag_html = function_exists('toroag_get_flag_html') ? toroag_get_flag_html( $lang_slug ) : '';

        if ( ! empty( $file_url ) ) {
            $data[] = [
                'id'          => $pod->ID(),
                'title'       => get_the_title( $pod->ID() ),
                'cover_id'    => null,
                'cover_url'   => '',
                'attachments' => [[
                    'id'       => $pod->ID(),
                    'title'    => get_the_title( $pod->ID() ),
                    'url'      => $file_url,
                    'lang'     => [ 'slug' => $lang_slug, 'name' => $lang_name ],
                    'flag'     => $flag_html,
                ]]
            ];
        }
    }

    // 5) Include view template compatto
    if ( ! empty( $data ) ) {
        return toroag_load_view( 'documenti-pagina-view', [
            'doc_plus_data' => $data,
            'title'         => $atts['title'],
        ] );
    }

    return '';
}
add_shortcode( 'documenti_pagina', 'documenti_pagina_shortcode' );
