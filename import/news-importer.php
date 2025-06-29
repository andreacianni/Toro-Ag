<?php
/**
 * Pagina Admin per importazione news
 * File: import/news-importer.php
 */

// Previeni accesso diretto
if (!defined('ABSPATH')) {
    exit;
}

// Gestisci azioni AJAX
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'analyze':
            $stats = toro_analyze_excel_data();
            wp_send_json_success($stats);
            break;
            
        case 'dry_run':
            $report = toro_dry_run_import();
            wp_send_json_success($report);
            break;
            
        case 'full_import':
            // TODO: Implementare importazione progressiva via AJAX
            wp_send_json_error('Funzione non ancora implementata');
            break;
    }
    exit;
}

// Analizza dati se richiesto
$stats = null;
$dry_run_report = null;

if (isset($_GET['analyze'])) {
    $stats = toro_analyze_excel_data();
}

if (isset($_GET['dry_run'])) {
    $dry_run_report = toro_dry_run_import();
}
?>

<div class="wrap toro-import-container">
    <h1>🚀 Importazione News da Excel</h1>
    
    <div class="notice notice-info">
        <p><strong>Attenzione:</strong> Questo strumento importerà le news dal file Excel nel database WordPress. 
        Esegui sempre un <strong>backup del database</strong> prima di procedere!</p>
    </div>

    <!-- Controllo file Excel -->
    <div class="postbox">
        <div class="postbox-header">
            <h2>📁 Controllo File Excel</h2>
        </div>
        <div class="inside">
            <?php
            // ✅ CORRETTO: usa get_stylesheet_directory() per child theme
            $excel_file = get_stylesheet_directory() . '/import/DB_News_da importare.xlsx';
            if (file_exists($excel_file)) {
                $file_size = size_format(filesize($excel_file));
                $file_date = date('d/m/Y H:i', filemtime($excel_file));
                echo "<p><span class='dashicons dashicons-yes-alt' style='color: green;'></span> 
                      File trovato: <code>{$excel_file}</code></p>";
                echo "<p><strong>Dimensione:</strong> {$file_size} | <strong>Modificato:</strong> {$file_date}</p>";
                
                // Pulsante analisi
                if (!$stats) {
                    echo '<a href="?page=toro-news-import&analyze=1" class="button button-secondary">📊 Analizza Dati Excel</a>';
                }
            } else {
                echo "<p><span class='dashicons dashicons-warning' style='color: red;'></span> 
                      <strong>Errore:</strong> File Excel non trovato in <code>{$excel_file}</code></p>";
                echo "<p>Carica il file <code>DB_News_da importare.xlsx</code> nella cartella del tema.</p>";
            }
            ?>
        </div>
    </div>

    <!-- Statistiche dati -->
    <?php if ($stats && !is_wp_error($stats)): ?>
    <div class="postbox">
        <div class="postbox-header">
            <h2>📊 Statistiche Dati Excel</h2>
        </div>
        <div class="inside">
            <div class="import-stats">
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['news_ita']; ?></div>
                    <div class="stat-label">News Italiane</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['news_eng']; ?></div>
                    <div class="stat-label">News Inglesi</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['traduzioni']; ?></div>
                    <div class="stat-label">Traduzioni 1:1</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['immagini']; ?></div>
                    <div class="stat-label">Immagini</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['documenti']; ?></div>
                    <div class="stat-label">Documenti</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number"><?php echo $stats['fogli_totali']; ?></div>
                    <div class="stat-label">Fogli Excel</div>
                </div>
            </div>
            
            <!-- Categorie -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div>
                    <h4>📂 Categorie Italiane</h4>
                    <ul>
                        <?php foreach ($stats['categorie_ita'] as $cat => $count): ?>
                        <li><strong><?php echo esc_html($cat); ?></strong>: <?php echo $count; ?> news</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div>
                    <h4>📂 Categorie Inglesi</h4>
                    <ul>
                        <?php foreach ($stats['categorie_eng'] as $cat => $count): ?>
                        <li><strong><?php echo esc_html($cat); ?></strong>: <?php echo $count; ?> news</li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <!-- Range date -->
            <?php if ($stats['range_date']['min']): ?>
            <div style="margin-top: 20px;">
                <h4>📅 Range Temporale</h4>
                <p>Dal <strong><?php echo date('d/m/Y', strtotime($stats['range_date']['min'])); ?></strong> 
                   al <strong><?php echo date('d/m/Y', strtotime($stats['range_date']['max'])); ?></strong></p>
            </div>
            <?php endif; ?>
            
            <!-- Pulsante dry run -->
            <div style="margin-top: 20px;">
                <?php if (!$dry_run_report): ?>
                <a href="?page=toro-news-import&analyze=1&dry_run=1" class="button button-primary">🧪 Dry Run (Simulazione)</a>
                <p class="description">Simula l'importazione senza creare contenuti reali. Raccomandato prima dell'importazione vera!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Report Dry Run -->
    <?php if ($dry_run_report && !is_wp_error($dry_run_report)): ?>
    <div class="postbox">
        <div class="postbox-header">
            <h2>🧪 Report Dry Run</h2>
        </div>
        <div class="inside">
            <div class="import-stats">
                <div class="stat-box">
                    <div class="stat-number" style="color: #00a32a;"><?php echo count($dry_run_report['would_create']); ?></div>
                    <div class="stat-label">Verrebbero Create</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #dba617;"><?php echo count($dry_run_report['would_skip']); ?></div>
                    <div class="stat-label">Verrebbero Saltate</div>
                </div>
                <div class="stat-box">
                    <div class="stat-number" style="color: #d63638;"><?php echo count($dry_run_report['errors']); ?></div>
                    <div class="stat-label">Errori</div>
                </div>
            </div>
            
            <!-- Dettagli report -->
            <div style="margin-top: 20px;">
                <h4>📋 Dettagli Simulazione</h4>
                
                <!-- News che verrebbero create -->
                <?php if (!empty($dry_run_report['would_create'])): ?>
                <details style="margin: 10px 0;">
                    <summary><strong style="color: #00a32a;">✅ News che verrebbero create (<?php echo count($dry_run_report['would_create']); ?>)</strong></summary>
                    <div class="import-log" style="max-height: 200px;">
                        <?php foreach ($dry_run_report['would_create'] as $item): ?>
                        <div class="log-success"><?php echo esc_html($item); ?></div>
                        <?php endforeach; ?>
                    </div>
                </details>
                <?php endif; ?>
                
                <!-- News che verrebbero saltate -->
                <?php if (!empty($dry_run_report['would_skip'])): ?>
                <details style="margin: 10px 0;">
                    <summary><strong style="color: #dba617;">⚠️ News che verrebbero saltate (<?php echo count($dry_run_report['would_skip']); ?>)</strong></summary>
                    <div class="import-log" style="max-height: 200px;">
                        <?php foreach ($dry_run_report['would_skip'] as $item): ?>
                        <div class="log-warning"><?php echo esc_html($item); ?></div>
                        <?php endforeach; ?>
                    </div>
                </details>
                <?php endif; ?>
                
                <!-- Errori -->
                <?php if (!empty($dry_run_report['errors'])): ?>
                <details style="margin: 10px 0;">
                    <summary><strong style="color: #d63638;">❌ Errori rilevati (<?php echo count($dry_run_report['errors']); ?>)</strong></summary>
                    <div class="import-log" style="max-height: 200px;">
                        <?php foreach ($dry_run_report['errors'] as $error): ?>
                        <div class="log-error"><?php echo esc_html($error); ?></div>
                        <?php endforeach; ?>
                    </div>
                </details>
                <?php endif; ?>
            </div>
            
            <!-- Opzioni di importazione -->
            <?php if (count($dry_run_report['errors']) === 0): ?>
            <div style="margin-top: 30px; padding: 20px; background: #f0f6fc; border-left: 4px solid #2271b1;">
                <h4 style="margin-top: 0;">🚀 Opzioni di Importazione</h4>
                <p>Il dry run non ha rilevato errori. Configura le opzioni e procedi con l'importazione.</p>
                <p><strong>⚠️ ATTENZIONE:</strong> Questa operazione modificherà contenuti nel database. Assicurati di aver fatto il backup!</p>
                
                <!-- Opzioni -->
                <div style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #c3c4c7; border-radius: 4px;">
                    <h5 style="margin-top: 0;">⚙️ Opzioni di Importazione</h5>
                    
                    <p style="margin: 15px 0;">
                        <label>
                            <input type="checkbox" id="force-update" checked> 
                            <strong>Aggiorna news esistenti</strong>
                        </label>
                        <br><small style="color: #646970;">Applica i fix di pulizia contenuti (BBCode → HTML) alle news già importate</small>
                    </p>
                    
                    <p style="margin: 15px 0;">
                        <label>
                            <input type="checkbox" id="import-media"> 
                            <strong>Importa media (immagini e PDF)</strong>
                        </label>
                        <br><small style="color: #646970;">Scarica e importa immagini e documenti dalle URL originali (più lento)</small>
                    </p>
                    
                    <p style="margin: 15px 0;">
                        <label>
                            <input type="checkbox" id="connect-translations"> 
                            <strong>Collega traduzioni WPML</strong>
                        </label>
                        <br><small style="color: #646970;">Collega automaticamente le news ITA ↔ ENG usando la tabella traduzioni</small>
                    </p>
                    
                    <p style="margin: 15px 0;">
                        <label>
                            <input type="checkbox" id="dry-run-mode"> 
                            <strong>Solo simulazione (Dry Run)</strong>
                        </label>
                        <br><small style="color: #646970;">Simula l'importazione senza modificare il database (per test)</small>
                    </p>
                </div>
                
                <!-- Statistiche previste -->
                <div style="margin: 20px 0; padding: 15px; background: #f6f7f7; border-radius: 4px;">
                    <h5 style="margin-top: 0;">📊 Previsione Operazioni</h5>
                    <div id="import-preview">
                        <p>✅ <span id="preview-create"><?php echo count($dry_run_report['would_create']); ?></span> news verrebbero create</p>
                        <p>🔄 <span id="preview-update">0</span> news verrebbero aggiornate</p>
                        <p>⏭️ <span id="preview-skip"><?php echo count($dry_run_report['would_skip']); ?></span> news verrebbero saltate</p>
                    </div>
                </div>
                
                <!-- Pulsanti -->
                <div style="margin-top: 20px;">
                    <button id="start-real-import" class="button button-primary button-large" style="margin-right: 10px;">
                        🚀 Avvia Importazione
                    </button>
                    <button id="download-backup" class="button button-secondary" onclick="alert('Funzione backup non ancora implementata. Usa tools esterni per il backup del DB.')">
                        💾 Backup Database
                    </button>
                </div>
            </div>
            <?php else: ?>
            <div style="margin-top: 30px; padding: 20px; background: #fcf0f1; border-left: 4px solid #d63638;">
                <h4 style="margin-top: 0;">❌ Errori Rilevati</h4>
                <p>Il dry run ha rilevato degli errori. Risolvi i problemi prima di procedere con l'importazione.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Errori -->
    <?php if (isset($stats) && is_wp_error($stats)): ?>
    <div class="notice notice-error">
        <p><strong>Errore lettura Excel:</strong> <?php echo $stats->get_error_message(); ?></p>
    </div>
    <?php endif; ?>
    
    <?php if (isset($dry_run_report) && is_wp_error($dry_run_report)): ?>
    <div class="notice notice-error">
        <p><strong>Errore dry run:</strong> <?php echo $dry_run_report->get_error_message(); ?></p>
    </div>
    <?php endif; ?>

    <!-- Area importazione progressiva -->
    <div id="import-progress-container" style="display: none;">
        <div class="postbox">
            <div class="postbox-header">
                <h2>⚡ Importazione in Corso</h2>
            </div>
            <div class="inside">
                <div class="progress-bar">
                    <div class="progress-fill" id="progress-fill" style="width: 0%;"></div>
                </div>
                <p id="progress-text">Preparazione...</p>
                <p id="current-item"></p>
                
                <div class="import-log" id="import-log">
                    <div class="log-info">🚀 Avvio importazione...</div>
                </div>
                
                <button id="stop-import" class="button button-secondary" style="margin-top: 10px;" disabled>
                    ⏹️ Interrompi Importazione
                </button>
            </div>
        </div>
    </div>

    <!-- Script JavaScript per gestione AJAX -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const startButton = document.getElementById('start-real-import');
        const progressContainer = document.getElementById('import-progress-container');
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        const currentItem = document.getElementById('current-item');
        const importLog = document.getElementById('import-log');
        
        // Elementi opzioni
        const forceUpdateCheckbox = document.getElementById('force-update');
        const importMediaCheckbox = document.getElementById('import-media');
        const connectTranslationsCheckbox = document.getElementById('connect-translations');
        const dryRunCheckbox = document.getElementById('dry-run-mode');
        
        // Elementi preview
        const previewCreate = document.getElementById('preview-create');
        const previewUpdate = document.getElementById('preview-update');
        const previewSkip = document.getElementById('preview-skip');
        
        let importInProgress = false;
        
        // Aggiorna preview quando cambiano le opzioni
        if (forceUpdateCheckbox) {
            forceUpdateCheckbox.addEventListener('change', updatePreview);
            dryRunCheckbox.addEventListener('change', updatePreview);
        }
        
        function updatePreview() {
            const forceUpdate = forceUpdateCheckbox.checked;
            const dryRun = dryRunCheckbox.checked;
            
            if (forceUpdate && !dryRun) {
                // Se force update, le esistenti diventano "aggiornate"
                previewCreate.textContent = '<?php echo count($dry_run_report["would_create"]); ?>';
                previewUpdate.textContent = '<?php echo count($dry_run_report["would_skip"]); ?>';
                previewSkip.textContent = '0';
            } else {
                // Modalità normale
                previewCreate.textContent = '<?php echo count($dry_run_report["would_create"]); ?>';
                previewUpdate.textContent = '0';
                previewSkip.textContent = '<?php echo count($dry_run_report["would_skip"]); ?>';
            }
            
            // Aggiorna testo pulsante
            if (dryRun) {
                startButton.textContent = '🧪 Esegui Simulazione';
                startButton.className = 'button button-secondary button-large';
            } else {
                startButton.textContent = '🚀 Avvia Importazione';
                startButton.className = 'button button-primary button-large';
            }
        }
        
        if (startButton) {
            startButton.addEventListener('click', function() {
                if (importInProgress) return;
                
                const isDryRun = dryRunCheckbox.checked;
                const confirmMessage = isDryRun 
                    ? 'Eseguire la simulazione di importazione?'
                    : 'Sei sicuro di voler avviare l\'importazione reale? Questa operazione modificherà il database.';
                
                if (!confirm(confirmMessage)) {
                    return;
                }
                
                startImport();
            });
        }
        
        function startImport() {
            importInProgress = true;
            startButton.disabled = true;
            
            const isDryRun = dryRunCheckbox.checked;
            startButton.textContent = isDryRun ? '⏳ Simulazione in corso...' : '⏳ Importazione in corso...';
            progressContainer.style.display = 'block';
            
            // Scorri fino alla sezione progress
            progressContainer.scrollIntoView({ behavior: 'smooth' });
            
            // Avvia importazione AJAX
            importNewsAjax();
        }
        
        function importNewsAjax() {
            const formData = new FormData();
            formData.append('action', 'toro_import_news');
            formData.append('security', '<?php echo wp_create_nonce("toro_import_news"); ?>');
            
            // Aggiungi opzioni
            formData.append('force_update', forceUpdateCheckbox.checked ? '1' : '0');
            formData.append('import_media', importMediaCheckbox.checked ? '1' : '0');
            formData.append('connect_translations', connectTranslationsCheckbox.checked ? '1' : '0');
            formData.append('dry_run_mode', dryRunCheckbox.checked ? '1' : '0');
            
            fetch('<?php echo admin_url("admin-ajax.php"); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    handleImportSuccess(data.data);
                } else {
                    handleImportError(data.data);
                }
            })
            .catch(error => {
                handleImportError('Errore di rete: ' + error.message);
            });
        }
        
        function handleImportSuccess(data) {
            progressFill.style.width = '100%';
            
            const isDryRun = dryRunCheckbox.checked;
            progressText.textContent = isDryRun ? 'Simulazione completata!' : 'Importazione completata!';
            currentItem.textContent = '';
            
            // Aggiungi risultati al log
            addToLog(isDryRun ? '✅ Simulazione completata!' : '✅ Importazione completata!', 'success');
            
            if (data.created && data.created.length > 0) {
                addToLog(`📝 Create ${data.created.length} news:`, 'info');
                data.created.forEach(item => addToLog(item, 'success'));
            }
            
            if (data.updated && data.updated.length > 0) {
                addToLog(`🔄 Aggiornate ${data.updated.length} news:`, 'info');
                data.updated.forEach(item => addToLog(item, 'success'));
            }
            
            if (data.translations_connected && data.translations_connected.length > 0) {
                addToLog(`🔗 Traduzioni collegate ${data.translations_connected.length}:`, 'info');
                data.translations_connected.forEach(item => addToLog(item, 'success'));
            }
            
            if (data.translations_errors && data.translations_errors.length > 0) {
                addToLog(`⚠️ Errori traduzioni (${data.translations_errors.length}):`, 'info');
                data.translations_errors.forEach(item => addToLog(item, 'warning'));
            }
            
            if (data.media_imported && data.media_imported.length > 0) {
                addToLog(`📸 Media importati (${data.media_imported.length}):`, 'info');
                data.media_imported.forEach(item => addToLog(item, 'success'));
            }
            
            if (data.media_errors && data.media_errors.length > 0) {
                addToLog(`⚠️ Errori media (${data.media_errors.length}):`, 'info');
                data.media_errors.forEach(item => addToLog(item, 'warning'));
            }
            
            if (data.skipped && data.skipped.length > 0) {
                addToLog(`⚠️ Saltate ${data.skipped.length} news:`, 'info');
                data.skipped.forEach(item => addToLog(item, 'warning'));
            }
            
            if (data.errors && data.errors.length > 0) {
                addToLog(`❌ Errori (${data.errors.length}):`, 'info');
                data.errors.forEach(item => addToLog(item, 'error'));
            }
            
            // Reset
            importInProgress = false;
            startButton.disabled = false;
            startButton.textContent = isDryRun ? '✅ Simulazione Completata' : '✅ Importazione Completata';
            startButton.style.backgroundColor = '#00a32a';
        }
        
        function handleImportError(error) {
            addToLog('❌ Errore durante importazione: ' + error, 'error');
            
            importInProgress = false;
            startButton.disabled = false;
            startButton.textContent = '❌ Operazione Fallita';
            startButton.style.backgroundColor = '#d63638';
            
            progressText.textContent = 'Operazione fallita!';
        }
        
        function addToLog(message, type = 'info') {
            const logDiv = document.createElement('div');
            logDiv.className = 'log-' + type;
            logDiv.textContent = message;
            importLog.appendChild(logDiv);
            
            // Scroll to bottom
            importLog.scrollTop = importLog.scrollHeight;
        }
        
        // Inizializza preview
        updatePreview();
    });
    </script>
</div>

<?php
// Note per lo sviluppatore
if (current_user_can('manage_options') && isset($_GET['debug'])) {
    echo '<div class="notice notice-info">';
    echo '<h3>🔧 Debug Info</h3>';
    echo '<p><strong>File Excel:</strong> ' . get_stylesheet_directory() . '/import/DB_News_da importare.xlsx</p>';
    echo '<p><strong>Funzioni caricate:</strong> ' . (function_exists('toro_read_excel_data') ? '✅' : '❌') . '</p>';
    echo '<p><strong>WPML attivo:</strong> ' . (function_exists('icl_object_id') ? '✅' : '❌') . '</p>';
    echo '<p><strong>PODS attivo:</strong> ' . (function_exists('pods') ? '✅' : '❌') . '</p>';
    echo '</div>';
}
?>