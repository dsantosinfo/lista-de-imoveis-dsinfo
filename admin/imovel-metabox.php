<?php
/**
 * Renderiza o HTML para o metabox de detalhes do imóvel.
 */
if (!defined('ABSPATH')) {
    exit;
}

// Adiciona um nonce para verificação de segurança.
wp_nonce_field('li_salvar_metadados_imovel', 'li_imovel_metabox_nonce');

// Lista de todos os campos que precisamos buscar do banco de dados.
$fields = [
    'li_codigo_referencia', 'li_area_total', 'li_area_construida', 'li_quartos',
    'li_banheiros', 'li_vagas_garagem', 'li_ano_construcao', 'li_valor_venda', 'li_valor_aluguel',
    'li_disponibilidade', 'li_estado_conservacao', 'li_galeria_ids', 'li_piso', 'li_andares',
    'li_rua', 'li_bairro', 'li_cidade', 'li_estado', 'li_cep',
    'li_possui_elevador', 'li_possui_area_servico', 'li_possui_varanda', 'li_possui_jardim',
    'li_possui_piscina', 'li_possui_churrasqueira', 'li_possui_sala_estar', 'li_possui_sala_jantar',
    'li_possui_cozinha', 'li_possui_lavabo', 'li_possui_escritorio', 'li_possui_deposito',
    'li_possui_area_lazer', 'li_possui_acesso_deficientes', 'li_possui_sistema_seguranca',
    'li_possui_ar_condicionado', 'li_possui_mobilia'
];

