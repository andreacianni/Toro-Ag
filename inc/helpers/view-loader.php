<?php
/**
 * @package ToroAG
 * @subpackage Helpers
 */

if ( ! function_exists( 'toroag_load_view' ) ) {
    /**
     * Carica una view passando i dati.
     *
     * @param string $view Nome della view (es. 'documenti-download')
     * @param array  $data Array di variabili da estrarre nella view
     * @return string HTML renderizzato
     */
    function toroag_load_view( $view, $data = [] ) {
        $view_file = get_stylesheet_directory() . '/inc/views/' . $view . '.php';

        if ( ! file_exists( $view_file ) ) {
            return '<p>View "' . esc_html( $view ) . '" non trovata.</p>';
        }

        // Debug: log dei dati forniti al loader
        echo '<!-- Debug loader BEFORE extract: ' . esc_html( wp_json_encode( $data ) ) . ' -->';

        // Estraiamo le variabili dal data array
        extract( $data, EXTR_SKIP );

        // Debug: log delle variabili estratte (layout)
        $layout_debug = isset( $layout ) ? $layout : 'undefined';
        echo '<!-- Debug loader AFTER extract: layout = ' . esc_html( $layout_debug ) . ' -->';

        ob_start();
        include $view_file;  // all'interno di questo file, dovremmo avere accesso a $layout e $doc_plus_data
        return ob_get_clean();
    }
}
