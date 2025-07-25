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
        
        <!-- Stack Verticale Ottimizzato -->
        <div class="row">
            <div class="col-12">
                
                <!-- Descrizione + Brochure affiancate -->
                <?php if (isset($sections['description']) || isset($sections['brochures'])): ?>
                <div class="row mb-4">
                    <?php if (isset($sections['description']) && isset($sections['brochures'])): ?>
                        <!-- Descrizione + Brochure: 9/3 colonne (come tipo prodotto) -->
                        <div class="col-lg-9">
                            <div class="toro-main-content">
                                <?= $sections['description']; ?>
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="toro-sidebar-content">
                                <?= $sections['brochures']; ?>
                            </div>
                        </div>
                    <?php elseif (isset($sections['description'])): ?>
                        <!-- Solo descrizione: full width -->
                        <div class="col-12">
                            <div class="toro-layout-description">
                                <?= $sections['description']; ?>
                            </div>
                        </div>
                    <?php elseif (isset($sections['brochures'])): ?>
                        <!-- Solo brochure: full width -->
                        <div class="col-12">
                            <div class="toro-layout-brochures">
                                <?= $sections['brochures']; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (isset($sections['products'])): ?>
                <!-- Prodotti Raggruppati per Tipo (senza titolo) -->
                <div class="toro-layout-products">
                    <?= $sections['products']; ?>
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
