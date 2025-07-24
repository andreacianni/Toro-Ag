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
                                    <div class="row align-items-center mb-2">
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
                                    <div class="row align-items-center mb-2">
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
                // 🔧 FIX WPML: Mostra documenti appropriati per lingua corrente
                if ($current === 'it') {
                    // Italiano: mostra solo documenti italiani (o senza lingua specificata)
                    if (!empty($slug) && $slug !== 'italiano') {
                        continue;
                    }
                } else {
                    // Altre lingue: mostra documenti nella lingua corrente O documenti italiani come fallback
                    $lang_map = ['en' => 'inglese', 'fr' => 'francese', 'es' => 'spagnolo'];
                    $target_lang = $lang_map[$current] ?? '';
                    
                    // Accetta documenti nella lingua target O documenti italiani (fallback)
                    if (!empty($slug) && $slug !== $target_lang && $slug !== 'italiano') {
                        continue;
                    }
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

/**
 * Shortcode per mostrare schede e documenti associati a un termine della tassonomia tipo_di_prodotto
 * Uso: [scheda_prodotto_tipo_dettaglio]
 */
add_action('init', function() {
    add_shortcode('scheda_prodotto_tipo_dettaglio', 'ta_scheda_prodotto_tipo_shortcode');
});

if (! function_exists('ta_scheda_prodotto_tipo_shortcode')) {
    function ta_scheda_prodotto_tipo_shortcode($atts) {
        if (!is_tax('tipo_di_prodotto')) {
            return '';
        }

        $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default = apply_filters('wpml_default_language', null);
        $term = get_queried_object();
        if (!$term || !isset($term->term_id)) {
            return '';
        }

        // Funzione per raccogliere e raggruppare elementi schede e documenti dal termine
        $get_grouped_term = function($field, $meta_file_key) use ($term, $current, $default) {
            $groups = [];
            
            // 🔧 DEBUG: Log info base
            if (isset($_GET['debug_docs'])) {
                error_log("🔧 DEBUG DOCS: Field={$field}, Current Lang={$current}, Term ID={$term->term_id}");
            }
            
            $term_id = apply_filters('wpml_object_id', $term->term_id, 'tipo_di_prodotto', true, $current) ?: $term->term_id;
            $pod = pods('tipo_di_prodotto', $term_id, ['lang' => $current]);
            $items = ($pod && $pod->exists()) ? $pod->field($field) : [];
            
            // 🔧 DEBUG: Items da PODS
            if (isset($_GET['debug_docs'])) {
                error_log("🔧 DEBUG DOCS: PODS items count=" . count($items));
            }
            
            if (empty($items)) {
                $term_id_def = apply_filters('wpml_object_id', $term->term_id, 'tipo_di_prodotto', true, $default) ?: $term->term_id;
                foreach ((array) get_term_meta($term_id_def, $field, false) as $raw) {
                    $items[] = $raw;
                }
                
                // 🔧 DEBUG: Items da term_meta
                if (isset($_GET['debug_docs'])) {
                    error_log("🔧 DEBUG DOCS: term_meta items count=" . count($items));
                }
            }
            foreach ((array) $items as $raw) {
                $id = is_array($raw) && isset($raw['ID']) ? intval($raw['ID']) : (is_object($raw) && isset($raw->ID) ? intval($raw->ID) : intval($raw));
                if (!$id) continue;
                
                // 🔧 DEBUG: Processing item
                if (isset($_GET['debug_docs'])) {
                    error_log("🔧 DEBUG DOCS: Processing item ID={$id}");
                }
                
                $elem_id = apply_filters('wpml_object_id', $id, $field === 'scheda_prodotto_tipo' ? 'scheda_prodotto' : 'documento_prodotto', true, $current) ?: $id;
                $slug = wp_get_post_terms($elem_id, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? '';
                
                // 🔧 DEBUG: Language info
                if (isset($_GET['debug_docs'])) {
                    error_log("🔧 DEBUG DOCS: Item {$elem_id}, Slug='{$slug}', Current='{$current}'");
                }
                
                // 🔧 FIX WPML: Mostra documenti appropriati per lingua corrente
                if ($current === 'it') {
                    // Italiano: mostra solo documenti italiani (o senza lingua specificata)
                    if (!empty($slug) && $slug !== 'italiano') {
                        if (isset($_GET['debug_docs'])) error_log("🔧 DEBUG DOCS: SKIP IT - slug not italiano");
                        continue;
                    }
                } else {
                    // Altre lingue: mostra documenti nella lingua corrente O documenti italiani come fallback
                    $lang_map = ['en' => 'inglese', 'fr' => 'francese', 'es' => 'spagnolo'];
                    $target_lang = $lang_map[$current] ?? '';
                    
                    if (isset($_GET['debug_docs'])) {
                        error_log("🔧 DEBUG DOCS: Target lang='{$target_lang}' for current='{$current}'");
                    }
                    
                    // Accetta documenti nella lingua target O documenti italiani (fallback)
                    if (!empty($slug) && $slug !== $target_lang && $slug !== 'italiano') {
                        if (isset($_GET['debug_docs'])) error_log("🔧 DEBUG DOCS: SKIP EN - slug '{$slug}' not target '{$target_lang}' or 'italiano'");
                        continue;
                    }
                }
                
                $file_id = get_post_meta($elem_id, $meta_file_key, true);
                if (!$file_id) {
                    if (isset($_GET['debug_docs'])) error_log("🔧 DEBUG DOCS: SKIP - no file_id for key '{$meta_file_key}'");
                    continue;
                }
                
                $url = wp_get_attachment_url($file_id);
                if (!$url) {
                    if (isset($_GET['debug_docs'])) error_log("🔧 DEBUG DOCS: SKIP - no URL for file_id '{$file_id}'");
                    continue;
                }
                
                $title = get_the_title($elem_id);
                $groups[$slug]['items'][] = compact('url', 'title');
                $groups[$slug]['lang'] = $slug;
                
                // 🔧 DEBUG: Added to group
                if (isset($_GET['debug_docs'])) {
                    error_log("🔧 DEBUG DOCS: ADDED to group '{$slug}' - {$title}");
                }
            }
            // Ordina per priorità lingua
            $order = ['italiano'=>0, 'inglese'=>1, 'francese'=>2, 'spagnolo'=>3];
            uksort($groups, function($a, $b) use ($order) {
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
            });
            return array_values($groups);
        };

        $schede = $get_grouped_term('scheda_prodotto_tipo', 'scheda-prodotto');
        $docs   = $get_grouped_term('documento_prodotto_tipo', 'documento-prodotto');

        $terms_data = [[
            'term_name' => $term->name,
            'products'  => [[
                'title' => $term->name,
                'schede'=> $schede,
                'docs'  => $docs,
            ]],
        ]];

        return ta_render_documenti_prodotto_view($terms_data);
    }
}
