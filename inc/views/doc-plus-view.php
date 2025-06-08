<?php
/**
 * View: doc-plus-view.php
 * Expects $doc_plus_data passed from the shortcode:
 *   array of [
 *     'id'         => post ID,
 *     'title'      => string,
 *     'cover_url'  => string,
 *     'attachments'=> [
 *       [
 *         'id'   => int,
 *         'title'=> string,
 *         'url'  => string,
 *         'lang' => ['slug'=>string,'name'=>string],
 *         'flag' => '<img … />'
 *       ],
 *       …
 *     ]
 *   ]
 */

// Get current language (WPML)
$current_lang = defined('ICL_LANGUAGE_CODE')
    ? ICL_LANGUAGE_CODE
    : apply_filters('wpml_current_language', null);

if ( empty($doc_plus_data) || ! is_array($doc_plus_data) ) {
    return; // nothing to render
}

// Loop all doc_plus items
foreach ( $doc_plus_data as $doc ) :

    // Filter attachments by language
    $filtered = array_filter( $doc['attachments'], function( $att ) use ( $current_lang ) {
        $slug = isset($att['lang']['slug']) ? $att['lang']['slug'] : '';
        if ( $current_lang === 'it' ) {
            // In Italian front-end: only show Italian attachments
            return $slug === 'italiano';
        }
        // In non-Italian front-end: show everything except Italian
        return $slug !== 'italiano';
    } );

    $count = count( $filtered );
    if ( 0 === $count ) {
        // Skip this doc entirely if no attachments pass the filter
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
          // Single attachment
          $att = array_shift( $filtered ); ?>
          <div class="doc-plus-single-attachment text-center">
            <a href="<?php echo esc_url( $att['url'] ); ?>"
               target="_blank"
               class="btn btn-primary">
              <?php echo esc_html( $att['title'] ); ?>
              <?php echo $att['flag']; ?>
            </a>
          </div>
      <?php else : 
          // Multiple attachments
      ?>
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
