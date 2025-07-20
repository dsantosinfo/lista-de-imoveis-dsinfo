<?php
/**
 * Widget Elementor: Galeria de Fotos do Imóvel
 * Exibe as imagens da galeria de um imóvel com funcionalidade de lightbox.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Galeria_Imovel_Widget extends \Elementor\Widget_Base {

    public function get_name() {
        return 'li-galeria-imovel';
    }

    public function get_title() {
        return 'Galeria de Fotos do Imóvel';
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return ['lista-imoveis-categoria'];
    }

    protected function _register_controls() {
        // --- Seção de Conteúdo ---
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
        $this->start_controls_section('style_section', ['label' => 'Estilo da Grade', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('columns', [
            'label' => 'Colunas', 'type' => \Elementor\Controls_Manager::SELECT, 'default' => '4',
            'options' => ['1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5', '6' => '6'],
            'selectors' => ['{{WRAPPER}} .li-galeria-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);']
        ]);
        $this->add_responsive_control('gap', [
            'label' => 'Espaçamento (px)', 'type' => \Elementor\Controls_Manager::SLIDER, 'default' => ['size' => 10],
            'range' => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => ['{{WRAPPER}} .li-galeria-grid' => 'gap: {{SIZE}}{{UNIT}};']
        ]);
        $this->add_control('image_border_radius', [
            'label' => 'Raio da Borda (px)', 'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => ['{{WRAPPER}} .li-galeria-grid img' => 'border-radius: {{SIZE}}{{UNIT}};']
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

        // === INÍCIO DA LÓGICA DE PRÉ-VISUALIZAÇÃO INTELIGENTE ===
        if (empty($imovel_id) && \Elementor\Plugin::$instance->editor->is_edit_mode()) {
            $latest_imovel = get_posts([
                'post_type' => 'imovel', 'posts_per_page' => 1, 'orderby' => 'date',
                'order' => 'DESC', 'fields' => 'ids',
            ]);
            if (!empty($latest_imovel)) {
                $imovel_id = $latest_imovel[0];
                echo '<div class="elementor-alert elementor-alert-info">Exibindo dados do imóvel mais recente como exemplo.</div>';
            }
        }
        // === FIM DA LÓGICA DE PRÉ-VISUALIZAÇÃO INTELIGENTE ===

        if (empty($imovel_id) || 'imovel' !== get_post_type($imovel_id)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="elementor-alert elementor-alert-warning">Nenhum imóvel encontrado para exibir.</div>';
            }
            return;
        }

        $gallery_ids_str = get_post_meta($imovel_id, 'li_galeria_ids', true);
        if (empty($gallery_ids_str)) {
            if (\Elementor\Plugin::$instance->editor->is_edit_mode()) {
                echo '<div class="elementor-alert elementor-alert-info">Nenhuma imagem na galeria para este imóvel.</div>';
            }
            return;
        }
        
        $gallery_ids = explode(',', $gallery_ids_str);

        echo '<div class="li-galeria-grid">';
        foreach ($gallery_ids as $id) {
            $full_url = wp_get_attachment_image_url($id, 'full');
            if ($full_url) {
                // Atributos para o lightbox do Elementor
                $this->add_render_attribute('gallery-item-link-' . $id, [
                    'href' => esc_url($full_url),
                    'data-elementor-open-lightbox' => 'yes',
                    'data-elementor-lightbox-gallery' => 'imovel-gallery-'. $imovel_id,
                ]);
                
                echo '<a ' . $this->get_render_attribute_string('gallery-item-link-' . $id) . '>';
                // OTIMIZAÇÃO: Usando wp_get_attachment_image() para performance e acessibilidade
                echo wp_get_attachment_image($id, 'large');
                echo '</a>';
            }
        }
        echo '</div>';
    }
}