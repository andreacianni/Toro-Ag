<?php
/**
 * View: documenti-pagina-view.php
 * Template compatto per documenti pagina - lista semplice senza card
 */

if ( empty( $doc_plus_data ) || ! is_array( $doc_plus_data ) ) {
    return;
}

// Se è stato passato un titolo, lo mostriamo
if ( isset( $title ) && trim( $title ) !== '' ) {
    echo '<h3 class="text-start fs-4 fw-bold border-bottom ps-1 py-2 my-4">' . esc_html( $title ) . '</h3>';
}

// Lingua corrente
$current_lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);

// Recuperiamo la mappa di priorità lingue
$order_map = function_exists('toroag_get_language_order') ? toroag_get_language_order() : [];

// Raccogliamo tutti gli allegati
$all_attachments = [];
foreach ( $doc_plus_data as $doc ) {
    $attachments = $doc['attachments'];
    
    // Ordiniamo allegati secondo la priorità linguistica
    usort( $attachments, function( $a, $b ) use ( $order_map ) {
        $pr_a = $order_map[ $a['lang']['slug'] ] ?? 999;
        $pr_b = $order_map[ $b['lang']['slug'] ] ?? 999;
        return $pr_a <=> $pr_b;
    } );
    
    $all_attachments = array_merge( $all_attachments, $attachments );
}

if ( empty( $all_attachments ) ) {
    return;
}

// Se siamo in italiano, mostriamo solo i documenti italiani
if ( $current_lang === 'it' ) {
    $filtered_attachments = array_filter( $all_attachments, function( $att ) {
        return $att['lang']['slug'] === 'italiano';
    } );
    
    if ( ! empty( $filtered_attachments ) ) {
        echo '<div class="documents-list">';
        foreach ( $filtered_attachments as $att ) {
            $doc_title = esc_html( $att['title'] );
            $url = esc_url( $att['url'] );
            $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-pdf';
            
            echo '<div class="mb-2">';
            echo '<a href="' . $url . '" target="_blank" class="text-decoration-none">';
            echo $doc_title . ' <i class="' . esc_attr( $icon_class ) . '"></i>';
            echo '</a>';
            echo '</div>';
        }
        echo '</div>';
    }
} else {
    // Per le altre lingue, raggruppiamo per lingua
    $by_language = [];
    foreach ( $all_attachments as $att ) {
        $lang_slug = $att['lang']['slug'];
        if ( $lang_slug !== 'italiano' ) {
            $by_language[ $lang_slug ][] = $att;
        }
    }
    
    if ( ! empty( $by_language ) ) {
        foreach ( $by_language as $lang_slug => $documents ) {
            // Titolo della sezione con bandiera
            $flag_html = function_exists('toroag_get_flag_html') ? toroag_get_flag_html( $lang_slug ) : '';
            $lang_names = [
                'inglese' => 'Download English',
                'francese' => 'Download French', 
                'spagnolo' => 'Download Spanish',
                'tedesco' => 'Download German',
                'portoghese' => 'Download Portuguese'
            ];
            $lang_display = $lang_names[ $lang_slug ] ?? 'Download ' . ucfirst( $lang_slug );
            
            echo '<h4 class="mt-4 mb-3">' . $flag_html . ' ' . esc_html( $lang_display ) . '</h4>';
            
            echo '<div class="documents-list">';
            foreach ( $documents as $att ) {
                $doc_title = esc_html( $att['title'] );
                $url = esc_url( $att['url'] );
                $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-file-earmark-pdf';
                
                echo '<div class="mb-2">';
                echo '<a href="' . $url . '" target="_blank" class="text-decoration-none">';
                echo $doc_title . ' <i class="' . esc_attr( $icon_class ) . '"></i>';
                echo '</a>';
                echo '</div>';
            }
            echo '</div>';
        }
    }
}
?>