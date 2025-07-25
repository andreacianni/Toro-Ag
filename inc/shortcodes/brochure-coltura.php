<?php
/**
 * Shortcode per mostrare brochure associate a un termine della tassonomia coltura
 * Uso: [brochure_coltura_dettaglio layout="card" titolo="Le nostre brochure"]
 * 
 * Parametri:
 * - layout: "card" (con immagini) o "list" (compatto) - default: "card"
 * - titolo: titolo personalizzato da mostrare sopra le brochure
 */

if (! function_exists('ta_render_brochure_coltura_view')) {
    function ta_render_brochure_coltura_view($brochure_data, $layout = 'card') {
        ob_start();
        
        // Nessun contenuto da mostrare
        if (empty($brochure_data)) {
            echo '<p class="text-center text-muted">' . esc_html__('Nessuna brochure disponibile', 'toro-ag') . '</p>';
            return ob_get_clean();
        }

        $brochure_count = count($brochure_data);
        ?>
        <div class="coltura-brochure">
            <?php if ($layout === 'card'): ?>
                <!-- Titolo Sezione Brochure -->
                <h5 class="fw-bold border-bottom px-3 py-2 mb-4">
                    <?= esc_html__('Scarica la Brochure', 'toro-ag'); ?>
                </h5>
                
                <!-- Layout Card con Immagini -->
                <?php foreach ($brochure_data as $group): ?>
                    <?php foreach ($group['items'] as $item): ?>
                        <div class="card h-100 brochure-card shadow-sm mb-4">
                            <?php if (!empty($item['image_url'])): ?>
                                <a href="<?= esc_url($item['url']); ?>" target="_blank" rel="noopener noreferrer" class="d-block h-100">
                                    <img src="<?= esc_url($item['image_url']); ?>" class="card-img-top h-100" alt="<?= esc_attr($item['title']); ?>" style="object-fit: cover; object-position: center;">
                                </a>
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light h-100">
                                    <?php if (function_exists('toroag_get_icon_class')): ?>
                                        <i class="<?= esc_attr(toroag_get_icon_class($item['url'])); ?> display-4 text-muted"></i>
                                    <?php else: ?>
                                        <i class="bi bi-file-earmark-text display-4 text-muted"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body text-center border-top">
                                <a href="<?= esc_url($item['url']); ?>" class="text-decoration-none" target="_blank" rel="noopener noreferrer">
                                    <?php if ($group['lang'] !== 'italiano' && function_exists('toroag_get_flag_html')): ?>
                                        <?= toroag_get_flag_html($group['lang']); ?> 
                                    <?php endif; ?>
                                    <?= esc_html($item['title']); ?>
                                    <?php if (function_exists('toroag_get_icon_class')): ?>
                                         <i class="<?= esc_attr(toroag_get_icon_class($item['url'])); ?>"></i>
                                    <?php endif; ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Layout List Compatto -->
                <?php foreach ($brochure_data as $group): ?>
                    <div class="row align-items-center mb-2">
                        <div class="col-auto">
                            <?php if ($group['lang'] !== 'italiano' && function_exists('toroag_get_flag_html')): ?>
                                <?= toroag_get_flag_html($group['lang']); ?>
                            <?php endif; ?>
                        </div>
                        <div class="col">
                            <?php foreach ($group['items'] as $item): ?>
                                <a href="<?= esc_url($item['url']); ?>" class="doc-link d-block mb-1" target="_blank" rel="noopener noreferrer">
                                    <?= esc_html($item['title']); ?>
                                    <?php if (function_exists('toroag_get_icon_class')): ?>
                                        <i class="bi <?= esc_attr(toroag_get_icon_class($item['url'])); ?>"></i>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}

// Registra lo shortcode brochure_coltura_dettaglio
add_action('init', function() {
    add_shortcode('brochure_coltura_dettaglio', 'ta_brochure_coltura_dettaglio_shortcode');
});

if (! function_exists('ta_brochure_coltura_dettaglio_shortcode')) {
    function ta_brochure_coltura_dettaglio_shortcode($atts) {
        // Verifica che siamo su una pagina archivio di coltura
        if (!is_tax('coltura')) {
            return '';
        }

        // Parse degli attributi
        $atts = shortcode_atts([
            'layout' => 'card', // 'card' o 'list'
            'titolo' => ''       // titolo personalizzato
        ], $atts, 'brochure_coltura_dettaglio');

        $layout = in_array($atts['layout'], ['card', 'list']) ? $atts['layout'] : 'card';
        $titolo = sanitize_text_field($atts['titolo']);

        $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default = apply_filters('wpml_default_language', null);
        $term = get_queried_object();
        
        if (!$term || !isset($term->term_id)) {
            return '';
        }

        // Funzione per raccogliere e raggruppare brochure dal termine coltura
        $get_grouped_brochure = function() use ($term, $current, $default) {
            $groups = [];
            
            // Prova prima con la lingua corrente
            $term_id = apply_filters('wpml_object_id', $term->term_id, 'coltura', true, $current) ?: $term->term_id;
            $pod = pods('coltura', $term_id, ['lang' => $current]);
            $items = ($pod && $pod->exists()) ? $pod->field('brochure_coltura') : [];
            
            // Fallback alla lingua di default se non trova nulla
            if (empty($items)) {
                $term_id_def = apply_filters('wpml_object_id', $term->term_id, 'coltura', true, $default) ?: $term->term_id;
                foreach ((array) get_term_meta($term_id_def, 'brochure_coltura', false) as $raw) {
                    $items[] = $raw;
                }
            }
            
            foreach ((array) $items as $raw) {
                // Estrai l'ID dalla struttura Pods
                $id = is_array($raw) && isset($raw['ID']) ? intval($raw['ID']) : 
                      (is_object($raw) && isset($raw->ID) ? intval($raw->ID) : intval($raw));
                
                if (!$id) continue;
                
                // Traduci l'ID della brochure nella lingua corrente
                $brochure_id = apply_filters('wpml_object_id', $id, 'brochure_coltura', true, $current) ?: $id;
                
                // Verifica la lingua della brochure
                $slug = wp_get_post_terms($brochure_id, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? '';
                
                // Filtra per lingua (italiano per 'it', non-italiano per altre lingue)
                if (($current === 'it' && $slug !== 'italiano') || ($current !== 'it' && $slug === 'italiano')) {
                    continue;
                }
                
                // Recupera il file della brochure
                $file_id = get_post_meta($brochure_id, 'brochure-file', true);
                if (!$file_id) continue;
                
                $url = wp_get_attachment_url($file_id);
                if (!$url) continue;
                
                $title = get_the_title($brochure_id);
                
                // Recupera l'immagine della brochure (opzionale)
                $image_id = get_post_meta($brochure_id, 'brochure-img', true);
                $image_url = $image_id ? wp_get_attachment_url($image_id) : '';
                
                $groups[$slug]['items'][] = compact('url', 'title', 'image_url');
                $groups[$slug]['lang'] = $slug;
            }
            
            // Ordina per priorità lingua
            $order = ['italiano'=>0, 'inglese'=>1, 'francese'=>2, 'spagnolo'=>3];
            uksort($groups, function($a, $b) use ($order) {
                return ($order[$a] ?? 99) <=> ($order[$b] ?? 99);
            });
            
            return array_values($groups);
        };

        $brochure_data = $get_grouped_brochure();

        // Genera l'output con titolo opzionale
        $output = '';
        
        // Aggiungi il titolo se presente e non vuoto
        if (!empty($titolo)) {
            $output .= '<h3 class="text-start fs-4 fw-bold border-bottom ps-1 py-2 my-4">' . esc_html($titolo) . '</h3>';
        }

        $output .= ta_render_brochure_coltura_view($brochure_data, $layout);
        
        return $output;
    }
}