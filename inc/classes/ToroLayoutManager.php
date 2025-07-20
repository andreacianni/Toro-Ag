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
            wpml_register_single_string('Toro Layout Manager', 'Chiedi informazioni sul prodotto', 'Chiedi informazioni sul prodotto');
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
     * Layout Manager per tipi di prodotto (placeholder)
     */
    public static function layout_tipo_prodotto($atts) {
        return self::debug_output('🚧 [toro_layout_tipo_prodotto] - In sviluppo...');
    }
    
    /**
     * Layout Manager per colture (placeholder)
     */
    public static function layout_coltura($atts) {
        return self::debug_output('🚧 [toro_layout_coltura] - In sviluppo...');
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
                
                if ($content_map['has_form_data']) {
                    $sections[] = 'form';
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
     * @param int $post_id ID del post
     * @param string $layout_type Tipo di layout
     * @return string Contenuto HTML della sezione
     */
    public static function load_section_content($section, $post_id, $layout_type) {
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
                
            case 'form':
                // Placeholder per futuro form
                return '<div class="toro-layout-form"><!-- Form placeholder --></div>';
                
            default:
                return '';
        }
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
        }
    }
    
    /**
     * Pulizia cache quando meta viene aggiornato
     */
    public static function clear_cache_on_meta_update($meta_id, $object_id, $meta_key, $meta_value) {
        $relevant_keys = ['scheda_prodotto', 'video_prodotto', 'form_data'];
        
        if (in_array($meta_key, $relevant_keys) && get_post_type($object_id) === 'prodotto') {
            $cache_key = self::CACHE_PREFIX . "content_check_{$object_id}";
            wp_cache_delete($cache_key);
        }
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
