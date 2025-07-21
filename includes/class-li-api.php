<?php
/**
 * Classe responsável por registrar os endpoints da API REST do plugin.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Api {

    public function __construct() {
        // Este hook registra os campos customizados para o CPT 'imovel' na API REST padrão.
        add_action('rest_api_init', [$this, 'register_custom_fields_to_api']);
        // Os endpoints personalizados para 'finalidade' foram removidos,
        // pois o WordPress já oferece rotas padrão para taxonomias com 'show_in_rest' ativado.
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
            // Garante que 'true' ou 'false' (booleanos do JSON) sejam salvos como '1' ou '0'.
            update_post_meta($post_id, $field_name, (bool)$value ? '1' : '0');
            return true;
        }

        // Lógica para 'li_galeria_ids' (espera um array de IDs)
        if ($field_name === 'li_galeria_ids') {
            if (is_array($value)) {
                $ids_sanitized = implode(',', array_map('intval', $value));
            } elseif (is_string($value)) {
                // Permite string de IDs separadas por vírgula também
                $ids_sanitized = implode(',', array_map('intval', explode(',', sanitize_text_field($value))));
            } else {
                $ids_sanitized = ''; // Valor inválido, salva vazio
            }
            update_post_meta($post_id, $field_name, $ids_sanitized);
            return true;
        }

        // Lógica para campos monetários (garante que null/vazio/string inválida vire 0.00)
        if (in_array($field_name, ['li_valor_venda', 'li_valor_aluguel'])) {
            // Converte para float, lida com vírgulas e pontos para formatação BR
            $valor_float = 0.0;
            if (is_numeric($value)) {
                $valor_float = floatval($value);
            } elseif (is_string($value) && !empty($value)) {
                $valor_limpo = str_replace('.', '', $value); // Remove pontos de milhar
                $valor_formatado = str_replace(',', '.', $valor_limpo); // Troca vírgula por ponto decimal
                $valor_float = floatval($valor_formatado);
            }
            update_post_meta($post_id, $field_name, $valor_float);
            return true;
        }

        // Para outros campos de texto/número genéricos
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
            // Inicializa as variáveis para cada iteração do loop
            $schema_type = 'string';
            $description = 'Campo customizado do imóvel: ' . str_replace(['li_', '_'], [' ', ' '], $field_name);
            $validate_callback = null;

            if (strpos($field_name, 'li_possui_') === 0) {
                $schema_type = 'boolean';
                $validate_callback = function($value) {
                    return is_bool($value) || is_numeric($value);
                };
            } elseif ($field_name === 'li_galeria_ids') {
                $schema_type = 'array';
                $validate_callback = function($value) {
                    return is_array($value) || is_string($value);
                };
            } elseif (in_array($field_name, ['li_valor_venda', 'li_valor_aluguel', 'li_area_total', 'li_area_construida', 'li_quartos', 'li_banheiros', 'li_vagas_garagem', 'li_ano_construcao', 'li_andares'])) {
                // Definir campos numéricos com tipo 'number' ou 'integer'
                $schema_type = (strpos($field_name, 'area') !== false || strpos($field_name, 'valor') !== false) ? 'number' : 'integer';
                $validate_callback = function($value) {
                    return is_numeric($value) || is_null($value) || (is_string($value) && empty($value));
                };
            }

            register_rest_field(
                'imovel', // Post Type ao qual o campo pertence
                $field_name,
                [
                    'get_callback'    => function($object) use ($field_name) {
                        $value = get_post_meta($object['id'], $field_name, true);

                        if (strpos($field_name, 'li_possui_') === 0) {
                            return ($value === '1');
                        }
                        if ($field_name === 'li_galeria_ids') {
                            return !empty($value) ? array_map('intval', explode(',', $value)) : [];
                        }
                        // Para campos numéricos, retorna float/int ou null se vazio
                        if (in_array($field_name, ['li_valor_venda', 'li_valor_aluguel', 'li_area_total', 'li_area_construida', 'li_quartos', 'li_banheiros', 'li_vagas_garagem', 'li_ano_construcao', 'li_andares'])) {
                            return is_numeric($value) ? ($schema_type === 'integer' ? intval($value) : floatval($value)) : null;
                        }
                        return $value;
                    },
                    'update_callback' => [$this, 'update_imovel_meta_callback'],
                    'schema'          => [
                        'type'        => $schema_type,
                        'description' => $description,
                        'context'     => ['view', 'edit'],
                        'arg_options' => [
                            'sanitize_callback' => function($value) use ($field_name) {
                                if (strpos($field_name, 'li_possui_') === 0) {
                                    return (bool)$value;
                                }
                                if ($field_name === 'li_galeria_ids') {
                                    if (is_array($value)) {
                                        return array_map('intval', $value);
                                    }
                                    if (is_string($value)) {
                                        return array_map('intval', array_filter(explode(',', $value))); // Filter to remove empty string if split from ""
                                    }
                                    return [];
                                }
                                // Sanitiza campos numéricos
                                if (in_array($field_name, ['li_valor_venda', 'li_valor_aluguel', 'li_area_total', 'li_area_construida', 'li_quartos', 'li_banheiros', 'li_vagas_garagem', 'li_ano_construcao', 'li_andares'])) {
                                    if (is_numeric($value)) return $value;
                                    if (is_string($value) && empty($value)) return null; // Salva null se vazio
                                    // Converte formato BR (1.000,00 -> 1000.00) se for string
                                    if (is_string($value)) {
                                        $value = str_replace('.', '', $value); // Remove pontos de milhar
                                        $value = str_replace(',', '.', $value); // Troca vírgula por ponto decimal
                                        return is_numeric($value) ? $value : null;
                                    }
                                    return null;
                                }
                                return sanitize_text_field($value);
                            },
                            'validate_callback' => function($value) use ($field_name, $validate_callback) {
                                if ($validate_callback) {
                                    return $validate_callback($value);
                                }
                                // Para campos que não têm um validate_callback específico,
                                // garantimos que não seja passado um WP_Error
                                return true;
                            },
                        ],
                    ],
                ]
            );
        }
    }
}