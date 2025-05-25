<?php
/**
 * View personalizzata per il rendering di schede e documenti di un singolo prodotto
 * con supporto WPML e fallback lingua di default.
 * Uso: [scheda_prodotto_dettaglio]
 */

if (! function_exists('ta_render_documenti_prodotto_view')) {
    function ta_render_documenti_prodotto_view($terms_data) {
        ob_start();
        if (empty($terms_data)) {
            echo '<p class="text-center text-muted">' . esc_html__('Non ci sono documenti da visualizzare','toro-ag') . '</p>';
            return ob_get_clean();
        }
        ?>
        <?php foreach ($terms_data as $term): ?>
            <?php $prod = $term['products'][0]; ?>
            <div class="row g-3">
                <div class="col-12">
                    <h5 class="section-title text-center my-3"><?= esc_html($prod['title']); ?></h5>
                </div>
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body small">
                            <?php if (!empty($prod['schede'])): ?>
                                <div class="schede mb-3">
                                    <strong><?= esc_html__('Schede Prodotto', 'toro-ag'); ?>:</strong>
                                    <?php foreach ($prod['schede'] as $group): ?>
                                        <div class="lang-group d-flex flex-wrap align-items-center mb-2">
                                            <?php if (function_exists('toroag_get_flag_html')): ?>
                                                <span class="me-2"><?= toroag_get_flag_html($group['lang']); ?></span>
                                            <?php endif; ?>
                                            <span class="lang-label text-uppercase small me-3"><?= esc_html($group['lang']); ?></span>
                                            <?php foreach ($group['items'] as $item): ?>
                                                <a href="<?= esc_url($item['url']); ?>" class="doc-link me-3" target="_blank" rel="noopener noreferrer">
                                                    <?php if (function_exists('toroag_get_icon_class')): ?>
                                                        <i class="bi <?= esc_attr(toroag_get_icon_class($item['url'])); ?> me-1"></i>
                                                    <?php endif; ?>
                                                    <?= esc_html($item['title']); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($prod['docs'])): ?>
                                <div class="documenti">
                                    <strong><?= esc_html__('Documenti Prodotto', 'toro-ag'); ?>:</strong>
                                    <?php foreach ($prod['docs'] as $group): ?>
                                        <div class="lang-group d-flex flex-wrap align-items-center mb-2">
                                            <?php if (function_exists('toroag_get_flag_html')): ?>
                                                <span class="me-2"><?= toroag_get_flag_html($group['lang']); ?></span>
                                            <?php endif; ?>
                                            <span class="lang-label text-uppercase small me-3"><?= esc_html($group['lang']); ?></span>
                                            <?php foreach ($group['items'] as $item): ?>
                                                <a href="<?= esc_url($item['url']); ?>" class="doc-link me-3" target="_blank" rel="noopener noreferrer">
                                                    <?php if (function_exists('toroag_get_icon_class')): ?>
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
        <?php endforeach;
        return ob_get_clean();
    }
}

add_action('init', function() {
    add_shortcode('scheda_prodotto_dettaglio', 'ta_scheda_prodotto_dettaglio_shortcode');
});

if (! function_exists('ta_scheda_prodotto_dettaglio_shortcode')) {
    function ta_scheda_prodotto_dettaglio_shortcode($atts) {
        global $post;
        if (!$post || get_post_type($post) !== 'prodotto') {
            return '';
        }

        // Lingua corrente e default
        $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default = apply_filters('wpml_default_language', null);

        // Funzione per raccogliere e raggruppare elementi
        $get_grouped = function($field, $meta_file_key) use ($post, $current, $default) {
            $groups = [];
            // ID prodotto per lingua
            $prod_id = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $current) ?: $post->ID;
            $pod = pods('prodotto', $prod_id, ['lang' => $current]);
            $items = $pod && $pod->exists() ? $pod->field($field) : [];
            // fallback meta
            if (empty($items)) {
                $prod_id_def = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $default) ?: $post->ID;
                foreach ((array) get_post_meta($prod_id_def, $field, false) as $raw) {
                    $items[] = $raw;
                }
            }
            foreach ((array) $items as $raw) {
                $id = is_array($raw) && isset($raw['ID']) ? intval($raw['ID']) : (is_object($raw) && isset($raw->ID) ? intval($raw->ID) : intval($raw));
                if (!$id) continue;
                // traduzione singolo elemento
                $elem_id = apply_filters('wpml_object_id', $id, 'scheda_prodotto', true, $current) ?: $id;
                // filtro lingua aggiuntiva
                $slug = wp_get_post_terms($elem_id, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? '';
                if (($current === 'it' && $slug !== 'italiano') || ($current !== 'it' && $slug === 'italiano')) {
                    continue;
                }
                // file allegato
                $file_id = get_post_meta($elem_id, $meta_file_key, true);
                if (!$file_id) continue;
                $url = wp_get_attachment_url($file_id);
                if (!$url) continue;
                $title = get_the_title($elem_id);
                $groups[$slug]['lang'] = $slug;
                $groups[$slug]['items'][] = compact('url', 'title');
            }
            // ordina gruppi per priorità lingua
            $order = ['italiano'=>0,'inglese'=>1,'francese'=>2,'spagnolo'=>3];
            uksort($groups, function($a, $b) use ($order) {
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
            });
            return array_values($groups);
        };

        $schede = $get_grouped('scheda_prodotto', 'scheda-prodotto');
        $docs   = $get_grouped('documento_prodotto', 'documento-prodotto');

        if (empty($schede) && empty($docs)) {
            return '<p class="text-center text-muted">' . esc_html__('Nessuna scheda o documento disponibile', 'toro-ag') . '</p>';
        }

        $terms_data = [[
            'term_name' => get_the_title($post),
            'products'  => [[
                'title' => get_the_title($post),
                'schede'=> $schede,
                'docs'  => $docs,
            ]],
        ]];
        return ta_render_documenti_prodotto_view($terms_data);
    }
}
