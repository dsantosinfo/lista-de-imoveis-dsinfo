<?php
/**
 * Plugin Name:       Lista de Imóveis
 * Plugin URI:        https://dsantosinfo.com.br/lista-de-imoveis
 * Description:       Gerencie e exiba imóveis de forma avançada com integração total ao Elementor.
 * Version:           8.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            DSantos Info
 * Author URI:        https://dsantosinfo.com.br
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lista-imoveis
 * Domain Path:       /languages
 *
 * @package ListaDeImoveis
 */

if (!defined('ABSPATH')) exit;

/**
 * Classe principal que carrega e inicializa todas as partes do plugin.
 */
final class Lista_Imoveis {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->define_constants();
        $this->load_dependencies();
        $this->init_plugin();
    }

    /**
     * Define constantes úteis para o plugin.
     */
    private function define_constants() {
        define('LI_PLUGIN_PATH', plugin_dir_path(__FILE__));
        define('LI_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('LI_PLUGIN_VERSION', '8.0');
    }

    /**
     * Carrega todos os arquivos necessários (as classes helper).
     */
    private function load_dependencies() {
        require_once LI_PLUGIN_PATH . 'includes/class-li-post-types.php';
        require_once LI_PLUGIN_PATH . 'includes/class-li-admin.php';
        require_once LI_PLUGIN_PATH . 'includes/class-li-public.php';
        require_once LI_PLUGIN_PATH . 'includes/class-li-elementor.php';
        require_once LI_PLUGIN_PATH . 'includes/class-li-api.php';
    }

    /**
     * Instancia as classes para registrar os hooks.
     */
    private function init_plugin() {
        new LI_Post_Types();
        new LI_Admin();
        new LI_Public();
        new LI_Elementor();
        new LI_Api();
        
        
        // Hooks de ativação/desativação
        register_activation_hook(__FILE__, ['LI_Post_Types', 'plugin_activation']);
        register_deactivation_hook(__FILE__, 'flush_rewrite_rules');
    }
}

/**
 * A função que inicializa o plugin.
 */
function lista_imoveis_run() {
    return Lista_Imoveis::instance();
}

// Vamos começar!
lista_imoveis_run();