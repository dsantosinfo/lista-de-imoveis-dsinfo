<?php
/**
 * Classe Helper com métodos reutilizáveis para a integração com Elementor.
 */
if (!defined('ABSPATH')) {
    exit;
}

class LI_Elementor_Helper {

    /**
     * Retorna uma lista de posts do tipo 'imovel' para uso em controles de seleção.
     * O método é estático para que não precisemos instanciar a classe.
     *
     * @return array
     */
    public static function get_imoveis_list() {
        $options = ['' => 'Selecione um imóvel...'];

        $imoveis_posts = get_posts([
            'post_type' => 'imovel',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'post_status' => 'publish', // Busca apenas imóveis publicados.
        ]);
        
        if ($imoveis_posts) {
            foreach ($imoveis_posts as $post) {
                $options[$post->ID] = $post->post_title;
            }
        }
        
        wp_reset_postdata();

        return $options;
    }
}