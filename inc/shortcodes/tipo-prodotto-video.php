<?php
/**
 * Shortcode: [video_tipo_prodotto_v2]
 * Recupera i video collegati via Pods (relationship pick) al campo 'tipo-video'
 * sul termine corrente di tipo_di_prodotto, con debug HTML (alpha 2).
 */
function ta_render_video_tipo_prodotto_v2_shortcode( $atts ) {
    // Debug alpha 2: funzione avviata in contesto tassonomia

    // Verifica contesto tassonomia tipo_di_prodotto
    $term = get_queried_object();
    if ( ! $term || ! isset( $term->term_id ) || $term->taxonomy !== 'tipo_di_prodotto' ) {
        return '';
    }

    // Lingue WPML
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // Recupero lista video dal campo Pods
    $pod_term = pods('tipo_di_prodotto', $term->term_id, ['lang' => $current_lang]);
    $videos = $pod_term->exists() ? $pod_term->field('tipo-video') : [];
    if ( empty($videos) ) {
        $term_id_default = apply_filters('wpml_object_id', $term->term_id, 'tipo_di_prodotto', true, $default_lang);
        $videos = $term_id_default ? get_term_meta($term_id_default, 'tipo-video', false) : [];
    }

    // Stack verticale video (come prodotti)
    $output = '<div class="toro-layout-videos-section"><div class="toro-video-stack">';

    foreach ( (array)$videos as $item ) {
        $video_id = is_array($item)&&isset($item['ID']) ? intval($item['ID']) : (is_object($item)&&isset($item->ID) ? intval($item->ID) : intval($item));
        $translated = apply_filters('wpml_object_id', $video_id, 'video', true, $current_lang);
        $video_id = $translated ? intval($translated) : $video_id;

        $video = get_post($video_id);
        if ( ! $video ) continue;

        $lingua_terms = wp_get_post_terms($video_id, 'lingua_aggiuntiva', ['fields' => 'slugs']);
        $first_lingua = $lingua_terms[0] ?? '';
        if ( ($current_lang === 'it' && $first_lingua !== 'italiano') || ($current_lang !== 'it' && $first_lingua === 'italiano') ) continue;

        $video_link = get_post_meta($video_id, 'video_link', true);
        $youtube_embed = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/', $video_link);

        $flag_html = ($current_lang !== 'it' && function_exists('toroag_get_flag_html')) ? toroag_get_flag_html($first_lingua) : '';

        // Debug per ogni video

        // Stack verticale con card come documenti
        $output .= '<div class="card shadow-sm mb-4">';
        $output .= '<div class="card-header">';
        $output .= '<a href="' . esc_url($video_link) . '" target="_blank" rel="noopener noreferrer" class="fw-bold">' . esc_html($video->post_title) . ' ' . $flag_html . '</a>';
        $output .= '</div>';
        $output .= '<div class="card-body p-0">';
        $output .= '<div class="fluid-width-video-wrapper" style="padding-top: 56.25%;">';
        $output .= '<iframe title="' . esc_attr($video->post_title) . '" src="' . esc_url($youtube_embed) . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';
        $output .= '</div></div></div>';
    }

    $output .= '</div></div>'; // toro-video-stack + toro-layout-videos-section
    return $output;
}
add_shortcode('video_tipo_prodotto_v2', 'ta_render_video_tipo_prodotto_v2_shortcode');

/**
 * Shortcode: [video_tipo_prodotto_standalone columns="3"]
 * Mostra i video del tipo di prodotto corrente in una griglia Bootstrap.
 */
