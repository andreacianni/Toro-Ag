<?php
/**
 * TORO AG GitHub Theme Updater
 * Integrazione WordPress Update API con GitHub Repository
 * 
 * @package ToroAG
 * @version 1.0.0
 * @author Andrea Cianni
 */

if (!defined('ABSPATH')) {
    exit;
}

class ToroGitHubUpdater {
    
    /**
     * @var string GitHub repository (formato: username/repo-name)
     */
    private $github_repo = 'andreacianni/Toro-Ag';
    
    /**
     * @var string Theme slug (nome della cartella del tema)
     */
    private $theme_slug;
    
    /**
     * @var string Theme stylesheet (get_option('stylesheet'))
     */
    private $theme_stylesheet;
    
    /**
     * @var string Version corrente del tema
     */
    private $theme_version;
    
    /**
     * @var string GitHub API token (opzionale ma raccomandato)
     */
    private $github_token;
    
    /**
     * @var array Dati del tema corrente
     */
    private $theme_data;
    
    /**
     * Constructor
     */
    public function __construct() {
        $this->theme_stylesheet = get_option('stylesheet');
        $this->theme_slug = 'toro-ag'; // Nome fisso della cartella tema (evita auto-rilevamento)
        $this->theme_data = wp_get_theme($this->theme_stylesheet);
        $this->theme_version = $this->theme_data->get('Version');
        $this->github_token = get_option('toro_github_token', '');

        // Inizializza hooks sempre (rimuovendo il controllo del nome auto-rilevato)
        $this->init_hooks();
        }
    }
    
    /**
     * Inizializza WordPress hooks
     */
    private function init_hooks() {
        // Hook per controllare aggiornamenti
        add_filter('pre_set_site_transient_update_themes', array($this, 'check_for_update'));
        
        // Hook per download personalizzato
        add_filter('upgrader_pre_download', array($this, 'download_package'), 10, 3);
        
        // Hook per informazioni tema
        add_filter('themes_api', array($this, 'theme_api_call'), 10, 3);
        
        // Hook per rinominare cartella dopo installazione
        add_filter('upgrader_post_install', array($this, 'rename_theme_folder'), 10, 3);

        // Pulizia transient quando necessario
        add_action('upgrader_process_complete', array($this, 'clear_update_transient'), 10, 2);

        // Settings page
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Controlla se ci sono aggiornamenti disponibili
     */
    public function check_for_update($transient) {
        if (empty($transient->checked)) {
            return $transient;
        }
        
        // Ottieni informazioni remote
        $remote_info = $this->get_remote_info();
        
        if (!$remote_info || is_wp_error($remote_info)) {
            return $transient;
        }
        
        // Confronta versioni
        if (version_compare($this->theme_version, $remote_info['version'], '<')) {
            $transient->response[$this->theme_stylesheet] = array(
                'theme' => $this->theme_stylesheet,
                'new_version' => $remote_info['version'],
                'url' => $remote_info['details_url'],
                'package' => $remote_info['download_url']
            );
        }
        
        return $transient;
    }
    
    /**
     * Ottiene informazioni remote dal repository GitHub
     */
    private function get_remote_info() {
        // Cache per 12 ore
        $cache_key = 'toro_github_remote_info';
        $cache_duration = 12 * HOUR_IN_SECONDS;
        
        $remote_info = get_transient($cache_key);
        if ($remote_info !== false) {
            return $remote_info;
        }
        
        // API endpoints GitHub
        $api_url = "https://api.github.com/repos/{$this->github_repo}";
        $releases_url = "{$api_url}/releases/latest";
        $commits_url = "{$api_url}/commits/main";
        
        // Headers per richiesta API
        $headers = array(
            'User-Agent' => 'TORO-AG-Updater/1.0'
        );
        
        if (!empty($this->github_token)) {
            $headers['Authorization'] = 'token ' . $this->github_token;
        }
        
        $request_args = array(
            'headers' => $headers,
            'timeout' => 30
        );
        
        // Prima prova a ottenere l'ultima release
        $release_response = wp_remote_get($releases_url, $request_args);
        
        if (!is_wp_error($release_response) && wp_remote_retrieve_response_code($release_response) === 200) {
            $release_data = json_decode(wp_remote_retrieve_body($release_response), true);
            
            if ($release_data && !empty($release_data['tag_name'])) {
                $remote_info = array(
                    'version' => ltrim($release_data['tag_name'], 'v'),
                    'details_url' => $release_data['html_url'],
                    'download_url' => $release_data['zipball_url'],
                    'body' => $release_data['body'] ?? '',
                    'date' => $release_data['published_at'] ?? '',
                    'type' => 'release'
                );
                
                set_transient($cache_key, $remote_info, $cache_duration);
                return $remote_info;
            }
        }
        
        // Fallback: usa l'ultimo commit se non ci sono release
        $commits_response = wp_remote_get($commits_url, $request_args);
        
        if (!is_wp_error($commits_response) && wp_remote_retrieve_response_code($commits_response) === 200) {
            $commit_data = json_decode(wp_remote_retrieve_body($commits_response), true);
            
            if ($commit_data && !empty($commit_data['sha'])) {
                // Versione basata su timestamp commit
                $commit_date = strtotime($commit_data['commit']['committer']['date']);
                $version = date('Y.m.d.Hi', $commit_date);
                
                $remote_info = array(
                    'version' => $version,
                    'details_url' => $commit_data['html_url'],
                    'download_url' => "https://github.com/{$this->github_repo}/archive/main.zip",
                    'body' => $commit_data['commit']['message'] ?? '',
                    'date' => $commit_data['commit']['committer']['date'] ?? '',
                    'type' => 'commit'
                );
                
                set_transient($cache_key, $remote_info, $cache_duration);
                return $remote_info;
            }
        }
        
        return false;
    }
    
    /**
     * Download personalizzato del package
     */
    public function download_package($reply, $package, $upgrader) {
        if (!$upgrader instanceof Theme_Upgrader) {
            return $reply;
        }
        
        // Verifica se è il nostro tema
        if (!str_contains($package, $this->github_repo)) {
            return $reply;
        }
        
        // Headers per il download
        $headers = array();
        if (!empty($this->github_token)) {
            $headers['Authorization'] = 'token ' . $this->github_token;
        }
        
        // Download del file
        $response = wp_remote_get($package, array(
            'headers' => $headers,
            'timeout' => 300,
            'stream' => true,
            'filename' => $upgrader->skin->theme . '.zip'
        ));
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        return $upgrader->skin->theme . '.zip';
    }
    
    /**
     * API call per informazioni tema
     */
    public function theme_api_call($result, $action, $args) {
        if ($action !== 'theme_information') {
            return $result;
        }
        
        if (!isset($args->slug) || $args->slug !== $this->theme_slug) {
            return $result;
        }
        
        $remote_info = $this->get_remote_info();
        
        if (!$remote_info || is_wp_error($remote_info)) {
            return $result;
        }
        
        return (object) array(
            'name' => $this->theme_data->get('Name'),
            'slug' => $this->theme_slug,
            'version' => $remote_info['version'],
            'author' => $this->theme_data->get('Author'),
            'author_profile' => $this->theme_data->get('AuthorURI'),
            'last_updated' => $remote_info['date'],
            'homepage' => $this->theme_data->get('ThemeURI'),
            'description' => $this->theme_data->get('Description'),
            'short_description' => $this->theme_data->get('Description'),
            'sections' => array(
                'description' => $this->theme_data->get('Description'),
                'changelog' => $remote_info['body']
            ),
            'download_link' => $remote_info['download_url'],
            'tags' => array('toro-ag', 'custom', 'divi-child'),
            'requires' => '5.0',
            'tested' => get_bloginfo('version'),
            'requires_php' => '7.4',
        );
    }
    
    /**
     * Pulisce i transient dopo l'aggiornamento
     */
    public function clear_update_transient($upgrader, $hook_extra) {
        if (isset($hook_extra['theme']) && $hook_extra['theme'] === $this->theme_slug) {
            delete_transient('toro_github_remote_info');
        }
    }

    /**
     * Rinomina la cartella del tema dopo l'installazione
     *
     * @param bool $response Installation response
     * @param array $hook_extra Hook extra data
     * @param array $result Installation result
     * @return bool
     */
    public function rename_theme_folder($response, $hook_extra, $result) {
        global $wp_filesystem;

        // Verifica se è il nostro tema
        if (!isset($hook_extra['theme']) ||
            !isset($result['destination']) ||
            !str_contains($result['destination'], 'andreacianni-Toro-Ag')) {
            return $response;
        }

        // Inizializza WP_Filesystem se necessario
        if (!$wp_filesystem) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $themes_dir = get_theme_root();
        $old_path = $result['destination']; // Percorso con nome GitHub
        $new_path = $themes_dir . '/' . $this->theme_slug; // Percorso con nome fisso

        // Se la cartella di destinazione esiste già, eliminala
        if ($wp_filesystem->exists($new_path)) {
            $wp_filesystem->delete($new_path, true);
        }

        // Rinomina la cartella
        if ($wp_filesystem->move($old_path, $new_path)) {
            // Aggiorna il percorso nel risultato
            $result['destination'] = $new_path;
            $result['destination_name'] = $this->theme_slug;

            // Log per debug
            error_log("TORO GitHub Updater: Cartella rinominata da " . basename($old_path) . " a " . $this->theme_slug);
        } else {
            error_log("TORO GitHub Updater: Errore nella rinomina da " . basename($old_path) . " a " . $this->theme_slug);
        }

        return $response;
    }

    /**
     * Aggiunge pagina settings
     */
    public function add_settings_page() {
        add_theme_page(
            'TORO AG GitHub Updater',
            'GitHub Updater',
            'manage_options',
            'toro-github-updater',
            array($this, 'settings_page')
        );
    }
    
    /**
     * Registra settings
     */
    public function register_settings() {
        register_setting('toro_github_settings', 'toro_github_token');
        
        add_settings_section(
            'toro_github_main',
            'Configurazione GitHub',
            array($this, 'settings_section_callback'),
            'toro_github_settings'
        );
        
        add_settings_field(
            'toro_github_token',
            'GitHub Token (opzionale)',
            array($this, 'token_field_callback'),
            'toro_github_settings',
            'toro_github_main'
        );
    }
    
    /**
     * Callback sezione settings
     */
    public function settings_section_callback() {
        echo '<p>Configura il token GitHub per aumentare i limiti API (opzionale ma raccomandato).</p>';
    }
    
    /**
     * Callback campo token
     */
    public function token_field_callback() {
        $token = get_option('toro_github_token', '');
        echo '<input type="password" id="toro_github_token" name="toro_github_token" value="' . esc_attr($token) . '" class="regular-text" />';
        echo '<p class="description">Token GitHub per accesso API. <a href="https://github.com/settings/tokens" target="_blank">Genera token</a></p>';
    }
    
    /**
     * Pagina settings
     */
    public function settings_page() {
        if (isset($_POST['check_now'])) {
            delete_transient('toro_github_remote_info');
            echo '<div class="notice notice-success"><p>Cache aggiornamenti pulita!</p></div>';
        }
        
        $remote_info = $this->get_remote_info();
        $status = $remote_info ? 'Connesso' : 'Non connesso';
        $status_class = $remote_info ? 'notice-success' : 'notice-error';
        
        ?>
        <div class="wrap">
            <h1>TORO AG GitHub Updater</h1>
            
            <div class="notice <?php echo $status_class; ?>">
                <p><strong>Status GitHub:</strong> <?php echo $status; ?></p>
                <?php if ($remote_info): ?>
                    <p><strong>Ultima versione:</strong> <?php echo $remote_info['version']; ?> 
                       (<?php echo $remote_info['type']; ?>)</p>
                    <p><strong>Versione corrente:</strong> <?php echo $this->theme_version; ?></p>
                <?php endif; ?>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('toro_github_settings');
                do_settings_sections('toro_github_settings');
                submit_button();
                ?>
            </form>
            
            <form method="post">
                <?php submit_button('Controlla Aggiornamenti Ora', 'secondary', 'check_now'); ?>
            </form>
            
            <h3>Informazioni Repository</h3>
            <table class="form-table">
                <tr>
                    <th>Repository GitHub</th>
                    <td><a href="https://github.com/<?php echo $this->github_repo; ?>" target="_blank">
                        <?php echo $this->github_repo; ?></a></td>
                </tr>
                <tr>
                    <th>Theme Slug</th>
                    <td><?php echo $this->theme_slug; ?></td>
                </tr>
                <tr>
                    <th>Versione Corrente</th>
                    <td><?php echo $this->theme_version; ?></td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Forza controllo aggiornamenti (per debug)
     */
    public function force_check() {
        delete_transient('toro_github_remote_info');
        delete_site_transient('update_themes');
        return $this->get_remote_info();
    }
}

// Inizializza solo se siamo in admin o durante gli aggiornamenti
if (is_admin() || wp_doing_cron()) {
    new ToroGitHubUpdater();
}