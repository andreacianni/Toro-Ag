<?php
/**
 * File: card-agente.php
 * Versione tema: toro-ag-template V0.9.5
 *
 * Shortcode per visualizzare i dati dell'agente in un card.
 * Utilizza i meta dell'utente per recuperare le informazioni.
 *
 * @package Toro Ag Template
 */


add_shortcode('agente_card', function ($atts = [], $content = null) {
    if (!is_user_logged_in()) return '';

    $user = wp_get_current_user();
    $user_id = $user->ID;

    // 🔁 Usa funzione centralizzata
    $dati = get_dati_agente($user_id);

    // Output
    ob_start(); ?>
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div class="me-2">
                Scheda Agente "<em><?php echo esc_html($user->display_name); ?></em>"
                </div>
                <div>
                    <?php if ( is_agente_attivo($user->ID) ) : ?>
                    <span class="badge bg-success fw-normal">
                        <i class="bi bi-check-circle me-1"></i> Attivo
                    </span>
                    <?php else : ?>
                    <span class="badge bg-danger fw-normal">
                        <i class="bi bi-slash-circle me-1"></i> Disattivato
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            

            <div class="card-body">
                <div class="row gy-2 align-items-start">
                <?php if (!empty($user->display_name)): ?>
                    <div class="col-4 text-end fw-semibold">Nome:</div>
                    <div class="col-8"><?php echo esc_html($user->display_name); ?></div>
                <?php endif; ?>

                <?php if (!empty($dati['indirizzo'])): ?>
                    <div class="col-4 text-end fw-semibold">Indirizzo:</div>
                    <div class="col-8"><?php echo esc_html($dati['indirizzo']); ?></div>
                <?php endif; ?>

                <?php if (!empty($dati['telefono'])): ?>
                    <div class="col-4 text-end fw-semibold">Telefono:</div>
                    <div class="col-8"><?php echo esc_html($dati['telefono']); ?></div>
                <?php endif; ?>

                <?php if (!empty($dati['cellulare'])): ?>
                    <div class="col-4 text-end fw-semibold">Cellulare:</div>
                    <div class="col-8"><?php echo esc_html($dati['cellulare']); ?></div>
                <?php endif; ?>

                <?php if (!empty($dati['territori'])): ?>
                    <div class="col-4 text-end fw-semibold">Territori:</div>
                    <div class="col-8"><?php echo esc_html($dati['territori']); ?></div>
                <?php endif; ?>


                    <div class="col-12 mt-3">
                        <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline-danger btn-sm w-100">Esci</a>
                    </div>
                    <div class="col-12">
                        <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="btn btn-outline-secondary btn-sm w-100">Reimposta password</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
});
