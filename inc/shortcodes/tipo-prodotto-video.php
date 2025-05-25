<?php
/**
 * Shortcode: [video_tipo_prodotto_v2]
 * Recupera i video collegati via Pods (relationship pick) al campo 'tipo-video'
 * sul termine corrente di tipo_di_prodotto, con debug HTML (alpha1).
 */
function ta_render_video_tipo_prodotto_v2_shortcode( $atts ) {
    // Debug alpha 1: funzione avviata in contesto tassonomia
    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: funzione avviata in contesto tassonomia -->';

    // Verifica contesto tassonomia tipo_di_prodotto
    $term = get_queried_object();
    if ( ! $term || ! isset( $term->term_id ) || $term->taxonomy !== 'tipo_di_prodotto' ) {
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: contesto non tassonomia tipo_di_prodotto -->';
        return '';
    }

    $terms = [ $term ];

    // Lingue WPML
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    $all_videos = [];

    foreach ( $terms as $term ) {
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: elaboro termine ' . $term->term_id . ' (' . esc_html($term->slug) . ') -->';

        // Traduzione term WPML
        $term_id_translated = apply_filters(
            'wpml_object_id',
            $term->term_id,
            'tipo_di_prodotto',
            true,
            $current_lang
        );
        $term_id = $term_id_translated
            ? intval( $term_id_translated )
            : intval( $term->term_id );

        // Carico il Pod del termine
        $pod_term = pods( 'tipo_di_prodotto', $term_id, ['lang' => $current_lang] );
        if ( ! $pod_term->exists() ) {
            echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: pod termine non esiste per ID ' . $term_id . ' -->';
            continue;
        }

        // Campo Pods 'tipo-video'
        $videos = $pod_term->field( 'tipo-video' );
        if ( empty( $videos ) ) {
            echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: nessun video in ' . $term_id . ', fallback lingua default -->';
            $term_id_default = apply_filters(
                'wpml_object_id',
                $term->term_id,
                'tipo_di_prodotto',
                true,
                $default_lang
            );
            $term_id_default = $term_id_default
                ? intval( $term_id_default )
                : intval( $term->term_id );
            $videos = array_map(
                'intval',
                get_term_meta( $term_id_default, 'tipo-video', false )
            );
        }
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: video trovati per termine ' . $term->term_id . ': ' . (is_array($videos)? implode(',', array_map('intval',$videos)) : '') . ' -->';

        foreach ( (array) $videos as $item ) {
            if ( is_array($item) && isset($item['ID']) ) {
                $all_videos[] = intval( $item['ID'] );
            } elseif ( is_object($item) && isset($item->ID) ) {
                $all_videos[] = intval( $item->ID );
            } else {
                $all_videos[] = intval( $item );
            }
        }
    }

    $all_videos = array_unique( $all_videos );
    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: tutti video raccolti: ' . implode(',', $all_videos) . ' -->';
    if ( empty( $all_videos ) ) {
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: nessun video disponibile -->';
        return '';
    }

    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: inizio markup griglia -->';
    $output  = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';

    foreach ( $all_videos as $video_id ) {
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: rendering video ID ' . $video_id . ' -->';

        // Traduzione video WPML
        $vid_trans = apply_filters(
            'wpml_object_id',
            $video_id,
            'video',
            true,
            $current_lang
        );
        $vid_id = $vid_trans ? intval( $vid_trans ) : $video_id;

        $video = get_post( $vid_id );
        if ( ! $video ) {
            echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: post video non trovato per ID ' . $vid_id . ' -->';
            continue;
        }

        $lingua_slugs = wp_get_post_terms(
            $vid_id,
            'lingua_aggiuntiva',
            ['fields' => 'slugs']
        );
        $first_lingua = $lingua_slugs[0] ?? '';
        if (
            ( $current_lang === 'it' && $first_lingua !== 'italiano' ) ||
            ( $current_lang !== 'it' && $first_lingua === 'italiano' )
        ) {
            echo '<!-- shortcode video_tipo_prodotto_v2 alpha 1: lingua non corrispondente per video ' . $vid_id . ' -->';
            continue;
        }

        $video_link   = get_post_meta( $vid_id, 'video_link', true );
        $youtube_embed = str_replace(
            'https://youtu.be/',
            'https://www.youtube.com/embed/',
            $video_link
        );

        $flag_html = '';
        if ( $current_lang !== 'it' && function_exists('toroag_get_flag_html') ) {
            $flag_html = toroag_get_flag_html( $first_lingua );
        }

        $output .= '<div class="col">';
        $output .=   '<div class="ratio ratio-16x9">';
        $output .=     '<iframe src="' . esc_url( $youtube_embed ) . '" '
                   . 'title="' . esc_attr( $video->post_title ) . '" '
                   . 'allowfullscreen></iframe>';
        $output .=   '</div>';
        $output .=   '<h5 class="mt-2">' . esc_html( $video->post_title )
                   . ' ' . $flag_html . '</h5>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode( 'video_tipo_prodotto_v2', 'ta_render_video_tipo_prodotto_v2_shortcode' );
