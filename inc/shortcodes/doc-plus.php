<?php
/**
 * Shortcode [doc_plus]
 * Mostra tutti i doc_plus collegati tramite il campo 'doc_plus_inpage' della pagina corrente.
 */
function doc_plus_shortcode() {
    $page_id  = get_the_ID();
    // Carico il Pod 'page' della pagina corrente
    $page_pod = pods( 'page', $page_id );
    if ( ! $page_pod->exists() ) {
        return '';
    }

    // Leggo il campo relazione verso doc_plus (multi-pick)
    $related_docs = $page_pod->field( 'doc_plus_inpage' );
    if ( empty( $related_docs ) ) {
        return ''; // nessun doc_plus collegato
    }

    ob_start();
    echo '<div class="doc-plus-list">';
    foreach ( $related_docs as $item ) {
        $doc_id  = $item['ID'];
        $pod     = pods( 'doc_plus', $doc_id );

        // Titolo (WPML fornisce già la traduzione)
        $title = get_the_title( $doc_id );

        // Cover
        $cover_id  = $pod->field( 'doc_plus_cover.ID' );
        $cover_url = $cover_id ? wp_get_attachment_url( $cover_id ) : '';

        ?>
        <div class="doc-plus-item">
          <h3><?php echo esc_html( $title ); ?></h3>

          <?php if ( $cover_url ) : ?>
            <img src="<?php echo esc_url( $cover_url ); ?>"
                 alt="<?php echo esc_attr( $title ); ?>"
                 class="doc-plus-cover" />
          <?php endif; ?>

          <?php
          // Allegati: relazione verso documenti_prodotto
          $allegati = $pod->field( 'doc_plus_allegati' );
          if ( ! empty( $allegati ) ) : ?>
            <ul class="doc-plus-allegati">
              <?php foreach ( $allegati as $rel ) :
                $pdf_id  = $rel['ID'];
                $pod_pdf = pods( 'documenti_prodotto', $pdf_id );

                // URL del PDF
                $file_id  = $pod_pdf->field( 'documento-prodotto.ID' );
                $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';

                // Titolo del PDF (tradotto da WPML)
                $pdf_title = get_the_title( $pdf_id );

                // Lingua aggiuntiva per la bandierina
                $lingue = $pod_pdf->field( 'lingua_aggiuntiva' );
                if ( ! empty( $lingue ) ) {
                    $term      = $lingue[0];
                    $slug      = $term['slug'];
                    $name      = $term['name'];
                    $flag_html = "<span class='flag flag-{$slug}'>"
                               . esc_html( $name )
                               . "</span> ";
                } else {
                    $flag_html = '';
                }
                ?>
                <li>
                  <?php echo $flag_html; ?>
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
    }
    echo '</div>';

    return ob_get_clean();
}
add_shortcode( 'doc_plus', 'doc_plus_shortcode' );


