<?php
/**
 * Widget Elementor: Detalhes do Imóvel (Personalizado com Repetidor)
 */
if (!defined('ABSPATH')) exit;

class LI_Detalhes_Personalizados_Widget extends \Elementor\Widget_Base {

    public function get_name() { return 'li-detalhes-personalizados'; }
    public function get_title() { return 'Detalhes do Imóvel (Personalizado)'; }
    public function get_icon() { return 'eicon-bullet-list'; }
    public function get_categories() { return ['lista-imoveis-categoria']; }

    /**
     * ATUALIZADO: Retorna a lista COMPLETA de campos disponíveis para o repetidor.
     */
    private function get_ficha_tecnica_fields() {
        return [
            '--dados' => '--- Dados Principais ---',
            'li_quartos' => 'Quartos',
            'li_banheiros' => 'Banheiros',
            'li_vagas_garagem' => 'Vagas na Garagem',
            'li_area_construida' => 'Área Construída',
            'li_area_total' => 'Área Total',
            'li_ano_construcao' => 'Ano de Construção',
            'li_piso' => 'Piso',
            'li_andares' => 'Número de Andares',
            '--caracteristicas' => '--- Características ---',
            'li_possui_elevador' => 'Possui Elevador',
            'li_possui_area_servico' => 'Possui Área de Serviço',
            'li_possui_varanda' => 'Possui Varanda',
            'li_possui_jardim' => 'Possui Jardim',
            'li_possui_piscina' => 'Possui Piscina',
            'li_possui_churrasqueira' => 'Possui Churrasqueira',
            'li_possui_sala_estar' => 'Possui Sala de Estar',
            'li_possui_sala_jantar' => 'Possui Sala de Jantar',
            'li_possui_cozinha' => 'Possui Cozinha',
            'li_possui_lavabo' => 'Possui Lavabo',
            'li_possui_escritorio' => 'Possui Escritório',
            'li_possui_deposito' => 'Possui Depósito',
            'li_possui_area_lazer' => 'Possui Área de Lazer',
            'li_possui_acesso_deficientes' => 'Possui Acesso p/ Deficientes',
            'li_possui_sistema_seguranca' => 'Possui Sistema de Segurança',
            'li_possui_ar_condicionado' => 'Possui Ar Condicionado',
            'li_possui_mobilia' => 'Possui Mobília',
        ];
    }
    
