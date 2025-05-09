<?php
/**
 * @package ToroAG
 * @subpackage Helpers
 */

/**
 * Restituisce la classe icona in base all'estensione del file.
 *
 * @param string $url URL del file
 * @return string Classe Bootstrap Icon da usare
 */
if ( ! function_exists( 'toroag_get_icon_class' ) ) {
    function toroag_get_icon_class( $url ) {
        $path = parse_url( $url, PHP_URL_PATH );
        $ext  = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

        switch ( $ext ) {
            case 'pdf':
                return 'bi-file-earmark-pdf';
            case 'xls':
            case 'xlsx':
            case 'csv':
            case 'ods':
                return 'bi-file-earmark-spreadsheet';
            case 'doc':
            case 'docx':
            case 'odt':
                return 'bi-file-earmark-word';
            default:
                return 'bi-file-earmark-text';
        }
    }
}
