<?php
/**
 * Shortcode [doc_plus id="123"]
 * Mostra titolo, cover e lista PDF (con bandierina lingua).
 */
function render_doc_plus_shortcode( $atts ) {
    // Parametri: id del doc_plus (default: post corrente)
    $atts = shortcode_atts( array(
        'id' => get_the_ID(),
    ), $atts, 'doc_plus' );

    $doc_id = intval( $atts['id'] );
    $pod   = pods( 'doc_plus', $doc_id );
    if ( ! $pod->exists() ) {
        return ''; // niente da mostrare
    }

    // 1) Titolo
    $title = get_the_title( $doc_id );

    // 2) Cover
    $cover_id  = $pod->field( 'doc_plus_cover.ID' );
    $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';

    // 3) Allegati (relation verso documenti_prodotto)
    $allegati = $pod->field( 'doc_plus_allegati' ); // array di ['ID'=>...]

    // Output buffer
    ob_start();
    ?>
    <div class="doc-plus">
      <h2><?php echo esc_html( $title ); ?></h2>
      <?php if ( $cover_url ) : ?>
        <img src="<?php echo esc_url( $cover_url ); ?>"
             alt="<?php echo esc_attr( $title ); ?>" />
      <?php endif; ?>

      <?php if ( ! empty( $allegati ) ) : ?>
        <ul class="doc-plus-allegati">
          <?php foreach ( $allegati as $item ) :
            $pdf_id  = $item['ID'];
            $pod_pdf = pods( 'documenti_prodotto', $pdf_id );

            // File PDF
            $file_id  = $pod_pdf->field( 'documento-prodotto.ID' );
            $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';

            // Titolo del PDF
            $pdf_title = get_the_title( $pdf_id );

            // Lingua aggiuntiva (tassonomia)
            $lingue = $pod_pdf->field( 'lingua_aggiuntiva' );
            if ( ! empty( $lingue ) ) {
                $term         = $lingue[0];
                $lang_slug    = $term['slug'];
                $lang_name    = $term['name'];
                // Qui puoi sostituire con il tuo markup bandiera
                $flag_markup  = "<span class='flag flag-{$lang_slug}'>"
                              . esc_html( $lang_name )
                              . "</span> ";
            } else {
                $flag_markup = '';
            }
            ?>
            <li>
              <?php echo $flag_markup; ?>
              <?php if ( $file_url ) : ?>
                <a href="<?php echo esc_url( $file_url ); ?>"
                   target="_blank">
                  <?php echo esc_html( $pdf_title ); ?>
                </a>
              <?php else : ?>
                <?php echo esc_html( $pdf_title ); ?>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}
add_shortcode( 'doc_plus', 'render_doc_plus_shortcode' );
