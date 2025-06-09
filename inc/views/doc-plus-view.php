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

// Validiamo e definiamo il layout
$allowed_layouts = [ 'single', 'multiple', 'modern', 'grid', 'list', 'clean' ];
$layout = isset( $layout ) && in_array( $layout, $allowed_layouts, true ) ? $layout : 'single';

// Recuperiamo la mappa di priorità lingue
$order_map = function_exists('toroag_get_language_order') ? toroag_get_language_order() : [];

// Apriamo la griglia delle card
echo '<div class="row x">';

foreach ( $doc_plus_data as $index => $doc ):
    // Inizio ciclo per ogni documento con layout corrente
    echo '<!-- Inizio ciclo document #: ' . ( $index + 1 ) . ' con layout ' . esc_html( $layout ) . ' -->';

    // Lingua corrente
    $current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);

    // Ordiniamo allegati secondo la priorità linguistica
    $attachments = $doc['attachments'];
    usort( $attachments, function( $a, $b ) use ( $order_map ) {
        $pr_a = $order_map[ $a['lang']['slug'] ] ?? 999;
        $pr_b = $order_map[ $b['lang']['slug'] ] ?? 999;
        return $pr_a <=> $pr_b;
    } );

    // Debug: numero totale prima del filtro
    echo '<!-- Debug: before filter - total attachments ' . count( $attachments ) . ' -->';

    // Filtriamo per lingua corrente
    $filtered = array_filter( $attachments, function( $att ) use ( $current_lang ) {
        return $current_lang === 'it'
            ? ( $att['lang']['slug'] === 'italiano' )
            : ( $att['lang']['slug'] !== 'italiano' );
    } );

    // Debug: numero dopo il filtro
    echo '<!-- Debug: after filter - filtered attachments ' . count( $filtered ) . ' -->';

    if ( empty( $filtered ) ) {
        echo '<!-- Skip: no attachments -->';
        continue;
    }

    // Rendering in base al layout selezionato
    switch ( $layout ) {
        case 'clean':
            // Layout clean: card pulita con link anche nell'immagine
            echo '<!-- Layout clean -->';
            echo '<div class="col-md-4 col-12 mb-4">';
            echo '<div class="card border-0 shadow-sm h-100">';
            if ( ! empty( $doc['cover_url'] ) ) {
                // Recupera il primo link di attachment
                $first_url = esc_url( reset( $filtered )['url'] );
                echo '<a href="' . $first_url . '" target="_blank">';
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top" alt="Cover">';
                echo '</a>';
            }
            echo '<div class="card-body pt-4 text-center">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'];
                echo '<h4><strong><a href="' . $url . '" target="_blank" class="text-decoration-none">'
                    . $title . '</a></strong>';
                if ( $slug !== 'italiano' ) {
                    echo ' ' . toroag_get_flag_html( $slug );
                }
                echo '</h4>';
            }
            echo '</div></div></div>';
            break;

        case 'grid':
            // Layout a griglia: cards uniformi
            echo '<div class="col-sm-6 col-md-4">';
            echo '<div class="card h-100">';
            echo '<div class="ratio ratio-4x3">';
            if($doc['cover_url']) echo '<img src="'.esc_url($doc['cover_url']).'" class="card-img-top" alt="Cover">';
            echo '</div>';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">'.esc_html($doc['title']).'</h5>';
            foreach($filtered as $att){
                $slug=$att['lang']['slug'];
                echo '<a href="'.esc_url($att['url']).'" class="btn btn-outline-primary btn-sm me-1 mb-1">'.esc_html($att['title']).'</a>';
                if($slug!=='italiano') echo toroag_get_flag_html($slug);
            }
            echo '</div></div></div>';
            break;
        
        case 'list':
            // Layout a lista: thumbs + testo a fianco
            echo '<div class="col-12">';
            echo '<div class="d-flex align-items-center p-3 border rounded">';
            if($doc['cover_url']) echo '<img src="'.esc_url($doc['cover_url']).'" class="flex-shrink-0 me-3" style="width:80px; height:80px; object-fit:cover; border-radius:4px;" alt="Cover">';
            echo '<div>';            
            echo '<h5>'.esc_html($doc['title']).'</h5>';
            echo '<ul class="list-unstyled mb-0">';
            foreach($filtered as $att){
                $slug=$att['lang']['slug'];
                echo '<li class="mb-1">';
                echo '<h6 class="d-inline"><a href="'.esc_url($att['url']).'">'.esc_html($att['title']).'</a></h6> ';
                if($slug!=='italiano') echo toroag_get_flag_html($slug);
                echo '</li>';
            }
            echo '</ul></div></div></div>';
            break;

        case 'multiple':
            echo '<!-- Layout multiple -->';
            echo '<div class="col-12 mb-4"><div class="card h-100"><div class="row g-0 align-items-stretch">';
            // Colonna testo a sinistra
            echo '<div class="col-md-8"><div class="card-body">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'];
                echo "<h4><strong><a href=\"{$url}\" target=\"_blank\">{$title}</a></strong>";
                if ( $slug !== 'italiano' ) {
                    echo ' ' . toroag_get_flag_html( $slug );
                }
                echo '</h4>';
            }
            echo '</div></div>';
            // Colonna immagine a destra
            echo '<div class="col-md-4">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
            }
            echo '</div></div></div></div>';
            break;

        case 'modern':
            echo '<!-- Layout modern -->';
            echo '<div class="col-lg-4 col-12 mb-4"><div class="card h-100 modern-layout position-relative overflow-hidden">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img h-100" style="object-fit:cover;" alt="Cover">';
            }
            echo '<div class="card-img-overlay d-flex flex-column justify-content-end bg-gradient-to-t from-black/50 to-transparent p-3">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'];
                echo "<h4 class=\"mb-2\"><strong><a href=\"{$url}\" target=\"_blank\" class=\"text-white text-decoration-none\">{$title}</a></strong>";
                if ( $slug !== 'italiano' ) {
                    echo ' ' . toroag_get_flag_html( $slug );
                }
                echo '</h4>';
            }
            echo '</div></div></div>';
            break;

        case 'single':
        default:
            echo '<!-- Layout single -->';
            echo '<div class="col-lg-4 col-12 mb-4"><div class="card h-100">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top" alt="Cover">';
            }
            echo '<div class="card-body text-center">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'];
                echo "<h4><strong><a href=\"{$url}\" target=\"_blank\" class=\"text-decoration-none\">{$title}</a></strong>";
                if ( $slug !== 'italiano' ) {
                    echo ' ' . toroag_get_flag_html( $slug );
                }
                echo '</h4>';
            }
            echo '</div></div></div>';
            break;
    }

    // Fine ciclo documento
    echo '<!-- Fine ciclo document #: ' . ( $index + 1 ) . ' -->';
endforeach;

echo '</div>';
