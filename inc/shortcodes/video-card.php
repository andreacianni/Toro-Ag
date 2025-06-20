<?php
/**
 * Shortcode [video_prodotto] – compatibile con WPML e Divi
 * Mostra tutti i video associati a un prodotto e filtra in base a 'lingua_aggiuntiva'.
 */

function toroag_filtra_per_lingua_aggiuntiva($video_ids) {
    $current_lang = function_exists('icl_object_id')
        ? apply_filters('wpml_current_language', null)
        : 'it';

    $debug = "<!-- lingua attiva: {$current_lang} / video totali prima: " . count($video_ids) . " -->\n";

    $filtered = [];
    foreach ($video_ids as $id) {
        $terms = wp_get_post_terms($id, 'lingua_aggiuntiva', ['fields' => 'slugs']);
        if (is_wp_error($terms) || empty($terms)) continue;

        $term = $terms[0];
        $debug .= "<!-- video ID: {$id} / lingua_aggiuntiva: {$term} -->\n";

        if ($current_lang === 'it' && $term === 'italiano') {
            $filtered[] = $id;
        } elseif ($current_lang !== 'it' && $term !== 'italiano') {
            $filtered[] = $id;
        }
    }

    echo $debug;
    return $filtered;
}

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

        echo '<div class="col">'
           . '<div class="card h-100">'
           . '<div class="card-video embed-responsive embed-responsive-16by9">' . $embed . '</div>'
           . '<div class="card-body">'
           . '<h5 class="card-title text-center py-2 mb-0">'
           . '<a href="' . esc_url($src) . '" target="_blank" rel="noopener noreferrer">'
           . esc_html(get_the_title($id)) . '</a>'
           . '</h5></div></div></div>';
    }
    echo '</div>';

    return ob_get_clean();
}
add_shortcode('video_prodotto', 'ac_video_prodotto_shortcode');

/**
 * Shortcode [carosello_video_pagina] – compatibile con WPML e Divi
 * Mostra tutti i video associati a una pagina e filtra in base a 'lingua_aggiuntiva'.
 * Supporta parametro: titolo="Titolo da visualizzare sopra i video"
 */

function ac_carosello_video_pagina_shortcode($atts = []) {
    ob_start();

    if (! is_page()) {
        return '<!-- [carosello_video_pagina] disponibile solo nelle pagine -->';
    }

    $atts = shortcode_atts([
        'titolo' => ''
    ], $atts);

    $source_id   = get_the_ID();
    $pod_context = 'page';
    $field_name  = 'video_pagina';

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

    if (!empty($atts['titolo'])) {
        echo '<h5 class="text-bg-dark text-center py-2 my-4 rounded-2">' . esc_html($atts['titolo']) . '</h5>';
    }

    $total_videos = count($video_ids);

    if ($total_videos <= 3) {
        echo '<div class="video-card-grid row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">';
        foreach ($video_ids as $id) {
            $src = get_post_meta($id, 'video_link', true);
            $embed = wp_oembed_get($src);
            if (! $embed) continue;

            echo '<div class="col">'
               . '<div class="card">'
               . '<div class="card-video embed-responsive embed-responsive-16by9">' . $embed . '</div>'
               . '<div class="card-body">'
               . '<h5 class="card-title text-center py-2 mb-0">'
               . '<a href="' . esc_url($src) . '" target="_blank" rel="noopener noreferrer">'
               . esc_html(get_the_title($id)) . '</a>'
               . '</h5></div></div></div>';
        }
        echo '</div>';
    } else {
        $carousel_id = 'swiper_' . $source_id;
        echo '<div class="swiper-container overflow-hidden position-relative pb-5" id="' . esc_attr($carousel_id) . '">
                <div class="swiper-wrapper">';

        foreach ($video_ids as $id) {
            $src = get_post_meta($id, 'video_link', true);
            $embed = wp_oembed_get($src);
            if (! $embed) continue;

            $title = esc_html(get_the_title($id));

            echo '<div class="swiper-slide">
                    <div class="card d-flex flex-column position-relative group video-card-height">
                        <div class="card-video embed-responsive embed-responsive-16by9">' . $embed . '</div>
                        <div class="card-body">
                            <h5 class="card-title text-center w-100 py-2 mb-0">
                                <a href="' . esc_url($src) . '" target="_blank" rel="noopener noreferrer" class="d-block text-decoration-none text-dark">'
                                . $title . '</a>
                            </h5>
                        </div>
                    </div>
                  </div>';
        }

        echo '  </div>
              </div>
              <div class="swiper-pagination position-absolute start-50 translate-middle-x mt-2" style="bottom: 0;"></div>';

        echo '<script>
            function equalizeCardHeights_' . esc_js($carousel_id) . '() {
                const cards = document.querySelectorAll("#' . esc_js($carousel_id) . ' .video-card-height");
                let max = 0;
                cards.forEach(c => {
                    c.style.height = "auto";
                    max = Math.max(max, c.offsetHeight);
                });
                cards.forEach(c => c.style.height = max + "px");
            }

            document.addEventListener("DOMContentLoaded", function () {
                new Swiper("#' . esc_js($carousel_id) . '", {
                    slidesPerView: 3,
                    spaceBetween: 30,
                    loop: true,
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: "#' . esc_js($carousel_id) . ' ~ .swiper-pagination",
                        clickable: true,
                    },
                    breakpoints: {
                        0: {
                            slidesPerView: 1,
                        },
                        768: {
                            slidesPerView: 2,
                        },
                        992: {
                            slidesPerView: 3,
                        }
                    },
                    on: {
                        init: equalizeCardHeights_' . esc_js($carousel_id) . ',
                        resize: equalizeCardHeights_' . esc_js($carousel_id) . '
                    }
                });
            });
        </script>';
    }

    return ob_get_clean();
}
add_shortcode('carosello_video_pagina', 'ac_carosello_video_pagina_shortcode');

