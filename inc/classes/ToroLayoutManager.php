<?php
/**
 * TORO AG Layout Manager
 * 
 * Classe principale per gestire layout intelligenti che eliminano colonne vuote
 * Approccio: Orchestratori che caricano shortcode esistenti solo quando necessario
 * 
 * @package ToroAG
 * @version 1.0.0
 * @author Novacom
 */

class ToroLayoutManager {
    
    /**
     * Versione corrente del Layout Manager
     */
    const VERSION = '1.0.0';
    
    /**
     * Prefisso per tutte le cache key
     */
    const CACHE_PREFIX = 'toro_layout_';
    
    /**
     * Durata cache (1 ora)
     */
    const CACHE_DURATION = 3600;
    
    /**
     * Modalità debug globale
     */
    private static $debug_mode = false;
    
    /**
     * Inizializza il Layout Manager
     */
    public static function init() {
        // Registra shortcode layout manager
        add_shortcode('toro_layout_prodotto', [__CLASS__, 'layout_prodotto']);
        add_shortcode('toro_layout_tipo_prodotto', [__CLASS__, 'layout_tipo_prodotto']);
        add_shortcode('toro_layout_coltura', [__CLASS__, 'layout_coltura']);
        
        // Hook per pulizia cache quando necessario
        add_action('save_post', [__CLASS__, 'clear_cache_on_post_save']);
        add_action('updated_post_meta', [__CLASS__, 'clear_cache_on_meta_update'], 10, 4);
        
        // Registra stringhe WPML
        add_action('init', [__CLASS__, 'register_wpml_strings']);
        add_action('admin_notices', [__CLASS__, 'force_wpml_sync']);
        
        // Carica assets CSS e JS
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
        
        // Debug mode se WP_DEBUG è attivo
        if (defined('WP_DEBUG') && WP_DEBUG) {
            self::$debug_mode = false; // Disabilitato anche in WP_DEBUG
        }
    }
    
    /**
     * Carica CSS e JavaScript del Layout Manager
     */
    public static function enqueue_assets() {
        // CSS sempre caricato
        wp_enqueue_style(
            'toro-layout-manager', 
            get_stylesheet_directory_uri() . '/assets/css/toro-layout-manager.css',
            [], 
            self::VERSION
        );
        
        // JavaScript solo sulle pagine che potrebbero usare i layout
        if (is_singular(['prodotto', 'tipo_di_prodotto']) || is_tax(['tipo_di_prodotto', 'coltura']) || is_page()) {
            wp_enqueue_script(
                'toro-layout-manager-js',
                get_stylesheet_directory_uri() . '/assets/js/toro-layout-manager.js',
                [],
                self::VERSION,
                true // Carica nel footer
            );
        }
    }
    
    /**
     * Registra stringhe per WPML
     */
    public static function register_wpml_strings() {
        if (function_exists('wpml_register_single_string')) {
            // Forza la registrazione della stringa
            wpml_register_single_string('Toro Layout Manager', 'Chiedi informazioni sul prodotto', 'Chiedi informazioni sul prodotto');
            
            // Debug: verifica registrazione
            if (current_user_can('manage_options') && isset($_GET['toro_debug_wpml'])) {
                echo '<div class="notice notice-info"><p>✅ WPML String registrata: "Chiedi informazioni sul prodotto" nel dominio "Toro Layout Manager"</p></div>';
            }
        }
    }
    
    /**
     * Funzione helper per forzare sync WPML stringhe
     * Chiamare con: ?toro_force_wpml_sync=1
     */
    public static function force_wpml_sync() {
        if (isset($_GET['toro_force_wpml_sync']) && current_user_can('manage_options')) {
            // Forza registrazione
            self::register_wpml_strings();
            
            // Prova a triggerare scan WPML
            if (function_exists('wpml_register_single_string')) {
                // Registra con forza
                wpml_register_single_string('Toro Layout Manager', 'Chiedi informazioni sul prodotto', 'Chiedi informazioni sul prodotto');
                
                echo '<div class="notice notice-success"><p>🔄 WPML Sync forzato! Controlla ora WPML > String Translation</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>❌ WPML non attivo o funzioni non disponibili</p></div>';
            }
        }
    }
    
