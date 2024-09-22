<?php get_header(); ?>

<main>
    <?php
    if ( have_posts() ) :
        while ( have_posts() ) : the_post();
            the_content();
            get_template_part( 'template-parts/content', 'booking-form' ); // Insertion du formulaire
        endwhile;
    endif;
    ?>
</main>

<?php get_footer(); ?>
