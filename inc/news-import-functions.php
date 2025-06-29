<?php
/**
 * Funzioni di importazione news da Excel
 * File: inc/news-import-functions.php
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Legge il file Excel e restituisce i dati strutturati
 */
function toro_read_excel_data() {
    $excel_file = get_stylesheet_directory() . '/import/DB_News_da importare.xlsx';
    
    if (!file_exists($excel_file)) {
        return new WP_Error('file_not_found', 'File Excel non trovato: ' . $excel_file);
    }
    
    // Usa SimpleXLSX per leggere Excel (incluso in molti hosting)
    if (!class_exists('SimpleXLSX')) {
        require_once get_stylesheet_directory() . '/import/SimpleXLSX.php';
    }
    
    try {
        $xlsx = SimpleXLSX::parse($excel_file);
        
        if (!$xlsx) {
            return new WP_Error('parse_error', 'Errore lettura Excel: ' . SimpleXLSX::parseError());
        }
        
        $data = [];
        
        // Leggi fogli specifici
        $sheets = $xlsx->sheetNames();
        
        foreach ($sheets as $index => $name) {
            $rows = $xlsx->rows($index);
            
            if (empty($rows)) continue;
            
            // Prima riga = header
            $headers = array_shift($rows);
            
            // Converti in array associativo
            $sheet_data = [];
            foreach ($rows as $row) {
                $row_data = [];
                foreach ($headers as $col_index => $header) {
                    $row_data[$header] = $row[$col_index] ?? '';
                }
                $sheet_data[] = $row_data;
            }
            
            $data[$name] = $sheet_data;
        }
        
        return $data;
        
    } catch (Exception $e) {
        return new WP_Error('exception', 'Errore: ' . $e->getMessage());
    }
}

/**
 * Analizza i dati Excel e restituisce statistiche
 */
function toro_analyze_excel_data() {
    $data = toro_read_excel_data();
    
    if (is_wp_error($data)) {
        return $data;
    }
    
    $stats = [
        'news_ita' => count($data['NewsToImport (ITA)'] ?? []),
        'news_eng' => count($data['NewsToImport (ENG)'] ?? []),
        'traduzioni' => count($data['Tabella_ID_traduzioni'] ?? []),
        'immagini' => count($data['ImgToImport'] ?? []),
        'documenti' => count($data['Doc-ToImport'] ?? []),
        'fogli_totali' => count($data),
        'categorie_ita' => [],
        'categorie_eng' => [],
        'range_date' => ['min' => null, 'max' => null]
    ];
    
    // Analizza categorie italiane
    if (isset($data['NewsToImport (ITA)'])) {
        $cats = array_column($data['NewsToImport (ITA)'], 'newscat_nome');
        $stats['categorie_ita'] = array_count_values(array_filter($cats));
    }
    
    // Analizza categorie inglesi
    if (isset($data['NewsToImport (ENG)'])) {
        $cats = array_column($data['NewsToImport (ENG)'], 'newscat_nome');
        $stats['categorie_eng'] = array_count_values(array_filter($cats));
    }
    
    // Analizza range date
    $all_dates = [];
    if (isset($data['NewsToImport (ITA)'])) {
        $all_dates = array_merge($all_dates, array_column($data['NewsToImport (ITA)'], 'news_data'));
    }
    if (isset($data['NewsToImport (ENG)'])) {
        $all_dates = array_merge($all_dates, array_column($data['NewsToImport (ENG)'], 'news_data'));
    }
    
    $valid_dates = array_filter($all_dates, function($date) {
        return !empty($date) && strtotime($date) !== false;
    });
    
    if (!empty($valid_dates)) {
        $timestamps = array_map('strtotime', $valid_dates);
        $stats['range_date'] = [
            'min' => date('Y-m-d', min($timestamps)),
            'max' => date('Y-m-d', max($timestamps))
        ];
    }
    
    return $stats;
}

/**
 * Pulisce il contenuto delle news
 */
function toro_clean_news_content($content) {
    if (empty($content)) return '';
    
    $cleaned = $content;
    
    // Fix escape sequences
    $cleaned = str_replace(['\\r\\n', '\\n'], "\n", $cleaned);
    
    // Fix multiple newlines
    $cleaned = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $cleaned);
    
    // Converti URLs in link HTML
    $cleaned = preg_replace(
        '/(https?:\/\/[^\s]+)/',
        '<a href="$1" target="_blank">$1</a>',
        $cleaned
    );
    
    // Fix spacing
    $cleaned = preg_replace('/[ \t]+/', ' ', $cleaned);
    
    // Converti in paragrafi WordPress
    $cleaned = wpautop(trim($cleaned));
    
    return $cleaned;
}

