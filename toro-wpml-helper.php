<?php
/**
 * TORO AG - Helper WPML String Registration
 * 
 * File helper per registrare manualmente le stringhe WPML
 * Da eseguire UNA VOLTA per registrare le stringhe nel database
 */

// Sicurezza
if (!defined('ABSPATH') || !current_user_can('manage_options')) {
    exit('Accesso negato');
}

/**
 * Forza registrazione di tutte le stringhe TORO Layout Manager
 */
function toro_force_register_all_wpml_strings() {
    if (!function_exists('wpml_register_single_string')) {
        return ['error' => 'WPML non attivo o funzioni String Translation non disponibili'];
    }
    
    $strings_to_register = [
        [
            'context' => 'Toro Layout Manager',
            'name' => 'Chiedi informazioni sul prodotto',
            'value' => 'Chiedi informazioni sul prodotto'
        ],
        // Aggiungi qui altre stringhe future
        [
            'context' => 'Toro Layout Manager', 
            'name' => 'Contattaci per maggiori informazioni',
            'value' => 'Contattaci per maggiori informazioni'
        ]
    ];
    
    $results = [];
    foreach ($strings_to_register as $string) {
        wpml_register_single_string($string['context'], $string['name'], $string['value']);
        $results[] = "✅ Registrata: '{$string['name']}' in contesto '{$string['context']}'";
    }
    
    // Forza refresh cache WPML
    if (function_exists('wpml_st_load_translations_from_mo')) {
        wpml_st_load_translations_from_mo();
    }
    
    return ['success' => $results];
}

// Se richiamato direttamente via URL
if (isset($_GET['action']) && $_GET['action'] === 'register_toro_strings') {
    $result = toro_force_register_all_wpml_strings();
    
    echo '<h2>🌍 TORO WPML String Registration</h2>';
    
    if (isset($result['error'])) {
        echo '<div style="background:#ffebee;padding:15px;border:1px solid #f44336;color:#c62828;">';
        echo '<strong>❌ Errore:</strong> ' . $result['error'];
        echo '</div>';
    } else {
        echo '<div style="background:#e8f5e8;padding:15px;border:1px solid #4caf50;color:#2e7d32;">';
        echo '<strong>🎉 Successo!</strong><br>';
        foreach ($result['success'] as $message) {
            echo $message . '<br>';
        }
        echo '</div>';
        
        echo '<h3>📝 Prossimi Step:</h3>';
        echo '<ol>';
        echo '<li>Vai su <strong>WPML > String Translation</strong></li>';
        echo '<li>Cerca: "<strong>Chiedi informazioni sul prodotto</strong>"</li>';
        echo '<li>Filtra per dominio: "<strong>Toro Layout Manager</strong>"</li>';
        echo '<li>Clicca sull\'icona della traduzione per aggiungere le traduzioni</li>';
        echo '</ol>';
    }
    
    echo '<p><a href="' . admin_url() . '">← Torna alla dashboard</a></p>';
    exit;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>TORO WPML String Registration</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .button { background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <h1>🌍 TORO Layout Manager - Registrazione Stringhe WPML</h1>
    
    <div class="warning">
        <strong>⚠️ Attenzione:</strong> Questo script registra manualmente le stringhe WPML per il TORO Layout Manager. 
        Esegui solo se le stringhe non appaiono automaticamente in WPML > String Translation.
    </div>
    
    <h2>📋 Stringhe da registrare:</h2>
    <ul>
        <li><strong>"Chiedi informazioni sul prodotto"</strong> - Testo pulsante CTA sidebar</li>
        <li><strong>"Contattaci per maggiori informazioni"</strong> - Futuro testo alternativo</li>
    </ul>
    
    <h2>🚀 Azione:</h2>
    <a href="?action=register_toro_strings" class="button">
        Registra Tutte le Stringhe WPML
    </a>
    
    <h2>🔧 Metodi Alternativi:</h2>
    <ol>
        <li><strong>URL Automatico:</strong> Visita una pagina prodotto che usa <code>[toro_layout_prodotto]</code></li>
        <li><strong>URL Debug:</strong> Aggiungi <code>?toro_force_wpml_sync=1</code> all'URL dell'admin</li>
        <li><strong>Registrazione Manuale:</strong> Vai su WPML > String Translation > "Add strings manually"</li>
    </ol>
    
    <h3>📝 Dati per registrazione manuale:</h3>
    <table border="1" style="border-collapse: collapse; width: 100%;">
        <tr>
            <th style="padding: 10px;">Dominio/Context</th>
            <th style="padding: 10px;">Nome Stringa</th>
            <th style="padding: 10px;">Valore</th>
        </tr>
        <tr>
            <td style="padding: 10px;">Toro Layout Manager</td>
            <td style="padding: 10px;">Chiedi informazioni sul prodotto</td>
            <td style="padding: 10px;">Chiedi informazioni sul prodotto</td>
        </tr>
    </table>
    
    <p><a href="<?php echo admin_url(); ?>">← Torna alla Dashboard WordPress</a></p>
</body>
</html>
