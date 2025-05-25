<?php
/**
 * Shortcode per mostrare i video associati al prodotto.
 * Uso: [video_prodotto_v2]
 */
function ta_render_video_prodotto_v2_shortcode($atts) {
    global $post;

    // Controllo Pods
    if ( ! function_exists('pods') ) {
        return '<!-- DEBUG: Funzione pods() non disponibile -->';
    }

    // Carico il Pod "prodotto"
    $pod = pods('prodotto', $post->ID);
    if ( ! $pod->exists() ) {
        return '<!-- DEBUG: Pod prodotto non trovato per ID ' . esc_html($post->ID) . ' -->';
    }

    // Recupero campo relazione video_prodotto
    $videos = $pod->field('video_prodotto');
    $output = '';
    $output .= '<!-- DEBUG: Contenuto raw di video_prodotto: ' . esc_html(var_export($videos, true)) . ' -->';

    if ( empty($videos) ) {
        $output .= '<!-- DEBUG: Nessun video associato al prodotto ID ' . esc_html($post->ID) . ' -->';
        return $output;
    }

    $output .= '<div class="video-prodotto-wrapper">';

    foreach ( (array) $videos as $video_item ) {
        // Estrazione corretta dell'ID dal risultato Pods
        if ( is_array($video_item) && isset($video_item['ID']) ) {
            $video_id = intval($video_item['ID']);
        } elseif ( is_object($video_item) && isset($video_item->ID) ) {
            $video_id = intval($video_item->ID);
        } else {
            $video_id = intval($video_item);
        }
        $output .= '<!-- DEBUG: Estratto video ID: ' . $video_id . ' -->';

        $video_post = get_post($video_id);
        if ( ! $video_post ) {
            $output .= '<!-- DEBUG: Nessun post trovato per video ID ' . $video_id . ' -->';
            continue;
        }

        // Titolo video
        $output .= '<!-- DEBUG: Titolo video: ' . esc_html($video_post->post_title) . ' -->';

        // Costruisco markup video (miniatura + link)
        $output .= '<div class="video-prodotto-item">';
        if ( has_post_thumbnail($video_id) ) {
            $output .= get_the_post_thumbnail($video_id, 'medium');
            $output .= '<!-- DEBUG: Aggiunta miniatura per video ID ' . $video_id . ' -->';
        }

        $video_url = get_permalink($video_post);
        $output .= '<a href="' . esc_url($video_url) . '">' . esc_html($video_post->post_title) . '</a>';
        $output .= '</div>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('video_prodotto_v2', 'ta_render_video_prodotto_v2_shortcode');
