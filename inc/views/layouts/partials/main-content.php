<?php
/**
 * Partial: Contenuto Principale - TORO AG Layout Manager
 * 
 * Template per contenuto principale quando c'è sidebar
 * Facilmente modificabile dal cliente
 * 
 * @var array $sections - Sezioni disponibili dal get_query_var('toro_sections')
 */

if (!defined('ABSPATH')) exit;

$sections = get_query_var('toro_sections', []);
?>

<?php if (isset($sections['image'])): ?>
<div class="toro-layout-image-section mb-4">
    <div class="text-center">
        <div class="d-inline-block" style="max-width: 400px;">
            <?php echo $sections['image']; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($sections['content'])): ?>
<div class="toro-layout-content-section">
    <?php echo $sections['content']; ?>
</div>
<?php endif; ?>

<?php if (isset($sections['cultures'])): ?>
<div class="toro-layout-cultures-section mt-4">
    <?php echo $sections['cultures']; ?>
</div>
<?php endif; ?>

<?php 
/**
 * PERSONALIZZAZIONE FACILE:
 * 
 * Per cambiare ordine sezioni, sposta i blocchi sopra
 * Per aggiungere HTML custom, inserisci qui:
 */ 
?>

<!-- Esempio personalizzazione:
<div class="toro-custom-section mt-4">
    <h4>Sezione Personalizzata</h4>
    <p>Contenuto aggiuntivo...</p>
</div>
-->

<?php
/**
 * POSSIBILI SEZIONI AGGIUNTIVE:
 * 
 * $sections['documents'] - generalmente in sidebar
 * $sections['videos'] - generalmente in sidebar  
 * $sections['form'] - generalmente in fondo
 * 
 * Ma puoi spostarle qui se necessario!
 */
?>
