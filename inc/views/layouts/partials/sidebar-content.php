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
    <h4>Schede Tecniche</h4>
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

<!-- Esempio CTA personalizzata:
<div class="toro-cta-sidebar mt-4 p-3 bg-light rounded">
    <h5>Hai bisogno di aiuto?</h5>
    <p>Contatta i nostri esperti</p>
    <a href="/contatti" class="btn btn-primary btn-sm">Contattaci</a>
</div>
-->
