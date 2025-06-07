<?php
/**
 * View per il rendering di prodotti e documenti (layout grid).
 * Riceve in input:
 *   - $terms_data: array di [ 'term_name', 'products' => [ ['title','schede','docs'], … ] ]
 *   - $layout: 'grid' o 'table'
 */
?>

<?php if (empty($terms_data)): ?>
  <p class="text-center"><?= esc_html__('Non ci sono prodotti con Schede o Documenti da visualizzare','toro-ag') ?></p>
  <?php return; ?>
<?php endif; ?>
<?php
/* per dev: mostra solo primi 2 termini
$terms_data = array_slice( $terms_data, 0, 2 );
*/
?>
<?php foreach ( $terms_data as $term ): ?>
  <?php
    // Recupera l'oggetto termine e il suo link
    $term_slug_obj = sanitize_title( $term['term_name'] );
    $term_obj      = get_term_by( 'slug',  $term_slug_obj, 'tipo_di_prodotto' );
    $term_link     = $term_obj ? get_term_link( $term_obj ) : '';
  ?>
  <h5 class="text-bg-dark text-center py-2 my-4 rounded-2">
    <?php if ( $term_link ): ?>
      <a href="<?= esc_url( $term_link ) ?>" class="term-link">
        <?= esc_html( $term['term_name'] ) ?>
      </a>
    <?php else: ?>
      <?= esc_html( $term['term_name'] ) ?>
    <?php endif; ?>
  </h5>
  <?php if ( empty( $term['products'] ) ): ?>
    <p class="text-center"><?= esc_html__( 'Non ci sono prodotti con Schede o Documenti da visualizzare', 'toro-ag' ) ?></p>
    <?php continue; ?>
  <?php endif; ?>

  <?php if ($layout==='grid'): ?>
    <div class="row g-3 mb-5 documenti-download-grid">
      <?php foreach($term['products'] as $prod): ?>
        <div class="col-md-3">
          <div class="card h-100 shadow-sm">
            <div class="card-header">
              <p class="card-title mb-0 fw-bold">
                <a href="<?= esc_url( get_permalink( $prod['ID'] ) ); ?>" class="prod-link">
                  <?php
                  // echo "\n<!-- DEBUG view: permalink via ID {$prod['ID']} = " . esc_url( get_permalink( $prod['ID'] ) ) . " -->\n";
                  ?>
                  <?= esc_html($prod['title']) ?>
                </a>
              </p>
            </div>
            <div class="card-body small">

              <?php // SCHEDE
              if (! empty( $prod['schede'] ) ) : ?>
                <div class="schede card-subtitle text-body-secondary">
                  <?= esc_html__( 'Schede', 'toro-ag' ) ?>:
                </div>
                  <?php
                  // raggruppa per lingua
                  $schede_by_lang = [];
                  foreach ( $prod['schede'] as $item ) {
                      $schede_by_lang[ $item['lang'] ][] = $item;
                  }
                  foreach ( $schede_by_lang as $lang_slug => $items ) : ?>
                    <div class="gruppo-lingua gruppo-lingua-<?= esc_attr( $lang_slug ) ?>">
                      <?php foreach ( $items as $item ) : ?>
                        <a href="<?= esc_url( $item['url'] ) ?>"
                           class="lang-<?= esc_attr( $lang_slug ) ?>"
                           target="_blank">
                          <span class="icone">
                            <?= toroag_get_flag_html( $lang_slug ) ?>
                            <i class="bi <?= esc_attr( toroag_get_icon_class( $item['url'] ) ) ?>"></i>
                          </span>
                          <span class="testo-link">
                            <?= esc_html( $item['title'] ) ?>
                          </span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php endforeach; ?>
                
              <?php endif; ?>

              <?php // DOCUMENTI
              if (! empty( $prod['docs'] ) ) : ?>
                <div class="documenti card-subtitle text-body-secondary mt-2">
                  <?= esc_html__( 'Documenti', 'toro-ag' ) ?>:
                </div>
                  <?php
                  // raggruppa per lingua
                  $docs_by_lang = [];
                  foreach ( $prod['docs'] as $item ) {
                      $docs_by_lang[ $item['lang'] ][] = $item;
                  }
                  foreach ( $docs_by_lang as $lang_slug => $items ) : ?>
                    <div class="gruppo-lingua gruppo-lingua-<?= esc_attr( $lang_slug ) ?>">
                      <?php foreach ( $items as $item ) : ?>
                        <a href="<?= esc_url( $item['url'] ) ?>"
                           class="lang-<?= esc_attr( $lang_slug ) ?>"
                           target="_blank">
                          <span class="icone">
                            <?= toroag_get_flag_html( $lang_slug ) ?>
                            <i class="bi <?= esc_attr( toroag_get_icon_class( $item['url'] ) ) ?>"></i>
                          </span>
                          <span class="testo-link">
                            <?= esc_html( $item['title'] ) ?>
                          </span>
                        </a>
                      <?php endforeach; ?>
                    </div>
                  <?php endforeach; ?>
                
              <?php endif; ?>

            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

