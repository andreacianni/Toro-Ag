<?php
/**
 * Toro-AG: Generic grid shortcode handler with context-aware term detection
 */

// Config map: defines available grid types and their settings
$toro_grid_config = [
    'tipi_prod'           => ['mode'=>'terms',           'taxonomy'=>'tipo_di_prodotto', 'post_type'=>null,       'image_field'=>'tipo-thumb'],
    'prodotti'            => ['mode'=>'posts',           'taxonomy'=>'tipo_di_prodotto', 'post_type'=>'prodotto', 'image_field'=>'featured'],
    'colture'             => ['mode'=>'terms',           'taxonomy'=>'coltura',          'post_type'=>null,       'image_field'=>'col_thumb'],
    'prodotti_x_coltura'  => ['mode'=>'posts',           'taxonomy'=>'coltura',          'post_type'=>'prodotto', 'image_field'=>'col_thumb'],
    'tipi_x_coltura'      => ['mode'=>'grouped_terms',   'taxonomy'=>'coltura',          'post_type'=>'prodotto', 'image_field'=>'featured',  'group_by'=>'tipo_di_prodotto'],
    'colture_for_post'    => ['mode'=>'terms_for_post',  'taxonomy'=>'coltura',          'post_type'=>null,       'image_field'=>'col_thumb'],
];

// Shortcode registration
define('TORO_GRID_SHORTCODE', 'toro_grid');
function toro_ag_grid_shortcode($atts) {
    global $toro_grid_config;
    $available_types = implode(', ', array_keys($toro_grid_config));
    $atts = shortcode_atts([
        'type' => '',  // key in the config map
        'term' => '',  // term slug when needed
    ], $atts, TORO_GRID_SHORTCODE);

    // Validate 'type'
    if (empty($atts['type']) || !isset($toro_grid_config[$atts['type']])) {
        return sprintf(
            '<div class="toro-error"><strong>Errore:</strong> parametro <code>type</code> mancante o non valido.<br>' .
            'Tipi disponibili: <code>%s</code>.<br>Esempio: <code>[%1$s type="prodotti"]</code></div>',
            esc_html($available_types), TORO_GRID_SHORTCODE
        );
    }

    $cfg = $toro_grid_config[$atts['type']];
    $items = [];

    // Auto-detect 'term' in taxonomy archives for posts and grouped_terms
    if (in_array($cfg['mode'], ['posts', 'grouped_terms']) && empty($atts['term'])) {
        if (is_tax($cfg['taxonomy'])) {
            $term_obj     = get_queried_object();
            $atts['term'] = $term_obj->slug;
        }
    }

    switch ($cfg['mode']) {
        case 'terms':
            // Always show all terms
            $items = get_terms([
                'taxonomy'   => $cfg['taxonomy'],
                'hide_empty' => false,
            ]);
            break;

        case 'posts':
            // List posts for a taxonomy term
            if (empty($atts['term'])) {
                return sprintf(
                    '<div class="toro-error"><strong>Errore:</strong> per il tipo <code>%s</code> devi essere in un archivio tassonomia o passare <code>term</code>.<br>' .
                    'Esempio: <code>[%1$s type="%1$s"]</code></div>',
                    esc_html($atts['type']), TORO_GRID_SHORTCODE
                );
            }
            $items = get_posts([
                'post_type'      => $cfg['post_type'],
                'posts_per_page' => -1,
                'tax_query'      => [[
                    'taxonomy' => $cfg['taxonomy'],
                    'field'    => 'slug',
                    'terms'    => $atts['term'],
                ]],
            ]);
            break;

        case 'grouped_terms':
            // Group posts by another taxonomy
            if (empty($atts['term'])) {
                return sprintf(
                    '<div class="toro-error"><strong>Errore:</strong> per il tipo <code>%s</code> devi essere in un archivio tassonomia o passare <code>term</code>.<br>' .
                    'Esempio: <code>[%1$s type="%1$s"]</code></div>',
                    esc_html($atts['type']), TORO_GRID_SHORTCODE
                );
            }
            $post_ids = get_posts([
                'post_type'      => $cfg['post_type'],
                'posts_per_page' => -1,
                'fields'         => 'ids',
                'tax_query'      => [[
                    'taxonomy' => $cfg['taxonomy'],
                    'field'    => 'slug',
                    'terms'    => $atts['term'],
                ]],
            ]);
            $items = get_terms([
                'taxonomy'   => $cfg['group_by'],
                'hide_empty' => false,
                'object_ids' => $post_ids,
            ]);
            break;

        case 'terms_for_post':
            // List terms attached to a single post
            if (!is_singular('prodotto')) {
                return '<div class="toro-error"><strong>Errore:</strong> questo shortcode va usato solo in pagine singole di prodotto.</div>';
            }
            $terms = get_the_terms(get_the_ID(), $cfg['taxonomy']);
            $items = is_array($terms) ? $terms : [];
            break;

        default:
            return sprintf(
                '<div class="toro-error"><strong>Errore interno:</strong> modalità <code>%s</code> non riconosciuta.</div>',
                esc_html($cfg['mode'])
            );
    }

    // Render the grid view
    ob_start();
    set_query_var('toro_ag_grid_items',      $items);
    set_query_var('toro_ag_grid_image_field', $cfg['image_field']);
    get_template_part('inc/views/shortcode', 'grid');
    return ob_get_clean();
}
add_shortcode(TORO_GRID_SHORTCODE, 'toro_ag_grid_shortcode');