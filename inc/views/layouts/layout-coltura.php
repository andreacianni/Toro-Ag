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
        
        <!-- Layout Diretto - Descrizione + Products nella Main, Brochure in Sidebar -->
        <div class="row">
            
            <!-- Main Content: Descrizione + Products Stack -->
            <div class="toro-main-content <?= isset($sections['brochures']) ? 'col-lg-9' : 'col-12'; ?>">
                
                <?php if (isset($sections['description'])): ?>
                <!-- Descrizione -->
                <div class="toro-term-description mb-4">
                    <?= $sections['description']; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($sections['products'])): ?>
                <!-- Prodotti Raggruppati per Tipo (senza titolo) -->
                <div class="toro-layout-products">
                    <?= $sections['products']; ?>
                </div>
                <?php endif; ?>
                
            </div>
            
            <?php if (isset($sections['brochures'])): ?>
            <!-- Brochure Sidebar -->
            <div class="toro-sidebar-content col-lg-3">
                <?= $sections['brochures']; ?>
            </div>
            <?php endif; ?>
            
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
