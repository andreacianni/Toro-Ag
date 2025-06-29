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

/**
 * Fix per Child Theme - Sostituisci nel functions.php
 * Cambia get_template_directory() con get_stylesheet_directory()
 */

// ✅ CORRETTO (punta al child theme)
require_once get_stylesheet_directory() . '/inc/news-import-functions.php';

// Hook per aggiungere menu admin
add_action('admin_menu', 'toro_add_news_import_menu');

function toro_add_news_import_menu() {
    add_management_page(
        'Importazione News',           // Page title
        'Importa News',               // Menu title
        'manage_options',             // Capability
        'toro-news-import',          // Menu slug
        'toro_news_import_page'      // Callback function
    );
}

function toro_news_import_page() {
    // ✅ CORRETTO: usa get_stylesheet_directory() per child theme
    include get_stylesheet_directory() . '/import/news-importer.php';
}

// Hook per scripts e styles della pagina admin
add_action('admin_enqueue_scripts', 'toro_news_import_scripts');

function toro_news_import_scripts($hook) {
    // Solo sulla nostra pagina
    if ($hook !== 'tools_page_toro-news-import') {
        return;
    }
    
    // Stili custom per la pagina
    wp_add_inline_style('wp-admin', '
        .toro-import-container {
            max-width: 1200px;
            margin: 20px 0;
        }
        .import-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .stat-box {
            background: #fff;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 15px;
            text-align: center;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2271b1;
        }
        .stat-label {
            font-size: 12px;
            color: #646970;
            margin-top: 5px;
        }
        .import-log {
            background: #f6f7f7;
            border: 1px solid #c3c4c7;
            border-radius: 4px;
            padding: 15px;
            max-height: 400px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        .log-success { color: #00a32a; }
        .log-error { color: #d63638; }
        .log-warning { color: #dba617; }
        .log-info { color: #2271b1; }
        .progress-bar {
            width: 100%;
            height: 20px;
            background-color: #f0f0f1;
            border-radius: 10px;
            overflow: hidden;
            margin: 10px 0;
        }
        .progress-fill {
            height: 100%;
            background-color: #00a32a;
            transition: width 0.3s ease;
        }
    ');
}

// Handler AJAX per importazione news
add_action('wp_ajax_toro_import_news', 'toro_handle_import_ajax');

function toro_handle_import_ajax() {
    // 🔧 FIX: Previeni output HTML indesiderato
    ob_start(); // Cattura tutto l'output
    
    // 🔧 FIX: Disabilita notice/warning che rompono JSON
    error_reporting(E_ERROR | E_PARSE); // Solo errori critici
    
    try {
        // Verifica security nonce
        if (!wp_verify_nonce($_POST['security'], 'toro_import_news')) {
            ob_clean();
            wp_send_json_error('Nonce non valido');
            return;
        }
        
        // Verifica permessi
        if (!current_user_can('manage_options')) {
            ob_clean();
            wp_send_json_error('Permessi insufficienti');
            return;
        }
        
        // Leggi opzioni dal POST
        $options = [
            'force_update' => !empty($_POST['force_update']),
            'import_media' => !empty($_POST['import_media']),
            'connect_translations' => !empty($_POST['connect_translations']),
            'dry_run_mode' => !empty($_POST['dry_run_mode'])
        ];
        
        // Se è dry run, usa la funzione di simulazione
        if ($options['dry_run_mode']) {
            $results = toro_dry_run_import();
            
            if (is_wp_error($results)) {
                ob_clean();
                wp_send_json_error($results->get_error_message());
            } else {
                // Formatta risultati dry run per essere compatibili con l'interfaccia
                $formatted_results = [
                    'created' => $results['would_create'],
                    'updated' => [], // Dry run non distingue tra create/update
                    'skipped' => $results['would_skip'],
                    'errors' => $results['errors'],
                    'total_processed' => count($results['would_create']) + count($results['would_skip'])
                ];
                ob_clean();
                wp_send_json_success($formatted_results);
            }
        } else {
            // Esegui importazione reale
            $results = toro_run_full_import($options);
            
            if (is_wp_error($results)) {
                ob_clean();
                wp_send_json_error($results->get_error_message());
            } else {
                ob_clean();
                wp_send_json_success($results);
            }
        }
        
    } catch (Exception $e) {
        ob_clean();
        wp_send_json_error('Eccezione: ' . $e->getMessage());
    } catch (Error $e) {
        ob_clean(); 
        wp_send_json_error('Errore fatale: ' . $e->getMessage());
    }
}

// Download SimpleXLSX se non esiste
add_action('init', 'toro_ensure_simplexlsx');

function toro_ensure_simplexlsx() {
    // ✅ CORRETTO: usa get_stylesheet_directory() per child theme
    $xlsx_file = get_stylesheet_directory() . '/import/SimpleXLSX.php';
    
    // Se non esiste, scaricalo
    if (!file_exists($xlsx_file) && is_admin()) {
        $xlsx_url = 'https://raw.githubusercontent.com/shuchkin/simplexlsx/master/src/SimpleXLSX.php';
        $xlsx_content = wp_remote_get($xlsx_url);
        
        if (!is_wp_error($xlsx_content) && wp_remote_retrieve_response_code($xlsx_content) === 200) {
            $body = wp_remote_retrieve_body($xlsx_content);
            file_put_contents($xlsx_file, $body);
        }
    }
}

// Aggiungi collegamento nel menu admin per accesso rapido
add_action('admin_bar_menu', 'toro_add_import_admin_bar', 100);

function toro_add_import_admin_bar($wp_admin_bar) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    $args = [
        'id' => 'toro-import-news',
        'title' => '📰 Importa News',
        'href' => admin_url('tools.php?page=toro-news-import'),
        'meta' => [
            'class' => 'toro-import-shortcut'
        ]
    ];
    
    $wp_admin_bar->add_node($args);
}

// Notifica admin se file Excel manca
add_action('admin_notices', 'toro_check_excel_file_notice');

function toro_check_excel_file_notice() {
    // Solo sulla pagina di importazione
    if (!isset($_GET['page']) || $_GET['page'] !== 'toro-news-import') {
        return;
    }
    
    // ✅ CORRETTO: usa get_stylesheet_directory() per child theme
    $excel_file = get_stylesheet_directory() . '/import/DB_News_da importare.xlsx';
    
    if (!file_exists($excel_file)) {
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>⚠️ File Excel mancante!</strong> ';
        echo 'Carica il file <code>DB_News_da importare.xlsx</code> in <code>' . get_stylesheet_directory() . '/import/</code></p>';
        echo '</div>';
    }
}

// Utility: Reset importazione (per debug)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('wp_ajax_toro_reset_import', 'toro_reset_import_debug');
    
    function toro_reset_import_debug() {
        if (!current_user_can('manage_options')) {
            wp_die('Permessi insufficienti');
        }
        
        // Elimina tutti i post con news_id_originale
        $posts = get_posts([
            'meta_key' => 'news_id_originale',
            'meta_compare' => 'EXISTS',
            'post_type' => 'post',
            'post_status' => 'any',
            'posts_per_page' => -1
        ]);
        
        $deleted = 0;
        foreach ($posts as $post) {
            if (wp_delete_post($post->ID, true)) {
                $deleted++;
            }
        }
        
        wp_send_json_success("Eliminati {$deleted} post importati");
    }
}