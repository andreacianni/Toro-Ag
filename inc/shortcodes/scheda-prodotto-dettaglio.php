<?php
/**
 * Shortcode per mostrare la scheda prodotto e i documenti nel dettaglio di un singolo prodotto
 * Basato su elenco_prodotti_con_dettagli, adattato al contesto single product
 * Uso: [scheda_prodotto_dettaglio]
 */
add_action('init', function() {
    add_shortcode('scheda_prodotto_dettaglio', 'ta_scheda_prodotto_dettaglio_shortcode');
});

if (!function_exists('ta_scheda_prodotto_dettaglio_shortcode')) {
    function ta_scheda_prodotto_dettaglio_shortcode($atts) {
        global $post;
        if (!$post || get_post_type($post) !== 'prodotto') {
            return '<!-- shortcode scheda_prodotto_dettaglio --><!-- DEBUG: contesto non prodotto -->';
        }

        // Lingua corrente
        $lang = defined('ICL_LANGUAGE_CODE')
            ? ICL_LANGUAGE_CODE
            : apply_filters('wpml_current_language', null);

        // Helper: raccoglie file da un meta key, filtra per lingua e ordina
        $collect_files = function($meta_key, $file_meta_key) use ($lang) {
            $lang_order = ['italiano'=>0, 'inglese'=>1, 'francese'=>2, 'spagnolo'=>3];
            $items = [];
            foreach ((array) get_post_meta(get_the_ID(), $meta_key, false) as $did) {
                $term = wp_get_post_terms($did, 'lingua_aggiuntiva', ['fields'=>'slugs']);
                $slug = !empty($term) ? $term[0] : 'altre';
                if (($lang === 'it' && $slug !== 'italiano') || ($lang !== 'it' && $slug === 'italiano')) {
                    continue;
                }
                $fid = get_post_meta($did, $file_meta_key, true);
                if (!$fid) continue;
                $url = wp_get_attachment_url($fid);
                if (!$url) continue;
                $items[] = [
                    'title' => get_the_title($did),
                    'url'   => $url,
                    'lang'  => $slug,
                    'prio'  => $lang_order[$slug] ?? 99
                ];
            }
            usort($items, function($a, $b) {
                return $a['prio'] <=> $b['prio'];
            });
            return $items;
        };

        // Raccogli schede prodotto e documenti
        $schede = $collect_files('scheda_prodotto', 'scheda-prodotto');
        $docs   = $collect_files('documento_prodotto', 'documento-prodotto');

        if (empty($schede) && empty($docs)) {
            return '<!-- shortcode scheda_prodotto_dettaglio --><!-- DEBUG: Nessuna scheda o documento disponibile -->';
        }

        // Genera output HTML
        $html = '<div class="product-documents">';

        if (!empty($schede)) {
            $html .= '<h4>' . esc_html__('Schede Prodotto', 'toro-ag') . '</h4>';
            $html .= '<ul class="scheda-prod-list">';
            foreach ($schede as $item) {
                $html .= sprintf(
                    '<li><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></li>',
                    esc_url($item['url']), esc_html($item['title'])
                );
            }
            $html .= '</ul>';
        }

        if (!empty($docs)) {
            $html .= '<h4>' . esc_html__('Documenti Prodotto', 'toro-ag') . '</h4>';
            $html .= '<ul class="docs-prod-list">';
            foreach ($docs as $item) {
                $html .= sprintf(
                    '<li><a href="%s" target="_blank" rel="noopener noreferrer">%s</a></li>',
                    esc_url($item['url']), esc_html($item['title'])
                );
            }
            $html .= '</ul>';
        }

        $html .= '</div>';

        return $html;
    }
}
