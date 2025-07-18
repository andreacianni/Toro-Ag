<?php
/**
 * View per il rendering di prodotti e documenti (layout grid e card) con sidebar navigazione.
 * Riceve in input:
 *   - $terms_data: array di [ 'term_name', 'products' => [ ['ID','title','schede','docs'], … ] ]
 *   - $layout: 'grid' o 'card'
 *   - $lang: lingua corrente (passata dallo shortcode)
 *   - $lang_order: array slug=>priorità delle lingue
 */
?>

<?php if ( empty( $terms_data ) ) : ?>
  <p class="text-center"><?php echo esc_html__( 'Non ci sono prodotti con Schede o Documenti da visualizzare', 'toro-ag' ); ?></p>
  <?php return; ?>
<?php endif; ?>

<?php
// Recupera l'ordinamento lingue tramite helper
$lang_order = function_exists( 'toroag_get_language_order' )
    ? toroag_get_language_order()
    : [];

// Calcola conteggio linguaggio effettivo per documenti/schede
$all_langs = [];
foreach ( $terms_data as $term ) {
    foreach ( $term['products'] as $prod ) {
        foreach ( $prod['schede'] as $item ) {
            $all_langs[] = $item['lang'];
        }
        foreach ( $prod['docs'] as $item ) {
            $all_langs[] = $item['lang'];
        }
    }
}
// Conteggio per lingua
$lang_counts = array_count_values( $all_langs );
// Filtra e ordina secondo lang_order, solo lingue con conteggi
$filter_langs = array_intersect_key( $lang_order, $lang_counts );
asort( $filter_langs );
?>

