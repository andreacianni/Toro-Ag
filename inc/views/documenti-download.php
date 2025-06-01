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
    <div class="row g-3 mb-5">
      <?php foreach($term['products'] as $prod): ?>
        <div class="col-md-3">
          <div class="card h-100 shadow-sm">
            <div class="card-header">
              <p class="card-title mb-0">
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

  <?php else: /* table layout */ ?>

    <table class="table mb-5 documenti-download-table small">
      <thead>
        <tr>
          <th class="prodotti align-middle"><?= esc_html__('Prodotti','toro-ag') ?></th>
          <th class="lingua align-middle"><?= esc_html__('Lingua','toro-ag') ?></th>
          <th class="schede align-middle"><?= esc_html__('Schede','toro-ag') ?></th>
          <th class="documenti align-middle"><?= esc_html__('Documenti','toro-ag') ?></th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($term['products'] as $prod): ?>
        <?php
        // Raccogli items per lingua
        $groups = [];
        foreach ($prod['schede'] as $item) {
            $groups[$item['lang']]['schede'][] = $item;
        }
        foreach ($prod['docs'] as $item) {
            $groups[$item['lang']]['docs'][] = $item;
        }
        // Calcolo totale righe per il prodotto
        $totalRows = 0;
        foreach ($groups as $lang_slug => $data) {
            $docsCount = count($data['docs'] ?? []);
            $totalRows += max(1, $docsCount);
        }
        $rowIndex = 0;
        // Rendering per ciascun gruppo linguistico
        foreach ($groups as $lang_slug => $data) {
            $schede = $data['schede'] ?? [];
            $docs   = $data['docs'] ?? [];
            $rowCount = max(1, count($docs));
            // Preparo HTML schede
            ob_start();
            foreach ($schede as $sc) {
                ?><div><a href="<?= esc_url($sc['url']) ?>" target="_blank"><i class="bi <?= esc_attr( toroag_get_icon_class( $item['url'] ) ) ?> me-1"></i><?= esc_html($sc['title']) ?></a></div><?php
            }
            $schede_html = ob_get_clean();
            for ($i = 0; $i < $rowCount; $i++):
        ?>
          <tr>
            <?php if ($rowIndex === 0): ?>
              <td rowspan="<?= $totalRows ?>" class="prodotti align-middle">
                <a href="<?= esc_url( get_permalink( $prod['ID'] ) ); ?>" class="prod-link">
                  <?= esc_html($prod['title']) ?>
                </a>
              </td>
            <?php endif; ?>
            <?php if ($i === 0): ?>
              <td rowspan="<?= $rowCount ?>" class="lingua align-middle lingua-<?= esc_attr($lang_slug) ?>">
                <?= toroag_get_flag_html($lang_slug) ?>
              </td>
              <td rowspan="<?= $rowCount ?>" class="schede align-middle">
                <?= $schede_html ?>
              </td>
            <?php endif; ?>
            <td class="documenti align-middle">
              <?php if (! empty($docs[$i])): ?>
                <a href="<?= esc_url($docs[$i]['url']) ?>" target="_blank"><i class="bi <?= esc_attr( toroag_get_icon_class( $item['url'] ) ) ?> me-1"></i><?= esc_html($docs[$i]['title']) ?></a>
              <?php endif; ?>
            </td>
          </tr>
        <?php
            $rowIndex++;
            endfor;
        }
        ?>
      <?php endforeach; ?>
      </tbody>
    </table>

  <?php endif; ?>

<?php endforeach; ?>