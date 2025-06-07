<?php
/**
 * View per il rendering di prodotti e documenti (layout grid e card)
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
    // header termine
    $term_slug   = sanitize_title( $term['term_name'] );
    $term_obj    = get_term_by( 'slug', $term_slug, 'tipo_di_prodotto' );
    $term_link   = $term_obj ? get_term_link( $term_obj ) : '';
  ?>
  <h5 class="text-bg-dark text-center py-2 my-4 rounded-2">
    <?php if ( $term_link ): ?>
      <a href="<?= esc_url( $term_link ) ?>" class="term-link"><?= esc_html( $term['term_name'] ) ?></a>
    <?php else: ?>
      <?= esc_html( $term['term_name'] ) ?>
    <?php endif; ?>
  </h5>

  <?php if ( empty( $term['products'] ) ): ?>
    <p class="text-center"><?= esc_html__( 'Non ci sono prodotti con Schede o Documenti da visualizzare', 'toro-ag' ) ?></p>
    <?php continue; ?>
  <?php endif; ?>

  <?php if ( $layout === 'grid' ): ?>
    <!-- GRID LAYOUT INVARIATO -->
    <div class="row g-3 mb-5 documenti-download-grid">
      <?php foreach ( $term['products'] as $prod ): ?>
        <div class="col-md-3">
          <div class="card h-100 shadow-sm">
            <div class="card-header">
              <p class="card-title mb-0 fw-bold">
                <a href="<?= esc_url( get_permalink( $prod['ID'] ) ) ?>" class="prod-link"><?= esc_html( $prod['title'] ) ?></a>
              </p>
            </div>
            <div class="card-body small">
              <?php // schede
              if ( ! empty( $prod['schede'] ) ):
                $schede_by_lang = [];
                foreach ( $prod['schede'] as $item ) {
                  $schede_by_lang[ $item['lang'] ][] = $item;
                }
                foreach ( $schede_by_lang as $lang => $items ): ?>
                  <div class="gruppo-lingua gruppo-lingua-<?= esc_attr( $lang ) ?>">
                    <?php foreach ( $items as $it ): ?>
                      <a href="<?= esc_url( $it['url'] ) ?>" class="lang-<?= esc_attr( $lang ) ?>" target="_blank">
                        <span class="icone"><?= toroag_get_flag_html( $lang ) ?><i class="bi <?= esc_attr( toroag_get_icon_class( $it['url'] ) ) ?>"></i></span>
                        <span class="testo-link"><?= esc_html( $it['title'] ) ?></span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach;
              endif;

              <?php // documenti
              if ( ! empty( $prod['docs'] ) ):
                $docs_by_lang = [];
                foreach ( $prod['docs'] as $item ) {
                  $docs_by_lang[ $item['lang'] ][] = $item;
                }
                foreach ( $docs_by_lang as $lang => $items ): ?>
                  <div class="gruppo-lingua gruppo-lingua-<?= esc_attr( $lang ) ?> mt-2">
                    <?php foreach ( $items as $it ): ?>
                      <a href="<?= esc_url( $it['url'] ) ?>" class="lang-<?= esc_attr( $lang ) ?>" target="_blank">
                        <span class="icone"><?= toroag_get_flag_html( $lang ) ?><i class="bi <?= esc_attr( toroag_get_icon_class( $it['url'] ) ) ?>"></i></span>
                        <span class="testo-link"><?= esc_html( $it['title'] ) ?></span>
                      </a>
                    <?php endforeach; ?>
                  </div>
                <?php endforeach;
              endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php elseif ( $layout === 'card' ): ?>
    <!-- CARD LAYOUT: un li per prodotto, con gruppi lingua e schede/docs annidati -->
    <ul class="list-group mb-5 documenti-download-list">
      <?php foreach ( $term['products'] as $prod ): ?>
        <?php
          // prepara gruppi lingua
          $groups = [];
          foreach ( $prod['schede'] as $sc ) {
            $groups[ $sc['lang'] ]['schede'][] = $sc;
          }
          foreach ( $prod['docs'] as $dc ) {
            $groups[ $dc['lang'] ]['docs'][] = $dc;
          }
        ?>
        <li class="list-group-item">
          <!-- titolo prodotto -->
          <div class="fw-bold mb-2 prod-titolo">
            <a href="<?= esc_url( get_permalink( $prod['ID'] ) ) ?>" class="prod-link"><?= esc_html( $prod['title'] ) ?></a>
          </div>
          <!-- lista lingue -->
          <ul class="list-unstyled ps-3 mb-0">
            <?php foreach ( $groups as $lang => $data ): ?>
              <li class="gruppo-lingua-<?= esc_attr( $lang ) ?> mb-3">
                <!-- intestazione lingua -->
                <div class="d-flex align-items-center mb-1">
                  <?= toroag_get_flag_html( $lang ) ?>
                  <span class="ms-2 text-uppercase"><?= esc_html( $lang ) ?></span>
                </div>
                <!-- schede -->
                <?php if ( ! empty( $data['schede'] ) ): ?>
                  <div class="card-subtitle text-body-secondary mb-1"><?= esc_html__( 'Schede', 'toro-ag' ) ?>:</div>
                  <ul class="list-unstyled ps-4 mb-2">
                    <?php foreach ( $data['schede'] as $sc ): ?>
                      <li class="d-flex align-items-center mb-1">
                        <i class="bi <?= esc_attr( toroag_get_icon_class( $sc['url'] ) ) ?> me-2"></i>
                        <a href="<?= esc_url( $sc['url'] ) ?>" target="_blank"><?= esc_html( $sc['title'] ) ?></a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
                <!-- documenti -->
                <?php if ( ! empty( $data['docs'] ) ): ?>
                  <div class="card-subtitle text-body-secondary mb-1"><?= esc_html__( 'Documenti', 'toro-ag' ) ?>:</div>
                  <ul class="list-unstyled ps-4 mb-0">
                    <?php foreach ( $data['docs'] as $dc ): ?>
                      <li class="d-flex align-items-center mb-1">
                        <i class="bi <?= esc_attr( toroag_get_icon_class( $dc['url'] ) ) ?> me-2"></i>
                        <a href="<?= esc_url( $dc['url'] ) ?>" target="_blank"><?= esc_html( $dc['title'] ) ?></a>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
<?php endforeach; ?>
