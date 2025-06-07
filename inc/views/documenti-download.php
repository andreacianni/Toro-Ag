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
    $slug = sanitize_title( $term['term_name'] );
    $t_obj = get_term_by( 'slug', $slug, 'tipo_di_prodotto' );
    $t_link = $t_obj ? get_term_link( $t_obj ) : '';
  ?>
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

  <?php if ( $layout === 'grid' ): ?>
    <!-- GRID invariato -->
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
              <?php // SCHEDE & DOCUMENTI per lingua come prima ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  <?php elseif ( $layout === 'card' ): ?>
    <!-- CARD: due colonne, prima col prodotto verticalmente centrato, seconda col nested per lingua -->
    <ul class="list-group mb-5 documenti-download-list">
      <li class="list-group-item list-group-item-dark d-flex fw-bold">
        <div class="col-3 ps-0 text-center"><?= esc_html__( 'Prodotti', 'toro-ag' ) ?></div>
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
          <div class="col-3 ps-0 d-flex align-items-center justify-content-center">
            <a href="<?= esc_url( get_permalink( $prod['ID'] ) ) ?>" class="prod-link text-center"><?= esc_html( $prod['title'] ) ?></a>
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
                    <ul class="list-unstyled mb-0 ps-2">
                      <?php foreach ( $data['schede'] as $s_item ): ?>
                        <li class="d-flex align-items-center mb-1">
                          <i class="bi <?= esc_attr( toroag_get_icon_class( $s_item['url'] ) ) ?> me-2"></i>
                          <a href="<?= esc_url( $s_item['url'] ) ?>" target="_blank"><?= esc_html( $s_item['title'] ) ?></a>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  <?php endif; ?>
                </div>
                <div class="col-5 small d-flex align-items-center">
                  <?php if ( ! empty( $data['docs'] ) ): ?>
                    <ul class="list-unstyled mb-0 ps-2">
                      <?php foreach ( $data['docs'] as $d_item ): ?>
                        <li class="d-flex align-items-center mb-1">
                          <i class="bi <?= esc_attr( toroag_get_icon_class( $d_item['url'] ) ) ?> me-2"></i>
                          <a href="<?= esc_url( $d_item['url'] ) ?>" target="_blank"><?= esc_html( $d_item['title'] ) ?></a>
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

