<?php
/**
 * View: doc-plus-view.php
 * Riceve in $doc_plus_data l'array completo di doc_plus + attachments + flag,
 * e il parametro 'layout' estratto dal loader.
 * Ordina gli allegati secondo toroag_get_language_order e mostra le bandiere per lingue aggiuntive.
 */

if ( empty( $doc_plus_data ) || ! is_array( $doc_plus_data ) ) {
    return;
}

// Validiamo e definiamo il layout
$allowed_layouts = ['clean','multiple', 'card-imgsup', 'card-imgsx', 'card-imgdx', 'modern', 'single'];
$layout = isset( $layout ) && in_array( $layout, $allowed_layouts, true ) ? $layout : 'single';

// Se è stato passato un titolo, lo mostriamo sopra la griglia
if ( isset( $title ) && trim( $title ) !== '' ) {
    // classe striscetta nera con testo bianco text-bg-dark text-center py-2 my-4 rounded-2
    echo '<h3 class="text-start fs-4 fw-bold border-bottom ps-1 py-2 my-4">' . esc_html( $title ) . '</h3>';
}

// Se è stata passata una griglia, la usiamo senza alterarla.
// I default responsive sono centralizzati qui e dipendono dal numero di elementi.
$default_grid_classes = [
  1 => 'row row-cols-1',
  3 => 'row row-cols-1 row-cols-md-2 row-cols-lg-3',
  4 => 'row row-cols-1 row-cols-md-2 row-cols-xl-4',
];

if ( isset($griglia) && trim($griglia) !== '' ) {
  // Esempio: "row row-cols-1 row-cols-md-3 g-4".
  $grid_class = esc_attr( $griglia );
} else {
  $grid_class = $default_grid_classes[ count( $doc_plus_data ) ] ?? 'row';
}

// Recuperiamo la mappa di priorità lingue
$order_map = function_exists('toroag_get_language_order') ? toroag_get_language_order() : [];

