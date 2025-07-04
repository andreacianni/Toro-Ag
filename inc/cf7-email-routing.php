<?php
/**
 * Contact Form 7 Email Routing
 * Gestisce l'invio condizionale delle email in base al settore selezionato
 * + Form Prodotti con riferimento automatico al prodotto
 */

if (!defined('ABSPATH')) {
    exit; // Impedisce accesso diretto
}

class CF7_Email_Routing {
    
    private $form_ids = [
        'main_form' => 123,     // ID del modulo principale (con settore)
        'simple_form' => 124,   // ID del modulo semplice (senza settore)
        'product_form' => 126   // ID del modulo prodotti (da aggiornare con ID reale)
    ];
    
    private $email_addresses = [
        'Supporto' => 'info@toro-ag.it',
        'Commerciale' => 'info@toro-ag.it', 
        'Marketing' => 'Francesco.Monteduro@toro.com',
        'Generica' => 'Francesco.Monteduro@toro.com',
        'Prodotti' => 'info@toro-ag.it',  // Email per richieste prodotti
        'default' => 'Francesco.Monteduro@toro.com' // Email di default
    ];
    
    public function __construct() {
        add_action('wpcf7_before_send_mail', [$this, 'route_email']);
        add_action('wpcf7_mail_sent', [$this, 'log_email_sent']);
    }
    
    /**
     * Gestisce il routing delle email
     */
    public function route_email($contact_form) {
        $form_id = $contact_form->id();
        
        // Verifica se è uno dei nostri moduli
        if (!in_array($form_id, $this->form_ids)) {
            return;
        }
        
        $submission = WPCF7_Submission::get_instance();
        if (!$submission) {
            return;
        }
        
        $posted_data = $submission->get_posted_data();
        
        // Determina l'email di destinazione
        $recipient_email = $this->get_recipient_email($posted_data, $form_id);
        
        // Modifica il destinatario
        $mail = $contact_form->prop('mail');
        $mail['recipient'] = $recipient_email;
        
        // Personalizza l'oggetto dell'email
        $mail['subject'] = $this->get_email_subject($posted_data, $form_id);
        
        $contact_form->set_properties(['mail' => $mail]);
    }
    
    /**
     * Determina l'email del destinatario
     */
    private function get_recipient_email($posted_data, $form_id) {
        // Se è il modulo principale (con settore) - QUI serve il routing
        if ($form_id == $this->form_ids['main_form']) {
            $motivo = isset($posted_data['motivo']) ? $posted_data['motivo'] : '';
            return isset($this->email_addresses[$motivo]) 
                ? $this->email_addresses[$motivo] 
                : $this->email_addresses['default'];
        }
        
        // Se è il modulo prodotti - SEMPRE info@toro-ag.it (nessun routing)
        if ($form_id == $this->form_ids['product_form']) {
            return $this->email_addresses['Prodotti'];
        }
        
        // Se è il modulo semplice (senza settore)
        return $this->email_addresses['default'];
    }
    
    /**
     * Genera l'oggetto dell'email
     */
    private function get_email_subject($posted_data, $form_id) {
        $nome = isset($posted_data['nome']) ? $posted_data['nome'] : 'Visitatore';
        
        // Form contatti con settore
        if ($form_id == $this->form_ids['main_form']) {
            $motivo = isset($posted_data['motivo']) ? $posted_data['motivo'] : 'Generica';
            return "Nuova richiesta: {$motivo} - {$nome}";
        }
        
        // Form prodotti
        if ($form_id == $this->form_ids['product_form']) {
            $product_info = $this->get_current_product_info();
            
            // Oggetto tradotto
            if (function_exists('pll_current_language') && pll_current_language() === 'en') {
                return "Information request for \"{$product_info['name']}\" - {$product_info['type']}";
            } else {
                return "Richiesta informazioni per \"{$product_info['name']}\" - {$product_info['type']}";
            }
        }
        
        // Form semplice
        return "Nuova richiesta di contatto - {$nome}";
    }
    
    /**
     * Recupera informazioni del prodotto corrente
     */
    private function get_current_product_info() {
        global $post;
        
        $product_name = 'Prodotto non identificato';
        $product_type = '';
        
        if ($post && $post->post_type === 'prodotto') {
            $product_name = get_the_title($post->ID);
            
            // Recupera il tipo di prodotto dalla tassonomia
            $terms = get_the_terms($post->ID, 'tipo_di_prodotto');
            if ($terms && !is_wp_error($terms)) {
                $product_type = $terms[0]->name;
            }
        }
        
        return [
            'name' => $product_name,
            'type' => $product_type
        ];
    }
    
    /**
     * Log dell'invio email (opzionale)
     */
    public function log_email_sent($contact_form) {
        if (WP_DEBUG) {
            $form_id = $contact_form->id();
            $form_type = 'unknown';
            
            if ($form_id == $this->form_ids['main_form']) $form_type = 'contatti';
            if ($form_id == $this->form_ids['product_form']) $form_type = 'prodotti';
            if ($form_id == $this->form_ids['simple_form']) $form_type = 'semplice';
            
            error_log("CF7: Email inviata per modulo {$form_type} (ID: {$form_id})");
        }
    }
    
    /**
     * Aggiorna gli ID dei moduli
     */
    public function update_form_ids($main_form_id, $simple_form_id = null, $product_form_id = null) {
        $this->form_ids['main_form'] = $main_form_id;
        if ($simple_form_id) {
            $this->form_ids['simple_form'] = $simple_form_id;
        }
        if ($product_form_id) {
            $this->form_ids['product_form'] = $product_form_id;
        }
    }
    
    /**
     * Aggiorna gli indirizzi email
     */
    public function update_email_addresses($addresses) {
        $this->email_addresses = array_merge($this->email_addresses, $addresses);
    }
}

// Inizializza la classe
new CF7_Email_Routing();