// Busca todos os valores de uma vez para otimização.
$values = [];
foreach ($fields as $field) {
    $values[$field] = get_post_meta($post->ID, $field, true);
}
$google_api_key = get_option('li_Maps_api_key');
?>
<style>
    .li-metabox-section { margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #ddd; }
    .li-metabox-section:last-child { border-bottom: none; }
    .li-metabox-section h3 { font-size: 1.2em; margin: 0 0 15px 0; padding-bottom: 10px; border-bottom: 1px solid #eee; }
    .li-metabox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
    .li-metabox-field { display: flex; flex-direction: column; }
    .li-metabox-field label { font-weight: bold; margin-bottom: 5px; }
    .li-metabox-field input, .li-metabox-field select, .li-metabox-field textarea { width: 100%; }
    .li-metabox-field.full-width { grid-column: 1 / -1; }
    .li-metabox-checkbox-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 10px; }
    .li-metabox-checkbox-grid label { display: flex; align-items: center; gap: 8px; font-weight: normal; }
    #li_galeria_preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; padding: 10px; background: #f9f9f9; border: 1px dashed #ddd; min-height: 100px; }
    .li-gallery-item { position: relative; width: 100px; height: 100px; }
    .li-gallery-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 4px; border: 1px solid #ccc; }
    .li-gallery-item .li-remove-image { position: absolute; top: -5px; right: -5px; background: #d63638; color: white; border: 2px solid white; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; font-weight: bold; line-height: 1; padding: 0; }
    /* Estilo para o aviso da API Key */
    .li-api-notice { background-color: #fff8e5; border-left: 4px solid #ffb900; padding: 10px 15px; margin-bottom: 20px; grid-column: 1 / -1; }
</style>

<div class="li-metabox-section">
    <h3>Endereço</h3>
    <div class="li-metabox-grid">
        <?php // MELHORIA: Aviso para o usuário caso a API Key não esteja configurada.
        if (empty($google_api_key)) : ?>
            <div class="li-api-notice">
                A chave da API do Google Maps não foi configurada. Para habilitar a busca de endereço, por favor, 
                <a href="<?php echo esc_url(admin_url('edit.php?post_type=imovel&page=li-settings')); ?>">adicione a chave na página de Configurações</a>.
            </div>
        <?php else : ?>
            <div class="li-metabox-field full-width">
                <label for="li_autocomplete_address">Buscar Endereço (Google Maps)</label>
                <input type="text" id="li_autocomplete_address" placeholder="Digite um endereço para buscar sugestões...">
                <div id="li_autocomplete_results"></div>
            </div>
        <?php endif; ?>
        
        <div class="li-metabox-field"><label for="li_rua">Rua e Número</label><input type="text" id="li_rua" name="li_rua" value="<?php echo esc_attr($values['li_rua']); ?>"></div>
        <div class="li-metabox-field"><label for="li_bairro">Bairro</label><input type="text" id="li_bairro" name="li_bairro" value="<?php echo esc_attr($values['li_bairro']); ?>"></div>
        <div class="li-metabox-field"><label for="li_cidade">Cidade</label><input type="text" id="li_cidade" name="li_cidade" value="<?php echo esc_attr($values['li_cidade']); ?>"></div>
        <div class="li-metabox-field"><label for="li_estado">Estado</label><input type="text" id="li_estado" name="li_estado" value="<?php echo esc_attr($values['li_estado']); ?>"></div>
        <div class="li-metabox-field"><label for="li_cep">CEP</label><input type="text" id="li_cep" name="li_cep" value="<?php echo esc_attr($values['li_cep']); ?>"></div>
    </div>
</div>

<div class="li-metabox-section">
    <h3>Informações Principais</h3>
    <div class="li-metabox-grid">
        <div class="li-metabox-field"><label for="li_codigo_referencia">Código de Referência</label><input type="text" id="li_codigo_referencia" name="li_codigo_referencia" value="<?php echo esc_attr($values['li_codigo_referencia']); ?>"></div>
        <div class="li-metabox-field"><label for="li_valor_venda">Valor de Venda (R$)</label><input type="text" id="li_valor_venda" name="li_valor_venda" value="<?php echo esc_attr($values['li_valor_venda']); ?>" placeholder="Ex: 1.500,00"></div>
        <div class="li-metabox-field"><label for="li_valor_aluguel">Valor de Aluguel (R$)</label><input type="text" id="li_valor_aluguel" name="li_valor_aluguel" value="<?php echo esc_attr($values['li_valor_aluguel']); ?>" placeholder="Ex: 2.500,00"></div>
        <div class="li-metabox-field"><label for="li_area_total">Área Total (m²)</label><input type="number" id="li_area_total" name="li_area_total" value="<?php echo esc_attr($values['li_area_total']); ?>"></div>
        <div class="li-metabox-field"><label for="li_area_construida">Área Construída (m²)</label><input type="number" id="li_area_construida" name="li_area_construida" value="<?php echo esc_attr($values['li_area_construida']); ?>"></div>
        <div class="li-metabox-field"><label for="li_quartos">Quartos</label><input type="number" id="li_quartos" name="li_quartos" value="<?php echo esc_attr($values['li_quartos']); ?>"></div>
        <div class="li-metabox-field"><label for="li_banheiros">Banheiros</label><input type="number" id="li_banheiros" name="li_banheiros" value="<?php echo esc_attr($values['li_banheiros']); ?>"></div>
        <div class="li-metabox-field"><label for="li_vagas_garagem">Vagas na Garagem</label><input type="number" id="li_vagas_garagem" name="li_vagas_garagem" value="<?php echo esc_attr($values['li_vagas_garagem']); ?>"></div>
        <div class="li-metabox-field"><label for="li_ano_construcao">Ano de Construção</label><input type="number" id="li_ano_construcao" name="li_ano_construcao" value="<?php echo esc_attr($values['li_ano_construcao']); ?>"></div>
        <div class="li-metabox-field"><label for="li_disponibilidade">Disponibilidade</label><select id="li_disponibilidade" name="li_disponibilidade"><option value="Disponível" <?php selected($values['li_disponibilidade'], 'Disponível'); ?>>Disponível</option><option value="Alugado" <?php selected($values['li_disponibilidade'], 'Alugado'); ?>>Alugado</option><option value="Vendido" <?php selected($values['li_disponibilidade'], 'Vendido'); ?>>Vendido</option></select></div>
        <div class="li-metabox-field"><label for="li_estado_conservacao">Estado de Conservação</label><select id="li_estado_conservacao" name="li_estado_conservacao"><option value="Novo" <?php selected($values['li_estado_conservacao'], 'Novo'); ?>>Novo</option><option value="Usado" <?php selected($values['li_estado_conservacao'], 'Usado'); ?>>Usado</option><option value="Reformado" <?php selected($values['li_estado_conservacao'], 'Reformado'); ?>>Reformado</option></select></div>
    </div>
</div>

<div class="li-metabox-section">
    <h3>Características Adicionais</h3>
    <div class="li-metabox-grid">
        <div class="li-metabox-field"><label for="li_piso">Piso</label><select id="li_piso" name="li_piso"><option value="">N/A</option><option value="Térreo" <?php selected($values['li_piso'], 'Térreo'); ?>>Térreo</option><option value="Sobrado" <?php selected($values['li_piso'], 'Sobrado'); ?>>Sobrado</option><option value="Andar" <?php selected($values['li_piso'], 'Andar'); ?>>Andar</option></select></div>
        <div class="li-metabox-field"><label for="li_andares">Número de Andares</label><input type="number" id="li_andares" name="li_andares" value="<?php echo esc_attr($values['li_andares']); ?>"></div>
    </div>
    <div class="li-metabox-checkbox-grid" style="margin-top: 20px;">
        <?php
        $checkboxes = ['li_possui_elevador' => 'Possui Elevador', 'li_possui_area_servico' => 'Possui Área de Serviço', 'li_possui_varanda' => 'Possui Varanda', 'li_possui_jardim' => 'Possui Jardim', 'li_possui_piscina' => 'Possui Piscina', 'li_possui_churrasqueira' => 'Possui Churrasqueira', 'li_possui_sala_estar' => 'Possui Sala de Estar', 'li_possui_sala_jantar' => 'Possui Sala de Jantar', 'li_possui_cozinha' => 'Possui Cozinha', 'li_possui_lavabo' => 'Possui Lavabo', 'li_possui_escritorio' => 'Possui Escritório', 'li_possui_deposito' => 'Possui Depósito', 'li_possui_area_lazer' => 'Possui Área de Lazer', 'li_possui_acesso_deficientes' => 'Possui Acesso p/ Deficientes', 'li_possui_sistema_seguranca' => 'Possui Sistema de Segurança', 'li_possui_ar_condicionado' => 'Possui Ar Condicionado', 'li_possui_mobilia' => 'Possui Mobília'];
        foreach ($checkboxes as $key => $label) {
            echo '<label><input type="checkbox" name="' . $key . '" value="1" ' . checked($values[$key], '1', false) . '> ' . $label . '</label>';
        }
        ?>
    </div>
</div>

<div class="li-metabox-section">
    <h3>Galeria de Fotos</h3>
    <div id="li_galeria_preview">
        <?php
        if ($values['li_galeria_ids']) {
            $ids = explode(',', $values['li_galeria_ids']);
            foreach ($ids as $id) {
                if ($image_url = wp_get_attachment_image_url($id, 'thumbnail')) {
                    echo '<div class="li-gallery-item" data-id="' . esc_attr($id) . '"><img src="' . esc_url($image_url) . '"><button type="button" class="li-remove-image">×</button></div>';
                }
            }
        }
        ?>
    </div>
    <input type="hidden" id="li_galeria_ids" name="li_galeria_ids" value="<?php echo esc_attr($values['li_galeria_ids']); ?>">
    <button type="button" id="li_galeria_upload_button" class="button" style="margin-top: 10px;">Adicionar Imagens</button>
</div>