<?php
/**
 * View: area-dati-agente.php
 * Versione tema: toro-ag-template V0.9.5
 */
?>

<div class="row flex-md-row-reverse">
        <!-- Sidebar a destra -->
        <?php echo do_shortcode('[agente_card]'); ?>

        <!-- Contenuto principale a sinistra -->
        <div class="col-md-8">
            <section class="p-4 rounded">
                <?php echo do_shortcode('[documenti_agente layout="gallery"]'); ?>
            </section>
        </div>
</div>
