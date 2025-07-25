<?php
/**
 * Template: Layout Coltura
 * Uso: [toro_layout_coltura sections="auto" layout="stacked" brochure_layout="card"]
 * 
 * Layout Stack Verticale:
 * - Hero (sempre)
 * - Descrizione (se presente)
 * - Prodotti raggruppati per tipo (toro_tipi_per_coltura con titoli H4)
 * - Brochure (se presenti, con layout adattato per immagini verticali)
 * - Video (se presenti)
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ottieni sezioni caricate
$sections = get_query_var('toro_layout_sections', []);
$atts = get_query_var('toro_layout_atts', []);
$term = get_queried_object();

// CSS classes per layout stacked
$container_classes = ['toro-layout-coltura', 'container-fluid', 'px-0'];
if ($atts['responsive'] === 'true') {
    $container_classes[] = 'toro-responsive';
}
?>

<div class="<?= esc_attr(implode(' ', $container_classes)); ?>">
    <?php if (!empty($sections)): ?>
        
        <!-- Stack Verticale - Tutte le sezioni in colonna singola -->
        <div class="row">
            <div class="col-12">
                
                <?php if (isset($sections['hero'])): ?>
                <!-- Hero Section -->
                <div class="toro-layout-hero-section">
                    <?= $sections['hero']; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($sections['description'])): ?>
                <!-- Descrizione Coltura -->
                <div class="toro-layout-description">
                    <?= $sections['description']; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($sections['products'])): ?>
                <!-- Prodotti Raggruppati per Tipo -->
                <div class="toro-layout-products">
                    <h4 class="fw-bold border-bottom px-3 py-2 my-4">
                        <?= esc_html__('Applicazioni', 'toro-ag'); ?>
                    </h4>
                    <?= $sections['products']; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($sections['brochures'])): ?>
                <!-- Brochure Coltura -->
                <div class="toro-layout-brochures">
                    <?= $sections['brochures']; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($sections['videos'])): ?>
                <!-- Video Coltura -->
                <div class="toro-layout-videos">
                    <?= $sections['videos']; ?>
                </div>
                <?php endif; ?>
                
            </div>
        </div>
        
    <?php else: ?>
        <!-- Fallback se nessuna sezione -->
        <div class="toro-layout-empty">
            <p><?= esc_html__('Nessun contenuto disponibile per questa coltura.', 'toro-ag'); ?></p>
        </div>
    <?php endif; ?>
</div>

<?php
// Reset query vars
set_query_var('toro_layout_sections', null);
set_query_var('toro_layout_atts', null);
?>
