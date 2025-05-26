<?php
/**
 * View personalizzata per il rendering di schede e documenti di un singolo prodotto
 * con supporto WPML e fallback lingua di default.
 * Uso: [scheda_prodotto_dettaglio]
 */

if (! function_exists('ta_render_documenti_prodotto_view')) {
    function ta_render_documenti_prodotto_view($terms_data) {
        ob_start();
        // Nessun contenuto da mostrare
        if (empty($terms_data) || (empty($terms_data[0]['products'][0]['schede']) && empty($terms_data[0]['products'][0]['docs']))) {
            echo '<p class="text-center text-muted">' . esc_html__('Nessuna scheda o documento disponibile', 'toro-ag') . '</p>';
            return ob_get_clean();
        }
        $prod = $terms_data[0]['products'][0];
        // Conteggi per pluralizzazione
        $schede_count = count($prod['schede'] ?: []);
        $docs_count   = count($prod['docs'] ?: []);
        ?>
        <div class="product-documents">
            <div class="row g-3">
                <div class="col-12">
                    <?php if ($schede_count > 0): ?>
                        <div class="card shadow-sm mb-3">
                            <div class="card-header">
                                <strong><?php echo ($schede_count === 1) ? esc_html__('Scheda Prodotto', 'toro-ag') : esc_html__('Schede Prodotto', 'toro-ag'); ?></strong>
                            </div>
                            <div class="card-body small">
                                <?php foreach ($prod['schede'] as $group): ?>
                                    <div class="row align-items-start mb-2">
                                        <div class="col-auto">
                                            <?php if ($group['lang'] !== 'italiano' && function_exists('toroag_get_flag_html')): ?>
                                                <?= toroag_get_flag_html($group['lang']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <?php foreach ($group['items'] as $item): ?>
                                                <a href="<?= esc_url($item['url']); ?>" class="doc-link d-block mb-1" target="_blank" rel="noopener noreferrer">
                                                    <?php if (function_exists('toroag_get_icon_class')): ?>
                                                        <i class="bi <?= esc_attr(toroag_get_icon_class($item['url'])); ?> me-1"></i>
                                                    <?php endif; ?>
                                                    <?= esc_html($item['title']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($docs_count > 0): ?>
                        <div class="card shadow-sm mb-4">
                            <div class="card-header">
                                <strong><?php echo ($docs_count === 1) ? esc_html__('Documento Prodotto', 'toro-ag') : esc_html__('Documenti Prodotto', 'toro-ag'); ?></strong>
                            </div>
                            <div class="card-body small">
                                <?php foreach ($prod['docs'] as $group): ?>
                                    <div class="row align-items-start mb-2">
                                        <div class="col-auto">
                                            <?php if ($group['lang'] !== 'italiano' && function_exists('toroag_get_flag_html')): ?>
                                                <?= toroag_get_flag_html($group['lang']); ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col">
                                            <?php foreach ($group['items'] as $item): ?>
                                                <a href="<?= esc_url($item['url']); ?>" class="doc-link d-block mb-1" target="_blank" rel="noopener noreferrer">
                                                    <?php if (function_exists('toroag_get_icon_class')): ?>
                                                        <i class="bi <?= esc_attr(toroag_get_icon_class($item['url'])); ?> me-1"></i>
                                                    <?php endif; ?>
                                                    <?= esc_html($item['title']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Registra lo shortcode scheda_prodotto_dettaglio
add_action('init', function() {
    add_shortcode('scheda_prodotto_dettaglio', 'ta_scheda_prodotto_dettaglio_shortcode');
});

if (! function_exists('ta_scheda_prodotto_dettaglio_shortcode')) {
    function ta_scheda_prodotto_dettaglio_shortcode($atts) {
        global $post;
        if (!$post || get_post_type($post) !== 'prodotto') {
            return '';
        }

        $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default = apply_filters('wpml_default_language', null);

        // Raccoglie e raggruppa elementi schede e documenti
        $get_grouped = function($field, $meta_file_key) use ($post, $current, $default) {
            $groups = [];
            $prod_id = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $current) ?: $post->ID;
            $pod = pods('prodotto', $prod_id, ['lang' => $current]);
            $items = ($pod && $pod->exists()) ? $pod->field($field) : [];
            if (empty($items)) {
                $prod_id_def = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $default) ?: $post->ID;
                foreach ((array) get_post_meta($prod_id_def, $field, false) as $raw) {
                    $items[] = $raw;
                }
            }
            foreach ((array) $items as $raw) {
                $id = is_array($raw) && isset($raw['ID']) ? intval($raw['ID']) : (is_object($raw) && isset($raw->ID) ? intval($raw->ID) : intval($raw));
                if (!$id) continue;
                $elem_id = apply_filters('wpml_object_id', $id, $field === 'scheda_prodotto' ? 'scheda_prodotto' : 'documento_prodotto', true, $current) ?: $id;
                $slug = wp_get_post_terms($elem_id, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? '';
                if (($current === 'it' && $slug !== 'italiano') || ($current !== 'it' && $slug === 'italiano')) {
                    continue;
                }
                $file_id = get_post_meta($elem_id, $meta_file_key, true);
                if (!$file_id) continue;
                $url = wp_get_attachment_url($file_id);
                if (!$url) continue;
                $title = get_the_title($elem_id);
                $groups[$slug]['items'][] = compact('url', 'title');
                $groups[$slug]['lang'] = $slug;
            }
            // Ordina per priorità lingua
            $order = ['italiano'=>0, 'inglese'=>1, 'francese'=>2, 'spagnolo'=>3];
            uksort($groups, function($a, $b) use ($order) {
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
            });
            return array_values($groups);
        };

        $schede = $get_grouped('scheda_prodotto', 'scheda-prodotto');
        $docs   = $get_grouped('documento_prodotto', 'documento-prodotto');

        $terms_data = [[
            'term_name' => '',
            'products'  => [[
                'title' => '',
                'schede'=> $schede,
                'docs'  => $docs,
            ]],
        ]];

        return ta_render_documenti_prodotto_view($terms_data);
    }
}
