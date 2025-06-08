<?php
/**
 * [doc_plus]
 * Recupera tutti i doc_plus collegati alla pagina corrente tramite meta key "pagine"
 */
function doc_plus_shortcode() {
    $page_id = get_the_ID();

    // Sostituisci 'pagine' con lo slug esatto del tuo field Pods di relazione verso le pagine
    $relation_meta_key = 'pagine';

    $args = array(
        'post_type'      => 'doc_plus',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => array(
            array(
                'key'     => $relation_meta_key,
                'value'   => sprintf( '"%d"', $page_id ),  // se Pods serializza in JSON; altrimenti usa "%{$page_id}%"
                'compare' => 'LIKE',
            ),
        ),
    );

    $q = new WP_Query( $args );
    if ( ! $q->have_posts() ) {
        wp_reset_postdata();
        return '';
    }

    ob_start();
    echo '<div class="doc-plus-list">';
    while ( $q->have_posts() ) {
        $q->the_post();
        $doc_id = get_the_ID();
        $pod    = pods( 'doc_plus', $doc_id );

        // Titolo (WPML restituisce la traduzione giusta)
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
          // Allegati
          $allegati = $pod->field( 'doc_plus_allegati' );
          if ( ! empty( $allegati ) ) : ?>
            <ul class="doc-plus-allegati">
              <?php foreach ( $allegati as $item ) :
                $pdf_id  = $item['ID'];
                $pod_pdf = pods( 'documenti_prodotto', $pdf_id );

                // URL del PDF
                $file_id  = $pod_pdf->field( 'documento-prodotto.ID' );
                $file_url = $file_id ? wp_get_attachment_url( $file_id ) : '';

                // Titolo del PDF
                $pdf_title = get_the_title( $pdf_id );

                // Lingua aggiuntiva
                $lingue = $pod_pdf->field( 'lingua_aggiuntiva' );
                if ( ! empty( $lingue ) ) {
                    $term     = $lingue[0];
                    $slug     = $term['slug'];
                    $name     = $term['name'];
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
    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode( 'doc_plus', 'doc_plus_shortcode' );


