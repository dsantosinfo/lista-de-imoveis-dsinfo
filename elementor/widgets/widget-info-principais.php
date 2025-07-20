<?php
/**
 * Widget Elementor: Informações Principais do Imóvel
 * Exibe uma lista com dados chave como código, disponibilidade e conservação.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Info_Principais_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'li-info-principais';
    }

    public function get_title() {
        return 'Informações Principais do Imóvel';
    }

    public function get_icon() {
        return 'eicon-price-list';
    }

    public function get_categories() {
        return ['lista-imoveis-categoria'];
    }

    protected function _register_controls() {
        // --- Seção de Fonte de Dados ---
        $this->start_controls_section('content_section', ['label' => 'Fonte de Dados']);
        $this->add_control('source', [
            'label' => 'Fonte',
            'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'current_post',
            'options' => [
                'current_post' => 'Imóvel Atual (Theme Builder)',
                'manual_selection' => 'Seleção Manual',
            ]
        ]);
        $this->add_control('imovel_id', [
            'label' => 'Imóvel',
            'type' => \Elementor\Controls_Manager::SELECT2,
            'options' => \LI_Elementor_Helper::get_imoveis_list(),
            'condition' => ['source' => 'manual_selection']
        ]);
        $this->end_controls_section();

        // --- Seção de Estilo ---
        $this->start_controls_section('style_section', ['label' => 'Estilo', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_control('label_color_info', [
            'label' => 'Cor do Rótulo',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .li-info-item strong' => 'color: {{VALUE}};']
        ]);
        $this->add_control('value_color_info', [
            'label' => 'Cor do Valor',
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .li-info-item span' => 'color: {{VALUE}};']
        ]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), [
            'name' => 'typography_info',
            'selector' => '{{WRAPPER}} .li-info-item'
        ]);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $imovel_id = 0;

        if ('manual_selection' === $settings['source'] && !empty($settings['imovel_id'])) {
            $imovel_id = $settings['imovel_id'];
        } else {
            $imovel_id = get_the_ID();
        }

        // Lógica de Pré-visualização Inteligente
        if (empty($imovel_id) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $latest_imovel = get_posts(['post_type' => 'imovel', 'posts_per_page' => 1, 'fields' => 'ids']);
            if (!empty($latest_imovel)) {
                $imovel_id = $latest_imovel[0];
            }
        }

        if (empty($imovel_id) || 'imovel' !== get_post_type($imovel_id)) {
            return;
        }
        
        // Lista fixa de campos a serem exibidos por este widget
        $fields = [
            'li_codigo_referencia' => 'Referência',
            'li_disponibilidade' => 'Disponibilidade',
            'li_estado_conservacao' => 'Conservação',
        ];

        echo '<div class="li-info-principais">';
        foreach ($fields as $key => $label) {
            $value = get_post_meta($imovel_id, $key, true);
            if (!empty($value)) {
                echo '<div class="li-info-item"><strong>' . esc_html($label) . ':</strong> <span>' . esc_html($value) . '</span></div>';
            }
        }
        echo '</div>';
    }
}