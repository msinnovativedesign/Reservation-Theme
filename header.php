<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <header class="site-header">
        <div class="site-logo-container">
            <div class="site-logo">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                }
                ?>
            </div>
        </div>
        <div class="site-title-container">
            <h1 class="site-title"><?php bloginfo('name'); ?></h1>
            <p class="site-description"><?php bloginfo('description'); ?></p>
        </div>
    </header>
</body>
</html>