function ta_render_video_tipo_prodotto_standalone_shortcode( $atts ) {
    $atts = shortcode_atts(
        [
            'columns' => 3,
        ],
        $atts,
        'video_tipo_prodotto_standalone'
    );

    $columns = absint( $atts['columns'] );
    if ( ! in_array( $columns, [ 1, 2, 3 ], true ) ) {
        $columns = 3;
    }

    // Verifica contesto tassonomia tipo_di_prodotto
    $term = get_queried_object();
    if ( ! $term || ! isset( $term->term_id ) || $term->taxonomy !== 'tipo_di_prodotto' ) {
        return '';
    }

    // Lingue WPML
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // Recupero lista video dal campo Pods
    $pod_term = pods('tipo_di_prodotto', $term->term_id, ['lang' => $current_lang]);
    $videos = $pod_term->exists() ? $pod_term->field('tipo-video') : [];
    if ( empty($videos) ) {
        $term_id_default = apply_filters('wpml_object_id', $term->term_id, 'tipo_di_prodotto', true, $default_lang);
        $videos = $term_id_default ? get_term_meta($term_id_default, 'tipo-video', false) : [];
    }

    $normalize_youtube_embed_url = static function ( $video_url ) {
        $parts = wp_parse_url( $video_url );
        if ( ! is_array( $parts ) || empty( $parts['host'] ) ) {
            return '';
        }

        $host = strtolower( preg_replace( '/^www\./', '', $parts['host'] ) );
        $path = trim( $parts['path'] ?? '', '/' );
        $query = [];
        parse_str( $parts['query'] ?? '', $query );
        $video_id = '';

        if ( 'youtu.be' === $host ) {
            $video_id = strtok( $path, '/' );
        } elseif ( in_array( $host, [ 'youtube.com', 'm.youtube.com' ], true ) && 'watch' === $path ) {
            $video_id = isset( $query['v'] ) && is_scalar( $query['v'] ) ? $query['v'] : '';
        } elseif ( in_array( $host, [ 'youtube.com', 'm.youtube.com' ], true ) && 0 === strpos( $path, 'embed/' ) ) {
            $video_id = strtok( substr( $path, strlen( 'embed/' ) ), '/' );
        }

        $video_id = is_scalar( $video_id ) ? (string) $video_id : '';
        if ( ! preg_match( '/^[A-Za-z0-9_-]{11}$/', $video_id ) ) {
            return '';
        }

        $allowed_params = [
            'autoplay', 'cc_lang_pref', 'cc_load_policy', 'controls', 'disablekb',
            'enablejsapi', 'end', 'fs', 'iv_load_policy', 'loop', 'modestbranding',
            'mute', 'origin', 'playsinline', 'playlist', 'rel', 'start',
        ];
        $embed_params = [];
        foreach ( $allowed_params as $param ) {
            if ( isset( $query[ $param ] ) && is_scalar( $query[ $param ] ) ) {
                $embed_params[ $param ] = sanitize_text_field( (string) $query[ $param ] );
            }
        }

        $embed_url = 'https://www.youtube.com/embed/' . $video_id;
        return empty( $embed_params ) ? $embed_url : $embed_url . '?' . http_build_query( $embed_params, '', '&', PHP_QUERY_RFC3986 );
    };

    $cards = [];

    foreach ( (array)$videos as $item ) {
        $video_id = is_array($item)&&isset($item['ID']) ? intval($item['ID']) : (is_object($item)&&isset($item->ID) ? intval($item->ID) : intval($item));
        $translated = apply_filters('wpml_object_id', $video_id, 'video', true, $current_lang);
        $video_id = $translated ? intval($translated) : $video_id;

        $video = get_post($video_id);
        if ( ! $video ) continue;

        $lingua_terms = wp_get_post_terms($video_id, 'lingua_aggiuntiva', ['fields' => 'slugs']);
        $first_lingua = $lingua_terms[0] ?? '';
        if ( ($current_lang === 'it' && $first_lingua !== 'italiano') || ($current_lang !== 'it' && $first_lingua === 'italiano') ) continue;

        $video_link = get_post_meta($video_id, 'video_link', true);
        $youtube_embed = $normalize_youtube_embed_url( $video_link );

        $flag_html = ($current_lang !== 'it' && function_exists('toroag_get_flag_html')) ? toroag_get_flag_html($first_lingua) : '';

        $card = '<div class="col">';
        $card .= '<div class="card h-100 shadow-sm">';
        $card .= '<div class="card-header flex-grow-1 d-flex align-items-center">';
        $card .= '<a href="' . esc_url($video_link) . '" target="_blank" rel="noopener noreferrer" class="fw-bold">' . esc_html($video->post_title) . ' ' . $flag_html . '</a>';
        $card .= '</div>';
        if ( $youtube_embed ) {
            $card .= '<div class="card-body p-0 flex-grow-0">';
            $card .= '<iframe title="' . esc_attr($video->post_title) . '" src="' . esc_url($youtube_embed) . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';
            $card .= '</div>';
        }
        $card .= '</div></div>';

        $cards[] = $card;
    }

    if ( empty( $cards ) ) {
        return '';
    }

    $grid_classes = [
        1 => 'row row-cols-1 g-4',
        2 => 'row row-cols-1 row-cols-md-2 g-4',
        3 => 'row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4',
    ];

    // Con il default, un solo video mantiene la larghezza di una colonna su due anche da lg.
    if ( 3 === $columns && 1 === count( $cards ) ) {
        $columns = 2;
    }

    return '<div class="toro-layout-videos-section"><div class="' . esc_attr( $grid_classes[ $columns ] ) . '">' . implode( '', $cards ) . '</div></div>';
}
add_shortcode('video_tipo_prodotto_standalone', 'ta_render_video_tipo_prodotto_standalone_shortcode');
