<?php
/**
 * Shortcode: [video_tipo_prodotto_v2]
 * Recupera i video collegati via Pods (relationship pick) al campo 'tipo-video'
 * sul termine corrente di tipo_di_prodotto, con debug HTML (alpha 2).
 */
function ta_render_video_tipo_prodotto_v2_shortcode( $atts ) {
    // Debug alpha 2: funzione avviata in contesto tassonomia
    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: funzione avviata in contesto tassonomia -->';

    // Verifica contesto tassonomia tipo_di_prodotto
    $term = get_queried_object();
    if ( ! $term || ! isset( $term->term_id ) || $term->taxonomy !== 'tipo_di_prodotto' ) {
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: contesto non tassonomia tipo_di_prodotto -->';
        return '';
    }

    // Lingue WPML
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);

    // Recupero lista video dal campo Pods
    $pod_term = pods('tipo_di_prodotto', $term->term_id, ['lang' => $current_lang]);
    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: pod esiste? ' . ($pod_term->exists() ? 'yes' : 'no') . ' -->';
    $videos = $pod_term->exists() ? $pod_term->field('tipo-video') : [];
    if ( empty($videos) ) {
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: nessun video in campo, fallback lingua default -->';
        $term_id_default = apply_filters('wpml_object_id', $term->term_id, 'tipo_di_prodotto', true, $default_lang);
        $videos = $term_id_default ? get_term_meta($term_id_default, 'tipo-video', false) : [];
    }

    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: video IDs raw: ' . implode(',', array_map(function($i){ return is_array($i)&&isset($i['ID'])?$i['ID']:(is_object($i)&&isset($i->ID)?$i->ID:$i); }, (array)$videos)) . ' -->';

    // Griglia video
    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: inizio video-card-grid -->';
    $output = '<div class="video-card-grid row">';

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
        echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: card video ' . $video_id . ' -->';

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
    echo '<!-- shortcode video_tipo_prodotto_v2 alpha 2: fine video-card-grid -->';
    return $output;
}
add_shortcode('video_tipo_prodotto_v2', 'ta_render_video_tipo_prodotto_v2_shortcode');
