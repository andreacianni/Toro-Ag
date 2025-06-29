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
 * Legge il file Excel usando PhpSpreadsheet o fallback
 * VERSIONE MULTIPLA LIBRERIA
 */
function toro_read_excel_data() {
    $excel_file = get_stylesheet_directory() . '/import/DB_News_da importare.xlsx';
    
    if (!file_exists($excel_file)) {
        return new WP_Error('file_not_found', 'File Excel non trovato: ' . $excel_file);
    }
    
    // Prova 1: PhpSpreadsheet (più comune in WordPress)
    if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
        return toro_read_excel_phpspreadsheet($excel_file);
    }
    
    // Prova 2: SimpleXLSX
    if (toro_load_simplexlsx()) {
        return toro_read_excel_simplexlsx($excel_file);
    }
    
    // Prova 3: Spout (se disponibile)
    if (class_exists('Box\Spout\Reader\ReaderFactory')) {
        return toro_read_excel_spout($excel_file);
    }
    
    return new WP_Error('no_excel_library', 
        'Nessuna libreria Excel disponibile. Installa PhpSpreadsheet o converti il file in CSV.');
}

/**
 * Carica SimpleXLSX con controlli
 */
function toro_load_simplexlsx() {
    if (class_exists('SimpleXLSX')) {
        return true;
    }
    
    $xlsx_path = get_stylesheet_directory() . '/import/SimpleXLSX.php';
    
    if (!file_exists($xlsx_path)) {
        return false;
    }
    
    try {
        require_once $xlsx_path;
        return class_exists('SimpleXLSX');
    } catch (Exception $e) {
        error_log('Errore caricamento SimpleXLSX: ' . $e->getMessage());
        return false;
    }
}

/**
 * Lettura con PhpSpreadsheet
 */
function toro_read_excel_phpspreadsheet($excel_file) {
    try {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($excel_file);
        
        $data = [];
        
        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $sheet_name = $worksheet->getTitle();
            $sheet_data = [];
            
            $rows = $worksheet->toArray();
            
            if (empty($rows)) continue;
            
            // Prima riga = headers
            $headers = array_shift($rows);
            
            foreach ($rows as $row) {
                $row_data = [];
                foreach ($headers as $col_index => $header) {
                    $row_data[$header] = $row[$col_index] ?? '';
                }
                $sheet_data[] = $row_data;
            }
            
            $data[$sheet_name] = $sheet_data;
        }
        
        return $data;
        
    } catch (Exception $e) {
        return new WP_Error('phpspreadsheet_error', 'Errore PhpSpreadsheet: ' . $e->getMessage());
    }
}

/**
 * Lettura con SimpleXLSX
 */
