<?php
/**
 * Shortcode per mostrare la scheda e i documenti nel dettaglio di un singolo prodotto,
 * riutilizzando la view `documenti-download` di elenco_prodotti_con_dettagli per l'HTML.
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

        // Helper: raccoglie file da meta key, filtra per lingua e ordina
        $collect = function($meta_key, $file_meta_key) use ($lang) {
            $order = ['italiano'=>0, 'inglese'=>1, 'francese'=>2, 'spagnolo'=>3];
            $out = [];
            foreach ((array) get_post_meta(get_the_ID(), $meta_key, false) as $did) {
                $slug = wp_get_post_terms($did, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? 'altre';
                // visibilità
                if (($lang === 'it' && $slug !== 'italiano') || ($lang !== 'it' && $slug === 'italiano')) {
                    continue;
                }
                $fid = get_post_meta($did, $file_meta_key, true);
                if (!$fid) continue;
                $url = wp_get_attachment_url($fid);
                if (!$url) continue;
                $out[] = [
                    'title'=> get_the_title($did),
                    'url'  => $url,
                    'lang' => $slug,
                    'prio' => $order[$slug] ?? 99,
                ];
            }
            usort($out, function($a,$b){ return $a['prio'] <=> $b['prio']; });
            return $out;
        };

        // Prepara dati per la view
        $schede = $collect('scheda_prodotto','scheda-prodotto');
        $docs   = $collect('documento_prodotto','documento-prodotto');

        if (empty($schede) && empty($docs)) {
            return '<!-- shortcode scheda_prodotto_dettaglio --><!-- DEBUG: Nessuna scheda o documento disponibile -->';
        }

        // Struttura identica a elenco_prodotti_con_dettagli
        $terms_data = [ [
            'term_name'=> get_the_title($post),
            'products' => [ [
                'title'=> get_the_title($post),
                'schede'=> $schede,
                'docs'  => $docs,
            ] ],
        ] ];

        // Renderizza la view esistente (layout grid di default)
        return toroag_load_view('documenti-download', [
            'terms_data'=> $terms_data,
            'layout'    => 'grid',
        ]);
    }
}
