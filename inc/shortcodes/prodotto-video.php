<?php
/**
 * Shortcode per mostrare i video associati al prodotto (con fallback WPML robusto).
 * Uso: [video_prodotto_v2]
 */
if ( ! function_exists('ta_render_video_prodotto_v2_shortcode') ) {
    function ta_render_video_prodotto_v2_shortcode($atts) {
        global $post;

        if ( ! function_exists('pods') ) {
            return '<!-- shortcode video_prodotto_v2 alpha 5 --><!-- DEBUG: Pods non disponibile -->';
        }

        $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default_lang = apply_filters('wpml_default_language', null);
        $output = '';

        $prod_id_current = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $current_lang);
        $prod_id_current = $prod_id_current ? intval($prod_id_current) : intval($post->ID);
        $output .= '<!-- shortcode video_prodotto_v2 alpha 5 --><!-- DEBUG: Prodotto ID lingua ' . esc_html($current_lang) . ': ' . esc_html($prod_id_current) . ' -->';

        $pod = pods('prodotto', $prod_id_current, array('lang' => $current_lang));
        if ( ! $pod->exists() ) {
            return '<!-- shortcode video_prodotto_v2 alpha 5 --><!-- DEBUG: Pod prodotto non trovato per ID ' . esc_html($prod_id_current) . ' -->';
        }

        $videos = $pod->field('video_prodotto');
        if ( empty($videos) ) {
            $prod_id_default = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $default_lang);
            $prod_id_default = $prod_id_default ? intval($prod_id_default) : intval($post->ID);
            $videos = array_map('intval', get_post_meta($prod_id_default, 'video_prodotto', false));
            if ( empty($videos) ) return '<!-- shortcode video_prodotto_v2 alpha 5 --><!-- DEBUG: Nessun video -->';
        }

        $output .= '<div class="video-card-grid row">';

        foreach ( (array) $videos as $item ) {
            $video_id = is_array($item) && isset($item['ID']) ? intval($item['ID']) : (is_object($item) && isset($item->ID) ? intval($item->ID) : intval($item));
            $translated = apply_filters('wpml_object_id', $video_id, 'video', true, $current_lang);
            $video_id = $translated ? intval($translated) : $video_id;

            $video = get_post($video_id);
            if ( ! $video ) continue;

            $lingua_terms = wp_get_post_terms($video_id, 'lingua_aggiuntiva', ['fields' => 'slugs']);
            $first_lingua = $lingua_terms[0] ?? '';
            if ( ($current_lang === 'it' && $first_lingua !== 'italiano') || ($current_lang !== 'it' && $first_lingua === 'italiano') ) continue;

            $video_link = get_post_meta($video_id, 'video_link', true);
            $youtube_embed = str_replace('https://youtu.be/', 'https://www.youtube.com/embed/', $video_link);

            $lingua_names = wp_get_post_terms($video_id, 'lingua_aggiuntiva', ['fields' => 'names']);
            $lingua_name = $lingua_names[0] ?? '';
            $flag_html = ($current_lang !== 'it' && function_exists('toroag_get_flag_html')) ? toroag_get_flag_html($first_lingua) : '';

            $output .= '<div class="col"><div class="card h-100">';
            $output .= '<div class="card-video embed-responsive embed-responsive-16by9">';
            $output .= '<div class="fluid-width-video-wrapper" style="padding-top: 56.25%;">';
            $output .= '<iframe title="' . esc_attr($video->post_title) . '" src="' . esc_url($youtube_embed) . '" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';
            $output .= '</div></div>';
            $output .= '<div class="card-body">';
            $output .= '<h5 class="card-title text-center py-2 mb-0">';
            $output .= '<a href="' . esc_url($video_link) . '" target="_blank" rel="noopener noreferrer">' . esc_html($video->post_title) . ' ' . $flag_html . '</a>';
            $output .= '</h5></div></div></div>';
        }

        $output .= '</div>';
        return $output;
    }
    add_shortcode('video_prodotto_v2', 'ta_render_video_prodotto_v2_shortcode');
}
