<?php
/**
 * View per il rendering di prodotti e documenti (layout grid e card).
 * Riceve in input:
 *   - $terms_data: array di [ 'term_name', 'products' => [ ['ID','title','schede','docs'], … ] ]
 *   - $layout: 'grid' o 'card'
 */
?>

<?php if ( empty( $terms_data ) ): ?>
  <p class="text-center"><?= esc_html__( 'Non ci sono prodotti con Schede o Documenti da visualizzare', 'toro-ag' ) ?></p>
  <?php return; ?>
<?php endif; ?>

<?php foreach ( $terms_data as $term ): ?>
  <?php
    // Header termine
    $slug   = sanitize_title( $term['term_name'] );
    $t_obj  = get_term_by( 'slug', $slug, 'tipo_di_prodotto' );
    $t_link = $t_obj ? get_term_link( $t_obj ) : '';
  ?>
  <?php if ( $lang !== 'it' ): ?>
  <div class="documenti-filter d-none d-md-flex align-items-center mb-3">
    <button type="button"
            data-lang=""
            class="filter-flag btn btn-sm btn-outline-secondary active"
            title="<?php esc_attr_e( 'Mostra tutte le lingue', 'toro-ag' ); ?>">
      <i class="bi bi-globe2"></i>
    </button>
    <?php foreach ( $lang_order as $lang_slug => $prio ): ?>
      <button type="button"
              data-lang="<?php echo esc_attr( $lang_slug ); ?>"
              class="filter-flag btn btn-sm btn-outline-secondary ms-1"
              title="<?php echo esc_attr( ucwords( $lang_slug ) ); ?>">
        <?php echo toroag_get_flag_html( $lang_slug ); ?>
      </button>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

  <h5 class="text-bg-dark text-center py-2 my-4 rounded-2">
    <?php if ( $t_link ): ?>
      <a href="<?= esc_url( $t_link ) ?>" class="term-link"><?= esc_html( $term['term_name'] ) ?></a>
    <?php else: ?>
      <?= esc_html( $term['term_name'] ) ?>
    <?php endif; ?>
  </h5>

  <?php if ( empty( $term['products'] ) ): ?>
    <p class="text-center"><?= esc_html__( 'Non ci sono prodotti con Schede o Documenti da visualizzare', 'toro-ag' ) ?></p>
    <?php continue; ?>
  <?php endif; ?>

  <?php if ( $layout === 'card' ): ?>
    <!-- CARD -->
    <div class="row g-3 mb-5 documenti-download-grid">
      <?php foreach ( $term['products'] as $prod ): ?>
        <div class="col-12 col-md-6 col-lg-4 col-xl-3">
          <div class="card h-100 shadow-sm">
            <div class="card-header">
              <p class="card-title mb-0 fw-bold text-start">
                <a href="<?= esc_url( get_permalink( $prod['ID'] ) ) ?>" class="prod-link"><?= esc_html( $prod['title'] ) ?></a>
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

  <?php elseif ( $layout === 'grid' ): ?>
    <!-- GRID griglia: due colonne, prima col prodotto verticalmente centrato, seconda col nested per lingua -->
    <ul class="list-group mb-5 documenti-download-list ps-0">
      <li class="list-group-item list-group-item-dark d-flex fw-bold">
        <div class="col-3 ps-0 text-start"><?= esc_html__( 'Prodotti', 'toro-ag' ) ?></div>
        <div class="col-9 ps-0 d-flex">
          <div class="col-1 ps-0"><?= esc_html__( 'Lingua', 'toro-ag' ) ?></div>
          <div class="col-5"><?= esc_html__( 'Schede', 'toro-ag' ) ?></div>
          <div class="col-5 pe-0"><?= esc_html__( 'Documenti', 'toro-ag' ) ?></div>
        </div>
      </li>
      <?php foreach ( $term['products'] as $prod ): ?>
        <?php
          // raggruppa schede e docs per lingua
          $groups = [];
          foreach ( $prod['schede'] as $s ) {
            $groups[ $s['lang'] ]['schede'][] = $s;
          }
          foreach ( $prod['docs'] as $d ) {
            $groups[ $d['lang'] ]['docs'][] = $d;
          }
          $total = count( $groups );
          $index = 0;
        ?>
        <li class="list-group-item d-flex">
          <!-- Prima colonna prodotto -->
          <div class="col-3 ps-0 d-flex align-items-center justify-content-start fw-bold">
            <a href="<?= esc_url( get_permalink( $prod['ID'] ) ) ?>" class="prod-link text-start"><?= esc_html( $prod['title'] ) ?></a>
          </div>
          <!-- Seconda colonna: lingua, schede, documenti -->
          <div class="col-9 ps-0">
            <?php foreach ( $groups as $lang => $data ): ?>
              <?php $index++; ?>
              <div class="row<?php if ( $index < $total ) echo ' border-bottom'; ?> mb-0 pb-0 grupo-lingua-<?= esc_attr( $lang ) ?> align-items-center">
                <div class="col-1 d-flex align-items-center justify-content-center">
                  <?= toroag_get_flag_html( $lang ) ?>
                </div>
                <div class="col-5 small d-flex align-items-center">
                  <?php if ( ! empty( $data['schede'] ) ): ?>
                    <ul class="list-unstyled mb-0 ps-2 pb-0">
                      <?php foreach ( $data['schede'] as $s_item ): ?>
                        <li class="d-flex align-items-center mb-0">
                          <a href="<?= esc_url( $s_item['url'] ) ?>" target="_blank" class="d-flex align-items-center">
                            <i class="bi <?= esc_attr( toroag_get_icon_class( $s_item['url'] ) ) ?> me-2"></i>
                            <span><?= esc_html( $s_item['title'] ) ?></span>
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
                <div class="col-5 small d-flex align-items-center">
                  <?php if ( ! empty( $data['docs'] ) ): ?>
                    <ul class="list-unstyled mb-0 ps-2 pb-0">
                      <?php foreach ( $data['docs'] as $d_item ): ?>
                        <li class="d-flex align-items-center mb-0">
                          <a href="<?= esc_url( $d_item['url'] ) ?>" target="_blank" class="d-flex align-items-center">
                            <i class="bi <?= esc_attr( toroag_get_icon_class( $d_item['url'] ) ) ?> me-2"></i>
                            <span><?= esc_html( $d_item['title'] ) ?></span>
                          </a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
<?php endforeach; ?>
