<?php
/**
 * File: inc/shortcodes/grid.php
 * Toro-AG: Separate grid shortcodes for each context, passing wrapper classes directly
 * Internationalized with WPML
 *
 * Usage examples:
 *
 * <!-- /tipi-di-prodotto/ -->
 * [toro_tipi_prod]
 *
 * <!-- /colture/ -->
 * [toro_colture]
 *
 * <!-- /tipi-di-prodotto/termine/ -->
 * [toro_prodotti_tipo]
 *
 * <!-- /prodotto/slug/ -->
 * [toro_culture_prodotto]
 *
 * <!-- /colture/termine/ -->
 * [toro_tipi_per_coltura]
 * 
 * 
 * 
 * [toro_prodotti_page] - Mostra in griglia i prodotti selezionati nel campo Relationship 'prodotti' da usare all’interno di una Page.
 * [toro_colture_page] - Mostra in griglia le colture selezionate nel campo Relationship 'applicazioni' da usare all’interno di una Page.
 */

// Include in functions.php:
// require_once get_stylesheet_directory() . '/inc/shortcodes/grid.php';

add_action('init', function() {
    add_shortcode('toro_tipi_prod',        'toro_grid_tipi_prod_shortcode');
    add_shortcode('toro_colture',          'toro_grid_colture_shortcode');
    add_shortcode('toro_prodotti_tipo',    'toro_grid_prodotti_tipo_shortcode');
    add_shortcode('toro_culture_prodotto', 'toro_grid_culture_prodotto_shortcode');
    add_shortcode('toro_tipi_per_coltura', 'toro_grid_tipi_per_coltura_shortcode');
    add_shortcode('toro_prodotti_page', 'toro_grid_prodotti_page_shortcode');
    add_shortcode('toro_colture_page', 'toro_grid_colture_page_shortcode');
});

/**
 * Shared renderer: uses view inc/views/shortcode-grid.php
 * Accepts an optional $wrapper_class for CSS
 */
function toro_ag_render_grid_view($items, $img_field, $wrapper_class = '') {
    ob_start();
    set_query_var('toro_ag_grid_items',       $items);
    set_query_var('toro_ag_grid_image_field', $img_field);
    set_query_var('toro_ag_grid_wrapper_class', $wrapper_class);
    get_template_part('inc/views/shortcode', 'grid');
    return ob_get_clean();
}

/**
 * /tipi-di-prodotto/ - static: all tipo_di_prodotto terms
 */
function toro_grid_tipi_prod_shortcode() {
    $terms = get_terms(['taxonomy'=>'tipo_di_prodotto','hide_empty'=>false]);
    return toro_ag_render_grid_view($terms, 'tipo-thumb', 'toro-grid--tipi-prod');
}

/**
 * /colture/ - static: all coltura terms
 */
function toro_grid_colture_shortcode() {
    $terms = get_terms(['taxonomy'=>'coltura','hide_empty'=>false]);
    return toro_ag_render_grid_view($terms, 'col_thumb', 'toro-grid--colture');
}

/**
 * /tipi-di-prodotto/termine/ - products for current tipo_di_prodotto term
 */
function toro_grid_prodotti_tipo_shortcode() {
    if (!is_tax('tipo_di_prodotto')) {
        return '<div class="toro-error">' . esc_html__( 'Please use this shortcode only on a Product Type archive.', 'toro-ag' ) . '</div>';
    }
    $term = get_queried_object();
    $products = get_posts([
        'post_type'=>'prodotto',
        'posts_per_page'=>-1,
        'tax_query'=>[[ 'taxonomy'=>'tipo_di_prodotto','field'=>'slug','terms'=>$term->slug ]]
    ]);
    return toro_ag_render_grid_view($products, 'featured', 'toro-grid--prodotti-tipo');
}

/**
 * /prodotto/slug/ - colture for current prodotto
 */
function toro_grid_culture_prodotto_shortcode() {
    if (!is_singular('prodotto')) {
        return '<div class="toro-error">' . esc_html__( 'Please use this shortcode only on single Product pages.', 'toro-ag' ) . '</div>';
    }
    $terms = get_the_terms(get_the_ID(), 'coltura');
    $items = is_array($terms) ? $terms : [];
    return toro_ag_render_grid_view($items, 'col_thumb', 'toro-grid--culture-prodotto');
}

/**
 * /colture/termine/ - nested: tipi_di_prodotto grouped by products under current coltura
 */
