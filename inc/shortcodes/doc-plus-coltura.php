<?php
/**
 * Shortcode [doc_plus_coltura]
 *
 * Rende i documenti doc_plus collegati al termine corrente di tassonomia coltura.
 * Riusa il mapping reale di [doc_plus] per cover, allegati e fallback WPML.
 */

if ( ! function_exists( 'doc_plus_coltura_shortcode' ) ) {
    function doc_plus_coltura_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'layout'  => 'card-imgsup',
            'title'   => '',
            'griglia' => '',
        ), $atts, 'doc_plus_coltura' );

        if ( ! is_tax( 'coltura' ) ) {
            return '';
        }

        $term = get_queried_object();
        if ( ! $term || empty( $term->term_id ) ) {
            return '';
        }

        $current_lang = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : apply_filters( 'wpml_current_language', null );
        $default_lang = apply_filters( 'wpml_default_language', null );

        $term_id_current = apply_filters( 'wpml_object_id', $term->term_id, 'coltura', true, $current_lang ) ?: $term->term_id;
        $pod_term        = pods( 'coltura', $term_id_current, array( 'lang' => $current_lang ) );
        $items           = ( $pod_term && $pod_term->exists() ) ? $pod_term->field( 'doc_plus_coltura' ) : array();

        if ( ! is_array( $items ) ) {
            $items = array();
        }

        if ( empty( $items ) ) {
            $term_id_def = apply_filters( 'wpml_object_id', $term->term_id, 'coltura', true, $default_lang ) ?: $term->term_id;
            foreach ( (array) get_term_meta( $term_id_def, 'doc_plus_coltura', false ) as $raw ) {
                $items[] = $raw;
            }
        }

        $related = array();
        foreach ( (array) $items as $raw ) {
            $id = is_array( $raw ) && isset( $raw['ID'] ) ? intval( $raw['ID'] ) :
                ( is_object( $raw ) && isset( $raw->ID ) ? intval( $raw->ID ) : intval( $raw ) );

            if ( $id ) {
                $related[] = $id;
            }
        }

        $related = array_values( array_unique( $related ) );

        if ( empty( $related ) ) {
            return '';
        }

        $data = array();
        foreach ( $related as $doc_id ) {
            $pod = pods( 'doc_plus', $doc_id, array( 'lang' => $current_lang ) );

            if ( ! $pod || ! method_exists( $pod, 'exists' ) || ! $pod->exists() ) {
                $fb  = apply_filters( 'wpml_object_id', $doc_id, 'doc_plus', true, $default_lang ) ?: $doc_id;
                $pod = pods( 'doc_plus', $fb, array( 'lang' => $default_lang ) );
            }

            if ( ! $pod || ! method_exists( $pod, 'exists' ) || ! $pod->exists() ) {
                continue;
            }

            $cover_id  = $pod->field( 'doc_plus_cover.ID' );
            $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';

            $raw_meta   = get_post_meta( $pod->ID(), 'doc_plus_allegati', false );
            $attach_ids = array();
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

            $attachments = array();
            foreach ( $attach_ids as $pdf_id ) {
                $pp = pods( 'documenti_prodotto', $pdf_id, array( 'lang' => $current_lang ) );

                if ( ! $pp || ! method_exists( $pp, 'exists' ) || ! $pp->exists() ) {
                    $fbp = apply_filters( 'wpml_object_id', $pdf_id, 'documenti_prodotto', true, $default_lang ) ?: $pdf_id;
                    $pp  = pods( 'documenti_prodotto', $fbp, array( 'lang' => $default_lang ) );
                }

                if ( ! $pp || ! method_exists( $pp, 'exists' ) || ! $pp->exists() ) {
                    continue;
                }

                $file_id   = $pp->field( 'documento-prodotto.ID' );
                $file_url  = $file_id ? wp_get_attachment_url( $file_id ) : '';
                $langs     = $pp->field( 'lingua_aggiuntiva' );
                $lang_slug = ! empty( $langs ) ? $langs[0]['slug'] : '';
                $lang_name = ! empty( $langs ) ? $langs[0]['name'] : '';
                $flag_html = function_exists( 'toroag_get_flag_html' ) ? toroag_get_flag_html( $lang_slug ) : '';

                $attachments[] = array(
                    'id'    => $pp->ID(),
                    'title' => get_the_title( $pp->ID() ),
                    'url'   => $file_url,
                    'lang'  => array(
                        'slug' => $lang_slug,
                        'name' => $lang_name,
                    ),
                    'flag'  => $flag_html,
                );
            }

            $data[] = array(
                'id'          => $pod->ID(),
                'title'       => get_the_title( $pod->ID() ),
                'cover_id'    => $cover_id,
                'cover_url'   => $cover_url,
                'attachments' => $attachments,
            );
        }

        if ( empty( $data ) ) {
            return '';
        }

        return toroag_load_view( 'doc-plus-view', array(
            'doc_plus_data' => $data,
            'layout'        => $atts['layout'],
            'title'         => $atts['title'],
            'griglia'       => $atts['griglia'],
        ) );
    }
}

add_shortcode( 'doc_plus_coltura', 'doc_plus_coltura_shortcode' );
