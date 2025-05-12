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
        echo "<!-- toroag_get_flag_html: ricevuto slug = {$lang_slug} -->\n";

        global $sitepress;

        if ( empty( $sitepress ) ) {
            echo "<!-- toroag_get_flag_html: sitepress non disponibile -->\n";
            return '';
        }

        $map = [
            'italiano'  => 'it',
            'inglese'   => 'en',
            'francese'  => 'fr',
            'spagnolo'  => 'es',
            'tedesco'   => 'de',
            'portoghese'=> 'pt',
            'arabo'     => 'ar',
        ];

        if ( empty( $map[ $lang_slug ] ) ) {
            echo "<!-- toroag_get_flag_html: mappatura non trovata per '{$lang_slug}' -->\n";
            return '';
        }

        $lang_code = $map[ $lang_slug ];
        $flag_url = $sitepress->get_flag_url( $lang_code );

        echo "<!-- toroag_get_flag_html: WPML code = {$lang_code}, URL = {$flag_url} -->\n";

        if ( empty( $flag_url ) ) {
            return '';
        }

        return '<img src="' . esc_url( $flag_url ) . '" alt="' . esc_attr( $lang_slug ) . '" class="wpml-flag" width="18" height="12">';
    }
}

if ( ! function_exists( 'toroag_filtra_per_lingua_aggiuntiva' ) ) {
    /**
     * Filtra un array di ID post in base alla lingua WPML e alla tassonomia lingua_aggiuntiva.
     * Funziona per tutti i post_type che usano questa tassonomia.
     *
     * @param array $post_ids Array di ID post da filtrare.
     * @return array Array filtrato in base alla lingua corrente.
     */
    function toroag_filtra_per_lingua_aggiuntiva( $post_ids ) {
        $current_lang = function_exists('icl_object_id')
            ? apply_filters('wpml_current_language', null)
            : 'it';

        echo "<!-- toroag_filtra_per_lingua_aggiuntiva: lingua corrente = {$current_lang} / post_ids in ingresso: " . implode(', ', $post_ids) . " -->\n";

        $filtered = [];
        foreach ( $post_ids as $id ) {
            $terms = wp_get_post_terms( $id, 'lingua_aggiuntiva', [ 'fields' => 'slugs' ] );
            echo "<!-- post ID {$id} → lingua_aggiuntiva = " . (is_wp_error($terms) ? 'ERRORE' : implode(', ', $terms)) . " -->\n";

            if ( is_wp_error( $terms ) || empty( $terms ) ) {
                continue;
            }

            $term = $terms[0];
            if ( ( $current_lang === 'it' && $term === 'italiano' ) ||
                 ( $current_lang !== 'it' && $term !== 'italiano' ) ) {
                $filtered[] = $id;
            }
        }

        echo "<!-- toroag_filtra_per_lingua_aggiuntiva: output filtrato = " . implode(', ', $filtered) . " -->\n";

        return $filtered;
    }
}