function toro_read_excel_simplexlsx($excel_file) {
    try {
        $xlsx = SimpleXLSX::parse($excel_file);
        
        if (!$xlsx) {
            $error_msg = method_exists('SimpleXLSX', 'parseError') 
                ? SimpleXLSX::parseError() 
                : 'Errore sconosciuto';
            return new WP_Error('simplexlsx_parse_error', 'Errore SimpleXLSX: ' . $error_msg);
        }
        
        $data = [];
        $sheets = $xlsx->sheetNames();
        
        foreach ($sheets as $index => $name) {
            $rows = $xlsx->rows($index);
            
            if (empty($rows)) continue;
            
            $headers = array_shift($rows);
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
        return new WP_Error('simplexlsx_exception', 'Errore SimpleXLSX: ' . $e->getMessage());
    }
}

/**
 * Lettura con Spout (fallback)
 */
function toro_read_excel_spout($excel_file) {
    try {
        $reader = \Box\Spout\Reader\ReaderFactory::create(\Box\Spout\Common\Type::XLSX);
        $reader->open($excel_file);
        
        $data = [];
        
        foreach ($reader->getSheetIterator() as $sheet) {
            $sheet_name = $sheet->getName();
            $sheet_data = [];
            $headers = null;
            
            foreach ($sheet->getRowIterator() as $row_index => $row) {
                $row_array = $row->toArray();
                
                if ($row_index === 1) {
                    $headers = $row_array;
                    continue;
                }
                
                if ($headers) {
                    $row_data = [];
                    foreach ($headers as $col_index => $header) {
                        $row_data[$header] = $row_array[$col_index] ?? '';
                    }
                    $sheet_data[] = $row_data;
                }
            }
            
            $data[$sheet_name] = $sheet_data;
        }
        
        $reader->close();
        return $data;
        
    } catch (Exception $e) {
        return new WP_Error('spout_error', 'Errore Spout: ' . $e->getMessage());
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
 * Pulisce il contenuto delle news - Alternativa più semplice e sicura: Pulisci tutto e ricostruisci
 */
function toro_clean_news_content($content) {
    if (empty($content)) return '';
    
    // 1. Escape sequences
    $content = str_replace(['\\r\\n', '\\n'], "\n", $content);
    
    // 2. Strip tutto l'HTML esistente per evitare problemi
    $content = strip_tags($content);
    
    // 3. Decode HTML entities
    $content = html_entity_decode($content, ENT_QUOTES, 'UTF-8');
    
    // 4. Converti BBCode pulito
    $bbcode_patterns = [
        '/\[URL=([^\]]+)\]([^\[]+)\[\/URL\]/i' => '<a href="$1" target="_blank">$2</a>',
        '/\[url=([^\]]+)\]([^\[]+)\[\/url\]/i' => '<a href="$1" target="_blank">$2</a>',
        '/\[B\]([^\[]+)\[\/B\]/i' => '<strong>$1</strong>',
        '/\[b\]([^\[]+)\[\/b\]/i' => '<strong>$1</strong>',
        '/\[I\]([^\[]+)\[\/I\]/i' => '<em>$1</em>',
        '/\[i\]([^\[]+)\[\/i\]/i' => '<em>$1</em>',
    ];
    
    foreach ($bbcode_patterns as $pattern => $replacement) {
        $content = preg_replace($pattern, $replacement, $content);
    }
    
    // 5. URLs normali (solo quelle non già in link)
    $content = preg_replace(
        '/(?<!href=["\'])https?:\/\/[^\s<>"]+(?![^<]*<\/a>)/i',
        '<a href="$0" target="_blank">$0</a>',
        $content
    );
    
    // 6. Fix spacing e paragrafi
    $content = preg_replace('/[ \t]+/', ' ', $content);
    $content = wpautop(trim($content));
    
    return $content;
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
 * Importazione con opzioni - VERSIONE CON TRADUZIONI e MEDIA
 */
function toro_run_full_import($options = []) {
    $defaults = [
        'force_update' => false,
        'import_media' => false,
        'connect_translations' => false,
        'skip_existing' => true
    ];
    
    $options = array_merge($defaults, $options);
    
    $data = toro_read_excel_data();
    
    if (is_wp_error($data)) {
        return $data;
    }
    
    $results = [
        'created' => [],
        'updated' => [],
        'skipped' => [],
        'errors' => [],
        'translations_connected' => [],
        'translations_errors' => [],
        'media_imported' => [],
        'media_errors' => [],
        'total_processed' => 0
    ];
    
    // Aumenta limiti per media
    ini_set('memory_limit', '1024M');
    set_time_limit(0);
    
    // Conta totale news da processare
    $total_news = 0;
    $total_news += count($data['NewsToImport (ITA)'] ?? []);
    $total_news += count($data['NewsToImport (ENG)'] ?? []);
    
    $processed = 0;
    
    // Importa news italiane
    if (isset($data['NewsToImport (ITA)'])) {
        foreach ($data['NewsToImport (ITA)'] as $news) {
        // foreach (array_slice($data['NewsToImport (ITA)'], 0, 10) as $news) { // Limita a 10 per test notizia
            $result = toro_import_single_news_with_media(
                $news, 
                $data, 
                'it', 
                $options['force_update'], 
                $options['import_media']
            );
            
            if (is_wp_error($result)) {
                $results['errors'][] = "News ITA #{$news['news_id']}: " . $result->get_error_message();
            } elseif ($result === 'skipped') {
                $results['skipped'][] = "News ITA #{$news['news_id']}: già esistente";
            } elseif (is_array($result)) {
                $action = $result['action'];
                $results[$action][] = "News ITA #{$news['news_id']} → Post ID: {$result['post_id']}";
                
                // Log media se importati
                if (isset($result['media'])) {
                    $media = $result['media'];
                    if (!empty($media['images'])) {
                        $results['media_imported'][] = "ITA #{$news['news_id']}: " . count($media['images']) . " immagini";
                    }
                    if (!empty($media['documents'])) {
                        $results['media_imported'][] = "ITA #{$news['news_id']}: " . count($media['documents']) . " documenti";
                    }
                    if (!empty($media['errors'])) {
                        $results['media_errors'] = array_merge($results['media_errors'], $media['errors']);
                    }
                }
            }
            
            $processed++;
            $results['total_processed'] = $processed;
        }
    }
    
    // Importa news inglesi
    if (isset($data['NewsToImport (ENG)'])) {
        foreach ($data['NewsToImport (ENG)'] as $news) {
            $result = toro_import_single_news_with_media(
                $news, 
                $data, 
                'en', 
                $options['force_update'], 
                $options['import_media']
            );
            
            if (is_wp_error($result)) {
                $results['errors'][] = "News ENG #{$news['news_id']}: " . $result->get_error_message();
            } elseif ($result === 'skipped') {
                $results['skipped'][] = "News ENG #{$news['news_id']}: già esistente";
            } elseif (is_array($result)) {
                $action = $result['action'];
                $results[$action][] = "News ENG #{$news['news_id']} → Post ID: {$result['post_id']}";
                
                // Log media se importati
                if (isset($result['media'])) {
                    $media = $result['media'];
                    if (!empty($media['images'])) {
                        $results['media_imported'][] = "ENG #{$news['news_id']}: " . count($media['images']) . " immagini";
                    }
                    if (!empty($media['documents'])) {
                        $results['media_imported'][] = "ENG #{$news['news_id']}: " . count($media['documents']) . " documenti";
                    }
                    if (!empty($media['errors'])) {
                        $results['media_errors'] = array_merge($results['media_errors'], $media['errors']);
                    }
                }
            }
            
            $processed++;
            $results['total_processed'] = $processed;
        }
    }
    
    // Collega traduzioni WPML se richiesto
    if ($options['connect_translations'] && isset($data['Tabella_ID_traduzioni'])) {
        $translation_results = toro_connect_wpml_translations($data['Tabella_ID_traduzioni']);
        
        if (is_wp_error($translation_results)) {
            $results['translations_errors'][] = $translation_results->get_error_message();
        } else {
            $results['translations_connected'] = $translation_results['connected'];
            $results['translations_errors'] = array_merge(
                $results['translations_errors'], 
                $translation_results['errors']
            );
            
            // Condividi media tra traduzioni se import_media è attivo
            if ($options['import_media']) {
                foreach ($translation_results['connected'] as $connection) {
                    // Estrai gli ID dai messaggi di log
                    if (preg_match('/Post ITA (\d+).*Post ENG (\d+)/', $connection, $matches)) {
                        $ita_id = $matches[1];
                        $eng_id = $matches[2];
                        toro_share_media_between_translations($ita_id, $eng_id);
                    }
                }
            }
        }
    }
    
    return $results;
}

/**
 * Importa singola news - VERSIONE CON UPDATE
 */
function toro_import_single_news($news_data, $all_data, $lang = 'it', $force_update = false) {
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
    
    $is_update = !empty($existing);
    $post_id = $is_update ? $existing[0]->ID : 0;
    
    // 🔧 NUOVO: Gestione modalità update
    if ($is_update && !$force_update) {
        return 'skipped';
    }
    
    // Pulisci contenuto con la nuova funzione
    $content = toro_clean_news_content($news_data['news_contenuto'] ?? '');
    
    // Dati del post
    $post_data = [
        'post_title' => sanitize_text_field($news_data['news_titolo'] ?? ''),
        'post_content' => $content,
        'post_status' => 'publish',
        'post_type' => 'post',
        'post_date' => toro_parse_excel_date($news_data['news_data'] ?? ''),
        'meta_input' => [
            'news_id_originale' => $news_id
        ]
    ];
    
    if ($is_update) {
        // 🔧 AGGIORNA post esistente
        $post_data['ID'] = $post_id;
        $result = wp_update_post($post_data);
        
        if (is_wp_error($result)) {
            return new WP_Error('post_update_failed', 'Aggiornamento post fallito: ' . $result->get_error_message());
        }
        
        $action = 'updated';
    } else {
        // 🔧 CREA nuovo post
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return new WP_Error('post_creation_failed', 'Creazione post fallita: ' . $post_id->get_error_message());
        }
        
        $action = 'created';
    }
    
    // Imposta lingua WPML (solo per nuovi post)
    if (!$is_update && function_exists('icl_object_id')) {
        do_action('wpml_set_element_language_details', [
            'element_id' => $post_id,
            'element_type' => 'post_post',
            'language_code' => $lang
        ]);
    }
    
    // Gestisci categoria (sempre, anche per update)
    $category_name = $news_data['newscat_nome'] ?? '';
    if (!empty($category_name)) {
        $category_id = toro_create_news_category($category_name, $lang);
        if ($category_id) {
            wp_set_post_categories($post_id, [$category_id]);
        }
    }
    
    // TODO: Gestire immagini e documenti
    // (implementeremo nel prossimo step)
    
    return [
        'post_id' => $post_id,
        'action' => $action
    ];
}

/**
 * Collega tutte le traduzioni WPML
 */
function toro_connect_wpml_translations($translation_data) {
    if (!function_exists('icl_object_id')) {
        return new WP_Error('wpml_not_active', 'WPML non è attivo');
    }
    
    $results = [
        'connected' => [],
        'errors' => [],
        'skipped' => []
    ];
    
    foreach ($translation_data as $translation) {
        $ita_id = $translation['Ita-id'] ?? 0;
        $eng_id = $translation['Eng-id'] ?? 0;
        
        if (empty($ita_id) || empty($eng_id)) {
            $results['errors'][] = "Mapping vuoto: ITA={$ita_id}, ENG={$eng_id}";
            continue;
        }
        
        // Trova i post WordPress corrispondenti
        $ita_post = toro_find_post_by_news_id($ita_id);
        $eng_post = toro_find_post_by_news_id($eng_id);
        
        if (!$ita_post) {
            $results['errors'][] = "Post italiano non trovato per news_id: {$ita_id}";
            continue;
        }
        
        if (!$eng_post) {
            $results['errors'][] = "Post inglese non trovato per news_id: {$eng_id}";
            continue;
        }
        
        // Verifica se già collegati
        if (toro_are_posts_already_connected($ita_post->ID, $eng_post->ID)) {
            $results['skipped'][] = "Già collegati: Post ITA {$ita_post->ID} ↔ Post ENG {$eng_post->ID}";
            continue;
        }
        
        // Collega le traduzioni
        $connect_result = toro_connect_post_translations($ita_post->ID, $eng_post->ID);
        
        if (is_wp_error($connect_result)) {
            $results['errors'][] = "Errore collegamento ITA {$ita_post->ID} ↔ ENG {$eng_post->ID}: " . $connect_result->get_error_message();
        } else {
            $results['connected'][] = "Collegati: Post ITA {$ita_post->ID} ({$ita_post->post_title}) ↔ Post ENG {$eng_post->ID} ({$eng_post->post_title})";
        }
    }
    
    return $results;
}

/**
 * Trova un post WordPress dal news_id originale
 */
function toro_find_post_by_news_id($news_id) {
    $posts = get_posts([
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
    
    return !empty($posts) ? $posts[0] : null;
}

/**
 * Verifica se due post sono già collegati come traduzioni
 */
function toro_are_posts_already_connected($post_id_1, $post_id_2) {
    if (!function_exists('icl_object_id')) {
        return false;
    }
    
    // Ottieni il trid (translation group ID) del primo post
    $trid_1 = apply_filters('wpml_element_trid', null, $post_id_1, 'post_post');
    $trid_2 = apply_filters('wpml_element_trid', null, $post_id_2, 'post_post');
    
    // Se hanno lo stesso trid (e non è null), sono già collegati
    return !empty($trid_1) && !empty($trid_2) && $trid_1 === $trid_2;
}

/**
 * Collega due post come traduzioni WPML
 */
function toro_connect_post_translations($ita_post_id, $eng_post_id) {
    if (!function_exists('icl_object_id')) {
        return new WP_Error('wpml_not_active', 'WPML non è attivo');
    }
    
    try {
        // 1. Assicurati che i post abbiano le lingue corrette
        do_action('wpml_set_element_language_details', [
            'element_id' => $ita_post_id,
            'element_type' => 'post_post',
            'language_code' => 'it'
        ]);
        
        do_action('wpml_set_element_language_details', [
            'element_id' => $eng_post_id,
            'element_type' => 'post_post',
            'language_code' => 'en',
            'source_language_code' => 'it'
        ]);
        
        // 2. Ottieni o crea il TRID (Translation ID)
        $trid = apply_filters('wpml_element_trid', null, $ita_post_id, 'post_post');
        
        if (empty($trid)) {
            // Se il post italiano non ha ancora un TRID, WPML ne creerà uno
            // Riapplica la lingua per forzare la creazione del TRID
            do_action('wpml_set_element_language_details', [
                'element_id' => $ita_post_id,
                'element_type' => 'post_post',
                'language_code' => 'it'
            ]);
            
            $trid = apply_filters('wpml_element_trid', null, $ita_post_id, 'post_post');
        }
        
        // 3. Collega il post inglese al TRID del post italiano
        if (!empty($trid)) {
            do_action('wpml_set_element_language_details', [
                'element_id' => $eng_post_id,
                'element_type' => 'post_post',
                'language_code' => 'en',
                'source_language_code' => 'it',
                'trid' => $trid
            ]);
        }
        
        // 4. Verifica che il collegamento sia avvenuto
        $ita_trid = apply_filters('wpml_element_trid', null, $ita_post_id, 'post_post');
        $eng_trid = apply_filters('wpml_element_trid', null, $eng_post_id, 'post_post');
        
        if ($ita_trid !== $eng_trid || empty($ita_trid)) {
            return new WP_Error('connection_failed', 'WPML non ha collegato i post correttamente');
        }
        
        return [
            'ita_post_id' => $ita_post_id,
            'eng_post_id' => $eng_post_id,
            'trid' => $ita_trid
        ];
        
    } catch (Exception $e) {
        return new WP_Error('exception', 'Errore durante collegamento: ' . $e->getMessage());
    }
}
/**
 * Sistema di import media avanzato
 * Aggiungi queste funzioni al file inc/news-import-functions.php
 */

/**
 * Cache per evitare duplicati
 */
class ToroMediaCache {
    private static $url_cache = [];
    private static $hash_cache = [];
    
    public static function get_by_url($url) {
        return self::$url_cache[$url] ?? null;
    }
    
    public static function set_url($url, $attachment_id) {
        self::$url_cache[$url] = $attachment_id;
    }
    
    public static function get_by_hash($hash) {
        return self::$hash_cache[$hash] ?? null;
    }
    
    public static function set_hash($hash, $attachment_id) {
        self::$hash_cache[$hash] = $attachment_id;
    }
}

/**
 * Import media con deduplicazione e ottimizzazione
 */
function toro_import_media_advanced($url, $title = '', $post_id = 0, $optimize = true) {
    if (empty($url)) {
        return new WP_Error('empty_url', 'URL vuoto');
    }
    
    // 1. Controlla cache URL
    $cached_id = ToroMediaCache::get_by_url($url);
    if ($cached_id && get_post($cached_id)) {
        return [
            'attachment_id' => $cached_id,
            'action' => 'cached_url'
        ];
    }
    
    // 2. Download temporaneo per analisi
    $temp_file = download_url($url);
    if (is_wp_error($temp_file)) {
        return new WP_Error('download_failed', 'Download fallito: ' . $temp_file->get_error_message());
    }
    
    // 3. Calcola hash per deduplicazione
    $file_hash = md5_file($temp_file);
    $cached_by_hash = ToroMediaCache::get_by_hash($file_hash);
    if ($cached_by_hash && get_post($cached_by_hash)) {
        unlink($temp_file);
        ToroMediaCache::set_url($url, $cached_by_hash); // Aggiorna cache URL
        return [
            'attachment_id' => $cached_by_hash,
            'action' => 'cached_hash'
        ];
    }
    
    // 4. Determina tipo file e ottimizzazioni
    $file_info = toro_analyze_file($temp_file, $url);
    
    // 5. Ottimizza se necessario
    if ($optimize && $file_info['can_optimize']) {
        $optimized_file = toro_optimize_image($temp_file, $file_info);
        if (!is_wp_error($optimized_file)) {
            unlink($temp_file);
            $temp_file = $optimized_file;
            $file_info = toro_analyze_file($temp_file, $url);
        }
    }
    
    // 6. Prepara per upload
    $file_array = [
        'name' => $file_info['filename'],
        'tmp_name' => $temp_file,
        'type' => $file_info['mime_type']
    ];
    
    // 7. Include WordPress media functions
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    
    // 8. Import in media library
    $attachment_id = media_handle_sideload($file_array, $post_id, $title);
    
    // 9. Cleanup
    if (file_exists($temp_file)) {
        unlink($temp_file);
    }
    
    if (is_wp_error($attachment_id)) {
        return new WP_Error('import_failed', 'Import fallito: ' . $attachment_id->get_error_message());
    }
    
    // 10. Salva in cache
    ToroMediaCache::set_url($url, $attachment_id);
    ToroMediaCache::set_hash($file_hash, $attachment_id);
    
    // 11. Salva metadati originali
    update_post_meta($attachment_id, '_toro_original_url', $url);
    update_post_meta($attachment_id, '_toro_file_hash', $file_hash);
    
    return [
        'attachment_id' => $attachment_id,
        'action' => 'imported',
        'optimized' => $optimize && $file_info['can_optimize']
    ];
}

/**
 * Analizza file per determinare ottimizzazioni
 */
function toro_analyze_file($file_path, $original_url) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file_path);
    finfo_close($finfo);
    
    $path_info = pathinfo($original_url);
    $extension = strtolower($path_info['extension'] ?? '');
    
    // Genera nome file pulito
    $filename = sanitize_file_name($path_info['filename'] ?? 'media');
    
    $can_optimize = false;
    $target_format = null;
    
    // Determina se può essere ottimizzato
    if (in_array($mime_type, ['image/png', 'image/gif']) && 
        function_exists('imagecreatefromstring')) {
        $can_optimize = true;
        $target_format = 'jpg';
        $filename = preg_replace('/\.(png|gif)$/i', '.jpg', $filename);
        $mime_type = 'image/jpeg';
    }
    
    // Gestione documenti con estensioni multiple
    $allowed_docs = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];
    if (in_array($extension, $allowed_docs)) {
        $filename .= '.' . $extension;
    } elseif (strpos($mime_type, 'image/') === 0) {
        $filename .= '.' . ($target_format ?: $extension ?: 'jpg');
    } else {
        $filename .= '.' . ($extension ?: 'file');
    }
    
    return [
        'filename' => $filename,
        'mime_type' => $mime_type,
        'extension' => $extension,
        'can_optimize' => $can_optimize,
        'target_format' => $target_format
    ];
}

/**
 * Ottimizza immagini (PNG → JPG compresso)
 */
function toro_optimize_image($file_path, $file_info) {
    if (!function_exists('imagecreatefromstring')) {
        return new WP_Error('gd_not_available', 'GD library non disponibile');
    }
    
    $image_data = file_get_contents($file_path);
    $image = imagecreatefromstring($image_data);
    
    if (!$image) {
        return new WP_Error('image_creation_failed', 'Impossibile creare immagine');
    }
    
    // Crea immagine JPG ottimizzata
    $width = imagesx($image);
    $height = imagesy($image);
    
    // Crea canvas bianco per PNG con trasparenza
    $optimized = imagecreatetruecolor($width, $height);
    $white = imagecolorallocate($optimized, 255, 255, 255);
    imagefill($optimized, 0, 0, $white);
    
    // Copia immagine originale
    imagecopy($optimized, $image, 0, 0, 0, 0, $width, $height);
    
    // Genera file temporaneo ottimizzato
    $temp_optimized = wp_tempnam('toro_optimized_');
    
    // Salva come JPG con qualità 85
    $success = imagejpeg($optimized, $temp_optimized, 85);
    
    // Cleanup
    imagedestroy($image);
    imagedestroy($optimized);
    
    if (!$success) {
        return new WP_Error('optimization_failed', 'Ottimizzazione fallita');
    }
    
    return $temp_optimized;
}

/**
 * Import media per singola news
 */
function toro_import_news_media($news_data, $images_data, $docs_data, $post_id, $options = []) {
    $defaults = [
        'optimize_images' => true,
        'set_featured' => true,
        'share_between_translations' => true
    ];
    
    $options = array_merge($defaults, $options);
    $results = [
        'images' => [],
        'documents' => [],
        'featured_set' => false,
        'errors' => []
    ];
    
    $news_id = $news_data['news_id'] ?? 0;
    
    // 1. Import immagini
    $related_images = array_filter($images_data, function($img) use ($news_id) {
        return ($img['news_id'] ?? 0) == $news_id;
    });
    
    foreach ($related_images as $img) {
        $url = $img['newsfoto_url'] ?? '';
        if (empty($url)) continue;
        
        $title = $news_data['news_titolo'] ?? '';
        $result = toro_import_media_advanced($url, "Immagine: $title", $post_id, $options['optimize_images']);
        
        if (is_wp_error($result)) {
            $results['errors'][] = "Errore immagine $url: " . $result->get_error_message();
            continue;
        }
        
        $attachment_id = $result['attachment_id'];
        $results['images'][] = [
            'attachment_id' => $attachment_id,
            'url' => $url,
            'action' => $result['action'],
            'optimized' => $result['optimized'] ?? false
        ];
        
        // Imposta come featured image se è la prima
        if ($options['set_featured'] && !$results['featured_set']) {
            set_post_thumbnail($post_id, $attachment_id);
            $results['featured_set'] = true;
        }
    }
    
    // 2. Import documenti
    $related_docs = array_filter($docs_data, function($doc) use ($news_id) {
        return ($doc['news_id'] ?? 0) == $news_id;
    });
    
    $doc_attachments = [];
    foreach ($related_docs as $doc) {
        $filename = $doc['newsdoc_file'] ?? '';
        if (empty($filename)) continue;
        
        // Costruisci URL completo
        $doc_url = 'https://www.toro-ag.it/public/news_documenti/' . $filename;
        $doc_title = $doc['newsdoc_titolo'] ?? $filename;
        
        $result = toro_import_media_advanced($doc_url, "Documento: $doc_title", $post_id, false);
        
        if (is_wp_error($result)) {
            $results['errors'][] = "Errore documento $doc_url: " . $result->get_error_message();
            continue;
        }
        
        $attachment_id = $result['attachment_id'];
        $doc_attachments[] = $attachment_id;
        
        $results['documents'][] = [
            'attachment_id' => $attachment_id,
            'title' => $doc_title,
            'filename' => $filename,
            'action' => $result['action']
        ];
    }
    
    // 3. Salva documenti in PODS
    if (!empty($doc_attachments) && function_exists('pods')) {
        $pod = pods('post', $post_id);
        if ($pod) {
            $pod->save('news_documenti', $doc_attachments);
        }
    }
    
    // 4. Salva immagini in PODS
    if (!empty($results['images']) && function_exists('pods')) {
        $image_ids = array_column($results['images'], 'attachment_id');
        $pod = pods('post', $post_id);
        if ($pod) {
            $pod->save('news_immagini', $image_ids);
        }
    }
    
    return $results;
}

/**
 * Condividi media tra traduzioni collegate
 */
function toro_share_media_between_translations($ita_post_id, $eng_post_id) {
    if (!function_exists('pods')) {
        return false;
    }
    
    // Ottieni media del post italiano
    $ita_pod = pods('post', $ita_post_id);
    if (!$ita_pod) return false;
    
    $featured_image = get_post_thumbnail_id($ita_post_id);
    $images = $ita_pod->field('news_immagini');
    $documents = $ita_pod->field('news_documenti');
    
    // Applica al post inglese
    $eng_pod = pods('post', $eng_post_id);
    if (!$eng_pod) return false;
    
    // Featured image
    if ($featured_image) {
        set_post_thumbnail($eng_post_id, $featured_image);
    }
    
    // Immagini
    if (!empty($images)) {
        $eng_pod->save('news_immagini', $images);
    }
    
    // Documenti  
    if (!empty($documents)) {
        $eng_pod->save('news_documenti', $documents);
    }
    
    return true;
}

/**
 * Aggiorna la funzione di import singola news per supportare media
 */
function toro_import_single_news_with_media($news_data, $all_data, $lang = 'it', $force_update = false, $import_media = false) {
    // Import base della news
    $result = toro_import_single_news($news_data, $all_data, $lang, $force_update);
    
    if (is_wp_error($result) || $result === 'skipped') {
        return $result;
    }
    
    $post_id = is_array($result) ? $result['post_id'] : $result;
    
    // Import media se richiesto
    if ($import_media) {
        $images_data = $all_data['ImgToImport'] ?? [];
        $docs_data = $all_data['Doc-ToImport'] ?? [];
        
        $media_result = toro_import_news_media($news_data, $images_data, $docs_data, $post_id);
        
        if (is_array($result)) {
            $result['media'] = $media_result;
        }
    }
    
    return $result;
}