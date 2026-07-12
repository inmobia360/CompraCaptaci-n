<?php
if (!defined('ABSPATH')) {
    exit;
}

$captacion_brand_name = captacion_app_setting('brand_name');
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class('captacion-wp-page'); ?>>
<?php wp_body_open(); ?>
<header class="captacion-wp-header">
    <div class="captacion-wp-container">
        <nav class="captacion-wp-nav" aria-label="Navegacion principal">
            <a class="captacion-wp-brand" href="<?php echo esc_url(home_url('/')); ?>">
                <?php echo esc_html($captacion_brand_name); ?>
            </a>
            <div class="captacion-wp-menu">
                <a href="<?php echo esc_url(home_url('/')); ?>">Inicio</a>
                <a href="<?php echo esc_url(home_url('/marketplace/')); ?>">Marketplace</a>
                <a href="<?php echo esc_url(home_url('/buscar-captaciones/')); ?>">Buscar captaciones</a>
                <a href="<?php echo esc_url(home_url('/ofrecer-captacion/')); ?>">Ofrecer captacion</a>
                <a href="<?php echo esc_url(home_url('/recursos/')); ?>">Recursos</a>
                <a href="<?php echo esc_url(home_url('/contacto/')); ?>">Contacto</a>
            </div>
        </nav>
    </div>
</header>
<main class="captacion-wp-main">
    <div class="captacion-wp-container">
        <article class="captacion-wp-card">
            <?php
            while (have_posts()) :
                the_post();
                the_title('<h1>', '</h1>');
                the_content();
            endwhile;
            ?>
        </article>
    </div>
</main>
<footer class="captacion-wp-footer">
    <div class="captacion-wp-container">
        <?php echo esc_html($captacion_brand_name); ?> &copy; <?php echo esc_html(date('Y')); ?>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
