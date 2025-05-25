<?php
/**
 * Shortcode: [scheda_prodotto]
 * Visualizza i link alle Schede Prodotto collegate al prodotto, con debug e bandierina lingua.
 * Riprende la logica di [video_prodotto_v2] per caricare tutti i CPT associati.
 */
function shortcode_scheda_prodotto( $atts ) {
    global $post;

    // Debug: inizio shortcode scheda_prodotto
    $output = "<!-- Debug [scheda_prodotto]: product ID {$post->ID} -->";

    // Ottieni Pods del CPT Scheda Prodotto con relazione al prodotto
    $params = [
        'where'    => "t.slug.meta_value = {$post->ID}", // adattare in base alla relazione
        'limit'    => -1,
    ];
    $pods_schede = pods( 'scheda_prodotto', $params );

    $count = $pods_schede->total();
    $output .= "<!-- Debug [scheda_prodotto]: trovato count = {$count} -->";

    if ( 0 === $count ) {
        return $output . '<p>Nessuna scheda trovata.</p>';
    }

    while ( $pods_schede->fetch() ) {
        $scheda_id   = $pods_schede->field( 'ID' );
        $title       = $pods_schede->display( 'post_title' );
        $permalink   = $pods_schede->field( 'permalink' );

        // Debug per ogni scheda
        $output .= "<!-- Debug scheda_prodotto: ID={$scheda_id}, title={$title} -->";

        // Lingua aggiuntiva
        $terms = get_the_terms( $scheda_id, 'lingue_aggiuntive' );
        $flag_html = '';
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $lang = reset( $terms );
            if ( function_exists( 'toroag_get_flag_html' ) ) {
                $flag_html = toroag_get_flag_html( $lang->slug );
            }
            $output .= "<!-- Debug lingua={$lang->slug} -->";
        } else {
            $output .= "<!-- Debug lingua assente -->";
        }

        // Costruisci link (rimuovendo badge)
        $output .= '<a href="' . esc_url( $permalink ) . '">'
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
 * Riprende la logica di [video_prodotto_v2] per caricare tutti i CPT associati.
 */
function shortcode_documento_prodotto( $atts ) {
    global $post;

    // Debug: inizio shortcode documento_prodotto
    $output = "<!-- Debug [documento_prodotto]: product ID {$post->ID} -->";

    // Ottieni Pods del CPT Documenti Prodotto con relazione al prodotto
    $params = [
        'where'    => "t.slug.meta_value = {$post->ID}", // adattare in base alla relazione
        'limit'    => -1,
    ];
    $pods_docs = pods( 'documenti_prodotto', $params );

    $count = $pods_docs->total();
    $output .= "<!-- Debug [documento_prodotto]: trovato count = {$count} -->";

    if ( 0 === $count ) {
        return $output . '<p>Nessun documento trovato.</p>';
    }

    while ( $pods_docs->fetch() ) {
        $doc_id     = $pods_docs->field( 'ID' );
        $title      = $pods_docs->display( 'post_title' );
        $permalink  = $pods_docs->field( 'permalink' );

        // Debug per ogni documento
        $output .= "<!-- Debug documento_prodotto: ID={$doc_id}, title={$title} -->";

        // Lingua aggiuntiva
        $terms = get_the_terms( $doc_id, 'lingue_aggiuntive' );
        $flag_html = '';
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            $lang = reset( $terms );
            if ( function_exists( 'toroag_get_flag_html' ) ) {
                $flag_html = toroag_get_flag_html( $lang->slug );
            }
            $output .= "<!-- Debug lingua={$lang->slug} -->";
        } else {
            $output .= "<!-- Debug lingua assente -->";
        }

        // Costruisci link (rimuovendo badge)
        $output .= '<a href="' . esc_url( $permalink ) . '">'
                 . esc_html( $title )
                 . $flag_html
                 . '</a><br />';
    }

    return $output;
}
add_shortcode( 'documento_prodotto', 'shortcode_documento_prodotto' );
