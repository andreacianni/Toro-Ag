<?php
/**
 * View: area-cambio-password.php
 * Versione tema: toro-ag-template V0.9.5
 */
?>
<h3>Cambio password</h3>
<form method="post">
    <?php wp_nonce_field('change_pass', 'change_pass_nonce'); ?>

    <p>
        <label for="new_pass">Nuova password</label><br>
        <input type="password" name="new_pass" id="new_pass" required>
    </p>

    <p>
        <label for="confirm_pass">Conferma nuova password</label><br>
        <input type="password" name="confirm_pass" id="confirm_pass" required>
    </p>

    <p>
        <input type="submit" name="submit_new_pass" value="Aggiorna password">
    </p>
</form>
