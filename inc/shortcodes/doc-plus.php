<?php
/**
 * Shortcode [doc_plus] – debug HTML comments (immediate echo)
 * Emette commenti HTML direttamente, per vedere subito cosa succede.
 */
function doc_plus_debug_shortcode() {
    // COMMENTO DI CONTROLLO: sempre emesso
    echo "\n<!-- doc_plus_debug: shortcode eseguito lingua=" 
       . ( defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'n.d.' )
       . " -->\n";

    $page_id  = get_the_ID();
    $page_pod = pods( 'page', $page_id );
    if ( ! $page_pod->exists() ) {
        echo "<!-- doc_plus_debug: pagina non trovata (ID={$page_id}) -->\n";
        return;
    }

    // 1) Leggo la relazione nella lingua corrente
    $related_docs = $page_pod->field( 'doc_plus_inpage' );

    // === INIZIO FALLOBACK WPML ===
    if ( empty( $related_docs ) && defined( 'ICL_LANGUAGE_CODE' ) ) {
        $default_lang = apply_filters( 'wpml_default_language', null );
        if ( ICL_LANGUAGE_CODE !== $default_lang ) {
            $orig_id      = apply_filters( 'wpml_object_id', $page_id, 'page', false, $default_lang );
            $orig_pod     = pods( 'page', $orig_id );
            $related_docs = $orig_pod->field( 'doc_plus_inpage' );
            echo "<!-- doc_plus_debug: fallback a lingua {$default_lang} page_id={$orig_id}, trovati "
               . count( (array)$related_docs ) . " doc_plus -->\n";
        }
    }
    // === FINE FALLOBACK ===

    if ( empty( $related_docs ) ) {
        echo "<!-- doc_plus_debug: nessun doc_plus collegato a pagina {$page_id} -->\n";
        return;
    }

    echo "<!-- doc_plus_debug: trovati " . count( $related_docs )
       . " doc_plus per pagina {$page_id} -->\n";

    foreach ( $related_docs as $item ) {
        $doc_id = (int) $item['ID'];
        $pod    = pods( 'doc_plus', $doc_id );

        // Titolo e lingua
        $title = get_the_title( $doc_id );
        $lang  = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'n.d.';

        // Cover
        $cover_id  = $pod->field( 'doc_plus_cover.ID' );
        $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';

        echo "<!-- doc_plus_debug: DOC ID={$doc_id} lingua={$lang} titolo=\"{$title}\" "
           . "cover_id={$cover_id} cover_url=\"{$cover_url}\" -->\n";

        // Allegati
        $allegati = $pod->field( 'doc_plus_allegati' );
        if ( empty( $allegati ) ) {
            echo "<!-- doc_plus_debug: nessun allegato per doc_plus {$doc_id} -->\n";
            continue;
        }

        foreach ( $allegati as $att ) {
            $pdf_id  = (int) $att['ID'];
            $pod_pdf = pods_
