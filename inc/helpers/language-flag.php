<?php
/**
 * @package ToroAG
 * @subpackage Helpers.AdditionalLanguages
 */

if ( ! function_exists( 'toroag_get_flag_html' ) ) {
    /**
     * Restituisce l'HTML <img> della bandiera WPML per uno slug di lingua.
     *
     * @param string $lang_slug  Slug usato nella tassonomia (italiano, inglese, francese, spagnolo, ecc.)
     * @return string            HTML <img> o vuoto se non disponibile
     */
    function toroag_get_flag_html( $lang_slug ) {
        global $sitepress;

        if ( empty( $sitepress ) ) {
            return '';
        }

        // Mappa slug tassonomia → codice lingua WPML
        $map = [
            'italiano'  => 'it',
            'inglese'   => 'en',
            'francese'  => 'fr',
            'spagnolo'  => 'es',
            'tedesco'   => 'de',
            'portoghese'=> 'pt',
            'arabo'    => 'ar',
            // aggiungi qui altre mappature se serve
        ];

        if ( empty( $map[ $lang_slug ] ) ) {
            return '';
        }

        $code = $map[ $lang_slug ];
        // SitePress::get_flag_url restituisce sempre l'URL corretto
        $url = $sitepress->get_flag_url( $code );
        if ( ! $url ) {
            return '';
        }

        return '<img src="' . esc_url( $url ) . '" ' .
               'alt="' . esc_attr( ucfirst( $lang_slug ) ) . '" ' .
               'class="lang-flag" />';
    }
}
