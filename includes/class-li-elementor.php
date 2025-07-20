<?php
/**
 * Classe responsável por inicializar a integração com o Elementor.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Elementor {

    /**
     * Construtor. Adiciona o hook para carregar a integração.
     */
    public function __construct() {
        add_action('init', [$this, 'register_elementor_integration']);
    }

    /**
     * Verifica se o Elementor está ativo e carrega o arquivo de integração.
     */
    public function register_elementor_integration() {
        if (did_action('elementor/loaded')) {
            require_once LI_PLUGIN_PATH . 'elementor/elementor-integration.php';
        }
    }
}