<?php
/**
 * Define a Tag Dinâmica do Elementor para campos de texto de imóveis.
 */
if (!defined('ABSPATH')) exit;

use Elementor\Controls_Manager;
use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module as TagsModule;

class LI_Imovel_Text_Tag extends Tag {

    public function get_name() { return 'imovel-text-data'; }
    public function get_title() { return 'Campo do Imóvel (Texto)'; }
    public function get_group() { return 'lista-imoveis-grupo'; }
    public function get_categories() { return [TagsModule::TEXT_CATEGORY, TagsModule::NUMBER_CATEGORY, TagsModule::URL_CATEGORY]; }
    
    // ATUALIZADO: Lista de campos agora está completa.
    protected function get_imovel_fields() {
        return [
            '' => '--- Selecione um Campo ---',
            'post_title' => 'Título do Imóvel',
            'post_content' => 'Descrição Principal (Conteúdo)',
            'permalink' => 'Link (URL) do Imóvel',
            'finalidades' => 'Lista de Finalidades',
            '--dados' => '--- Dados Principais ---',
            'li_codigo_referencia' => 'Código de Referência',
            'li_valor_venda' => 'Valor de Venda (Formatado)',
            'li_valor_aluguel' => 'Valor de Aluguel (Formatado)',
            'li_area_total' => 'Área Total (m²)',
            'li_area_construida' => 'Área Construída (m²)',
            'li_quartos' => 'Quartos',
            'li_banheiros' => 'Banheiros',
            'li_vagas_garagem' => 'Vagas na Garagem',
            'li_ano_construcao' => 'Ano de Construção',
            'li_disponibilidade' => 'Disponibilidade',
            'li_estado_conservacao' => 'Estado de Conservação',
            'li_piso' => 'Piso',
            'li_andares' => 'Número de Andares',
            '--endereco' => '--- Endereço ---',
            'full_address' => 'Endereço Completo (Formatado)',
            'address_bairro_cidade_uf' => 'Endereço (Bairro, Cidade - UF)',
            'li_rua' => 'Endereço (Apenas Rua e Número)',
            'li_bairro' => 'Endereço (Apenas Bairro)',
            'li_cidade' => 'Endereço (Apenas Cidade)',
            'li_estado' => 'Endereço (Apenas Estado)',
            'li_cep' => 'Endereço (Apenas CEP)',
            '--caracteristicas' => '--- Características (Sim/Não) ---',
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

    public function _register_controls() {
        $this->add_control('source', ['label' => 'Fonte', 'type' => Controls_Manager::SELECT, 'default' => 'current_post', 'options' => ['current_post' => 'Imóvel Atual (p/ Theme Builder)', 'manual_selection' => 'Seleção Manual']]);
        $this->add_control('imovel_id', ['label' => 'Imóvel', 'type' => Controls_Manager::SELECT2, 'options' => \LI_Elementor_Helper::get_imoveis_list(), 'condition' => ['source' => 'manual_selection']]);
        $this->add_control('imovel_field', ['label' => 'Campo', 'type' => Controls_Manager::SELECT, 'options' => $this->get_imovel_fields()]);
    }

    public function render() {
        $source = $this->get_settings('source');
        $field_key = $this->get_settings('imovel_field');
        $imovel_id = ('manual_selection' === $source && !empty($this->get_settings('imovel_id'))) ? $this->get_settings('imovel_id') : get_the_ID();

        if (empty($imovel_id) || empty($field_key) || get_post_type($imovel_id) !== 'imovel') { return; }

        $value = '';
        
        if (strpos($field_key, 'li_possui_') === 0) { $value = (get_post_meta($imovel_id, $field_key, true) == '1') ? 'Sim' : 'Não'; } 
        elseif (in_array($field_key, ['post_title', 'post_content', 'post_excerpt'])) { $post = get_post($imovel_id); $value = $post->$field_key; }
        elseif ($field_key === 'permalink') { $value = get_permalink($imovel_id); }
        elseif ($field_key === 'finalidades') { $terms = get_the_terms($imovel_id, 'finalidade'); if ($terms && !is_wp_error($terms)) { $value = implode(', ', wp_list_pluck($terms, 'name')); } } 
        elseif ($field_key === 'li_valor_venda' || $field_key === 'li_valor_aluguel') { $raw_value = get_post_meta($imovel_id, str_replace('_formatado', '', $field_key), true); if ($float_value = floatval($raw_value)) { $value = 'R$ ' . number_format($float_value, 2, ',', '.'); } }
        elseif ($field_key === 'full_address') { $rua = get_post_meta($imovel_id, 'li_rua', true); $bairro = get_post_meta($imovel_id, 'li_bairro', true); $cidade = get_post_meta($imovel_id, 'li_cidade', true); $estado = get_post_meta($imovel_id, 'li_estado', true); $cep = get_post_meta($imovel_id, 'li_cep', true); $address_parts = array_filter([$rua, $bairro, $cidade, $estado]); $value = implode(', ', $address_parts); if($cep) $value .= ' - CEP: ' . $cep; }
        elseif ($field_key === 'address_bairro_cidade_uf') { $bairro = get_post_meta($imovel_id, 'li_bairro', true); $cidade = get_post_meta($imovel_id, 'li_cidade', true); $estado = get_post_meta($imovel_id, 'li_estado', true); $address_parts = array_filter([$bairro, $cidade]); $value = implode(', ', $address_parts); if($estado) $value .= ' - ' . $estado; }
        else { $value = get_post_meta($imovel_id, $field_key, true); }

        echo wp_kses_post($value);
    }
}