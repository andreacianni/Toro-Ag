<?php
/**
 * Shortcode per mostrare i video associati al prodotto (con fallback WPML robusto).
 * Uso: [video_prodotto_v2]
 */
if ( ! function_exists('ta_render_video_prodotto_v2_shortcode') ) {
    function ta_render_video_prodotto_v2_shortcode($atts) {
        global $post;

        if ( ! function_exists('pods') ) {
            return '<!-- EN fix 7 --><!-- DEBUG: Pods non disponibile -->';
        }

        // Lingue WPML
        $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default_lang = apply_filters('wpml_default_language', null);
        $output = '';

        // Mappa prodotto nella lingua corrente
        $prod_id_current = apply_filters('wpml_object_id', $post->ID, 'prodotto', false, $current_lang);
        $prod_id_current = $prod_id_current ? intval($prod_id_current) : intval($post->ID);
        $output .= '<!-- EN fix 7 --><!-- DEBUG: Prodotto ID lingua ' . esc_html($current_lang) . ': ' . esc_html($prod_id_current) . ' -->';

        // Carica Pod prodotto (lingua corrente)
        $pod = pods('prodotto', $prod_id_current, array('lang' => $current_lang));
        if ( ! $pod->exists() ) {
            return '<!-- EN fix 7 --><!-- DEBUG: Pod prodotto non trovato per ID ' . esc_html($prod_id_current) . ' -->';
        }

        // Recupera relazione video
        $videos = $pod->field('video_prodotto');
        $output .= '<!-- EN fix 7 --><!-- DEBUG: Raw video_prodotto: ' . esc_html(var_export($videos, true)) . ' -->';

        // Se non ci sono video, fallback IT
        if ( empty($videos) ) {
            $prod_id_default = apply_filters('wpml_object_id', $post->ID, 'prodotto', false, $default_lang);
            $prod_id_default = $prod_id_default ? intval($prod_id_default) : intval($post->ID);
            $output .= '<!-- EN fix 7 --><!-- DEBUG: Fallback Prodotto IT ID: ' . esc_html($prod_id_default) . ' -->';

            // Carica Pod fallback italiano (ottieni direttamente il meta)
            $output .= '<!-- EN fix 7 --><!-- DEBUG: Uso get_post_meta fallback -->';
            $videos = get_post_meta($prod_id_default, 'video_prodotto', true);
            $output .= '<!-- EN fix 7 --><!-- DEBUG: Raw get_post_meta fallback: ' . esc_html(var_export($videos, true)) . ' -->';
            $output .= '<!-- EN fix 7 --><!-- DEBUG: Raw fallback video_prodotto: ' . esc_html(var_export($videos, true)) . ' -->';

            if ( empty($videos) ) {
                $output .= '<!-- EN fix 7 --><!-- DEBUG: Ancora nessun video -->';
                return $output;
            }
        }

        // Output
        $output .= '<div class="video-prodotto-wrapper">';
        foreach ( (array) $videos as $item ) {
            $video_id = is_array($item) && isset($item['ID']) ? intval($item['ID']) : (is_object($item) && isset($item->ID) ? intval($item->ID) : intval($item));
            $output .= '<!-- EN fix 7 --><!-- DEBUG: ID raw: ' . esc_html($video_id) . ' -->';

            // Traduzione video
            $translated = apply_filters('wpml_object_id', $video_id, 'video', false, $current_lang);
            $video_id = $translated ? intval($translated) : $video_id;
            $output .= '<!-- EN fix 7 --><!-- DEBUG: ID tradotto: ' . esc_html($video_id) . ' -->';

            $video = get_post($video_id);
            if ( ! $video ) {
                $output .= '<!-- EN fix 7 --><!-- DEBUG: Video non trovato: ' . esc_html($video_id) . ' -->';
                continue;
            }

            $output .= '<!-- EN fix 7 --><!-- DEBUG: Titolo: ' . esc_html($video->post_title) . ' -->';
            $output .= '<div class="video-prodotto-item">';
            if ( has_post_thumbnail($video_id) ) {
                $output .= get_the_post_thumbnail($video_id, 'medium');
                $output .= '<!-- EN fix 7 --><!-- DEBUG: Thumbnail -->';
            }
            $output .= '<!-- EN fix 7 --><!-- DEBUG: Link -->'
                . '<a href="' . esc_url(get_permalink($video_id)) . '">' . esc_html($video->post_title) . '</a>';
            $output .= '</div>';
        }
        $output .= '</div>';

        return $output;
    }
    add_shortcode('video_prodotto_v2', 'ta_render_video_prodotto_v2_shortcode');
}