// Apriamo la griglia delle card
echo '<div class="'. $grid_class .' doc-plus-view-layout-' . esc_attr( $layout ) . '">';

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
    // echo '<!-- Debug: before filter - total attachments ' . count( $attachments ) . ' -->';

    // Filtriamo per lingua corrente
    $filtered = array_filter( $attachments, function( $att ) use ( $current_lang ) {
        return $current_lang === 'it'
            ? ( $att['lang']['slug'] === 'italiano' )
            : ( $att['lang']['slug'] !== 'italiano' );
    } );

    // Debug: numero dopo il filtro
    // echo '<!-- Debug: after filter - filtered attachments ' . count( $filtered ) . ' -->';

    if ( empty( $filtered ) ) {
        echo '<!-- Skip: no attachments -->';
        continue;
    }

    // Determiniamo se c'è un solo link
    $single_link = count( $filtered ) === 1;
    $first_attachment = reset( $filtered );
    $single_url = $single_link ? esc_url( $first_attachment['url'] ) : '';

    // Rendering in base al layout selezionato
    switch ( $layout ) {
        case 'clean':
            // Layout clean: card pulita con link anche nell'immagine
            echo '<div class="col mb-4 layout-clean">';
                echo '<div class="card border-0 h-100 layout-clean">';
                if ( ! empty( $doc['cover_url'] ) ) {
                    // Recupera il primo link di attachment
                    $first_url = esc_url( reset( $filtered )['url'] );
                    echo '<a href="' . $first_url . '" target="_blank">';
                    echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top px-xl-5" alt="Cover">';
                    echo '</a>';
                }
                    echo '<div class="card-body pt-4 px-4 text-center">';
                    echo '<ul class="list-group list-group-flush d-inline-block text-start mw-100 mb-0 ps-0">';
                    $attachment_count = count( $filtered );
                    $attachment_index = 0;
                    foreach ( $filtered as $att ) {
                        $attachment_index++;
                        $title = esc_html( $att['title'] );
                        $url   = esc_url( $att['url'] );
                        $slug  = $att['lang']['slug'];
                        // *** MODIFICATO: aggiunta bandiera prima del titolo e icona dopo il titolo ***
                        $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                        $border_class = $attachment_index < $attachment_count ? ' border-bottom' : '';
                        echo '<li class="list-group-item border-0 bg-transparent px-0 py-2' . $border_class . '">';
                        echo '<a href="' . $url . '" target="_blank" class="d-flex align-items-start gap-2 text-decoration-none">';
                        if ( $slug !== 'italiano' ) {
                            echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                        }
                        echo '<span class="doc-plus-link-title flex-grow-1 text-break lh-sm">' . $title . '</span>';
                        if ( $current_lang === 'it' ) {
                            echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                        } else {
                            // echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                        }
                        echo '</a></li>';
                    }
                    echo '</ul></div>';
                echo '</div>';
            echo '</div>';
            break;
        /*
        case 'card-imgsup':
            // Layout clean: card pulita con link anche nell'immagine
            // echo '<!-- Layout clean -->';
            echo '<div class="col mb-4 layout-card-imgsup">';

            echo '<div class="card h-100">';
            if ( ! empty( $doc['cover_url'] ) ) {
                // Recupera il primo link di attachment
                $first_url = esc_url( reset( $filtered )['url'] );
                echo '<a href="' . $first_url . '" target="_blank">';
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top img-fluid w-100" alt="Cover">';
                echo '</a>';
            }
            echo '<div class="card-body pt-4 text-center">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'];
                // *** MODIFICATO: aggiunta bandiera prima del titolo e icona dopo il titolo ***
                $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                echo '<p class="mb-3"><a href="' . $url . '" target="_blank" class="text-decoration-none">';
                if ( $slug !== 'italiano' ) {
                    echo toroag_get_flag_html( $slug ) . ' ';
                }
                echo $title . ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                echo '</a></p>';
            }
            echo '</div></div></div>';
            break;
        case 'card-imgsx':
            echo '<div class="col mb-4 layout-card-imgsx">';
            echo '<div class="card h-100">';
            echo '<div class="row g-0 align-items-stretch">';
                // Colonna immagine a sinistra
                echo '<div class="col-md-4">';
                    if ( ! empty( $doc['cover_url'] ) ) {
                        echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                        . 'class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
                    }
                echo '</div>';
                // Colonna testo a destra
                echo '<div class="col-md-8 d-flex align-items-center">';
                echo '<div class="card-body">';
                    foreach ( $filtered as $att ) {
                        $title = esc_html( $att['title'] );
                        $url   = esc_url( $att['url'] );
                        $slug  = $att['lang']['slug'];
                        // *** MODIFICATO: aggiunta bandiera prima del titolo e icona dopo il titolo ***
                        $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                        echo "<p class=\"\">";
                        echo "<a href=\"{$url}\" target=\"_blank\">";
                        if ( $slug !== 'italiano' ) {
                            echo toroag_get_flag_html( $slug ) . ' ';
                        }
                        echo $title . ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                        echo "</a></p>";
                    }
                echo '</div></div>';
            echo '</div></div></div>';
            break;

        case 'card-imgdx':
            // Layout card con immagine a destra
            echo '<div class="col mb-4 layout-card-imgdx">';
            echo '<div class="card h-100">';
            echo '<div class="row g-0 align-items-stretch">';
                // Colonna testo a sinistra
                echo '<div class="col-md-8 d-flex align-items-center"><div class="card-body">';
                    foreach ( $filtered as $att ) {
                        $title = esc_html( $att['title'] );
                        $url   = esc_url( $att['url'] );
                        $slug  = $att['lang']['slug'];
                        // *** MODIFICATO: aggiunta bandiera prima del titolo e icona dopo il titolo ***
                        $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                        echo "<p><a href=\"{$url}\" target=\"_blank\">";
                        if ( $slug !== 'italiano' ) {
                            echo toroag_get_flag_html( $slug ) . ' ';
                        }
                        echo $title . ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                        echo '</a></p>';
                    }
                echo '</div></div>';
                // Colonna immagine a destra
                echo '<div class="col-md-4">';
                    if ( ! empty( $doc['cover_url'] ) ) {
                        echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                        . 'class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
                    }
                echo '</div>';
            echo '</div></div></div>';
            break;
        */
        case 'card-imgsup':
            echo '<div class="col mb-4 layout-card imgsup">';
            
            if ( $single_link ) {
                // Card completamente cliccabile per singolo link
                echo '<a href="' . $single_url . '" target="_blank" class="text-decoration-none text-reset">';
                echo '<div class="card h-100">';
                if ( ! empty( $doc['cover_url'] ) ) {
                    echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top img-fluid w-100" alt="Cover">';
                }
                echo '<div class="card-body pt-4 px-4 text-center">';
                $att = $first_attachment;
                $title = esc_html( $att['title'] );
                $slug  = $att['lang']['slug'];
                $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $att['url'] ) : 'bi-file-earmark-text';
                echo '<p class="mb-3 d-inline-flex align-items-start gap-2 text-start mw-100">';
                if ( $slug !== 'italiano' ) {
                    echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                }
                echo '<span class="doc-plus-link-title flex-grow-1 text-break lh-sm">' . $title . '</span>';
                if ( $current_lang === 'it' ) {
                    echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                } else {
                    // echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                }
                echo '</p>';
                echo '</div></div></a>';
            } else {
                // Card normale con link individuali
                echo '<div class="card h-100">';
                if ( ! empty( $doc['cover_url'] ) ) {
                    echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top img-fluid w-100" alt="Cover">';
                }
                echo '<div class="card-body pt-4 px-4">';
                echo '<ul class="list-group list-group-flush mb-0 ps-0">';
                $attachment_count = count( $filtered );
                $attachment_index = 0;
                foreach ( $filtered as $att ) {
                    $attachment_index++;
                    $title = esc_html( $att['title'] );
                    $url   = esc_url( $att['url'] );
                    $slug  = $att['lang']['slug'];
                    $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                    $border_class = $attachment_index < $attachment_count ? ' border-bottom' : '';
                    echo '<li class="list-group-item border-0 bg-transparent px-0 py-2' . $border_class . '">';
                    echo '<a href="' . $url . '" target="_blank" class="d-flex align-items-start gap-2 text-decoration-none">';
                    if ( $slug !== 'italiano' ) {
                        echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                    }
                    echo '<span class="doc-plus-link-title flex-grow-1 text-start text-break lh-sm">' . $title . '</span>';
                    if ( $current_lang === 'it' ) {
                        echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                    } else {
                        // echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                    }
                    echo '</a></li>';
                }
                echo '</ul></div></div>';
            }
            echo '</div>';
            break;

        case 'card-imgsx':
            echo '<div class="col mb-4 layout-card imgsx">';
            
            if ( $single_link ) {
                // Card completamente cliccabile per singolo link
                echo '<a href="' . $single_url . '" target="_blank" class="text-decoration-none text-reset">';
                echo '<div class="card h-100">';
                echo '<div class="row g-0 align-items-stretch">';
                    // Colonna immagine a sinistra
                    echo '<div class="col-md-4">';
                        if ( ! empty( $doc['cover_url'] ) ) {
                            echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                            . 'class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
                        }
                    echo '</div>';
                    // Colonna testo a destra
                    echo '<div class="col-md-8 d-flex align-items-center">';
                    echo '<div class="card-body px-4">';
                        $att = $first_attachment;
                        $title = esc_html( $att['title'] );
                        $slug  = $att['lang']['slug'];
                        $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $att['url'] ) : 'bi-file-earmark-text';
                        echo '<p class="mb-0 d-flex align-items-start gap-2">';
                        if ( $slug !== 'italiano' ) {
                            echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                        }
                        echo '<span class="doc-plus-link-title flex-grow-1 text-start text-break lh-sm">' . $title . '</span>';
                        if ( $current_lang === 'it' ) {
                            echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                        } else {
                            // echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                        }
                        echo '</p>';
                    echo '</div></div>';
                echo '</div></div></a>';
            } else {
                // Card normale con link individuali
                echo '<div class="card h-100">';
                echo '<div class="row g-0 align-items-stretch">';
                    // Colonna immagine a sinistra
                    echo '<div class="col-md-4">';
                        if ( ! empty( $doc['cover_url'] ) ) {
                            echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                            . 'class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
                        }
                    echo '</div>';
                    // Colonna testo a destra
                    echo '<div class="col-md-8 d-flex align-items-center">';
                    echo '<div class="card-body px-4">';
                    echo '<ul class="list-group list-group-flush mb-0 ps-0">';
                    $attachment_count = count( $filtered );
                    $attachment_index = 0;
                        foreach ( $filtered as $att ) {
                            $attachment_index++;
                            $title = esc_html( $att['title'] );
                            $url   = esc_url( $att['url'] );
                            $slug  = $att['lang']['slug'];
                            $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                            $border_class = $attachment_index < $attachment_count ? ' border-bottom' : '';
                            echo '<li class="list-group-item border-0 bg-transparent px-0 py-2' . $border_class . '">';
                            echo '<a href="' . $url . '" target="_blank" class="d-flex align-items-start gap-2 text-decoration-none">';
                            if ( $slug !== 'italiano' ) {
                                echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                            }
                            echo '<span class="doc-plus-link-title flex-grow-1 text-start text-break lh-sm">' . $title . '</span>';
                            if ( $current_lang === 'it' ) {
                                echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                            } else {
                                // echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                            }
                            echo '</a></li>';
                        }
                    echo '</ul></div></div>';
                echo '</div></div>';
            }
            echo '</div>';
            break;

        case 'card-imgdx':
            echo '<div class="col mb-4 layout-card imgdx">';
            
            if ( $single_link ) {
                // Card completamente cliccabile per singolo link
                echo '<a href="' . $single_url . '" target="_blank" class="text-decoration-none text-reset">';
                echo '<div class="card h-100">';
                echo '<div class="row g-0 align-items-stretch">';
                    // Colonna testo a sinistra
                    echo '<div class="col-md-8 d-flex align-items-center"><div class="card-body px-4">';
                        $att = $first_attachment;
                        $title = esc_html( $att['title'] );
                        $slug  = $att['lang']['slug'];
                        $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $att['url'] ) : 'bi-file-earmark-text';
                        echo '<p class="mb-0 d-flex align-items-start gap-2">';
                        if ( $slug !== 'italiano' ) {
                            echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                        }
                        echo '<span class="doc-plus-link-title flex-grow-1 text-start text-break lh-sm">' . $title . '</span>';
                        if ( $current_lang === 'it' ) {
                            echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                        } else {
                            // echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                        }
                        echo '</p>';
                    echo '</div></div>';
                    // Colonna immagine a destra
                    echo '<div class="col-md-4">';
                        if ( ! empty( $doc['cover_url'] ) ) {
                            echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                            . 'class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
                        }
                    echo '</div>';
                echo '</div></div></a>';
            } else {
                // Card normale con link individuali
                echo '<div class="card h-100">';
                echo '<div class="row g-0 align-items-stretch">';
                    // Colonna testo a sinistra
                    echo '<div class="col-md-8 d-flex align-items-center"><div class="card-body px-4">';
                    echo '<ul class="list-group list-group-flush mb-0 ps-0">';
                    $attachment_count = count( $filtered );
                    $attachment_index = 0;
                        foreach ( $filtered as $att ) {
                            $attachment_index++;
                            $title = esc_html( $att['title'] );
                            $url   = esc_url( $att['url'] );
                            $slug  = $att['lang']['slug'];
                            $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                            $border_class = $attachment_index < $attachment_count ? ' border-bottom' : '';
                            echo '<li class="list-group-item border-0 bg-transparent px-0 py-2' . $border_class . '">';
                            echo '<a href="' . $url . '" target="_blank" class="d-flex align-items-start gap-2 text-decoration-none">';
                            if ( $slug !== 'italiano' ) {
                                echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                            }
                            echo '<span class="doc-plus-link-title flex-grow-1 text-start text-break lh-sm">' . $title . '</span>';
                            if ( $current_lang === 'it' ) {
                                echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                            } else {
                                // echo ' <i class="' . esc_attr( $icon_class ) . ' flex-shrink-0"></i>';
                            }
                            echo '</a></li>';
                        }
                    echo '</ul></div></div>';
                    // Colonna immagine a destra
                    echo '<div class="col-md-4">';
                        if ( ! empty( $doc['cover_url'] ) ) {
                            echo '<img src="' . esc_url( $doc['cover_url'] ) . '" '
                            . 'class="img-fluid h-100" style="object-fit:cover;" alt="Cover">';
                        }
                    echo '</div>';
                echo '</div></div>';
            }
            echo '</div>';
            break;
        case 'modern':
            // Layout moderno: card con immagine di copertura e titolo centrato
            echo '<div class="col mb-4 layout-modern">';
            echo '<div class="card h-100 modern-layout position-relative overflow-hidden2">';
            if ( ! empty( $doc['cover_url'] ) ) {
                echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img h-100" style="object-fit:cover;" alt="Cover">';
            }
            echo '<div class="card-img-overlay d-flex flex-column justify-content-end p-0">';
            foreach ( $filtered as $att ) {
                $title = esc_html( $att['title'] );
                $url   = esc_url( $att['url'] );
                $slug  = $att['lang']['slug'];
                // *** MODIFICATO: aggiunta bandiera prima del titolo e icona dopo il titolo ***
                $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-text';
                echo "<h5 class=\"mb-0 pt-2 text-center bg-dark\" style=\"--bs-bg-opacity: .7;\"><a href=\"{$url}\" target=\"_blank\" class=\"text-white text-decoration-none\">";
                if ( $slug !== 'italiano' ) {
                    echo toroag_get_flag_html( $slug ) . ' ';
                }
                echo $title;
                if ( $current_lang === 'it' ) {
                    echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                } else {
                    // echo ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                }
                echo '</a></h5>';
            }
            echo '</div></div></div>';
            break;

        case 'single':
        default:
            // Layout singolo: card con copertina e titolo centrato
            echo '<div class="col mb-4 layout-single">';
                if ( ! empty( $doc['cover_url'] ) ) {
                    echo '<img src="' . esc_url( $doc['cover_url'] ) . '" class="card-img-top" alt="Cover">';
                }
                echo '<div class="card-body px-4 text-center">';
                    echo '<ul class="list-group list-group-flush d-inline-block text-start mw-100 mb-0 ps-0">';
                    $attachment_count = count( $filtered );
                    $attachment_index = 0;
                    foreach ( $filtered as $att ) {
                        $attachment_index++;
                        $title = esc_html( $att['title'] );
                        $url   = esc_url( $att['url'] );
                        $slug  = $att['lang']['slug'];
                        $border_class = $attachment_index < $attachment_count ? ' border-bottom' : '';
                        echo '<li class="list-group-item border-0 bg-transparent px-0 py-2' . $border_class . '">';
                        echo '<a href="' . $url . '" target="_blank" class="d-flex align-items-start gap-2 text-decoration-none">';
                        if ( $slug !== 'italiano' ) {
                            echo '<span class="flex-shrink-0">' . toroag_get_flag_html( $slug ) . '</span>';
                        }
                        echo '<span class="doc-plus-link-title flex-grow-1 text-break lh-sm">' . $title . '</span>';
                        echo '</a></li>';
                    }
                    echo '</ul>';
                echo '</div>';
            echo '</div>';
            break;
    }

    // Fine ciclo documento
    echo '<!-- Fine ciclo document #: ' . ( $index + 1 ) . ' -->';
endforeach;

echo '</div>';