    /**
     * Layout Manager per singoli prodotti
     * 
     * @param array $atts Attributi shortcode
     * @return string HTML output
     */
    public static function layout_prodotto($atts) {
        // Validazione contesto
        if (!is_singular('prodotto')) {
            return self::debug_output('❌ Shortcode [toro_layout_prodotto] può essere usato solo su pagine prodotto');
        }
        
        // Parse parametri
        $atts = shortcode_atts([
            'sections' => 'auto',
            'layout' => 'adaptive', 
            'sidebar_position' => 'left',
            'image_size' => 'large',
            'form_position' => 'bottom',
            'responsive' => 'true',
            'debug' => 'false'
        ], $atts);
        
        // Abilita debug se richiesto
        $debug_local = ($atts['debug'] === 'true') || self::$debug_mode;
        
        if ($debug_local) {
            $debug_info = "🔧 DEBUG [toro_layout_prodotto]\n";
            $debug_info .= "Product ID: " . get_the_ID() . "\n";
            $debug_info .= "Params: " . json_encode($atts) . "\n\n";
        }
        
        // Ottieni availability contenuto (con cache)
        $product_id = get_the_ID();
        $content_map = self::get_product_content_availability($product_id);
        
        if ($debug_local) {
            $debug_info .= "Content Availability:\n" . json_encode($content_map, JSON_PRETTY_PRINT) . "\n\n";
        }
        
        // Determina sezioni da caricare
        $sections_to_load = self::determine_sections($atts['sections'], $content_map, 'prodotto');
        
        if ($debug_local) {
            $debug_info .= "Sections to Load: " . implode(', ', $sections_to_load) . "\n\n";
        }
        
        // Caricamento condizionale shortcode
        $loaded_sections = [];
        foreach ($sections_to_load as $section) {
            $section_content = self::load_section_content($section, $product_id, 'prodotto');
            if (!empty($section_content)) {
                $loaded_sections[$section] = $section_content;
            }
            
            if ($debug_local) {
                $debug_info .= "Section '{$section}': " . (empty($section_content) ? 'EMPTY' : 'LOADED (' . strlen($section_content) . ' chars)') . "\n";
            }
        }
        
        if ($debug_local) {
            $debug_info .= "\nFinal Sections: " . implode(', ', array_keys($loaded_sections)) . "\n";
        }
        
        // Genera layout HTML
        $layout_html = self::render_adaptive_layout($loaded_sections, $atts, 'prodotto');
        
        // Output finale
        $output = '';
        if ($debug_local) {
            $output .= self::debug_output($debug_info);
        }
        $output .= $layout_html;
        
        return $output;
    }
    
    /**
     * Layout Manager per tipi di prodotto
     * 
     * @param array $atts Attributi shortcode
     * @return string HTML output
     */
    public static function layout_tipo_prodotto($atts) {
        // Validazione contesto
        if (!is_tax('tipo_di_prodotto')) {
            return self::debug_output('❌ Shortcode [toro_layout_tipo_prodotto] può essere usato solo su pagine tipo di prodotto');
        }
        
        // Parse parametri
        $atts = shortcode_atts([
            'sections' => 'auto',
            'layout' => 'flexible', 
            'columns' => 'auto',
            'responsive' => 'true',
            'debug' => 'false'
        ], $atts);
        
        // Abilita debug se richiesto
        $debug_local = ($atts['debug'] === 'true') || self::$debug_mode;
        
        if ($debug_local) {
            $debug_info = "🔧 DEBUG [toro_layout_tipo_prodotto]\n";
            $debug_info .= "Term ID: " . get_queried_object()->term_id . "\n";
            $debug_info .= "Params: " . json_encode($atts) . "\n\n";
        }
        
        // Ottieni availability contenuto (con cache)
        $term = get_queried_object();
        $content_map = self::get_tipo_prodotto_content_availability($term);
        
        if ($debug_local) {
            $debug_info .= "Content Availability:\n" . json_encode($content_map, JSON_PRETTY_PRINT) . "\n\n";
        }
        
        // Determina sezioni da caricare
        $sections_to_load = self::determine_sections($atts['sections'], $content_map, 'tipo_prodotto');
        
        if ($debug_local) {
            $debug_info .= "Sections to Load: " . implode(', ', $sections_to_load) . "\n\n";
        }
        
        // Caricamento condizionale shortcode
        $loaded_sections = [];
        foreach ($sections_to_load as $section) {
            $section_content = self::load_section_content($section, $term->term_id, 'tipo_prodotto');
            
            // 🔧 DEBUG TEMPORANEO: Log contenuto sezione documents
            if ($section === 'documents' && isset($_GET['debug_docs'])) {
                error_log("🔧 LAYOUT DEBUG: Section 'documents' content length: " . strlen($section_content));
                error_log("🔧 LAYOUT DEBUG: Section 'documents' content preview: " . substr(strip_tags($section_content), 0, 200));
            }
            
            if (!empty($section_content)) {
                $loaded_sections[$section] = $section_content;
            }
            
            if ($debug_local) {
                $debug_info .= "Section '{$section}': " . (empty($section_content) ? 'EMPTY' : 'LOADED (' . strlen($section_content) . ' chars)') . "\n";
            }
        }
        
        if ($debug_local) {
            $debug_info .= "\nFinal Sections: " . implode(', ', array_keys($loaded_sections)) . "\n";
        }
        
        // Genera layout HTML
        $layout_html = self::render_adaptive_layout($loaded_sections, $atts, 'tipo_prodotto');
        
        // Output finale
        $output = '';
        if ($debug_local) {
            $output .= self::debug_output($debug_info);
        }
        $output .= $layout_html;
        
        return $output;
    }
    
