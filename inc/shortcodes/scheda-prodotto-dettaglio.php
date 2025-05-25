<?php
/**
 * View personalizzata per il rendering di schede e documenti di un singolo prodotto
 * Adattamento della view `documenti-download` di elenco_prodotti_con_dettagli
 * Riceve in input:
 *   - $terms_data: array di [ 'term_name', 'products' => [ ['title','schede','docs'], … ] ]
 *   - $layout: 'grid' o 'table' (ma in questo contesto useremo solo 'grid')
 */
if (! function_exists('ta_render_documenti_prodotto_view')) {
    function ta_render_documenti_prodotto_view($terms_data, $layout = 'grid') {
        ob_start();
        if (empty($terms_data)) {
            echo '<p class="text-center text-muted">' . esc_html__('Non ci sono documenti da visualizzare','toro-ag') . '</p>';
            return ob_get_clean();
        }
        ?>
        <?php foreach ( $terms_data as $term ): ?>
            <?php $prod = $term['products'][0]; ?>
            <?php if ($layout === 'grid'): ?>
                <div class="row g-3">
                    <div class="col-12">
                        <h5 class="section-title text-center my-3"><?= esc_html($prod['title']); ?></h5>
                    </div>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body small">
                                <?php // SCHEDE ?>
                                <?php if (! empty( $prod['schede'] ) ) : ?>
                                    <div class="schede mb-3">
                                        <strong><?= esc_html__( 'Schede Prodotto', 'toro-ag' ); ?>:</strong>
                                        <?php
                                            $schede_by_lang = [];
                                            foreach ( $prod['schede'] as $item ) {
                                                $schede_by_lang[ $item['lang'] ][] = $item;
                                            }
                                            foreach ( $schede_by_lang as $lang_slug => $items ) : ?>
                                                <div class="lang-group d-flex flex-wrap align-items-center mb-2">
                                                    <?php if ( function_exists('toroag_get_flag_html') ): ?>
                                                        <span class="me-2"><?= toroag_get_flag_html( $lang_slug ); ?></span>
                                                    <?php endif; ?>
                                                    <span class="lang-label text-uppercase small me-3"><?= esc_html($lang_slug); ?></span>
                                                    <?php foreach ( $items as $item ) : ?>
                                                        <a href="<?= esc_url($item['url']); ?>" class="doc-link me-3" target="_blank" rel="noopener noreferrer">
                                                            <?php if ( function_exists('toroag_get_icon_class') ): ?>
                                                                <i class="bi <?= esc_attr(toroag_get_icon_class($item['url'])); ?> me-1"></i>
                                                            <?php endif; ?>
                                                            <?= esc_html($item['title']); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>

                                <?php // DOCUMENTI ?>
                                <?php if (! empty( $prod['docs'] ) ) : ?>
                                    <div class="documenti">
                                        <strong><?= esc_html__( 'Documenti Prodotto', 'toro-ag' ); ?>:</strong>
                                        <?php
                                            $docs_by_lang = [];
                                            foreach ( $prod['docs'] as $item ) {
                                                $docs_by_lang[ $item['lang'] ][] = $item;
                                            }
                                            foreach ( $docs_by_lang as $lang_slug => $items ) : ?>
                                                <div class="lang-group d-flex flex-wrap align-items-center mb-2">
                                                    <?php if ( function_exists('toroag_get_flag_html') ): ?>
                                                        <span class="me-2"><?= toroag_get_flag_html( $lang_slug ); ?></span>
                                                    <?php endif; ?>
                                                    <span class="lang-label text-uppercase small me-3"><?= esc_html($lang_slug); ?></span>
                                                    <?php foreach ( $items as $item ) : ?>
                                                        <a href="<?= esc_url($item['url']); ?>" class="doc-link me-3" target="_blank" rel="noopener noreferrer">
                                                            <?php if ( function_exists('toroag_get_icon_class') ): ?>
                                                                <i class="bi <?= esc_attr(toroag_get_icon_class($item['url'])); ?> me-1"></i>
                                                            <?php endif; ?>
                                                            <?= esc_html($item['title']); ?>
                                                        </a>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach;
        return ob_get_clean();
    }
}


/**
 * Shortcode per mostrare la scheda e i documenti nel dettaglio di un singolo prodotto,
 * utilizzando la view personalizzata ta_render_documenti_prodotto_view().
 * Uso: [scheda_prodotto_dettaglio]
 */
add_action('init', function() {
    add_shortcode('scheda_prodotto_dettaglio', 'ta_scheda_prodotto_dettaglio_shortcode');
});

if (! function_exists('ta_scheda_prodotto_dettaglio_shortcode') ) {
    function ta_scheda_prodotto_dettaglio_shortcode($atts) {
        global $post;
        if (!$post || get_post_type($post) !== 'prodotto') {
            return '<!-- shortcode scheda_prodotto_dettaglio --><!-- DEBUG: contesto non prodotto -->';
        }

        // Lingua corrente
        $lang = defined('ICL_LANGUAGE_CODE')
            ? ICL_LANGUAGE_CODE
            : apply_filters('wpml_current_language', null);

        // Raccoglie file da meta key Pods, filtra per lingua e ordina
        $default_lang = apply_filters('wpml_default_language', null);
        $collect = function($meta_key, $file_meta_key) use ($lang, $default_lang) {
            $order = ['italiano'=>0, 'inglese'=>1, 'francese'=>2, 'spagnolo'=>3];
            // Ottieni relazione Pods con fallback lingua di default (CPT unici)
            if (! function_exists('pods')) {
                return [];
            }
            // prova con lingua corrente
            $params = ['lang' => $lang];
            $prod_pod = pods('prodotto', get_the_ID(), $params);
            if (! $prod_pod->exists() || empty($prod_pod->field($meta_key))) {
                // fallback default lang
                $prod_pod = pods('prodotto', get_the_ID(), ['lang' => $default_lang]);
            }
            if (! $prod_pod->exists()) {
                return [];
            }
            $raw_items = $prod_pod->field($meta_key);
            if (empty($raw_items)) {
                return [];
            }
            $out = [];
            foreach ((array) $raw_items as $item) {
                // estrai ID relazione
                $did = is_array($item) && isset($item['ID']) ? intval($item['ID']) : (is_object($item) && isset($item->ID) ? intval($item->ID) : intval($item));
                if (! $did) continue;
                // lingua aggiuntiva
                $slug = wp_get_post_terms($did, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? 'altre';
                if (($lang === 'it' && $slug !== 'italiano') || ($lang !== 'it' && $slug === 'italiano')) {
                    continue;
                }
                // id file dentro CPT
                $fid = get_post_meta($did, $file_meta_key, true);
                if (! $fid) continue;
                $url = wp_get_attachment_url($fid);
                if (! $url) continue;
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

        // Prepara dati
        $schede = $collect('scheda_prodotto','scheda-prodotto');
        $docs   = $collect('documento_prodotto','documento-prodotto');

        if (empty($schede) && empty($docs)) {
            return '<p class="text-center text-muted">' . esc_html__('Nessuna scheda o documento disponibile', 'toro-ag') . '</p>';
        }

        $terms_data = [[
            'term_name'=> get_the_title($post),
            'products'=> [[
                'title'=> get_the_title($post),
                'schede'=> $schede,
                'docs'  => $docs,
            ]],
        ]];

        // Usa la view personalizzata
        return ta_render_documenti_prodotto_view($terms_data, 'grid');
    }
}
