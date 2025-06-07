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
            'arabo'     => 'ar',
            'polacco'   => 'pl',
            'svedese'   => 'sv',
            'afrikaans' => 'af',// aggiungi qui altre mappature se serve
        ];

        if ( empty( $map[ $lang_slug ] ) ) {
            echo '<!-- FLAG DEBUG: slug “' . esc_html( $lang_slug ) . '” non mappato -->';
            return '';
        }

        $code = $map[ $lang_slug ];
        // SitePress::get_flag_url restituisce sempre l'URL corretto
        $url = $sitepress->get_flag_url( $code );
        echo '<!-- FLAG DEBUG: slug=' . esc_html( $lang_slug )
             . ' code=' . esc_html( $code )
             . ' URL WPML=' . esc_url( $url ) . ' -->';
        if ( ! $url ) {
            // Se WPML non restituisce l’URL, usiamo il path diretto al plugin
            $fallback = plugins_url( 'sitepress-multilingual-cms/res/flags/' . $code . '.svg' );
            echo '<!-- FLAG DEBUG: fallback URL=' . esc_url( $fallback ) . ' -->';
            $url = $fallback;
        }

        return '<img src="' . esc_url( $url ) . '" ' .
               'alt="' . esc_attr( ucfirst( $lang_slug ) ) . '" ' .
               'class="lang-flag" />';
    }
}

if ( ! function_exists( 'toroag_get_language_label' ) ) {
    /**
     * Restituisce il nome leggibile della lingua in base allo slug.
     *
     * @param string $lang_slug  Slug della lingua (italiano, inglese, ecc.)
     * @return string            Nome della lingua localizzato
     */
    function toroag_get_language_label( $lang_slug ) {
        $labels = [
            'italiano'   => __( 'Italian', 'toro-ag' ),
            'inglese'    => __( 'English', 'toro-ag' ),
            'spagnolo'   => __( 'Spanish', 'toro-ag' ),
            'francese'   => __( 'French',  'toro-ag' ),
            'tedesco'    => __( 'German',  'toro-ag' ),
            'portoghese' => __( 'Portuguese', 'toro-ag' ),
            'arabo'      => __( 'Arabic',   'toro-ag' ),
            'polacco'    => __( 'Polish',   'toro-ag' ),
            'svedese'    => __( 'Swedish',  'toro-ag' ),
            'afrikaans'  => __( 'Afrikaans','toro-ag' ),
        ];

        return isset( $labels[ $lang_slug ] )
            ? $labels[ $lang_slug ]
            : ucfirst( $lang_slug );
    }
}
