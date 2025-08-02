<?php
/**
 * Divi Cake Child Theme
 * Functions.php
 *
 * 🧪 TEST WORKFLOW GIT - San Tommaso Andrea - 02/08/2025 15:30
 * Questo commento PHP conferma che le modifiche passano da Local → Staging
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

// Carica JavaScript per Privacy Toggle
function enqueue_privacy_toggle_script() {
    wp_enqueue_script(
        'toro-privacy-toggle',
        get_stylesheet_directory_uri() . '/assets/js/privacy-toggle.js',
        [],
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'enqueue_privacy_toggle_script');

// Carica CSS per Layout Manager
function enqueue_toro_layout_manager_styles() {
    wp_enqueue_style(
        'toro-layout-manager',
        get_stylesheet_directory_uri() . '/assets/css/toro-layout-manager.css',
        ['bootstrap-css'],
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_toro_layout_manager_styles');


// TEST DEBUG: Shortcode semplice per verificare funzionamento
add_shortcode('test_debug_toro', function() {
    $layout_shortcode_exists = shortcode_exists('toro_layout_tipo_prodotto');
    $prodotto_shortcode_exists = shortcode_exists('toro_layout_prodotto');
    $coltura_shortcode_exists = shortcode_exists('toro_layout_coltura');
    
    $debug_info = '<div style="background:red;color:white;padding:20px;margin:20px;">';
    $debug_info .= '🧪 TEST SHORTCODE FUNZIONA!<br>';
    $debug_info .= 'Classe ToroLayoutManager: ' . (class_exists('ToroLayoutManager') ? '✅ ESISTE' : '❌ NON ESISTE') . '<br>';
    $debug_info .= '[toro_layout_tipo_prodotto]: ' . ($layout_shortcode_exists ? '✅ REGISTRATO' : '❌ NON REGISTRATO') . '<br>';
    $debug_info .= '[toro_layout_prodotto]: ' . ($prodotto_shortcode_exists ? '✅ REGISTRATO' : '❌ NON REGISTRATO') . '<br>';
    $debug_info .= '[toro_layout_coltura]: ' . ($coltura_shortcode_exists ? '✅ REGISTRATO' : '❌ NON REGISTRATO') . '<br>';
    
    // Test se il metodo init() è stato chiamato
    if (method_exists('ToroLayoutManager', 'init')) {
        $debug_info .= 'Metodo init(): ✅ ESISTE<br>';
    } else {
        $debug_info .= 'Metodo init(): ❌ NON ESISTE<br>';
    }
    
    $debug_info .= '</div>';
    
    return $debug_info;
});

// carica Layout Manager intelligente
require_once get_stylesheet_directory() . '/inc/classes/ToroLayoutManager.php';

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
require_once get_stylesheet_directory() . '/inc/shortcodes/brochure-coltura.php';
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
    // Previeni qualsiasi output
    ob_start();
    error_reporting(0); // Disabilita tutti gli errori PHP
    ini_set('display_errors', 0);
    
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
                $formatted_results = [
                    'created' => $results['would_create'],
                    'updated' => [],
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

// TEST DIRETTO AJAX
add_action('wp_ajax_test_toro_debug', 'test_toro_debug');
function test_toro_debug() {
    // Output pulito
    header('Content-Type: application/json');
    ob_clean();
    echo json_encode(['test' => 'success', 'time' => date('Y-m-d H:i:s')]);
    exit;
}
/**
 * Converte data Excel (numero seriale o formato europeo) in formato WordPress
 */
