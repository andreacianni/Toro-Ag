<?php
/**
 * View: area-login.php
 * Versione tema: toro-ag-template V0.9.5
 */
?>
  <div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
      <div class="card shadow rounded-3">
        <div class="card-body p-4">
          <h2 class="mb-4 text-center">Accedi</h2>
          <p class="text-muted text-center mb-3">Devi essere loggato per accedere a quest’area.</p>
          <?php
          wp_login_form([
              'label_username' => 'Nome utente',
              'label_password' => 'Password',
              'label_remember' => 'Ricordami',
              'label_log_in'    => 'Accedi',
              'form_id'         => 'loginform-custom',
              'id_username'     => 'user_login',
              'id_password'     => 'user_pass',
              'id_remember'     => 'rememberme',
              'id_submit'       => 'wp-submit',
              'class_container' => 'mb-3'
          ]);
          ?>
          <p class="mt-3 text-center">
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>">Hai dimenticato la password?</a>
          </p>
        </div>
      </div>
    </div>
  </div>