    /**
     * Layout Manager per colture (placeholder)
     */
    public static function layout_coltura($atts) {
        // Validazione contesto
        if (!is_tax('coltura')) {
            return self::debug_output('❌ Shortcode [toro_layout_coltura] può essere usato solo su pagine coltura');
        }
        
        // Parse parametri
        $atts = shortcode_atts([
            'sections' => 'auto',
            'layout' => 'stacked',
            'brochure_layout' => 'card',
            'responsive' => 'true',
            'debug' => 'false'
        ], $atts);
        
        // Abilita debug se richiesto
        $debug_local = ($atts['debug'] === 'true') || self::$debug_mode;
        
        if ($debug_local) {
            $debug_info = "🔧 DEBUG [toro_layout_coltura]\n";
            $debug_info .= "Term ID: " . get_queried_object()->term_id . "\n";
            $debug_info .= "Params: " . json_encode($atts) . "\n\n";
        }
        
        // Ottieni availability contenuto (con cache)
        $term = get_queried_object();
        $content_map = self::get_coltura_content_availability($term);
        
        if ($debug_local) {
            $debug_info .= "Content Availability:\n" . json_encode($content_map, JSON_PRETTY_PRINT) . "\n\n";
        }
        
        // Determina sezioni da caricare
        $sections_to_load = self::determine_sections($atts['sections'], $content_map, 'coltura');
        
        if ($debug_local) {
            $debug_info .= "Sections to Load: " . implode(', ', $sections_to_load) . "\n\n";
        }
        
        // Caricamento condizionale shortcode
        $loaded_sections = [];
        foreach ($sections_to_load as $section) {
            $section_content = self::load_section_content($section, $term->term_id, 'coltura', $atts);
            if (!empty($section_content)) {
                $loaded_sections[$section] = $section_content;
            }
            
            if ($debug_local) {
                $debug_info .= "Section '{$section}': " . (empty($section_content) ? 'EMPTY' : 'LOADED (' . strlen($section_content) . ' chars)') . "\n";
            }
        }
        
        if ($debug_local) {
            $debug_info .= "\nFinal Sections: " . implode(', ', array_keys($loaded_sections)) . "\n";
        }
        
        // Genera layout HTML
        $layout_html = self::render_adaptive_layout($loaded_sections, $atts, 'coltura');
        
        // Output finale
        $output = '';
        if ($debug_local) {
            $output .= self::debug_output($debug_info);
        }
        $output .= $layout_html;
        
        return $output;
    }
    
    /**
     * Ottieni disponibilità contenuto per un tipo prodotto (con cache)
     * 
     * @param object $term Termine tipo_di_prodotto
     * @return array Mappa disponibilità contenuto
     */
    public static function get_tipo_prodotto_content_availability($term) {
        $cache_key = self::CACHE_PREFIX . "tipo_content_check_{$term->term_id}";
        $cached = wp_cache_get($cache_key);
        
        if ($cached !== false && !self::$debug_mode) {
            return $cached;
        }
        
        // Query leggere - solo existence check
        $availability = [
            'has_hero' => true, // Hero sempre disponibile
            'has_description' => !empty($term->description),
            'has_products' => self::check_term_has_products($term->term_id, 'tipo_di_prodotto'),
            'has_documents' => self::check_tipo_prodotto_has_documents($term->term_id), // 🔧 FIX WPML AWARENESS
            'has_videos' => !empty(get_term_meta($term->term_id, 'tipo-video', true))
        ];
        
        // Cache per 1 ora
        wp_cache_set($cache_key, $availability, '', self::CACHE_DURATION);
        
        return $availability;
    }
    
    /**
     * Ottieni disponibilità contenuto per una coltura (con cache)
     * 
     * @param object $term Termine coltura
     * @return array Mappa disponibilità contenuto
     */
    public static function get_coltura_content_availability($term) {
        $cache_key = self::CACHE_PREFIX . "coltura_content_check_{$term->term_id}";
        $cached = wp_cache_get($cache_key);
        
        if ($cached !== false && !self::$debug_mode) {
            return $cached;
        }
        
        // Query leggere - solo existence check
        $availability = [
            'has_hero' => true, // Hero sempre disponibile
            'has_description' => !empty($term->description),
            'has_products' => self::check_term_has_products($term->term_id, 'coltura'),
            'has_brochures' => self::check_coltura_has_brochures($term->term_id),
            'has_videos' => !empty(get_term_meta($term->term_id, 'video-coltura', true))
        ];
        
        // Cache per 1 ora
        wp_cache_set($cache_key, $availability, '', self::CACHE_DURATION);
        
        return $availability;
    }
    
    /**
     * Controlla se un termine ha prodotti associati
     * 
     * @param int $term_id ID del termine
     * @param string $taxonomy Nome tassonomia
     * @return bool True se ha prodotti
     */
    private static function check_term_has_products($term_id, $taxonomy) {
        $products = get_posts([
            'post_type' => 'prodotto',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'tax_query' => [[
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $term_id
            ]]
        ]);
        
        return !empty($products);
    }
    
