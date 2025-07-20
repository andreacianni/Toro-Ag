<?php
/**
 * Partial: Main Content Full Width - TORO AG Layout Manager
 * 
 * Template per contenuto principale full-width (senza sidebar)
 * Facilmente modificabile dal cliente
 * 
 * @var array $sections - Sezioni disponibili
 */

if (!defined('ABSPATH')) exit;

$sections = get_query_var('toro_sections', []);
?>

<?php if (isset($sections['image'])): ?>
<div class="toro-layout-image-section mb-4 text-center">
    <?php echo $sections['image']; ?>
</div>
<?php endif; ?>

<?php if (isset($sections['content'])): ?>
<div class="toro-layout-content-section">
    <?php echo $sections['content']; ?>
</div>
<?php endif; ?>

<?php 
/**
 * In layout full-width, mostra anche documenti e video inline
 * invece che in sidebar
 */
?>

<?php if (isset($sections['documents'])): ?>
<div class="toro-layout-documents-section mt-4">
    <h3>Documentazione Tecnica</h3>
    <div class="row">
        <div class="col-12">
            <?php echo $sections['documents']; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($sections['videos'])): ?>
<div class="toro-layout-videos-section mt-4">
    <h3>Video e Contenuti Multimediali</h3>
    <div class="row">
        <div class="col-12">
            <?php echo $sections['videos']; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($sections['cultures'])): ?>
<div class="toro-layout-cultures-section mt-4">
    <?php echo $sections['cultures']; ?>
</div>
<?php endif; ?>

<?php
/**
 * LAYOUT FULL-WIDTH PERSONALIZZAZIONI:
 * 
 * In questo layout puoi:
 * - Aggiungere sezioni a tutta larghezza
 * - Creare layout a più colonne per documenti/video
 * - Inserire call-to-action centrali
 * - Aggiungere contenuto promozionale
 */
?>

<!-- Esempio sezione promozionale:
<div class="toro-promo-section mt-5 p-4 bg-primary text-white rounded">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4>Scopri la nostra gamma completa</h4>
            <p class="mb-0">Trova il prodotto perfetto per le tue esigenze</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="/prodotti" class="btn btn-light">Vedi tutti i prodotti</a>
        </div>
    </div>
</div>
-->
