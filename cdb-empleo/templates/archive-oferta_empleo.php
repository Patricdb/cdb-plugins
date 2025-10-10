<?php
/**
 * Archive template para ofertas de empleo
 *
 * Este archivo se utiliza para mostrar el listado de ofertas de empleo (CPT: oferta_empleo)
 * en el frontend.
 */
get_header(); ?>

<div class="ofertas-archive">
    <h1><?php post_type_archive_title(); ?></h1>
    
    <?php if ( have_posts() ) : ?>
        <div class="ofertas-grid">
            <?php while ( have_posts() ) : the_post();
                // Recuperar los campos personalizados
                $bar_id             = get_post_meta( get_the_ID(), 'cdb_bar', true );
                $posicion_id        = get_post_meta( get_the_ID(), 'cdb_posicion', true );
                $tipo_oferta        = get_post_meta( get_the_ID(), 'cdb_tipo_oferta', true );
                $fecha_incorporacion= get_post_meta( get_the_ID(), 'cdb_fecha_incorporacion', true );
                $fecha_fin          = get_post_meta( get_the_ID(), 'cdb_fecha_fin', true );
                ?>
                <div class="oferta-card">
                    <?php if ( has_post_thumbnail() ) : ?>
                        <div class="oferta-thumbnail">
                            <?php the_post_thumbnail('medium'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <h2 class="oferta-title"><?php the_title(); ?></h2>
                    
                    <div class="oferta-meta">
                        <p><strong>Bar:</strong> <?php echo $bar_id ? esc_html( get_the_title( $bar_id ) ) : 'No asignado'; ?></p>
                        <p><strong>Posición:</strong> <?php echo $posicion_id ? esc_html( get_the_title( $posicion_id ) ) : 'No asignado'; ?></p>
                        <p><strong>Tipo de oferta:</strong> <?php echo esc_html( $tipo_oferta ); ?></p>
                        <p><strong>Incorporación:</strong> <?php echo esc_html( $fecha_incorporacion ); ?></p>
                        <p><strong>Fin:</strong> <?php echo esc_html( $fecha_fin ); ?></p>
                    </div>
                    
                    <div class="oferta-excerpt">
                        <?php the_excerpt(); ?>
                    </div>
                    
                    <a class="oferta-link" href="<?php the_permalink(); ?>">Ver Oferta</a>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php
        // Paginación de las ofertas.
        the_posts_pagination( array(
            'prev_text' => __('« Anterior', 'cdb-empleo'),
            'next_text' => __('Siguiente »', 'cdb-empleo'),
        ) );
        ?>
        
    <?php else : ?>
        <?php echo cdb_empleo_get_mensaje( 'sin_ofertas' ); ?>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
