<?php
/**
 * View: doc-plus-view.php
 * Riceve in $doc_plus_data l’array completo di doc_plus + attachments + flag,
 * e il parametro 'layout' passato dallo shortcode.
 */

if ( empty( $doc_plus_data ) || ! is_array( $doc_plus_data ) ) {
    return;
}

// Recuperiamo il layout passato
global $atts;
$layout = $atts['layout'] ?? 'single'; // default 'single'

// Debug: mostriamo il layout scelto
echo '<!-- Debug: layout passato = ' . esc_html( $layout ) . ' -->';

// Apriamo la griglia delle card
echo '<div class="row x">';

foreach ( $doc_plus_data as $index => $doc ):
    echo '<!-- Inizio ciclo document #: ' . ( $index + 1 ) . ' -->';

    // Filtro degli allegati secondo la lingua
    $current_lang = defined('ICL_LANGUAGE_CODE')
        ? ICL_LANGUAGE_CODE
        : apply_filters('wpml_current_language', null);

    $filtered = array_filter( $doc['attachments'], function( $att ) use ( $current_lang ) {
        $slug = $att['lang']['slug'] ?? '';
        return $current_lang === 'it'
            ? ( $slug === 'italiano' )
            : ( $slug !== 'italiano' );
    } );

    if ( empty( $filtered ) ) {
        echo '<!-- Debug: nessun attachment filtrato, skip document -->';
        continue;
    }

    // Debug: in quale case entriamo
    switch ( $layout ) {
        case 'multiple':
            echo '<!-- Debug: case = multiple -->';
            // Multiple attachments con immagine a destra e testi a sinistra
            echo '<div class="col-12 mb-4">';
            echo '<div class="card h-100"><div class="row g-0 align-items-stretch">';
            // Testi a sinistra
            echo '<div class="col-md-8"><div class="card-body">';
            foreach ( $filtered as $att ) {
                echo '<h4><strong><a href="' . esc_url( $att['url'] ) . '" target="_blank">'
                    . esc_html( $att['title'] )
                    . '</a></strong></h4>';
                if ( $current_lang !== 'it' && ! empty( $att['flag'] ) ) {
                    echo '<p>' . $att['flag'] . '</p>';
                }
            }
            echo '</div></div>';
            // Immagine a destra full height
            echo '<div class="col-md-4">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                   . 'class="img-fluid h-100" style="object-fit:cover;" alt="" >';
            }
            echo '</div>';
            echo '</div></div></div>';
            break;

        case 'modern':
            echo '<!-- Debug: case = modern -->';
            // Nuovo layout moderno: card overlay con cover e titoli
            echo '<div class="col-lg-4 col-12 mb-4">';
            echo '<div class="card h-100 modern-layout position-relative overflow-hidden">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                   . 'class="card-img h-100" style="object-fit:cover;" alt="" >';
            }
            echo '<div class="card-img-overlay d-flex flex-column justify-content-end bg-gradient-to-t from-black/50 to-transparent p-3">';
            foreach ( $filtered as $att ) {
                echo '<h4 class="mb-2"><strong><a href="' . esc_url( $att['url'] ) . '" '
                   . 'target="_blank" class="text-white text-decoration-none">'
                   . esc_html( $att['title'] )
                   . '</a></strong></h4>';
            }
            echo '</div></div></div>';
            break;

        case 'single':
        default:
            echo '<!-- Debug: case = single (default) -->';
            // Single-style: una card per documento con tutti i link in h4 grassetti
            echo '<div class="col-lg-4 col-12 mb-4">';
            echo '<div class="card h-100">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top" alt="" >';
            }
            echo '<div class="card-body text-center">';
            foreach ( $filtered as $att ) {
                echo '<h4><strong><a href="' . esc_url( $att['url'] ) . '" '
                   . 'target="_blank" class="text-decoration-none">'
                   . esc_html( $att['title'] )
                   . '</a></strong></h4>';
            }
            echo '</div></div></div>';
            break;
    }

    echo '<!-- Fine ciclo document #: ' . ( $index + 1 ) . ' -->';
endforeach;

echo '</div>';
?>
