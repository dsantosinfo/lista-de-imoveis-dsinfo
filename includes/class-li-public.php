<?php
/**
 * Classe responsável pela funcionalidade pública (frontend) do plugin.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Public {

    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_public_assets']);
        add_filter('template_include', [$this, 'include_custom_templates']);
    }

    /**
     * Enfileira os scripts e estilos para as páginas de imóveis.
     */
    public function enqueue_public_assets() {
        // Estilos que carregam em todas as páginas de imóveis
        if (is_post_type_archive('imovel') || is_tax('finalidade') || is_singular('imovel')) {
            wp_enqueue_style('li-public-style', LI_PLUGIN_URL . 'public/css/public-style.css', [], LI_PLUGIN_VERSION);
        }

        // REMOVIDO: Não precisamos mais carregar nenhum script na página de single
        // A funcionalidade de lightbox será gerenciada pelo Elementor ou pelo tema.
    }

    /**
     * Injeta os templates do plugin se a opção estiver ativa e o tema não possuir os seus.
     */
    public function include_custom_templates($template) {
        $templates_enabled = get_option('li_enable_plugin_templates', 1);
        if (!$templates_enabled) {
            return $template;
        }

        if (class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->preview->is_preview_mode()) {
            return $template;
        }
        
        if (is_post_type_archive('imovel') || is_tax('finalidade')) {
            $theme_file = locate_template(['archive-imovel.php']);
            if (empty($theme_file)) {
                $template = LI_PLUGIN_PATH . 'templates/archive-imovel.php';
            }
        }
        
        if (is_singular('imovel')) {
            $theme_file = locate_template(['single-imovel.php']);
            if (empty($theme_file)) {
                $template = LI_PLUGIN_PATH . 'templates/single-imovel.php';
            }
        }

        return $template;
    }
}