<?php
/**
 * Classe responsável por registrar os endpoints da API REST do plugin.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Api {

    public function __construct() {
        // Mantenha este hook para registrar os campos customizados na API padrão.
        // Garante que o método 'register_custom_fields_to_api' é chamado no momento certo.
        add_action('rest_api_init', [$this, 'register_custom_fields_to_api']);
    }

    /**
     * Helper para atualizar os metadados a partir dos parâmetros da requisição.
     * Esta função é o 'update_callback' para os campos customizados
     * registrados no endpoint padrão do WP (/wp/v2/imovel/).
     *
     * @param mixed       $value      O valor do campo recebido da requisição REST.
     * @param WP_Post     $post       O objeto WP_Post (o post que está sendo criado/atualizado).
     * @param string      $field_name O nome do campo (meta_key) que está sendo atualizado.
     * @param WP_REST_Request $request    O objeto da requisição REST completa.
     * @param string      $object_type O tipo de objeto ('post', 'user', etc.).
     * @return bool True se a atualização foi bem-sucedida, false em caso de falha.
     */
    public function update_imovel_meta_callback($value, $post, $field_name, $request, $object_type) {
        $post_id = $post->ID;

        // Lógica para campos booleanos (li_possui_...)
        if (strpos($field_name, 'li_possui_') === 0) {
            update_post_meta($post_id, $field_name, (bool)$value ? '1' : '0');
            return true;
        }

        // Lógica para 'li_galeria_ids' (espera um array de IDs)
        if ($field_name === 'li_galeria_ids') {
            if (is_array($value)) {
                $ids_sanitized = implode(',', array_map('intval', $value));
            } elseif (is_string($value)) {
                $ids_sanitized = implode(',', array_map('intval', explode(',', sanitize_text_field($value))));
            } else {
                $ids_sanitized = ''; // Valor inválido, salva vazio
            }
            update_post_meta($post_id, $field_name, $ids_sanitized);
            return true;
        }

        // Para outros campos de texto/número
        update_post_meta($post_id, $field_name, sanitize_text_field($value));
        return true;
    }

    /**
     * Registra os metadados customizados para exposição na API REST padrão.
     * Isso fará com que os campos apareçam no endpoint /wp/v2/imovel/.
     */
    public function register_custom_fields_to_api() {
        $meta_fields_to_expose = [
            'li_codigo_referencia', 'li_area_total', 'li_quartos', 'li_banheiros', 'li_vagas_garagem',
            'li_valor_venda', 'li_valor_aluguel', 'li_rua', 'li_bairro', 'li_cidade', 'li_estado', 'li_cep',
            'li_area_construida', 'li_ano_construcao', 'li_disponibilidade', 'li_estado_conservacao',
            'li_piso', 'li_andares', 'li_galeria_ids',
            'li_possui_elevador', 'li_possui_area_servico', 'li_possui_varanda', 'li_possui_jardim',
            'li_possui_piscina', 'li_possui_churrasqueira', 'li_possui_sala_estar', 'li_possui_sala_jantar',
            'li_possui_cozinha', 'li_possui_lavabo', 'li_possui_escritorio', 'li_possui_deposito',
            'li_possui_area_lazer', 'li_possui_acesso_deficientes', 'li_possui_sistema_seguranca',
            'li_possui_ar_condicionado', 'li_possui_mobilia'
        ];

        foreach ($meta_fields_to_expose as $field_name) {
            register_rest_field(
                'imovel', // Post Type ao qual o campo pertence
                $field_name,
                [
                    'get_callback'    => function($object) use ($field_name) { // Removi $request pois nem sempre é necessário e pode causar erro se não for injetado
                        // $object é uma array associativa com os dados brutos do post.
                        $value = get_post_meta($object['id'], $field_name, true);
                        
                        if (strpos($field_name, 'li_possui_') === 0) {
                            return ($value === '1');
                        }
                        if ($field_name === 'li_galeria_ids') {
                            return !empty($value) ? array_map('intval', explode(',', $value)) : [];
                        }
                        return $value;
                    },
                    'update_callback' => [$this, 'update_imovel_meta_callback'], 
                    'schema'          => [
                        'type'        => (strpos($field_name, 'li_possui_') === 0) ? 'boolean' : ( ($field_name === 'li_galeria_ids') ? 'array' : 'string' ),
                        'description' => 'Campo customizado do imóvel: ' . str_replace(['li_', '_'], [' ', ' '], $field_name),
                        'context'     => ['view', 'edit'],
                        'arg_options' => [
                            'sanitize_callback' => function($value, $request, $param) use ($field_name) {
                                if (strpos($field_name, 'li_possui_') === 0) {
                                    return (bool)$value;
                                }
                                if ($field_name === 'li_galeria_ids') {
                                    if (is_array($value)) {
                                        return array_map('intval', $value);
                                    }
                                    if (is_string($value)) {
                                        return array_map('intval', explode(',', $value));
                                    }
                                    return [];
                                }
                                return sanitize_text_field($value);
                            },
                            'validate_callback' => function($value, $request, $param) use ($field_name) {
                                if ( (strpos($field_name, 'li_possui_') === 0) && !is_bool($value) && !is_numeric($value) ) return new WP_Error('rest_invalid_param', sprintf('"%s" deve ser um booleano ou 0/1.', $field_name), ['status' => 400]);
                                if ( ($field_name === 'li_galeria_ids') && !is_array($value) && !is_string($value) ) return new WP_Error('rest_invalid_param', sprintf('"%s" deve ser um array de IDs ou string separada por vírgulas.', $field_name), ['status' => 400]);
                                return true;
                            },
                        ],
                    ],
                ]
            );
        }
    }
}