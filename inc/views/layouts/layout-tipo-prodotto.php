<?php
/**
 * Template Layout Tipo Prodotto - TORO AG Layout Manager
 * 
 * Template per [toro_layout_tipo_prodotto] - IN SVILUPPO
 * 
 * @var array $sections - Sezioni caricate
 * @var array $atts - Attributi shortcode
 * @var string $layout_type - 'tipo_prodotto'
 */

if (!defined('ABSPATH')) exit;

$sections = get_query_var('toro_layout_sections', []);
$atts = get_query_var('toro_layout_atts', []);
?>

<div class="toro-layout-container toro-layout-tipo-prodotto">
    
    <!-- Hero Section (se presente) -->
    <?php if (isset($sections['hero'])): ?>
    <div class="toro-layout-hero-section mb-5">
        <?php echo $sections['hero']; ?>
    </div>
    <?php endif; ?>
    
    <!-- Layout Flessibile per Tipo Prodotto -->
    <div class="row">
        
        <!-- Contenuto Principale -->
        <div class="col-lg-8 col-md-12">
            
            <?php if (isset($sections['description'])): ?>
            <div class="toro-layout-description-section mb-4">
                <?php echo $sections['description']; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($sections['products'])): ?>
            <div class="toro-layout-products-section">
                <h3>Prodotti di questo tipo</h3>
                <?php echo $sections['products']; ?>
            </div>
            <?php endif; ?>
            
        </div>
        
        <!-- Sidebar -->
        <div class="col-lg-4 col-md-12">
            
            <?php if (isset($sections['documents'])): ?>
            <div class="toro-layout-documents-section mb-4">
                <h4>Documentazione</h4>
                <?php echo $sections['documents']; ?>
            </div>
            <?php endif; ?>
            
            <?php if (isset($sections['videos'])): ?>
            <div class="toro-layout-videos-section">
                <h4>Video</h4>
                <?php echo $sections['videos']; ?>
            </div>
            <?php endif; ?>
            
        </div>
        
    </div>
    
</div>

<!-- Template per tipo prodotto - da completare nella Fase 3C -->
