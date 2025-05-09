<?php
function toro_handle_secure_download() {
    if ( !isset($_GET['secure_download']) ) return;

    // Sicurezza: verifica nonce se vuoi (opzionale)
    if ( !is_user_logged_in() ) {
        wp_die('Accesso negato. Devi essere loggato.');
    }

    $current_user = wp_get_current_user();
    $ctp_attivo = get_user_meta($current_user->ID, 'agente_attivo', true);

    if ( $ctp_attivo !== '1' ) {
        // Ritardo di 2 secondi e redirect all'area riservata con stile
        echo '<!DOCTYPE html>
        <html lang="it">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="refresh" content="2;url=' . esc_url(home_url('/area-agenti')) . '">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Accesso Negato</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    background-color: #f9f9f9;
                    margin: 0;
                }
                .message-box {
                    background: #fff;
                    padding: 2rem 3rem;
                    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                    text-align: center;
                    border-radius: 10px;
                }
                .loader {
                    margin: 1rem auto;
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #3498db;
                    border-radius: 50%;
                    width: 30px;
                    height: 30px;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        </head>
        <body>
            <div class="message-box">
                <h2>Accesso negato</h2>
                <p>Verrai reindirizzato all&#39;area riservata tra pochi secondi...</p>
                <div class="loader"></div>
            </div>
        </body>
        </html>';
        exit;
    }

    $doc_id = intval($_GET['secure_download']);
    if ( !$doc_id ) wp_die('ID documento non valido.');

    $pods = pods('documento_agente', $doc_id);
    if ( !$pods ) wp_die('Documento non trovato.');

    $file_info = $pods->field('file_pdf');

    if ( empty($file_info['ID']) ) {
        wp_die('Nessun file PDF associato.');
    }

    $file_path = get_attached_file($file_info['ID']);
    if ( !file_exists($file_path) ) {
        wp_die('File non trovato.');
    }

    $file_name = basename($file_path);

    // Header e output del file
    header('Content-Description: File Transfer');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $file_name . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
}

add_action('init', 'toro_handle_secure_download');
