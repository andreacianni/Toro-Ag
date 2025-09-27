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
    <div class="documenti-sidebar position-sticky" style="top: 140px;">
      
      <?php if ( $lang !== 'it' ) : // Filtro lingue solo per inglese in desktop ?>
        <div class="documenti-filter mb-3">
          <h6 class="fw-bold mb-2 small"><?php esc_html_e( 'Choose language:', 'toro-ag' ); ?></h6>
          <div class="d-flex flex-wrap gap-1">
            <?php foreach ( $filter_langs as $lang_slug => $prio ) : ?>
              <button type="button"
                      data-lang="<?php echo esc_attr( $lang_slug ); ?>"
                      class="filter-flag btn btn-sm btn-outline-secondary p-1"
                      title="<?php echo esc_attr( toroag_get_language_label( $lang_slug ) . ' (' . ( $lang_counts[ $lang_slug ] ?? 0 ) . ')' ); ?>">
                <?php echo toroag_get_flag_html( $lang_slug ); ?>
              </button>
            <?php endforeach; ?>
            <button type="button"
                    data-lang=""
                    class="filter-flag btn btn-sm btn-outline-secondary p-1"
                    title="<?php esc_attr_e( 'All languages', 'toro-ag' ); ?>">
              <i class="bi bi-globe2"></i>
            </button>
          </div>
        </div>
      <?php endif; ?>

      <!-- Menu navigazione sezioni -->
      <nav class="documenti-nav">
        <h6 class="fw-bold mb-2 small"><?php esc_html_e( 'Navigation', 'toro-ag' ); ?></h6>
        <div class="nav-sections">
          <?php foreach ( $terms_data as $term ) : ?>
            <?php $section_id = 'section-' . sanitize_title( $term['term_name'] ); ?>
            <div class="nav-section-item mb-1">
              <a href="#<?php echo esc_attr( $section_id ); ?>" 
                 class="nav-link-section text-decoration-none d-block py-1 px-2 rounded small">
                <?php echo esc_html( $term['term_name'] ); ?>
              </a>
            </div>
          <?php endforeach; ?>
        </div>
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
        <h4 class="text-bg-white text-start ps-2 py-2 my-4 border-bottom">
          <?php if ( $t_link ) : ?>
            <a href="<?php echo esc_url( $t_link ); ?>" class="text-decoration-none"><?php echo esc_html( $term['term_name'] ); ?></a>
          <?php else : ?>
            <?php echo esc_html( $term['term_name'] ); ?>
          <?php endif; ?>
        </h4>

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

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Smooth scroll per i link della sidebar
  const navLinks = document.querySelectorAll('.nav-link-section');
  
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      // Non prevenire default - lascia che toro-layout-manager.js gestisca lo scroll

      // Aggiorna solo stato attivo
      navLinks.forEach(l => l.classList.remove('active'));
      this.classList.add('active');
    });
  });
  
  // Evidenzia sezione attiva durante lo scroll
  const sections = document.querySelectorAll('.documenti-section');
  const observerOptions = {
    root: null,
    rootMargin: '-140px 0px -70% 0px', // Aggiustato per menu sticky 120px + margin
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
  
  // Gestione filtro lingue
  const filterButtons = document.querySelectorAll('.filter-flag');
  const navigationItems = document.querySelectorAll('.nav-section-item');
  
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      const selectedLang = this.getAttribute('data-lang');
      
      // Aggiorna stato attivo bottoni
      filterButtons.forEach(btn => btn.classList.remove('active'));
      this.classList.add('active');
      
      // Filtra le voci della navigazione
      filterNavigationByLanguage(selectedLang);
      
      // Filtra anche il contenuto (se esiste già questa funzionalità)
      if (typeof filterDocumentsByLanguage === 'function') {
        filterDocumentsByLanguage(selectedLang);
      }
    });
  });
  
  function filterNavigationByLanguage(selectedLang) {
    navigationItems.forEach(navItem => {
      const link = navItem.querySelector('.nav-link-section');
      const sectionId = link.getAttribute('href').substring(1);
      const section = document.getElementById(sectionId);
      
      if (!section) {
        navItem.style.display = 'none';
        return;
      }
      
      // Se nessuna lingua selezionata o "tutte le lingue" (data-lang="")
      if (!selectedLang || selectedLang === '') {
        navItem.style.display = 'block';
        return;
      }
      
      // Controlla se la sezione ha documenti nella lingua selezionata
      const hasLanguageContent = section.querySelector(`.gruppo-lingua-${selectedLang}`) !== null;
      
      if (hasLanguageContent) {
        navItem.style.display = 'block';
      } else {
        navItem.style.display = 'none';
      }
    });
  }
  
  // Inizializza con "tutte le lingue" selezionato
  const allLanguagesButton = document.querySelector('.filter-flag[data-lang=""]');
  if (allLanguagesButton) {
    allLanguagesButton.classList.add('active');
  }
});
</script>