<?php elseif ( $layout === 'card' ): ?>
<ul class="list-group mb-5 documenti-download-list">
  <li class="list-group-item list-group-item-dark d-flex fw-bold">
    <div class="col-3 ps-0"><?= esc_html__( 'Prodotti', 'toro-ag' ) ?></div>
    <div class="col-1"><?= esc_html__( 'Lingua', 'toro-ag' ) ?></div>
    <div class="col-4"><?= esc_html__( 'Schede', 'toro-ag' ) ?></div>
    <div class="col-4 pe-0"><?= esc_html__( 'Documenti', 'toro-ag' ) ?></div>
  </li>
  <?php foreach ( $term['products'] as $prod ): ?>
    <?php
      $groups = [];
      foreach ( $prod['schede'] as $item ) {
        $groups[ $item['lang'] ]['schede'][] = $item;
      }
      foreach ( $prod['docs'] as $item ) {
        $groups[ $item['lang'] ]['docs'][] = $item;
      }
    ?>
    <?php foreach ( $groups as $lang_slug => $data ): ?>
      <li class="list-group-item d-flex align-items-center gruppo-lingua-<?= esc_attr( $lang_slug ) ?>">
        <div class="col-3 ps-0">
          <a href="<?= esc_url( get_permalink( $prod['ID'] ) ) ?>" class="prod-link d-block">
            <?= esc_html( $prod['title'] ) ?>
          </a>
        </div>
        <div class="col-1 d-flex align-items-center">
          <a href="<?= esc_url( get_permalink( $prod['ID'] ) ) ?>#" class="d-inline-block">
            <?= toroag_get_flag_html( $lang_slug ) ?>
          </a>
        </div>
        <div class="col-4">
          <ul class="list-unstyled mb-0 ps-0">
            <?php foreach ( $data['schede'] ?? [] as $sc ): ?>
              <li class="d-flex align-items-center mb-1">
                <a href="<?= esc_url( $sc['url'] ) ?>" target="_blank" class="d-flex align-items-center">
                  <i class="bi <?= esc_attr( toroag_get_icon_class( $sc['url'] ) ) ?> me-2"></i>
                  <span><?= esc_html( $sc['title'] ) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <div class="col-4 pe-0">
          <ul class="list-unstyled mb-0 ps-0">
            <?php foreach ( $data['docs'] ?? [] as $doc ): ?>
              <li class="d-flex align-items-center mb-1">
                <a href="<?= esc_url( $doc['url'] ) ?>" target="_blank" class="d-flex align-items-center">
                  <i class="bi <?= esc_attr( toroag_get_icon_class( $doc['url'] ) ) ?> me-2"></i>
                  <span><?= esc_html( $doc['title'] ) ?></span>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </li>
    <?php endforeach; ?>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<?php endforeach; ?>