function toro_grid_tipi_per_coltura_shortcode() {
    if (!is_tax('coltura')) {
        return '<div class="toro-error">' . esc_html__( 'Please use this shortcode only on a Cultures archive.', 'toro-ag' ) . '</div>';
    }
    $current_term = get_queried_object();
    $product_ids = get_posts([
        'post_type'=>'prodotto',
        'posts_per_page'=>-1,
        'fields'=>'ids',
        'tax_query'=>[[ 'taxonomy'=>'coltura','field'=>'term_id','terms'=>$current_term->term_id ]]
    ]);
    if (empty($product_ids)) {
        return '<div class="toro-error">' . esc_html__( 'No products associated with this culture.', 'toro-ag' ) . '</div>';
    }
    $type_terms = get_terms(['taxonomy'=>'tipo_di_prodotto','hide_empty'=>false,'object_ids'=>$product_ids]);
    if (empty($type_terms)) {
        return '<div class="toro-error">' . esc_html__( 'No Product Types found for this culture.', 'toro-ag' ) . '</div>';
    }
    $output = '';
    foreach ($type_terms as $type_term) {
        $output .= sprintf('<h5 class="text-bg-dark px-3 py-2 my-4 rounded-2"><a href="%1$s">%2$s</a></h5>', esc_url(get_term_link($type_term)), esc_html($type_term->name));
        $products = get_posts([
            'post_type'=>'prodotto',
            'posts_per_page'=>-1,
            'tax_query'=>[
                ['taxonomy'=>'tipo_di_prodotto','field'=>'term_id','terms'=>$type_term->term_id],
                ['taxonomy'=>'coltura','field'=>'term_id','terms'=>$current_term->term_id],
            ],
        ]);
        $nested_html = toro_ag_render_grid_view($products,'featured','toro-grid--tipi-per-coltura nested-grid');
        $output .= $nested_html;
    }
    return $output;
}

/**
 * [toro_prodotti_page]
 * Recupera il meta 'prodotti', normalizza anche un singolo ID in array,
 * e mostra la grid dei prodotti selezionati.
 */
function toro_grid_prodotti_page_shortcode() {
    if ( ! is_page() ) {
        return '';
    }

    // DEBUG: raw prodotti meta
    $meta_raw = get_post_meta( get_the_ID(), 'prodotti', true );
    echo "<!-- DEBUG: raw prodotti meta = " . esc_html( $meta_raw ) . " -->\n";
    if ( empty( $meta_raw ) ) {
        echo "<!-- DEBUG: meta vuoto, esco -->\n";
        return '';
    }

    // Estrai tutti i numeri (ogni ID) da qualunque stringa
    preg_match_all( '/\d+/', $meta_raw, $matches );
    $ids = array_map( 'intval', $matches[0] );
    echo "<!-- DEBUG: parsed prodotti IDs = " . implode( ',', $ids ) . " -->\n";
    if ( empty( $ids ) ) {
        echo "<!-- DEBUG: nessun ID valido, esco -->\n";
        return '';
    }

    $products = get_posts( array(
        'post_type'      => 'prodotto',
        'posts_per_page' => -1,
        'post__in'       => $ids,
        'orderby'        => 'post__in',
    ) );

    echo "<!-- DEBUG: recuperati prodotti post IDs = " . implode( ',', wp_list_pluck( $products, 'ID' ) ) . " -->\n";
    if ( empty( $products ) ) {
        echo "<!-- DEBUG: get_posts non ha trovato nulla, esco -->\n";
        return '';
    }

    return toro_ag_render_grid_view(
        $products,
        'featured',
        'toro-grid--prodotti-page'
    );
}

/**
 * [toro_colture_page]
 * Recupera il meta 'applicazioni', normalizza anche un singolo ID in array,
 * e mostra la grid delle colture selezionate.
 */
function toro_grid_colture_page_shortcode() {
    if ( ! is_page() ) {
        return '';
    }

    // DEBUG: raw applicazioni meta
    $meta_raw = get_post_meta( get_the_ID(), 'applicazioni', true );
    echo "<!-- DEBUG: raw applicazioni meta = " . esc_html( $meta_raw ) . " -->\n";
    if ( empty( $meta_raw ) ) {
        echo "<!-- DEBUG: meta vuoto, esco -->\n";
        return '';
    }

    // Estrai tutti i numeri (ogni term ID)
    preg_match_all( '/\d+/', $meta_raw, $matches );
    $term_ids = array_map( 'intval', $matches[0] );
    echo "<!-- DEBUG: parsed applicazioni term IDs = " . implode( ',', $term_ids ) . " -->\n";
    if ( empty( $term_ids ) ) {
        echo "<!-- DEBUG: nessun term ID valido, esco -->\n";
        return '';
    }

    $terms = get_terms( array(
        'taxonomy'   => 'coltura',
        'hide_empty' => false,
        'include'    => $term_ids,
        'orderby'    => 'include',
    ) );

    if ( is_wp_error( $terms ) ) {
        echo "<!-- DEBUG: get_terms WP_Error: " . esc_html( $terms->get_error_message() ) . " -->\n";
        return '';
    }
    echo "<!-- DEBUG: recuperati termini term IDs = " . implode( ',', wp_list_pluck( $terms, 'term_id' ) ) . " -->\n";
    if ( empty( $terms ) ) {
        echo "<!-- DEBUG: get_terms non ha trovato nulla, esco -->\n";
        return '';
    }

    return toro_ag_render_grid_view(
        $terms,
        'col_thumb',
        'toro-grid--colture-page'
    );
}