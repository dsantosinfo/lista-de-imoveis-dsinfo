<?php
/**
 * Classe responsável por toda a funcionalidade do painel de administração.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Admin {

    /**
     * Construtor. Adiciona todos os hooks da área administrativa.
     */
    public function __construct() {
        // Metabox
        add_action('add_meta_boxes', [$this, 'add_imovel_metabox']);
        add_action('save_post_imovel', [$this, 'save_imovel_metadata']);

        // Página de Configurações
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
        
        // Scripts e Estilos do Admin
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);

        // Filtros da Tela de Listagem
        add_action('restrict_manage_posts', [$this, 'add_admin_list_filters']);
        add_filter('parse_query', [$this, 'apply_admin_list_filters']);

        // Forçar Editor Clássico
        add_filter('use_block_editor_for_post_type', [$this, 'disable_gutenberg_for_imoveis'], 10, 2);
    }

    // =========================================================================
    // PÁGINA DE CONFIGURAÇÕES
    // =========================================================================

    public function add_settings_page() {
        add_submenu_page('edit.php?post_type=imovel', 'Configurações', 'Configurações', 'manage_options', 'li-settings', [$this, 'render_settings_page']);
    }

    public function register_settings() {
        register_setting('li_settings_group', 'li_Maps_api_key');
        register_setting('li_settings_group', 'li_enable_plugin_templates');
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Configurações - Lista de Imóveis</h1>
            <form method="post" action="options.php">
                <?php settings_fields('li_settings_group'); ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Chave da API do Google Maps</th>
                        <td>
                            <input type="text" name="li_Maps_api_key" value="<?php echo esc_attr(get_option('li_Maps_api_key')); ?>" size="60" />
                            <p class="description">Insira sua chave da API da Plataforma Google Maps com as APIs "Maps JavaScript API" e "Places API" ativadas.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">Templates do Plugin</th>
                        <td>
                            <label for="li_enable_plugin_templates">
                                <input type="checkbox" id="li_enable_plugin_templates" name="li_enable_plugin_templates" value="1" <?php checked(get_option('li_enable_plugin_templates', 1), 1); ?> />
                                Ativar templates padrão do plugin (<code>archive-imovel.php</code> e <code>single-imovel.php</code>).
                            </label>
                            <p class="description">Desmarque esta opção se você deseja usar os arquivos de template do seu próprio tema ou se estiver usando o Construtor de Temas do Elementor.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    // =========================================================================
    // METABOX DE DETALHES DO IMÓVEL
    // =========================================================================

    public function add_imovel_metabox() {
        add_meta_box('li_imovel_details', 'Detalhes do Imóvel', [$this, 'render_imovel_metabox_html'], 'imovel', 'normal', 'high');
    }

    public function render_imovel_metabox_html($post) {
        // Usa a constante LI_PLUGIN_PATH definida no arquivo principal
        require_once LI_PLUGIN_PATH . 'admin/imovel-metabox.php';
    }

    public function save_imovel_metadata($post_id) {
        if (!isset($_POST['li_imovel_metabox_nonce']) || !wp_verify_nonce($_POST['li_imovel_metabox_nonce'], 'li_salvar_metadados_imovel')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (get_post_type($post_id) !== 'imovel' || !current_user_can('edit_post', $post_id)) return;
        
        $campos_texto = ['li_codigo_referencia', 'li_area_total', 'li_area_construida', 'li_quartos', 'li_banheiros', 'li_vagas_garagem', 'li_ano_construcao', 'li_disponibilidade', 'li_estado_conservacao', 'li_piso', 'li_andares', 'li_rua', 'li_bairro', 'li_cidade', 'li_estado', 'li_cep'];
        foreach ($campos_texto as $campo) { if (isset($_POST[$campo])) { update_post_meta($post_id, $campo, sanitize_text_field($_POST[$campo])); } }

        $campos_monetarios = ['li_valor_venda', 'li_valor_aluguel'];
        foreach ($campos_monetarios as $campo) {
            if (isset($_POST[$campo])) {
                $valor_br = sanitize_text_field($_POST[$campo]);
                $valor_limpo = str_replace('.', '', $valor_br);
                $valor_float = str_replace(',', '.', $valor_limpo);
                $valor_final = floatval(preg_replace('/[^\d\.]/', '', $valor_float));
                update_post_meta($post_id, $campo, $valor_final);
            }
        }
        
        $campos_checkbox = ['li_possui_elevador', 'li_possui_area_servico', 'li_possui_varanda', 'li_possui_jardim', 'li_possui_piscina', 'li_possui_churrasqueira', 'li_possui_sala_estar', 'li_possui_sala_jantar', 'li_possui_cozinha', 'li_possui_lavabo', 'li_possui_escritorio', 'li_possui_deposito', 'li_possui_area_lazer', 'li_possui_acesso_deficientes', 'li_possui_sistema_seguranca', 'li_possui_ar_condicionado', 'li_possui_mobilia'];
        foreach ($campos_checkbox as $campo) { update_post_meta($post_id, $campo, isset($_POST[$campo]) ? '1' : '0'); }
        
        if (isset($_POST['li_galeria_ids'])) {
            $ids_sanitizados = implode(',', array_map('intval', explode(',', $_POST['li_galeria_ids'])));
            update_post_meta($post_id, 'li_galeria_ids', $ids_sanitizados);
        }
    }

    // =========================================================================
    // SCRIPTS E FILTROS DO ADMIN
    // =========================================================================

    public function enqueue_admin_scripts($hook) {
        if ('post.php' === $hook || 'post-new.php' === $hook) {
            global $post;
            if (isset($post->post_type) && 'imovel' === $post->post_type) {
                wp_enqueue_media();
                wp_enqueue_script('li-metabox-scripts', LI_PLUGIN_URL . 'admin/js/metabox-scripts.js', ['jquery'], LI_PLUGIN_VERSION, true);
                
                $api_key = get_option('li_Maps_api_key');
                if ($api_key) {
                    wp_enqueue_script('google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . esc_attr($api_key) . '&libraries=places', [], null, true);
                }
            }
        }
    }

    /**
     * Adiciona os atributos async e defer ao script do Google Maps.
     */
    public function add_async_attribute_to_script($tag, $handle, $src) {
        if ('google-maps-api' === $handle) {
            return str_replace(' src=', ' async defer src=', $tag);
        }
        return $tag;
    }

    public function add_admin_list_filters($post_type) {
        if ($post_type !== 'imovel') { return; }
        global $wpdb;
        $current_bairro = isset($_GET['li_bairro_filter']) ? sanitize_text_field($_GET['li_bairro_filter']) : '';
        $bairros = $wpdb->get_col($wpdb->prepare("SELECT DISTINCT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value != '' ORDER BY meta_value", 'li_bairro'));
        echo '<select name="li_bairro_filter"><option value="">Todos os Bairros</option>';
        foreach ($bairros as $bairro) { printf('<option value="%s"%s>%s</option>', esc_attr($bairro), selected($current_bairro, $bairro, false), esc_html($bairro)); }
        echo '</select>';

        $current_valor = isset($_GET['li_valor_filter']) ? sanitize_text_field($_GET['li_valor_filter']) : '';
        $faixas_valor = ['0-300000' => 'Até R$ 300.000', '300001-600000' => 'R$ 300.001 - R$ 600.000', '600001-1000000' => 'R$ 600.001 - R$ 1.000.000', '1000001-999999999' => 'Acima de R$ 1.000.000'];
        echo '<select name="li_valor_filter"><option value="">Qualquer Valor</option>';
        foreach ($faixas_valor as $range => $label) { printf('<option value="%s"%s>%s</option>', esc_attr($range), selected($current_valor, $range, false), esc_html($label)); }
        echo '</select>';
    }

    public function apply_admin_list_filters($query) {
        global $pagenow;
        if (is_admin() && $pagenow === 'edit.php' && isset($query->query['post_type']) && $query->query['post_type'] === 'imovel' && $query->is_main_query()) {
            $meta_query = $query->get('meta_query') ?: [];
            if (isset($_GET['li_bairro_filter']) && !empty($_GET['li_bairro_filter'])) { $meta_query[] = ['key' => 'li_bairro', 'value' => sanitize_text_field($_GET['li_bairro_filter']), 'compare' => '=']; }
            if (isset($_GET['li_valor_filter']) && !empty($_GET['li_valor_filter'])) {
                $range = explode('-', sanitize_text_field($_GET['li_valor_filter']));
                if (count($range) === 2) { $meta_query[] = ['key' => 'li_valor_venda', 'value' => [$range[0], $range[1]], 'type' => 'NUMERIC', 'compare' => 'BETWEEN']; }
            }
            if (!empty($meta_query)) { $query->set('meta_query', $meta_query); }
        }
    }

    public function disable_gutenberg_for_imoveis($is_enabled, $post_type) {
        if ($post_type === 'imovel') { return false; }
        return $is_enabled;
    }
}