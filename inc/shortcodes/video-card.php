<?php
/**
 * Shortcode [video_prodotto] – compatibile con WPML e Divi
 * Mostra tutti i video associati a un prodotto e filtra in base a 'lingua_aggiuntiva'.
 */

function ac_video_prodotto_shortcode() {
    ob_start();

    if (! is_singular('prodotto')) {
        return '<!-- [video_prodotto] disponibile solo nelle pagine singolo prodotto -->';
    }

    $source_id   = get_the_ID();
    $pod_context = 'prodotto';
    $field_name  = 'video_prodotto';

    $pod = pods($pod_context, $source_id);
    if (! $pod) {
        return '<!-- Errore: pod non trovato -->';
    }

    $raw = $pod->field($field_name);
    if (empty($raw) || ! is_array($raw)) {
        return "<!-- Nessun video associato: campo '{$field_name}' vuoto. ID post: {$source_id} -->";
    }

    $video_ids = array_map(function($v) {
        return is_array($v) && !empty($v['ID']) ? intval($v['ID']) : intval($v);
    }, $raw);

    $video_ids = array_filter($video_ids);
    $video_ids = toroag_filtra_per_lingua_aggiuntiva($video_ids);

    if (empty($video_ids)) {
        return '<!-- Nessun video nella lingua corrente -->';
    }

    echo '<div class="video-card-grid row">';
    foreach ($video_ids as $id) {
        $src = get_post_meta($id, 'video_link', true);
        $embed = wp_oembed_get($src);
        if (! $embed) continue;

        // Ottieni il termine lingua associato
        $terms = wp_get_post_terms($id, 'lingua_aggiuntiva', ['fields' => 'slugs']);
        $lang_slug = (!is_wp_error($terms) && !empty($terms)) ? $terms[0] : '';
        $flag_html = toroag_get_flag_html($lang_slug);

        echo '<div class="col">'
           . '<div class="card h-100">'
           . '<div class="card-video embed-responsive embed-responsive-16by9">' . $embed . '</div>'
           . '<div class="card-body">'
           . '<h5 class="card-title text-center py-2 mb-0">'
           . '<a href="' . esc_url($src) . '" target="_blank" rel="noopener noreferrer">'
           . esc_html(get_the_title($id)) . '</a> ' . $flag_html
           . '</h5></div></div></div>';
    }
    echo '</div>';

    return ob_get_clean();
}
add_shortcode('video_prodotto', 'ac_video_prodotto_shortcode');
