<?php
/**
 * Shortcode [doc_plus]
 * Recupera tutti i doc_plus collegati alla pagina corrente e ne stampa titolo, cover e allegati.
 */
function doc_plus_shortcode() {
    $page_id = get_the_ID();

    // Slug del campo Pods che hai usato per collegare doc_plus alle pagine
    $relation_field = 'pagine'; 

    // Parametri Pods per recuperare tutti i doc_plus collegati alla pagina
    $params = array(
        'limit' => -1,
        'where' => "{$relation_field}.ID = {$page_id}",
        'orderby' => 'post_date DESC',
    );

    $docs = pods( 'doc_plus', $params );
    if ( ! $docs->total() ) {
        return ''; // nessun doc_plus collegato
    }

    ob_start();
    echo '<div class="doc-plus-list">';
    while ( $docs->fetch() ) {
        $doc_id = $docs->id();
        $title  = $docs->display( 'post_title' );

        // Cover
        $cover_id  = $docs->field( 'doc_plus_cover.ID' );
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
          $allegati = $docs->field( 'doc_plus_allegati' );
          if ( ! empty( $allegati ) ) : ?>
            <ul class="doc-plus-allegati">
              <?php foreach ( $allegati as $item ) :
                $pdf_id  = $item['ID'];
                $pod_pdf = pods( 'documenti_prodotto', $pdf_id );

                // URL del PDF
                $file_id  = $pod_pdf->field( 'documento-prodotto.ID' );
                $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';

                // Titolo del PDF
                $pdf_title = $pod_pdf->display( 'post_title' );

                // Lingua aggiuntiva (tassonomia)
                $lingue = $pod_pdf->field( 'lingua_aggiuntiva' );
                if ( ! empty( $lingue ) ) {
                    $term      = $lingue[0];
                    $slug      = $term['slug'];
                    $name      = $term['name'];
                    // Qui il markup della bandierina (puoi sostituire con il tuo SVG)
                    $flag_html = "<span class='flag flag-{$slug}'>{$name}</span> ";
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

