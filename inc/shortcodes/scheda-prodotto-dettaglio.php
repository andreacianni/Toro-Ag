<?php
/**
 * Shortcode per mostrare la scheda e i documenti nel dettaglio di un singolo prodotto,
 * adattato per un layout in colonna, mantenendo grouping per lingua.
 * Uso: [scheda_prodotto_dettaglio]
 */
add_action('init', function() {
    add_shortcode('scheda_prodotto_dettaglio', 'ta_scheda_prodotto_dettaglio_shortcode');
});

if (! function_exists('ta_scheda_prodotto_dettaglio_shortcode') ) {
    function ta_scheda_prodotto_dettaglio_shortcode($atts) {
        global $post;
        // Solo su single prodotto
        if (!$post || get_post_type($post) !== 'prodotto') {
            return '<!-- shortcode scheda_prodotto_dettaglio --><!-- DEBUG: contesto non prodotto -->';
        }

        // Lingua corrente
        $lang = defined('ICL_LANGUAGE_CODE')
            ? ICL_LANGUAGE_CODE
            : apply_filters('wpml_current_language', null);

        // Funzione per raccogliere e filtrare file
        $collect = function($meta_key, $file_meta_key) use ($lang) {
            $order = ['italiano'=>0, 'inglese'=>1, 'francese'=>2, 'spagnolo'=>3];
            $result = [];
            foreach ((array) get_post_meta($post->ID, $meta_key, false) as $did) {
                $slug = wp_get_post_terms($did, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? 'altre';
                // visibilità per lingua
                if (($lang === 'it' && $slug !== 'italiano') || ($lang !== 'it' && $slug === 'italiano')) {
                    continue;
                }
                $fid = get_post_meta($did, $file_meta_key, true);
                if (!$fid) continue;
                $url = wp_get_attachment_url($fid);
                if (!$url) continue;
                $result[] = [
                    'title'=> get_the_title($did),
                    'url'  => $url,
                    'lang' => $slug,
                    'prio' => $order[$slug] ?? 99,
                ];
            }
            usort($result, function($a, $b) {
                return $a['prio'] <=> $b['prio'];
            });
            return $result;
        };

        // Raccogli schede e documenti
        $schede = $collect('scheda_prodotto', 'scheda-prodotto');
        $docs   = $collect('documento_prodotto', 'documento-prodotto');

        if (empty($schede) && empty($docs)) {
            return '<p class="text-center text-muted">' . esc_html__('Nessuna scheda o documento disponibile', 'toro-ag') . '</p>';
        }

        // Inizio HTML
        $html = '<div class="product-documents">';

        // Output schede
        if (!empty($schede)) {
            $html .= '<div class="schede-section mb-3">';
            $html .= '<h5 class="section-title">' . esc_html__('Schede Prodotto', 'toro-ag') . '</h5>';
            // Raggruppa per lingua
            $groups = [];
            foreach ($schede as $item) {
                $groups[$item['lang']][] = $item;
            }
            foreach ($groups as $slug => $items) {
                $html .= '<div class="lang-group lang-' . esc_attr($slug) . ' mb-2">';
                // Bandiera e nome lingua
                if (function_exists('toroag_get_flag_html')) {
                    $html .= '<span class="flag me-1">' . toroag_get_flag_html($slug) . '</span>';
                }
                $html .= '<span class="lang-label text-uppercase small me-2">' . esc_html($slug) . '</span>';
                // Link
                foreach ($items as $doc) {
                    $html .= '<a href="' . esc_url($doc['url']) . '" class="doc-link d-block small text-decoration-none mb-1">';
                    // Icona file
                    if (function_exists('toroag_get_icon_class')) {
                        $html .= '<i class="bi ' . esc_attr(toroag_get_icon_class($doc['url'])) . ' me-1"></i>';
                    }
                    $html .= esc_html($doc['title']);
                    $html .= '</a>';
                }
                $html .= '</div>';
            }
            $html .= '</div>'; // fine schede-section
        }

        // Output documenti
        if (!empty($docs)) {
            $html .= '<div class="docs-section">';
            $html .= '<h5 class="section-title">' . esc_html__('Documenti Prodotto', 'toro-ag') . '</h5>';
            $groups = [];
            foreach ($docs as $item) {
                $groups[$item['lang']][] = $item;
            }
            foreach ($groups as $slug => $items) {
                $html .= '<div class="lang-group lang-' . esc_attr($slug) . ' mb-2">';
                if (function_exists('toroag_get_flag_html')) {
                    $html .= '<span class="flag me-1">' . toroag_get_flag_html($slug) . '</span>';
                }
                $html .= '<span class="lang-label text-uppercase small me-2">' . esc_html($slug) . '</span>';
                foreach ($items as $doc) {
                    $html .= '<a href="' . esc_url($doc['url']) . '" class="doc-link d-block small text-decoration-none mb-1">';
                    if (function_exists('toroag_get_icon_class')) {
                        $html .= '<i class="bi ' . esc_attr(toroag_get_icon_class($doc['url'])) . ' me-1"></i>';
                    }
                    $html .= esc_html($doc['title']);
                    $html .= '</a>';
                }
                $html .= '</div>';
            }
            $html .= '</div>'; // fine docs-section
        }

        $html .= '</div>'; // fine product-documents

        return $html;
    }
}
