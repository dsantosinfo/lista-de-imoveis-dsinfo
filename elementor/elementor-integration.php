<?php
/**
 * Ponto de entrada para toda a integração do plugin com o Elementor.
 * Carrega e registra as Tags Dinâmicas e os Widgets customizados.
 */
if (!defined('ABSPATH')) {
    exit;
}

final class LI_Elementor_Integration {

    private static $_instance = null;

    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        // Carrega a classe Helper primeiro para que fique disponível para tags e widgets
        require_once(__DIR__ . '/class-li-elementor-helper.php');

        // Registra os componentes do Elementor
        add_action('elementor/dynamic_tags/register', [$this, 'register_dynamic_tags']);
        add_action('elementor/elements/categories_registered', [$this, 'add_widget_category']);
        add_action('elementor/widgets/register', [$this, 'register_widgets']);
    }

    /**
     * Registra as Tags Dinâmicas.
     */
    public function register_dynamic_tags(\Elementor\Core\DynamicTags\Manager $dynamic_tags_manager) {
        require_once(__DIR__ . '/tags/imovel-text-tag.php');
        require_once(__DIR__ . '/tags/imovel-image-tag.php');

        $dynamic_tags_manager->register_group('lista-imoveis-grupo', [
            'title' => 'Dados do Imóvel'
        ]);

        $dynamic_tags_manager->register(new \LI_Imovel_Text_Tag());
        $dynamic_tags_manager->register(new \LI_Imovel_Image_Tag());
    }

    /**
     * Adiciona a categoria "Imóveis" no painel de widgets do Elementor.
     */
    public function add_widget_category(\Elementor\Elements_Manager $elements_manager) {
        $elements_manager->add_category(
            'lista-imoveis-categoria',
            [
                'title' => 'Imóveis',
                'icon' => 'eicon-home',
            ]
        );
    }
    
    /**
     * Carrega e registra os widgets customizados.
     */
    public function register_widgets(\Elementor\Widgets_Manager $widgets_manager) {
        // ATUALIZADO: Lista de todos os nossos widgets
        $widget_files = [
            'widget-detalhes-personalizados.php', // O antigo 'ficha-tecnica' renomeado
            'widget-info-principais.php',         // Novo widget
            'widget-caracteristicas.php',         // Novo widget
            'widget-galeria-imovel.php',
        ];

        foreach ($widget_files as $file) {
            $path = __DIR__ . '/widgets/' . $file;
            if (file_exists($path)) {
                require_once $path;
            }
        }
        
        // ATUALIZADO: Registra todas as classes de widget
        if (class_exists('LI_Detalhes_Personalizados_Widget')) { $widgets_manager->register(new \LI_Detalhes_Personalizados_Widget()); }
        if (class_exists('LI_Info_Principais_Widget')) { $widgets_manager->register(new \LI_Info_Principais_Widget()); }
        if (class_exists('LI_Caracteristicas_Widget')) { $widgets_manager->register(new \LI_Caracteristicas_Widget()); }
        if (class_exists('LI_Galeria_Imovel_Widget')) { $widgets_manager->register(new \LI_Galeria_Imovel_Widget()); }
    }
}

// Inicializa a integração
LI_Elementor_Integration::instance();