    /**
     * Ottieni disponibilità contenuto per un prodotto (con cache)
     * 
     * @param int $product_id ID del prodotto
     * @return array Mappa disponibilità contenuto
     */
    public static function get_product_content_availability($product_id) {
        $cache_key = self::CACHE_PREFIX . "content_check_{$product_id}";
        $cached = wp_cache_get($cache_key);
        
        if ($cached !== false && !self::$debug_mode) {
            return $cached;
        }
        
        // Query leggere - solo existence check
        $availability = [
            'has_main_content' => !empty(get_post_field('post_content', $product_id)),
            'has_documents' => !empty(get_post_meta($product_id, 'scheda_prodotto', true)),
            'has_videos' => !empty(get_post_meta($product_id, 'video_prodotto', true)),
            'has_cultures' => !empty(get_the_terms($product_id, 'coltura')),
            'has_featured_image' => has_post_thumbnail($product_id),
            'has_gallery' => self::check_product_gallery($product_id),
            'has_form_data' => !empty(get_post_meta($product_id, 'form_data', true)) // Placeholder per form
        ];
        
        // Cache per 1 ora
        wp_cache_set($cache_key, $availability, '', self::CACHE_DURATION);
        
        return $availability;
    }
    
    /**
     * Determina quali sezioni caricare basato su parametri e contenuto disponibile
     * 
     * @param string $sections_param Parametro sections dal shortcode
     * @param array $content_map Mappa contenuto disponibile
     * @param string $layout_type Tipo di layout (prodotto, tipo_prodotto, coltura)
     * @return array Lista sezioni da caricare
     */
    public static function determine_sections($sections_param, $content_map, $layout_type) {
        if ($sections_param === 'auto') {
            // Auto-detect basato su contenuto disponibile
            $sections = [];
            
            if ($layout_type === 'prodotto') {
                // Immagine sempre presente se disponibile
                if ($content_map['has_featured_image']) {
                    $sections[] = 'image';
                }
                
                // Contenuto principale sempre presente
                if ($content_map['has_main_content']) {
                    $sections[] = 'content';
                }
                
                // Sezioni condizionali
                if ($content_map['has_documents']) {
                    $sections[] = 'documents';
                }
                
                if ($content_map['has_cultures']) {
                    $sections[] = 'cultures';
                }
                
                if ($content_map['has_videos']) {
                    $sections[] = 'videos';
                }
                
                if ($content_map['has_gallery']) {
                    $sections[] = 'gallery';
                }
                
                if ($content_map['has_form_data']) {
                    $sections[] = 'form';
                }
            } elseif ($layout_type === 'tipo_prodotto') {
                // Hero sempre presente
                if ($content_map['has_hero']) {
                    $sections[] = 'hero';
                }
                
                // Descrizione se disponibile
                if ($content_map['has_description']) {
                    $sections[] = 'description';
                }
                
                // Prodotti sempre se disponibili
                if ($content_map['has_products']) {
                    $sections[] = 'products';
                }
                
                // Documenti se disponibili
                if ($content_map['has_documents']) {
                    $sections[] = 'documents';
                }
                
                // Video se disponibili
                if ($content_map['has_videos']) {
                    $sections[] = 'videos';
                }
            }
            
            return $sections;
        } else {
            // Sezioni custom specificate manualmente
            return explode(',', $sections_param);
        }
    }
    
    /**
     * Carica contenuto per una specifica sezione
     * 
     * @param string $section Nome sezione
     * @param int $post_id ID del post o term
     * @param string $layout_type Tipo di layout
     * @return string Contenuto HTML della sezione
     */
    public static function load_section_content($section, $post_id, $layout_type) {
        if ($layout_type === 'prodotto') {
            switch ($section) {
                case 'image':
                    return get_the_post_thumbnail($post_id, 'large', ['class' => 'toro-layout-image img-fluid']);
                    
                case 'content':
                    return apply_filters('the_content', get_post_field('post_content', $post_id));
                    
                case 'documents':
                    // Riusa shortcode esistente [scheda_prodotto_dettaglio]
                    return do_shortcode('[scheda_prodotto_dettaglio]');
                    
                case 'cultures':
                    // Riusa shortcode esistente [toro_culture_prodotto]
                    return do_shortcode('[toro_culture_prodotto]');
                    
                case 'videos':
                    // Riusa shortcode esistente [video_prodotto_v2]
                    return do_shortcode('[video_prodotto_v2]');
                    
                case 'gallery':
                    // Nuova gestione galleria prodotto
                    return self::render_product_gallery($post_id);
                    
                case 'form':
                    // Placeholder per futuro form
                    return '<div class="toro-layout-form"><!-- Form placeholder --></div>';
                    
                default:
                    return '';
            }
        } elseif ($layout_type === 'tipo_prodotto') {
            switch ($section) {
                case 'hero':
                    // Riusa shortcode esistente [hero_tipo_prodotto_e_coltura]
                    return do_shortcode('[hero_tipo_prodotto_e_coltura]');
                    
                case 'description':
                    // Descrizione del termine
                    $term = get_queried_object();
                    return !empty($term->description) ? '<div class="toro-term-description">' . wpautop($term->description) . '</div>' : '';
                    
                case 'products':
                    // Riusa shortcode esistente [toro_prodotti_tipo]
                    return do_shortcode('[toro_prodotti_tipo]');
                    
                case 'documents':
                    // Riusa shortcode esistente [scheda_prodotto_tipo_dettaglio]
                    return do_shortcode('[scheda_prodotto_tipo_dettaglio]');
                    
                case 'videos':
                    // Riusa shortcode esistente [video_tipo_prodotto_v2]
                    return do_shortcode('[video_tipo_prodotto_v2]');
                    
                default:
                    return '';
            }
        } elseif ($layout_type === 'coltura') {
            switch ($section) {
                case 'hero':
                    // Riusa shortcode esistente [hero_tipo_prodotto_e_coltura]
                    return do_shortcode('[hero_tipo_prodotto_e_coltura]');
                    
                case 'description':
                    // Descrizione del termine coltura
                    $term = get_queried_object();
                    return !empty($term->description) ? '<div class="toro-term-description">' . wpautop($term->description) . '</div>' : '';
                    
                case 'products':
                    // Usa shortcode [toro_tipi_per_coltura] con titoli allineati
                    $content = do_shortcode('[toro_tipi_per_coltura]');
                    // Aggiorna titoli da H5 a H4 come da specifiche
                    $content = str_replace(
                        'class="text-bg-dark px-3 py-2 my-4 rounded-2"',
                        'class="fw-bold border-bottom px-3 py-2 my-4"',
                        $content
                    );
                    return $content;
                    
                case 'brochures':
                    // Riusa shortcode [brochure_coltura_dettaglio] con layout adattato
                    $atts = func_get_args()[3] ?? []; // Ottieni attributi dal layout_coltura
                    $brochure_layout = $atts['brochure_layout'] ?? 'card';
                    return do_shortcode('[brochure_coltura_dettaglio layout="' . $brochure_layout . '"]');
                    
                case 'videos':
                    // Video coltura se implementato
                    return '<!-- Video coltura non ancora implementato -->';
                    
                default:
                    return '';
            }
                    
                default:
                    return '';
            }
        }
        
