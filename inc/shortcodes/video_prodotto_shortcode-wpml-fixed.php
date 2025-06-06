<?php
/**
 * Shortcode [video_prodotto] – compatibile con WPML e Divi
 * Mostra video associati a prodotti o tassonomie, filtrati per lingua con tassonomia 'lingua_aggiuntiva'.
 */

function toroag_filtra_per_lingua_aggiuntiva($video_posts) {
    $current_lang = function_exists('icl_object_id')
        ? apply_filters('wpml_current_language', null)
        : 'it';

    $debug = "<!-- lingua attiva: {$current_lang} / video totali prima: " . count($video_posts) . " -->\n";

    $filtered = array_filter($video_posts, function($p) use (&$debug, $current_lang) {
        if (! $p instanceof WP_Post) return false;
        $has_term = has_term('italiano', 'lingua_aggiuntiva', $p->ID);
        $debug .= "<!-- video ID: {$p->ID} / ha 'italiano': " . ($has_term ? 'sì' : 'no') . " -->\n";
        return ($current_lang === 'it') ? $has_term : ! $has_term;
    });

    echo $debug;
    return $filtered;
}

function ac_video_prodotto_shortcode() {
    ob_start(); // cattura tutto l'output per sicurezza layout Divi

    $is_prod = is_singular('prodotto');
    $is_taxo = is_tax(array('tipo_di_prodotto','product-types'));

    if (! $is_prod && ! $is_taxo) {
        return '';
    }

    $pod_context = $is_prod ? 'prodotto' : 'term_tipo_di_prodotto';
    $field_name  = $is_prod ? 'video_prodotto' : 'tipo-video';
    $source_id   = $is_prod ? get_the_ID() : get_queried_object_id();

    // WPML: recupera l’ID in italiano se possibile, ma solo se la traduzione esiste
    $current_lang = apply_filters('wpml_current_language', null);
    if ('it' !== $current_lang) {
        $mapped_id = apply_filters('wpml_object_id', $source_id, $is_prod ? 'prodotto' : 'tipo_di_prodotto', false, 'it');
        if (! empty($mapped_id)) {
            $source_id = $mapped_id;
        }
    }

    $pod = pods($pod_context, $source_id);
    if (! $pod) {
        return '<!-- Errore: pod non trovato -->';
    }

    $raw = $pod->field($field_name);
    if (empty($raw) || ! is_array($raw)) {
        return '<!-- Nessun video associato -->';
    }

    // Verifica oggetti video WP_Post validi
    $videos_raw = array_map(function($v) {
        $id = is_array($v) && !empty($v['ID']) ? intval($v['ID']) : intval($v);
        return get_post($id);
    }, $raw);

    $videos = toroag_filtra_per_lingua_aggiuntiva(array_filter($videos_raw));

    if (empty($videos)) {
        return '<!-- Nessun video nella lingua corrente -->';
    }

    echo '<div class="video-card-grid row">';
    foreach ($videos as $video) {
        $src = get_post_meta($video->ID, 'video_link', true);
        $embed = wp_oembed_get($src);
        if (! $embed) continue;

        echo '<div class="col">'
           . '<div class="card h-100">'
           . '<div class="card-video embed-responsive embed-responsive-16by9">' . $embed . '</div>'
           . '<div class="card-body">'
           . '<h5 class="card-title text-center py-2 mb-0">'
           . '<a href="' . esc_url($src) . '" target="_blank" rel="noopener noreferrer">'
           . esc_html(get_the_title($video->ID)) . '</a>'
           . '</h5></div></div></div>';
    }
    echo '</div>';

    return ob_get_clean();
}
add_shortcode('video_prodotto', 'ac_video_prodotto_shortcode');
