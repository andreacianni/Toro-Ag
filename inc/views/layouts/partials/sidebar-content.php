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

<?php if (isset($sections['documents'])): ?>
<div class="toro-layout-documents-section mb-4">
    <?php echo $sections['documents']; ?>
</div>
<?php endif; ?>

<?php if (isset($sections['videos'])): ?>
<div class="toro-layout-videos-section mb-4">
    <h4>Video Prodotto</h4>
    <?php echo $sections['videos']; ?>
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
<div class="toro-cta-sidebar mt-4 p-3 text-center" style="background: white; border-radius: 8px; border: 1px solid #e0e0e0;">
    <a href="#contatti" class="btn btn-primary btn-sm">Chiedi informazioni sul prodotto</a>
</div>
<?php endif; ?>