        return '';
    }
    
    /**
     * Renderizza layout adattivo usando template system
     * 
     * @param array $sections Sezioni caricate con contenuto
     * @param array $atts Attributi shortcode
     * @param string $layout_type Tipo di layout
     * @return string HTML finale
     */
    public static function render_adaptive_layout($sections, $atts, $layout_type) {
        if (empty($sections)) {
            return '<div class="toro-layout-empty">Nessun contenuto disponibile</div>';
        }
        
        // Imposta variabili per template
        set_query_var('toro_layout_sections', $sections);
        set_query_var('toro_layout_atts', $atts);
        set_query_var('toro_layout_type', $layout_type);
        
        // Determina template da usare
        $template_name = self::get_layout_template($layout_type);
        
        // Rendering con template
        ob_start();
        $template_file = locate_template('inc/views/layouts/' . $template_name . '.php');
        if ($template_file) {
            include $template_file;
        } else {
            echo '<div class="toro-layout-error">Template non trovato: ' . esc_html($template_name) . '</div>';
        }
        $output = ob_get_clean();
        
        // Pulizia variabili
        set_query_var('toro_layout_sections', null);
        set_query_var('toro_layout_atts', null);
        set_query_var('toro_layout_type', null);
        
        return $output;
    }
    
    /**
     * Determina quale template usare per il layout type
     * 
     * @param string $layout_type
     * @return string Nome template
     */
    private static function get_layout_template($layout_type) {
        $templates = [
            'prodotto' => 'layout-prodotto',
            'tipo_prodotto' => 'layout-tipo-prodotto', 
            'coltura' => 'layout-coltura'
        ];
        
        return $templates[$layout_type] ?? 'layout-prodotto';
    }
    
    /**
     * Pulizia cache quando post viene salvato
     */
    public static function clear_cache_on_post_save($post_id) {
        if (get_post_type($post_id) === 'prodotto') {
            $cache_key = self::CACHE_PREFIX . "content_check_{$post_id}";
            wp_cache_delete($cache_key);
            
            // Pulisci anche cache dei tipi prodotto associati
            $term_ids = wp_get_post_terms($post_id, 'tipo_di_prodotto', ['fields' => 'ids']);
            foreach ($term_ids as $term_id) {
                $cache_key_term = self::CACHE_PREFIX . "tipo_content_check_{$term_id}";
                wp_cache_delete($cache_key_term);
            }
        }
    }
    
    /**
     * Pulizia cache quando meta viene aggiornato
     */
    public static function clear_cache_on_meta_update($meta_id, $object_id, $meta_key, $meta_value) {
        $relevant_keys = ['scheda_prodotto', 'video_prodotto', 'galleria_prodotto', 'form_data'];
        $relevant_term_keys = ['scheda_prodotto_tipo', 'documento_prodotto_tipo', 'tipo-video'];
        
        if (in_array($meta_key, $relevant_keys) && get_post_type($object_id) === 'prodotto') {
            $cache_key = self::CACHE_PREFIX . "content_check_{$object_id}";
            wp_cache_delete($cache_key);
        }
        
        // Pulisci cache per term meta (tipo prodotto)
        if (in_array($meta_key, $relevant_term_keys)) {
            $cache_key_term = self::CACHE_PREFIX . "tipo_content_check_{$object_id}";
            wp_cache_delete($cache_key_term);
        }
    }
    
    /**
     * Verifica se il prodotto ha una galleria di immagini
     * 
     * @param int $product_id ID del prodotto
     * @return bool True se ha galleria (Featured + PODS o solo Featured)
     */
    public static function check_product_gallery($product_id) {
        // Controlla Featured Image
        $has_featured = has_post_thumbnail($product_id);
        
        // Controlla campo PODS galleria_prodotto
        $pods_gallery = get_post_meta($product_id, 'galleria_prodotto', true);
        $has_pods_images = !empty($pods_gallery) && is_array($pods_gallery);
        
        // Se non ha Featured Image e non ha PODS gallery → nessuna galleria
        if (!$has_featured && !$has_pods_images) {
            return false;
        }
        
        // Conta totale immagini
        $total_images = 0;
        if ($has_featured) $total_images++;
        if ($has_pods_images) $total_images += count($pods_gallery);
        
        // Considera "galleria" solo se ha almeno 1 immagine
        return $total_images > 0;
    }
    
    /**
     * Renderizza galleria prodotto con carousel Swiper
     * 
     * @param int $product_id ID del prodotto
     * @return string HTML galleria
     */
    public static function render_product_gallery($product_id) {
        // Raccogli tutte le immagini
        $all_images = [];
        
        // 1. Featured Image sempre prima
        if (has_post_thumbnail($product_id)) {
            $featured_id = get_post_thumbnail_id($product_id);
            $all_images[] = [
                'id' => $featured_id,
                'url' => wp_get_attachment_image_url($featured_id, 'large'),
                'thumb' => wp_get_attachment_image_url($featured_id, 'medium'),
                'alt' => get_post_meta($featured_id, '_wp_attachment_image_alt', true) ?: get_the_title($product_id),
                'type' => 'featured'
            ];
        }
        
        // 2. PODS Gallery Images
        $pods_gallery = get_post_meta($product_id, 'galleria_prodotto', false);
        
        if (!empty($pods_gallery) && is_array($pods_gallery)) {
            foreach ($pods_gallery as $image_id) {
                $image_url = wp_get_attachment_image_url($image_id, 'large');
                $thumb_url = wp_get_attachment_image_url($image_id, 'medium');
                
                if ($image_url) {
                    $all_images[] = [
                        'id' => $image_id,
                        'url' => $image_url,
                        'thumb' => $thumb_url,
                        'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: 'Galleria ' . get_the_title($product_id),
                        'type' => 'gallery'
                    ];
                }
            }
        }
        
        // Se nessuna immagine, ritorna vuoto
        if (empty($all_images)) {
            return '';
        }
        
        // Se solo 1 immagine, mostra singola (no carousel)
        if (count($all_images) === 1) {
            $image = $all_images[0];
            return sprintf(
                '<div class="toro-single-image"><img src="%s" alt="%s" class="img-fluid"></div>',
                esc_url($image['url']),
                esc_attr($image['alt'])
            );
        }
        
        // Multiple immagini: genera carousel Swiper
        return self::generate_swiper_gallery($all_images, $product_id);
    }
    
    /**
     * Genera HTML per carousel Swiper V2 con thumbs sotto e frecce dinamiche
     * 
     * @param array $images Array di immagini
     * @param int $product_id ID prodotto per ID univoci
     * @return string HTML carousel
     */
    private static function generate_swiper_gallery($images, $product_id) {
        $gallery_id = 'toro-gallery-' . $product_id;
        $thumbs_id = 'toro-thumbs-' . $product_id;
        
        $html = '<div class="toro-product-gallery-v2" data-gallery-id="' . $gallery_id . '">';
        
        // Container principale con overflow visible per frecce
        $html .= '<div class="toro-gallery-main-container">';
        
        // Viewport con overflow hidden per nascondere immagini laterali
        $html .= '<div class="toro-gallery-viewport">';
        $html .= sprintf('<div class="swiper toro-gallery-main" id="%s">', $gallery_id);
        $html .= '<div class="swiper-wrapper">';
        
        // Slide immagini principali con aspect ratio data
        foreach ($images as $index => $image) {
            // Ottieni dimensioni reali immagine per aspect ratio
            $image_meta = wp_get_attachment_metadata($image['id']);
            $aspect_ratio = 1; // default square
            if (!empty($image_meta['width']) && !empty($image_meta['height'])) {
                $aspect_ratio = $image_meta['width'] / $image_meta['height'];
            }
            
            $html .= sprintf(
                '<div class="swiper-slide" data-aspect-ratio="%.3f"><img src="%s" alt="%s" class="img-fluid toro-main-image"></div>',
                $aspect_ratio,
                esc_url($image['url']),
                esc_attr($image['alt'])
            );
        }
        
        $html .= '</div>'; // swiper-wrapper
        
        // Frecce native Swiper - elementi HTML richiesti
        $html .= '<div class="swiper-button-next"></div>';
        $html .= '<div class="swiper-button-prev"></div>';
        
        $html .= '</div>'; // swiper main
        $html .= '</div>'; // viewport
        
        // Frecce native Swiper generate automaticamente da navigation: true
        // RIMOSSO: frecce custom HTML che causavano duplicazione
        
        $html .= '</div>'; // main container
        
        // Thumbs orizzontali sotto con crop centrale
        $html .= '<div class="toro-gallery-thumbs-container">';
        $html .= sprintf('<div class="swiper toro-gallery-thumbs" id="%s">', $thumbs_id);
        $html .= '<div class="swiper-wrapper">';
        
        // Thumbs quadrate con crop centrale
        foreach ($images as $image) {
            $html .= sprintf(
                '<div class="swiper-slide toro-thumb-slide"><div class="toro-thumb-wrapper"><img src="%s" alt="%s" class="img-fluid toro-thumb-image"></div></div>',
                esc_url($image['thumb']),
                esc_attr($image['alt'])
            );
        }
        
        $html .= '</div>'; // swiper-wrapper thumbs
        $html .= '</div>'; // swiper thumbs
        $html .= '</div>'; // thumbs container
        
        $html .= '</div>'; // toro-product-gallery-v2
        
        // JavaScript per inizializzare Swiper V2 con frecce dinamiche
        $html .= self::generate_swiper_javascript($gallery_id, $thumbs_id);
        
        return $html;
    }
    
    /**
     * Genera JavaScript per inizializzare carousel Swiper V2 con frecce dinamiche
     * 
     * @param string $gallery_id ID carousel principale
     * @param string $thumbs_id ID carousel thumbs
     * @return string JavaScript inline
     */
    private static function generate_swiper_javascript($gallery_id, $thumbs_id) {
        return sprintf('
        <script>
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof Swiper !== "undefined") {
                const galleryContainer = document.querySelector(`[data-gallery-id="%s"]`);
                if (!galleryContainer) return;
                
                // Inizializza thumbs carousel V2 - orizzontale sotto
                const thumbsSwiper = new Swiper("#%s", {
                    direction: "horizontal",
                    slidesPerView: "auto",
                    spaceBetween: 8,
                    watchSlidesProgress: true,
                    freeMode: true,
                    mousewheel: false,
                    grabCursor: true,
                    centeredSlides: false
                });
                
                // Inizializza main carousel V2 con frecce native Swiper
                const mainSwiper = new Swiper("#%s", {
                    spaceBetween: 0,
                    navigation: {
                        nextEl: ".swiper-button-next",
                        prevEl: ".swiper-button-prev"
                    },
                    thumbs: {
                        swiper: thumbsSwiper
                    },
                    keyboard: {
                        enabled: true
                    },
                    mousewheel: false,
                    loop: true,
                    effect: "slide",
                    speed: 400,
                    autoHeight: false, /* Altezza fissa basata su prima immagine */
                    on: {
                        init: function() {
                            // Frecce native Swiper gestite da CSS
                        }
                    }
                });
                
            } else {
                console.warn("Swiper.js non caricato - galleria prodotto V2 non disponibile");
            }
        });
        </script>',
            $gallery_id,
            $thumbs_id,
            $gallery_id
        );
    }
    
    /**
     * 🔧 Controlla se una coltura ha brochure (WPML aware)
     * 
     * @param int $term_id ID del termine coltura
     * @return bool True se ha brochure nella lingua corrente
     */
    private static function check_coltura_has_brochures($term_id) {
        $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default = apply_filters('wpml_default_language', null);
        
        // 1. Prova PODS per lingua corrente
        $term_id_current = apply_filters('wpml_object_id', $term_id, 'coltura', true, $current) ?: $term_id;
        $pod = pods('coltura', $term_id_current, ['lang' => $current]);
        $items = ($pod && $pod->exists()) ? $pod->field('brochure_coltura') : [];
        
        // Fix per PODS che ritorna false
        if (!is_array($items)) {
            $items = [];
        }
        
        // 2. Fallback a term_meta per lingua default
        if (empty($items)) {
            $term_id_def = apply_filters('wpml_object_id', $term_id, 'coltura', true, $default) ?: $term_id;
            $meta_items = get_term_meta($term_id_def, 'brochure_coltura', false);
            foreach ((array) $meta_items as $raw) {
                $items[] = $raw;
            }
        }
        
        // 3. Controlla se almeno una brochure è valida per la lingua corrente
        foreach ((array) $items as $raw) {
            $id = is_array($raw) && isset($raw['ID']) ? intval($raw['ID']) : (is_object($raw) && isset($raw->ID) ? intval($raw->ID) : intval($raw));
            if (!$id) continue;
            
            $brochure_id = apply_filters('wpml_object_id', $id, 'brochure_coltura', true, $current) ?: $id;
            $slug = wp_get_post_terms($brochure_id, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? '';
            
            // Logica lingua per brochure
            if ($current === 'it') {
                // Italiano: accetta brochure italiane (o senza lingua)
                if (empty($slug) || $slug === 'italiano') {
                    $file_id = get_post_meta($brochure_id, 'brochure-file', true);
                    if ($file_id && wp_get_attachment_url($file_id)) {
                        return true;
                    }
                }
            } else {
                // Altre lingue: priorità lingua target > italiano fallback
                $lang_map = ['en' => 'inglese', 'fr' => 'francese', 'es' => 'spagnolo'];
                $target_lang = $lang_map[$current] ?? '';
                
                // Accetta target language O italiano come fallback
                if ($slug === $target_lang || $slug === 'italiano' || $slug === '') {
                    $file_id = get_post_meta($brochure_id, 'brochure-file', true);
                    if ($file_id && wp_get_attachment_url($file_id)) {
                        return true;
                    }
                }
            }
        }
        
        return false;
    }
     * Replica la logica di scheda-prodotto-dettaglio.php per consistency
     * 
     * @param int $term_id ID del termine tipo_di_prodotto
     * @return bool True se ha documenti nella lingua corrente
     */
    private static function check_tipo_prodotto_has_documents($term_id) {
        $current = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default = apply_filters('wpml_default_language', null);
        
        // 🔧 DEBUG: Log info base se richiesto
        if (isset($_GET['debug_docs'])) {
            error_log("🔧 LAYOUT AVAILABILITY CHECK: Current Lang={$current}, Term ID={$term_id}");
        }
        
        // Helper per controllare esistenza documenti per un campo
        $check_field_exists = function($field, $meta_file_key) use ($term_id, $current, $default) {
            // 1. Prova PODS per lingua corrente
            $term_id_current = apply_filters('wpml_object_id', $term_id, 'tipo_di_prodotto', true, $current) ?: $term_id;
            $pod = pods('tipo_di_prodotto', $term_id_current, ['lang' => $current]);
            $items = ($pod && $pod->exists()) ? $pod->field($field) : [];
            
            // Fix per PODS che ritorna false
            if (!is_array($items)) {
                $items = [];
            }
            
            // 🔧 DEBUG: PODS results
            if (isset($_GET['debug_docs'])) {
                error_log("🔧 LAYOUT CHECK: PODS field={$field}, items count=" . count($items));
            }
            
            // 2. Fallback a term_meta per lingua default
            if (empty($items)) {
                $term_id_def = apply_filters('wpml_object_id', $term_id, 'tipo_di_prodotto', true, $default) ?: $term_id;
                $meta_items = get_term_meta($term_id_def, $field, false);
                foreach ((array) $meta_items as $raw) {
                    $items[] = $raw;
                }
                
                if (isset($_GET['debug_docs'])) {
                    error_log("🔧 LAYOUT CHECK: term_meta items count=" . count($items));
                }
            }
            
            // 3. Controlla se almeno un documento è valido per la lingua corrente
            $has_valid_docs = false;
            foreach ((array) $items as $raw) {
                $id = is_array($raw) && isset($raw['ID']) ? intval($raw['ID']) : (is_object($raw) && isset($raw->ID) ? intval($raw->ID) : intval($raw));
                if (!$id) continue;
                
                $elem_id = apply_filters('wpml_object_id', $id, $field === 'scheda_prodotto_tipo' ? 'scheda_prodotto' : 'documento_prodotto', true, $current) ?: $id;
                $slug = wp_get_post_terms($elem_id, 'lingua_aggiuntiva', ['fields'=>'slugs'])[0] ?? '';
                
                // Logica lingua identica a scheda-prodotto-dettaglio.php
                if ($current === 'it') {
                    // Italiano: accetta documenti italiani (o senza lingua)
                    if (empty($slug) || $slug === 'italiano') {
                        $file_id = get_post_meta($elem_id, $meta_file_key, true);
                        if ($file_id && wp_get_attachment_url($file_id)) {
                            $has_valid_docs = true;
                            break;
                        }
                    }
                } else {
                    // Altre lingue: priorità lingua target > italiano fallback
                    $lang_map = ['en' => 'inglese', 'fr' => 'francese', 'es' => 'spagnolo'];
                    $target_lang = $lang_map[$current] ?? '';
                    
                    // Accetta target language O italiano come fallback
                    if ($slug === $target_lang || $slug === 'italiano' || $slug === '') {
                        $file_id = get_post_meta($elem_id, $meta_file_key, true);
                        if ($file_id && wp_get_attachment_url($file_id)) {
                            $has_valid_docs = true;
                            break;
                        }
                    }
                }
            }
            
            if (isset($_GET['debug_docs'])) {
                error_log("🔧 LAYOUT CHECK: Field {$field} has_valid_docs=" . ($has_valid_docs ? 'YES' : 'NO'));
            }
            
            return $has_valid_docs;
        };
        
        // Controlla entrambi i tipi di documenti
        $has_schede = $check_field_exists('scheda_prodotto_tipo', 'scheda-prodotto');
        $has_docs = $check_field_exists('documento_prodotto_tipo', 'documento-prodotto');
        
        $result = $has_schede || $has_docs;
        
        if (isset($_GET['debug_docs'])) {
            error_log("🔧 LAYOUT CHECK FINAL: Term {$term_id}, Lang {$current}, Result=" . ($result ? 'HAS_DOCS' : 'NO_DOCS'));
        }
        
        return $result;
    }
    
    /**
     * Formatta output di debug
     */
    private static function debug_output($message) {
        if (!self::$debug_mode && strpos($message, '🔧') === false) {
            return '';
        }
        
        return '<div class="toro-debug-output" style="background:#f0f0f0;border:1px solid #ccc;padding:10px;margin:10px 0;font-family:monospace;font-size:12px;white-space:pre-wrap;">' . esc_html($message) . '</div>';
    }
}

// Inizializza il Layout Manager
add_action('init', ['ToroLayoutManager', 'init']);
