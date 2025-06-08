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

        extract( $data, EXTR_SKIP );
        ob_start();
        include $view_file;
        var_dump($data);
        return ob_get_clean();
    }
}
