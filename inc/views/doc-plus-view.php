<?php
/**
 * View: doc-plus-view.php
 * Riceve in $doc_plus_data l’array completo di doc_plus + attachments + flag.
 */

// 0) Debug lingua
$current_lang = defined('ICL_LANGUAGE_CODE')
    ? ICL_LANGUAGE_CODE
    : apply_filters('wpml_current_language', null);
echo "<!-- DEBUG VIEW: current_lang = {$current_lang} -->\n";

if ( empty( $doc_plus_data ) || ! is_array( $doc_plus_data ) ) {
    echo "<!-- DEBUG VIEW: no data -->\n";
    return;
}

foreach ( $doc_plus_data as $doc ):

    // 1) Debug cover & doc_id
    echo "<!-- DEBUG VIEW: processing doc_id={$doc['id']} title=\"{$doc['title']}\" -->\n";

    // 2) Raw IDs prima del filtro
    $raw_ids = wp_list_pluck( $doc['attachments'], 'id' );
    echo "<!-- DEBUG VIEW: raw attachment IDs = " . implode( ',', $raw_ids ) . " -->\n";

    // 3) Applichiamo il filtro secondo la lingua
    $filtered = array_filter( $doc['attachments'], function( $att ) use ( $current_lang ) {
        // in italiano solo 'italiano', altrimenti tutti tranne 'italiano'
        $slug = $att['lang']['slug'] ?? '';
        return $current_lang === 'it'
            ? ( $slug === 'italiano' )
            : ( $slug !== 'italiano' );
    } );

    // 4) Debug IDs dopo filtro
    $filtered_ids = wp_list_pluck( $filtered, 'id' );
    echo "<!-- DEBUG VIEW: filtered attachment IDs = " . implode( ',', $filtered_ids ) . " -->\n";

    $count = count( $filtered );
    if ( $count === 0 ) {
        echo "<!-- DEBUG VIEW: no attachments to show for doc_id={$doc['id']} -->\n";
        continue;
    }
    ?>
    <div class="doc-plus-item mb-5">
      <h3 class="doc-plus-title"><?php echo esc_html( $doc['title'] ); ?></h3>
      <?php if ( ! empty( $doc['cover_url'] ) ) : ?>
        <div class="doc-plus-cover mb-3 text-center">
          <img src="<?php echo esc_url( $doc['cover_url'] ); ?>"
               alt="<?php echo esc_attr( $doc['title'] ); ?>"
               class="img-fluid" />
        </div>
      <?php endif; ?>

      <?php if ( 1 === $count ) :
          $att = array_shift( $filtered ); ?>
          <div class="doc-plus-single-attachment text-center">
            <a href="<?php echo esc_url( $att['url'] ); ?>"
               target="_blank"
               class="btn btn-primary">
              <?php echo esc_html( $att['title'] ); ?>
              <?php echo $att['flag']; ?>
            </a>
          </div>
      <?php else : ?>
        <ul class="doc-plus-attachments list-unstyled row">
          <?php foreach ( $filtered as $att ) : ?>
            <li class="col-md-6 mb-3">
              <a href="<?php echo esc_url( $att['url'] ); ?>"
                 target="_blank"
                 class="d-block border p-3 h-100 text-center">
                <strong><?php echo esc_html( $att['title'] ); ?></strong><br/>
                <?php echo $att['flag']; ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>

    </div>
<?php
endforeach;