/**
 * Crea o trova una categoria
 */
function toro_create_news_category($name, $lang = 'it') {
    // Mapping categorie ITA → ENG
    $category_mapping = [
        'Eventi' => 'Events',
        'Fiere' => 'Fairs',
        'Prodotti' => 'Products',
        'Social' => 'Social',
        'Formazione' => null // Solo italiano
    ];
    
    // Trova o crea categoria
    $term = get_term_by('name', $name, 'category');
    
    if (!$term) {
        $result = wp_insert_term($name, 'category');
        if (is_wp_error($result)) {
            return false;
        }
        $term_id = $result['term_id'];
    } else {
        $term_id = $term->term_id;
    }
    
    // Gestione WPML se attivo
    if (function_exists('icl_object_id') && $lang === 'it') {
        // Registra come italiano
        do_action('wpml_set_element_language_details', [
            'element_id' => $term_id,
            'element_type' => 'tax_category',
            'language_code' => 'it'
        ]);
        
        // Se ha traduzione inglese, creala
        $eng_name = $category_mapping[$name] ?? null;
        if ($eng_name) {
            $eng_term = get_term_by('name', $eng_name, 'category');
            
            if (!$eng_term) {
                $eng_result = wp_insert_term($eng_name, 'category');
                if (!is_wp_error($eng_result)) {
                    $eng_term_id = $eng_result['term_id'];
                    
                    // Registra come inglese
                    do_action('wpml_set_element_language_details', [
                        'element_id' => $eng_term_id,
                        'element_type' => 'tax_category',
                        'language_code' => 'en',
                        'source_language_code' => 'it'
                    ]);
                    
                    // Collega traduzioni
                    do_action('wpml_connect_translations', [
                        'element_id' => $term_id,
                        'element_type' => 'tax_category',
                        'translations' => [
                            'it' => $term_id,
                            'en' => $eng_term_id
                        ]
                    ]);
                }
            }
        }
    }
    
    return $term_id;
}

/**
 * Download e importa file nella media library
 */
function toro_import_media_file($url, $title = '', $post_id = 0) {
    if (empty($url)) return false;
    
    // Include WordPress media functions
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // Download temporaneo
    $temp_file = download_url($url);
    
    if (is_wp_error($temp_file)) {
        return new WP_Error('download_failed', 'Download fallito: ' . $temp_file->get_error_message());
    }
    
    // Info file
    $file_array = [
        'name' => basename($url),
        'tmp_name' => $temp_file
    ];
    
    // Import in media library
    $attachment_id = media_handle_sideload(
        $file_array,
        $post_id,
        $title
    );
    
    // Cleanup
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    if (is_wp_error($attachment_id)) {
        return new WP_Error('import_failed', 'Import fallito: ' . $attachment_id->get_error_message());
    }
    
    return $attachment_id;
}

/**
 * Dry run - simula importazione senza creare contenuti
 */
function toro_dry_run_import() {
    $data = toro_read_excel_data();
    
    if (is_wp_error($data)) {
        return $data;
    }
    
    $report = [
        'would_create' => [],
        'would_skip' => [],
        'errors' => [],
        'warnings' => []
    ];
    
    // Simula importazione news italiane
    if (isset($data['NewsToImport (ITA)'])) {
        foreach ($data['NewsToImport (ITA)'] as $news) {
            $news_id = $news['news_id'] ?? 0;
            $title = $news['news_titolo'] ?? '';
            
            // Controlla se esiste già
            $existing = get_posts([
                'meta_query' => [
                    [
                        'key' => 'news_id_originale',
                        'value' => $news_id,
                        'compare' => '='
                    ]
                ],
                'post_type' => 'post',
                'post_status' => 'any',
                'posts_per_page' => 1
            ]);
            
            if (!empty($existing)) {
                $report['would_skip'][] = "News ITA #{$news_id}: già esistente (Post ID: {$existing[0]->ID})";
            } else {
                $report['would_create'][] = "News ITA #{$news_id}: {$title}";
            }
        }
    }
    
    // Simula importazione news inglesi
    if (isset($data['NewsToImport (ENG)'])) {
        foreach ($data['NewsToImport (ENG)'] as $news) {
            $news_id = $news['news_id'] ?? 0;
            $title = $news['news_titolo'] ?? '';
            
            $existing = get_posts([
                'meta_query' => [
                    [
                        'key' => 'news_id_originale',
                        'value' => $news_id,
                        'compare' => '='
                    ]
                ],
                'post_type' => 'post',
                'post_status' => 'any',
                'posts_per_page' => 1
            ]);
            
            if (!empty($existing)) {
                $report['would_skip'][] = "News ENG #{$news_id}: già esistente (Post ID: {$existing[0]->ID})";
            } else {
                $report['would_create'][] = "News ENG #{$news_id}: {$title}";
            }
        }
    }
    
    return $report;
}

