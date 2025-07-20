<?php
if (!defined('ABSPATH')) {
    exit;
}

require_once(__DIR__ . '/imovel-text-tag.php'); 

class LI_Imovel_Image_Tag extends \LI_Imovel_Text_Tag {

    public function get_name() { return 'imovel-image-data'; }
    public function get_title() { return 'Campo do Imóvel (Imagem)'; }
    public function get_categories() { return [\Elementor\Modules\DynamicTags\Module::IMAGE_CATEGORY]; }
    public function render() { return; }

    // Sobrescreve apenas os campos de imagem
    protected function get_imovel_fields() {
        return ['featured_image' => 'Imagem Destacada'];
    }

    public function get_value(array $options = []) {
        $source = $this->get_settings('source');
        $imovel_id = 0;

        // Lógica para definir o ID do imóvel
        if ('manual_selection' === $source) {
            $imovel_id = $this->get_settings('imovel_id');
        } else {
            $imovel_id = get_the_ID();
        }

        if (empty($imovel_id) || get_post_type($imovel_id) !== 'imovel') { return null; }

        $thumbnail_id = get_post_thumbnail_id($imovel_id);
        if (empty($thumbnail_id)) { return null; }

        return ['id' => $thumbnail_id, 'url' => wp_get_attachment_url($thumbnail_id)];
    }
}