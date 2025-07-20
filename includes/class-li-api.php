<?php
/**
 * Classe responsável por registrar os endpoints da API REST do plugin.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Api {

    protected $namespace = 'lista-imoveis/v1';

    public function __construct() {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Registra todas as rotas da API.
     */
    public function register_routes() {
        // Rota para LER um único imóvel
        register_rest_route($this->namespace, '/imovel/(?P<id>\d+)', [
            'methods'  => WP_REST_Server::READABLE, // GET
            'callback' => [$this, 'get_imovel'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Rota para CRIAR um imóvel
        register_rest_route($this->namespace, '/imovel', [
            'methods'  => WP_REST_Server::CREATABLE, // POST
            'callback' => [$this, 'create_imovel'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Rota para ATUALIZAR um imóvel
        register_rest_route($this->namespace, '/imovel/(?P<id>\d+)', [
            'methods'  => WP_REST_Server::EDITABLE, // POST, PUT, PATCH
            'callback' => [$this, 'update_imovel'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);

        // Rota para DELETAR um imóvel
        register_rest_route($this->namespace, '/imovel/(?P<id>\d+)', [
            'methods'  => WP_REST_Server::DELETABLE, // DELETE
            'callback' => [$this, 'delete_imovel'],
            'permission_callback' => [$this, 'permissions_check'],
        ]);
    }

    /**
     * Verificação de permissão para todas as rotas.
     * Permite acesso apenas a usuários que podem editar posts.
     */
    public function permissions_check() {
        return current_user_can('edit_posts');
    }

    /**
     * Callback para buscar um único imóvel.
     */
    public function get_imovel(WP_REST_Request $request) {
        $id = (int) $request['id'];
        $post = get_post($id);

        if (empty($post) || $post->post_type !== 'imovel') {
            return new WP_Error('rest_post_not_found', 'Imóvel não encontrado.', ['status' => 404]);
        }

        return new WP_REST_Response($this->prepare_imovel_for_response($post), 200);
    }

    /**
     * Callback para criar um imóvel.
     */
    public function create_imovel(WP_REST_Request $request) {
        $params = $request->get_params();
        $post_args = [
            'post_type'    => 'imovel',
            'post_status'  => 'publish',
            'post_title'   => sanitize_text_field($params['titulo'] ?? 'Novo Imóvel'),
            'post_content' => sanitize_textarea_field($params['descricao'] ?? ''),
        ];

        $post_id = wp_insert_post($post_args, true);
        if (is_wp_error($post_id)) {
            return $post_id;
        }

        $this->update_imovel_meta($post_id, $params);
        return new WP_REST_Response(['id' => $post_id, 'message' => 'Imóvel criado com sucesso.'], 201);
    }

    /**
     * Callback para atualizar um imóvel.
     */
    public function update_imovel(WP_REST_Request $request) {
        $id = (int) $request['id'];
        $post = get_post($id);
        if (empty($post) || $post->post_type !== 'imovel') {
            return new WP_Error('rest_post_not_found', 'Imóvel não encontrado.', ['status' => 404]);
        }

        $params = $request->get_params();
        $post_args = [
            'ID' => $id,
        ];
        if (isset($params['titulo'])) $post_args['post_title'] = sanitize_text_field($params['titulo']);
        if (isset($params['descricao'])) $post_args['post_content'] = sanitize_textarea_field($params['descricao']);

        wp_update_post($post_args, true);
        $this->update_imovel_meta($id, $params);

        return new WP_REST_Response(['id' => $id, 'message' => 'Imóvel atualizado com sucesso.'], 200);
    }

    /**
     * Callback para deletar um imóvel.
     */
    public function delete_imovel(WP_REST_Request $request) {
        $id = (int) $request['id'];
        $result = wp_delete_post($id, true); // Força a exclusão

        if (!$result) {
            return new WP_Error('rest_cannot_delete', 'Não foi possível deletar o imóvel.', ['status' => 500]);
        }
        return new WP_REST_Response(['message' => 'Imóvel deletado com sucesso.'], 200);
    }
    
    /**
     * Helper para atualizar os metadados a partir dos parâmetros da requisição.
     */
    private function update_imovel_meta($post_id, $params) {
        $meta_fields = ['li_codigo_referencia', 'li_area_total', 'li_quartos', 'li_banheiros', 'li_vagas_garagem', 'li_valor_venda', 'li_valor_aluguel', 'li_rua', 'li_bairro', 'li_cidade', 'li_estado', 'li_cep'];
        foreach($meta_fields as $field) {
            if (isset($params[$field])) {
                update_post_meta($post_id, $field, sanitize_text_field($params[$field]));
            }
        }
    }

    /**
     * Helper para formatar os dados de um imóvel para a resposta da API.
     */
    private function prepare_imovel_for_response($post) {
        $meta = get_post_meta($post->ID);
        $data = [
            'id' => $post->ID,
            'titulo' => $post->post_title,
            'descricao' => $post->post_content,
            'link' => get_permalink($post->ID),
            'imagem_destacada' => get_the_post_thumbnail_url($post->ID, 'full'),
            'dados_customizados' => [],
        ];
        $meta_fields = ['li_codigo_referencia', 'li_area_total', 'li_quartos', 'li_banheiros', 'li_vagas_garagem', 'li_valor_venda', 'li_valor_aluguel', 'li_rua', 'li_bairro', 'li_cidade', 'li_estado', 'li_cep'];
        foreach($meta_fields as $field) {
            $data['dados_customizados'][$field] = $meta[$field][0] ?? null;
        }
        return $data;
    }
}