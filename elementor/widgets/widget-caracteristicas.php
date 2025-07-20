<?php
/**
 * Widget Elementor: Características do Imóvel
 * Exibe uma lista de características (comodidades) que o imóvel possui.
 */
if (!defined('ABSPATH')) exit;

class LI_Caracteristicas_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'li-caracteristicas'; }
    public function get_title() { return 'Características do Imóvel'; }
    public function get_icon() { return 'eicon-check-circle-o'; }
    public function get_categories() { return ['lista-imoveis-categoria']; }

    protected function _register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Fonte de Dados']);
        $this->add_control('source', ['label' => 'Fonte', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'current_post', 'options' => ['current_post' => 'Imóvel Atual (Theme Builder)', 'manual_selection' => 'Seleção Manual']]);
        $this->add_control('imovel_id', ['label' => 'Imóvel', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => \LI_Elementor_Helper::get_imoveis_list(), 'condition' => ['source' => 'manual_selection']]);
        $this->end_controls_section();

        $this->start_controls_section('style_section', ['label' => 'Estilo da Lista', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('columns_char', ['label' => 'Colunas', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => '2', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4'], 'selectors' => ['{{WRAPPER}} .li-caracteristicas-lista' => 'grid-template-columns: repeat({{VALUE}}, 1fr);']]);
        $this->add_control('icon_color_char', ['label' => 'Cor do Ícone', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .li-caracteristica-item i' => 'color: {{VALUE}};']]);
        $this->add_control('text_color_char', ['label' => 'Cor do Texto', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .li-caracteristica-item span' => 'color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'typography_char', 'selector' => '{{WRAPPER}} .li-caracteristica-item span']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $imovel_id = ('manual_selection' === $settings['source'] && !empty($settings['imovel_id'])) ? $settings['imovel_id'] : get_the_ID();

        if (empty($imovel_id) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $latest_imovel = get_posts(['post_type' => 'imovel', 'posts_per_page' => 1, 'fields' => 'ids']);
            if (!empty($latest_imovel)) { $imovel_id = $latest_imovel[0]; }
        }

        if (empty($imovel_id) || 'imovel' !== get_post_type($imovel_id)) { return; }
        
        // ATUALIZADO: Lista completa com todas as 17 características.
        $caracteristicas = [ 
            'li_possui_piscina' => 'Piscina', 
            'li_possui_churrasqueira' => 'Churrasqueira', 
            'li_possui_elevador' => 'Elevador', 
            'li_possui_jardim' => 'Jardim', 
            'li_possui_varanda' => 'Varanda', 
            'li_possui_area_lazer' => 'Área de Lazer', 
            'li_possui_mobilia' => 'Mobiliado', 
            'li_possui_ar_condicionado' => 'Ar Condicionado', 
            'li_possui_sistema_seguranca' => 'Sistema de Segurança',
            'li_possui_area_servico' => 'Área de Serviço', 
            'li_possui_sala_estar' => 'Sala de Estar',
            'li_possui_sala_jantar' => 'Sala de Jantar',
            'li_possui_cozinha' => 'Cozinha', 
            'li_possui_lavabo' => 'Lavabo',
            'li_possui_escritorio' => 'Escritório', 
            'li_possui_deposito' => 'Depósito',
            'li_possui_acesso_deficientes' => 'Acesso para Deficientes'
        ];

        $items_html = '';
        foreach ($caracteristicas as $key => $label) {
            $value = get_post_meta($imovel_id, $key, true);
            if ($value === '1') {
                $items_html .= '<div class="li-caracteristica-item"><i class="fas fa-check"></i> <span>' . esc_html($label) . '</span></div>';
            }
        }

        if (!empty($items_html)) {
            echo '<div class="li-caracteristicas-lista">' . $items_html . '</div>';
        } elseif (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
            echo '<div class="elementor-alert elementor-alert-info">Nenhuma característica selecionada para este imóvel.</div>';
        }
    }
}