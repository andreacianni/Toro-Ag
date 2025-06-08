<?php
/**
 * View: doc-plus-view.php
 * Riceve in $doc_plus_data l’array completo di doc_plus + attachments + flag.
 */

// 0) Debug lingua
$current_lang = defined('ICL_LANGUAGE_CODE')
    ? ICL_LANGUAGE_CODE
    : apply_filters('wpml_current_language', null);
echo "<!-- DEBUG VIEW: current_lang = {$current_lang} -->\n";

if ( empty( $doc_plus_data ) || ! is_array( $doc_plus_data ) ) {
    echo "<!-- DEBUG VIEW: no data -->\n";
    return;
}

// Apriamo la griglia delle card
echo '<div class="row">';

foreach ( $doc_plus_data as $doc ):
    // Applichiamo il filtro secondo la lingua
    $filtered = array_filter( $doc['attachments'], function( $att ) use ( $current_lang ) {
        $slug = $att['lang']['slug'] ?? '';
        return $current_lang === 'it'
            ? ( $slug === 'italiano' )
            : ( $slug !== 'italiano' );
    } );

    $count = count( $filtered );
    if ( $count === 0 ) {
        continue;
    }

    if ( 1 === $count ) :
        // Card singola: large 1/3, tablet 1/1
        $att = array_shift( $filtered );
        echo '<div class="col-lg-4 col-12 mb-4">';
        echo '<div class="card h-100">';
        if ( ! empty( $doc['cover_url'] ) ) {
            echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
               . 'class="card-img-top" '
               . 'alt="' . esc_attr( $doc['title'] ) . '">';
        }
        echo '<div class="card-body text-center">';
        echo '<a href="' . esc_url( $att['url'] ) . '" '
           . 'target="_blank" '
           . 'class="btn btn-primary">'
           . esc_html( $att['title'] )
           . '</a>';
        echo '</div></div></div>';
    else:
        // Card multipla: full width
        echo '<div class="col-12 mb-4">';
        echo '<div class="card">';
        echo '<div class="row g-0">';
        // Immagine a sinistra
        echo '<div class="col-md-4">';
        if ( ! empty( $doc['cover_url'] ) ) {
            echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
               . 'class="img-fluid" '
               . 'alt="' . esc_attr( $doc['title'] ) . '">';
        }
        echo '</div>';
        // Titoli a destra
        echo '<div class="col-md-8">';
        echo '<div class="card-body">';
        foreach ( $filtered as $att ) {
            echo '<p class="mb-2">'
               . '<a href="' . esc_url( $att['url'] ) . '" target="_blank">'
               . esc_html( $att['title'] )
               . '</a></p>';
        }
        echo '</div></div></div></div></div>';
    endif;
endforeach;

// Chiudiamo la griglia
echo '</div>';