/**
 * Shortcode [video_pagina] – compatibile con WPML e Divi
 * Carica subito tutti i video in una griglia statica
 * Supporta parametro: titolo="Titolo da visualizzare sopra i video"
 */

function ac_video_pagina_shortcode($atts = []) {
    if (! is_page()) {
        return '<!-- [video_pagina] disponibile solo nelle pagine -->';
    }

    $atts = shortcode_atts([
        'titolo' => ''
    ], $atts);

    $source_id = get_the_ID();
    $pod       = pods('page', $source_id);
    if (! $pod) {
        return '<!-- Errore: pod non trovato -->';
    }

    $raw = $pod->field('video_pagina');
    if (empty($raw) || ! is_array($raw)) {
        return "<!-- Nessun video associato: campo 'video_pagina' vuoto. ID post: {$source_id} -->";
    }

    $video_ids = array_map(function($v) {
        return is_array($v) && !empty($v['ID']) ? intval($v['ID']) : intval($v);
    }, $raw);
    $video_ids = array_values(array_filter($video_ids));
    $video_ids = toroag_filtra_per_lingua_aggiuntiva($video_ids);

    if (empty($video_ids)) {
        return '<!-- Nessun video nella lingua corrente -->';
    }

    ob_start();

    if (! empty($atts['titolo'])) {
        echo '<h5 class="text-bg-dark text-center py-2 my-4 rounded-2">' . esc_html($atts['titolo']) . '</h5>';
    }

    echo '<div id="video-pagina-wrapper">';
    echo '<div class="d-flex flex-wrap justify-content-start" id="video-pagina-grid">';

    foreach ($video_ids as $id) {
        $src  = get_post_meta($id, 'video_link', true);
        $oemb = wp_oembed_get($src);
        if (! $oemb) {
            continue;
        }
        echo '<div class="p-2" style="flex: 0 0 33.333%;">';
        echo '<div class="card h-100">';
        echo '<div class="embed-responsive embed-responsive-16by9 w-100">' . $oemb . '</div>';
        echo '<div class="card-body">';
        echo '<h5 class="card-title text-center py-2 mb-0">';
        echo '<a href="' . esc_url($src) . '" target="_blank" rel="noopener noreferrer">';
        echo esc_html(get_the_title($id));
        echo '</a></h5></div></div></div>';
    }

    echo '</div>'; // chiude grid
    echo '</div>'; // chiude wrapper

    return ob_get_clean();
}
add_shortcode('video_pagina', 'ac_video_pagina_shortcode');
