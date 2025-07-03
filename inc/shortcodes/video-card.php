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

/**
 * Shortcode [carosello_video_pagina] – compatibile con WPML e Divi
 * Mostra tutti i video associati a una pagina e filtra in base a 'lingua_aggiuntiva'.
 * Supporta parametro: titolo="Titolo da visualizzare sopra i video"
 * VERSIONE CORRETTA CON FALLBACK WPML
 */

function ac_carosello_video_pagina_shortcode($atts = []) {
    ob_start();

    if (! is_page()) {
        return '<!-- [carosello_video_pagina] disponibile solo nelle pagine -->';
    }

    $atts = shortcode_atts([
        'titolo' => ''
    ], $atts);

    // Gestione WPML robusta come negli shortcode funzionanti
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);
    
    $source_id = get_the_ID();
    $pod_context = 'page';
    $field_name = 'video_pagina';

    // Debug info
    echo "<!-- shortcode carosello_video_pagina: ID corrente {$source_id}, lingua {$current_lang} -->";

    // Tentativo con Pods nella lingua corrente
    $pod = pods($pod_context, $source_id, ['lang' => $current_lang]);
    $raw = $pod && $pod->exists() ? $pod->field($field_name) : [];

    // Fallback WPML se non trova video nella lingua corrente
    if (empty($raw)) {
        echo "<!-- shortcode carosello_video_pagina: nessun video in lingua corrente, fallback -->";
        $page_id_default = apply_filters('wpml_object_id', $source_id, 'page', true, $default_lang);
        $page_id_default = $page_id_default ? intval($page_id_default) : intval($source_id);
        echo "<!-- shortcode carosello_video_pagina: ID lingua default: {$page_id_default} -->";
        
        // Recupero diretto con get_post_meta come fallback
        $raw = array_map('intval', get_post_meta($page_id_default, $field_name, false));
        
        if (empty($raw)) {
            return "<!-- Nessun video associato: campo '{$field_name}' vuoto anche in lingua default. ID post: {$source_id} -->";
        }
    }

    $video_ids = array_map(function($v) {
        return is_array($v) && !empty($v['ID']) ? intval($v['ID']) : intval($v);
    }, (array)$raw);

    $video_ids = array_filter($video_ids);
    echo "<!-- shortcode carosello_video_pagina: video IDs pre-filtro: " . implode(',', $video_ids) . " -->";
    
    $video_ids = toroag_filtra_per_lingua_aggiuntiva($video_ids);

    if (empty($video_ids)) {
        return '<!-- Nessun video nella lingua corrente dopo filtro -->';
    }

    if (!empty($atts['titolo'])) {
        echo '<h3 class="text-start fw-bold border-bottom ps-1 py-2 my-4">' . esc_html($atts['titolo']) . '</h3>';
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
 * VERSIONE CORRETTA CON FALLBACK WPML
 */

function ac_video_pagina_shortcode($atts = []) {
    if (! is_page()) {
        return '<!-- [video_pagina] disponibile solo nelle pagine -->';
    }

    $atts = shortcode_atts([
        'titolo' => ''
    ], $atts);

    // Gestione WPML robusta come negli shortcode funzionanti
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
    $default_lang = apply_filters('wpml_default_language', null);
    
    $source_id = get_the_ID();
    $field_name = 'video_pagina';

    // Debug info
    echo "<!-- shortcode video_pagina: ID corrente {$source_id}, lingua {$current_lang} -->";

    // Tentativo con Pods nella lingua corrente
    $pod = pods('page', $source_id, ['lang' => $current_lang]);
    $raw = $pod && $pod->exists() ? $pod->field($field_name) : [];

    // Fallback WPML se non trova video nella lingua corrente
    if (empty($raw)) {
        echo "<!-- shortcode video_pagina: nessun video in lingua corrente, fallback -->";
        $page_id_default = apply_filters('wpml_object_id', $source_id, 'page', true, $default_lang);
        $page_id_default = $page_id_default ? intval($page_id_default) : intval($source_id);
        echo "<!-- shortcode video_pagina: ID lingua default: {$page_id_default} -->";
        
        // Recupero diretto con get_post_meta come fallback
        $raw = array_map('intval', get_post_meta($page_id_default, $field_name, false));
        
        if (empty($raw)) {
            return "<!-- Nessun video associato: campo '{$field_name}' vuoto anche in lingua default. ID post: {$source_id} -->";
        }
    }

    $video_ids = array_map(function($v) {
        return is_array($v) && !empty($v['ID']) ? intval($v['ID']) : intval($v);
    }, (array)$raw);
    
    $video_ids = array_values(array_filter($video_ids));
    echo "<!-- shortcode video_pagina: video IDs pre-filtro: " . implode(',', $video_ids) . " -->";
    
    $video_ids = toroag_filtra_per_lingua_aggiuntiva($video_ids);

    if (empty($video_ids)) {
        return '<!-- Nessun video nella lingua corrente dopo filtro -->';
    }

    ob_start();

    if (! empty($atts['titolo'])) {
        echo '<h3 class="text-start fs-4 fw-bold border-bottom ps-1 py-2 my-4">' . esc_html($atts['titolo']) . '</h3>';
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