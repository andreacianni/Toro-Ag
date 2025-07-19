<?php
/**
 * Template Layout Prodotto - TORO AG Layout Manager
 * 
 * Template principale per [toro_layout_prodotto]
 * Completamente modificabile dal cliente
 * 
 * Variabili disponibili:
 * @var array $sections - Sezioni caricate con contenuto
 * @var array $atts - Attributi shortcode  
 * @var string $layout_type - Tipo layout (prodotto)
 * 
 * Esempio sections:
 * [
 *   'image' => '<img src="..." class="toro-layout-image">',
 *   'content' => '<div>Contenuto principale...</div>',
 *   'documents' => '<div>Schede tecniche...</div>',
 *   'cultures' => '<div>Colture associate...</div>',
 *   'videos' => '<div>Video prodotto...</div>',
 *   'form' => '<div>Form contatti...</div>'
 * ]
 */

// Sicurezza: esci se chiamato direttamente
if (!defined('ABSPATH')) exit;

// Estrai variabili
$sections = get_query_var('toro_layout_sections', []);
$atts = get_query_var('toro_layout_atts', []);
$layout_type = get_query_var('toro_layout_type', 'prodotto');

// Parametri layout
$sidebar_position = $atts['sidebar_position'] ?? 'left';
$has_sidebar_content = isset($sections['documents']) || isset($sections['videos']) || isset($sections['form']);

// Auto-hide sidebar se non c'è contenuto
if ($sidebar_position === 'auto') {
    $sidebar_position = $has_sidebar_content ? 'left' : 'hide';
}

// CSS classes dinamiche
$container_classes = [
    'toro-layout-container',
    'toro-layout-' . esc_attr($layout_type),
    'toro-layout-sidebar-' . esc_attr($sidebar_position)
];

// Aggiungi classe compatta per sidebar ristrette  
if ($has_sidebar_content && in_array($sidebar_position, ['left', 'right'])) {
    $container_classes[] = 'toro-layout-sidebar-compact';
}

if (empty($sections)) {
    echo '<div class="toro-layout-empty">Nessun contenuto disponibile</div>';
    return;
}
?>

<div class="<?php echo implode(' ', $container_classes); ?>">
    
    <?php if ($sidebar_position === 'hide' || !$has_sidebar_content): ?>
        
        <!-- Layout Full-Width (senza sidebar) -->
        <div class="row">
            <div class="col-12">
                
                <?php 
                // Carica partial per contenuto principale full-width
                set_query_var('toro_sections', $sections);
                get_template_part('inc/views/layouts/partials/main-content-full'); 
                ?>
                
            </div>
        </div>
        
    <?php else: ?>
        
        <!-- Layout con Sidebar -->
        <div class="row">
            
            <?php if ($sidebar_position === 'left'): ?>
                
                <!-- Sidebar a Sinistra - Ristretta -->
                <div class="col-lg-3 col-md-12 order-lg-1 order-2">
                    <?php 
                    set_query_var('toro_sections', $sections);
                    get_template_part('inc/views/layouts/partials/sidebar-content'); 
                    ?>
                </div>
                
                <!-- Contenuto Principale - Più Largo -->
                <div class="col-lg-9 col-md-12 order-lg-2 order-1">
                    <?php 
                    set_query_var('toro_sections', $sections);
                    get_template_part('inc/views/layouts/partials/main-content'); 
                    ?>
                </div>
                
            <?php else: ?>
                
                <!-- Contenuto Principale -->
                <div class="col-lg-9 col-md-12">
                    <?php 
                    set_query_var('toro_sections', $sections);
                    get_template_part('inc/views/layouts/partials/main-content'); 
                    ?>
                </div>
                
                <!-- Sidebar a Destra - Ristretta -->
                <div class="col-lg-3 col-md-12">
                    <?php 
                    set_query_var('toro_sections', $sections);
                    get_template_part('inc/views/layouts/partials/sidebar-content'); 
                    ?>
                </div>
                
            <?php endif; ?>
            
        </div>
        
    <?php endif; ?>
    
    <?php 
    // Form in fondo se richiesto
    if (isset($sections['form']) && ($atts['form_position'] ?? 'bottom') === 'bottom'): 
    ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="toro-layout-form-section">
                <?php echo $sections['form']; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
</div>

<?php
// Pulizia variabili
set_query_var('toro_sections', null);
?>
