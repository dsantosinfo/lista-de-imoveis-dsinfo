<?php
/**
 * Classe responsável por registrar o Custom Post Type e Taxonomias do plugin.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Post_Types {

    /**
     * Construtor. Adiciona os hooks para registro.
     */
    public function __construct() {
        add_action('init', [$this, 'register_cpt_imovel']);
        add_action('init', [$this, 'register_taxonomy_finalidade']);
    }

    /**
     * Ação executada na ativação do plugin.
     * Garante que o CPT e a taxonomia sejam registrados e as regras de URL atualizadas.
     */
    public static function plugin_activation() {
        // Instancia a classe para ter acesso aos métodos de registro
        $instance = new self();
        $instance->register_cpt_imovel();
        $instance->register_taxonomy_finalidade();
        
        // Atualiza as regras de reescrita do WordPress
        flush_rewrite_rules();
    }

    /**
     * Registra o Custom Post Type 'imovel'.
     */
    public function register_cpt_imovel() {
        $labels = [
            'name'                  => 'Imóveis',
            'singular_name'         => 'Imóvel',
            'menu_name'             => 'Imóveis',
            'add_new'               => 'Adicionar Novo',
            'add_new_item'          => 'Adicionar Novo Imóvel',
            'edit_item'             => 'Editar Imóvel',
            'new_item'              => 'Novo Imóvel',
            'view_item'             => 'Ver Imóvel',
            'view_items'            => 'Ver Imóveis',
            'search_items'          => 'Buscar Imóveis',
            'not_found'             => 'Nenhum imóvel encontrado',
            'not_found_in_trash'    => 'Nenhum imóvel encontrado na lixeira',
            'all_items'             => 'Todos os Imóveis',
        ];
        $args = [
            'labels'        => $labels,
            'public'        => true,
            'has_archive'   => 'imoveis',
            'show_in_rest'  => true,
            'menu_icon'     => 'dashicons-admin-home',
            'supports'      => ['title', 'editor', 'thumbnail', 'excerpt', 'revisions'],
            'rewrite'       => ['slug' => 'imoveis', 'with_front' => false],
        ];
        register_post_type('imovel', $args);
    }

    /**
     * Registra a Taxonomia 'finalidade'.
     */
    public function register_taxonomy_finalidade() {
        $labels = [
            'name'              => 'Finalidades',
            'singular_name'     => 'Finalidade',
            'search_items'      => 'Buscar Finalidades',
            'all_items'         => 'Todas as Finalidades',
            'parent_item'       => 'Finalidade Pai',
            'parent_item_colon' => 'Finalidade Pai:',
            'edit_item'         => 'Editar Finalidade',
            'update_item'       => 'Atualizar Finalidade',
            'add_new_item'      => 'Adicionar Nova Finalidade',
            'new_item_name'     => 'Nome da Nova Finalidade',
            'menu_name'         => 'Finalidades',
        ];
        $args = [
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => true,
            'show_in_rest'      => true,
            'rewrite'           => ['slug' => 'finalidade'],
        ];
        register_taxonomy('finalidade', ['imovel'], $args);
    }
}