function toro_parse_excel_date($date_string) {
    error_log("PARSING DATE: input='{$date_string}'");
    
    if (empty($date_string)) {
        $result = date('Y-m-d H:i:s');
        error_log("PARSING DATE: empty input, returning current date: {$result}");
        return $result;
    }
    
    // Se è già in formato corretto, usala
    if (preg_match('/^\d{4}-\d{2}-\d{2}/', $date_string)) {
        $result = date('Y-m-d H:i:s', strtotime($date_string));
        error_log("PARSING DATE: already correct format, returning: {$result}");
        return $result;
    }
    
    // 🔧 NUOVO: Gestisce numero seriale Excel
    if (is_numeric($date_string) && $date_string > 1 && $date_string < 100000) {
        // Converte da numero seriale Excel a timestamp Unix
        // Excel conta da 1° gennaio 1900, ma ha un bug per il 1900 (non bisestile)
        // Quindi sottraiamo 25569 giorni per arrivare al 1° gennaio 1970 (Unix epoch)
        $unix_timestamp = ($date_string - 25569) * 86400; // 86400 secondi in un giorno
        
        $result = date('Y-m-d H:i:s', $unix_timestamp);
        error_log("PARSING DATE: converted from Excel serial {$date_string} to: {$result}");
        return $result;
    }
    
    // Converte formato europeo dd/mm/yyyy (fallback)
    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date_string, $matches)) {
        $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
        $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        $year = $matches[3];
        
        $result = $year . '-' . $month . '-' . $day . ' 00:00:00';
        error_log("PARSING DATE: converted from european format: {$result}");
        return $result;
    }
    
    // Fallback: prova strtotime normale
    $timestamp = strtotime($date_string);
    if ($timestamp !== false) {
        $result = date('Y-m-d H:i:s', $timestamp);
        error_log("PARSING DATE: strtotime fallback: {$result}");
        return $result;
    }
    
    // Ultima risorsa: data attuale
    $result = date('Y-m-d H:i:s');
    error_log("PARSING DATE: last resort current date: {$result}");
    return $result;
}

/**
 * Enqueue script per nascondere campi social degli agenti
 * TORO AG - Feature: Hide Social Fields for Agents
 * 
 * 🔧 PER DISABILITARE QUESTA FUNZIONALITÀ:
 * Commenta le righe dalla 576 alla 704 (tutto il blocco da qui fino a "add_action('user_register'...")
 * Oppure rimuovi il file assets/js/admin-user-social-fields.js
 */
function toro_ag_enqueue_admin_user_scripts($hook) {
    // Carica solo nelle pagine di editing/creazione utente
    if ($hook !== 'user-edit.php' && $hook !== 'user-new.php') {
        return;
    }
    
    // Enqueue dello script
    wp_enqueue_script(
        'toro-ag-admin-user-social-fields',
        get_stylesheet_directory_uri() . '/assets/js/admin-user-social-fields.js',
        array('jquery'),
        filemtime(get_stylesheet_directory() . '/assets/js/admin-user-social-fields.js'),
        true
    );
    
    // Opzionale: Passa dati PHP a JavaScript se necessario
    wp_localize_script(
        'toro-ag-admin-user-social-fields',
        'toroAgentFields',
        array(
            'agentRole' => 'agente',
            'debug' => defined('WP_DEBUG') && WP_DEBUG
        )
    );
}
add_action('admin_enqueue_scripts', 'toro_ag_enqueue_admin_user_scripts');

/**
 * Aggiunge checkbox "Modalità semplificata profilo" nella pagina di editing utente
 * TORO AG - Feature: Simplified Profile Mode Toggle
 */