<!-- Layout a due colonne: sidebar + contenuto -->
<div class="row">
  <!-- SIDEBAR SINISTRA -->
  <div class="col-lg-3 d-none d-lg-block">
    <div class="documenti-sidebar position-sticky" style="top: 2rem;">
      
      <?php if ( $lang !== 'it' ) : // Filtro lingue solo per inglese in desktop ?>
        <div class="documenti-filter mb-4">
          <h6 class="fw-bold mb-3"><?php esc_html_e( 'Choose language:', 'toro-ag' ); ?></h6>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ( $filter_langs as $lang_slug => $prio ) : ?>
              <button type="button"
                      data-lang="<?php echo esc_attr( $lang_slug ); ?>"
                      class="filter-flag btn btn-sm btn-outline-secondary"
                      title="<?php echo esc_attr( toroag_get_language_label( $lang_slug ) . ' (' . ( $lang_counts[ $lang_slug ] ?? 0 ) . ')' ); ?>">
                <?php echo toroag_get_flag_html( $lang_slug ); ?>
              </button>
            <?php endforeach; ?>
            <button type="button"
                    data-lang=""
                    class="filter-flag btn btn-sm btn-outline-secondary"
                    title="<?php esc_attr_e( 'All languages', 'toro-ag' ); ?>">
              <i class="bi bi-globe2"></i>
            </button>
          </div>
        </div>
      <?php endif; ?>

      <!-- Menu navigazione sezioni -->
      <nav class="documenti-nav">
        <h6 class="fw-bold mb-3"><?php esc_html_e( 'Sections', 'toro-ag' ); ?></h6>
        <ul class="list-unstyled">
          <?php foreach ( $terms_data as $term ) : ?>
            <?php $section_id = 'section-' . sanitize_title( $term['term_name'] ); ?>
            <li class="mb-2">
              <a href="#<?php echo esc_attr( $section_id ); ?>" 
                 class="nav-link-section text-decoration-none d-block py-1 px-2 rounded">
                <?php echo esc_html( $term['term_name'] ); ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </div>

  <!-- CONTENUTO PRINCIPALE -->
  <div class="col-lg-9">
    
    <?php if ( $lang !== 'it' ) : // Filtro mobile/tablet solo per inglese ?>
      <div class="d-lg-none documenti-filter-mobile mb-4">
        <h6 class="fw-bold mb-3"><?php esc_html_e( 'Choose language:', 'toro-ag' ); ?></h6>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ( $filter_langs as $lang_slug => $prio ) : ?>
            <button type="button"
                    data-lang="<?php echo esc_attr( $lang_slug ); ?>"
                    class="filter-flag btn btn-sm btn-outline-secondary"
                    title="<?php echo esc_attr( toroag_get_language_label( $lang_slug ) . ' (' . ( $lang_counts[ $lang_slug ] ?? 0 ) . ')' ); ?>">
              <?php echo toroag_get_flag_html( $lang_slug ); ?>
            </button>
          <?php endforeach; ?>
          <button type="button"
                  data-lang=""
                  class="filter-flag btn btn-sm btn-outline-secondary"
                  title="<?php esc_attr_e( 'All languages', 'toro-ag' ); ?>">
            <i class="bi bi-globe2"></i>
          </button>
        </div>
      </div>
    <?php endif; ?>

    <?php foreach ( $terms_data as $term ) : ?>
      <?php
        // Header termine con ID per l'ancora
        $section_id = 'section-' . sanitize_title( $term['term_name'] );
        $slug   = sanitize_title( $term['term_name'] );
        $t_obj  = get_term_by( 'slug', $slug, 'tipo_di_prodotto' );
        $t_link = $t_obj ? get_term_link( $t_obj ) : '';
      ?>

      <section id="<?php echo esc_attr( $section_id ); ?>" class="documenti-section">
        <h5 class="text-bg-dark text-center py-2 my-4 rounded-2">
          <?php if ( $t_link ) : ?>
            <a href="<?php echo esc_url( $t_link ); ?>" class="term-link text-white text-decoration-none"><?php echo esc_html( $term['term_name'] ); ?></a>
          <?php else : ?>
            <?php echo esc_html( $term['term_name'] ); ?>
          <?php endif; ?>
        </h5>

        <?php if ( empty( $term['products'] ) ) : ?>
          <p class="text-center"><?php echo esc_html__( 'Non ci sono prodotti con Schede o Documenti da visualizzare', 'toro-ag' ); ?></p>
          <?php continue; ?>
        <?php endif; ?>

        <?php if ( $layout === 'card' ) : ?>
          <!-- CARD -->
          <div class="row g-3 mb-5 documenti-download-grid">
            <?php foreach ( $term['products'] as $prod ) : ?>
              <div class="col-12 col-md-6 col-lg-6 col-xl-4 product-item">
                <div class="card h-100 shadow-sm">
                  <div class="card-header">
                    <p class="card-title mb-0 fw-bold text-start">
                      <a href="<?php echo esc_url( get_permalink( $prod['ID'] ) ); ?>" class="prod-link"><?php echo esc_html( $prod['title'] ); ?></a>
                    </p>
                  </div>
                  <div class="card-body small">
                    <?php if ( ! empty( $prod['schede'] ) ) : ?>
                      <div class="schede card-subtitle text-body-secondary">
                        <?php echo esc_html__( 'Schede', 'toro-ag' ); ?>:
                      </div>
                      <?php
                        $schede_by_lang = [];
                        foreach ( $prod['schede'] as $item ) {
                            $schede_by_lang[ $item['lang'] ][] = $item;
                        }
                      ?>
                      <?php foreach ( $schede_by_lang as $lang_slug => $items ) : ?>
                        <div class="gruppo-lingua gruppo-lingua-<?php echo esc_attr( $lang_slug ); ?>">
                          <?php foreach ( $items as $item ) : ?>
                            <a href="<?php echo esc_url( $item['url'] ); ?>" class="lang-<?php echo esc_attr( $lang_slug ); ?>" target="_blank">
                              <span class="icone">
                                <?php echo toroag_get_flag_html( $lang_slug ); ?>
                                <i class="bi <?php echo esc_attr( toroag_get_icon_class( $item['url'] ) ); ?>"></i>
                              </span>
                              <span class="testo-link"><?php echo esc_html( $item['title'] ); ?></span>
                            </a>
                          <?php endforeach; ?>
                        </div>
                      <?php endforeach; ?>
                    <?php endif; ?>

                    <?php if ( ! empty( $prod['docs'] ) ) : ?>
                      <div class="documenti card-subtitle text-body-secondary mt-2">
                        <?php echo esc_html__( 'Documenti', 'toro-ag' ); ?>:
                      </div>
                      <?php
                        $docs_by_lang = [];
                        foreach ( $prod['docs'] as $item ) {
                            $docs_by_lang[ $item['lang'] ][] = $item;
                        }
                      ?>
                      <?php foreach ( $docs_by_lang as $lang_slug => $items ) : ?>
                        <div class="gruppo-lingua gruppo-lingua-<?php echo esc_attr( $lang_slug ); ?>">
                          <?php foreach ( $items as $item ) : ?>
                            <a href="<?php echo esc_url( $item['url'] ); ?>" class="lang-<?php echo esc_attr( $lang_slug ); ?>" target="_blank">
                              <span class="icone">
                                <?php echo toroag_get_flag_html( $lang_slug ); ?>
                                <i class="bi <?php echo esc_attr( toroag_get_icon_class( $item['url'] ) ); ?>"></i>
                              </span>
                              <span class="testo-link"><?php echo esc_html( $item['title'] ); ?></span>
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

        <?php elseif ( $layout === 'grid' ) : ?>
          <!-- GRID griglia: due colonne, prodotto/documenti -->
          <ul class="list-group mb-5 documenti-download-list ps-0">
            <li class="list-group-item list-group-item-dark d-flex fw-bold">
              <div class="col-3 ps-0 text-start"><?php echo esc_html__( 'Prodotti', 'toro-ag' ); ?></div>
              <div class="col-9 ps-0 d-flex">
                <div class="col-1 ps-0"><?php echo esc_html__( 'Lingua', 'toro-ag' ); ?></div>
                <div class="col-5"><?php echo esc_html__( 'Schede', 'toro-ag' ); ?></div>
                <div class="col-5 pe-0"><?php echo esc_html__( 'Documenti', 'toro-ag' ); ?></div>
              </div>
            </li>
            <?php $index = 0; ?>
            <?php foreach ( $term['products'] as $prod ) : ?>
              <?php
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
              <?php foreach ( $groups as $lang_slug => $data ) : $index++; ?>
                <?php if ( $index === 1 ) : ?>
                  <li class="list-group-item d-flex">
                    <div class="col-3 ps-0 d-flex align-items-center justify-content-start fw-bold" rowspan="<?php echo esc_attr( $total ); ?>">
                      <a href="<?php echo esc_url( get_permalink( $prod['ID'] ) ); ?>" class="prod-link text-start"><?php echo esc_html( $prod['title'] ); ?></a>
                    </div>
                    <div class="col-9 ps-0">
                <?php endif; ?>
                      <div class="row<?php echo ( $index < $total ) ? ' border-bottom' : ''; ?> mb-0 pb-0 gruppo-lingua-<?php echo esc_attr( $lang_slug ); ?> align-items-center">
                        <div class="col-1 d-flex align-items-center justify-content-center">
                          <?php echo toroag_get_flag_html( $lang_slug ); ?>
                        </div>
                        <div class="col-5 small d-flex align-items-center">
                          <?php if ( ! empty( $data['schede'] ) ) : ?>
                            <ul class="list-unstyled mb-0 ps-2 pb-0">
                              <?php foreach ( $data['schede'] as $s_item ) : ?>
                                <li class="d-flex align-items-center mb-0">
                                  <a href="<?php echo esc_url( $s_item['url'] ); ?>" target="_blank" class="d-flex align-items-center">
                                    <i class="bi <?php echo esc_attr( toroag_get_icon_class( $s_item['url'] ) ); ?> me-2"></i>
                                    <span><?php echo esc_html( $s_item['title'] ); ?></span>
                                  </a>
                                </li>
                              <?php endforeach; ?>
                            </ul>
                          <?php endif; ?>
                        </div>
                        <div class="col-5 small d-flex align-items-center">
                          <?php if ( ! empty( $data['docs'] ) ) : ?>
                            <ul class="list-unstyled mb-0 ps-2 pb-0">
                              <?php foreach ( $data['docs'] as $d_item ) : ?>
                                <li class="d-flex align-items-center mb-0">
                                  <a href="<?php echo esc_url( $d_item['url'] ); ?>" target="_blank" class="d-flex align-items-center">
                                    <i class="bi <?php echo esc_attr( toroag_get_icon_class( $d_item['url'] ) ); ?> me-2"></i>
                                    <span><?php echo esc_html( $d_item['title'] ); ?></span>
                                  </a>
                                </li>
                              <?php endforeach; ?>
                            </ul>
                          <?php endif; ?>
                        </div>
                      </div>
                <?php if ( $index === $total ) : ?>
                    </div>
                  </li>
                <?php endif; ?>
              <?php endforeach; ?>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </section>

    <?php endforeach; ?>
  </div>
