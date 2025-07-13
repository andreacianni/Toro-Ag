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

// Raccogliamo tutti gli allegati e li ordiniamo
$all_attachments = [];
foreach ( $doc_plus_data as $doc ) {
    $attachments = $doc['attachments'];
    
    // Ordiniamo allegati secondo la priorità linguistica
    usort( $attachments, function( $a, $b ) use ( $order_map ) {
        $pr_a = $order_map[ $a['lang']['slug'] ] ?? 999;
        $pr_b = $order_map[ $b['lang']['slug'] ] ?? 999;
        return $pr_a <=> $pr_b;
    } );
    
    // Filtriamo per lingua corrente
    $filtered = array_filter( $attachments, function( $att ) use ( $current_lang ) {
        return $current_lang === 'it'
            ? ( $att['lang']['slug'] === 'italiano' )
            : ( $att['lang']['slug'] !== 'italiano' );
    } );
    
    $all_attachments = array_merge( $all_attachments, $filtered );
}

if ( empty( $all_attachments ) ) {
    return;
}
?>

<ul class="list-unstyled">
    <?php foreach ( $all_attachments as $att ) : 
        $title = esc_html( $att['title'] );
        $url   = esc_url( $att['url'] );
        $slug  = $att['lang']['slug'];
        $icon_class = function_exists('toroag_get_icon_class') ? toroag_get_icon_class( $url ) : 'bi-download';
    ?>
        <li class="mb-2">
            <a href="<?php echo $url; ?>" target="_blank" class="text-decoration-none">
                <i class="<?php echo esc_attr( $icon_class ); ?> me-2"></i>
                <?php 
                if ( $slug !== 'italiano' && function_exists('toroag_get_flag_html') ) {
                    echo toroag_get_flag_html( $slug ) . ' ';
                }
                echo $title; 
                ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>