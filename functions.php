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

// aggiungie bootstrap
function aggiungi_bootstrap() {
    // Frontend
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js', array(), null, true);
    wp_enqueue_style('bootstrap-icons', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css');
}
add_action('wp_enqueue_scripts', 'aggiungi_bootstrap');

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

// Breadcrumbs Yoast SEO
// aggiunge l'id "breadcrumbs" al breadcrumb di Yoast SEO

function divi_breadcrumb_shortcode() {
    if ( function_exists('yoast_breadcrumb') ) {
      return yoast_breadcrumb('<p id="breadcrumbs">','</p>', false);
    }
  }
  add_shortcode('my_breadcrumbs', 'divi_breadcrumb_shortcode');
/**
 * Shortcode [my_breadcrumbs]: integra Yoast Breadcrumbs
 * e per le tassonomie 'tipo_di_prodotto' e 'coltura'
 * aggiunge sempre il link alla pagina archivio tradotta.
 */
function toro_ag_divi_breadcrumb_shortcode() {
    if ( ! function_exists( 'yoast_breadcrumb' ) ) {
        return ''; // Yoast non attivo
    }

    // Se siamo in un termine di tipo_di_prodotto o coltura
    if ( is_tax( array( 'tipo_di_prodotto', 'coltura' ) ) ) {
        $taxonomy = get_query_var( 'taxonomy' ); // 'tipo_di_prodotto' o 'coltura'

        // Mappa slug pagina archivio per ciascuna tassonomia
        $map = array(
            'tipo_di_prodotto' => 'tipi-di-prodotto',
            'coltura'          => 'applicazioni',
        );

        // 1) Home
        $breadcrumbs = '<p id="breadcrumbs">';
        $breadcrumbs .= '<a href="' . home_url() . '">' . esc_html__( 'Home', 'toro-ag' ) . '</a>';

        // 2) Link alla pagina archivio (tradotta con WPML)
        if ( isset( $map[ $taxonomy ] ) ) {
            $slug = $map[ $taxonomy ];
            $page = get_page_by_path( $slug, OBJECT, 'page' );
            if ( $page ) {
                // ottiene ID tradotto nella lingua corrente
                $page_id = apply_filters( 'wpml_object_id', $page->ID, 'page', true, ICL_LANGUAGE_CODE );
                $breadcrumbs .= ' &raquo; <a href="' . get_permalink( $page_id ) . '">'
                              . esc_html( get_the_title( $page_id ) ) . '</a>';
            }
        }

        // 3) Term name
        $breadcrumbs .= ' &raquo; ' . single_term_title( '', false );
        $breadcrumbs .= '</p>';

        return $breadcrumbs;
    }

    // fallback Yoast normale per tutte le altre pagine
    return yoast_breadcrumb( '<p id="breadcrumbs">', '</p>', false );
}
remove_shortcode( 'my_breadcrumbs' );
add_shortcode(   'my_breadcrumbs', 'toro_ag_divi_breadcrumb_shortcode' );

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

/**
 * Converte i simboli ™ e ® in superscript.
 * Prima “riporta a testo normale” eventuali <sup>™</sup> e <sup>®</sup> già presenti,
 * poi aggiunge un unico wrapping <sup>…</sup> su tutti i simboli trovati.
 */
function toro_ag_trademarks_to_superscript( $text ) {
    if ( empty( $text ) ) {
        return $text;
    }

    // 1) Rimuove qualsiasi <sup>™</sup> o <sup>®</sup> già esistente
    //    in modo da non creare incapsulamenti multipli.
    //    Usiamo una regex che cerca <sup>™</sup> oppure <sup>®</sup> e riporta solo il carattere
    $text = preg_replace( '#<sup>(™|®)</sup>#', '$1', $text );

    // 2) Ora che abbiamo un testo “pulito” da eventuali <sup>™, <sup>®</sup>,
    //    applichiamo il wrapping correttamente, una sola volta.
    //    Notare che è importante prima TM e poi REG, per non sovrascrivere un TM appena creato.
    $text = str_replace( '™', '<sup>™</sup>', $text );
    $text = str_replace( '®', '<sup>®</sup>', $text );

    return $text;
}

// Applichiamo il filtro al titolo e al contenuto standard di WP
add_filter( 'the_title', 'toro_ag_trademarks_to_superscript' );
add_filter( 'the_content', 'toro_ag_trademarks_to_superscript' );

// Se usate Pods per campi personalizzati, includiamo anche quelli
add_filter( 'pods_content', 'toro_ag_trademarks_to_superscript', 10, 2 );
add_filter( 'pods_title',   'toro_ag_trademarks_to_superscript', 10, 2 );

// Se qualche modulo Divi sfugge ai filtri normali, lo convertiamo anche qui
add_filter( 'et_pb_render_content', 'toro_ag_trademarks_to_superscript', 999 );

/**
 * Wrapper generico per applicare il filtro anche all'output di QUALSIASI shortcode.
 * Il filtro `do_shortcode_tag` cattura l'HTML restituito da ciascun shortcode.
 */
function toro_ag_trademarks_shortcodes_wrapper( $output, $tag, $attr, $m ) {
    return toro_ag_trademarks_to_superscript( $output );
}
add_filter( 'do_shortcode_tag', 'toro_ag_trademarks_shortcodes_wrapper', 10, 4 );