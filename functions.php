<?php
/**
 * Divi Cake Child Theme
 * Functions.php
 *
 * ===== NOTES ==================================================================
 * 
 * Unlike style.css, the functions.php of a child theme does not override its 
 * counterpart from the parent. Instead, it is loaded in addition to the parent's 
 * functions.php. (Specifically, it is loaded right before the parent's file.)
 * 
 * In that way, the functions.php of a child theme provides a smart, trouble-free 
 * method of modifying the functionality of a parent theme. 
 * 
 * Discover Divi Child Themes: https://divicake.com/products/category/divi-child-themes/
 * Sell Your Divi Child Themes: https://divicake.com/open/
 * 
 * =============================================================================== */
 
function divichild_enqueue_scripts() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}
add_action( 'wp_enqueue_scripts', 'divichild_enqueue_scripts' );

function custom_divi_color_palette() {
    return array(
        'personalizzato' => array(
            'label'  => __('Personalizzato', 'divi'),
            'colors' => array('#ff5733', '#33ff57', '#3357ff', '#f4f4f4', '#222222'),
        ),
    );
}

add_filter('et_divi_custom_color_schemes', 'custom_divi_color_palette');

// carica text domain
function mytheme_setup() {
    load_theme_textdomain( 'toro-ag', get_template_directory() . '/languages' );
}
add_action('after_setup_theme', 'mytheme_setup');

// disattiva il CTP Progetti
function unregister_et_project_cpt() {
    unregister_post_type('project');
}
add_action('init', 'unregister_et_project_cpt', 100);

// Aggiunge Bootstrap 5.3.3 e Bootstrap Icons
function aggiungi_bootstrap() {
    // Frontend
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), null, true);
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');
}
add_action('wp_enqueue_scripts', 'aggiungi_bootstrap');

// Aggiunge SwiperJS per le gallerie
function enqueue_swiper_assets() {
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', [], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_swiper_assets');

// Backend
function aggiungi_bootstrap_admin() {
    wp_enqueue_style('bootstrap-icons-admin', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');
}
add_action('admin_enqueue_scripts', 'aggiungi_bootstrap_admin');


// carica helpers e shortcodes
require_once get_stylesheet_directory() . '/inc/helpers/file-icon.php';
require_once get_stylesheet_directory() . '/inc/helpers/language-flag.php';
require_once get_stylesheet_directory() . '/inc/helpers/view-loader.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/documenti-download.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/card-agente.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/documenti-agente.php';
require_once get_stylesheet_directory() . '/inc/admin-agente-nuovo.php';
require_once get_stylesheet_directory() . '/inc/admin-agente-modifica.php';
require_once get_stylesheet_directory() . '/inc/area-agenti-frontend.php';
require_once get_stylesheet_directory() . '/inc/admin-agente-elenco.php';
require_once get_stylesheet_directory() . '/inc/helpers/secure-download.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/ricerca-agenti.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/documenti-nel-prodotto.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/documenti-cultura.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/documenti-download.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/hero-tipidiprodotto-colture.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/grid.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/video-card.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/prodotto-video.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/tipo-prodotto-video.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/scheda-prodotto-dettaglio.php';
require_once get_stylesheet_directory() . '/inc/helpers/sup.php';
require_once get_stylesheet_directory() . '/inc/helpers/breadcrumbs.php';
require_once get_stylesheet_directory() . '/inc/shortcodes/doc-plus.php';
// require_once get_stylesheet_directory() . '/inc/views/doc-plus-view.php';

/*
$shortcode_file = get_stylesheet_directory() . '/inc/shortcodes/doc-plus.php';
// invalidiamo il PHP OPcache per questo file, così verrà ricaricato fresco ad ogni richiesta
if ( function_exists('opcache_invalidate') ) {
    opcache_invalidate( $shortcode_file, true );
}
// includiamo sempre la versione aggiornata
require $shortcode_file;
*/

/**
 * Ora registro tutti i filtri che usano toro_ag_trademarks_to_superscript()
 */

// Applichiamo il filtro ai titoli e ai contenuti “normali” di WP
// add_filter( 'the_title',   'toro_ag_trademarks_to_superscript' );
// add_filter( 'the_content', 'toro_ag_trademarks_to_superscript' );

// Se usi Pods, intercetta anche i Custom Field
//add_filter( 'pods_content', 'toro_ag_trademarks_to_superscript', 10, 2 );
// add_filter( 'pods_title',   'toro_ag_trademarks_to_superscript', 10, 2 );

// Se Divi “salta” the_content, intercetta anche qui
// add_filter( 'et_pb_render_content', 'toro_ag_trademarks_to_superscript', 999 );

/**
 * Wrapper generico per catturare TUTTI gli shortcode:
 * così l'output di qualsiasi [tuo_shortcode] viene prima “ripulito” dagli sup duplicati
 * (anche quelli escapati) e poi wrappato correttamente.
 */
/*
function toro_ag_trademarks_shortcodes_wrapper( $output, $tag, $attr, $m ) {
    return toro_ag_trademarks_to_superscript( $output );
}
add_filter( 'do_shortcode_tag', 'toro_ag_trademarks_shortcodes_wrapper', 10, 4 );
*/
/*
function toro_ag_enqueue_sup_classes_script() {
  // Registra ed enqueue del nostro JS
  wp_enqueue_script(
    'toro-ag-sup-classes',
    get_stylesheet_directory_uri() . '/assets/js/sup-classes.js',
    [],       // dipendenze (vuoto perché usiamo solo Vanilla JS)
    null,     // versione (null per non forzare versioni)
    true      // mettiamo lo script nel footer
  );
}
add_action( 'wp_enqueue_scripts', 'toro_ag_enqueue_sup_classes_script' );
*/

// SHORTCODE PER CARICARE UNA PAGINA PHP
function shortcode_includi_php($atts) {
    $atts = shortcode_atts(array(
        'file' => ''
    ), $atts);

    $file = sanitize_text_field($atts['file']);
    $filepath = get_theme_file_path('inc/shortcodes/' . $file . '.php');

    if (file_exists($filepath)) {
        ob_start();
        include $filepath;
        return ob_get_clean();
    } else {
        return "File PHP non trovato: $file";
    }
}
add_shortcode('include_php', 'shortcode_includi_php');

// nasconde voce Commenti dal menù
add_action('admin_menu', 'remove_comments_menu');
function remove_comments_menu() {
    remove_menu_page('edit-comments.php');
}

// Aggiunge "Aggiungi Brochure" sotto la voce "Colture"
add_action('admin_menu', 'aggiungi_sottomenu_brochure', 11);
function aggiungi_sottomenu_brochure() {
    add_submenu_page(
        'edit-tags.php?taxonomy=coltura',           // parent slug
        __('Aggiungi Brochure', 'testo-dominio'), // page title
        __('Aggiungi Brochure', 'testo-dominio'), // menu title
        'edit_posts',                               // capability
        'post-new.php?post_type=brochure_coltura'           // menu slug
    );
}

// Aggiunge "Aggiungi Scheda" sotto la voce "Prodotti"
add_action('admin_menu', 'aggiungi_sottomenu_scheda', 12);
function aggiungi_sottomenu_scheda() {
    add_submenu_page(
        'edit.php?post_type=prodotto',
        __('Aggiungi Scheda', 'testo-dominio'),
        __('Aggiungi Scheda', 'testo-dominio'),
        'edit_posts',
        'post-new.php?post_type=scheda_prodotto'
    );
}
