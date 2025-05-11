<?php
function toroag_filtra_per_lingua_aggiuntiva($video_posts) {
    $current_lang = function_exists('icl_object_id')
        ? apply_filters('wpml_current_language', null)
        : 'it';

    echo "<!-- lingua attiva: {$current_lang} / video totali prima: " . count($video_posts) . " -->";

    return array_filter($video_posts, function($p) use ($current_lang) {
        if (! $p instanceof WP_Post) return false;
        $has_term = has_term('italiano', 'lingua_aggiuntiva', $p->ID);
        echo "<!-- video ID: {$p->ID} / ha 'italiano': " . ($has_term ? 'sì' : 'no') . " -->";
        return ($current_lang === 'it') ? $has_term : ! $has_term;
    });
}

function ac_video_prodotto_shortcode() {
    $is_prod = is_singular('prodotto');
    $is_taxo = is_tax(array('tipo_di_prodotto','product-types'));

    if (! $is_prod && ! $is_taxo) {
        return '';
    }

    if ($is_prod) {
        $pod_context = 'prodotto';
        $field_name  = 'video_prodotto';
        $source_id   = get_the_ID();
    } else {
        $pod_context = 'term_tipo_di_prodotto';
        $field_name  = 'tipo-video';
        $term        = get_queried_object();
        $source_id   = $term->term_id;
    }

    $pod = pods($pod_context, $source_id);
    $videos = $pod->field($field_name);

    if (empty($videos)) {
        return '<!-- Nessun video associato -->';
    }

    // Applica filtro lingua
    $videos = toroag_filtra_per_lingua_aggiuntiva($videos);

    if (empty($videos)) {
        return '<!-- Nessun video nella lingua corrente -->';
    }

    ob_start();
    echo '<div class="video-card-grid row">';
    foreach ($videos as $video) {
        $id = is_object($video) ? $video->ID : $video;
        set_query_var('video_id', $id);
        get_template_part('inc/views/card', 'video');
    }
    echo '</div>';
    return ob_get_clean();
}
