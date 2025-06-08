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
global $layout;
$layout = ! empty( $layout ) ? $layout : 'single'; // default 'single'

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
        continue;
    }

    // Render in base al layout selezionato
    switch ( $layout ) {
        case 'multiple':
            // Multiple attachments card: ciclo già presente
            echo '<div class="col-12 mb-4">';
            echo '<div class="card"><div class="row g-0">';
            echo '<div class="col-md-4">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="img-fluid" alt="" >';
            }
            echo '</div>'; // col-md-4
            echo '<div class="col-md-8"><div class="card-body">';
            foreach ( $filtered as $att ) {
                echo '<p class="mb-2">';
                echo '<a href="' . esc_url( $att['url'] ) . '" target="_blank">' . esc_html( $att['title'] ) . '</a>';
                if ( $current_lang !== 'it' && ! empty( $att['flag'] ) ) {
                    echo ' ' . $att['flag'];
                }
                echo '</p>';
            }
            echo '</div></div></div></div></div>';
            break;

        case 'single':
        default:
            // Single-style: una card per documento con tutti i link in body
            echo '<div class="col-lg-4 col-12 mb-4">';
            echo '<div class="card h-100">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top" alt="" >';
            }
            echo '<div class="card-body text-center">';
            // Mostriamo tutti gli allegati come pulsanti
            foreach ( $filtered as $att ) {
                echo '<a href="' . esc_url( $att['url'] ) . '" target="_blank" class="btn btn-primary me-2 mb-2">';
                echo esc_html( $att['title'] );
                if ( $current_lang !== 'it' && ! empty( $att['flag'] ) ) {
                    echo ' ' . $att['flag'];
                }
                echo '</a>';
            }
            echo '</div></div></div>';
            break;
    }

    echo '<!-- Fine ciclo document #: ' . ( $index + 1 ) . ' -->';
endforeach;

echo '</div>';
?>
