<?php
/**
 * Shortcode per mostrare la scheda prodotto associata al prodotto (con fallback WPML robusto).
 * Uso: [scheda_prodotto]
 */
if ( ! function_exists('ta_render_scheda_prodotto_shortcode') ) {
    function ta_render_scheda_prodotto_shortcode($atts) {
        global $post;

        if ( ! function_exists('pods') ) {
            return '<!-- shortcode scheda_prodotto --><!-- DEBUG: Pods non disponibile -->';
        }

        // Lingua corrente e lingua di default
        $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
        $default_lang = apply_filters('wpml_default_language', null);
        $output = '<!-- shortcode scheda_prodotto --><!-- DEBUG: Prodotto ID lingua ' . esc_html($current_lang) . ' -->';

        // Recupera l'ID del prodotto nella lingua corrente (fallback al post corrente)
        $prod_id_current = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $current_lang);
        $prod_id_current = $prod_id_current ? intval($prod_id_current) : intval($post->ID);

        // Carica il pod prodotto
        $pod = pods('prodotto', $prod_id_current, ['lang' => $current_lang]);
        if ( ! $pod->exists() ) {
            return '<!-- shortcode scheda_prodotto --><!-- DEBUG: Pod prodotto non trovato per ID ' . esc_html($prod_id_current) . ' -->';
        }

        // Recupera il campo scheda_prodotto
        $schede = $pod->field('scheda_prodotto');
        if ( empty($schede) ) {
            // Fallback alla lingua di default
            $prod_id_default = apply_filters('wpml_object_id', $post->ID, 'prodotto', true, $default_lang);
            $prod_id_default = $prod_id_default ? intval($prod_id_default) : intval($post->ID);
            $schede = array_map('intval', get_post_meta($prod_id_default, 'scheda_prodotto', false));
            if ( empty($schede) ) {
                return '<!-- shortcode scheda_prodotto --><!-- DEBUG: Nessuna scheda prodotto disponibile -->';
            }
        }

        // Output lista schede prodotto
        $output .= '<ul class="scheda-prod-list">';
        foreach ( (array) $schede as $item ) {
            // Determina ID allegato
            $file_id = is_array($item) && isset($item['ID']) ? intval($item['ID']) : (is_object($item) && isset($item->ID) ? intval($item->ID) : intval($item));
            if ( ! $file_id ) continue;

            // URL del file (pod o attachment)
            $file_url = pods_v( 'guid', $item ) ?: wp_get_attachment_url($file_id);
            // Link sicuro se disponibile
            $download_url = function_exists('toroag_get_secure_download_url') ? toroag_get_secure_download_url($file_id) : $file_url;

            // Titolo del file
            $file_title = get_the_title($file_id) ?: basename(get_attached_file($file_id));

            $output .= '<li><a href="' . esc_url($download_url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($file_title) . '</a></li>';
        }
        $output .= '</ul>';

        return $output;
    }
    add_shortcode('scheda_prodotto', 'ta_render_scheda_prodotto_shortcode');
}