/**
 * Importazione vera e propria
 */
function toro_run_full_import($progress_callback = null) {
    $data = toro_read_excel_data();
    
    if (is_wp_error($data)) {
        return $data;
    }
    
    $results = [
        'created' => [],
        'skipped' => [],
        'errors' => [],
        'total_processed' => 0
    ];
    
    // Aumenta limiti
    ini_set('memory_limit', '512M');
    set_time_limit(0);
    
    // Conta totale news da processare
    $total_news = 0;
    $total_news += count($data['NewsToImport (ITA)'] ?? []);
    $total_news += count($data['NewsToImport (ENG)'] ?? []);
    
    $processed = 0;
    
    // Importa news italiane
    if (isset($data['NewsToImport (ITA)'])) {
        foreach ($data['NewsToImport (ITA)'] as $news) {
            $result = toro_import_single_news($news, $data, 'it');
            
            if (is_wp_error($result)) {
                $results['errors'][] = $result->get_error_message();
            } elseif ($result === 'skipped') {
                $results['skipped'][] = "News ITA #{$news['news_id']}: già esistente";
            } else {
                $results['created'][] = "News ITA #{$news['news_id']} → Post ID: {$result}";
            }
            
            $processed++;
            $results['total_processed'] = $processed;
            
            // Callback per progress
            if ($progress_callback && is_callable($progress_callback)) {
                call_user_func($progress_callback, $processed, $total_news, $news['news_titolo'] ?? '');
            }
        }
    }
    
    // Importa news inglesi
    if (isset($data['NewsToImport (ENG)'])) {
        foreach ($data['NewsToImport (ENG)'] as $news) {
            $result = toro_import_single_news($news, $data, 'en');
            
            if (is_wp_error($result)) {
                $results['errors'][] = $result->get_error_message();
            } elseif ($result === 'skipped') {
                $results['skipped'][] = "News ENG #{$news['news_id']}: già esistente";
            } else {
                $results['created'][] = "News ENG #{$news['news_id']} → Post ID: {$result}";
            }
            
            $processed++;
            $results['total_processed'] = $processed;
            
            if ($progress_callback && is_callable($progress_callback)) {
                call_user_func($progress_callback, $processed, $total_news, $news['news_titolo'] ?? '');
            }
        }
    }
    
    return $results;
}

/**
 * Importa singola news
 */
function toro_import_single_news($news_data, $all_data, $lang = 'it') {
    $news_id = $news_data['news_id'] ?? 0;
    
    // Controlla se esiste già
    $existing = get_posts([
        'meta_query' => [
            [
                'key' => 'news_id_originale',
                'value' => $news_id,
                'compare' => '='
            ]
        ],
        'post_type' => 'post',
        'post_status' => 'any',
        'posts_per_page' => 1
    ]);
    
    if (!empty($existing)) {
        return 'skipped';
    }
    
    // Pulisci contenuto
    $content = toro_clean_news_content($news_data['news_contenuto'] ?? '');
    
    // Dati del post
    $post_data = [
        'post_title' => sanitize_text_field($news_data['news_titolo'] ?? ''),
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_date' => date('Y-m-d H:i:s', strtotime($news_data['news_data'] ?? 'now')),
        'meta_input' => [
            'news_id_originale' => $news_id
        ]
    ];
    
    // Crea post
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        return new WP_Error('post_creation_failed', 'Creazione post fallita: ' . $post_id->get_error_message());
    }
    
    // Imposta lingua WPML
    if (function_exists('icl_object_id')) {
        do_action('wpml_set_element_language_details', [
            'element_id' => $post_id,
            'element_type' => 'post_post',
            'language_code' => $lang
        ]);
    }
    
    // Gestisci categoria
    $category_name = $news_data['newscat_nome'] ?? '';
    if (!empty($category_name)) {
        $category_id = toro_create_news_category($category_name, $lang);
        if ($category_id) {
            wp_set_post_categories($post_id, [$category_id]);
        }
    }
    
    // TODO: Gestire immagini e documenti
    // (implementeremo nel prossimo step)
    
    return $post_id;
}