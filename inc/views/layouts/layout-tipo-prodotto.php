<?php
/**
 * Template Layout Manager - Tipo Prodotto
 * 
 * Layout 9/3 colonne (75%/25%) per pagine tipo di prodotto
 * Pattern: Descrizione + Prodotti | Documenti + Video
 * Mobile: Stack completo (descrizione → prodotti → docs → video)
 * 
 * Variables disponibili:
 * - $toro_layout_sections: array sezioni caricate
 * - $toro_layout_atts: attributi shortcode  
 * - $toro_layout_type: 'tipo_prodotto'
 */

// Recupera variabili template
$sections = get_query_var('toro_layout_sections', []);
$atts = get_query_var('toro_layout_atts', []);
$layout_type = get_query_var('toro_layout_type', '');

// Valida input
if (empty($sections) || $layout_type !== 'tipo_prodotto') {
    echo '<div class="toro-layout-error">Template non configurato correttamente</div>';
    return;
}

// Separazione sezioni per colonne
$main_sections = [];
$sidebar_sections = [];

foreach ($sections as $section_name => $section_content) {
    switch ($section_name) {
        case 'description':
        case 'products':
            $main_sections[$section_name] = $section_content;
            break;
            
        case 'documents':
        case 'videos':
            $sidebar_sections[$section_name] = $section_content;
            break;
            
        case 'hero':
            // Hero viene gestito separatamente, non nelle colonne
            break;
    }
}

// Determina layout colonne
$sidebar_empty = empty($sidebar_sections);
$main_col_class = $sidebar_empty ? 'col-12' : 'col-lg-9';
$sidebar_col_class = 'col-lg-3';

// CSS classes responsive
$responsive_class = ($atts['responsive'] === 'true') ? 'toro-layout-responsive' : '';
?>

<div class="toro-layout-tipo-prodotto <?php echo esc_attr($responsive_class); ?>" style="margin-top: 0;">
    
    <?php if (false && isset($sections['hero'])): // Hero disabilitato - gestito da Divi ?>
        <!-- Hero Section (Full Width - gestito da Divi) -->
        <div class="toro-hero-section mb-4">
            <?php echo $sections['hero']; ?>
        </div>
    <?php endif; ?>
    
    <div class="container-fluid px-0">
        <div class="row">
            
            <!-- Main Content Column (col-9 o col-12) -->
            <div class="<?php echo esc_attr($main_col_class); ?> toro-main-content">
                
                <?php if (isset($main_sections['description']) && !empty($main_sections['description'])): ?>
                    <!-- Descrizione Tipo Prodotto -->
                    <section class="toro-layout-description mb-4">
                        <?php echo $main_sections['description']; ?>
                    </section>
                <?php endif; ?>
                
                <?php if (isset($main_sections['products']) && !empty($main_sections['products'])): ?>
                    <!-- Prodotti Grid -->
                    <section class="toro-layout-products">
                        <?php echo $main_sections['products']; ?>
                    </section>
                <?php endif; ?>
                
            </div>
            
            <?php if (!$sidebar_empty): ?>
                <!-- Sidebar Column (col-3) -->
                <div class="<?php echo esc_attr($sidebar_col_class); ?> toro-sidebar-content" style="margin-top: 2rem;">
                    
                    <?php if (isset($sidebar_sections['documents']) && !empty($sidebar_sections['documents'])): ?>
                        <!-- Documenti Section -->
                        <section class="toro-layout-docs mb-4">
                            <?php echo $sidebar_sections['documents']; ?>
                        </section>
                    <?php endif; ?>
                    
                    <?php if (isset($sidebar_sections['videos']) && !empty($sidebar_sections['videos'])): ?>
                        <!-- Video Section -->
                        <section class="toro-layout-videos">
                            <?php echo $sidebar_sections['videos']; ?>
                        </section>
                    <?php endif; ?>
                    
                </div>
            <?php endif; ?>
            
        </div> <!-- .row -->
    </div> <!-- .container-fluid -->
    
</div> <!-- .toro-layout-tipo-prodotto -->

<?php
/**
 * Mobile Responsive Behavior (CSS gestito in toro-layout-manager.css):
 * 
 * Desktop (lg+): 75%/25% side-by-side
 * Mobile: Stack completo
 * - Descrizione
 * - Prodotti  
 * - Documenti
 * - Video
 */
?>
