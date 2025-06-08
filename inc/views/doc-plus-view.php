<?php
/**
 * View: doc-plus-view.php
 * Riceve in $doc_plus_data l’array completo di doc_plus + attachments + flag,
 * e il parametro 'layout' estratto dal loader.
 * Ordina gli allegati secondo toroag_get_language_order e mostra le bandiere per lingue aggiuntive.
 */

if ( empty( $doc_plus_data ) || ! is_array( $doc_plus_data ) ) {
    return;
}

// Assicuriamoci che $layout sia definito e valido
$allowed_layouts = [ 'single', 'multiple', 'modern' ];
$layout = isset( $layout ) && in_array( $layout, $allowed_layouts, true ) ? $layout : 'single';

// Recuperiamo l'ordine delle lingue aggiuntive
action_exists('toroag_get_language_order') && $order_map = toroag_get_language_order();

// Debug: layout scelto
echo '<!-- Debug: layout passato = ' . esc_html( $layout ) . ' -->';
// Apriamo la griglia delle card
echo '<div class="row x">';

foreach ( $doc_plus_data as $index => $doc ):
    echo '<!-- Inizio ciclo document #: ' . ( $index + 1 ) . ' -->';

    // Filtro degli allegati secondo la lingua
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);

    // Filtra: lingua corrente come primo criterio, poi tutte le altre lingue aggiuntive
    $attachments = $doc['attachments'];
    // Ordiniamo secondo la mappa e manteniamo italiano in testa se corrente
    usort( $attachments, function( $a, $b ) use ( $order_map, $current_lang ) {
        $slug_a = $a['lang']['slug'] ?? '';
        $slug_b = $b['lang']['slug'] ?? '';
        // Se la lingua attuale è italiano, mantieni only italiano -> poi le altre ordinate
        if ( 'it' === $current_lang ) {
            if ( $slug_a === 'italiano' && $slug_b !== 'italiano' ) return -1;
            if ( $slug_b === 'italiano' && $slug_a !== 'italiano' ) return 1;
        }
        $pr_a = $order_map[ $slug_a ] ?? 999;
        $pr_b = $order_map[ $slug_b ] ?? 999;
        return $pr_a <=> $pr_b;
    } );

    // Applichiamo nuovamente il filtro di WPML per sicurezza
    $filtered = array_filter( $attachments, function( $att ) use ( $current_lang ) {
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
echo '<!-- Debug: entering case = ' . esc_html( $layout ) . ' -->';

    switch ( $layout ) {
        case 'multiple':
            echo '<!-- Debug: case = multiple -->';
            echo '<div class="col-12 mb-4">';
            echo '<div class="card h-100"><div class="row g-0 align-items-stretch">';
            // Testi a sinistra
            echo '<div class="col-md-8"><div class="card-body">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'] ?? '';
                echo "<h4><strong><a href=\"{$url}\" target=\"_blank\">{$title}</a></strong>";
                if ( 'italiano' !== $slug ) {
                    echo " " . toroag_get_flag_html( $slug );
                }
                echo "</h4>";
            }
            echo '</div></div>';
            // Immagine a destra full height
            echo '<div class="col-md-4">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                   . 'class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
            }
            echo '</div>';
            echo '</div></div></div>';
            break;

        case 'modern':
            echo '<!-- Debug: case = modern -->';
            echo '<div class="col-lg-4 col-12 mb-4">';
            echo '<div class="card h-100 modern-layout position-relative overflow-hidden">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                   . 'class="card-img h-100" style="object-fit:cover;" alt="Cover">';
            }
            echo '<div class="card-img-overlay d-flex flex-column justify-content-end bg-gradient-to-t from-black/50 to-transparent p-3">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'] ?? '';
                echo "<h4 class=\"mb-2\"><strong><a href=\"{$url}\" target=\"_blank\" class=\"text-white text-decoration-none\">{$title}</a></strong>";
                if ( 'italiano' !== $slug ) {
                    echo " " . toroag_get_flag_html( $slug );
                }
                echo "</h4>";
            }
            echo '</div></div></div>';
            break;

        case 'single':
        default:
            echo '<!-- Debug: case = single -->';
            echo '<div class="col-lg-4 col-12 mb-4">';
            echo '<div class="card h-100">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top" alt="Cover">';
            }
            echo '<div class="card-body text-center">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'] ?? '';
                echo "<h4><strong><a href=\"{$url}\" target=\"_blank\" class=\"text-decoration-none\">{$title}</a></strong>";
                if ( 'italiano' !== $slug ) {
                    echo " " . toroag_get_flag_html( $slug );
                }
                echo "</h4>";
            }
            echo '</div></div></div>';
            break;
    }

    echo '<!-- Fine ciclo document #: ' . ( $index + 1 ) . ' -->';
endforeach;

echo '</div>';