    protected function _register_controls() {
        $this->start_controls_section('content_section', ['label' => 'Fonte de Dados']);
        $this->add_control('source', ['label' => 'Fonte', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => 'current_post', 'options' => ['current_post' => 'Imóvel Atual (Theme Builder)', 'manual_selection' => 'Seleção Manual']]);
        $this->add_control('imovel_id', ['label' => 'Imóvel', 'type' => \Elementor\Controls_Manager::SELECT2, 'options' => \LI_Elementor_Helper::get_imoveis_list(), 'condition' => ['source' => 'manual_selection']]);
        $this->end_controls_section();

        $this->start_controls_section('items_section', ['label' => 'Itens da Ficha Técnica']);
        $repeater = new \Elementor\Repeater();
        $repeater->add_control('dado_imovel', ['label' => 'Dado do Imóvel', 'type' => \Elementor\Controls_Manager::SELECT, 'options' => $this->get_ficha_tecnica_fields(), 'default' => 'li_quartos']);
        $repeater->add_control('rotulo_personalizado', ['label' => 'Rótulo Personalizado', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'Ex: Dormitórios']);
        $repeater->add_control('icone', ['label' => 'Ícone', 'type' => \Elementor\Controls_Manager::ICONS, 'default' => ['value' => 'fas fa-check', 'library' => 'solid']]);
        $repeater->add_control('sufixo', ['label' => 'Sufixo do Valor', 'type' => \Elementor\Controls_Manager::TEXT, 'placeholder' => 'Ex: m²']);
        $this->add_control('lista_caracteristicas', [
            'label' => 'Características a Exibir', 'type' => \Elementor\Controls_Manager::REPEATER, 'fields' => $repeater->get_controls(),
            'default' => [
                ['dado_imovel' => 'li_quartos', 'icone' => ['value' => 'fas fa-bed', 'library' => 'solid']],
                ['dado_imovel' => 'li_banheiros', 'icone' => ['value' => 'fas fa-bath', 'library' => 'solid']],
                ['dado_imovel' => 'li_vagas_garagem', 'icone' => ['value' => 'fas fa-car', 'library' => 'solid']],
                ['dado_imovel' => 'li_area_construida', 'icone' => ['value' => 'fas fa-ruler-combined', 'library' => 'solid'], 'sufixo' => ' m²'],
            ],
            'title_field' => '{{{ rotulo_personalizado || dado_imovel.replace(/li_|_/g, " ").replace(/possui/g, "").trim().replace(/\b\w/g, l => l.toUpperCase()) }}}',
        ]);
        $this->end_controls_section();

        $this->start_controls_section('style_section', ['label' => 'Estilo', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('columns', ['label' => 'Colunas', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => '4', 'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4'], 'selectors' => ['{{WRAPPER}} .li-ficha-tecnica' => 'grid-template-columns: repeat({{VALUE}}, 1fr);']]);
        $this->add_control('icon_color', ['label' => 'Cor do Ícone', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .li-ficha-item i' => 'color: {{VALUE}};', '{{WRAPPER}} .li-ficha-item svg' => 'fill: {{VALUE}};']]);
        $this->add_control('label_color', ['label' => 'Cor do Rótulo', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .li-ficha-item-label' => 'color: {{VALUE}};']]);
        $this->add_control('value_color', ['label' => 'Cor do Valor', 'type' => \Elementor\Controls_Manager::COLOR, 'selectors' => ['{{WRAPPER}} .li-ficha-item-value' => 'color: {{VALUE}};']]);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'label_typography', 'label' => 'Tipografia do Rótulo', 'selector' => '{{WRAPPER}} .li-ficha-item-label']);
        $this->add_group_control(\Elementor\Group_Control_Typography::get_type(), ['name' => 'value_typography', 'label' => 'Tipografia do Valor', 'selector' => '{{WRAPPER}} .li-ficha-item-value']);
        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();
        $imovel_id = ('manual_selection' === $settings['source'] && !empty($settings['imovel_id'])) ? $settings['imovel_id'] : get_the_ID();

        if (empty($imovel_id) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $latest_imovel = get_posts(['post_type' => 'imovel', 'posts_per_page' => 1, 'orderby' => 'date', 'order' => 'DESC', 'fields' => 'ids']);
            if (!empty($latest_imovel)) { $imovel_id = $latest_imovel[0]; echo '<div class="elementor-alert elementor-alert-info">Exibindo dados do imóvel mais recente como exemplo.</div>'; }
        }

        if (empty($imovel_id) || 'imovel' !== get_post_type($imovel_id)) { if (\Elementor\Plugin::$instance->editor->is_edit_mode()) { echo '<div class="elementor-alert elementor-alert-warning">Nenhum imóvel encontrado para exibir.</div>'; } return; }

        $lista = $settings['lista_caracteristicas'];
        if (empty($lista)) return;
        
        $todos_os_campos = $this->get_ficha_tecnica_fields();

        echo '<div class="li-ficha-tecnica">';
        foreach ($lista as $item) {
            $key = $item['dado_imovel'];
            $value = get_post_meta($imovel_id, $key, true);

            if (empty($value) || $value === '0') { continue; }

            $label = !empty($item['rotulo_personalizado']) ? $item['rotulo_personalizado'] : ($todos_os_campos[$key] ?? '');
            
            echo '<div class="li-ficha-item elementor-repeater-item-' . esc_attr($item['_id']) . '">';
            \Elementor\Icons_Manager::render_icon($item['icone'], ['aria-hidden' => 'true']);
            echo '<div class="li-ficha-text">';
            
            if (strpos($key, 'li_possui_') === 0) {
                echo '<span class="li-ficha-item-value">' . esc_html(str_replace('Possui ', '', $label)) . '</span>';
            } else {
                echo '<span class="li-ficha-item-label">' . esc_html($label) . '</span>';
                echo '<span class="li-ficha-item-value">' . esc_html($value) . esc_html($item['sufixo']) . '</span>';
            }
            
            echo '</div></div>';
        }
        echo '</div>';
    }
}