</div>

<!-- CSS e JavaScript per la sidebar -->
<style>
.documenti-sidebar {
  max-height: calc(100vh - 4rem);
  overflow-y: auto;
}

.nav-link-section {
  color: #6c757d;
  transition: all 0.2s ease;
}

.nav-link-section:hover,
.nav-link-section.active {
  color: #495057;
  background-color: #f8f9fa;
  transform: translateX(5px);
}

.documenti-section {
  scroll-margin-top: 2rem;
}

/* Responsive: cards layout per sidebar */
@media (min-width: 992px) {
  .documenti-download-grid .col-xl-4 {
    flex: 0 0 50% !important;
    max-width: 50% !important;
  }
}

@media (min-width: 1200px) {
  .documenti-download-grid .col-xl-4 {
    flex: 0 0 33.333333% !important;
    max-width: 33.333333% !important;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Smooth scroll per i link della sidebar
  const navLinks = document.querySelectorAll('.nav-link-section');
  
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('href').substring(1);
      const targetElement = document.getElementById(targetId);
      
      if (targetElement) {
        targetElement.scrollIntoView({
          behavior: 'smooth',
          block: 'start'
        });
        
        // Aggiorna stato attivo
        navLinks.forEach(l => l.classList.remove('active'));
        this.classList.add('active');
      }
    });
  });
  
  // Evidenzia sezione attiva durante lo scroll
  const sections = document.querySelectorAll('.documenti-section');
  const observerOptions = {
    root: null,
    rootMargin: '-20% 0px -70% 0px',
    threshold: 0
  };
  
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const id = entry.target.id;
        navLinks.forEach(link => {
          link.classList.remove('active');
          if (link.getAttribute('href') === '#' + id) {
            link.classList.add('active');
          }
        });
      }
    });
  }, observerOptions);
  
  sections.forEach(section => observer.observe(section));
});
</script>