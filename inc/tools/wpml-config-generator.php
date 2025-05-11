<?php
/**
 * Script per generare automaticamente il file wpml-config.xml
 * basato sulle impostazioni attuali di WPML in WordPress.
 *
 * Percorso consigliato: inc/tools/wpml-config-generator.php
 * Output: docs/wpml/wpml-config.xml
 */

function toroag_generate_wpml_config() {
    if ( ! function_exists('get_option') ) {
        require_once ABSPATH . 'wp-load.php';
    }

    $settings = get_option('icl_sitepress_settings');
    if ( ! isset($settings['translation-management']['custom_fields_translation']) ) {
        echo "⚠️  Nessuna configurazione trovata.\n";
        return;
    }

    $fields = $settings['translation-management']['custom_fields_translation'];

    $xml = new SimpleXMLElement('<wpml-config/>');
    $customFieldsNode = $xml->addChild('custom-fields');

    foreach ($fields as $field => $action) {
        switch ($action) {
            case 1:
            case '1':
                $customFieldsNode->addChild('custom-field', $field)->addAttribute('action', 'translate');
                break;
            case 2:
            case '2':
                $customFieldsNode->addChild('custom-field', $field)->addAttribute('action', 'copy');
                break;
            case 3:
            case '3':
                $customFieldsNode->addChild('custom-field', $field)->addAttribute('action', 'copy-once');
                break;
            default:
                $customFieldsNode->addChild('custom-field', $field)->addAttribute('action', 'ignore');
                break;
        }
    }

    $outputDir = get_template_directory() . '/docs/wpml';
    if ( ! file_exists($outputDir) ) {
        mkdir($outputDir, 0755, true);
    }

    $outputPath = $outputDir . '/wpml-config.xml';
    $xml->asXML($outputPath);
    echo "✅ wpml-config.xml generato in: $outputPath\n";
}

// Esecuzione diretta se richiamato standalone
if ( php_sapi_name() === 'cli' || basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME']) ) {
    toroag_generate_wpml_config();
}
