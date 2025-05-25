<?php
/**
 * Shortcode: [video_tipo_prodotto_v2]
 * Recupera i video collegati via Pods (relationship pick) al campo 'tipo-video'
 * sui termini di tipo_di_prodotto associati al prodotto corrente.
 */
function ta_render_video_tipo_prodotto_v2_shortcode( $atts ) {
    if ( ! is_singular( 'prodotto' ) ) {
        return '';
    }

    global $post;

    // Lingue WPML
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // Prendo i termini 'tipo_di_prodotto' del prodotto
    $terms = wp_get_post_terms( $post->ID, 'tipo_di_prodotto' );
    if ( is_wp_error($terms) || empty($terms) ) {
        return '';
    }

    $all_videos = [];

    foreach ( $terms as $term ) {
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

        // Carico il Pod del termine (multilingua)
        $pod_term = pods( 'tipo_di_prodotto', $term_id, ['lang' => $current_lang] );
        if ( ! $pod_term->exists() ) {
            continue;
        }

        // Campo Pods 'tipo-video'
        $videos = $pod_term->field( 'tipo-video' );
        if ( empty( $videos ) ) {
            // fallback sulla lingua di default
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

            // Attenzione: per i termini si usa get_term_meta
            $videos = array_map(
                'intval',
                get_term_meta( $term_id_default, 'tipo-video', false )
            );

            if ( empty( $videos ) ) {
                continue;
            }
        }

        // Raccogliamo tutti gli ID (gestione array/object)
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
    if ( empty( $all_videos ) ) {
        return '';
    }

    // Inizio output con griglia Bootstrap 5
    $output  = '<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';

    foreach ( $all_videos as $video_id ) {
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
            continue;
        }

        // Filtro per lingua aggiuntiva (stessa logica di video_prodotto_v2)
        $lingua_slugs = wp_get_post_terms(
            $vid_id,
            'lingua_aggiuntiva',
            ['fields' => 'slugs']
        );
        $first_lingua = $lingua_slugs[0] ?? '';
        if (
            ( $current_lang === 'it' && $first_lingua !== 'italiano' )
            || ( $current_lang !== 'it' && $first_lingua === 'italiano' )
        ) {
            continue;
        }

        $video_link   = get_post_meta( $vid_id, 'video_link', true );
        $youtube_embed = str_replace(
            'https://youtu.be/',
            'https://www.youtube.com/embed/',
            $video_link
        );

        // Flag per lingue diverse
        $flag_html = '';
        if ( $current_lang !== 'it' && function_exists('toroag_get_flag_html') ) {
            $flag_html = toroag_get_flag_html( $first_lingua );
        }

        // Markup
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
