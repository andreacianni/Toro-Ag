<?php
/**
 * Shortcode [video_prodotto]
 * Mostra i video in card Bootstrap (2 per riga) con filtro per lingua via taxonomy 'lingua_aggiuntiva'.
 * Funziona sia nelle pagine singolo prodotto (campo video_prodotto) sia negli archivi di tipo_di_prodotto (campo tipo-video).
 */
function ac_video_prodotto_shortcode() {
    // Contesto: singolo prodotto o archivio tipo_di_prodotto (slug originale o tradotto)
    $is_prod = is_singular('prodotto');
    $is_taxo = is_tax(array('tipo_di_prodotto','product-types'));

    if (! $is_prod && ! $is_taxo) {
        return '';
    }

    // Imposta contesto e campo
    if ($is_prod) {
        $pod_context = 'prodotto';
        $field_name  = 'video_prodotto';
        $source_id   = get_the_ID();
    } else {
        // Per termini Pods usa context 'term_{taxonomy}'
        $pod_context = 'term_tipo_di_prodotto';
        $field_name  = 'tipo-video';
        $term        = get_queried_object();
        $source_id   = $term->term_id;
    }

    // Lingua corrente WPML ('it', 'en', ecc.)
    $current_lang = apply_filters('wpml_current_language', null);
    // Se non italiano, usa l'ID originale in italiano
    if ('it' !== $current_lang) {
        $orig = apply_filters('wpml_object_id', $source_id, $is_prod ? 'prodotto' : 'tipo_di_prodotto', false, 'it');
        if ($orig) {
            $source_id = $orig;
        }
    }

    // Carica Pods (post_type o term)
    $pod = pods($pod_context, $source_id);
    if (! $pod) {
        return '';
    }

    // Recupera array raw del campo
    $raw = $pod->field($field_name);
    if (empty($raw) || ! is_array($raw)) {
        return '';
    }

    // Filtra per lingua aggiuntiva (tassonomia)
    $videos = array();
    foreach ($raw as $item) {
        $vid = is_array($item) && ! empty($item['ID']) ? intval($item['ID']) : intval($item);
        if (! $vid) {
            continue;
        }
        $pvid = pods('video', $vid);
        if (! $pvid) {
            continue;
        }
        $src = $pvid->field('video_link');
        if (! $src) {
            continue;
        }
        $terms = wp_get_post_terms($vid, 'lingua_aggiuntiva', array('fields'=>'slugs'));
        if (is_wp_error($terms) || empty($terms)) {
            continue;
        }
        $lang = $terms[0];
        if ('it' === $current_lang) {
            if ('italiano' !== $lang) {
                continue;
            }
        } else {
            if ('italiano' === $lang) {
                continue;
            }
        }
        $videos[] = array('id'=>$vid, 'src'=>$src, 'title'=>get_the_title($vid));
    }

    if (empty($videos)) {
        return '';
    }

    // Ordina per ID
    usort($videos, function($a, $b) {
        return $a['id'] <=> $b['id'];
    });

    // Genera markup Bootstrap cards
    $output = '<div class="row row-cols-2 g-4 video-prodotto-list">';
    foreach ($videos as $v) {
        $embed = wp_oembed_get($v['src']);
        if (! $embed) {
            continue;
        }
        $output .= '<div class="col">'
                 . '<div class="card h-100">'
                 . '<div class="card-video embed-responsive embed-responsive-16by9">' . $embed . '</div>'
                 . '<div class="card-body">'
                 . '<h5 class="card-title text-center py-2 mb-0">'
                 . '<a href="' . esc_url($v['src']) . '" target="_blank" rel="noopener noreferrer">' . esc_html($v['title']) . '</a>'
                 . '</h5></div></div></div>';
    }
    $output .= '</div>';

    return $output;
}
add_shortcode('video_prodotto', 'ac_video_prodotto_shortcode');
