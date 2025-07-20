<?php
/**
 * Template de Arquivo para Imóveis (v2.1 - Revisado)
 * Fornecido pelo Plugin Lista de Imóveis
 */

get_header(); ?>

<div id="primary" class="content-area li-container">
    <main id="main" class="site-main">

        <header class="page-header">
            <?php
                if (is_tax()) {
                    the_archive_title('<h1 class="page-title">', '</h1>');
                } else {
                    echo '<h1 class="page-title">Nossos Imóveis</h1>';
                }
                the_archive_description('<div class="archive-description">', '</div>');
            ?>
        </header>

        <?php if (have_posts()) : ?>
            <div class="li-grid">
                <?php while (have_posts()) : the_post(); 
                    $imovel_id = get_the_ID();
                ?>
                    <article id="post-<?php echo $imovel_id; ?>" <?php post_class('li-card'); ?>>
                        
                        <div class="li-card-image-wrapper">
                            <a href="<?php the_permalink(); ?>">
                                <?php if (has_post_thumbnail()) :
                                    the_post_thumbnail('large');
                                else: ?>
                                    <div class="li-card-thumbnail li-placeholder">
                                        <span>Imagem não disponível</span>
                                    </div>
                                <?php endif; ?>
                            </a>
                            <div class="li-card-price-badge">
                                <?php 
                                    $valor_venda = get_post_meta($imovel_id, 'li_valor_venda', true);
                                    if ($valor_venda > 0) { 
                                        echo 'R$ ' . number_format($valor_venda, 0, ',', '.'); 
                                    } else {
                                        $valor_aluguel = get_post_meta($imovel_id, 'li_valor_aluguel', true);
                                        if ($valor_aluguel > 0) {
                                            echo 'R$ ' . number_format($valor_aluguel, 0, ',', '.') . ' <span class="li-price-suffix">/mês</span>';
                                        }
                                    }
                                ?>
                            </div>
                            <div class="li-card-finalidade-badge">
                                <?php the_terms($imovel_id, 'finalidade', '', ', '); ?>
                            </div>
                        </div>
                        
                        <div class="li-card-content">
                            <h2 class="li-card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                            
                            <?php 
                            $bairro = get_post_meta($imovel_id, 'li_bairro', true);
                            $cidade = get_post_meta($imovel_id, 'li_cidade', true);
                            if ($bairro || $cidade) : ?>
                                <div class="li-card-location">
                                    <span class="dashicons dashicons-location"></span>
                                    <?php echo esc_html(implode(', ', array_filter([$bairro, $cidade]))); ?>
                                </div>
                            <?php endif; ?>

                            <div class="li-card-details">
                                <?php if ($area = get_post_meta($imovel_id, 'li_area_construida', true)) : ?>
                                    <span><span class="dashicons dashicons-fullscreen-alt3"></span> <?php echo esc_html($area); ?> m²</span>
                                <?php endif; ?>
                                <?php if ($quartos = get_post_meta($imovel_id, 'li_quartos', true)) : ?>
                                    <span><span class="dashicons dashicons-bed"></span> <?php echo esc_html($quartos); ?> Quartos</span>
                                <?php endif; ?>
                                <?php if ($vagas = get_post_meta($imovel_id, 'li_vagas_garagem', true)) : ?>
                                    <span><span class="dashicons dashicons-car"></span> <?php echo esc_html($vagas); ?> Vagas</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($ref = get_post_meta($imovel_id, 'li_codigo_referencia', true)) : ?>
                        <div class="li-card-footer">
                            <span>Ref: <?php echo esc_html($ref); ?></span>
                        </div>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
            </div>
            
            <?php the_posts_pagination(); ?>

        <?php else : ?>
            <p>Nenhum imóvel encontrado.</p>
        <?php endif; ?>

    </main>
</div>

<?php get_footer(); ?>