// Mostra il campo nella pagina di editing utente - DOPO i campi contatto ma PRIMA di Pods
function toro_ag_show_simplified_profile_field($user) {
    $hide_social_fields = get_user_meta($user->ID, 'hide_social_fields', true);
    // Default: true (checked) se non è stato mai impostato
    if ($hide_social_fields === '') {
        $hide_social_fields = '1';
    }
    ?>
    <style>
    /* Assicuriamoci che sia visibile e ben posizionato */
    .toro-simplified-profile-section {
        margin: 20px 0;
        padding: 15px 0;
        border-bottom: 1px solid #c3c4c7;
    }
    </style>
    <script>
    jQuery(document).ready(function($) {
        // Sposta il campo subito dopo "Informazioni di contatto"
        var $contactSection = $('h2').filter(function() {
            return $(this).text().trim() === 'Informazioni di contatto';
        });
        
        if ($contactSection.length > 0) {
            var $contactTable = $contactSection.next('.form-table');
            if ($contactTable.length > 0) {
                $('.toro-simplified-profile-section').insertAfter($contactTable);
                // console.log('✅ Sezione Preferenze Profilo spostata dopo Informazioni di contatto');
            }
        }
    });
    </script>
    
    <div class="toro-simplified-profile-section">
        <h3>Preferenze Profilo</h3>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">Modalità Profilo</th>
                <td>
                    <label for="hide_social_fields">
                        <input type="checkbox" 
                               name="hide_social_fields" 
                               id="hide_social_fields" 
                               value="1" 
                               <?php checked($hide_social_fields, '1'); ?> />
                        Modalità semplificata profilo
                        <p class="description">Nasconde i campi social media per un'interfaccia più pulita.</p>
                    </label>
                </td>
            </tr>
        </table>
    </div>
    <?php
}
add_action('show_user_profile', 'toro_ag_show_simplified_profile_field');
add_action('edit_user_profile', 'toro_ag_show_simplified_profile_field');

// Salva il campo quando viene aggiornato il profilo
function toro_ag_save_simplified_profile_field($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    
    // Salva il valore del checkbox (1 se checked, 0 se unchecked)
    $hide_social_fields = isset($_POST['hide_social_fields']) ? '1' : '0';
    update_user_meta($user_id, 'hide_social_fields', $hide_social_fields);
}
add_action('personal_options_update', 'toro_ag_save_simplified_profile_field');
add_action('edit_user_profile_update', 'toro_ag_save_simplified_profile_field');

/**
 * Aggiunge il campo anche nella pagina di creazione nuovo utente
 */
function toro_ag_show_simplified_profile_field_new_user() {
    ?>
    <h3>Preferenze Profilo</h3>
    <table class="form-table" role="presentation">
        <tr>
            <th scope="row">Modalità Profilo</th>
            <td>
                <label for="hide_social_fields">
                    <input type="checkbox" 
                           name="hide_social_fields" 
                           id="hide_social_fields" 
                           value="1" 
                           checked /> <!-- Default checked per nuovi utenti -->
                    Modalità semplificata profilo
                    <p class="description">Nasconde i campi social media per un'interfaccia più pulita.</p>
                </label>
            </td>
        </tr>
    </table>
    <?php
}
add_action('user_new_form', 'toro_ag_show_simplified_profile_field_new_user');

// Salva il campo per nuovi utenti
function toro_ag_save_simplified_profile_field_new_user($user_id) {
    // Salva il valore del checkbox per nuovo utente
    $hide_social_fields = isset($_POST['hide_social_fields']) ? '1' : '0';
    update_user_meta($user_id, 'hide_social_fields', $hide_social_fields);
}
add_action('user_register', 'toro_ag_save_simplified_profile_field_new_user');

// 🧪 TEST WORKFLOW GIT - SHORTCODE VISIBILE
// San Tommaso Andrea - 02/08/2025 15:30
// Questo shortcode stampa un messaggio HTML visibile per confermare il deploy
function toro_test_workflow_shortcode() {
    return '<div style="background: #28a745; color: white; padding: 15px; margin: 20px 0; border-radius: 5px; text-align: center; font-weight: bold;">
        🧪 TEST WORKFLOW GIT ATTIVO! 🚀<br>
        Modifica committata il: ' . date('d/m/Y H:i:s') . '<br>
        <small>Se vedi questo messaggio, il workflow Local → Staging funziona!</small>
    </div>';
}
add_shortcode('test_workflow', 'toro_test_workflow_shortcode');

// 🚀 TORO AG GitHub Auto-Updater
// Sistema per aggiornamenti automatici tema da GitHub
require_once get_stylesheet_directory() . '/inc/github-updater/ToroGitHubUpdater.php';

// prova aggiornamento automatico da github