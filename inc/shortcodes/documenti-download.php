<?php
/**
 * Shortcode per mostrare i video associati al prodotto (con fallback WPML migliorato).
 * Uso: [video_prodotto_v2]
 */
function ta_render_video_prodotto_v2_shortcode($atts) {
    global $post;

    // Verifica Pods
    if ( ! function_exists('pods') ) {
        return '<!-- DEBUG: Funzione pods() non disponibile -->';
    }

    // Lingua attuale e di default WPML
    $current_lang = function_exists('apply_filters') ? apply_filters('wpml_current_language', null) : 'it';
    $default_lang = function_exists('apply_filters') ? apply_filters('wpml_default_language', null) : 'it';

    // Mappa ID prodotto alla lingua corrente
    $prod_id_current = function_exists('apply_filters')
        ? apply_filters('wpml_object_id', $post->ID, 'prodotto', false, $current_lang)
        : $post->ID;
    $output = '';
    $output .= '<!-- DEBUG: Product ID per lingua ' . esc_html($current_lang) . ': ' . esc_html($prod_id_current) . ' -->';

    // Carica Pod per il prodotto nella lingua corrente
    $pod = pods('prodotto', $prod_id_current);
    if ( ! $pod->exists() ) {
        return '<!-- DEBUG: Pod prodotto non trovato per ID ' . esc_html($prod_id_current) . ' -->';
    }

    // Recupera campo relazione video_prodotto
    $videos = $pod->field('video_prodotto');
    $output .= '<!-- DEBUG: Raw video_prodotto (lang ' . esc_html($current_lang) . '): ' . esc_html(var_export($videos, true)) . ' -->';

    // Se vuoto, fallback alla lingua di default
    if ( empty($videos) || $videos === false ) {
        $prod_id_default = function_exists('apply_filters')
            ? apply_filters('wpml_object_id', $post->ID, 'prodotto', false, $default_lang)
            : $post->ID;
        $output .= '<!-- DEBUG: Fallback prodotto lingua ' . esc_html($default_lang) . ' ID ' . esc_html($prod_id_default) . ' -->';

        $pod = pods('prodotto', $prod_id_default);
        $videos = $pod->field('video_prodotto');
        $output .= '<!-- DEBUG: Raw fallback video_prodotto: ' . esc_html(var_export($videos, true)) . ' -->';

        if ( empty($videos) || $videos === false ) {
            $output .= '<!-- DEBUG: Nessun video anche in fallback -->';
            return $output;
        }
    }

    // Output video
    $output .= '<div class="video-prodotto-wrapper">';
    foreach ( (array) $videos as $video_item ) {
        // Estrai ID video
        if ( is_array($video_item) && isset($video_item['ID']) ) {
            $video_id = intval($video_item['ID']);
        } elseif ( is_object($video_item) && isset($video_item->ID) ) {
            $video_id = intval($video_item->ID);
        } else {
            $video_id = intval($video_item);
        }
        $output .= '<!-- DEBUG: Estratto video ID raw: ' . esc_html($video_id) . ' -->';

        // Mappa alla traduzione del video nella lingua corrente
        if ( function_exists('apply_filters') ) {
            $translated_vid = apply_filters('wpml_object_id', $video_id, 'video', false, $current_lang);
            $output .= '<!-- DEBUG: Video ID ' . esc_html($video_id) . ' tradotto in ' . esc_html($current_lang) . ': ' . esc_html($translated_vid) . ' -->';
            $video_id = $translated_vid;
        }

        // Recupera post video
        $video_post = get_post($video_id);
        if ( ! $video_post ) {
            $output .= '<!-- DEBUG: Nessun post trovato per video ID ' . esc_html($video_id) . ' -->';
            continue;
        }

        $output .= '<!-- DEBUG: Titolo video: ' . esc_html($video_post->post_title) . ' -->';
        $output .= '<div class="video-prodotto-item">';

        // Miniatura
        if ( has_post_thumbnail($video_id) ) {
            $output .= get_the_post_thumbnail($video_id, 'medium');
            $output .= '<!-- DEBUG: Miniatura video ID ' . esc_html($video_id) . ' -->';
        }

        // Link al video
        $permalink = get_permalink($video_post);
        $output .= '<a href="' . esc_url($permalink) . '">' . esc_html($video_post->post_title) . '</a>';
        $output .= '</div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('video_prodotto_v2', 'ta_render_video_prodotto_v2_shortcode');
