<?php
/**
 * Partial: Sidebar Content - TORO AG Layout Manager
 * 
 * Template per contenuto sidebar
 * Facilmente modificabile dal cliente
 * 
 * @var array $sections - Sezioni disponibili
 */

if (!defined('ABSPATH')) exit;

$sections = get_query_var('toro_sections', []);
?>

<?php
$current_language = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : apply_filters('wpml_current_language', null);
if (isset($sections['documents']) || isset($sections['videos'])):
?>
<div class="toro-product-documents-sidebar" data-product-documents-sidebar>
    <?php if ($current_language !== 'it'): ?>
    <div class="toro-product-documents-filter documenti-filter mb-3" data-product-documents-filter hidden>
        <h6 class="fw-bold mb-2 small"><?php echo esc_html(ta_product_documents_filter_string('Choose language', 'Choose language')); ?></h6>
        <div class="d-flex flex-wrap gap-1" data-product-documents-filter-controls></div>
    </div>
    <?php endif; ?>

    <?php if (isset($sections['documents'])): ?>
    <div class="toro-layout-documents-section mb-4">
        <?php echo $sections['documents']; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($sections['videos'])): ?>
    <div class="toro-layout-videos-section">
        <?php echo $sections['videos']; ?>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php 
// Form in sidebar se richiesto
$form_position = get_query_var('toro_layout_atts', [])['form_position'] ?? 'bottom';
if (isset($sections['form']) && $form_position === 'sidebar'): 
?>
<div class="toro-layout-form-section">
    <h4><i class="bi bi-envelope"></i> Contatti</h4>
    <?php echo $sections['form']; ?>
</div>
<?php endif; ?>

<?php
/**
 * PERSONALIZZAZIONE SIDEBAR:
 * 
 * Aggiungi qui contenuto personalizzato sidebar:
 * - Banner promozionali
 * - Link correlati  
 * - Call-to-action
 * - Informazioni aggiuntive
 */
?>

<!-- CTA Informazioni Prodotto - Visibile solo in layout a due colonne -->
<?php 
// Mostra CTA solo se ci sono sezioni sidebar (layout a due colonne)
if (isset($sections['documents']) || isset($sections['videos']) || isset($sections['form'])): 
?>
<div class="toro-cta-sidebar mt-4 py-3 text-center">
    <a href="#form-prodotti" class="btn-submit border shadow-sm"><?php echo apply_filters('wpml_translate_single_string', 'Chiedi informazioni sul prodotto', 'Toro Layout Manager', 'Chiedi informazioni sul prodotto'); ?></a>
</div>
<?php endif; ?>
