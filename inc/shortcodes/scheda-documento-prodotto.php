<?php
/**
 * Shortcode: [scheda_prodotto]
 * Visualizza i link alle Schede Prodotto collegate al prodotto, con debug e bandierina lingua.
 * Usa la relazione Pods definita su scheda_prodotto nel CPT prodotto.
 */
function shortcode_scheda_prodotto( $atts ) {
    global $post;

    // Debug: inizio shortcode scheda_prodotto
    $output = "<!-- Debug [scheda_prodotto]: product ID {$post->ID} -->";

    // Recupera relazione dal pod del prodotto
    $pods_prod = pods( get_post_type( $post ), $post->ID );
    $schede = $pods_prod->field( 'scheda_prodotto' );

    $count = is_array( $schede ) ? count( $schede ) : 0;
    $output .= "<!-- Debug [scheda_prodotto]: trovato count = {$count} -->";

    if ( $count === 0 ) {
        return $output . '<p>Nessuna scheda trovata.</p>';
    }

    foreach ( $schede as $item ) {
        $scheda_id = intval( $item['ID'] );
        $scheda_post = get_post( $scheda_id );
        
        // Debug per ogni scheda
        $output .= "<!-- Debug scheda_prodotto: ID={$scheda_id} -->";

        $url   = get_permalink( $scheda_post );
        $title = get_the_title( $scheda_post );

        // Lingua aggiuntiva
        $terms = get_the_terms( $scheda_id, 'lingue_aggiuntive' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $lang = reset( $terms );
            $output .= "<!-- Debug lingua={$lang->slug} -->";
            $flag_html = function_exists( 'toroag_get_flag_html' ) ? toroag_get_flag_html( $lang->slug ) : '';
        } else {
            $output .= "<!-- Debug lingua assente -->";
            $flag_html = '';
        }

        $output .= '<a href="' . esc_url( $url ) . '">'
                 . esc_html( $title )
                 . $flag_html
                 . '</a><br />';
    }

    return $output;
}
add_shortcode( 'scheda_prodotto', 'shortcode_scheda_prodotto' );


/**
 * Shortcode: [documento_prodotto]
 * Visualizza i link ai Documenti Prodotto collegati al prodotto, con debug e bandierina lingua.
 * Usa la relazione Pods definita su documenti_prodotto nel CPT prodotto.
 */
function shortcode_documento_prodotto( $atts ) {
    global $post;

    // Debug: inizio shortcode documento_prodotto
    $output = "<!-- Debug [documento_prodotto]: product ID {$post->ID} -->";

    $pods_prod = pods( get_post_type( $post ), $post->ID );
    $docs = $pods_prod->field( 'documenti_prodotto' );

    $count = is_array( $docs ) ? count( $docs ) : 0;
    $output .= "<!-- Debug [documento_prodotto]: trovato count = {$count} -->";

    if ( $count === 0 ) {
        return $output . '<p>Nessun documento trovato.</p>';
    }

    foreach ( $docs as $item ) {
        $doc_id = intval( $item['ID'] );
        
        // Debug per ogni documento
        $output .= "<!-- Debug documento_prodotto: ID={$doc_id} -->";

        $doc_post = get_post( $doc_id );
        $url   = get_permalink( $doc_post );
        $title = get_the_title( $doc_post );

        // Lingua aggiuntiva
        $terms = get_the_terms( $doc_id, 'lingue_aggiuntive' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $lang = reset( $terms );
            $output .= "<!-- Debug lingua={$lang->slug} -->";
            $flag_html = function_exists( 'toroag_get_flag_html' ) ? toroag_get_flag_html( $lang->slug ) : '';
        } else {
            $output .= "<!-- Debug lingua assente -->";
            $flag_html = '';
        }

        $output .= '<a href="' . esc_url( $url ) . '">'
                 . esc_html( $title )
                 . $flag_html
                 . '</a><br />';
    }

    return $output;
}
add_shortcode( 'documento_prodotto', 'shortcode_documento_prodotto' );
