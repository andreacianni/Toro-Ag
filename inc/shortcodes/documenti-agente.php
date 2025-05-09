<?php
/**
 * Shortcode: documenti_agente
 * Versione: toro-ag-template V0.9.5
 */

add_shortcode('documenti_agente', 'shortcode_documenti_agente');

function shortcode_documenti_agente($atts) {
    if (!is_user_logged_in()) return 'Devi accedere per vedere i documenti.';

    $user_id = get_current_user_id();
    $agente_attivo = get_user_meta($user_id, 'agente_attivo', true);

    if (!$agente_attivo) return 'Non sei autorizzato a visualizzare i documenti.';

    $categorie = get_terms([
        'taxonomy' => 'categoria_documento',
        'hide_empty' => false,
    ]);

    ob_start();

    $args = shortcode_atts([
        'layout' => 'lista' // alternativa: 'gallery'
    ], $atts);

    $template = $args['layout'] === 'gallery' ? 'documenti-gallery.php' : 'documenti-lista.php';

    $template_path = get_stylesheet_directory() . '/inc/views/' . $template;

    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo '<p>Template "' . esc_html($template) . '" non trovato.</p>';
    }

    return ob_get_clean();
}