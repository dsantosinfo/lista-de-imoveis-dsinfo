<?php
/**
 * Template de Página Individual para Imóveis (v4.1 - Conteúdo Completo)
 */

get_header(); ?>

<div id="primary" class="content-area li-container">
    <main id="main" class="site-main">

        <?php while (have_posts()) : the_post(); 
            $imovel_id = get_the_ID();
            $gallery_ids_str = get_post_meta($imovel_id, 'li_galeria_ids', true);
            $gallery_ids = !empty($gallery_ids_str) ? explode(',', $gallery_ids_str) : [];

            if (has_post_thumbnail()) {
                array_unshift($gallery_ids, get_post_thumbnail_id());
                $gallery_ids = array_unique($gallery_ids);
            }
        ?>
            <article id="post-<?php echo $imovel_id; ?>" <?php post_class('li-single-imovel'); ?>>
                <header class="entry-header">
                    <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                    <?php if ($ref = get_post_meta($imovel_id, 'li_codigo_referencia', true)): ?>
                        <div class="li-ref"><strong>Referência:</strong> <?php echo esc_html($ref); ?></div>
                    <?php endif; ?>
                </header>

                <div class="li-single-content">
                    <div class="li-main-content">
                        
                        <?php if (!empty($gallery_ids)): ?>
                        <div class="li-gallery-hero-wrapper">
                            <div class="swiper li-gallery-main">
                                <div class="swiper-wrapper">
                                    <?php foreach ($gallery_ids as $id) :
                                        if ($full_url = wp_get_attachment_image_url($id, 'full')) : ?>
                                            <div class="swiper-slide">
                                                <a href="<?php echo esc_url($full_url); ?>" data-elementor-open-lightbox="yes" data-elementor-lightbox-gallery="imovel-gallery-<?php echo $imovel_id; ?>">
                                                    <?php echo wp_get_attachment_image($id, 'full'); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                            <?php if (count($gallery_ids) > 1) : ?>
                            <div class="swiper li-gallery-thumbnails">
                                <div class="swiper-wrapper">
                                    <?php foreach ($gallery_ids as $id) :
                                        if (wp_get_attachment_image_url($id, 'thumbnail')) : ?>
                                            <div class="swiper-slide">
                                                <?php echo wp_get_attachment_image($id, 'medium'); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                        
                        <div class="entry-content">
                            <h2>Descrição do Imóvel</h2>
                            <?php the_content(); ?>
                        </div>
                    </div>

                    <aside class="li-sidebar">
                        
                        <div class="li-sidebar-box li-price-box">
                            <?php 
                                $valor_venda = get_post_meta($imovel_id, 'li_valor_venda', true);
                                if ($valor_venda > 0) { 
                                    echo '<div class="li-price-venda"><span>Valor de Venda</span><strong>R$ ' . number_format($valor_venda, 2, ',', '.') . '</strong></div>'; 
                                }
                                $valor_aluguel = get_post_meta($imovel_id, 'li_valor_aluguel', true);
                                if ($valor_aluguel > 0) {
                                    echo '<div class="li-price-aluguel"><span>Valor de Aluguel</span><strong>R$ ' . number_format($valor_aluguel, 2, ',', '.') . ' /mês</strong></div>';
                                }
                            ?>
                        </div>

                        <div class="li-sidebar-box">
                            <h3>Ficha Técnica</h3>
                            <ul class="li-details-list">
                                <?php
                                // ATUALIZADO: Lista completa de detalhes.
                                $details_fields = [ 
                                    'li_quartos' => 'Quartos', 
                                    'li_banheiros' => 'Banheiros', 
                                    'li_vagas_garagem' => 'Vagas', 
                                    'li_area_construida' => 'Área Construída', 
                                    'li_area_total' => 'Área Total', 
                                    'li_ano_construcao' => 'Ano de Construção', 
                                    'li_piso' => 'Piso', 
                                    'li_andares' => 'Nº de Andares', 
                                    'li_estado_conservacao' => 'Estado', 
                                    'li_disponibilidade' => 'Disponibilidade' 
                                ];
                                $sufixos = ['li_area_construida' => ' m²', 'li_area_total' => ' m²'];
                                foreach ($details_fields as $key => $label) {
                                    if ($value = get_post_meta($imovel_id, $key, true)) {
                                        $sufixo = $sufixos[$key] ?? '';
                                        echo '<li><strong>' . esc_html($label) . ':</strong> <span>' . esc_html($value . $sufixo) . '</span></li>';
                                    }
                                }
                                ?>
                            </ul>
                        </div>
                        
                        <?php
                        // ATUALIZADO: Lista completa de características.
                        $caracteristicas = [ 
                            'li_possui_piscina' => 'Piscina', 
                            'li_possui_churrasqueira' => 'Churrasqueira', 
                            'li_possui_elevador' => 'Elevador', 
                            'li_possui_jardim' => 'Jardim', 
                            'li_possui_varanda' => 'Varanda', 
                            'li_possui_area_lazer' => 'Área de Lazer', 
                            'li_possui_mobilia' => 'Mobiliado', 
                            'li_possui_ar_condicionado' => 'Ar Condicionado', 
                            'li_possui_sistema_seguranca' => 'Sistema de Segurança',
                            'li_possui_area_servico' => 'Área de Serviço', 
                            'li_possui_sala_estar' => 'Sala de Estar',
                            'li_possui_sala_jantar' => 'Sala de Jantar',
                            'li_possui_cozinha' => 'Cozinha', 
                            'li_possui_lavabo' => 'Lavabo',
                            'li_possui_escritorio' => 'Escritório', 
                            'li_possui_deposito' => 'Depósito',
                            'li_possui_acesso_deficientes' => 'Acesso para Deficientes'
                        ];
                        $items_html = '';
                        foreach ($caracteristicas as $key => $label) {
                            if (get_post_meta($imovel_id, $key, true) === '1') {
                                $items_html .= '<li><span class="dashicons dashicons-yes"></span> ' . esc_html($label) . '</li>';
                            }
                        }
                        if (!empty($items_html)) : ?>
                            <div class="li-sidebar-box">
                                <h3>Comodidades</h3>
                                <ul class="li-caracteristicas-sidebar-list">
                                    <?php echo $items_html; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                    </aside>
                </div>
            </article>
        <?php endwhile; ?>
    </main>
</div>

<?php get_footer(); ?>