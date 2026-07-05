<?php
if (!defined('ABSPATH')) {
    exit;
}

function captacion_app_defaults() {
    return array(
        'brand_name' => 'Compra Captación',
        'site_title' => 'Captaciones inmobiliarias | Compra, vende y colabora entre profesionales',
        'meta_description' => 'Compra Captación es un marketplace B2B de captaciones inmobiliarias para profesionales. Publica oportunidades, busca demandas activas y colabora con acceso protegido.',
        'hero_kicker' => 'Red privada B2B para profesionales inmobiliarios',
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',
        'hero_description' => 'Compra Captación ayuda a agentes, agencias e inversores a publicar captaciones, cruzar demandas activas y colaborar con acceso protegido, trazabilidad comercial y mejor contexto de operacion.',
        'primary_cta' => 'Entender el recorrido',
        'secondary_cta' => 'Ver el producto en accion',
        'contact_email' => 'info@compracaptacion.com',
        'stripe_payment_link' => '',
        'stripe_membership_initial_link' => '',
        'stripe_membership_initial_annual_link' => '',
        'stripe_membership_professional_link' => '',
        'stripe_membership_professional_annual_link' => '',
        'stripe_membership_agency_link' => '',
        'stripe_membership_agency_annual_link' => '',
        'stripe_marketplace_single_link' => '',
        'stripe_marketplace_plus_pack_link' => '',
        'stripe_marketplace_premium_pack_link' => '',
        'mailchimp_api_key' => '',
        'mailchimp_audience_id' => '',
        'mailchimp_double_optin' => '0',
        'social_login_enabled' => '0',
        'webhook_api_key' => '',
        'ai_admin_provider' => '',
        'ai_admin_api_key' => '',
        'ai_admin_model' => '',
        'saas_admin_email' => 'inmobia360@gmail.com',
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_settings() {
    static $settings_cache = null;
    if ($settings_cache !== null) {
        return $settings_cache;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $saved = get_option('captacion_app_settings', array());
    $defaults = captacion_app_defaults();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    foreach ($defaults as $key => $default) {
        if (strpos($key, 'stripe_') === 0 && $default && strpos((string) $default, 'REEMPLAZA_') === false && (!$settings[$key] || strpos((string) $settings[$key], 'REEMPLAZA_') !== false)) {
            $settings[$key] = $default;
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $settings_cache = $settings;
    return $settings_cache;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_setting($key) {
    $settings = captacion_app_settings();
    return isset($settings[$key]) ? $settings[$key] : '';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_sanitize_settings($input) {
    $defaults = captacion_app_defaults();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    foreach ($defaults as $key => $default) {
        $value = isset($input[$key]) ? wp_unslash($input[$key]) : $default;
        if ($key === 'hero_title') {
            $output[$key] = wp_kses($value, array(
                'span' => array('class' => array()),
                'strong' => array('class' => array()),
                'br' => array(),
            ));
        } elseif (strpos($key, 'stripe_') === 0) {
            $output[$key] = esc_url_raw($value);
        } elseif ($key === 'contact_email') {
            $output[$key] = sanitize_email($value);
        } elseif ($key === 'saas_admin_email') {
            $output[$key] = sanitize_email($value);
        } elseif ($key === 'mailchimp_api_key' || $key === 'mailchimp_audience_id' || $key === 'webhook_api_key' || $key === 'ai_admin_provider' || $key === 'ai_admin_api_key' || $key === 'ai_admin_model') {
            $output[$key] = sanitize_text_field($value);
        } elseif ($key === 'mailchimp_double_optin' || $key === 'social_login_enabled') {
            $output[$key] = !empty($value) ? '1' : '0';
        } else {
            $output[$key] = sanitize_text_field($value);
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return $output;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_admin_menu() {
    add_menu_page(
        'Compra Captación',
        'Compra Captación',
        'manage_options',
        'captacion-app-settings',
        'captacion_app_render_settings_page',
        'dashicons-admin-home',
        3
    );
    add_submenu_page(
        'captacion-app-settings',
        'Recursos profesionales',
        'Recursos profesionales',
        'manage_options',
        'captacion-app-resources',
        'captacion_app_render_resources_admin_page'
    );
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_settings() {
    register_setting('captacion_app_settings_group', 'captacion_app_settings', array(
        'sanitize_callback' => 'captacion_app_sanitize_settings',
        'default' => captacion_app_defaults(),
    ));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_sanitize_resource_settings($input) {
    $output = array();
    foreach (captacion_app_resource_catalog_defaults() as $resource_id => $resource) {
        $row = isset($input[$resource_id]) && is_array($input[$resource_id]) ? wp_unslash($input[$resource_id]) : array();
        $schema = isset($row['editable_fields_schema']) ? json_decode((string) $row['editable_fields_schema'], true) : $resource['editable_fields_schema'];
        $output[$resource_id] = array(
            'static_pdf_attachment_id' => absint($row['static_pdf_attachment_id'] ?? 0),
            'static_pdf_url' => esc_url_raw($row['static_pdf_url'] ?? ''),
            'docx_template_attachment_id' => absint($row['docx_template_attachment_id'] ?? 0),
            'docx_template_url' => esc_url_raw($row['docx_template_url'] ?? ''),
            'plan_access' => in_array(($row['plan_access'] ?? ''), array('basic', 'professional_plus', 'premium'), true) ? $row['plan_access'] : 'basic',
            'editable_fields_schema' => is_array($schema) ? array_values(array_filter(array_map('sanitize_key', $schema))) : $resource['editable_fields_schema'],
        );
    }
    return $output;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_resource_settings() {
    register_setting('captacion_app_resources_group', 'captacion_app_resource_settings', array(
        'sanitize_callback' => 'captacion_app_sanitize_resource_settings',
        'default' => array(),
    ));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_render_resources_admin_page() {
    if (!current_user_can('manage_options')) return;
    global $wpdb;
    $catalog = captacion_app_resource_catalog();
    $events_table = captacion_app_resource_events_table_name();
    $events = $wpdb->get_results("SELECT * FROM {$events_table} ORDER BY created_at DESC LIMIT 100", ARRAY_A);
    ?>
    <div class="wrap">
        <h1>Recursos profesionales</h1>
        <p>Configura los PDF publicos y las plantillas DOCX internas. Los DOCX nunca se exponen al usuario final.</p>
        <form method="post" action="options.php">
            <?php settings_fields('captacion_app_resources_group'); ?>
            <?php foreach ($catalog as $resource_id => $resource) : ?>
                <div class="card" style="max-width:1000px;padding:18px;margin-top:14px">
                    <h2><?php echo esc_html($resource['title']); ?></h2>
                    <p><?php echo esc_html($resource['description']); ?></p>
                    <table class="form-table" role="presentation">
                        <tr><th>PDF de Medios</th><td><input class="small-text" type="number" min="0" name="captacion_app_resource_settings[<?php echo esc_attr($resource_id); ?>][static_pdf_attachment_id]" value="<?php echo esc_attr($resource['static_pdf_attachment_id']); ?>"> <span class="description">ID del adjunto PDF.</span></td></tr>
                        <tr><th>URL PDF alternativa</th><td><input class="large-text" type="url" name="captacion_app_resource_settings[<?php echo esc_attr($resource_id); ?>][static_pdf_url]" value="<?php echo esc_attr($resource['static_pdf_url']); ?>"></td></tr>
                        <tr><th>DOCX interno de Medios</th><td><input class="small-text" type="number" min="0" name="captacion_app_resource_settings[<?php echo esc_attr($resource_id); ?>][docx_template_attachment_id]" value="<?php echo esc_attr($resource['docx_template_attachment_id']); ?>"> <span class="description">ID interno; no se muestra ni descarga en frontend.</span></td></tr>
                        <tr><th>URL DOCX interna</th><td><input class="large-text" type="url" name="captacion_app_resource_settings[<?php echo esc_attr($resource_id); ?>][docx_template_url]" value="<?php echo esc_attr($resource['docx_template_url']); ?>"></td></tr>
                        <tr><th>Plan minimo</th><td><select name="captacion_app_resource_settings[<?php echo esc_attr($resource_id); ?>][plan_access]"><?php foreach (array('basic'=>'Basico','professional_plus'=>'Professional Plus','premium'=>'Premium') as $value => $label) : ?><option value="<?php echo esc_attr($value); ?>" <?php selected($resource['plan_access'], $value); ?>><?php echo esc_html($label); ?></option><?php endforeach; ?></select></td></tr>
                        <tr><th>Esquema editable JSON</th><td><textarea class="large-text code" rows="2" name="captacion_app_resource_settings[<?php echo esc_attr($resource_id); ?>][editable_fields_schema]"><?php echo esc_textarea(wp_json_encode($resource['editable_fields_schema'])); ?></textarea></td></tr>
                    </table>
                </div>
            <?php endforeach; ?>
            <?php submit_button('Guardar recursos'); ?>
        </form>
        <h2>Trazabilidad reciente</h2>
        <table class="widefat striped">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Recurso</th><th>Plan</th><th>Accion</th><th>Archivo</th><th>IP</th></tr></thead>
            <tbody>
            <?php if ($events) : foreach ($events as $event) : ?>
                <tr><td><?php echo esc_html($event['created_at']); ?></td><td><?php echo esc_html($event['user_id']); ?></td><td><?php echo esc_html($event['resource_title']); ?></td><td><?php echo esc_html($event['plan_type']); ?></td><td><?php echo esc_html($event['action_type']); ?></td><td><?php echo esc_html($event['generated_file_id'] ?: '-'); ?></td><td><?php echo esc_html($event['ip_address']); ?></td></tr>
            <?php endforeach; else : ?>
                <tr><td colspan="7">Todavia no hay eventos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $settings = captacion_app_settings();
    ?>
    <div class="wrap">
        <h1>Compra Captación</h1>
        <p>Edita los textos principales y la pasarela Stripe de la web.</p>
        <?php if (isset($_GET['captacion_pages_created']) || isset($_GET['captacion_pages_updated'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    Paginas procesadas. Creadas: <?php echo esc_html(absint($_GET['captacion_pages_created'] ?? 0)); ?>.
                    Actualizadas: <?php echo esc_html(absint($_GET['captacion_pages_updated'] ?? 0)); ?>.
                </p>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['captacion_cleanup_records']) || isset($_GET['captacion_cleanup_batches'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    Limpieza SaaS completada. Registros demo/sinteticos marcados como eliminados: <?php echo esc_html(absint($_GET['captacion_cleanup_records'] ?? 0)); ?>.
                    Lotes demo marcados como eliminados: <?php echo esc_html(absint($_GET['captacion_cleanup_batches'] ?? 0)); ?>.
                </p>
            </div>
        <?php endif; ?>
        <?php if (isset($_GET['captacion_reset_day_one'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    Reset SaaS dia 1 completado. Tablas vaciadas: <?php echo esc_html(absint($_GET['captacion_reset_tables'] ?? 0)); ?>.
                    Usuarios SaaS eliminados: <?php echo esc_html(absint($_GET['captacion_reset_users'] ?? 0)); ?>.
                    Administrador SaaS preparado: <?php echo esc_html(sanitize_email($_GET['captacion_reset_admin'] ?? '')); ?>.
                </p>
            </div>
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        <div class="card" style="max-width: 900px; margin-top: 16px; padding: 18px;">
            <h2>Restaurar paginas editables</h2>
            <p>Crea o actualiza la estructura base de paginas de Compra Captación para devolverlas al contenido original del tema.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('captacion_app_create_pages'); ?>
                <input type="hidden" name="action" value="captacion_app_create_pages">
                <?php submit_button('Restaurar paginas base', 'secondary', 'submit', false); ?>
            </form>
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        <div class="card" style="max-width: 900px; margin-top: 16px; padding: 18px;">
            <h2>Preparar SaaS para produccion</h2>
            <p>Marca como eliminados datos demo/sinteticos y lotes demo. No borra usuarios WordPress ni datos privados reales.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('captacion_app_prepare_production'); ?>
                <input type="hidden" name="action" value="captacion_app_prepare_production">
                <?php submit_button('Limpiar datos demo del SaaS', 'delete', 'submit', false); ?>
            </form>
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        <div class="card" style="max-width: 900px; margin-top: 16px; padding: 18px; border-left:4px solid #b32d2e;">
            <h2>Reset SaaS dia 1</h2>
            <p><strong>Accion irreversible.</strong> Vacia los repositorios propios de Compra Captación, elimina usuarios SaaS detectados por metadatos de la app y prepara el administrador SaaS configurado. No guarda la contrasena en codigo.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" autocomplete="off">
                <?php wp_nonce_field('captacion_app_reset_day_one'); ?>
                <input type="hidden" name="action" value="captacion_app_reset_day_one">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="captacion_reset_confirm">Confirmacion</label></th>
                        <td><input id="captacion_reset_confirm" class="regular-text" name="captacion_reset_confirm" placeholder="RESET" autocomplete="off"> <p class="description">Escribe RESET para ejecutar la limpieza total.</p></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="captacion_reset_admin_password">Contrasena admin SaaS</label></th>
                        <td><input id="captacion_reset_admin_password" class="regular-text" type="password" name="captacion_reset_admin_password" autocomplete="new-password"> <p class="description">Se aplicara al usuario <?php echo esc_html($settings['saas_admin_email']); ?>. No se almacena en archivos del tema.</p></td>
                    </tr>
                </table>
                <?php submit_button('Reset SaaS dia 1', 'delete', 'submit', false); ?>
            </form>
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        <form method="post" action="options.php">
            <?php settings_fields('captacion_app_settings_group'); ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="captacion_brand_name">Marca</label></th>
                    <td><input id="captacion_brand_name" class="regular-text" name="captacion_app_settings[brand_name]" value="<?php echo esc_attr($settings['brand_name']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_site_title">Titulo SEO</label></th>
                    <td><input id="captacion_site_title" class="large-text" name="captacion_app_settings[site_title]" value="<?php echo esc_attr($settings['site_title']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_meta_description">Descripcion SEO</label></th>
                    <td><textarea id="captacion_meta_description" class="large-text" rows="3" name="captacion_app_settings[meta_description]"><?php echo esc_textarea($settings['meta_description']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_hero_kicker">Texto superior portada</label></th>
                    <td><input id="captacion_hero_kicker" class="large-text" name="captacion_app_settings[hero_kicker]" value="<?php echo esc_attr($settings['hero_kicker']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_hero_title">Titulo principal</label></th>
                    <td>
                        <textarea id="captacion_hero_title" class="large-text code" rows="3" name="captacion_app_settings[hero_title]"><?php echo esc_textarea($settings['hero_title']); ?></textarea>
                        <p class="description">Puedes usar span con clases text-blue o text-green para mantener colores.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_hero_description">Descripcion principal</label></th>
                    <td><textarea id="captacion_hero_description" class="large-text" rows="3" name="captacion_app_settings[hero_description]"><?php echo esc_textarea($settings['hero_description']); ?></textarea></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_primary_cta">Boton principal</label></th>
                    <td><input id="captacion_primary_cta" class="regular-text" name="captacion_app_settings[primary_cta]" value="<?php echo esc_attr($settings['primary_cta']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_secondary_cta">Boton secundario</label></th>
                    <td><input id="captacion_secondary_cta" class="regular-text" name="captacion_app_settings[secondary_cta]" value="<?php echo esc_attr($settings['secondary_cta']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_contact_email">Email de contacto</label></th>
                    <td><input id="captacion_contact_email" class="regular-text" type="email" name="captacion_app_settings[contact_email]" value="<?php echo esc_attr($settings['contact_email']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_saas_admin_email">Administrador SaaS</label></th>
                    <td>
                        <input id="captacion_saas_admin_email" class="regular-text" type="email" name="captacion_app_settings[saas_admin_email]" value="<?php echo esc_attr($settings['saas_admin_email']); ?>">
                        <p class="description">Este usuario tendra acceso SaaS premium total al iniciar sesion. La contrasena se gestiona en Usuarios de WordPress, no en el tema.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_payment_link">Payment Link de Stripe</label></th>
                    <td>
                        <input id="captacion_stripe_payment_link" class="large-text" type="url" name="captacion_app_settings[stripe_payment_link]" value="<?php echo esc_attr($settings['stripe_payment_link']); ?>">
                        <p class="description">Pago de desbloqueo/compra de captacion. Ejemplo: https://buy.stripe.com/xxxxxxxxxxxx. No introduzcas claves secretas de Stripe.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_initial_link">Stripe Starter mensual</label></th>
                    <td>
                        <input id="captacion_stripe_membership_initial_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_initial_link]" value="<?php echo esc_attr($settings['stripe_membership_initial_link']); ?>">
                        <p class="description">Payment Link de 19 EUR/mes para Starter. Los 3 primeros meses se gestionan via trial interno.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_initial_annual_link">Stripe Starter anual</label></th>
                    <td>
                        <input id="captacion_stripe_membership_initial_annual_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_initial_annual_link]" value="<?php echo esc_attr($settings['stripe_membership_initial_annual_link']); ?>">
                        <p class="description">Payment Link anual de 190 EUR/año para Starter. Si se deja vacio, se usara el enlace mensual.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_professional_link">Stripe Plan Profesional mensual</label></th>
                    <td>
                        <input id="captacion_stripe_membership_professional_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_professional_link]" value="<?php echo esc_attr($settings['stripe_membership_professional_link']); ?>">
                        <p class="description">Payment Link de 29 EUR/mes para Profesional.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_professional_annual_link">Stripe Plan Profesional anual</label></th>
                    <td>
                        <input id="captacion_stripe_membership_professional_annual_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_professional_annual_link]" value="<?php echo esc_attr($settings['stripe_membership_professional_annual_link']); ?>">
                        <p class="description">Payment Link anual de 290 EUR/año para Profesional. Si se deja vacio, se usara el enlace mensual.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_agency_link">Stripe Plan Premium mensual</label></th>
                    <td>
                        <input id="captacion_stripe_membership_agency_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_agency_link]" value="<?php echo esc_attr($settings['stripe_membership_agency_link']); ?>">
                        <p class="description">Payment Link de 49 EUR/mes para Premium.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_agency_annual_link">Stripe Plan Premium anual</label></th>
                    <td>
                        <input id="captacion_stripe_membership_agency_annual_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_agency_annual_link]" value="<?php echo esc_attr($settings['stripe_membership_agency_annual_link']); ?>">
                        <p class="description">Payment Link anual de 490 EUR/año para Premium. Si se deja vacio, se usara el enlace mensual.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_marketplace_single_link">Stripe acceso individual</label></th>
                    <td>
                        <input id="captacion_stripe_marketplace_single_link" class="large-text" type="url" name="captacion_app_settings[stripe_marketplace_single_link]" value="<?php echo esc_attr($settings['stripe_marketplace_single_link']); ?>">
                        <p class="description">Payment Link de 10 EUR para un acceso individual. Los datos solo se desbloquean tras webhook confirmado.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_marketplace_plus_pack_link">Stripe pack Profesional</label></th>
                    <td>
                        <input id="captacion_stripe_marketplace_plus_pack_link" class="large-text" type="url" name="captacion_app_settings[stripe_marketplace_plus_pack_link]" value="<?php echo esc_attr($settings['stripe_marketplace_plus_pack_link']); ?>">
                        <p class="description">Payment Link de 5 EUR para 10 accesos extra.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_marketplace_premium_pack_link">Stripe pack Premium</label></th>
                    <td>
                        <input id="captacion_stripe_marketplace_premium_pack_link" class="large-text" type="url" name="captacion_app_settings[stripe_marketplace_premium_pack_link]" value="<?php echo esc_attr($settings['stripe_marketplace_premium_pack_link']); ?>">
                        <p class="description">Payment Link de 5 EUR para 15 accesos extra.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_mailchimp_api_key">Mailchimp API Key</label></th>
                    <td>
                        <input id="captacion_mailchimp_api_key" class="large-text" type="password" autocomplete="off" name="captacion_app_settings[mailchimp_api_key]" value="<?php echo esc_attr($settings['mailchimp_api_key']); ?>">
                        <p class="description">Clave API de Mailchimp. Se usa solo en servidor para enviar contactos desde los formularios.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_mailchimp_audience_id">Mailchimp Audience ID</label></th>
                    <td>
                        <input id="captacion_mailchimp_audience_id" class="regular-text" name="captacion_app_settings[mailchimp_audience_id]" value="<?php echo esc_attr($settings['mailchimp_audience_id']); ?>">
                        <p class="description">ID de la audiencia/lista donde se guardaran los contactos.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Login social</th>
                    <td>
                        <label><input type="checkbox" name="captacion_app_settings[social_login_enabled]" value="1" <?php checked($settings['social_login_enabled'], '1'); ?>> Mostrar botones Google/Apple en registro y acceso</label>
                        <p class="description">Activalo solo despues de instalar y configurar un plugin compatible, por ejemplo Sign In With Socials.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_webhook_api_key">API Key para webhooks</label></th>
                    <td>
                        <input id="captacion_webhook_api_key" class="regular-text" type="password" autocomplete="off" name="captacion_app_settings[webhook_api_key]" value="<?php echo esc_attr($settings['webhook_api_key']); ?>">
                        <p class="description">Clave compartida para integraciones externas. Debe enviarse en el header X-Captacion-Webhook-Key. No uses claves de Stripe ni contrasenas reales.</p>
                    </td>
                </tr>
                <tr>
                    <th colspan="2" style="padding-bottom:0;"><h2 style="margin:16px 0 4px;font-size:1.15em;font-weight:700;">Inteligencia Artificial (centralizada)</h2><p class="description" style="margin-bottom:8px;">Configura un proveedor de IA para uso interno: explicaciones de match, mejora de titulos/descripciones y el asistente Match IA. Los usuarios avanzados pueden traer su propia API key desde el panel privado.</p></th>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_ai_admin_provider">Proveedor de IA</label></th>
                    <td>
                        <select id="captacion_ai_admin_provider" name="captacion_app_settings[ai_admin_provider]">
                            <option value="">— Seleccionar —</option>
                            <?php foreach (captacion_app_ai_providers() as $provider_key => $provider_info) : ?>
                                <option value="<?php echo esc_attr($provider_key); ?>" <?php selected($settings['ai_admin_provider'], $provider_key); ?>><?php echo esc_html($provider_info['label']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description">Selecciona el proveedor para la IA centralizada de la plataforma.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_ai_admin_api_key">API Key</label></th>
                    <td>
                        <input id="captacion_ai_admin_api_key" class="large-text" type="password" autocomplete="off" name="captacion_app_settings[ai_admin_api_key]" value="<?php echo esc_attr($settings['ai_admin_api_key']); ?>">
                        <p class="description">Clave de API del proveedor seleccionado. Se almacena en la base de datos de WordPress.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_ai_admin_model">Modelo (opcional)</label></th>
                    <td>
                        <input id="captacion_ai_admin_model" class="regular-text" name="captacion_app_settings[ai_admin_model]" value="<?php echo esc_attr($settings['ai_admin_model']); ?>" placeholder="Ej: gpt-4o-mini">
                        <p class="description">Si se deja vacio, se usara el modelo por defecto del proveedor.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mailchimp double opt-in</th>
                    <td>
                        <label><input type="checkbox" name="captacion_app_settings[mailchimp_double_optin]" value="1" <?php checked($settings['mailchimp_double_optin'], '1'); ?>> Enviar confirmacion antes de suscribir</label>
                        <p class="description">Desmarcado: el contacto entra directamente como suscrito. Marcado: queda pendiente de confirmar.</p>
                    </td>
                'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

            </table>
            <?php submit_button('Guardar cambios'); ?>
        </form>
    </div>
    <?php
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_setup() {
    add_theme_support('title-tag');
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_media_url($relative_path) {
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $relative_path = ltrim((string) $relative_path, '/');
    if ($relative_path === '') {
        return get_template_directory_uri();
    }
    if (isset($cache[$relative_path])) {
        return $cache[$relative_path];
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $fallback_url = get_template_directory_uri() . '/' . $relative_path;
    $filename = basename($relative_path);
    if ($filename === '') {
        $cache[$relative_path] = $fallback_url;
        return $fallback_url;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $attachments = get_posts(array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'posts_per_page' => 20,
        'fields' => 'ids',
        'meta_query' => array(
            array(
                'key' => '_wp_attached_file',
                'value' => $filename,
                'compare' => 'LIKE',
            ),
        ),
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    foreach ($attachments as $attachment_id) {
        $attached_file = (string) get_post_meta($attachment_id, '_wp_attached_file', true);
        if (basename($attached_file) !== $filename) {
            continue;
        }
        $url = wp_get_attachment_url($attachment_id);
        if ($url) {
            $cache[$relative_path] = $url;
            return $url;
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $cache[$relative_path] = $fallback_url;
    return $fallback_url;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_output_theme_favicon() {
    $favicon_uri = captacion_app_media_url('media/favicon-compra-captacion.png');
    echo '<link rel="icon" type="image/png" sizes="512x512" href="' . esc_url($favicon_uri) . '">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url($favicon_uri) . '">' . "\n";
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_enqueue_assets() {
    wp_enqueue_style(
        'captacion-app-theme',
        get_stylesheet_uri(),
        array(),
        '1.5.3'
    );
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',
       'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_mailchimp_datacenter($api_key) {
    if (!is_string($api_key) || strpos($api_key, '-') === false) {
        return '';
    }
    $parts = explode('-', $api_key);
    return sanitize_key(end($parts));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_mailchimp_allowed_tags() {
    return array(
        'registro-inicio',
        'contacto',
        'busco-captacion',
        'ofrecer-captacion',
        'plan-inicial',
        'plan-profesional',
        'recursos-gratis',
        'interes-herramientas-profesionales',
        'reporte-denuncia',
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',
       'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_mailchimp_event_category($source, $tags = array()) {
    $source = sanitize_key((string) $source);
    $tags = array_map('sanitize_key', is_array($tags) ? $tags : array());
    if ($source === 'registro-inicio' || in_array('registro-inicio', $tags, true)) {
        return 'registro';
    }
    if ($source === 'contacto' || in_array('contacto', $tags, true)) {
        return 'contacto';
    }
    if ($source === 'reporte-denuncia' || in_array('reporte-denuncia', $tags, true)) {
        return 'reporte_denuncia';
    }
    if ($source === 'busco-captacion' || in_array('busco-captacion', $tags, true)) {
        return 'busco_captacion';
    }
    if ($source === 'ofrecer-captacion' || in_array('ofrecer-captacion', $tags, true)) {
        return 'ofrecer_captacion';
    }
    return 'general';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_events_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_mail_events';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_install_mail_events_table() {
    global $wpdb;
    $table = captacion_app_events_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        category VARCHAR(60) NOT NULL,
        source VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        name VARCHAR(190) DEFAULT '' NOT NULL,
        agency VARCHAR(190) DEFAULT '' NOT NULL,
        phone VARCHAR(80) DEFAULT '' NOT NULL,
        reference VARCHAR(190) DEFAULT '' NOT NULL,
        message TEXT NULL,
        tags TEXT NULL,
        payload LONGTEXT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY  (id),
        KEY category (category),
        KEY source (source),
        KEY email (email),
        KEY created_at (created_at)
    ) {$charset_collate};";
    dbDelta($sql);
    update_option('captacion_mail_events_table_version', '20260615');
}
add_action('after_switch_theme', 'captacion_app_install_mail_events_table');
function captacion_app_maybe_install_mail_events_table() {
    if (get_option('captacion_mail_events_table_version') !== '20260615') {
        captacion_app_install_mail_events_table();
    }
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',
       'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_records_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_app_records';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_install_records_table() {
    global $wpdb;
    $table = captacion_app_records_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        record_type VARCHAR(60) NOT NULL,
        record_key VARCHAR(190) DEFAULT '' NOT NULL,
        user_id BIGINT UNSIGNED DEFAULT 0 NOT NULL,
        user_email VARCHAR(190) DEFAULT '' NOT NULL,
        title VARCHAR(255) DEFAULT '' NOT NULL,
        status VARCHAR(80) DEFAULT '' NOT NULL,
        related_id VARCHAR(190) DEFAULT '' NOT NULL,
        payload LONGTEXT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
        import_batch_id VARCHAR(80) DEFAULT '' NOT NULL,
        data_origin VARCHAR(40) DEFAULT 'manual' NOT NULL,
        is_demo TINYINT(1) DEFAULT 0 NOT NULL,
        privacy_scope VARCHAR(40) DEFAULT 'private_user' NOT NULL,
        consent_status VARCHAR(40) DEFAULT '' NOT NULL,
        source_file_name VARCHAR(190) DEFAULT '' NOT NULL,
        source_hash CHAR(64) DEFAULT '' NOT NULL,
        deleted_at DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY record_type_key (record_type, record_key),
        KEY record_type (record_type),
        KEY user_email (user_email),
        KEY status (status),
        KEY related_id (related_id),
        KEY updated_at (updated_at),
        KEY owner_user_id (owner_user_id),
        KEY import_batch_id (import_batch_id),
        KEY data_origin (data_origin),
        KEY is_demo (is_demo),
        KEY privacy_scope (privacy_scope),
        KEY deleted_at (deleted_at)
    ) {$charset_collate};";
    dbDelta($sql);
    update_option('captacion_app_records_table_version', '20260627');
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_maybe_install_records_table() {
    if (get_option('captacion_app_records_table_version') !== '20260627') {
        captacion_app_install_records_table();
    }
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_migrate_existing_records_ownership() {
    if (get_option('captacion_app_records_data_migration') === '20260627') return;
    global $wpdb;
    $table = captacion_app_records_table_name();
    $wpdb->query("UPDATE {$table} SET owner_user_id = user_id WHERE owner_user_id = 0 AND user_id > 0");
    $wpdb->query("UPDATE {$table} SET data_origin = 'manual' WHERE data_origin = ''");
    $wpdb->query("UPDATE {$table} SET privacy_scope = 'private_user' WHERE privacy_scope = ''");
    update_option('captacion_app_records_data_migration', '20260627');
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_soft_delete_legacy_seed_records() {
    if (get_option('captacion_app_legacy_seed_cleanup') === '20260627-production') return;
    global $wpdb;
    $table = captacion_app_records_table_name();
    $now = current_time('mysql');
    $wpdb->query($wpdb->prepare(
        "UPDATE {$table} SET deleted_at = %s WHERE deleted_at IS NULL AND record_type = 'property' AND record_key IN ('prop-1','prop-2','prop-3')",
        $now
    ));
    $wpdb->query($wpdb->prepare(
        "UPDATE {$table} SET deleted_at = %s WHERE deleted_at IS NULL AND record_type = 'need' AND record_key IN ('need-1','need-2','need-3')",
        $now
    ));
    $wpdb->query($wpdb->prepare(
        "UPDATE {$table} SET deleted_at = %s WHERE deleted_at IS NULL AND record_type = 'property' AND (title = 'Piso para reforma en Galicia' OR title = 'Edificio de Oficinas con parking subterráneo' OR title = 'Local comercial prime en rentabilidad')",
        $now
    ));
    $wpdb->query($wpdb->prepare(
        "UPDATE {$table} SET deleted_at = %s WHERE deleted_at IS NULL AND record_type = 'need' AND (title = 'Inversor busca piso para reformar en O Couto' OR title = 'Particular busca Chalet de lujo en Pozuelo' OR title = 'Profesional busca Nave Industrial para logística')",
        $now
    ));
    update_option('captacion_app_legacy_seed_cleanup', '20260627-production');
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_import_batches_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_import_batches';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_install_import_batches_table() {
    global $wpdb;
    $table = captacion_app_import_batches_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        import_batch_id VARCHAR(80) NOT NULL,
        owner_user_id BIGINT UNSIGNED NOT NULL DEFAULT 0,
        created_by BIGINT UNSIGNED NOT NULL DEFAULT 0,
        data_origin VARCHAR(40) NOT NULL,
        is_demo TINYINT(1) NOT NULL DEFAULT 0,
        privacy_scope VARCHAR(40) NOT NULL,
        source_file_name VARCHAR(190) DEFAULT '' NOT NULL,
        source_hash CHAR(64) DEFAULT '' NOT NULL,
        schema_version VARCHAR(20) DEFAULT '1.0' NOT NULL,
        status VARCHAR(40) NOT NULL DEFAULT 'pending',
        records_total INT UNSIGNED NOT NULL DEFAULT 0,
        records_imported INT UNSIGNED NOT NULL DEFAULT 0,
        records_rejected INT UNSIGNED NOT NULL DEFAULT 0,
        summary_json LONGTEXT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        deleted_at DATETIME NULL,
        PRIMARY KEY (id),
        UNIQUE KEY import_batch_id (import_batch_id),
        KEY owner_user_id (owner_user_id),
        KEY data_origin (data_origin),
        KEY privacy_scope (privacy_scope),
        KEY status (status),
        KEY deleted_at (deleted_at)
    ) {$charset_collate};";
    dbDelta($sql);
    update_option('captacion_import_batches_table_version', '20260627');
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_maybe_install_import_batches_table() {
    if (get_option('captacion_import_batches_table_version') !== '20260627') {
        captacion_app_install_import_batches_table();
    }
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_allowed_record_types() {
    return array(
        'property',
        'need',
        'smart_match',
        'report',
        'notification',
        'access_request',
        'activity',
        'user_preferences',
        'dashboard_state',
        'task',
        'generated_pdf',
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_generate_import_batch_id() {
    return 'batch_' . bin2hex(random_bytes(12));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_create_import_batch($data) {
    global $wpdb;
    captacion_app_maybe_install_import_batches_table();
    $table = captacion_app_import_batches_table_name();
    $now = current_time('mysql');
    $batch_id = $data['import_batch_id'] ?? captacion_app_generate_import_batch_id();
    $row = array(
        'import_batch_id' => $batch_id,
        'owner_user_id' => absint($data['owner_user_id'] ?? 0),
        'created_by' => absint($data['created_by'] ?? get_current_user_id()),
        'data_origin' => sanitize_key($data['data_origin'] ?? 'manual'),
        'is_demo' => !empty($data['is_demo']) ? 1 : 0,
        'privacy_scope' => sanitize_key($data['privacy_scope'] ?? 'private_user'),
        'source_file_name' => sanitize_text_field($data['source_file_name'] ?? ''),
        'source_hash' => sanitize_text_field($data['source_hash'] ?? ''),
        'schema_version' => sanitize_text_field($data['schema_version'] ?? '1.0'),
        'status' => 'pending',
        'records_total' => absint($data['records_total'] ?? 0),
        'records_imported' => 0,
        'records_rejected' => 0,
        'summary_json' => !empty($data['summary_json']) ? wp_json_encode($data['summary_json']) : null,
        'created_at' => $now,
        'updated_at' => $now,
    );
    $inserted = $wpdb->insert($table, $row);
    if ($inserted === false) {
        return new WP_Error('captacion_batch_create_failed', 'No se pudo registrar el XML importado: ' . $wpdb->last_error, array('status' => 500));
    }
    return absint($wpdb->insert_id);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_update_import_batch_status($batch_id, $status, $extra = array()) {
    global $wpdb;
    $table = captacion_app_import_batches_table_name();
    $data = array('status' => sanitize_key($status), 'updated_at' => current_time('mysql'));
    if (isset($extra['records_total'])) $data['records_total'] = absint($extra['records_total']);
    if (isset($extra['records_imported'])) $data['records_imported'] = absint($extra['records_imported']);
    if (isset($extra['records_rejected'])) $data['records_rejected'] = absint($extra['records_rejected']);
    if (isset($extra['source_hash'])) $data['source_hash'] = sanitize_text_field($extra['source_hash']);
    if (isset($extra['summary_json'])) $data['summary_json'] = wp_json_encode($extra['summary_json']);
    return $wpdb->update($table, $data, array('import_batch_id' => $batch_id));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_visible_import_batch_statuses() {
    return array('active', 'error');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_hidden_import_batch_statuses() {
    return array('paused', 'pending_deletion', 'deleted');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_is_terminal_workflow_status($status) {
    $status = strtolower(remove_accents(sanitize_text_field((string) $status)));
    if ($status === '') return false;
    $terminal = array('completed', 'completada', 'completado', 'closed', 'cerrada', 'cerrado', 'cancelled', 'canceled', 'cancelada', 'cancelado', 'rejected', 'rechazada', 'rechazado', 'denied', 'aprobada', 'aprobado', 'approved', 'done', 'finalizada', 'finalizado');
    foreach ($terminal as $needle) {
        if (strpos($status, $needle) !== false) return true;
    }
    return false;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_get_import_batch($batch_id) {
    global $wpdb;
    $table = captacion_app_import_batches_table_name();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE import_batch_id = %s", $batch_id), ARRAY_A);
    return $row;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_get_import_batch_by_source($owner_user_id, $data_origin, $source_file_name) {
    global $wpdb;
    $table = captacion_app_import_batches_table_name();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE owner_user_id = %d AND data_origin = %s AND source_file_name = %s AND deleted_at IS NULL AND (status IS NULL OR status != 'deleted') ORDER BY created_at DESC LIMIT 1",
        absint($owner_user_id), sanitize_key($data_origin), sanitize_text_field($source_file_name)
    ), ARRAY_A);
    return $row;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_user_can_manage_import_batch($batch) {
    if (!$batch || !is_array($batch)) return false;
    if (current_user_can('manage_options')) return true;
    $user_id = get_current_user_id();
    return absint($batch['owner_user_id']) === $user_id;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_get_import_batch_pending_blockers($batch_id) {
    global $wpdb;
    $records_table = captacion_app_records_table_name();
    $properties = $wpdb->get_results($wpdb->prepare(
        "SELECT record_key, related_id, payload FROM {$records_table} WHERE import_batch_id = %s AND record_type = 'property' AND deleted_at IS NULL LIMIT 1000",
        $batch_id
    ), ARRAY_A);
    if (empty($properties)) {
        $batch = captacion_app_get_import_batch($batch_id);
        if ($batch && !empty($batch['source_file_name'])) {
            $properties = $wpdb->get_results($wpdb->prepare(
                "SELECT record_key, related_id, payload FROM {$records_table} WHERE owner_user_id = %d AND data_origin = %s AND source_file_name = %s AND record_type = 'property' AND deleted_at IS NULL LIMIT 1000",
                absint($batch['owner_user_id']), sanitize_key($batch['data_origin']), sanitize_text_field($batch['source_file_name'])
            ), ARRAY_A);
        }
    }
    $property_ids = array();
    foreach ($properties as $property) {
        foreach (array($property['record_key'] ?? '', $property['related_id'] ?? '') as $value) {
            $value = sanitize_text_field($value);
            if ($value !== '') $property_ids[$value] = true;
        }
        $payload = json_decode($property['payload'] ?: '{}', true);
        if (is_array($payload)) {
            foreach (array($payload['id'] ?? '', $payload['reference'] ?? '', $payload['recordKey'] ?? '') as $value) {
                $value = sanitize_text_field($value);
                if ($value !== '') $property_ids[$value] = true;
            }
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (empty($property_ids)) {
        return array('count' => 0, 'items' => array());
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $blockers = array();
    $candidate_rows = $wpdb->get_results(
        "SELECT record_type, record_key, title, status, related_id, payload FROM {$records_table} WHERE record_type IN ('access_request','operation','task') AND deleted_at IS NULL ORDER BY updated_at DESC LIMIT 500",
        ARRAY_A
    );
    foreach ($candidate_rows as $row) {
        $status = sanitize_text_field($row['status'] ?? '');
        if (captacion_app_is_terminal_workflow_status($status)) continue;
        $payload = json_decode($row['payload'] ?: '{}', true);
        $references = array(sanitize_text_field($row['related_id'] ?? ''));
        if (is_array($payload)) {
            foreach (array('propertyId', 'property_id', 'related_id', 'opportunity_id', 'id') as $key) {
                if (!empty($payload[$key])) $references[] = sanitize_text_field($payload[$key]);
            }
        }
        foreach ($references as $reference) {
            if ($reference !== '' && isset($property_ids[$reference])) {
                $blockers[] = array(
                    'type' => sanitize_key($row['record_type']),
                    'id' => sanitize_text_field($row['record_key']),
                    'title' => sanitize_text_field($row['title']),
                    'status' => sanitize_text_field($row['status']),
                );
                break;
            }
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return array('count' => count($blockers), 'items' => array_slice($blockers, 0, 10));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_hard_delete_import_batch_records($batch_id) {
    global $wpdb;
    $records_table = captacion_app_records_table_name();
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$records_table} WHERE import_batch_id = %s",
        $batch_id
    ));
    $batch = captacion_app_get_import_batch($batch_id);
    if ($batch && !empty($batch['source_file_name'])) {
        $deleted += $wpdb->query($wpdb->prepare(
            "DELETE FROM {$records_table} WHERE owner_user_id = %d AND data_origin = %s AND source_file_name = %s",
            absint($batch['owner_user_id']),
            sanitize_key($batch['data_origin']),
            sanitize_text_field($batch['source_file_name'])
        ));
    }
    return $deleted;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_hard_delete_import_batch($batch_id) {
    global $wpdb;
    $batches_table = captacion_app_import_batches_table_name();
    return $wpdb->query($wpdb->prepare(
        "DELETE FROM {$batches_table} WHERE import_batch_id = %s",
        $batch_id
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_complete_pending_feed_deletions($owner_user_id = 0) {
    global $wpdb;
    captacion_app_maybe_install_import_batches_table();
    captacion_app_maybe_install_records_table();
    $batches_table = captacion_app_import_batches_table_name();
    if ($owner_user_id) {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$batches_table} WHERE status = 'pending_deletion' AND deleted_at IS NULL AND owner_user_id = %d LIMIT 50",
            absint($owner_user_id)
        ), ARRAY_A);
    } else {
        $rows = $wpdb->get_results("SELECT * FROM {$batches_table} WHERE status = 'pending_deletion' AND deleted_at IS NULL LIMIT 50", ARRAY_A);
    }
    $completed = array();
    foreach ($rows as $batch) {
        $batch_id = $batch['import_batch_id'];
        $blockers = captacion_app_get_import_batch_pending_blockers($batch_id);
        if (!empty($blockers['count'])) continue;
        captacion_app_hard_delete_import_batch_records($batch_id);
        captacion_app_hard_delete_import_batch($batch_id);
        captacion_app_log_resource_event(array('resource_id' => 'import_batch_delete'), 'xml_batch_deletion_completed', array(
            'import_batch_id' => $batch_id,
            'owner_user_id' => $batch['owner_user_id'],
        ));
        $completed[] = $batch_id;
    }
    return $completed;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_resource_catalog_defaults() {
    return array(
        'nda' => array('resource_id'=>'nda','title'=>'Modelo de acuerdo de confidencialidad / NDA','description'=>'Modelo orientativo para proteger la información compartida durante una colaboración.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/01_Modelo_acuerdo_confidencialidad_NDA.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','tax_id','email','phone','date','reference','notes')),
        'collaboration' => array('resource_id'=>'collaboration','title'=>'Modelo de acuerdo de colaboración entre profesionales','description'=>'Base para documentar funciones, honorarios y reglas de colaboración.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/02_Modelo_acuerdo_colaboracion_profesionales.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','tax_id','email','phone','date','reference','notes')),
        'capture-checklist' => array('resource_id'=>'capture-checklist','title'=>'Checklist documental de captación','description'=>'Lista de comprobación para preparar un expediente de captación.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/03_Checklist_documental_captacion.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','email','phone','date','reference','notes')),
        'capture-sheet' => array('resource_id'=>'capture-sheet','title'=>'Ficha profesional de captación','description'=>'Ficha estructurada para resumir producto, situación y condiciones.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/04_Ficha_profesional_captacion.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','tax_id','email','phone','date','reference','notes')),
        'demand-sheet' => array('resource_id'=>'demand-sheet','title'=>'Ficha de demanda activa','description'=>'Documento para registrar una búsqueda profesional y sus criterios.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/05_Ficha_demanda_activa.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','email','phone','date','reference','notes')),
        'safe-collaboration-guide' => array('resource_id'=>'safe-collaboration-guide','title'=>'Guía de colaboración segura','description'=>'Guía práctica para compartir información y avanzar operaciones con trazabilidad.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/06_Guia_colaboracion_segura.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','email','phone','date','reference','notes')),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_resource_catalog() {
    static $catalog_cache = null;
    if ($catalog_cache !== null) {
        return $catalog_cache;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $catalog = captacion_app_resource_catalog_defaults();
    $saved = get_option('captacion_app_resource_settings', array());
    foreach ($catalog as $resource_id => &$resource) {
        if (isset($saved[$resource_id]) && is_array($saved[$resource_id])) {
            $saved_resource = $saved[$resource_id];
            $saved_pdf_url = (string) ($saved_resource['static_pdf_url'] ?? '');
            if ($saved_pdf_url === '' || strpos($saved_pdf_url, '/recursos/plantilla-') !== false) {
                unset($saved_resource['static_pdf_url']);
            }
            $resource = array_merge($resource, $saved_resource);
        }
        $pdf_attachment_id = absint($resource['static_pdf_attachment_id']);
        $docx_attachment_id = absint($resource['docx_template_attachment_id']);
        if ($pdf_attachment_id && get_post_mime_type($pdf_attachment_id) === 'application/pdf') {
            $resource['static_pdf_url'] = wp_get_attachment_url($pdf_attachment_id);
        }
        if ($docx_attachment_id) {
            $mime = (string) get_post_mime_type($docx_attachment_id);
            if (in_array($mime, array('application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword'), true)) {
                $resource['docx_template_url'] = wp_get_attachment_url($docx_attachment_id);
            }
        }
    }
    unset($resource);
    $catalog_cache = $catalog;
    return $catalog_cache;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_resource_local_pdf_path($resource_id) {
    $map = array(
        'nda' => 'recursos/plantilla-nda-confidencialidad-captacion-app.pdf',
        'collaboration' => 'recursos/plantilla-acuerdo-colaboracion-honorarios-captacion-app.pdf',
    );
    if (empty($map[$resource_id])) return '';
    $path = trailingslashit(get_template_directory()) . $map[$resource_id];
    return file_exists($path) ? $path : '';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_resource_template_pdf_url($resource_id) {
    $resource_id = sanitize_key($resource_id);
    return wp_nonce_url(
        admin_url('admin-post.php?action=captacion_resource_template_pdf_download&resource=' . rawurlencode($resource_id)),
        'captacion_resource_template_' . $resource_id
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_download_resource_template_pdf() {
    $resource_id = sanitize_key($_GET['resource'] ?? '');
    if (!$resource_id) wp_die('Recurso no encontrado.', 'Compra Captación', array('response' => 404));
    check_admin_referer('captacion_resource_template_' . $resource_id);
    $resource = captacion_app_resource_access_check($resource_id, false);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $local_path = captacion_app_resource_local_pdf_path($resource_id);
    if ($local_path) {
        $pdf = file_get_contents($local_path);
    } else {
        $pdf = Captacion_PDF_Generator::generate($resource['title'], array(
            'Tipo de documento' => 'Plantilla base',
            'Uso' => 'Documento orientativo para adaptar con datos profesionales.',
            'Nota' => 'Revisión jurídica recomendada antes de uso contractual definitivo.',
        ));
    }
    if (!$pdf) wp_die('No se pudo preparar el PDF.', 'Compra Captación', array('response' => 500));
    captacion_app_log_resource_event($resource, 'download_generated_pdf');
    nocache_headers();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . sanitize_file_name($resource['title']) . '.pdf"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
    exit;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_resource_events_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_resource_events';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_install_resource_events_table() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = captacion_app_resource_events_table_name();
    $charset = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        resource_id VARCHAR(120) NOT NULL,
        resource_title VARCHAR(255) NOT NULL,
        plan_type VARCHAR(40) NOT NULL,
        action_type VARCHAR(50) NOT NULL,
        generated_file_id BIGINT UNSIGNED NULL,
        generated_file_url TEXT NULL,
        form_data_json LONGTEXT NULL,
        ip_address VARCHAR(64) NOT NULL DEFAULT '',
        user_agent VARCHAR(255) NOT NULL DEFAULT '',
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id),
        KEY resource_id (resource_id),
        KEY action_type (action_type),
        KEY created_at (created_at)
    ) {$charset};");
    update_option('captacion_resource_events_table_version', '20260620');
}
add_action('after_switch_theme', 'captacion_app_install_resource_events_table');
function captacion_app_maybe_install_resource_events_table() {
    if (get_option('captacion_resource_events_table_version') !== '20260620') captacion_app_install_resource_events_table();
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_log_resource_event($resource, $action_type, $data = array()) {
    global $wpdb;
    $allowed = array('open_create_pdf','generate_pdf','download_generated_pdf');
    if (!in_array($action_type, $allowed, true)) return false;
    captacion_app_maybe_install_resource_events_table();
    $state = captacion_app_get_user_access_state(get_current_user_id());
    return $wpdb->insert(captacion_app_resource_events_table_name(), array(
        'user_id'=>get_current_user_id(),
        'resource_id'=>$resource['resource_id'],
        'resource_title'=>$resource['title'],
        'plan_type'=>$state['plan_type'],
        'action_type'=>$action_type,
        'generated_file_id'=>absint($data['generated_file_id'] ?? 0) ?: null,
        'generated_file_url'=>esc_url_raw($data['generated_file_url'] ?? ''),
        'form_data_json'=>isset($data['form_data']) ? wp_json_encode($data['form_data']) : null,
        'ip_address'=>substr(sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? ''), 0, 64),
        'user_agent'=>substr(sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
        'created_at'=>current_time('mysql'),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

class Captacion_PDF_Generator {
    public static function generate($title, $fields) {
        $lines = array(
            'COMPRA CAPTACIÓN',
            strtoupper((string) $title),
            '',
            'Documento personalizado generado por el usuario.',
            'Plantilla base: recurso profesional descargable de Compra Captación.',
            'Revision juridica recomendada antes de uso contractual definitivo.',
            '',
            'DATOS COMPLETADOS',
            ''
        );
        foreach ($fields as $label => $value) {
            if ($value !== '') $lines[] = $label . ': ' . $value;
        }
        $lines[] = '';
        $lines[] = 'TRAZABILIDAD';
        $lines[] = 'Generado: ' . current_time('mysql');
        $lines[] = 'Uso previsto: revision operativa, descarga y envio por el usuario.';
        $text = implode("\n", $lines);
        if (function_exists('iconv')) {
            $converted = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
            if ($converted !== false) $text = $converted;
        }
        $content = "BT\n/F1 11 Tf\n50 790 Td\n";
        foreach (explode("\n", $text) as $index => $line) {
            if ($index) $content .= "0 -17 Td\n";
            $content .= '(' . str_replace(array('\\','(',')'), array('\\\\','\\(','\\)'), $line) . ") Tj\n";
        }
        $content .= "ET";
        $objects = array(
            '<< /Type /Catalog /Pages 2 0 R >>',
            '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',
            "<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream",
            '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        );
        $pdf = "%PDF-1.4\n";
        $offsets = array(0);
        foreach ($objects as $i => $object) { $offsets[] = strlen($pdf); $pdf .= ($i + 1) . " 0 obj\n{$object}\nendobj\n"; }
        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";
        foreach (array_slice($offsets, 1) as $offset) $pdf .= sprintf("%010d 00000 n \n", $offset);
        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";
        return $pdf;
    }
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_resource_access_check($resource_id, $require_create = false) {
    $catalog = captacion_app_resource_catalog();
    if (!isset($catalog[$resource_id])) return new WP_Error('captacion_resource_missing', 'Recurso no encontrado.', array('status'=>404));
    if (!is_user_logged_in()) return new WP_Error('captacion_resource_login', 'Debes iniciar sesion.', array('status'=>401));
    if (!captacion_app_is_email_verified(get_current_user_id())) return new WP_Error('captacion_resource_verify', 'Debes verificar tu correo.', array('status'=>403));
    $state = captacion_app_get_user_access_state(get_current_user_id());
    $levels = array('basic'=>0, 'professional_plus'=>1, 'premium'=>2);
    $minimum_plan = $catalog[$resource_id]['plan_access'] ?? 'basic';
    if (($levels[$state['plan_type']] ?? 0) < ($levels[$minimum_plan] ?? 0)) return new WP_Error('captacion_resource_plan', 'Tu plan no incluye este recurso.', array('status'=>403));
    return $catalog[$resource_id];
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_generate_resource_pdf(WP_REST_Request $request) {
    if (!captacion_app_rest_rate_limit('generate_pdf_' . get_current_user_id(), 10, HOUR_IN_SECONDS)) return new WP_Error('captacion_pdf_rate', 'Limite temporal de generacion alcanzado.', array('status'=>429));
    $resource_id = sanitize_key((string) $request->get_param('resource_id'));
    $resource = captacion_app_resource_access_check($resource_id, true);
    if (is_wp_error($resource)) return $resource;
    $fields = array(
        'Nombre profesional'=>sanitize_text_field((string)$request->get_param('professional_name')),
        'Agencia/empresa'=>sanitize_text_field((string)$request->get_param('company')),
        'NIF/CIF'=>sanitize_text_field((string)$request->get_param('tax_id')),
        'Email'=>sanitize_email((string)$request->get_param('email')),
        'Telefono'=>sanitize_text_field((string)$request->get_param('phone')),
        'Fecha'=>sanitize_text_field((string)$request->get_param('date')),
        'Referencia interna'=>sanitize_text_field((string)$request->get_param('reference')),
        'Observaciones'=>sanitize_textarea_field((string)$request->get_param('notes')),
    );
    if (strlen($fields['Nombre profesional']) < 3 || !is_email($fields['Email'])) return new WP_Error('captacion_pdf_fields', 'Completa nombre profesional y email.', array('status'=>422));
    $upload = wp_upload_dir();
    $directory = trailingslashit($upload['basedir']) . 'captacion-private/' . get_current_user_id();
    if (!wp_mkdir_p($directory)) return new WP_Error('captacion_pdf_storage', 'No se pudo preparar el almacenamiento.', array('status'=>500));
    if (!file_exists($directory . '/index.php')) file_put_contents($directory . '/index.php', "<?php exit;\n");
    if (!file_exists($directory . '/.htaccess')) file_put_contents($directory . '/.htaccess', "Require all denied\nDeny from all\n");
    if (!file_exists($directory . '/web.config')) file_put_contents($directory . '/web.config', "<?xml version=\"1.0\"?><configuration><system.webServer><authorization><deny users=\"*\" /></authorization></system.webServer></configuration>");
    $filename = wp_generate_password(32, false, false) . '.pdf';
    $path = trailingslashit($directory) . $filename;
    if (file_put_contents($path, Captacion_PDF_Generator::generate($resource['title'], $fields)) === false) return new WP_Error('captacion_pdf_write', 'No se pudo generar el PDF.', array('status'=>500));
    $record_id = captacion_app_upsert_record(array('record_type'=>'generated_pdf','record_key'=>'generated-' . get_current_user_id() . '-' . wp_generate_uuid4(),'user_id'=>get_current_user_id(),'user_email'=>wp_get_current_user()->user_email,'title'=>$resource['title'],'status'=>'generated','related_id'=>$resource_id,'payload'=>array('resource_id'=>$resource_id,'path'=>$path,'filename'=>$filename)));
    if (is_wp_error($record_id) || !absint($record_id)) return new WP_Error('captacion_pdf_record', 'El PDF se genero, pero no se pudo registrar la descarga.', array('status'=>500));
    $download_url = wp_nonce_url(admin_url('admin-post.php?action=captacion_generated_pdf_download&file_id=' . absint($record_id)), 'captacion_generated_pdf_' . absint($record_id));
    $preview_url = wp_nonce_url(admin_url('admin-post.php?action=captacion_generated_pdf_download&preview=1&file_id=' . absint($record_id)), 'captacion_generated_pdf_' . absint($record_id));
    captacion_app_log_resource_event($resource, 'generate_pdf', array('generated_file_id'=>$record_id,'generated_file_url'=>$download_url,'form_data'=>$fields));
    return rest_ensure_response(array('ok'=>true,'downloadUrl'=>$download_url,'previewUrl'=>$preview_url));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_render_create_pdf_page() {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path !== 'recursos/crear-pdf') return;
    $resource_id = sanitize_key($_GET['resource'] ?? '');
    $resource = captacion_app_resource_access_check($resource_id, true);
    if (is_wp_error($resource)) {
        if (!is_user_logged_in()) wp_safe_redirect(home_url('/#/inicio'));
        else wp_die(esc_html($resource->get_error_message()), 'Compra Captación', array('response'=>$resource->get_error_data()['status'] ?? 403));
        exit;
    }
    captacion_app_log_resource_event($resource, 'open_create_pdf');
    $user = wp_get_current_user();
    $endpoint = rest_url('captacion/v1/resources/generate');
    $nonce = wp_create_nonce('wp_rest');
    ?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo esc_html($resource['title']); ?></title><style>body{font-family:Arial,sans-serif;background:#f1f5f9;color:#10233c;margin:0}.wrap{max-width:760px;margin:40px auto;padding:24px}.panel{background:#fff;border:1px solid #dbe5ef;padding:28px;border-radius:8px}label{display:block;font-size:13px;font-weight:700;margin-top:15px}input,textarea{width:100%;box-sizing:border-box;margin-top:6px;padding:12px;border:1px solid #cbd5e1;border-radius:7px}button,a.button{display:inline-block;margin-top:18px;padding:12px 18px;border:0;border-radius:7px;background:#1b67d6;color:#fff;text-decoration:none;font-weight:700;cursor:pointer}.secondary{background:#10233c}.note{font-size:12px;color:#64748b;margin-top:18px}.error{color:#b91c1c;font-size:13px}</style></head><body><main class="wrap"><a href="<?php echo esc_url(home_url('/#/recursos')); ?>">Volver a recursos</a><div class="panel"><h1><?php echo esc_html($resource['title']); ?></h1><p><?php echo esc_html($resource['description']); ?></p><form id="pdf-form"><label>Nombre profesional *<input name="professional_name" required value="<?php echo esc_attr($user->display_name); ?>"></label><label>Agencia/empresa opcional<input name="company"></label><label>NIF/CIF opcional<input name="tax_id"></label><label>Email *<input name="email" type="email" required value="<?php echo esc_attr($user->user_email); ?>"></label><label>Teléfono<input name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'captacion_phone', true)); ?>"></label><label>Fecha<input name="date" type="date" value="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label><label>Referencia interna<input name="reference"></label><label>Observaciones<textarea name="notes" rows="5"></textarea></label><button type="button" class="secondary" onclick="preview()">Vista previa</button> <button type="submit">Generar PDF</button><div id="result"></div></form><p class="note">Documento orientativo generado por el usuario. Revisar antes de firmar o utilizar.</p></div></main><script>const form=document.getElementById('pdf-form'),result=document.getElementById('result');function preview(){const d=new FormData(form);result.innerHTML='<div class="note"><strong>Vista previa</strong><br>'+[...d.entries()].map(x=>x[0]+': '+String(x[1]).replace(/[<>&]/g,'')).join('<br>')+'</div>'}form.addEventListener('submit',async e=>{e.preventDefault();result.textContent='Generando...';const payload=Object.fromEntries(new FormData(form));payload.resource_id=<?php echo wp_json_encode($resource_id); ?>;try{const r=await fetch(<?php echo wp_json_encode($endpoint); ?>,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':<?php echo wp_json_encode($nonce); ?>},body:JSON.stringify(payload)});const d=await r.json();if(!r.ok||!d.ok)throw new Error(d.message||'No se pudo generar');result.innerHTML='<a class="button" href="'+d.downloadUrl+'">Descargar PDF</a>'}catch(err){result.innerHTML='<p class="error">'+err.message+'</p>'}});</script></body></html><?php
    exit;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_resource_field_labels() {
    return array(
        'professional_name' => array('label' => 'Nombre profesional', 'required' => true, 'type' => 'text', 'placeholder' => 'Nombre y apellidos'),
        'company' => array('label' => 'Agencia o empresa', 'required' => false, 'type' => 'text', 'placeholder' => 'Nombre comercial o razón social'),
        'tax_id' => array('label' => 'NIF/CIF', 'required' => false, 'type' => 'text', 'placeholder' => 'Dato fiscal si procede'),
        'email' => array('label' => 'Email profesional', 'required' => true, 'type' => 'email', 'placeholder' => 'correo@agencia.es'),
        'phone' => array('label' => 'Teléfono', 'required' => false, 'type' => 'tel', 'placeholder' => '+34 600 000 000'),
        'date' => array('label' => 'Fecha del documento', 'required' => false, 'type' => 'date', 'placeholder' => ''),
        'reference' => array('label' => 'Referencia interna', 'required' => false, 'type' => 'text', 'placeholder' => 'Ej.: REF-001234'),
        'notes' => array('label' => 'Observaciones o cláusulas adicionales', 'required' => false, 'type' => 'textarea', 'placeholder' => 'Añade condiciones, contexto o notas internas relevantes.'),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_render_create_pdf_page_v2() {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $resource_id = sanitize_key($_GET['resource'] ?? '');
    $resource = captacion_app_resource_access_check($resource_id, true);
    if (is_wp_error($resource)) {
        if (!is_user_logged_in()) wp_safe_redirect(home_url('/#/inicio'));
        else wp_die(esc_html($resource->get_error_message()), 'Compra Captación', array('response' => $resource->get_error_data()['status'] ?? 403));
        exit;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    captacion_app_log_resource_event($resource, 'open_create_pdf');
    $user = wp_get_current_user();
    $endpoint = rest_url('captacion/v1/resources/generate');
    $nonce = wp_create_nonce('wp_rest');
    $field_meta = captacion_app_resource_field_labels();
    $schema = is_array($resource['editable_fields_schema'] ?? null) ? $resource['editable_fields_schema'] : array_keys($field_meta);
    $defaults = array(
        'professional_name' => $user->display_name,
        'email' => $user->user_email,
        'phone' => get_user_meta($user->ID, 'captacion_phone', true),
        'date' => current_time('Y-m-d'),
    );
    $download_template_url = captacion_app_resource_template_pdf_url($resource_id);
    ?><!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title><?php echo esc_html($resource['title']); ?> · Personalizar PDF</title>
      <style>
        :root{--navy:#10233c;--blue:#1b67d6;--blue-dark:#1554b3;--slate:#64748b;--line:#dbe5ef;--bg:#f5f8fc;--green:#0f9f6e;--amber:#b7791f}*{box-sizing:border-box}body{margin:0;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:radial-gradient(circle at top left,#eaf3ff 0,#f8fafc 36%,#eef3f8 100%);color:var(--navy)}a{color:inherit}.shell{max-width:1180px;margin:0 auto;padding:28px 18px 48px}.topbar{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:22px}.back{display:inline-flex;align-items:center;gap:8px;padding:10px 13px;border:1px solid var(--line);border-radius:14px;background:#fff;color:var(--slate);font-size:12px;font-weight:800;text-decoration:none}.badge{display:inline-flex;padding:7px 10px;border-radius:999px;background:#dbeafe;color:var(--blue);font-size:10px;font-weight:900;text-transform:uppercase;letter-spacing:.12em}.hero{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(320px,.95fr);gap:22px;align-items:stretch}.panel{background:rgba(255,255,255,.92);border:1px solid var(--line);border-radius:28px;box-shadow:0 18px 55px rgba(16,35,60,.08);overflow:hidden}.intro{padding:28px}.intro h1{font-size:clamp(28px,4vw,48px);line-height:1.02;margin:14px 0 14px;font-weight:950;letter-spacing:-.04em}.intro p{color:var(--slate);line-height:1.65;font-size:14px;max-width:690px}.steps{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:24px}.step{padding:12px;border:1px solid #e2e8f0;border-radius:16px;background:#f8fafc;font-size:11px;color:var(--slate);font-weight:800}.step strong{display:block;color:var(--navy);font-size:12px;margin-bottom:3px}.trust{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-top:18px}.trust span{border:1px solid #e2e8f0;background:#fff;border-radius:16px;padding:12px;font-size:11px;color:var(--slate);font-weight:750}.form-wrap{padding:22px}.form-head{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:16px}.form-head h2{margin:0;font-size:19px;letter-spacing:-.02em}.form-head p{margin:5px 0 0;color:var(--slate);font-size:12px;line-height:1.5}.grid{display:grid;grid-template-columns:repeat(2,1fr);gap:14px}label{display:block;font-size:12px;font-weight:900;color:#475569}.full{grid-column:1/-1}input,textarea{width:100%;margin-top:7px;padding:13px 14px;border:1px solid #cbd5e1;border-radius:15px;background:#fff;color:var(--navy);font:inherit;font-size:14px;outline:none}textarea{min-height:120px;resize:vertical}input:focus,textarea:focus{border-color:var(--blue);box-shadow:0 0 0 4px rgba(27,103,214,.12)}.req{color:var(--blue)}.actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:18px}.btn{border:0;border-radius:15px;padding:13px 16px;font-size:12px;font-weight:950;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;justify-content:center;gap:8px}.btn.primary{background:var(--blue);color:#fff;box-shadow:0 12px 24px rgba(27,103,214,.22)}.btn.primary:hover{background:var(--blue-dark)}.btn.dark{background:var(--navy);color:#fff}.btn.light{background:#fff;border:1px solid var(--line);color:var(--blue)}.btn:disabled{opacity:.55;cursor:not-allowed}.preview{padding:22px;background:#0b1d33;color:#fff;display:flex;flex-direction:column}.paper{background:#fff;color:var(--navy);border-radius:22px;padding:24px;box-shadow:0 18px 45px rgba(0,0,0,.22);min-height:480px}.paper .mini{font-size:10px;letter-spacing:.14em;color:var(--blue);font-weight:950;text-transform:uppercase}.paper h2{font-size:24px;line-height:1.08;margin:10px 0 16px}.paper-row{display:flex;justify-content:space-between;gap:12px;border-bottom:1px solid #e2e8f0;padding:9px 0;font-size:12px}.paper-row b{color:#334155}.paper-row span{text-align:right;color:#475569}.result{display:none;margin-top:16px;padding:16px;border-radius:20px;border:1px solid rgba(15,159,110,.25);background:#ecfdf5;color:#065f46}.result.show{display:block}.share-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:9px;margin-top:12px}.note{margin-top:12px;font-size:11px;line-height:1.55;color:#64748b}.error{display:none;margin-top:12px;padding:12px;border-radius:16px;background:#fef2f2;color:#991b1b;font-size:12px;font-weight:750}.error.show{display:block}@media(max-width:900px){.hero{grid-template-columns:1fr}.steps,.trust{grid-template-columns:1fr 1fr}.grid{grid-template-columns:1fr}.preview{order:-1}.paper{min-height:auto}}@media(max-width:560px){.shell{padding:18px 12px 36px}.intro,.form-wrap,.preview{padding:18px}.steps,.trust,.share-grid{grid-template-columns:1fr}.actions .btn{width:100%}}
      </style>
      <style>
        html[data-theme="light"]{--navy:#10233c;--blue:#1b67d6;--blue-dark:#1554b3;--slate:#52657a;--line:#d4deea;--bg:#f5f8fc;--surface:#ffffff;--soft:#f3f7fb;--paper:#ffffff;--paperText:#10233c}html[data-theme="dark"]{--navy:#eaf2ff;--blue:#79b7ff;--blue-dark:#5aa3f5;--slate:#b7c6d8;--line:#33465d;--bg:#091321;--surface:#111f31;--soft:#16263a;--paper:#f8fafc;--paperText:#10233c}html[data-theme="dark"] body{background:radial-gradient(circle at 18% -10%,rgba(27,103,214,.28),transparent 32%),linear-gradient(145deg,#091321,#0d1c30 58%,#10233c);color:var(--navy)}html[data-theme="dark"] .panel,html[data-theme="dark"] .back,html[data-theme="dark"] .trust span{background:rgba(17,31,49,.94);border-color:var(--line);box-shadow:0 18px 55px rgba(0,0,0,.22)}html[data-theme="dark"] .step{background:#16263a;border-color:#33465d;color:var(--slate)}html[data-theme="dark"] .step strong,html[data-theme="dark"] .form-head h2{color:#edf5ff}html[data-theme="dark"] input,html[data-theme="dark"] textarea{background:#0d1c30;border-color:#33465d;color:#edf5ff}html[data-theme="dark"] .btn.light{background:#16263a;border-color:#45607a;color:#b9dcff}html[data-theme="dark"] .result{background:#123d34;border-color:#1c7e62;color:#bdf4df}.intro h1{font-weight:780;letter-spacing:-.035em}.form-head h2,.paper h2{font-weight:760}.step,.trust span,label,.btn,.badge{font-weight:720}.btn{border-radius:999px;padding:12px 17px;transition:transform .16s ease,box-shadow .16s ease,background-color .16s ease}.btn:hover{transform:translateY(-1px)}.btn.primary{background:linear-gradient(135deg,#1b67d6,#2f7df0);box-shadow:0 10px 22px rgba(27,103,214,.20)}.btn.dark{background:linear-gradient(135deg,#10233c,#1c3554)}.preview-modal{position:fixed;inset:0;z-index:100;display:none;align-items:center;justify-content:center;padding:18px;background:rgba(5,13,25,.72);backdrop-filter:blur(10px)}.preview-modal.show{display:flex}.preview-dialog{width:min(1020px,100%);height:min(88vh,860px);border-radius:24px;background:var(--surface);border:1px solid var(--line);box-shadow:0 30px 80px rgba(0,0,0,.35);overflow:hidden;display:flex;flex-direction:column}.preview-dialog-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid var(--line)}.preview-dialog-head strong{font-size:14px;font-weight:760;color:var(--navy)}.preview-dialog iframe{width:100%;height:100%;border:0;background:#fff}.share-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.result strong{font-weight:760}.muted-action{color:var(--slate);background:transparent;border:1px solid var(--line)}@media(max-width:720px){.share-grid{grid-template-columns:1fr}.preview-dialog{height:86vh}}
      </style>
      <script>
        try { document.documentElement.dataset.theme = localStorage.getItem('captacion_theme_v1') || 'dark'; } catch (error) { document.documentElement.dataset.theme = 'dark'; }
      </script>
    </head>
    <body>
      <main class="shell">
        <div class="topbar"><a class="back" href="<?php echo esc_url(home_url('/#/recursos')); ?>">← Volver a recursos</a><?php if ($download_template_url) : ?><a class="back" href="<?php echo esc_url($download_template_url); ?>" target="_blank" rel="noopener noreferrer">Descargar plantilla original</a><?php endif; ?></div>
        <section class="hero">
          <div class="panel intro">
            <span class="badge">Documento guiado</span>
            <h1><?php echo esc_html($resource['title']); ?></h1>
            <p><?php echo esc_html($resource['description']); ?> Completa los campos necesarios y genera una versión personalizada descargable, lista para adjuntar o compartir con la contraparte.</p>
            <div class="steps"><div class="step"><strong>1. Datos</strong>Completa lo imprescindible.</div><div class="step"><strong>2. Revisión</strong>Comprueba la vista previa.</div><div class="step"><strong>3. PDF</strong>Genera el documento.</div><div class="step"><strong>4. Comparte</strong>Email, WhatsApp o mensaje.</div></div>
            <div class="trust"><span>Acceso protegido para usuarios verificados</span><span>Archivo guardado en zona privada</span><span>Revisión jurídica recomendada</span></div>
          </div>
          <aside class="panel preview" aria-label="Vista previa del documento">
            <div class="paper" id="pdf-preview">
              <span class="mini">Compra Captación · PDF personalizado</span>
              <h2><?php echo esc_html($resource['title']); ?></h2>
              <div id="preview-rows"></div>
              <p class="note">Documento generado con finalidad operativa y orientativa. Revisión jurídica recomendada antes de uso contractual definitivo.</p>
            </div>
          </aside>
        </section>
        <section class="panel form-wrap" style="margin-top:22px">
          <div class="form-head"><div><h2>Completa la plantilla</h2><p>Los campos marcados son necesarios para generar el PDF personalizado. Puedes descargar la plantilla original para revisarla antes.</p></div><span class="badge">PDF + personalización</span></div>
          <form id="pdf-form">
            <input type="hidden" name="resource_id" value="<?php echo esc_attr($resource_id); ?>">
            <div class="grid">
              <?php foreach ($schema as $field_key) : $meta = $field_meta[$field_key] ?? null; if (!$meta) continue; $value = $defaults[$field_key] ?? ''; ?>
                <label class="<?php echo $field_key === 'notes' ? 'full' : ''; ?>"><?php echo esc_html($meta['label']); ?> <?php if ($meta['required']) : ?><span class="req">*</span><?php endif; ?>
                  <?php if ($meta['type'] === 'textarea') : ?>
                    <textarea name="<?php echo esc_attr($field_key); ?>" placeholder="<?php echo esc_attr($meta['placeholder']); ?>"><?php echo esc_textarea($value); ?></textarea>
                  <?php else : ?>
                    <input name="<?php echo esc_attr($field_key); ?>" type="<?php echo esc_attr($meta['type']); ?>" <?php echo $meta['required'] ? 'required' : ''; ?> value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($meta['placeholder']); ?>">
                  <?php endif; ?>
                </label>
              <?php endforeach; ?>
            </div>
            <div class="actions"><button class="btn primary" id="generate-btn" type="submit">Generar PDF personalizado</button><?php if ($download_template_url) : ?><a class="btn light" href="<?php echo esc_url($download_template_url); ?>" target="_blank" rel="noopener noreferrer">Ver plantilla base</a><?php endif; ?></div>
            <div id="pdf-error" class="error"></div>
            <div id="pdf-result" class="result"><strong>PDF personalizado listo</strong><p class="note">Revisa la vista previa antes de descargar. Si ves algo que quieras ajustar, vuelve al formulario con “Modificar datos”.</p><div class="share-grid"><button id="preview-generated-pdf" class="btn primary" type="button">Vista previa del PDF</button><button id="edit-generated-pdf" class="btn muted-action" type="button">Modificar datos</button><a id="download-link" class="btn light" href="#">Descargar PDF</a><a id="email-link" class="btn light" href="#">Preparar email</a><a id="whatsapp-link" class="btn light" href="#" target="_blank" rel="noopener noreferrer">WhatsApp</a><button id="copy-share" class="btn dark" type="button">Copiar mensaje</button><button id="native-share" class="btn light" type="button" style="display:none">Compartir</button></div></div>
          </form>
        </section>
        <div id="generated-pdf-preview-modal" class="preview-modal" role="dialog" aria-modal="true" aria-labelledby="generated-pdf-preview-title">
          <div class="preview-dialog">
            <div class="preview-dialog-head"><strong id="generated-pdf-preview-title">Vista previa del PDF generado</strong><button type="button" class="btn muted-action" id="close-generated-pdf-preview">Cerrar</button></div>
            <iframe id="generated-pdf-preview-frame" title="Vista previa del PDF generado"></iframe>
          </div>
        </div>
      </main>
      <script>
        const endpoint = <?php echo wp_json_encode($endpoint); ?>;
        const nonce = <?php echo wp_json_encode($nonce); ?>;
        const resourceTitle = <?php echo wp_json_encode($resource['title']); ?>;
        const form = document.getElementById('pdf-form');
        const previewRows = document.getElementById('preview-rows');
        const result = document.getElementById('pdf-result');
        const errorBox = document.getElementById('pdf-error');
        const generateBtn = document.getElementById('generate-btn');
        let latestDownloadUrl = '';
        let latestPreviewUrl = '';
        function clean(value){return String(value || '').trim();}
        function fieldLabel(name){const labels = <?php echo wp_json_encode(array_map(static function($item){ return $item['label']; }, $field_meta)); ?>; return labels[name] || name;}
        function updatePreview(){const data = new FormData(form); const rows = []; for (const [key,value] of data.entries()) { if (key === 'resource_id' || !clean(value)) continue; rows.push(`<div class="paper-row"><b>${fieldLabel(key)}</b><span>${clean(value).replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]))}</span></div>`); } previewRows.innerHTML = rows.join('') || '<p class="note">Completa el formulario para ver la vista previa del documento.</p>';}
        function shareText(){return `He generado el documento "${resourceTitle}" desde Compra Captación. Te envío el PDF personalizado adjunto para revisión.`;}
        function configureShare(downloadUrl, previewUrl){latestDownloadUrl = downloadUrl; latestPreviewUrl = previewUrl || downloadUrl; document.getElementById('download-link').href = downloadUrl; document.getElementById('email-link').href = `mailto:?subject=${encodeURIComponent(resourceTitle)}&body=${encodeURIComponent(shareText() + '\n\nNota: adjunto el PDF descargado desde mi área privada.')}`; document.getElementById('whatsapp-link').href = `https://wa.me/?text=${encodeURIComponent(shareText() + ' Adjuntaré el PDF descargado para revisión.')}`; const native = document.getElementById('native-share'); if (navigator.share) native.style.display = 'inline-flex';}
        form.addEventListener('input', updatePreview); updatePreview();
        document.getElementById('copy-share').addEventListener('click', async () => { try { await navigator.clipboard.writeText(shareText()); document.getElementById('copy-share').textContent = 'Mensaje copiado'; setTimeout(() => document.getElementById('copy-share').textContent = 'Copiar mensaje', 1800); } catch(e) {} });
        document.getElementById('native-share').addEventListener('click', async () => { if (!navigator.share) return; try { await navigator.share({title:resourceTitle,text:shareText(),url:window.location.href}); } catch(e) {} });
        document.getElementById('preview-generated-pdf').addEventListener('click', () => { if (!latestPreviewUrl) return; document.getElementById('generated-pdf-preview-frame').src = latestPreviewUrl; document.getElementById('generated-pdf-preview-modal').classList.add('show'); });
        document.getElementById('close-generated-pdf-preview').addEventListener('click', () => { document.getElementById('generated-pdf-preview-modal').classList.remove('show'); document.getElementById('generated-pdf-preview-frame').src = 'about:blank'; });
        document.getElementById('generated-pdf-preview-modal').addEventListener('click', event => { if (event.target.id === 'generated-pdf-preview-modal') document.getElementById('close-generated-pdf-preview').click(); });
        document.getElementById('edit-generated-pdf').addEventListener('click', () => { result.classList.remove('show'); form.scrollIntoView({behavior:'smooth',block:'start'}); const first = form.querySelector('input:not([type="hidden"]), textarea'); if (first) first.focus(); });
        form.addEventListener('submit', async event => { event.preventDefault(); errorBox.classList.remove('show'); result.classList.remove('show'); generateBtn.disabled = true; generateBtn.textContent = 'Generando...'; try { const payload = Object.fromEntries(new FormData(form).entries()); const response = await fetch(endpoint, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json','X-WP-Nonce':nonce}, body:JSON.stringify(payload)}); const body = await response.json(); if (!response.ok || !body.ok) throw new Error(body.message || 'No se pudo generar el PDF.'); configureShare(body.downloadUrl, body.previewUrl); result.classList.add('show'); result.scrollIntoView({behavior:'smooth',block:'center'}); } catch(error) { errorBox.textContent = error.message || 'No se pudo generar el PDF.'; errorBox.classList.add('show'); } finally { generateBtn.disabled = false; generateBtn.textContent = 'Generar PDF personalizado'; } });
      </script>
    </body>
    </html><?php
    exit;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_download_generated_pdf() {
    global $wpdb;
    $file_id = absint($_GET['file_id'] ?? 0);
    check_admin_referer('captacion_generated_pdf_' . $file_id);
    $table = captacion_app_records_table_name();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d AND record_type='generated_pdf'", $file_id), ARRAY_A);
    if (!$row || (!current_user_can('manage_options') && absint($row['owner_user_id'] ?: $row['user_id']) !== get_current_user_id())) wp_die('Acceso denegado.', 403);
    $payload = json_decode($row['payload'], true);
    $path = $payload['path'] ?? '';
    if (!$path || !is_file($path)) wp_die('Archivo no disponible.', 404);
    $resource = captacion_app_resource_catalog()[$payload['resource_id']] ?? array('resource_id'=>'unknown','title'=>$row['title']);
    captacion_app_log_resource_event($resource, 'download_generated_pdf', array('generated_file_id'=>$file_id));
    nocache_headers();
    $disposition = !empty($_GET['preview']) ? 'inline' : 'attachment';
    header('Content-Type: application/pdf');
    header('Content-Disposition: ' . $disposition . '; filename="' . sanitize_file_name($row['title']) . '.pdf"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_access_log_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_marketplace_access_log';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_install_access_log_table() {
    global $wpdb;
    $table = captacion_app_access_log_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $sql = "CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT UNSIGNED NOT NULL,
        opportunity_id VARCHAR(190) NOT NULL,
        plan_type VARCHAR(40) NOT NULL,
        access_type VARCHAR(40) NOT NULL,
        amount_paid DECIMAL(10,2) DEFAULT 0 NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY user_opportunity (user_id, opportunity_id),
        KEY user_id (user_id),
        KEY plan_type (plan_type),
        KEY created_at (created_at)
    ) {$charset_collate};";
    dbDelta($sql);
    update_option('captacion_access_log_table_version', '20260620');
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_maybe_install_access_log_table() {
    if (get_option('captacion_access_log_table_version') !== '20260620') captacion_app_install_access_log_table();
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_plan_config($plan_type) {
    $plans = array(
        'basic' => array('included' => 3, 'extra_pack' => 0, 'extra_price' => 10, 'checkout_key' => 'stripe_marketplace_single_link'),
        'professional_plus' => array('included' => 20, 'extra_pack' => 10, 'extra_price' => 5, 'checkout_key' => 'stripe_marketplace_plus_pack_link'),
        'premium' => array('included' => 30, 'extra_pack' => 15, 'extra_price' => 5, 'checkout_key' => 'stripe_marketplace_premium_pack_link'),
    );
    return $plans[$plan_type] ?? $plans['basic'];
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_normalize_user_plan($plan_type) {
    $plan_type = sanitize_key((string) $plan_type);
    $legacy = array('initial' => 'basic', 'professional' => 'professional_plus', 'agency' => 'premium');
    $plan_type = $legacy[$plan_type] ?? $plan_type;
    return in_array($plan_type, array('basic', 'professional_plus', 'premium'), true) ? $plan_type : 'basic';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_is_saas_admin($user_id = 0) {
    $user_id = absint($user_id ?: get_current_user_id());
    if (!$user_id) return false;
    $user = get_userdata($user_id);
    if (!$user || empty($user->user_email)) return false;
    $admin_email = strtolower(sanitize_email(captacion_app_setting('saas_admin_email')));
    return $admin_email && strtolower((string) $user->user_email) === $admin_email;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ensure_user_access_meta($user_id) {
    $user_id = absint($user_id);
    if (captacion_app_is_saas_admin($user_id)) {
        update_user_meta($user_id, 'captacion_plan_type', 'premium');
        update_user_meta($user_id, 'captacion_subscription_status', 'active');
        update_user_meta($user_id, 'captacion_included_marketplace_accesses', 999999);
        update_user_meta($user_id, 'captacion_extra_marketplace_accesses', 0);
        update_user_meta($user_id, 'captacion_last_reset_at', current_time('mysql'));
        return;
    }
    $plan_type = captacion_app_normalize_user_plan(get_user_meta($user_id, 'captacion_plan_type', true));
    $config = captacion_app_plan_config($plan_type);
    $last_reset = sanitize_text_field((string) get_user_meta($user_id, 'captacion_last_reset_at', true));
    $current_month = current_time('Y-m');
    if (!$last_reset || substr($last_reset, 0, 7) !== $current_month) {
        update_user_meta($user_id, 'captacion_used_marketplace_accesses', 0);
        update_user_meta($user_id, 'captacion_last_reset_at', current_time('mysql'));
    }
    update_user_meta($user_id, 'captacion_plan_type', $plan_type);
    update_user_meta($user_id, 'captacion_included_marketplace_accesses', $config['included']);
    if (get_user_meta($user_id, 'captacion_extra_marketplace_accesses', true) === '') update_user_meta($user_id, 'captacion_extra_marketplace_accesses', 0);
    if (get_user_meta($user_id, 'captacion_credits_purchased', true) === '') update_user_meta($user_id, 'captacion_credits_purchased', 0);
    if (get_user_meta($user_id, 'captacion_subscription_status', true) === '') update_user_meta($user_id, 'captacion_subscription_status', $plan_type === 'basic' ? 'active' : 'pending');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_get_user_access_state($user_id) {
    $user_id = absint($user_id);
    if (!$user_id) return array('plan_type'=>'basic','included_marketplace_accesses'=>0,'used_marketplace_accesses'=>0,'extra_marketplace_accesses'=>0,'remaining_marketplace_accesses'=>0,'credits_purchased'=>0,'last_reset_at'=>'','subscription_status'=>'guest');
    captacion_app_ensure_user_access_meta($user_id);
    if (captacion_app_is_saas_admin($user_id)) {
        return array(
            'plan_type' => 'premium',
            'included_marketplace_accesses' => 999999,
            'used_marketplace_accesses' => 0,
            'extra_marketplace_accesses' => 0,
            'remaining_marketplace_accesses' => 999999,
            'monthly_consumed_accesses' => 0,
            'monthly_total_accesses' => 999999,
            'usage_percentage' => 0,
            'credits_purchased' => 0,
            'last_reset_at' => sanitize_text_field((string) get_user_meta($user_id, 'captacion_last_reset_at', true)),
            'subscription_status' => 'active',
            'is_saas_admin' => true,
        );
    }
    $plan_type = captacion_app_normalize_user_plan(get_user_meta($user_id, 'captacion_plan_type', true));
    $included = absint(get_user_meta($user_id, 'captacion_included_marketplace_accesses', true));
    $used = absint(get_user_meta($user_id, 'captacion_used_marketplace_accesses', true));
    $extra = absint(get_user_meta($user_id, 'captacion_extra_marketplace_accesses', true));
    global $wpdb;
    $month_start = current_time('Y-m-01 00:00:00');
    $consumed = absint($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . captacion_app_access_log_table_name() . " WHERE user_id = %d AND created_at >= %s", $user_id, $month_start)));
    $remaining = max(0, $included - $used) + $extra;
    $capacity = $remaining + $consumed;
    return array(
        'plan_type' => $plan_type,
        'included_marketplace_accesses' => $included,
        'used_marketplace_accesses' => $used,
        'extra_marketplace_accesses' => $extra,
        'remaining_marketplace_accesses' => $remaining,
        'monthly_consumed_accesses' => $consumed,
        'monthly_total_accesses' => $capacity,
        'usage_percentage' => $capacity ? min(100, round(($consumed / $capacity) * 100)) : 0,
        'credits_purchased' => absint(get_user_meta($user_id, 'captacion_credits_purchased', true)),
        'last_reset_at' => sanitize_text_field((string) get_user_meta($user_id, 'captacion_last_reset_at', true)),
        'subscription_status' => sanitize_key((string) get_user_meta($user_id, 'captacion_subscription_status', true)),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_get_access_history($user_id, $limit = 50) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT opportunity_id, access_type, created_at FROM " . captacion_app_access_log_table_name() . " WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
        absint($user_id), min(100, max(1, absint($limit)))
    ), ARRAY_A);
    $remaining = captacion_app_get_user_access_state($user_id)['remaining_marketplace_accesses'];
    foreach ($rows as $index => &$row) $row['balance_remaining'] = $remaining + $index;
    return $rows;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_grant_marketplace_accesses($user_id, $plan_type, $quantity, $payment_reference = '') {
    // Webhook integration point: call only after the payment provider confirms the charge server-side.
    $user_id = absint($user_id);
    $plan_type = captacion_app_normalize_user_plan($plan_type);
    $quantity = absint($quantity);
    if (!$user_id || !$quantity) return false;
    if ($payment_reference && get_user_meta($user_id, 'captacion_credit_payment_' . sanitize_key($payment_reference), true)) return false;
    $current = absint(get_user_meta($user_id, 'captacion_extra_marketplace_accesses', true));
    $purchased = absint(get_user_meta($user_id, 'captacion_credits_purchased', true));
    update_user_meta($user_id, 'captacion_extra_marketplace_accesses', $current + $quantity);
    update_user_meta($user_id, 'captacion_credits_purchased', $purchased + $quantity);
    if ($payment_reference) update_user_meta($user_id, 'captacion_credit_payment_' . sanitize_key($payment_reference), current_time('mysql'));
    return true;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_set_user_plan_from_webhook($user_id, $plan_type, $subscription_status, $payment_reference = '') {
    // Webhook integration point. Never call from browser-controlled input.
    $user_id = absint($user_id);
    $plan_type = captacion_app_normalize_user_plan($plan_type);
    $subscription_status = sanitize_key((string) $subscription_status);
    if (!$user_id || !in_array($subscription_status, array('active','trialing','past_due','canceled','pending'), true)) return false;
    if ($payment_reference && get_user_meta($user_id, 'captacion_plan_payment_' . sanitize_key($payment_reference), true)) return false;
    $config = captacion_app_plan_config($plan_type);
    update_user_meta($user_id, 'captacion_plan_type', $plan_type);
    update_user_meta($user_id, 'captacion_subscription_status', $subscription_status);
    update_user_meta($user_id, 'captacion_included_marketplace_accesses', $config['included']);
    update_user_meta($user_id, 'captacion_used_marketplace_accesses', 0);
    update_user_meta($user_id, 'captacion_last_reset_at', current_time('mysql'));
    if ($payment_reference) update_user_meta($user_id, 'captacion_plan_payment_' . sanitize_key($payment_reference), current_time('mysql'));
    return true;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_confirm_single_marketplace_access($user_id, $opportunity_id, $amount_paid = 10, $payment_reference = '') {
    // Webhook integration point for the Basic single-access checkout.
    global $wpdb;
    $user_id = absint($user_id);
    $opportunity_id = sanitize_text_field((string) $opportunity_id);
    if (!$user_id || !$opportunity_id || captacion_app_user_has_opportunity_access($user_id, $opportunity_id)) return false;
    if ($payment_reference && get_user_meta($user_id, 'captacion_single_payment_' . sanitize_key($payment_reference), true)) return false;
    captacion_app_maybe_install_access_log_table();
    $inserted = $wpdb->insert(captacion_app_access_log_table_name(), array('user_id'=>$user_id,'opportunity_id'=>$opportunity_id,'plan_type'=>'basic','access_type'=>'single_purchase','amount_paid'=>(float)$amount_paid,'created_at'=>current_time('mysql')), array('%d','%s','%s','%s','%f','%s'));
    if ($inserted && $payment_reference) update_user_meta($user_id, 'captacion_single_payment_' . sanitize_key($payment_reference), current_time('mysql'));
    if ($inserted) captacion_app_mark_access_requests_for_opportunity($opportunity_id, 'approved');
    return (bool) $inserted;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_mark_access_requests_for_opportunity($opportunity_id, $status) {
    global $wpdb;
    $opportunity_id = sanitize_text_field((string) $opportunity_id);
    $status = sanitize_text_field((string) $status);
    if (!$opportunity_id || !$status) return 0;
    $records_table = captacion_app_records_table_name();
    $updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$records_table} SET status = %s, updated_at = %s WHERE record_type = 'access_request' AND related_id = %s AND deleted_at IS NULL",
        $status,
        current_time('mysql'),
        $opportunity_id
    ));
    captacion_app_complete_pending_feed_deletions();
    return absint($updated);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_public_nonce_permission(WP_REST_Request $request) {
    $nonce = $request->get_header('X-WP-Nonce');
    if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
        return true;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $site_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
    $origin = get_http_origin();
    $referer = wp_get_referer();
    $origin_host = $origin ? wp_parse_url($origin, PHP_URL_HOST) : '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($site_host && ($origin_host === $site_host || $referer_host === $site_host)) {
        return true;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return new WP_Error('captacion_invalid_nonce', 'La sesion del formulario ha caducado. Recarga la pagina.', array('status'=>403));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_private_permission(WP_REST_Request $request) {
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        $site_host = wp_parse_url(home_url('/'), PHP_URL_HOST);
        $origin = get_http_origin();
        $referer = wp_get_referer();
        $origin_host = $origin ? wp_parse_url($origin, PHP_URL_HOST) : '';
        $referer_host = $referer ? wp_parse_url($referer, PHP_URL_HOST) : '';
        $same_origin = $site_host && ($origin_host === $site_host || $referer_host === $site_host);
        if ($same_origin && is_user_logged_in()) {
            if (!current_user_can('read')) return new WP_Error('captacion_permission_required', 'Tu cuenta no tiene permisos para esta accion.', array('status'=>403));
            if (!captacion_app_is_email_verified(get_current_user_id())) return new WP_Error('captacion_email_unverified', 'Confirma tu correo electronico para acceder.', array('status'=>403));
            return true;
        }
        return new WP_Error('captacion_invalid_nonce', 'La sesion del formulario ha caducado. Recarga la pagina.', array('status'=>403));
    }
    if (!is_user_logged_in()) return new WP_Error('captacion_auth_required', 'Debes iniciar sesion.', array('status'=>401));
    if (!current_user_can('read')) return new WP_Error('captacion_permission_required', 'Tu cuenta no tiene permisos para esta accion.', array('status'=>403));
    if (!captacion_app_is_email_verified(get_current_user_id())) return new WP_Error('captacion_email_unverified', 'Confirma tu correo electronico para acceder.', array('status'=>403));
    return true;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ajax_rest_nonce() {
    if (!is_user_logged_in()) {
        wp_send_json_error(array('message' => 'Debes iniciar sesion.'), 401);
    }
    wp_send_json_success(array('nonce' => wp_create_nonce('wp_rest')));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_rate_limit($scope, $limit = 10, $ttl = 600) {
    $remote_address = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = 'captacion_rate_' . sanitize_key($scope) . '_' . substr(hash('sha256', $remote_address), 0, 24);
    $count = (int) get_transient($key);
    if ($count >= $limit) return false;
    set_transient($key, $count + 1, $ttl);
    return true;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_is_email_verified($user_id) {
    $value = get_user_meta(absint($user_id), 'captacion_email_verified', true);
    return $value === '' || $value === '1';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_send_verification_email($user_id) {
    $user = get_userdata(absint($user_id));
    if (!$user) return new WP_Error('captacion_verify_user', 'No se encontro la cuenta.', array('status'=>404));
    $token = bin2hex(random_bytes(32));
    update_user_meta($user->ID, 'captacion_email_verification_hash', hash('sha256', $token));
    update_user_meta($user->ID, 'captacion_email_verification_expires', time() + DAY_IN_SECONDS);
    $url = add_query_arg(array('captacion_verify_email'=>'1','uid'=>$user->ID,'token'=>$token), home_url('/'));
    $subject = 'Confirma tu registro en Compra Captación';
    $body = "Hola {$user->display_name},\n\nConfirma tu registro durante las proximas 24 horas:\n{$url}\n\nSi no solicitaste esta cuenta, ignora este mensaje.";
    return wp_mail($user->user_email, $subject, $body, array('Content-Type: text/plain; charset=UTF-8'))
        ? true
        : new WP_Error('captacion_verify_mail', 'No se pudo enviar el correo de confirmacion.', array('status'=>500));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_handle_email_verification() {
    if (empty($_GET['captacion_verify_email'])) return;
    $user_id = absint($_GET['uid'] ?? 0);
    $token = sanitize_text_field(wp_unslash($_GET['token'] ?? ''));
    $stored_hash = (string) get_user_meta($user_id, 'captacion_email_verification_hash', true);
    $expires = absint(get_user_meta($user_id, 'captacion_email_verification_expires', true));
    $ok = $user_id && strlen($token) === 64 && $expires >= time() && $stored_hash && hash_equals($stored_hash, hash('sha256', $token));
    if ($ok) {
        update_user_meta($user_id, 'captacion_email_verified', '1');
        update_user_meta($user_id, 'captacion_subscription_status', 'active');
        delete_user_meta($user_id, 'captacion_email_verification_hash');
        delete_user_meta($user_id, 'captacion_email_verification_expires');
    }
    wp_safe_redirect(add_query_arg('email_verification', $ok ? 'success' : 'invalid', home_url('/')) . '#/inicio');
    exit;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_register_professional(WP_REST_Request $request) {
    if (!captacion_app_rest_rate_limit('register', 6, 15 * MINUTE_IN_SECONDS)) {
        return new WP_Error('captacion_register_rate_limit', 'Demasiados intentos de registro. Espera unos minutos.', array('status'=>429));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $name = sanitize_text_field((string) $request->get_param('name'));
    $email = sanitize_email((string) $request->get_param('email'));
    $phone = preg_replace('/[^0-9+]/', '', (string) $request->get_param('phone'));
    $password = (string) $request->get_param('password');
    $privacy = filter_var($request->get_param('privacyAccepted'), FILTER_VALIDATE_BOOLEAN);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!is_email($email)) return new WP_Error('captacion_register_email', 'Introduce un correo electronico valido.', array('status'=>422));
    if (email_exists($email)) return new WP_Error('captacion_register_exists', 'Ya existe una cuenta con este correo.', array('status'=>409));
    if (strlen($name) < 3) $name = sanitize_text_field(strstr($email, '@', true) ?: 'Profesional');
    if ($phone !== '' && !preg_match('/^\+[1-9][0-9]{7,14}$/', $phone)) return new WP_Error('captacion_register_phone', 'Introduce un numero de contacto en formato internacional.', array('status'=>422));
    if (strlen($password) < 8) return new WP_Error('captacion_register_password', 'La contrasena debe tener al menos 8 caracteres.', array('status'=>422));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $base_username = sanitize_user(strstr($email, '@', true), true) ?: 'profesional';
    $username = $base_username;
    $suffix = 1;
    while (username_exists($username)) $username = $base_username . $suffix++;
    $role = get_role('captacion_agent') ? 'captacion_agent' : 'subscriber';
    $user_id = wp_insert_user(array(
        'user_login'=>$username,
        'user_email'=>$email,
        'display_name'=>$name,
        'user_pass'=>$password,
        'role'=>$role,
    ));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    update_user_meta($user_id, 'captacion_phone', $phone);
    update_user_meta($user_id, 'captacion_profile_complete', '0');
    update_user_meta($user_id, 'captacion_privacy_accepted_at', current_time('mysql'));
    update_user_meta($user_id, 'captacion_privacy_version', '2026-06-20');
    update_user_meta($user_id, 'captacion_commercial_consent', $commercial_consent ? '1' : '0');
    if ($commercial_consent) update_user_meta($user_id, 'captacion_commercial_consent_at', current_time('mysql'));
    update_user_meta($user_id, 'captacion_plan_type', 'basic');
    update_user_meta($user_id, 'captacion_included_marketplace_accesses', 0);
    update_user_meta($user_id, 'captacion_used_marketplace_accesses', 0);
    update_user_meta($user_id, 'captacion_extra_marketplace_accesses', 0);
    update_user_meta($user_id, 'captacion_credits_purchased', 0);
    update_user_meta($user_id, 'captacion_last_reset_at', current_time('mysql'));
    update_user_meta($user_id, 'captacion_subscription_status', 'pending_verification');
    update_user_meta($user_id, 'captacion_email_verified', '0');
    $mail_result = captacion_app_send_verification_email($user_id);
    if (is_wp_error($mail_result)) {
        require_once ABSPATH . 'wp-admin/includes/user.php';
        wp_delete_user($user_id);
        return $mail_result;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    captacion_app_log_mail_event(array(
        'category'=>'registro','source'=>'registro-profesional','email'=>$email,'name'=>$name,'phone'=>$phone,
        'message'=>'Alta profesional con perfil pendiente de completar.','tags'=>array('registro-inicio'),
        'payload'=>array('user_id'=>$user_id,'profile_complete'=>false),
    ));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'ok'=>true,
        'userId'=>(int) $user_id,
        'displayName'=>$name,
        'email'=>$email,
        'phone'=>$phone,
        'profileComplete'=>false,
        'emailVerified'=>false,
        'message'=>'Te hemos enviado un correo electronico para confirmar tu registro. Revisa tu bandeja de entrada y valida tu cuenta para acceder.',
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_professional_role() {
    if (!get_role('captacion_agent')) add_role('captacion_agent', 'Profesional Compra Captación', array('read' => true, 'upload_files' => true));
}
add_action('after_switch_theme', 'captacion_app_register_professional_role');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_login(WP_REST_Request $request) {
    if (!captacion_app_rest_rate_limit('login', 10, 15 * MINUTE_IN_SECONDS)) return new WP_Error('captacion_login_rate_limit', 'Demasiados intentos. Espera unos minutos.', array('status'=>429));
    $email = sanitize_email((string) $request->get_param('email'));
    $password = (string) $request->get_param('password');
    if (!is_email($email) || !$password) return new WP_Error('captacion_login_fields', 'Completa correo y contrasena.', array('status'=>422));
    $user = get_user_by('email', $email);
    if (!$user) return new WP_Error('captacion_login_email_not_found', 'No existe una cuenta con este correo.', array('status'=>404));
    if (!captacion_app_is_email_verified($user->ID)) {
        return new WP_Error('captacion_email_unverified', 'Debes confirmar tu correo electronico antes de acceder.', array('status'=>403, 'email'=>$email, 'canResend'=>true));
    }
    $signed_in = wp_signon(array('user_login'=>$user->user_login, 'user_password'=>$password, 'remember'=>true), is_ssl());
    if (is_wp_error($signed_in)) return new WP_Error('captacion_login_password', 'La contrasena es incorrecta.', array('status'=>401));
    wp_set_current_user($signed_in->ID);
    return rest_ensure_response(array(
        'ok'=>true,
        'userId'=>(int) $signed_in->ID,
        'displayName'=>$signed_in->display_name,
        'email'=>$signed_in->user_email,
        'phone'=>sanitize_text_field((string) get_user_meta($signed_in->ID, 'captacion_phone', true)),
        'profileComplete'=>get_user_meta($signed_in->ID, 'captacion_profile_complete', true) === '1',
        'accessState'=>captacion_app_get_user_access_state($signed_in->ID),
        'emailVerified'=>true,
        'nonce'=>wp_create_nonce('wp_rest'),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_resend_verification(WP_REST_Request $request) {
    if (!captacion_app_rest_rate_limit('resend_verification', 3, HOUR_IN_SECONDS)) {
        return new WP_Error('captacion_verify_rate_limit', 'Has solicitado demasiados reenvios. Intentalo mas tarde.', array('status'=>429));
    }
    $email = sanitize_email((string) $request->get_param('email'));
    $user = get_user_by('email', $email);
    if (!$user || captacion_app_is_email_verified($user->ID)) {
        return rest_ensure_response(array('ok'=>true,'message'=>'Si la cuenta esta pendiente, recibiras un nuevo correo de verificacion.'));
    }
    $result = captacion_app_send_verification_email($user->ID);
    if (is_wp_error($result)) return $result;
    return rest_ensure_response(array('ok'=>true,'message'=>'Correo de verificacion reenviado.'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_logout() {
    wp_logout();
    return rest_ensure_response(array('ok'=>true));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_access_status(WP_REST_Request $request) {
    $opportunity_id = sanitize_text_field((string) $request->get_param('opportunity_id'));
    return rest_ensure_response(array(
        'ok'=>true,
        'accessState'=>captacion_app_get_user_access_state(get_current_user_id()),
        'accessHistory'=>captacion_app_get_access_history(get_current_user_id()),
        'opportunityUnlocked'=>$opportunity_id ? captacion_app_user_has_opportunity_access(get_current_user_id(), $opportunity_id) : false,
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_submit_report(WP_REST_Request $request) {
    $user = wp_get_current_user();
    $data = (array) $request->get_json_params();
    if (!empty($data['website'])) return new WP_Error('captacion_report_spam', 'No se pudo enviar el reporte.', array('status'=>422));
    $name = sanitize_text_field($data['name'] ?? $user->display_name);
    $email = sanitize_email($data['email'] ?? $user->user_email);
    $phone = sanitize_text_field($data['phone'] ?? get_user_meta($user->ID, 'captacion_phone', true));
    $comment = sanitize_textarea_field($data['comment'] ?? '');
    $url = esc_url_raw($data['url'] ?? '');
    if (!$name || !is_email($email) || strlen($comment) < 10) return new WP_Error('captacion_report_fields', 'Completa nombre, correo y comentario.', array('status'=>422));
    $key = 'REPORT-' . strtoupper(wp_generate_password(10, false, false));
    $record_id = captacion_app_upsert_record(array('record_type'=>'report','record_key'=>$key,'user_id'=>$user->ID,'user_email'=>$email,'title'=>'Reporte de contenido','status'=>'pendiente_revision','related_id'=>$url,'payload'=>array('name'=>$name,'email'=>$email,'phone'=>$phone,'comment'=>$comment,'url'=>$url)));
    captacion_app_notify_internal_mail_event(array('category'=>'reporte_denuncia','source'=>'canal-denuncias','email'=>$email,'name'=>$name,'phone'=>$phone,'reference'=>$key,'message'=>$comment,'payload'=>array('url'=>$url)));
    wp_mail($email, 'Hemos recibido tu reporte', "Hola {$name},\n\nTu reporte {$key} está en trámite y será revisado. Recibirás una respuesta en breve.\n\nCompra Captación");
    return rest_ensure_response(array('ok'=>true,'id'=>$record_id,'reference'=>$key,'message'=>'Reporte enviado correctamente. Recibirás una confirmación por correo.'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_user_has_opportunity_access($user_id, $opportunity_id) {
    global $wpdb;
    $table = captacion_app_access_log_table_name();
    return (bool) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE user_id = %d AND opportunity_id = %s", absint($user_id), sanitize_text_field($opportunity_id)));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_consume_access(WP_REST_Request $request) {
    global $wpdb;
    $user_id = get_current_user_id();
    $opportunity_id = sanitize_text_field((string) $request->get_param('opportunity_id'));
    if (!$opportunity_id || strlen($opportunity_id) > 190) return new WP_Error('captacion_access_opportunity', 'Oportunidad no valida.', array('status'=>422));
    captacion_app_maybe_install_access_log_table();
    if (captacion_app_user_has_opportunity_access($user_id, $opportunity_id)) return rest_ensure_response(array('ok'=>true, 'already_unlocked'=>true, 'accessState'=>captacion_app_get_user_access_state($user_id)));
    $state = captacion_app_get_user_access_state($user_id);
    if (!empty($state['is_saas_admin'])) {
        $wpdb->insert(captacion_app_access_log_table_name(), array(
            'user_id'=>$user_id,
            'opportunity_id'=>$opportunity_id,
            'plan_type'=>'premium',
            'access_type'=>'saas_admin',
            'amount_paid'=>0,
            'created_at'=>current_time('mysql'),
        ), array('%d','%s','%s','%s','%f','%s'));
        captacion_app_mark_access_requests_for_opportunity($opportunity_id, 'approved');
        return rest_ensure_response(array('ok'=>true, 'already_unlocked'=>false, 'access_type'=>'saas_admin', 'accessState'=>captacion_app_get_user_access_state($user_id)));
    }
    if ($state['remaining_marketplace_accesses'] < 1) return new WP_Error('captacion_access_balance', 'No tienes accesos disponibles. Elige la opcion de compra correspondiente a tu plan.', array('status'=>402, 'accessState'=>$state));
    $included_remaining = max(0, $state['included_marketplace_accesses'] - $state['used_marketplace_accesses']);
    $access_type = $included_remaining > 0 ? 'included' : 'extra';
    if ($access_type === 'included') update_user_meta($user_id, 'captacion_used_marketplace_accesses', $state['used_marketplace_accesses'] + 1);
    else update_user_meta($user_id, 'captacion_extra_marketplace_accesses', max(0, $state['extra_marketplace_accesses'] - 1));
    $wpdb->insert(captacion_app_access_log_table_name(), array(
        'user_id'=>$user_id,
        'opportunity_id'=>$opportunity_id,
        'plan_type'=>$state['plan_type'],
        'access_type'=>$access_type,
        'amount_paid'=>0,
        'created_at'=>current_time('mysql'),
    ), array('%d','%s','%s','%s','%f','%s'));
    captacion_app_mark_access_requests_for_opportunity($opportunity_id, 'approved');
    return rest_ensure_response(array('ok'=>true, 'already_unlocked'=>false, 'access_type'=>$access_type, 'accessState'=>captacion_app_get_user_access_state($user_id)));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_purchase_intent(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $state = captacion_app_get_user_access_state($user_id);
    $config = captacion_app_plan_config($state['plan_type']);
    $opportunity_id = sanitize_text_field((string) $request->get_param('opportunity_id'));
    $checkout_url = esc_url_raw(captacion_app_setting($config['checkout_key']));
    $intent = array(
        'id'=>'purchase-intent-' . $user_id . '-' . time(),
        'opportunity_id'=>$opportunity_id,
        'plan_type'=>$state['plan_type'],
        'quantity'=>$state['plan_type'] === 'basic' ? 1 : $config['extra_pack'],
        'amount'=>$config['extra_price'],
        'status'=>'pending_checkout',
        'created_at'=>current_time('mysql'),
    );
    captacion_app_upsert_record(array('record_type'=>'access_request','record_key'=>$intent['id'],'user_id'=>$user_id,'user_email'=>wp_get_current_user()->user_email,'title'=>'Intencion de compra de accesos','status'=>'pending_checkout','related_id'=>$opportunity_id,'payload'=>$intent));
    return rest_ensure_response(array(
        'ok'=>true,
        'checkoutConfigured'=>(bool) $checkout_url,
        'checkoutUrl'=>$checkout_url,
        'message'=>$checkout_url ? 'Continua al checkout. Los accesos se concederan solo tras confirmacion del webhook.' : 'Checkout no disponible temporalmente. Revisa la configuracion de pago antes de activar nuevas compras.',
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_save_task(WP_REST_Request $request) {
    $title = sanitize_text_field((string) $request->get_param('title'));
    $date = sanitize_text_field((string) $request->get_param('date'));
    $time = sanitize_text_field((string) $request->get_param('time'));
    $description = sanitize_textarea_field((string) $request->get_param('description'));
    $related_id = sanitize_text_field((string) $request->get_param('related_id'));
    $reminder = sanitize_key((string) $request->get_param('reminder'));
    $channel = sanitize_key((string) $request->get_param('channel'));
    if (strlen($title) < 3) return new WP_Error('captacion_task_title', 'Indica el titulo de la tarea.', array('status'=>422));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return new WP_Error('captacion_task_date', 'Indica una fecha valida.', array('status'=>422));
    if ($time && !preg_match('/^\d{2}:\d{2}$/', $time)) return new WP_Error('captacion_task_time', 'Indica una hora valida.', array('status'=>422));
    if (!in_array($reminder, array('none','15m','1h','1d'), true)) return new WP_Error('captacion_task_reminder', 'Recordatorio no valido.', array('status'=>422));
    if (!in_array($channel, array('panel','email','whatsapp_todo'), true)) return new WP_Error('captacion_task_channel', 'Canal no valido.', array('status'=>422));
    $state = captacion_app_get_user_access_state(get_current_user_id());
    if ($state['plan_type'] !== 'premium') return new WP_Error('captacion_task_plan', 'El calendario avanzado esta disponible en Premium.', array('status'=>403));
    $timestamp = strtotime($date . ' ' . ($time ?: '09:00'));
    if (!$timestamp) return new WP_Error('captacion_task_date', 'No se pudo interpretar la fecha.', array('status'=>422));
    $task = array('id'=>'TASK-' . get_current_user_id() . '-' . time(),'title'=>$title,'description'=>$description,'date'=>$date,'time'=>$time,'dueAt'=>$timestamp * 1000,'related_id'=>$related_id,'reminder'=>$reminder,'channel'=>$channel,'notification_status'=>'scheduled_structure_only','status'=>'pending','created_at'=>current_time('mysql'));
    $result = captacion_app_upsert_record(array('record_type'=>'task','record_key'=>$task['id'],'user_id'=>get_current_user_id(),'user_email'=>wp_get_current_user()->user_email,'title'=>$title,'status'=>'pending','related_id'=>$related_id,'payload'=>$task));
    if (is_wp_error($result)) return $result;
    return rest_ensure_response(array('ok'=>true,'task'=>$task,'message'=>'Tarea guardada. Las notificaciones externas quedan pendientes de infraestructura real.'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_list_tasks() {
    global $wpdb;
    captacion_app_maybe_install_records_table();
    $table = captacion_app_records_table_name();
    $rows = $wpdb->get_results($wpdb->prepare("SELECT record_key, title, status, related_id, payload, created_at, updated_at FROM {$table} WHERE record_type = 'task' AND owner_user_id = %d AND deleted_at IS NULL ORDER BY updated_at DESC LIMIT 200", get_current_user_id()), ARRAY_A);
    foreach ($rows as &$row) $row['payload'] = json_decode($row['payload'] ?: '{}', true);
    return rest_ensure_response(array('ok'=>true,'tasks'=>$rows));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_contact(WP_REST_Request $request) {
    if (!captacion_app_rest_rate_limit('contact', 8, 15 * MINUTE_IN_SECONDS)) {
        return new WP_Error('captacion_contact_rate_limit', 'Demasiados mensajes. Espera unos minutos.', array('status'=>429));
    }
    $name = sanitize_text_field((string) $request->get_param('name'));
    $email = sanitize_email((string) $request->get_param('email'));
    $phone = sanitize_text_field((string) $request->get_param('phone'));
    $preference = sanitize_key((string) $request->get_param('preference')) ?: 'email';
    $message = sanitize_textarea_field((string) $request->get_param('message'));
    $privacy = filter_var($request->get_param('privacyAccepted'), FILTER_VALIDATE_BOOLEAN);
    if (!in_array($preference, array('call','whatsapp','email'), true)) $preference = 'email';
    if (strlen($name) < 3) return new WP_Error('captacion_contact_name', 'Indica nombre y apellidos.', array('status'=>422));
    if (!is_email($email)) return new WP_Error('captacion_contact_email', 'Introduce un correo valido.', array('status'=>422));
    if (strlen($message) < 5) return new WP_Error('captacion_contact_message', 'Escribe un mensaje.', array('status'=>422));
    if (!$privacy) return new WP_Error('captacion_contact_privacy', 'Debes aceptar la politica de privacidad.', array('status'=>422));
    if (in_array($preference, array('call','whatsapp'), true) && !preg_match('/^\+?[0-9][0-9\s().-]{7,19}$/', $phone)) {
        return new WP_Error('captacion_contact_phone', 'El telefono es obligatorio para llamada o WhatsApp.', array('status'=>422));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $settings = captacion_app_settings();
    $recipient = sanitize_email($settings['contact_email'] ?? get_option('admin_email')) ?: get_option('admin_email');
    $body = "Nombre: {$name}\nEmail: {$email}\nTelefono: {$phone}\nPreferencia: {$preference}\n\nMensaje:\n{$message}";
    $sent = wp_mail($recipient, 'Nuevo mensaje de contacto en Compra Captación', $body, array('Content-Type: text/plain; charset=UTF-8','Reply-To: ' . $name . ' <' . $email . '>'));
    captacion_app_log_mail_event(array('category'=>'contacto','source'=>'contacto','email'=>$email,'name'=>$name,'phone'=>$phone,'message'=>$message,'tags'=>array('contacto'),'payload'=>array('preference'=>$preference,'sent'=>(bool)$sent)));
    return rest_ensure_response(array('ok'=>(bool)$sent,'message'=>$sent ? 'Mensaje enviado correctamente.' : 'El mensaje se ha registrado, pero el correo no pudo enviarse.'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_property_types() {
    return array('Piso', 'Casa / chalet', 'Ático', 'Dúplex', 'Apartamento', 'Estudio', 'Finca rústica con vivienda', 'Edificio residencial', 'Local comercial', 'Nave', 'Oficina', 'Terreno / solar', 'Garaje', 'Trastero');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_normalize_property_type($value) {
    $value = sanitize_text_field((string) $value);
    $normalized = strtolower(remove_accents($value));
    $external = array(
        'commercial' => 'Local comercial',
        'retail' => 'Local comercial',
        'shop' => 'Local comercial',
        'premises' => 'Local comercial',
        'apartment' => 'Apartamento',
        'flat' => 'Piso',
        'house' => 'Casa / chalet',
        'chalet' => 'Casa / chalet',
        'villa' => 'Casa / chalet',
        'office' => 'Oficina',
        'industrial' => 'Nave',
        'warehouse' => 'Nave',
        'plot' => 'Terreno / solar',
        'land' => 'Terreno / solar',
    );
    if (isset($external[$normalized])) return $external[$normalized];
    $legacy = array(
        'Casa/Chalet' => 'Casa / chalet',
        'Casa / Chalet' => 'Casa / chalet',
        'Local Comercial' => 'Local comercial',
        'Edificio' => 'Edificio residencial',
        'Suelo/Terreno' => 'Terreno / solar',
        'Suelo / Terreno' => 'Terreno / solar',
    );
    return $legacy[$value] ?? $value;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_property_conditions() {
    return array('Lista para entrar / operar', 'Buen estado', 'De origen', 'Sin reforma necesaria', 'Necesita actualización', 'Reforma menor', 'Reforma mayor', 'Reforma integral', 'En obras', 'Obra nueva', 'No califica');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_offer_mandates() {
    return array('Sí, con exclusividad', 'Encargo de agente único', 'Exclusiva compartida', 'No, nota de encargo abierta', 'Sin exclusiva formalizada', 'Pendiente de confirmar');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_need_mandates() {
    return array('Con exclusividad', 'Encargo de agente único', 'Exclusiva compartida', 'Nota de encargo abierta', 'Sin exclusiva formalizada', 'Pendiente de confirmar', 'Cualquiera');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_urgencies() {
    return array('Alta', 'Media', 'Baja', 'Sin urgencia definida');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_documentation_levels() {
    return array('Nota simple únicamente', 'Nota simple + planos', 'Nota simple + certificado energético', 'Nota simple + planos + certificado energético', 'Expediente jurídico completo', 'Tasación disponible', 'Expediente jurídico completo + tasación', 'No califica');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_residential_types() {
    return array('Piso', 'Casa / chalet', 'Ático', 'Dúplex', 'Apartamento', 'Estudio', 'Finca rústica con vivienda', 'Edificio residencial');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_conditions_for_type($type) {
    if (in_array($type, captacion_app_residential_types(), true)) return captacion_app_property_conditions();
    if (in_array($type, array('Local comercial', 'Nave', 'Oficina'), true)) return array('Lista para entrar / operar', 'Buen estado', 'Necesita actualización', 'Reforma menor', 'Reforma mayor', 'Reforma integral', 'En obras', 'No califica');
    if ($type === 'Terreno / solar') return array('No califica');
    if (in_array($type, array('Garaje', 'Trastero'), true)) return array('Buen estado', 'Necesita actualización', 'No califica');
    return array('No califica');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_enum_value($value, $allowed, $field_label) {
    $value = sanitize_text_field((string) $value);
    if (!in_array($value, $allowed, true)) {
        return new WP_Error('captacion_invalid_enum', sprintf('El valor de %s no es válido.', $field_label), array('status' => 422));
    }
    return $value;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_enum_list($values, $allowed, $field_label) {
    $values = is_array($values) ? array_values(array_unique(array_map('sanitize_text_field', $values))) : array();
    if (!$values) {
        return new WP_Error('captacion_required_list', sprintf('Selecciona al menos un valor para %s.', $field_label), array('status' => 422));
    }
    foreach ($values as $value) {
        if (!in_array($value, $allowed, true)) {
            return new WP_Error('captacion_invalid_enum', sprintf('El valor de %s no es válido.', $field_label), array('status' => 422));
        }
    }
    return $values;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_positive_number($value, $field_label) {
    $number = is_numeric($value) ? (float) $value : 0;
    if ($number <= 0) {
        return new WP_Error('captacion_invalid_number', sprintf('%s debe ser mayor que cero.', $field_label), array('status' => 422));
    }
    return $number;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_sanitize_real_estate_payload($record_type, $payload) {
    $payload = is_array($payload) ? $payload : array();
    $type = captacion_app_normalize_property_type($payload['property_type'] ?? $payload['type'] ?? '');
    $type = captacion_app_enum_value($type, captacion_app_property_types(), 'tipo de inmueble');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $title = sanitize_text_field((string) ($payload['title'] ?? ''));
    $description = sanitize_textarea_field((string) ($payload['description'] ?? ''));
    if (strlen($title) < 8) return new WP_Error('captacion_short_title', 'El título debe tener al menos 8 caracteres.', array('status' => 422));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $community_code = sanitize_text_field((string) ($payload['community_code'] ?? $payload['autonomous_community_id'] ?? ''));
    $province_code = sanitize_text_field((string) ($payload['province_code'] ?? $payload['province_id'] ?? ''));
    $municipality_code = sanitize_text_field((string) ($payload['municipality_code'] ?? $payload['municipality_ine_code'] ?? $payload['municipality_id'] ?? ''));
    if (!$community_code || !$province_code || !$municipality_code) {
        return new WP_Error('captacion_required_territory', 'Comunidad autónoma, provincia y municipio son obligatorios.', array('status' => 422));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $is_property = $record_type === 'property';
    $area = captacion_app_positive_number($is_property ? ($payload['total_area_m2'] ?? $payload['superficie_construida'] ?? $payload['surface'] ?? 0) : ($payload['desired_area_min_m2'] ?? $payload['surface'] ?? 0), $is_property ? 'La superficie total' : 'La superficie mínima');
    $amount = captacion_app_positive_number($is_property ? ($payload['indicative_price'] ?? $payload['price'] ?? 0) : ($payload['max_budget'] ?? $payload['budget'] ?? 0), $is_property ? 'El precio orientativo' : 'El presupuesto máximo');
    if (is_wp_error($area)) return $area;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $commission = sanitize_text_field((string) ($is_property ? ($payload['offered_commission'] ?? $payload['fee'] ?? '') : ($payload['accepted_commission'] ?? $payload['feeSplit'] ?? '')));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $residential = in_array($type, captacion_app_residential_types(), true);
    $bathrooms_apply = $residential || in_array($type, array('Local comercial', 'Nave', 'Oficina'), true);
    $rooms = absint($is_property ? ($payload['rooms'] ?? $payload['bedrooms'] ?? 0) : ($payload['min_rooms'] ?? $payload['bedrooms'] ?? 0));
    $bathrooms = absint($is_property ? ($payload['bathrooms'] ?? 0) : ($payload['min_bathrooms'] ?? $payload['bathrooms'] ?? 0));
    if ($residential && $type !== 'Estudio' && $rooms < 1) return new WP_Error('captacion_required_rooms', 'El número de habitaciones es obligatorio para vivienda.', array('status' => 422));
    if ($bathrooms_apply && $bathrooms < 1) return new WP_Error('captacion_required_bathrooms', 'El número de baños es obligatorio para este tipo de inmueble.', array('status' => 422));
    if (!$residential) $rooms = 0;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $urgency = captacion_app_enum_value($is_property ? ($payload['sale_urgency'] ?? $payload['urgency'] ?? '') : ($payload['search_urgency'] ?? $payload['urgency'] ?? ''), captacion_app_urgencies(), $is_property ? 'urgencia de venta' : 'urgencia de búsqueda');
    $docs = captacion_app_enum_value($is_property ? ($payload['documentation_level'] ?? $payload['docs'] ?? '') : ($payload['required_documentation_level'] ?? ''), captacion_app_documentation_levels(), 'nivel de documentación');
    if (is_wp_error($urgency)) return $urgency;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $clean = array();
    foreach ($payload as $key => $value) {
        $safe_key = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $key);
        if ($safe_key && is_scalar($value)) $clean[$safe_key] = is_numeric($value) ? $value : sanitize_text_field((string) $value);
    }
    $clean['id'] = sanitize_text_field((string) ($payload['id'] ?? ''));
    $clean['title'] = $title;
    $clean['description'] = $description;
    $clean['property_type'] = $type;
    $clean['type'] = $type;
    $clean['community_code'] = $community_code;
    $clean['province_code'] = $province_code;
    $clean['municipality_code'] = $municipality_code;
    $clean['rooms'] = $rooms;
    $clean['bedrooms'] = $rooms;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($is_property) {
        $legacy_rehab = filter_var($payload['necesita_reforma_integral'] ?? $payload['rehab'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $condition = captacion_app_enum_value($payload['property_condition'] ?? ($legacy_rehab ? 'Reforma integral' : ''), captacion_app_conditions_for_type($type), 'condición de la propiedad');
        $mandate = captacion_app_enum_value($payload['mandate_type'] ?? '', captacion_app_offer_mandates(), 'tipo de encargo');
        if (is_wp_error($condition)) return $condition;
        if (is_wp_error($mandate)) return $mandate;
        $clean['total_area_m2'] = $area;
        $clean['surface'] = $area;
        $clean['indicative_price'] = $amount;
        $clean['price'] = $amount;
        $clean['offered_commission'] = $commission;
        $clean['fee'] = $commission;
        $clean['property_condition'] = $condition;
        $clean['rehab'] = $condition === 'Reforma integral';
        $clean['mandate_type'] = $mandate;
        $clean['exclusive'] = in_array($mandate, array('Sí, con exclusividad', 'Encargo de agente único', 'Exclusiva compartida'), true);
        $clean['sale_urgency'] = $urgency;
        $clean['urgency'] = $urgency;
        $clean['documentation_level'] = $docs;
        $clean['docs'] = $docs;
    } else {
        $conditions = captacion_app_enum_list($payload['accepted_property_conditions'] ?? array(), captacion_app_conditions_for_type($type), 'condiciones aceptadas');
        $mandates = captacion_app_enum_list($payload['accepted_mandate_types'] ?? array(), captacion_app_need_mandates(), 'tipos de captación aceptada');
        if (is_wp_error($conditions)) return $conditions;
        if (is_wp_error($mandates)) return $mandates;
        $clean['desired_area_min_m2'] = $area;
        $clean['surface'] = $area;
        $clean['min_rooms'] = $rooms;
        $clean['min_bathrooms'] = $bathrooms;
        $clean['max_budget'] = $amount;
        $clean['budget'] = $amount;
        $clean['accepted_commission'] = $commission;
        $clean['feeSplit'] = $commission;
        $clean['accepted_property_conditions'] = $conditions;
        $clean['accepted_mandate_types'] = $mandates;
        $clean['search_urgency'] = $urgency;
        $clean['urgency'] = $urgency;
        $clean['required_documentation_level'] = $docs;
    }
    return $clean;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_upsert_record($data) {
    global $wpdb;
    captacion_app_maybe_install_records_table();
    $table = captacion_app_records_table_name();
    $type = sanitize_key($data['record_type'] ?? '');
    if (!in_array($type, captacion_app_allowed_record_types(), true)) {
        return new WP_Error('captacion_invalid_record_type', 'Tipo de registro no permitido.', array('status' => 400));
    }
    $payload = is_array($data['payload'] ?? null) ? $data['payload'] : array();
    $record_key = sanitize_text_field($data['record_key'] ?? '');
    if (!$record_key) {
        $record_key = sanitize_title($type . '-' . ($payload['id'] ?? '') . '-' . md5(wp_json_encode($payload)));
    }
    $current_user_id = get_current_user_id();
    $now = current_time('mysql');
    $row = array(
        'record_type' => $type,
        'record_key' => $record_key,
        'user_id' => absint($data['user_id'] ?? $current_user_id),
        'user_email' => sanitize_email($data['user_email'] ?? ''),
        'title' => sanitize_text_field($data['title'] ?? ''),
        'status' => sanitize_text_field($data['status'] ?? ''),
        'related_id' => sanitize_text_field($data['related_id'] ?? ''),
        'payload' => wp_json_encode($payload),
        'updated_at' => $now,
        'owner_user_id' => absint($data['owner_user_id'] ?? $current_user_id),
        'created_by' => absint($data['created_by'] ?? $current_user_id),
        'import_batch_id' => sanitize_text_field($data['import_batch_id'] ?? ''),
        'data_origin' => sanitize_key($data['data_origin'] ?? 'manual'),
        'is_demo' => !empty($data['is_demo']) ? 1 : 0,
        'privacy_scope' => sanitize_key($data['privacy_scope'] ?? 'private_user'),
        'consent_status' => sanitize_text_field($data['consent_status'] ?? ''),
        'source_file_name' => sanitize_text_field($data['source_file_name'] ?? ''),
        'source_hash' => sanitize_text_field($data['source_hash'] ?? ''),
        'deleted_at' => $data['deleted_at'] ?? null,
    );
    $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE record_type = %s AND record_key = %s", $type, $record_key));
    if ($existing_id) {
        unset($row['created_at']);
        $wpdb->update($table, $row, array('id' => absint($existing_id)));
        if (in_array($type, array('access_request', 'task'), true) && captacion_app_is_terminal_workflow_status($row['status'])) {
            captacion_app_complete_pending_feed_deletions(absint($row['owner_user_id']));
        }
        return absint($existing_id);
    }
    $row['created_at'] = $now;
    $wpdb->insert($table, $row);
    if (in_array($type, array('access_request', 'task'), true) && captacion_app_is_terminal_workflow_status($row['status'])) {
        captacion_app_complete_pending_feed_deletions(absint($row['owner_user_id']));
    }
    return absint($wpdb->insert_id);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_save_record(WP_REST_Request $request) {
    $payload = $request->get_json_params();
    $payload = is_array($payload) ? $payload : array();
    $record_payload = $payload['payload'] ?? array();
    if (!is_array($record_payload)) {
        $record_payload = array('value' => $record_payload);
    }
    $record_type = sanitize_key($payload['record_type'] ?? '');
    if (in_array($record_type, array('property', 'need'), true)) {
        $record_payload = captacion_app_sanitize_real_estate_payload($record_type, $record_payload);
        if (is_wp_error($record_payload)) return $record_payload;
    }
    $user_id = get_current_user_id();
    $result = captacion_app_upsert_record(array(
        'record_type' => $record_type,
        'record_key' => $payload['record_key'] ?? '',
        'user_id' => $user_id,
        'user_email' => wp_get_current_user()->user_email,
        'title' => $record_payload['title'] ?? $payload['title'] ?? '',
        'status' => $payload['status'] ?? '',
        'related_id' => $payload['related_id'] ?? '',
        'payload' => $record_payload,
        'owner_user_id' => $user_id,
        'created_by' => $user_id,
        'data_origin' => 'manual',
        'is_demo' => 0,
        'privacy_scope' => 'private_user',
    ));
    if (is_wp_error($result)) {
        return $result;
    }
    return rest_ensure_response(array('ok' => true, 'id' => $result));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_list_records(WP_REST_Request $request) {
    global $wpdb;
    captacion_app_maybe_install_records_table();
    $table = captacion_app_records_table_name();
    $type = sanitize_key((string) $request->get_param('record_type'));
    $email = sanitize_email((string) $request->get_param('user_email'));
    $limit = min(5000, max(1, absint($request->get_param('limit') ?: 100)));
    $include_demo = rest_sanitize_boolean($request->get_param('include_demo') ?? false);
    $where = array('deleted_at IS NULL');
    $params = array();
    $batches_table = captacion_app_import_batches_table_name();
    $where[] = "NOT EXISTS (SELECT 1 FROM {$batches_table} b WHERE b.import_batch_id = {$table}.import_batch_id AND b.status IN ('paused','pending_deletion','deleted') AND b.deleted_at IS NULL)";
    $user_id = get_current_user_id();
    if (current_user_can('manage_options')) {
        if ($email) { $where[] = 'user_email = %s'; $params[] = $email; }
    } else {
        $owner_conditions = array('owner_user_id = %d');
        $params[] = $user_id;
        $owner_conditions[] = 'user_id = %d';
        $params[] = $user_id;
        if ($include_demo) {
            $owner_conditions[] = '(is_demo = 1 AND privacy_scope = \'global_demo\')';
        }
        $where[] = '(' . implode(' OR ', $owner_conditions) . ')';
    }
    if ($type) { $where[] = 'record_type = %s'; $params[] = $type; }
    $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where) . " ORDER BY updated_at DESC LIMIT %d";
    $params[] = $limit;
    $rows = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    foreach ($rows as &$row) {
        $row['payload'] = json_decode($row['payload'] ?: '{}', true);
    }
    return rest_ensure_response(array('ok' => true, 'records' => $rows));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

define('CAPTACION_XML_MAX_SIZE', 10 * 1024 * 1024);
define('CAPTACION_XML_MAX_RECORDS', 1000);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_import_upload_dir_filter($dirs) {
    $subdir = '/captacion-imports';
    $dirs['path'] = $dirs['basedir'] . $subdir;
    $dirs['url'] = $dirs['baseurl'] . $subdir;
    $dirs['subdir'] = $subdir;
    if (!wp_mkdir_p($dirs['path'])) {
        $dirs['error'] = 'No se pudo crear el directorio seguro de importaciones.';
    }
    return $dirs;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_import_field_aliases() {
    return array(
        'external_id' => array('id','reference','ref','codigo','cod','external_id','mls_id','referencia'),
        'title' => array('title','titulo','name','nombre','headline','subject'),
        'description' => array('description','descripcion','desc','remarks','text','observations','observaciones'),
        'property_type' => array('type','tipo','property_type','tipologia','category','categoria'),
        'operation' => array('operation','operacion','transaction','offer_type','tipo_operacion'),
        'price' => array('price','precio','amount','value','preu','pvp','importe'),
        'currency' => array('currency','moneda'),
        'province' => array('province','provincia','region','state'),
        'municipality' => array('city','municipio','municipality','ciudad','town','poblacion','localidad'),
        'postal_code' => array('postal_code','zip','codigo_postal','cp','postcode'),
        'address_approx' => array('address','direccion','street','calle','location_detail'),
        'surface' => array('surface','superficie','area','built_area','size','m2','metros','total_area'),
        'rooms' => array('rooms','habitaciones','dormitorios','bedrooms','beds'),
        'bathrooms' => array('bathrooms','banos','baños','baths','toilets'),
        'latitude' => array('latitude','lat','latitud'),
        'longitude' => array('longitude','lng','lon','longitud'),
        'image' => array('image','images','gallery','galeria','imagenes','photos','photo','pictures','picture','url_image','image_url','image_urls','foto','fotos'),
        'status' => array('status','estado','state'),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_import_value_by_alias($row, $field, $default = '') {
    $aliases = captacion_app_import_field_aliases();
    foreach (($aliases[$field] ?? array($field)) as $alias) {
        $key = sanitize_key(remove_accents($alias));
        if (isset($row[$key]) && trim((string) $row[$key]) !== '') return trim((string) $row[$key]);
    }
    return $default;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_normalize_import_property_row($row, $source_name, $index, $prefix) {
    $external_id = captacion_app_import_value_by_alias($row, 'external_id', $prefix . '-' . ($index + 1));
    $type = captacion_app_normalize_property_type(captacion_app_import_value_by_alias($row, 'property_type', 'Piso'));
    if (!in_array($type, captacion_app_property_types(), true)) $type = 'Piso';
    $price = captacion_app_xml_number_value(captacion_app_import_value_by_alias($row, 'price', '0'));
    $surface = captacion_app_xml_number_value(captacion_app_import_value_by_alias($row, 'surface', '0'));
    $municipality = sanitize_text_field(captacion_app_import_value_by_alias($row, 'municipality'));
    $province = sanitize_text_field(captacion_app_import_value_by_alias($row, 'province'));
    $description = sanitize_textarea_field(captacion_app_import_value_by_alias($row, 'description', 'Propiedad importada.'));
    $title = sanitize_text_field(captacion_app_import_value_by_alias($row, 'title'));
    if (!$title) $title = trim($type . ($municipality ? ' en ' . $municipality : '') . ($external_id ? ' - Ref. ' . $external_id : ''));
    if (!$title) $title = 'Propiedad importada';
    $operation = captacion_app_xml_operation_value(captacion_app_import_value_by_alias($row, 'operation'), '');
    $rooms = absint(captacion_app_import_value_by_alias($row, 'rooms', '0'));
    $bathrooms = absint(captacion_app_import_value_by_alias($row, 'bathrooms', '0'));
    $payload = array(
        'id' => sanitize_text_field($external_id),
        'external_id' => sanitize_text_field($external_id),
        'title' => $title,
        'description' => $description,
        'property_type' => $type,
        'type' => $type,
        'operation' => $operation,
        'price' => $price,
        'indicative_price' => $price,
        'currency' => sanitize_text_field(captacion_app_import_value_by_alias($row, 'currency', 'EUR')),
        'province' => $province,
        'municipality' => $municipality,
        'postal_code' => sanitize_text_field(captacion_app_import_value_by_alias($row, 'postal_code')),
        'address_approx' => sanitize_text_field(captacion_app_import_value_by_alias($row, 'address_approx')),
        'surface' => $surface,
        'total_area_m2' => $surface,
        'rooms' => $rooms,
        'bedrooms' => $rooms,
        'bathrooms' => $bathrooms,
        'latitude' => sanitize_text_field(captacion_app_import_value_by_alias($row, 'latitude')),
        'longitude' => sanitize_text_field(captacion_app_import_value_by_alias($row, 'longitude')),
        'image' => esc_url_raw(captacion_app_import_value_by_alias($row, 'image')),
        'source_file' => sanitize_text_field($source_name),
        'publication_status' => 'active',
        'imported_at' => current_time('mysql'),
        'updated_at' => current_time('mysql'),
    );
    $status = sanitize_text_field(captacion_app_import_value_by_alias($row, 'status', 'active'));
    $record_key = $prefix . '-' . substr(hash('sha256', $source_name . '|' . $external_id), 0, 18);
    return array('record_type' => 'property', 'record_key' => $record_key, 'title' => $title, 'status' => $status, 'related_id' => sanitize_text_field($external_id), 'payload' => $payload);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_validate_import_xml($raw_xml) {
    if (empty($raw_xml) || strlen(trim($raw_xml)) === 0) {
        return new WP_Error('captacion_xml_empty', 'El XML está vacío.', array('status' => 400));
    }
    if (strlen($raw_xml) > CAPTACION_XML_MAX_SIZE) {
        return new WP_Error('captacion_xml_too_large', 'El XML supera el tamaño máximo de 10 MB.', array('status' => 413));
    }
    if (stripos($raw_xml, '<!DOCTYPE') !== false || stripos($raw_xml, '<!ENTITY') !== false) {
        return new WP_Error('captacion_xml_doctype', 'El XML contiene DOCTYPE o entidades no permitidas.', array('status' => 400));
    }
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($raw_xml, 'SimpleXMLElement', LIBXML_NONET);
    if ($xml === false) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        $msg = 'Error al parsear XML: ' . ($errors[0]->message ?? 'formato inválido');
        return new WP_Error('captacion_xml_parse', $msg, array('status' => 400));
    }
    libxml_clear_errors();
    if ($xml->getName() !== 'captacionData') {
        return new WP_Error('captacion_xml_root', 'El elemento raíz debe ser captacionData.', array('status' => 400));
    }
    $schema_version = (string) $xml['schemaVersion'];
    if (empty($schema_version)) {
        return new WP_Error('captacion_xml_schema', 'Falta schemaVersion en el elemento raíz.', array('status' => 400));
    }
    $data_origin = (string) $xml['dataOrigin'];
    $privacy_scope = (string) $xml['privacyScope'];
    $record_count = 0;
    $records = array();
    foreach ($xml->children() as $child) {
        $tag = $child->getName();
        if (!in_array($tag, captacion_app_allowed_record_types(), true)) continue;
        $record_count++;
        if ($record_count > CAPTACION_XML_MAX_RECORDS) {
            return new WP_Error('captacion_xml_max_records', 'El XML supera el máximo de ' . CAPTACION_XML_MAX_RECORDS . ' registros.', array('status' => 413));
        }
        $record_key = (string) $child['recordKey'];
        if (empty($record_key)) {
            return new WP_Error('captacion_xml_key', 'Falta recordKey en un registro.', array('status' => 400));
        }
        $payload = array();
        foreach ($child->children() as $field) {
            $fname = $field->getName();
            $fval = (string) $field;
            if (!empty($fname)) {
                if (isset($payload[$fname])) {
                    if (!is_array($payload[$fname])) $payload[$fname] = array($payload[$fname]);
                    $payload[$fname][] = $fval;
                } else {
                    $payload[$fname] = $fval;
                }
            }
        }
        $records[] = array(
            'record_type' => $tag,
            'record_key' => $record_key,
            'title' => $payload['title'] ?? '',
            'status' => $payload['status'] ?? '',
            'related_id' => $payload['related_id'] ?? '',
            'payload' => $payload,
        );
    }
    return array(
        'schemaVersion' => $schema_version,
        'dataOrigin' => $data_origin,
        'privacyScope' => $privacy_scope,
        'records' => $records,
        'total' => count($records),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_generate_xml_summary($records, $imported, $rejected) {
    $types = array_count_values(array_column($records, 'record_type'));
    return array(
        'totalRecords' => count($records),
        'recordsImported' => $imported,
        'recordsRejected' => $rejected,
        'byType' => $types,
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_property_marketplace_missing_fields($payload) {
    $payload = is_array($payload) ? $payload : array();
    $missing = array();
    $required = array(
        'title' => array('title'),
        'type' => array('property_type', 'type'),
        'operation' => array('operation'),
        'price' => array('price', 'indicative_price'),
        'currency' => array('currency'),
        'location' => array('municipality', 'city', 'province'),
        'description' => array('description'),
        'owner' => array('owner_user_id', 'user_id'),
        'source' => array('data_origin', 'source_type'),
        'rooms' => array('rooms', 'bedrooms'),
        'bathrooms' => array('bathrooms', 'baths'),
        'surface' => array('surface', 'total_area_m2', 'built_area'),
    );
    foreach ($required as $label => $keys) {
        $has_value = false;
        foreach ($keys as $key) {
            if (isset($payload[$key]) && trim((string) $payload[$key]) !== '' && (string) $payload[$key] !== '0') {
                $has_value = true;
                break;
            }
        }
        if (!$has_value) $missing[] = $label;
    }
    $has_province_city = (trim((string) ($payload['province'] ?? '')) !== '' && trim((string) ($payload['municipality'] ?? $payload['city'] ?? '')) !== '');
    $has_codes = (trim((string) ($payload['province_code'] ?? '')) !== '' && trim((string) ($payload['municipality_code'] ?? '')) !== '');
    if (!$has_province_city && !$has_codes && !in_array('location', $missing, true)) $missing[] = 'location';
    return array_values(array_unique($missing));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_prepare_imported_property_payload($payload, $context) {
    $payload = is_array($payload) ? $payload : array();
    $payload['owner_user_id'] = absint($context['owner_user_id'] ?? get_current_user_id());
    $payload['user_id'] = absint($context['user_id'] ?? get_current_user_id());
    $payload['feed_id'] = sanitize_text_field($context['import_batch_id'] ?? '');
    $payload['import_batch_id'] = sanitize_text_field($context['import_batch_id'] ?? '');
    $payload['source_type'] = sanitize_key($context['data_origin'] ?? 'xml_file');
    $payload['data_origin'] = sanitize_key($context['data_origin'] ?? 'xml_file');
    $payload['source_owner'] = $payload['owner_user_id'];
    $payload['is_demo'] = !empty($context['is_demo']);
    $payload['imported_at'] = $payload['imported_at'] ?? current_time('mysql');
    $payload['last_imported_at'] = current_time('mysql');
    $missing = captacion_app_property_marketplace_missing_fields($payload);
    $payload['missing_fields'] = $missing;
    $payload['review_alerts'] = $missing;
    $payload['publication_status'] = empty($missing) ? 'active' : 'pending_review';
    $payload['status'] = empty($missing) ? 'active' : 'pending_review';
    return $payload;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_import_records_from_xml($parsed, $overrides) {
    global $wpdb;
    $table = captacion_app_records_table_name();
    $user_id = absint($overrides['user_id'] ?? get_current_user_id());
    $user = get_userdata($user_id);
    $user_email = $user ? $user->user_email : '';
    $import_batch_id = $overrides['import_batch_id'] ?? '';
    $data_origin = $overrides['data_origin'] ?? 'user_xml';
    $is_demo = !empty($overrides['is_demo']) ? 1 : 0;
    $privacy_scope = $overrides['privacy_scope'] ?? 'private_user';
    $owner_user_id = absint($overrides['owner_user_id'] ?? $user_id);
    $source_file_name = sanitize_text_field($overrides['source_file_name'] ?? '');
    $source_hash = $overrides['source_hash'] ?? '';
    $imported = 0;
    $updated = 0;
    $pending_review = 0;
    $rejected = 0;
    $errors = array();
    foreach ($parsed['records'] as $rec) {
        $record_type = sanitize_key($rec['record_type']);
        $record_key = sanitize_text_field($rec['record_key']);
        $payload_raw = is_array($rec['payload']) ? $rec['payload'] : array();
        if (!in_array($data_origin, array('xml_url', 'xml_file', 'csv_file', 'json_file', 'webhook'), true) && in_array($record_type, array('property', 'need'), true)) {
            $sanitized = captacion_app_sanitize_real_estate_payload($record_type, $payload_raw);
            if (is_wp_error($sanitized)) {
                $rejected++;
                $errors[] = array('key' => $record_key, 'error' => $sanitized->get_error_message());
                continue;
            }
            $payload_raw = $sanitized;
        }
        if ($record_type === 'property' && in_array($data_origin, array('xml_url', 'xml_file', 'csv_file', 'json_file', 'webhook'), true)) {
            $payload_raw = captacion_app_prepare_imported_property_payload($payload_raw, array(
                'owner_user_id' => $owner_user_id,
                'user_id' => $user_id,
                'import_batch_id' => $import_batch_id,
                'data_origin' => $data_origin,
                'is_demo' => $is_demo,
            ));
            if (($payload_raw['status'] ?? '') === 'pending_review') $pending_review++;
        }
        $title = sanitize_text_field($rec['title'] ?: ($payload_raw['title'] ?? ''));
        $status = sanitize_text_field($payload_raw['status'] ?? $rec['status'] ?? '');
        $related_id = sanitize_text_field($rec['related_id'] ?: ($payload_raw['related_id'] ?? ''));
        $now = current_time('mysql');
        $row = array(
            'record_type' => $record_type,
            'record_key' => $record_key,
            'user_id' => $user_id,
            'user_email' => $user_email,
            'title' => $title,
            'status' => $status,
            'related_id' => $related_id,
            'payload' => wp_json_encode($payload_raw),
            'created_at' => $now,
            'updated_at' => $now,
            'owner_user_id' => $owner_user_id,
            'created_by' => $user_id,
            'import_batch_id' => $import_batch_id,
            'data_origin' => $data_origin,
            'is_demo' => $is_demo,
            'privacy_scope' => $privacy_scope,
            'consent_status' => $overrides['consent_status'] ?? '',
            'source_file_name' => $source_file_name,
            'source_hash' => $source_hash,
            'deleted_at' => null,
        );
        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE record_type = %s AND record_key = %s",
            $record_type, $record_key
        ));
        if ($existing_id) {
            $existing_payload_json = $wpdb->get_var($wpdb->prepare("SELECT payload FROM {$table} WHERE id = %d", absint($existing_id)));
            $existing_payload = json_decode($existing_payload_json ?: '{}', true);
            if (is_array($existing_payload) && !empty($existing_payload['manual_override']) && $record_type === 'property') {
                $payload_raw['xml_update_pending'] = true;
                $payload_raw['xml_pending_payload'] = $row['payload'];
                $payload_raw = array_merge($payload_raw, $existing_payload);
                $payload_raw['xml_update_pending'] = true;
                $row['payload'] = wp_json_encode($payload_raw);
                $row['status'] = sanitize_text_field($existing_payload['status'] ?? $row['status']);
            }
        }
        $result = captacion_app_upsert_record($row);
        if (is_wp_error($result)) {
            $rejected++;
            $errors[] = array('key' => $record_key, 'error' => $result->get_error_message());
        } else {
            if ($existing_id) $updated++;
            else $imported++;
        }
    }
    return array(
        'imported' => $imported,
        'updated' => $updated,
        'pending_review' => $pending_review,
        'rejected' => $rejected,
        'errors' => $errors,
        'summary' => captacion_app_generate_xml_summary($parsed['records'], $imported, $rejected),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_download_xml_feed_url($url) {
    $url = esc_url_raw(trim((string) $url));
    if (!$url || !wp_http_validate_url($url)) {
        return new WP_Error('captacion_xml_url_invalid', 'Introduce una URL XML valida.', array('status' => 422));
    }
    if (strlen($url) > 190) {
        return new WP_Error('captacion_xml_url_length', 'La URL del XML no puede superar 190 caracteres.', array('status' => 422));
    }
    $parts = wp_parse_url($url);
    if (empty($parts['scheme']) || !in_array(strtolower($parts['scheme']), array('http', 'https'), true)) {
        return new WP_Error('captacion_xml_url_scheme', 'La URL debe empezar por http:// o https://.', array('status' => 422));
    }
    $response = wp_remote_get($url, array(
        'timeout' => 25,
        'redirection' => 3,
        'limit_response_size' => CAPTACION_XML_MAX_SIZE + 1024,
        'user-agent' => 'Compra Captación XML Feed Importer',
    ));
    if (is_wp_error($response)) {
        return new WP_Error('captacion_xml_url_fetch', 'No se pudo descargar el XML: ' . $response->get_error_message(), array('status' => 400));
    }
    $code = wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        return new WP_Error('captacion_xml_url_http', 'El servidor XML respondio con HTTP ' . $code . '.', array('status' => 400));
    }
    $body = wp_remote_retrieve_body($response);
    if (!$body || strlen(trim($body)) === 0) {
        return new WP_Error('captacion_xml_url_empty', 'El XML descargado esta vacio.', array('status' => 400));
    }
    if (strlen($body) > CAPTACION_XML_MAX_SIZE) {
        return new WP_Error('captacion_xml_url_size', 'El XML supera el tamano maximo permitido.', array('status' => 413));
    }
    $content_type = strtolower(trim((string) wp_remote_retrieve_header($response, 'content-type')));
    $body_preview = strtolower(substr(trim($body), 0, 500));
    $looks_like_html = strpos($content_type, 'html') !== false
        || preg_match('/^<!doctype\s+html/i', $body)
        || preg_match('/^<html\b/i', $body)
        || preg_match('/<body\b/i', $body)
        || preg_match('/ha fallado la comprobaci[oó]n de la cookie|cookie|consent|login|iniciar sesi[oó]n|acceso restringido/i', $body_preview);
    if ($looks_like_html) {
        return new WP_Error(
            'captacion_xml_url_cookie_or_html',
            'La URL no devuelve XML: parece una pagina HTML, de login o de comprobacion de cookies.',
            array('status' => 422)
        );
    }
    if (stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) {
        return new WP_Error('captacion_xml_url_doctype', 'El XML contiene DOCTYPE o entidades no permitidas.', array('status' => 400));
    }
    return array('url' => $url, 'body' => $body, 'hash' => hash('sha256', $body));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_child_value($node, $names, $default = '') {
    foreach ((array) $names as $name) {
        if (isset($node->{$name}) && trim((string) $node->{$name}) !== '') return sanitize_text_field((string) $node->{$name});
    }
    foreach ((array) $names as $name) {
        $att = (string) $node[$name];
        if ($att !== '') return sanitize_text_field($att);
    }
    return $default;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_nested_child_value($node, $paths, $default = '') {
    foreach ((array) $paths as $path) {
        $parts = is_array($path) ? $path : explode('/', (string) $path);
        $current = $node;
        foreach ($parts as $part) {
            if (!isset($current->{$part})) {
                $current = null;
                break;
            }
            $current = $current->{$part};
        }
        if ($current !== null && trim((string) $current) !== '') return sanitize_text_field((string) $current);
    }
    return $default;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_number_value($value) {
    $value = trim((string) $value);
    if ($value === '') return 0;
    $value = preg_replace('/[^0-9,.]/', '', $value);
    if (strpos($value, ',') !== false && strpos($value, '.') !== false && strrpos($value, ',') > strrpos($value, '.')) {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
    } else {
        $value = str_replace(',', '.', $value);
    }
    return (float) $value;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_operation_value($operation, $price_freq = '') {
    $raw = strtolower(trim((string) ($operation ?: $price_freq)));
    if (in_array($raw, array('rent', 'rental', 'alquiler', 'month', 'monthly', 'semana', 'week', 'weekly', 'arrendamiento', 'renting', 'lease'), true)) return 'alquiler';
    if (in_array($raw, array('sale', 'sell', 'venta', 'comprar', 'buy', 'compraventa', 'vender', 'vendo'), true)) return 'venta';
    if (in_array($raw, array('transfer', 'traspaso', 'traspass'), true)) return 'traspaso';
    return 'venta';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_first_image($node) {
    $names = array('images', 'image', 'fotos', 'foto', 'photos', 'photo', 'pictures', 'picture', 'galeria', 'gallery', 'media', 'multimedia', 'url', 'URL', 'Url');
    $url_keys = array('url', 'URL', 'Url', 'src', 'href', 'link', 'file', 'archivo', 'ruta', 'path', 'source', 'download');
    foreach ($names as $name) {
        if (!isset($node->{$name})) continue;
        $value = '';
        $el = $node->{$name};
        if ($el->children()->count()) {
            foreach ($el->children() as $child) {
                if ($child->children()->count()) {
                    $value = captacion_app_xml_nested_child_value($child, $url_keys, '');
                }
                if (!$value) {
                    foreach ($url_keys as $attr) {
                        $att = (string) $child[$attr];
                        if ($att) { $value = $att; break; }
                    }
                }
                if (!$value) $value = trim((string) $child);
                if ($value) break;
            }
        } else {
            $value = trim((string) $el);
        }
        if (!$value) {
            foreach ($url_keys as $attr) {
                $att = (string) $el[$attr];
                if ($att) { $value = $att; break; }
            }
        }
        if ($value) return esc_url_raw($value);
    }
    return '';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_desc_value($node) {
    $preferred = array('es', 'en');
    if (isset($node->desc)) {
        $desc = $node->desc;
        if ($desc->children()->count()) {
            foreach ($preferred as $lang) {
                if (isset($desc->{$lang}) && trim((string) $desc->{$lang}) !== '') return sanitize_text_field((string) $desc->{$lang});
            }
            foreach ($desc->children() as $child) {
                $val = trim((string) $child);
                if ($val !== '') return sanitize_text_field($val);
            }
        }
        $val = trim((string) $desc);
        if ($val !== '') return sanitize_text_field($val);
    }
    return '';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_node_to_array($node) {
    $result = array();
    if (!$node) return $result;
    foreach ($node->attributes() as $name => $value) {
        $result['@' . $name] = sanitize_text_field((string) $value);
    }
    foreach ($node->children() as $child) {
        $name = $child->getName();
        $value = $child->children()->count() ? captacion_app_xml_node_to_array($child) : trim((string) $child);
        if (isset($result[$name])) {
            if (!is_array($result[$name]) || array_keys($result[$name]) !== range(0, count($result[$name]) - 1)) {
                $result[$name] = array($result[$name]);
            }
            $result[$name][] = $value;
        } else {
            $result[$name] = $value;
        }
    }
    return $result;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_xml_image_urls($node) {
    $urls = array();
    $image_tags = array('image', 'imagen', 'photo', 'foto', 'picture', 'pictures', 'photos');
    $url_keys = array('url', 'URL', 'Url', 'src', 'href', 'link', 'file', 'archivo', 'ruta', 'path', 'source', 'download');
    foreach ($node->xpath('.//*') ?: array() as $child) {
        $tag = strtolower($child->getName());
        if (!in_array($tag, $image_tags, true)) continue;
        $value = '';
        foreach ($url_keys as $key) {
            if (isset($child->{$key}) && trim((string) $child->{$key}) !== '') { $value = trim((string) $child->{$key}); break; }
        }
        if (!$value) {
            foreach ($url_keys as $attr) {
                $att = (string) $child[$attr];
                if ($att !== '') { $value = $att; break; }
            }
        }
        if (!$value) $value = trim((string) $child);
        if ($value) $urls[] = esc_url_raw($value);
    }
    return array_values(array_unique(array_filter($urls)));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_parse_csv_properties($raw_csv, $source_name) {
    if (empty($raw_csv) || strlen(trim($raw_csv)) === 0) return new WP_Error('captacion_csv_empty', 'El CSV esta vacio.', array('status' => 400));
    if (strlen($raw_csv) > CAPTACION_XML_MAX_SIZE) return new WP_Error('captacion_csv_too_large', 'El CSV supera el tamano maximo de 10 MB.', array('status' => 413));
    $raw_csv = preg_replace('/^\xEF\xBB\xBF/', '', (string) $raw_csv);
    $lines = preg_split('/\r\n|\r|\n/', $raw_csv);
    $sample = implode("\n", array_slice($lines, 0, 5));
    $delimiter = substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';
    $headers = null;
    $records = array();
    $seen = array();
    foreach ($lines as $line_index => $line) {
        if (trim($line) === '') continue;
        $cells = str_getcsv($line, $delimiter);
        if ($headers === null) {
            $headers = array_map(static function ($header) { return sanitize_key(remove_accents(trim((string) $header))); }, $cells);
            continue;
        }
        if (count(array_filter($cells, static function ($cell) { return trim((string) $cell) !== ''; })) === 0) continue;
        if (count($records) >= CAPTACION_XML_MAX_RECORDS) break;
        $row = array();
        foreach ($headers as $index => $header) {
            if ($header === '') continue;
            $row[$header] = isset($cells[$index]) ? trim((string) $cells[$index]) : '';
        }
        $record = captacion_app_normalize_import_property_row($row, $source_name, $line_index, 'csv');
        if (isset($seen[$record['record_key']])) continue;
        $seen[$record['record_key']] = true;
        $records[] = $record;
    }
    if (empty($headers)) return new WP_Error('captacion_csv_headers', 'El CSV debe incluir una fila de cabeceras.', array('status' => 422));
    return array('schemaVersion' => 'csv-1.0', 'dataOrigin' => 'csv_file', 'privacyScope' => 'private_user', 'records' => $records, 'total' => count($records));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_parse_json_properties($raw_json, $source_name) {
    if (empty($raw_json) || strlen(trim($raw_json)) === 0) return new WP_Error('captacion_json_empty', 'El JSON esta vacio.', array('status' => 400));
    if (strlen($raw_json) > CAPTACION_XML_MAX_SIZE) return new WP_Error('captacion_json_too_large', 'El JSON supera el tamano maximo de 10 MB.', array('status' => 413));
    $decoded = json_decode((string) $raw_json, true);
    if (!is_array($decoded)) return new WP_Error('captacion_json_parse', 'No se pudo interpretar el JSON.', array('status' => 400));
    $items = $decoded;
    foreach (array('properties', 'propiedades', 'listings', 'items', 'data', 'records') as $key) {
        if (isset($decoded[$key]) && is_array($decoded[$key])) { $items = $decoded[$key]; break; }
    }
    if (isset($items['id']) || isset($items['reference']) || isset($items['referencia'])) $items = array($items);
    $records = array();
    $seen = array();
    foreach ($items as $index => $item) {
        if (!is_array($item) || count($records) >= CAPTACION_XML_MAX_RECORDS) continue;
        $normalized = array();
        foreach ($item as $key => $value) {
            if (is_array($value)) continue;
            $normalized[sanitize_key(remove_accents((string) $key))] = is_scalar($value) ? (string) $value : '';
        }
        $record = captacion_app_normalize_import_property_row($normalized, $source_name, $index, 'json');
        if (isset($seen[$record['record_key']])) continue;
        $seen[$record['record_key']] = true;
        $records[] = $record;
    }
    return array('schemaVersion' => 'json-1.0', 'dataOrigin' => 'json_file', 'privacyScope' => 'private_user', 'records' => $records, 'total' => count($records));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_parse_external_xml_properties($raw_xml, $source_url) {
    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($raw_xml, 'SimpleXMLElement', LIBXML_NONET);
    if ($xml === false) {
        libxml_clear_errors();
        return new WP_Error('captacion_xml_external_parse', 'No se pudo interpretar el XML descargado.', array('status' => 400));
    }
    libxml_clear_errors();
    $candidate_names = array('property', 'realty', 'offer', 'listing', 'item', 'ad', 'object', 'estate');
    $nodes = array();
    foreach ($candidate_names as $name) {
        foreach ($xml->xpath('//*[local-name()="' . $name . '"]') ?: array() as $node) $nodes[] = $node;
    }
    if (!$nodes && in_array($xml->getName(), $candidate_names, true)) $nodes[] = $xml;
    $records = array();
    $seen = array();
    foreach ($nodes as $index => $node) {
        if (count($records) >= CAPTACION_XML_MAX_RECORDS) break;
        $external_id = captacion_app_xml_child_value($node, array('id', 'reference', 'ref', 'external_id', 'mls_id', 'codigo', 'cod', 'ID'), 'xml-' . ($index + 1));
        $record_key = 'xmlurl-' . substr(md5($source_url . '|' . $external_id . '|' . $index), 0, 18);
        if (isset($seen[$record_key])) continue;
        $seen[$record_key] = true;
        $title = captacion_app_xml_child_value($node, array('title', 'name', 'headline', 'notes', 'titulo', 'heading', 'subject', 'summary', 'nombre'), '');
        $description = captacion_app_xml_desc_value($node) ?: captacion_app_xml_child_value($node, array('description', 'desc', 'remarks', 'text', 'notes', 'comment', 'observations', 'notas'), 'Propiedad importada desde feed XML externo.');
        $type = captacion_app_normalize_property_type(captacion_app_xml_child_value($node, array('property_type', 'type', 'category', 'tipologia', 'tipo'), 'Piso'));
        if (!in_array($type, captacion_app_property_types(), true)) $type = 'Piso';
        $price = captacion_app_xml_number_value(captacion_app_xml_child_value($node, array('price', 'amount', 'value', 'precio', 'preu', 'cost', 'importe', 'pvp'), '0'));
        $surface = captacion_app_xml_number_value(captacion_app_xml_child_value($node, array('surface', 'area', 'built_area', 'size', 'superficie', 'metros', 'm2', 'total_area'), captacion_app_xml_nested_child_value($node, array('surface_area/built', 'surface_area/plot', 'surface_area'), '0')));
        $municipality = captacion_app_xml_child_value($node, array('city', 'municipality', 'town', 'locality', 'ciudad', 'poblacion', 'localidad', 'municipio'), captacion_app_xml_nested_child_value($node, array('location/town', 'location/city', 'location/locality'), ''));
        $province = captacion_app_xml_child_value($node, array('province', 'region', 'state', 'provincia'), captacion_app_xml_nested_child_value($node, array('location/province', 'location/region'), ''));
        if (!$title) $title = trim(($description ? mb_substr(sanitize_text_field($description), 0, 80) : $type) . ($municipality ? ' en ' . $municipality : '') . ($external_id ? ' - Ref. ' . $external_id : ''));
        if (!$title) $title = 'Propiedad importada XML';
        $operation = captacion_app_xml_operation_value(captacion_app_xml_child_value($node, array('operation', 'transaction', 'offer_type', 'operacion', 'tipo_operacion', 'transaction_type'), ''), captacion_app_xml_child_value($node, array('price_freq', 'price_period'), ''));
        $rooms = absint(captacion_app_xml_child_value($node, array('rooms', 'bedrooms', 'beds', 'habitaciones', 'dormitorios', 'room', 'bedroom'), '0'));
        $bathrooms = absint(captacion_app_xml_child_value($node, array('bathrooms', 'baths', 'banos', 'baños', 'bathroom', 'toilets'), '0'));
        $images = captacion_app_xml_image_urls($node);
        $payload = array(
            'id' => $external_id,
            'external_id' => $external_id,
            'title' => $title,
            'description' => sanitize_textarea_field($description),
            'property_type' => $type,
            'type' => $type,
            'operation' => $operation,
            'price' => $price,
            'indicative_price' => $price,
            'currency' => captacion_app_xml_child_value($node, array('currency'), 'EUR'),
            'country' => captacion_app_xml_child_value($node, array('country'), captacion_app_xml_nested_child_value($node, array('location/country'), '')),
            'province' => $province,
            'municipality' => $municipality,
            'address_approx' => captacion_app_xml_child_value($node, array('address', 'street', 'location_detail'), captacion_app_xml_nested_child_value($node, array('location/address'), '')),
            'surface' => $surface,
            'total_area_m2' => $surface,
            'rooms' => $rooms,
            'bedrooms' => $rooms,
            'bathrooms' => $bathrooms,
            'latitude' => captacion_app_xml_nested_child_value($node, array('location/latitude', 'latitude'), ''),
            'longitude' => captacion_app_xml_nested_child_value($node, array('location/longitude', 'longitude'), ''),
            'image' => !empty($images) ? $images[0] : captacion_app_xml_first_image($node),
            'images' => $images,
            'gallery' => $images,
            'source_data' => captacion_app_xml_node_to_array($node),
            'source_email' => captacion_app_xml_child_value($node, array('email', 'contact_email', 'mail'), ''),
            'source_phone' => captacion_app_xml_child_value($node, array('contact_number', 'phone', 'telephone', 'telefono', 'tel'), ''),
            'source_whatsapp' => captacion_app_xml_child_value($node, array('whatsapp_number', 'whatsapp', 'whatsapp_phone'), ''),
            'source_url' => esc_url_raw($source_url),
            'publication_status' => 'active',
            'imported_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );
        $records[] = array('record_type' => 'property', 'record_key' => $record_key, 'title' => $title, 'status' => 'active', 'related_id' => $external_id, 'payload' => $payload);
    }
    return array('schemaVersion' => 'external-url-1.0', 'dataOrigin' => 'xml_url', 'privacyScope' => 'private_user', 'records' => $records, 'total' => count($records));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_xml_feed_import_url(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    if (!$user_id) return new WP_Error('captacion_auth', 'Debes iniciar sesion.', array('status' => 401));
    $download = captacion_app_download_xml_feed_url($request->get_param('url'));
    if (is_wp_error($download)) return $download;
    $parsed = captacion_app_parse_external_xml_properties($download['body'], $download['url']);
    if (is_wp_error($parsed)) return $parsed;
    if (empty($parsed['records'])) return new WP_Error('captacion_xml_no_properties', 'No se detectaron propiedades importables en este XML.', array('status' => 422));
    $source_label = substr($download['url'], 0, 190);
    $existing_batch = captacion_app_get_import_batch_by_source($user_id, 'xml_url', $source_label);
    $batch_id = $existing_batch ? $existing_batch['import_batch_id'] : captacion_app_generate_import_batch_id();
    if (!$existing_batch) {
        $batch_created = captacion_app_create_import_batch(array('import_batch_id' => $batch_id, 'owner_user_id' => $user_id, 'created_by' => $user_id, 'data_origin' => 'xml_url', 'is_demo' => false, 'privacy_scope' => 'private_user', 'source_file_name' => $source_label, 'source_hash' => $download['hash'], 'records_total' => $parsed['total']));
        if (is_wp_error($batch_created)) return $batch_created;
    }
    $result = captacion_app_import_records_from_xml($parsed, array('user_id' => $user_id, 'import_batch_id' => $batch_id, 'data_origin' => 'xml_url', 'is_demo' => false, 'privacy_scope' => 'private_user', 'owner_user_id' => $user_id, 'source_file_name' => $source_label, 'source_hash' => $download['hash']));
    $summary = array_merge($result['summary'], array('properties_updated' => $result['updated'], 'properties_pending_review' => $result['pending_review'], 'technical_errors' => array_slice($result['errors'], 0, 10)));
    captacion_app_update_import_batch_status($batch_id, $result['rejected'] > 0 ? 'error' : 'active', array('records_total' => $parsed['total'], 'records_imported' => $result['imported'] + $result['updated'], 'records_rejected' => $result['rejected'], 'source_hash' => $download['hash'], 'summary_json' => $summary));
    captacion_app_log_resource_event(array('resource_id' => 'xml_feed_import_url'), 'xml_batch_created', array('import_batch_id' => $batch_id, 'owner_user_id' => $user_id, 'source' => $source_label));
    return rest_ensure_response(array('ok' => true, 'import_batch_id' => $batch_id, 'imported' => $result['imported'], 'updated' => $result['updated'], 'pending_review' => $result['pending_review'], 'rejected' => $result['rejected'], 'summary' => $summary));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_parse_file_for_import($raw, $source_name) {
    $extension = strtolower(pathinfo((string) $source_name, PATHINFO_EXTENSION));
    if ($extension === 'csv') return captacion_app_parse_csv_properties($raw, $source_name);
    if ($extension === 'json') return captacion_app_parse_json_properties($raw, $source_name);
    if (stripos($raw, '<captacionData') !== false) {
        $parsed = captacion_app_validate_import_xml($raw);
        if (!is_wp_error($parsed)) return $parsed;
    }
    return captacion_app_parse_external_xml_properties($raw, $source_name);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_parse_xml_file_for_import($raw_xml, $source_name) {
    return captacion_app_parse_file_for_import($raw_xml, $source_name);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_xml_feed_import_file(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    if (!$user_id) return new WP_Error('captacion_auth', 'Tu sesion ha caducado. Vuelve a iniciar sesion.', array('status' => 401));
    $files = $request->get_file_params();
    if (empty($files['file']) || !is_array($files['file'])) {
        return new WP_Error('captacion_import_file_required', 'Selecciona un archivo XML, CSV o JSON.', array('status' => 422));
    }
    $file = $files['file'];
    if (!empty($file['error'])) {
        return new WP_Error('captacion_import_file_upload', 'No se pudo subir el archivo.', array('status' => 400));
    }
    $filename = sanitize_file_name($file['name'] ?? 'feed.xml');
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    if (!in_array($extension, array('xml', 'csv', 'json'), true)) {
        return new WP_Error('captacion_import_file_extension', 'El archivo debe tener extension .xml, .csv o .json.', array('status' => 422));
    }
    $allowed_mimes = array('text/xml', 'application/xml', 'application/json', 'text/json', 'text/csv', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream', 'text/plain');
    $mime = sanitize_text_field($file['type'] ?? '');
    if ($mime && !in_array($mime, $allowed_mimes, true)) {
        return new WP_Error('captacion_import_file_mime', 'El tipo de archivo no esta permitido.', array('status' => 422));
    }
    if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return new WP_Error('captacion_import_file_tmp', 'No se pudo leer el archivo subido.', array('status' => 400));
    }
    if (filesize($file['tmp_name']) > CAPTACION_XML_MAX_SIZE) {
        return new WP_Error('captacion_import_file_size', 'El archivo supera el tamano maximo permitido.', array('status' => 413));
    }
    $raw = file_get_contents($file['tmp_name']);
    if (!$raw || trim($raw) === '') {
        return new WP_Error('captacion_import_file_empty', 'El archivo no contiene datos validos.', array('status' => 400));
    }
    if ($extension === 'xml' && (stripos($raw, '<!DOCTYPE') !== false || stripos($raw, '<!ENTITY') !== false)) {
        return new WP_Error('captacion_xml_file_doctype', 'El archivo XML contiene elementos no permitidos.', array('status' => 400));
    }
    require_once ABSPATH . 'wp-admin/includes/file.php';
    add_filter('upload_dir', 'captacion_app_import_upload_dir_filter');
    $upload = wp_handle_upload($file, array(
        'test_form' => false,
        'mimes' => array('xml' => 'text/xml|application/xml|text/plain', 'csv' => 'text/csv|application/csv|application/vnd.ms-excel|text/plain', 'json' => 'application/json|text/json|text/plain'),
    ));
    remove_filter('upload_dir', 'captacion_app_import_upload_dir_filter');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $parsed = captacion_app_parse_file_for_import($raw, $filename);
    if (is_wp_error($parsed)) return $parsed;
    if (empty($parsed['records'])) {
        return new WP_Error('captacion_xml_no_properties', 'No se han detectado propiedades compatibles.', array('status' => 422));
    }
    $hash = hash('sha256', $raw);
    $data_origin = $extension . '_file';
    $batch_id = $extension . '_local_' . gmdate('Ymd_His') . '_' . substr($hash, 0, 8);
    $stored_name = basename($upload['file'] ?? $filename);
    $batch_created = captacion_app_create_import_batch(array('import_batch_id' => $batch_id, 'owner_user_id' => $user_id, 'created_by' => $user_id, 'data_origin' => $data_origin, 'is_demo' => false, 'privacy_scope' => 'private_user', 'source_file_name' => substr($stored_name, 0, 190), 'source_hash' => $hash, 'records_total' => $parsed['total']));
    if (is_wp_error($batch_created)) return $batch_created;
    $result = captacion_app_import_records_from_xml($parsed, array('user_id' => $user_id, 'import_batch_id' => $batch_id, 'data_origin' => $data_origin, 'is_demo' => false, 'privacy_scope' => 'private_user', 'owner_user_id' => $user_id, 'source_file_name' => substr($stored_name, 0, 190), 'source_hash' => $hash));
    $summary = array_merge($result['summary'], array('source_file_path' => $upload['file'] ?? '', 'source_file_url' => $upload['url'] ?? '', 'properties_updated' => $result['updated'], 'properties_pending_review' => $result['pending_review'], 'technical_errors' => array_slice($result['errors'], 0, 10)));
    captacion_app_update_import_batch_status($batch_id, $result['rejected'] > 0 ? 'error' : 'active', array('records_imported' => $result['imported'] + $result['updated'], 'records_rejected' => $result['rejected'], 'summary_json' => $summary));
    captacion_app_log_resource_event(array('resource_id' => 'import_file'), 'import_batch_created', array('import_batch_id' => $batch_id, 'owner_user_id' => $user_id, 'source' => $filename, 'format' => $extension));
    return rest_ensure_response(array(
        'success' => true,
        'ok' => true,
        'feed_id' => $batch_id,
        'import_batch_id' => $batch_id,
        'filename' => $filename,
        'format' => $extension,
        'properties_imported' => $result['imported'],
        'properties_updated' => $result['updated'],
        'properties_pending_review' => $result['pending_review'],
        'properties_failed' => $result['rejected'],
        'properties_incomplete' => $result['pending_review'],
        'status' => $result['rejected'] > 0 ? 'error' : 'active',
        'summary' => $summary,
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_xml_feed_sync(WP_REST_Request $request) {
    $batch_id = sanitize_text_field($request->get_param('import_batch_id'));
    $batch = captacion_app_get_import_batch($batch_id);
    if (!$batch || !empty($batch['deleted_at'])) return new WP_Error('captacion_batch_not_found', 'Lote no encontrado.', array('status' => 404));
    if (!captacion_app_user_can_manage_import_batch($batch)) return new WP_Error('captacion_forbidden', 'No tienes permiso para sincronizar este XML.', array('status' => 403));
    if ($batch['data_origin'] !== 'xml_url' || empty($batch['source_file_name'])) return new WP_Error('captacion_batch_not_url', 'Este lote no procede de una URL XML sincronizable.', array('status' => 400));
    $download = captacion_app_download_xml_feed_url($batch['source_file_name']);
    if (is_wp_error($download)) return $download;
    $parsed = captacion_app_parse_external_xml_properties($download['body'], $batch['source_file_name']);
    if (is_wp_error($parsed)) return $parsed;
    global $wpdb;
    $records_table = captacion_app_records_table_name();
    $blockers = captacion_app_get_import_batch_pending_blockers($batch_id);
    if (empty($blockers['count'])) {
        $wpdb->query($wpdb->prepare("DELETE FROM {$records_table} WHERE import_batch_id = %s", $batch_id));
    }
    $result = captacion_app_import_records_from_xml($parsed, array('user_id' => absint($batch['owner_user_id']), 'import_batch_id' => $batch_id, 'data_origin' => 'xml_url', 'is_demo' => false, 'privacy_scope' => 'private_user', 'owner_user_id' => absint($batch['owner_user_id']), 'source_file_name' => $batch['source_file_name'], 'source_hash' => $download['hash']));
    captacion_app_update_import_batch_status($batch_id, $result['rejected'] > 0 ? 'error' : 'active', array('records_total' => $parsed['total'], 'records_imported' => $result['imported'] + $result['updated'], 'records_rejected' => $result['rejected'], 'source_hash' => $download['hash'], 'summary_json' => $result['summary']));
    return rest_ensure_response(array('ok' => true, 'import_batch_id' => $batch_id, 'imported' => $result['imported'], 'updated' => $result['updated'], 'rejected' => $result['rejected']));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_import_rollback(WP_REST_Request $request) {
    global $wpdb;
    $batch_id = sanitize_text_field($request->get_param('import_batch_id'));
    $batch = captacion_app_get_import_batch($batch_id);
    if (!$batch || !empty($batch['deleted_at'])) return new WP_Error('captacion_batch_not_found', 'Lote no encontrado.', array('status' => 404));
    if (!captacion_app_user_can_manage_import_batch($batch)) return new WP_Error('captacion_forbidden', 'No tienes permiso para revertir este lote.', array('status' => 403));
    $records_table = captacion_app_records_table_name();
    $now = current_time('mysql');
    $affected = $wpdb->query($wpdb->prepare("UPDATE {$records_table} SET deleted_at = %s, status = %s, updated_at = %s WHERE import_batch_id = %s AND deleted_at IS NULL", $now, 'rolled_back', $now, $batch_id));
    $summary = json_decode((string) ($batch['summary_json'] ?? '{}'), true);
    if (!is_array($summary)) $summary = array();
    $summary['rolled_back_records'] = max(0, (int) $affected);
    $summary['rolled_back_at'] = $now;
    captacion_app_update_import_batch_status($batch_id, 'rolled_back', array('summary_json' => $summary));
    captacion_app_log_resource_event(array('resource_id' => 'import_rollback'), 'import_batch_rolled_back', array('import_batch_id' => $batch_id, 'owner_user_id' => absint($batch['owner_user_id']), 'records' => max(0, (int) $affected)));
    return rest_ensure_response(array('ok' => true, 'import_batch_id' => $batch_id, 'rolled_back' => max(0, (int) $affected)));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_import_template() {
    $headers = array('referencia','titulo','tipo','operacion','precio','moneda','provincia','municipio','codigo_postal','superficie','habitaciones','banos','descripcion','imagen');
    $example = array('REF-001','Piso luminoso en zona centro','Piso','venta','250000','EUR','Madrid','Madrid','28013','95','3','2','Descripcion comercial de la oportunidad','https://ejemplo.com/foto.jpg');
    $csv = implode(';', $headers) . "\n" . implode(';', array_map(static function ($value) { return '"' . str_replace('"', '""', $value) . '"'; }, $example)) . "\n";
    $response = new WP_REST_Response($csv, 200);
    $response->header('Content-Type', 'text/csv; charset=utf-8');
    $response->header('Content-Disposition', 'attachment; filename="captacion-import-template.csv"');
    return $response;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_webhook_permission(WP_REST_Request $request) {
    $expected = (string) captacion_app_setting('webhook_api_key');
    if ($expected === '') return false;
    $provided = (string) $request->get_header('x-captacion-webhook-key');
    return $provided !== '' && hash_equals($expected, $provided);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_webhook_receive(WP_REST_Request $request) {
    $body = (string) $request->get_body();
    if (trim($body) === '') return new WP_Error('captacion_webhook_empty', 'El webhook no contiene datos.', array('status' => 400));
    $owner_user_id = absint($request->get_param('owner_user_id'));
    $owner_email = sanitize_email((string) $request->get_param('owner_email'));
    if (!$owner_user_id && $owner_email) {
        $user = get_user_by('email', $owner_email);
        if ($user) $owner_user_id = absint($user->ID);
    }
    if (!$owner_user_id || !get_userdata($owner_user_id)) return new WP_Error('captacion_webhook_owner', 'Indica owner_user_id u owner_email valido.', array('status' => 422));
    $content_type = strtolower((string) $request->get_header('content-type'));
    $source_name = 'webhook-' . gmdate('Ymd-His');
    if (strpos($content_type, 'xml') !== false || strpos(ltrim($body), '<') === 0) {
        if (stripos($body, '<!DOCTYPE') !== false || stripos($body, '<!ENTITY') !== false) return new WP_Error('captacion_webhook_xml_doctype', 'XML no permitido.', array('status' => 400));
        $parsed = captacion_app_parse_external_xml_properties($body, $source_name);
    } else {
        $parsed = captacion_app_parse_json_properties($body, $source_name . '.json');
    }
    if (is_wp_error($parsed)) return $parsed;
    if (empty($parsed['records'])) return new WP_Error('captacion_webhook_empty_records', 'No se detectaron propiedades importables.', array('status' => 422));
    $hash = hash('sha256', $body);
    $batch_id = 'webhook_' . gmdate('Ymd_His') . '_' . substr($hash, 0, 8);
    $batch_created = captacion_app_create_import_batch(array('import_batch_id' => $batch_id, 'owner_user_id' => $owner_user_id, 'created_by' => $owner_user_id, 'data_origin' => 'webhook', 'is_demo' => false, 'privacy_scope' => 'private_user', 'source_file_name' => $source_name, 'source_hash' => $hash, 'records_total' => $parsed['total']));
    if (is_wp_error($batch_created)) return $batch_created;
    $result = captacion_app_import_records_from_xml($parsed, array('user_id' => $owner_user_id, 'import_batch_id' => $batch_id, 'data_origin' => 'webhook', 'is_demo' => false, 'privacy_scope' => 'private_user', 'owner_user_id' => $owner_user_id, 'source_file_name' => $source_name, 'source_hash' => $hash));
    $summary = array_merge($result['summary'], array('properties_updated' => $result['updated'], 'properties_pending_review' => $result['pending_review'], 'technical_errors' => array_slice($result['errors'], 0, 10)));
    captacion_app_update_import_batch_status($batch_id, $result['rejected'] > 0 ? 'error' : 'active', array('records_imported' => $result['imported'] + $result['updated'], 'records_rejected' => $result['rejected'], 'summary_json' => $summary));
    return rest_ensure_response(array('ok' => true, 'import_batch_id' => $batch_id, 'imported' => $result['imported'], 'updated' => $result['updated'], 'pending_review' => $result['pending_review'], 'rejected' => $result['rejected'], 'summary' => $summary));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_xml_demo_import(WP_REST_Request $request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('captacion_forbidden', 'Solo administradores.', array('status' => 403));
    }
    $raw_xml = $request->get_body();
    $parsed = captacion_app_validate_import_xml($raw_xml);
    if (is_wp_error($parsed)) return $parsed;
    $hash = hash('sha256', $raw_xml);
    $batch_id = captacion_app_generate_import_batch_id();
    $batch_row_id = captacion_app_create_import_batch(array(
        'import_batch_id' => $batch_id,
        'owner_user_id' => CAPTACION_DEMO_OWNER_USER_ID,
        'created_by' => get_current_user_id(),
        'data_origin' => 'demo_xml',
        'is_demo' => true,
        'privacy_scope' => 'global_demo',
        'source_file_name' => 'demo-import.xml',
        'source_hash' => $hash,
        'records_total' => $parsed['total'],
    ));
    $result = captacion_app_import_records_from_xml($parsed, array(
        'user_id' => CAPTACION_DEMO_OWNER_USER_ID,
        'import_batch_id' => $batch_id,
        'data_origin' => 'demo_xml',
        'is_demo' => true,
        'privacy_scope' => 'global_demo',
        'owner_user_id' => CAPTACION_DEMO_OWNER_USER_ID,
        'source_file_name' => 'demo-import.xml',
        'source_hash' => $hash,
    ));
    captacion_app_update_import_batch_status($batch_id, 'active', array(
        'records_imported' => $result['imported'],
        'records_rejected' => $result['rejected'],
        'summary_json' => $result['summary'],
    ));
    captacion_app_log_resource_event(array('resource_id' => 'xml_demo_import'), 'xml_import_completed', array(
        'import_batch_id' => $batch_id,
        'imported' => $result['imported'],
        'rejected' => $result['rejected'],
    ));
    return rest_ensure_response(array(
        'ok' => true,
        'import_batch_id' => $batch_id,
        'imported' => $result['imported'],
        'rejected' => $result['rejected'],
        'summary' => $result['summary'],
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_xml_user_import(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('captacion_auth', 'Debes iniciar sesión.', array('status' => 401));
    }
    $raw_xml = $request->get_body();
    $parsed = captacion_app_validate_import_xml($raw_xml);
    if (is_wp_error($parsed)) return $parsed;
    $hash = hash('sha256', $raw_xml);
    $batch_id = captacion_app_generate_import_batch_id();
    $batch_row_id = captacion_app_create_import_batch(array(
        'import_batch_id' => $batch_id,
        'owner_user_id' => $user_id,
        'created_by' => $user_id,
        'data_origin' => 'user_xml',
        'is_demo' => false,
        'privacy_scope' => 'private_user',
        'source_file_name' => sanitize_text_field($request->get_header('X-Filename') ?: 'user-import.xml'),
        'source_hash' => $hash,
        'records_total' => $parsed['total'],
    ));
    $result = captacion_app_import_records_from_xml($parsed, array(
        'user_id' => $user_id,
        'import_batch_id' => $batch_id,
        'data_origin' => 'user_xml',
        'is_demo' => false,
        'privacy_scope' => 'private_user',
        'owner_user_id' => $user_id,
        'source_file_name' => sanitize_text_field($request->get_header('X-Filename') ?: 'user-import.xml'),
        'source_hash' => $hash,
    ));
    captacion_app_update_import_batch_status($batch_id, 'active', array(
        'records_imported' => $result['imported'],
        'records_rejected' => $result['rejected'],
        'summary_json' => $result['summary'],
    ));
    captacion_app_log_resource_event(array('resource_id' => 'xml_user_import'), 'xml_import_completed', array(
        'import_batch_id' => $batch_id,
        'imported' => $result['imported'],
        'rejected' => $result['rejected'],
    ));
    return rest_ensure_response(array(
        'ok' => true,
        'import_batch_id' => $batch_id,
        'imported' => $result['imported'],
        'rejected' => $result['rejected'],
        'summary' => $result['summary'],
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_xml_user_export(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('captacion_auth', 'Debes iniciar sesión.', array('status' => 401));
    }
    global $wpdb;
    $table = captacion_app_records_table_name();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE owner_user_id = %d AND deleted_at IS NULL ORDER BY record_type, created_at ASC",
        $user_id
    ), ARRAY_A);
    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;
    $root = $dom->createElement('captacionData');
    $root->setAttribute('schemaVersion', '1.0');
    $root->setAttribute('dataOrigin', 'user_export');
    $root->setAttribute('privacyScope', 'private_user');
    $root->setAttribute('isDemo', 'false');
    $root->setAttribute('generatedAt', current_time('c'));
    $root->setAttribute('exportUserId', (string) $user_id);
    foreach ($rows as $row) {
        $el = $dom->createElement($row['record_type']);
        $el->setAttribute('recordKey', $row['record_key']);
        if (!empty($row['title'])) $el->setAttribute('title', $row['title']);
        if (!empty($row['status'])) $el->setAttribute('status', $row['status']);
        if (!empty($row['related_id'])) $el->setAttribute('relatedId', $row['related_id']);
        $payload = json_decode($row['payload'], true);
        if (is_array($payload)) {
            foreach ($payload as $k => $v) {
                if (is_scalar($v)) {
                    $field = $dom->createElement(sanitize_key($k), htmlspecialchars((string) $v, ENT_XML1, 'UTF-8'));
                    $el->appendChild($field);
                }
            }
        }
        $root->appendChild($el);
    }
    $summary = $dom->createElement('summary');
    $total = $dom->createElement('totalRecords', (string) count($rows));
    $summary->appendChild($total);
    $types = array_count_values(array_column($rows, 'record_type'));
    foreach ($types as $type => $count) {
        $te = $dom->createElement($type, (string) $count);
        $summary->appendChild($te);
    }
    $root->appendChild($summary);
    $dom->appendChild($root);
    $xml_content = $dom->saveXML();
    $hash_attr = $dom->createAttribute('hash');
    $hash_attr->value = hash('sha256', $xml_content);
    $root->appendChild($hash_attr);
    $xml_content = $dom->saveXML();
    captacion_app_log_resource_event(array('resource_id' => 'xml_user_export'), 'user_private_data_exported', array(
        'owner_user_id' => $user_id,
        'total_records' => count($rows),
    ));
    return rest_ensure_response(array(
        'ok' => true,
        'xml' => $xml_content,
        'filename' => 'captacion-app-export-' . $user_id . '.xml',
        'total_records' => count($rows),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_delete_import_batch(WP_REST_Request $request) {
    $batch_id = sanitize_text_field($request->get_param('import_batch_id') ?: $request->get_param('feed_id'));
    $confirm = sanitize_text_field($request->get_param('confirm') ?? '');
    if ($confirm !== 'CONFIRMAR') {
        return new WP_Error('captacion_confirm_required', 'Debes enviar confirm=CONFIRMAR para eliminar.', array('status' => 400));
    }
    $batch = captacion_app_get_import_batch($batch_id);
    if (!$batch || !empty($batch['deleted_at'])) {
        return new WP_Error('captacion_batch_not_found', 'Lote no encontrado.', array('status' => 404));
    }
    if (!captacion_app_user_can_manage_import_batch($batch)) {
        return new WP_Error('captacion_forbidden', 'No tienes permiso para eliminar este lote.', array('status' => 403));
    }
    captacion_app_log_resource_event(array('resource_id' => 'import_batch_delete'), 'xml_batch_deletion_requested', array(
        'import_batch_id' => $batch_id,
        'owner_user_id' => $batch['owner_user_id'],
        'current_status' => $batch['status'] ?? '',
    ));
    if (($batch['status'] ?? '') === 'pending_deletion') {
        $completed = captacion_app_complete_pending_feed_deletions(absint($batch['owner_user_id']));
        return rest_ensure_response(array(
            'success' => true,
            'ok' => true,
            'feed_id' => $batch_id,
            'import_batch_id' => $batch_id,
            'status' => in_array($batch_id, $completed, true) ? 'deleted' : 'pending_deletion',
            'message' => in_array($batch_id, $completed, true) ? 'XML eliminado correctamente' : 'El XML sigue pendiente de eliminación hasta cerrar sus procesos activos.',
        ));
    }
    $blockers = captacion_app_get_import_batch_pending_blockers($batch_id);
    if (!empty($blockers['count'])) {
        captacion_app_log_resource_event(array('resource_id' => 'import_batch_delete'), 'xml_batch_deletion_pending', array(
            'import_batch_id' => $batch_id,
            'owner_user_id' => $batch['owner_user_id'],
            'blockers' => $blockers,
        ));
        return rest_ensure_response(array(
            'success' => true,
            'ok' => true,
            'feed_id' => $batch_id,
            'import_batch_id' => $batch_id,
            'status' => $batch['status'] ?? 'active',
            'blocked' => true,
            'blockers' => $blockers,
            'message' => 'No se puede eliminar este XML hasta cerrar o descartar las operaciones activas vinculadas.',
        ));
    }
    captacion_app_hard_delete_import_batch_records($batch_id);
    captacion_app_hard_delete_import_batch($batch_id);
    captacion_app_log_resource_event(array('resource_id' => 'import_batch_delete'), 'xml_batch_deleted', array(
        'import_batch_id' => $batch_id,
        'owner_user_id' => $batch['owner_user_id'],
    ));
    return rest_ensure_response(array(
        'success' => true,
        'ok' => true,
        'feed_id' => $batch_id,
        'import_batch_id' => $batch_id,
        'status' => 'deleted',
        'message' => 'XML eliminado correctamente',
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_update_import_batch(WP_REST_Request $request) {
    $batch_id = sanitize_text_field($request->get_param('import_batch_id') ?: $request->get_param('feed_id'));
    $status = sanitize_key($request->get_param('status') ?? '');
    if (!in_array($status, array('active', 'paused'), true)) {
        return new WP_Error('captacion_batch_status', 'Estado de lote no permitido.', array('status' => 400));
    }
    $batch = captacion_app_get_import_batch($batch_id);
    if (!$batch || !empty($batch['deleted_at'])) {
        return new WP_Error('captacion_batch_not_found', 'Lote no encontrado.', array('status' => 404));
    }
    if (!captacion_app_user_can_manage_import_batch($batch)) {
        return new WP_Error('captacion_forbidden', 'No tienes permiso para modificar este lote.', array('status' => 403));
    }
    global $wpdb;
    $batches_table = captacion_app_import_batches_table_name();
    $wpdb->update($batches_table, array('status' => $status, 'updated_at' => current_time('mysql')), array('import_batch_id' => $batch_id));
    captacion_app_log_resource_event(array('resource_id' => 'import_batch_status'), $status === 'paused' ? 'xml_batch_paused' : 'xml_batch_reactivated', array(
        'import_batch_id' => $batch_id,
        'owner_user_id' => $batch['owner_user_id'],
    ));
    return rest_ensure_response(array('ok' => true, 'import_batch_id' => $batch_id, 'status' => $status));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_feed_pending_properties(WP_REST_Request $request) {
    $feed_id = sanitize_text_field($request->get_param('feed_id'));
    $batch = captacion_app_get_import_batch($feed_id);
    if (!$batch || !empty($batch['deleted_at'])) return new WP_Error('captacion_feed_not_found', 'Feed no encontrado.', array('status' => 404));
    if (!captacion_app_user_can_manage_import_batch($batch)) return new WP_Error('captacion_forbidden', 'No tienes permiso.', array('status' => 403));
    global $wpdb;
    $table = captacion_app_records_table_name();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT id, record_key, title, payload, status FROM {$table} WHERE import_batch_id = %s AND record_type = 'property' AND status = 'pending_review' AND deleted_at IS NULL ORDER BY id ASC LIMIT 1000",
        $feed_id
    ), ARRAY_A);
    $properties = array();
    foreach ($rows as $row) {
        $payload = json_decode($row['payload'] ?: '{}', true);
        $properties[] = array(
            'id' => absint($row['id']),
            'record_key' => $row['record_key'],
            'title' => sanitize_text_field($row['title']),
            'external_id' => sanitize_text_field($payload['external_id'] ?? $payload['id'] ?? ''),
            'property_type' => sanitize_text_field($payload['property_type'] ?? $payload['type'] ?? ''),
            'type' => sanitize_text_field($payload['type'] ?? $payload['property_type'] ?? ''),
            'price' => isset($payload['price']) ? (float) $payload['price'] : 0,
            'province' => sanitize_text_field($payload['province'] ?? ''),
            'municipality' => sanitize_text_field($payload['municipality'] ?? $payload['city'] ?? ''),
            'status' => sanitize_text_field($row['status']),
            'missing_fields' => is_array($payload['missing_fields'] ?? null) ? $payload['missing_fields'] : array(),
            'payload' => $payload,
        );
    }
    return rest_ensure_response(array('ok' => true, 'feed_id' => $feed_id, 'total' => count($properties), 'properties' => $properties));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_update_pending_property(WP_REST_Request $request) {
    $feed_id = sanitize_text_field($request->get_param('feed_id'));
    $record_key = sanitize_text_field($request->get_param('record_key'));
    $batch = captacion_app_get_import_batch($feed_id);
    if (!$batch || !empty($batch['deleted_at'])) return new WP_Error('captacion_feed_not_found', 'Feed no encontrado.', array('status' => 404));
    if (!captacion_app_user_can_manage_import_batch($batch)) return new WP_Error('captacion_forbidden', 'No tienes permiso.', array('status' => 403));
    global $wpdb;
    $table = captacion_app_records_table_name();
    $row = $wpdb->get_row($wpdb->prepare(
        "SELECT id, payload, status FROM {$table} WHERE record_key = %s AND import_batch_id = %s AND record_type = 'property' AND deleted_at IS NULL",
        $record_key, $feed_id
    ), ARRAY_A);
    if (!$row) return new WP_Error('captacion_property_not_found', 'Propiedad no encontrada.', array('status' => 404));
    $payload = json_decode($row['payload'] ?: '{}', true);
    $field = sanitize_key($request->get_param('field'));
    $value = sanitize_text_field($request->get_param('value'));
    $fields = $request->get_param('fields');
    if (!$field && !is_array($fields)) return new WP_Error('captacion_field_required', 'Indica el campo a actualizar.', array('status' => 400));
    if ($field === '_publish') {
        $payload['publication_status'] = 'active';
        $payload['status'] = 'active';
        $wpdb->update($table, array('payload' => wp_json_encode($payload), 'status' => 'active', 'updated_at' => current_time('mysql')), array('id' => absint($row['id'])));
        return rest_ensure_response(array('ok' => true, 'record_key' => $record_key, 'status' => 'active', 'missing_fields' => $payload['missing_fields'] ?? array()));
    }
    $field_map = array(
        'title' => 'title',
        'type' => 'property_type',
        'operation' => 'operation',
        'price' => 'price',
        'currency' => 'currency',
        'location' => null,
        'description' => 'description',
        'owner' => null,
        'rooms' => 'rooms',
        'bathrooms' => 'bathrooms',
        'surface' => 'surface',
    );
    if (is_array($fields)) {
        foreach ($fields as $field_key => $field_value) {
            $field_key = sanitize_key($field_key);
            $field_value = is_scalar($field_value) ? sanitize_text_field((string) $field_value) : '';
            if ($field_key === 'location') {
                $parts = array_map('trim', explode(',', $field_value));
                $payload['province'] = $parts[0] ?? '';
                $payload['municipality'] = $parts[1] ?? $parts[0] ?? '';
            } elseif ($field_key === 'owner') {
                $payload['owner_name'] = $field_value;
                $payload['contact_email'] = $field_value;
            } elseif (isset($field_map[$field_key])) {
                $payload[$field_map[$field_key]] = $field_value;
            }
        }
    } elseif ($field === 'location') {
        $parts = array_map('trim', explode(',', $value));
        $payload['province'] = $parts[0] ?? '';
        $payload['municipality'] = $parts[1] ?? $parts[0] ?? '';
    } elseif ($field === 'owner') {
        $payload['owner_name'] = $value;
        $payload['contact_email'] = $value;
    } elseif (isset($field_map[$field])) {
        $payload[$field_map[$field]] = $value;
    }
    $missing = captacion_app_property_marketplace_missing_fields($payload);
    $payload['missing_fields'] = $missing;
    $payload['review_alerts'] = $missing;
    $current_status = $row['status'] ?? 'pending_review';
    $new_status = ($current_status === 'active') ? 'active' : (empty($missing) ? 'active' : 'pending_review');
    $update_row = array('payload' => wp_json_encode($payload), 'status' => $new_status, 'updated_at' => current_time('mysql'));
    if (!empty($payload['title'])) $update_row['title'] = sanitize_text_field($payload['title']);
    $wpdb->update($table, $update_row, array('id' => absint($row['id'])));
    return rest_ensure_response(array('ok' => true, 'record_key' => $record_key, 'status' => $new_status, 'missing_fields' => $missing));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_publish_all_pending(WP_REST_Request $request) {
    $feed_id = sanitize_text_field($request->get_param('feed_id'));
    $body = $request->get_json_params();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $batch = captacion_app_get_import_batch($feed_id);
    if (!$batch || !empty($batch['deleted_at'])) return new WP_Error('captacion_feed_not_found', 'Feed no encontrado.', array('status' => 404));
    if (!captacion_app_user_can_manage_import_batch($batch)) return new WP_Error('captacion_forbidden', 'No tienes permiso.', array('status' => 403));
    global $wpdb;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($keys !== null) {
        if (empty($keys)) {
            return rest_ensure_response(array('ok' => true, 'feed_id' => $feed_id, 'published_properties' => 0));
        }
        $placeholders = implode(',', array_fill(0, count($keys), '%s'));
        $sql = $wpdb->prepare(
            "SELECT id, payload FROM {$table} WHERE import_batch_id = %s AND record_type = 'property' AND status = 'pending_review' AND record_key IN ($placeholders) AND deleted_at IS NULL",
            array_merge(array($feed_id), $keys)
        );
    } else {
        $sql = $wpdb->prepare(
            "SELECT id, payload FROM {$table} WHERE import_batch_id = %s AND record_type = 'property' AND status = 'pending_review' AND deleted_at IS NULL",
            $feed_id
        );
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $rows = $wpdb->get_results($sql, ARRAY_A);
    $updated = 0;
    $requested = is_array($keys) ? count($keys) : count($rows);
    foreach ($rows as $row) {
        $payload = json_decode($row['payload'] ?: '{}', true);
        $payload['publication_status'] = 'active';
        $payload['status'] = 'active';
        $wpdb->update($table, array('payload' => wp_json_encode($payload), 'status' => 'active', 'updated_at' => current_time('mysql')), array('id' => absint($row['id'])));
        $updated++;
    }
    return rest_ensure_response(array('ok' => true, 'feed_id' => $feed_id, 'requested_properties' => $requested, 'published_properties' => $updated, 'not_found_properties' => max(0, $requested - $updated)));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_list_import_batches(WP_REST_Request $request) {
    global $wpdb;
    $batches_table = captacion_app_import_batches_table_name();
    $records_table = captacion_app_records_table_name();
    $user_id = get_current_user_id();
    captacion_app_complete_pending_feed_deletions(current_user_can('manage_options') ? 0 : $user_id);
    if (current_user_can('manage_options')) {
        $rows = $wpdb->get_results("SELECT * FROM {$batches_table} WHERE deleted_at IS NULL AND (status IS NULL OR status != 'deleted') ORDER BY created_at DESC LIMIT 100", ARRAY_A);
    } else {
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$batches_table} WHERE owner_user_id = %d AND deleted_at IS NULL AND (status IS NULL OR status != 'deleted') ORDER BY created_at DESC LIMIT 100",
            $user_id
        ), ARRAY_A);
    }
    $existing_source_keys = array();
    foreach ($rows as $candidate_row) {
        if (!empty($candidate_row['source_file_name'])) {
            $existing_source_keys[absint($candidate_row['owner_user_id']) . '|' . sanitize_key($candidate_row['data_origin']) . '|' . sanitize_text_field($candidate_row['source_file_name'])] = true;
        }
    }
    if (current_user_can('manage_options')) {
        $record_sources = $wpdb->get_results(
            "SELECT owner_user_id, created_by, data_origin, source_file_name, source_hash, import_batch_id, MIN(created_at) created_at, MAX(updated_at) updated_at, COUNT(*) records_total FROM {$records_table} WHERE deleted_at IS NULL AND source_file_name != '' AND data_origin IN ('xml_url','xml_file','csv_file','json_file','webhook') GROUP BY owner_user_id, data_origin, source_file_name ORDER BY updated_at DESC LIMIT 100",
            ARRAY_A
        );
    } else {
        $record_sources = $wpdb->get_results($wpdb->prepare(
            "SELECT owner_user_id, created_by, data_origin, source_file_name, source_hash, import_batch_id, MIN(created_at) created_at, MAX(updated_at) updated_at, COUNT(*) records_total FROM {$records_table} WHERE owner_user_id = %d AND deleted_at IS NULL AND source_file_name != '' AND data_origin IN ('xml_url','xml_file','csv_file','json_file','webhook') GROUP BY owner_user_id, data_origin, source_file_name ORDER BY updated_at DESC LIMIT 100",
            $user_id
        ), ARRAY_A);
    }
    foreach ($record_sources as $record_source) {
        $source_key = absint($record_source['owner_user_id']) . '|' . sanitize_key($record_source['data_origin']) . '|' . sanitize_text_field($record_source['source_file_name']);
        if (isset($existing_source_keys[$source_key])) continue;
        $recovered_batch_id = sanitize_text_field($record_source['import_batch_id'] ?: 'xml_recovered_' . substr(md5($source_key), 0, 16));
        $batch_created = captacion_app_create_import_batch(array(
            'import_batch_id' => $recovered_batch_id,
            'owner_user_id' => absint($record_source['owner_user_id']),
            'created_by' => absint($record_source['created_by']),
            'data_origin' => sanitize_key($record_source['data_origin']),
            'is_demo' => false,
            'privacy_scope' => 'private_user',
            'source_file_name' => sanitize_text_field($record_source['source_file_name']),
            'source_hash' => sanitize_text_field($record_source['source_hash']),
            'records_total' => absint($record_source['records_total']),
            'summary_json' => array('recovered_from_records' => true, 'totalRecords' => absint($record_source['records_total'])),
        ));
        if (!is_wp_error($batch_created)) {
            captacion_app_update_import_batch_status($recovered_batch_id, 'active', array('records_total' => absint($record_source['records_total']), 'records_imported' => absint($record_source['records_total']), 'records_rejected' => 0));
            $recovered_row = captacion_app_get_import_batch($recovered_batch_id);
            if ($recovered_row) $rows[] = $recovered_row;
        }
        $existing_source_keys[$source_key] = true;
    }
    $unique_rows = array();
    $seen_sources = array();
    foreach ($rows as $candidate_row) {
        $source_key = absint($candidate_row['owner_user_id']) . '|' . sanitize_key($candidate_row['data_origin']) . '|' . sanitize_text_field($candidate_row['source_file_name']);
        if (!empty($candidate_row['source_file_name']) && isset($seen_sources[$source_key])) continue;
        if (!empty($candidate_row['source_file_name'])) $seen_sources[$source_key] = true;
        $unique_rows[] = $candidate_row;
    }
    $rows = $unique_rows;
    foreach ($rows as &$row) {
        $counts = $wpdb->get_results($wpdb->prepare(
            "SELECT record_type, COUNT(*) total FROM {$records_table} WHERE import_batch_id = %s AND deleted_at IS NULL GROUP BY record_type",
            $row['import_batch_id']
        ), ARRAY_A);
        $row['properties_count'] = 0;
        $row['needs_count'] = 0;
        $row['active_properties_count'] = 0;
        $row['pending_review_properties_count'] = 0;
        $row['pending_blockers_count'] = 0;
        $row['report'] = json_decode($row['summary_json'] ?: '{}', true);
        if (($row['status'] ?? '') === 'pending_deletion') {
            $blockers = captacion_app_get_import_batch_pending_blockers($row['import_batch_id']);
            $row['pending_blockers_count'] = absint($blockers['count'] ?? 0);
            $row['pending_blockers'] = $blockers['items'] ?? array();
        }
        foreach ($counts as $count_row) {
            if ($count_row['record_type'] === 'property') $row['properties_count'] = absint($count_row['total']);
            if ($count_row['record_type'] === 'need') $row['needs_count'] = absint($count_row['total']);
        }
        $status_counts = $wpdb->get_results($wpdb->prepare(
            "SELECT status, COUNT(*) total FROM {$records_table} WHERE import_batch_id = %s AND record_type = 'property' AND deleted_at IS NULL GROUP BY status",
            $row['import_batch_id']
        ), ARRAY_A);
        foreach ($status_counts as $status_row) {
            if ($status_row['status'] === 'active') $row['active_properties_count'] = absint($status_row['total']);
            if ($status_row['status'] === 'pending_review') $row['pending_review_properties_count'] = absint($status_row['total']);
        }
        if ($row['properties_count'] === 0 && !empty($row['source_file_name'])) {
            $source_counts = $wpdb->get_results($wpdb->prepare(
                "SELECT record_type, COUNT(*) total FROM {$records_table} WHERE owner_user_id = %d AND data_origin = %s AND source_file_name = %s AND deleted_at IS NULL GROUP BY record_type",
                absint($row['owner_user_id']), sanitize_key($row['data_origin']), sanitize_text_field($row['source_file_name'])
            ), ARRAY_A);
            foreach ($source_counts as $count_row) {
                if ($count_row['record_type'] === 'property') $row['properties_count'] = absint($count_row['total']);
                if ($count_row['record_type'] === 'need') $row['needs_count'] = absint($count_row['total']);
            }
            $source_status_counts = $wpdb->get_results($wpdb->prepare(
                "SELECT status, COUNT(*) total FROM {$records_table} WHERE owner_user_id = %d AND data_origin = %s AND source_file_name = %s AND record_type = 'property' AND deleted_at IS NULL GROUP BY status",
                absint($row['owner_user_id']), sanitize_key($row['data_origin']), sanitize_text_field($row['source_file_name'])
            ), ARRAY_A);
            foreach ($source_status_counts as $status_row) {
                if ($status_row['status'] === 'active') $row['active_properties_count'] = absint($status_row['total']);
                if ($status_row['status'] === 'pending_review') $row['pending_review_properties_count'] = absint($status_row['total']);
            }
        }
    }
    return rest_ensure_response(array('ok' => true, 'batches' => $rows));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_delete_my_data(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('captacion_auth', 'Debes iniciar sesión.', array('status' => 401));
    }
    if (!current_user_can('manage_options') && !captacion_app_is_saas_admin($user_id)) {
        return new WP_Error('captacion_forbidden', 'Esta acción solo está disponible para administradores.', array('status' => 403));
    }
    $confirm = sanitize_text_field($request->get_param('confirm') ?? '');
    if ($confirm !== 'CONFIRMAR') {
        return new WP_Error('captacion_confirm_required', 'Debes enviar confirm=CONFIRMAR para eliminar tus datos.', array('status' => 400));
    }
    global $wpdb;
    $table = captacion_app_records_table_name();
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table} WHERE owner_user_id = %d",
        $user_id
    ));
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table} WHERE user_id = %d AND owner_user_id = 0",
        $user_id
    ));
    $batches_table = captacion_app_import_batches_table_name();
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$batches_table} WHERE owner_user_id = %d",
        $user_id
    ));
    $access_log_table = captacion_app_access_log_table_name();
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$access_log_table} WHERE user_id = %d",
        $user_id
    ));
    $events_table = captacion_app_events_table_name();
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$events_table} WHERE email = %s",
        wp_get_current_user()->user_email
    ));
    captacion_app_log_resource_event(array('resource_id' => 'user_data_deletion'), 'user_private_data_deleted', array(
        'owner_user_id' => $user_id,
    ));
    return rest_ensure_response(array('ok' => true, 'message' => 'Todos tus datos privados han sido eliminados.'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_records_routes() {
    register_rest_route('captacion/v1', '/records', array(
        array(
            'methods' => 'POST',
            'callback' => 'captacion_app_rest_save_record',
            'permission_callback' => 'captacion_app_rest_private_permission',
        ),
        array(
            'methods' => 'GET',
            'callback' => 'captacion_app_rest_list_records',
            'permission_callback' => 'captacion_app_rest_private_permission',
        ),
    ));
    register_rest_route('captacion/v1', '/register', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_register_professional',
        'permission_callback' => 'captacion_app_rest_public_nonce_permission',
    ));
    register_rest_route('captacion/v1', '/login', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_login',
        'permission_callback' => 'captacion_app_rest_public_nonce_permission',
    ));
    register_rest_route('captacion/v1', '/verification/resend', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_resend_verification',
        'permission_callback' => 'captacion_app_rest_public_nonce_permission',
    ));
    register_rest_route('captacion/v1', '/logout', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_logout',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/marketplace-access/status', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'captacion_app_rest_access_status',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/marketplace-access/consume', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_consume_access',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/marketplace-access/purchase-intent', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_purchase_intent',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/tasks', array(
        array('methods'=>WP_REST_Server::CREATABLE,'callback'=>'captacion_app_rest_save_task','permission_callback'=>'captacion_app_rest_private_permission'),
        array('methods'=>WP_REST_Server::READABLE,'callback'=>'captacion_app_rest_list_tasks','permission_callback'=>'captacion_app_rest_private_permission'),
    ));
    register_rest_route('captacion/v1', '/resources/generate', array(
        'methods'=>WP_REST_Server::CREATABLE,
        'callback'=>'captacion_app_rest_generate_resource_pdf',
        'permission_callback'=>'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/contact', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_contact',
        'permission_callback' => 'captacion_app_rest_public_nonce_permission',
    ));
    register_rest_route('captacion/v1', '/reports', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_submit_report',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml/demo/import', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_xml_demo_import',
        'permission_callback' => function () { return current_user_can('manage_options'); },
    ));
    register_rest_route('captacion/v1', '/xml/user/import', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_xml_user_import',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml/user/export', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'captacion_app_rest_xml_user_export',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml-feeds/import-url', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_xml_feed_import_url',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml-feeds/import-file', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_xml_feed_import_file',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/import/upload', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_xml_feed_import_file',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/import/template', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'captacion_app_rest_import_template',
        'permission_callback' => '__return_true',
    ));
    register_rest_route('captacion/v1', '/webhook/receive', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_webhook_receive',
        'permission_callback' => 'captacion_app_rest_webhook_permission',
    ));
    register_rest_route('captacion/v1', '/xml-feeds/(?P<import_batch_id>[a-zA-Z0-9_-]+)/sync', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_xml_feed_sync',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml-feeds', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'captacion_app_rest_list_import_batches',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml-feeds/(?P<feed_id>[a-zA-Z0-9_-]+)', array(
        array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'captacion_app_rest_delete_import_batch',
            'permission_callback' => 'captacion_app_rest_private_permission',
        ),
        array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'captacion_app_rest_update_import_batch',
            'permission_callback' => 'captacion_app_rest_private_permission',
        ),
    ));
    register_rest_route('captacion/v1', '/xml-feeds/(?P<feed_id>[a-zA-Z0-9_-]+)/pending', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'captacion_app_rest_feed_pending_properties',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml-feeds/(?P<feed_id>[a-zA-Z0-9_-]+)/properties/(?P<record_key>[^/]+)', array(
        'methods' => WP_REST_Server::EDITABLE,
        'callback' => 'captacion_app_rest_update_pending_property',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/xml-feeds/(?P<feed_id>[a-zA-Z0-9_-]+)/publish-all', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_publish_all_pending',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/import-batches', array(
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'captacion_app_rest_list_import_batches',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/import-batches/(?P<import_batch_id>[a-zA-Z0-9_-]+)', array(
        array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'captacion_app_rest_delete_import_batch',
            'permission_callback' => 'captacion_app_rest_private_permission',
        ),
        array(
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'captacion_app_rest_update_import_batch',
            'permission_callback' => 'captacion_app_rest_private_permission',
        ),
    ));
    register_rest_route('captacion/v1', '/import-batches/(?P<import_batch_id>[a-zA-Z0-9_-]+)/rollback', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_rest_import_rollback',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
    register_rest_route('captacion/v1', '/my-data', array(
        'methods' => WP_REST_Server::DELETABLE,
        'callback' => 'captacion_app_rest_delete_my_data',
        'permission_callback' => 'captacion_app_rest_private_permission',
    ));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_generic_lost_password_error($errors) {
    if (!is_wp_error($errors) || !$errors->has_errors()) return $errors;
    $generic = 'Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña.';
    foreach ($errors->get_error_codes() as $code) {
        $errors->remove($code);
        $errors->add($code, $generic);
    }
    return $errors;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_log_mail_event($data) {
    global $wpdb;
    captacion_app_maybe_install_mail_events_table();
    $table = captacion_app_events_table_name();
    $wpdb->insert($table, array(
        'category' => sanitize_key($data['category'] ?? 'general'),
        'source' => sanitize_text_field($data['source'] ?? ''),
        'email' => sanitize_email($data['email'] ?? ''),
        'name' => sanitize_text_field($data['name'] ?? ''),
        'agency' => sanitize_text_field($data['agency'] ?? ''),
        'phone' => sanitize_text_field($data['phone'] ?? ''),
        'reference' => sanitize_text_field($data['reference'] ?? ''),
        'message' => sanitize_textarea_field($data['message'] ?? ''),
        'tags' => wp_json_encode(array_values($data['tags'] ?? array())),
        'payload' => wp_json_encode($data['payload'] ?? array()),
        'created_at' => current_time('mysql'),
    ), array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_notify_internal_mail_event($data) {
    $admin_email = 'inmobia360@gmail.com';
    $category = sanitize_key($data['category'] ?? 'general');
    $source = sanitize_text_field($data['source'] ?? '');
    $subject_map = array(
        'registro' => 'Nuevo registro en Compra Captación',
        'contacto' => 'Nuevo mensaje de contacto en Compra Captación',
        'reporte_denuncia' => 'Nuevo reporte en el canal de denuncias',
        'busco_captacion' => 'Nueva demanda publicada en Compra Captación',
        'ofrecer_captacion' => 'Nueva captacion publicada en Compra Captación',
    );
    $subject = $subject_map[$category] ?? 'Nuevo evento en Compra Captación';
    $lines = array(
        'Categoria: ' . $category,
        'Origen: ' . $source,
        'Email: ' . sanitize_email($data['email'] ?? ''),
        'Nombre: ' . sanitize_text_field($data['name'] ?? ''),
        'Agencia: ' . sanitize_text_field($data['agency'] ?? ''),
        'Telefono: ' . sanitize_text_field($data['phone'] ?? ''),
        'Referencia: ' . sanitize_text_field($data['reference'] ?? ''),
        'Etiquetas: ' . implode(', ', array_map('sanitize_text_field', $data['tags'] ?? array())),
        'Mensaje:',
        sanitize_textarea_field($data['message'] ?? ''),
    );
    wp_mail($admin_email, $subject, implode("\n", $lines), array('Content-Type: text/plain; charset=UTF-8'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',
       'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_notification_templates() {
    return array(
        'welcome' => array(
            'category' => 'registro',
            'subject' => 'Bienvenido a Compra Captación',
            'body' => "Hola {name},\n\nTu cuenta profesional en Compra Captación se ha creado correctamente. Desde tu panel privado podras publicar demandas, revisar captaciones compatibles y recibir notificaciones cuando aparezcan oportunidades relevantes.\n\nEquipo Compra Captación",
        ),
        'contact_received' => array(
            'category' => 'contacto',
            'subject' => 'Hemos recibido tu mensaje en Compra Captación',
            'body' => "Hola {name},\n\nHemos recibido tu mensaje y lo estamos revisando. En breve recibiras una comunicacion referente a tu consulta.\n\nEquipo Compra Captación",
        ),
        'report_received' => array(
            'category' => 'reporte_denuncia',
            'subject' => 'Tu reporte esta en tramite',
            'body' => "Hola {name},\n\nTu reporte asociado a tu cuenta ha quedado registrado con la referencia {reference}. El mensaje esta en tramite y recibiras una respuesta en breve.\n\nEquipo Compra Captación",
        ),
        'match_need' => array(
            'category' => 'busco_captacion',
            'subject' => 'Nueva captacion compatible con tu demanda',
            'body' => "Hola {name},\n\nSe ha detectado una captacion compatible con tu demanda: {reference}. Puedes revisar la oportunidad desde tu panel privado, en la seccion Notificaciones.\n\nEquipo Compra Captación",
        ),
        'match_property' => array(
            'category' => 'ofrecer_captacion',
            'subject' => 'Nueva demanda compatible con tu captacion',
            'body' => "Hola {name},\n\nSe ha detectado una demanda compatible con tu captacion: {reference}. Puedes revisar la oportunidad desde tu panel privado, en la seccion Notificaciones.\n\nEquipo Compra Captación",
        ),
        'no_match_watch' => array(
            'category' => 'general',
            'subject' => 'Alerta activada para futuras coincidencias',
            'body' => "Hola {name},\n\nPor ahora no se han detectado coincidencias directas para {reference}. La alerta queda activa y te avisaremos en tu panel privado, seccion Notificaciones, cuando aparezca una compatibilidad real.\n\nEquipo Compra Captación",
        ),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_render_notification_text($template, $data) {
    $name = sanitize_text_field($data['name'] ?? '');
    $reference = sanitize_text_field($data['reference'] ?? 'tu publicacion');
    $message = sanitize_textarea_field($data['message'] ?? '');
    return strtr((string) $template, array(
        '{name}' => $name ? $name : 'Hola',
        '{reference}' => $reference ? $reference : 'tu publicacion',
        '{message}' => $message,
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_send_notification_email(WP_REST_Request $request) {
    $email = sanitize_email((string) $request->get_param('email'));
    if (!is_email($email)) {
        return new WP_Error('captacion_notification_invalid_email', 'Email no valido.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $type = sanitize_key((string) $request->get_param('type'));
    $templates = captacion_app_notification_templates();
    if (!isset($templates[$type])) {
        $type = 'no_match_watch';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $name = sanitize_text_field((string) $request->get_param('name'));
    $agency = sanitize_text_field((string) $request->get_param('agency'));
    $reference = sanitize_text_field((string) $request->get_param('reference'));
    $message = sanitize_textarea_field((string) $request->get_param('message'));
    $template = $templates[$type];
    $subject = captacion_app_render_notification_text($template['subject'], compact('name', 'reference', 'message'));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Bcc: inmobia360@gmail.com',
    );
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $event_data = array(
        'category' => sanitize_key($template['category'] ?? 'general'),
        'source' => 'notification_' . $type,
        'email' => $email,
        'name' => $name,
        'agency' => $agency,
        'phone' => '',
        'reference' => $reference,
        'message' => $message ? $message : $subject,
        'tags' => array('notification', $type),
        'payload' => array('sent' => (bool) $sent, 'subject' => $subject),
    );
    captacion_app_log_mail_event($event_data);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'ok' => (bool) $sent,
        'type' => $type,
        'email' => $email,
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_notification_routes() {
    register_rest_route('captacion/v1', '/notifications/send', array(
        'methods' => 'POST',
        'callback' => 'captacion_app_send_notification_email',
        'permission_callback' => 'captacion_app_rest_public_nonce_permission',
        'args' => array(
            'email' => array('required' => true),
            'type' => array('required' => true),
        ),
    ));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_mailchimp_subscribe(WP_REST_Request $request) {
    $settings = captacion_app_settings();
    $api_key = trim((string) ($settings['mailchimp_api_key'] ?? ''));
    $audience_id = trim((string) ($settings['mailchimp_audience_id'] ?? ''));
    $email = sanitize_email((string) $request->get_param('email'));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!$commercial_consent) {
        return new WP_Error('captacion_mailchimp_consent_required', 'Se requiere consentimiento comercial separado para suscribirse.', array('status' => 422));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!$api_key || !$audience_id) {
        return new WP_Error('captacion_mailchimp_missing_settings', 'Mailchimp no esta configurado.', array('status' => 400));
    }
    if (!is_email($email)) {
        return new WP_Error('captacion_mailchimp_invalid_email', 'Email no valido.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $datacenter = captacion_app_mailchimp_datacenter($api_key);
    if (!$datacenter) {
        return new WP_Error('captacion_mailchimp_invalid_key', 'API Key de Mailchimp no valida.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $allowed_tags = captacion_app_mailchimp_allowed_tags();
    $raw_tags = $request->get_param('tags');
    $raw_tags = is_array($raw_tags) ? $raw_tags : array($request->get_param('tag'));
    $tags = array_values(array_intersect($allowed_tags, array_map('sanitize_key', array_filter($raw_tags))));
    if (!$tags) {
        $tags = array('contacto');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $name = sanitize_text_field((string) $request->get_param('name'));
    $agency = sanitize_text_field((string) $request->get_param('agency'));
    $phone = sanitize_text_field((string) $request->get_param('phone'));
    $source = sanitize_text_field((string) $request->get_param('source'));
    $reference = sanitize_text_field((string) $request->get_param('reference'));
    $message = sanitize_textarea_field((string) $request->get_param('message'));
    $category = captacion_app_mailchimp_event_category($source, $tags);
    if (!$source) {
        $source = $tags[0];
        $category = captacion_app_mailchimp_event_category($source, $tags);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $member_hash = md5(strtolower($email));
    $url = sprintf('https://%s.api.mailchimp.com/3.0/lists/%s/members/%s', $datacenter, rawurlencode($audience_id), $member_hash);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $body = array(
        'email_address' => $email,
        'status_if_new' => $status,
        'status' => $status,
        'merge_fields' => array_filter(array(
            'FNAME' => $name,
        )),
        'tags' => $tags,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $response = wp_remote_request($url, array(
        'method' => 'PUT',
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode('captacion:' . $api_key),
            'Content-Type' => 'application/json',
        ),
        'body' => wp_json_encode($body),
        'timeout' => 12,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (is_wp_error($response)) {
        return new WP_Error('captacion_mailchimp_request_failed', $response->get_error_message(), array('status' => 502));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $code = wp_remote_retrieve_response_code($response);
    $payload = json_decode(wp_remote_retrieve_body($response), true);
    if ($code < 200 || $code >= 300) {
        $api_message = is_array($payload) && !empty($payload['detail']) ? $payload['detail'] : 'Mailchimp no pudo guardar el contacto.';
        return new WP_Error('captacion_mailchimp_api_error', $api_message, array('status' => $code ?: 502));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $note_parts = array_filter(array(
        $agency ? 'Agencia: ' . $agency : '',
        $phone ? 'Telefono: ' . $phone : '',
        $source ? 'Origen: ' . $source : '',
        $reference ? 'Referencia: ' . $reference : '',
        $message ? 'Mensaje: ' . $message : '',
    ));
    if ($note_parts) {
        wp_remote_post($url . '/notes', array(
            'headers' => array(
                'Authorization' => 'Basic ' . base64_encode('captacion:' . $api_key),
                'Content-Type' => 'application/json',
            ),
            'body' => wp_json_encode(array('note' => implode(' | ', $note_parts))),
            'timeout' => 8,
        ));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $event_data = array(
        'category' => $category,
        'source' => $source,
        'email' => $email,
        'name' => $name,
        'agency' => $agency,
        'phone' => $phone,
        'reference' => $reference,
        'message' => $message,
        'tags' => $tags,
        'payload' => array(
            'mailchimp_id' => is_array($payload) && isset($payload['id']) ? $payload['id'] : '',
            'status' => $status,
        ),
    );
    captacion_app_log_mail_event($event_data);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'ok' => true,
        'email' => $email,
        'tags' => $tags,
        'mailchimp_id' => is_array($payload) && isset($payload['id']) ? $payload['id'] : '',
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_mailchimp_routes() {
    register_rest_route('captacion/v1', '/mailchimp/subscribe', array(
        'methods' => 'POST',
        'callback' => 'captacion_app_mailchimp_subscribe',
        'permission_callback' => 'captacion_app_rest_public_nonce_permission',
        'args' => array(
            'email' => array('required' => true),
        ),
    ));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',
       'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_is_demo_environment() {
    if (defined('CAPTACION_APP_STAGING') && CAPTACION_APP_STAGING) {
        return true;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $environment = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
    return in_array($environment, array('local', 'development', 'staging'), true);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_wp_robots($robots) {
    if (captacion_app_is_demo_environment()) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
        $robots['noarchive'] = true;
        $robots['max-image-preview'] = 'none';
        $robots['max-snippet'] = -1;
        $robots['max-video-preview'] = -1;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return $robots;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_send_demo_headers() {
    if (captacion_app_is_demo_environment()) {
        header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    }
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_page_meta_descriptions() {
    return array(
        'inicio' => 'Marketplace B2B para agentes inmobiliarios: publica captaciones, cruza demandas activas y colabora con acceso protegido y trazabilidad.',
        'marketplace' => 'Revisa captaciones inmobiliarias, demandas de compradores y oportunidades de colaboracion entre agentes en un marketplace B2B protegido.',
        'buscar-captaciones' => 'Publica demandas de compradores y encuentra captaciones inmobiliarias compatibles por zona, presupuesto, tipologia y condiciones de colaboracion.',
        'ofrecer-captacion' => 'Publica y monetiza captaciones inmobiliarias con acceso protegido, control de datos sensibles y colaboracion profesional entre agencias.',
        'como-funciona' => 'Conoce como funciona Compra Captación: publica captaciones y demandas, detecta coincidencias y colabora con trazabilidad comercial.',
        'recursos' => 'Herramientas IA, calculadoras, plantillas y recursos para agentes inmobiliarios que quieren captar mejor y trabajar con mas productividad.',
        'planes' => 'Compara planes para agentes inmobiliarios: acceso inicial, recursos profesionales, publicacion de captaciones y demandas, y funciones avanzadas.',
        'contacto' => 'Contacta con Compra Captación para solicitar acceso, resolver dudas sobre planes o proponer colaboraciones inmobiliarias profesionales.',
        'area-privada' => 'Area privada para gestionar captaciones, demandas, favoritos, solicitudes, alertas y trazabilidad de operaciones inmobiliarias.',
        'aviso-legal' => 'Aviso legal de Compra Captación con informacion del titular, condiciones de uso, responsabilidades y datos pendientes de validacion final.',
        'privacidad' => 'Politica de privacidad de Compra Captación para tratamiento de datos, finalidades, derechos, seguridad y acceso profesional a la plataforma.',
        'cookies' => 'Politica de cookies de Compra Captación con informacion sobre cookies necesarias, estadisticas, marketing y consentimiento mediante Complianz.',
        'normas-publicacion' => 'Normas para publicar captaciones y demandas inmobiliarias con calidad, confidencialidad, legalidad y respeto entre profesionales.',
        'condiciones-de-contratacion' => 'Condiciones de contratacion de Compra Captación para planes, servicios, pagos, activacion, obligaciones y uso profesional de la plataforma.',
        'canal-de-denuncias' => 'Canal de denuncias de Compra Captación para comunicar incumplimientos, irregularidades o riesgos con confidencialidad y proteccion.',
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_output_meta_description() {
    if (defined('RANK_MATH_VERSION')) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!is_page()) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $post = get_queried_object();
    if (!$post || empty($post->post_name)) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $descriptions = captacion_app_page_meta_descriptions();
    if (!isset($descriptions[$post->post_name])) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    echo '<meta name="description" content="' . esc_attr($descriptions[$post->post_name]) . '">' . "\n";
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_output_cookie_banner_visibility_fix() {
    ?>
    <style id="captacion-complianz-visibility-fix">
        #cmplz-cookiebanner-container,
        .cmplz-cookiebanner,
        .cmplz-manage-consent,
        .cmplz-modal,
        .cmplz-soft-cookiewall {
            z-index: 2147483646 !important;
            pointer-events: auto !important;
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        .cmplz-cookiebanner,
        .cmplz-modal,
        .cmplz-soft-cookiewall {
            max-width: min(100vw - 24px, 760px) !important;
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        body .cmplz-cookiebanner,
        body .cmplz-cookiebanner *,
        body .cmplz-manage-consent,
        body .cmplz-manage-consent * {
            transition-property: background-color, border-color, color, box-shadow !important;
        }
    </style>
    <?php
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_seed_content_map() {
    $content_map = array(
        'inicio' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Captaciones inmobiliarias para profesionales</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las captaciones inmobiliarias son el centro de Compra Captación: una plataforma B2B para agentes, agencias e inversores que quieren publicar oportunidades, encontrar demandas activas y colaborar con acceso protegido. El objetivo es convertir producto captado, compradores cualificados y acuerdos entre profesionales en operaciones mejor documentadas.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Captaciones inmobiliarias para profesionales en Compra Captación"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>En el mercado inmobiliario profesional no siempre coincide quien tiene la captacion con quien tiene el comprador. Compra Captación ordena ese intercambio con fichas orientativas, demandas estructuradas, reglas de confidencialidad y trazabilidad para que cada parte pueda valorar si existe encaje antes de compartir informacion sensible.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Comprar captaciones inmobiliarias con contexto</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Comprar captaciones inmobiliarias no debe reducirse a recibir una direccion o un contacto. Un profesional necesita entender zona, tipologia, precio, estado comercial, condiciones de colaboracion y nivel de informacion disponible. Por eso la plataforma prioriza oportunidades con contexto y evita exponer datos privados del propietario desde el primer momento.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Vender captaciones inmobiliarias sin perder control</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vender captaciones inmobiliarias permite monetizar una oportunidad real cuando el captador no quiere o no puede trabajarla solo. La ficha publica muestra lo necesario para despertar interes profesional, mientras los datos sensibles, documentos, propietario y direccion exacta se reservan para fases protegidas del flujo.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Colaboracion inmobiliaria 50/50</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La colaboracion inmobiliaria 50/50 sigue siendo una formula habitual entre agentes: una parte aporta producto y otra aporta comprador, inversor o demanda activa. Compra Captación ayuda a ordenar esa colaboracion con solicitudes, condiciones, favoritos, alertas y seguimiento para reducir conversaciones dispersas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Marketplace inmobiliario B2B</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El <a href="/marketplace/">marketplace inmobiliario B2B</a> conecta captaciones y demandas para profesionales, no para particulares. Desde una misma web puedes revisar oportunidades, <a href="/buscar-captaciones/">buscar captaciones inmobiliarias</a>, <a href="/ofrecer-captacion/">vender captaciones inmobiliarias</a>, consultar <a href="/recursos/">herramientas IA para agentes inmobiliarios</a> y comparar <a href="/planes/">planes para agentes inmobiliarios</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Preguntas frecuentes sobre captaciones inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>Que es una captacion inmobiliaria?</strong> Es una oportunidad vinculada a un inmueble, propietario o activo que puede trabajarse comercialmente.</li><li><strong>Para quien es Compra Captación?</strong> Para agentes, agencias, personal shoppers, inversores profesionales y equipos inmobiliarios.</li><li><strong>Se muestran datos privados?</strong> No. La direccion exacta, propietario y documentos deben protegerse hasta que exista interes cualificado.</li><li><strong>Como empiezo?</strong> Revisa el marketplace, publica una demanda o prepara una captacion para compartirla con otros profesionales.</li></ul><!-- /wp:list -->
HTML,
        'marketplace' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Marketplace inmobiliario B2B de captaciones y demandas</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Un marketplace inmobiliario B2B permite a agentes, agencias e inversores profesionales compartir captaciones, publicar demandas activas y detectar oportunidades de colaboracion sin convertir la web en un portal abierto para particulares. Compra Captación conecta profesionales que tienen producto con profesionales que tienen comprador.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/property-defaults/edificio-default.jpg" alt="Marketplace inmobiliario B2B de captaciones y demandas"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>El marketplace concentra la parte operativa de la plataforma: fichas de captaciones inmobiliarias, demandas de compradores, oportunidades fuera de mercado, solicitudes de acceso, reglas de confidencialidad y enlaces con el <a href="/area-privada/">area privada inmobiliaria</a>. Cada publicacion debe aportar informacion comercial suficiente, pero sin revelar datos que puedan comprometer al captador.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Captaciones inmobiliarias activas</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las captaciones inmobiliarias activas son oportunidades publicadas por profesionales que tienen relacion con un propietario, un inmueble o un activo vendible. En el marketplace se presentan con informacion orientativa: tipologia, provincia, zona aproximada, precio, modalidad, estado comercial, exclusividad y condiciones de colaboracion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Demandas inmobiliarias de compradores</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las demandas inmobiliarias permiten que un agente con cliente comprador indique que tipo de propiedad busca. Una demanda bien definida incluye territorio, presupuesto, habitaciones, superficie, urgencia y solvencia. Al cruzar demanda y captacion, Compra Captación ayuda a detectar coincidencias y abrir conversaciones con mas probabilidad de cierre.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Colaboracion entre agentes y agencias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La colaboracion inmobiliaria es el eje del marketplace. Un profesional puede aportar la captacion y otro puede aportar comprador, inversor o demanda activa. La plataforma ayuda a ordenar la solicitud, el acceso, las condiciones y el seguimiento de cada oportunidad.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Marketplace privado, no portal para particulares</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captación no compite con un portal inmobiliario tradicional para publico final. Su objetivo es facilitar trabajo B2B entre agentes inmobiliarios, agencias, inversores profesionales y equipos que necesitan compartir producto captado con mayor control.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Oportunidades fuera de mercado</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Muchas oportunidades inmobiliarias no se publican de forma masiva en portales abiertos. El marketplace puede ayudar a trabajar propiedades fuera de mercado, activos discretos, captaciones exclusivas o colaboraciones que requieren privacidad antes de compartir datos sensibles.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como se protege cada operacion</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La direccion exacta, los datos del propietario, documentos privados y contactos directos deben quedar reservados hasta que exista un profesional cualificado y una solicitud con contexto. Esta capa de proteccion permite que el captador mantenga el control y que la contraparte revise la oportunidad con trazabilidad.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Ventajas frente a un portal inmobiliario tradicional</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Trabajar solo con profesionales y no con trafico generalista.</li><li>Publicar captaciones inmobiliarias con informacion limitada y acceso protegido.</li><li>Conectar demandas inmobiliarias con oportunidades compatibles.</li><li>Ordenar la colaboracion entre agencias y agentes con reglas claras.</li><li>Relacionar el marketplace con <a href="/buscar-captaciones/">buscar captaciones</a>, <a href="/ofrecer-captacion/">ofrecer captacion</a>, <a href="/como-funciona/">como funciona</a>, <a href="/planes/">planes</a>, <a href="/recursos/">recursos</a> y <a href="/normas-publicacion/">normas de publicacion</a>.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Como usar el marketplace inmobiliario B2B</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Revisa captaciones disponibles por zona, tipologia y contexto comercial.</li><li>Publica una demanda desde <a href="/buscar-captaciones/">buscar captaciones inmobiliarias</a> si representas a un comprador.</li><li>Prepara una ficha desde <a href="/ofrecer-captacion/">vender captaciones inmobiliarias</a> si tienes producto o propietario.</li><li>Consulta <a href="/como-funciona/">como funciona Compra Captación</a> antes de solicitar acceso protegido.</li><li>Aplica las <a href="/normas-publicacion/">normas de publicacion inmobiliaria</a> para mantener calidad y confidencialidad.</li></ul><!-- /wp:list -->
HTML,
        'buscar-captaciones' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Buscar captaciones inmobiliarias para clientes compradores</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Buscar captaciones inmobiliarias es clave cuando un agente tiene un cliente comprador, una demanda activa o una oportunidad de venta y necesita encontrar una propiedad compatible. Compra Captación permite publicar lo que busca tu cliente y cruzarlo con captaciones disponibles dentro de un entorno profesional, protegido y trazable.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/property-defaults/piso-default.jpg" alt="Buscar captaciones inmobiliarias para clientes compradores"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Esta seccion esta pensada para agentes inmobiliarios, agencias, personal shopper inmobiliario y profesionales que representan a un comprador real. El objetivo no es navegar inventario sin criterio, sino transformar una necesidad concreta en una demanda inmobiliaria clara: comunidad autonoma, provincia, municipio, presupuesto, tipo de propiedad, urgencia, solvencia y condiciones de colaboracion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Publica una demanda inmobiliaria activa</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Una demanda inmobiliaria activa describe lo que busca un cliente comprador y permite que otros profesionales detecten si tienen una captacion compatible. Cuanto mejor se define la demanda, mas facil es encontrar propiedades que encajen con el comprador y evitar conversaciones improductivas.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Tipo de inmueble que busca el cliente comprador.</li><li>Comunidad autonoma, provincia, municipio y zona prioritaria.</li><li>Presupuesto minimo y maximo con margen realista.</li><li>Habitaciones, banos, superficie, estado y requisitos no negociables.</li><li>Timing de compra, solvencia, financiacion y condiciones de colaboracion.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Encuentra captaciones compatibles</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El valor de buscar captaciones inmobiliarias dentro de Compra Captación esta en el cruce entre oferta y demanda. Si otro profesional tiene una propiedad, un activo discreto o una captacion fuera de mercado que encaja con tu comprador, la plataforma ayuda a identificar la coincidencia y ordenar el siguiente paso sin exponer datos sensibles antes de tiempo.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Trabaja como agente del comprador</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El agente del comprador necesita herramientas para localizar oportunidades, comparar alternativas y negociar con informacion suficiente. Esta pagina debe comunicar que Compra Captación no es solo una lista de inmuebles, sino un marketplace inmobiliario B2B donde la demanda profesional tambien tiene protagonismo.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Accede a propiedades fuera de mercado</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Muchas oportunidades no aparecen en portales abiertos o todavia no estan preparadas para publicarse de forma masiva. Por eso una red de captaciones protegidas puede ser util para encontrar propiedades fuera de mercado, activos discretos o colaboraciones que solo tienen sentido entre profesionales verificados.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como funciona la busqueda de captaciones</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El flujo recomendado es simple: defines la demanda, el sistema la cruza con captaciones inmobiliarias disponibles, revisas coincidencias, solicitas acceso protegido y avanzas solo cuando existe encaje real. Este modelo reduce ruido, protege al captador y ayuda al profesional con comprador a concentrarse en oportunidades con mas probabilidad de cierre.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Ventajas para agentes con cliente comprador</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Convertir una necesidad del comprador en una busqueda profesional estructurada.</li><li>Acceder a captaciones compatibles sin depender solo de portales generalistas.</li><li>Trabajar demandas inmobiliarias con trazabilidad y contexto comercial.</li><li>Encontrar oportunidades para colaborar con otros agentes de forma ordenada.</li><li>Conectar la busqueda con el <a href="/marketplace/">marketplace</a>, la pagina para <a href="/ofrecer-captacion/">ofrecer captacion</a>, el flujo de <a href="/como-funciona/">como funciona</a>, los <a href="/planes/">planes</a> y los <a href="/recursos/">recursos profesionales</a>.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Preguntas frecuentes al buscar captaciones inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>Necesito tener comprador real?</strong> Si, la demanda debe representar una necesidad profesional y verificable.</li><li><strong>Que datos debo evitar?</strong> No publiques documentos privados ni datos personales innecesarios del comprador.</li><li><strong>Como se detectan coincidencias?</strong> Por territorio, presupuesto, tipologia, habitaciones, banos, superficie y contexto de colaboracion.</li><li><strong>Que hago despues?</strong> Revisa el <a href="/marketplace/">marketplace inmobiliario B2B</a> y solicita acceso solo cuando exista encaje real.</li></ul><!-- /wp:list -->
HTML,
        'ofrecer-captacion' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Vender captaciones inmobiliarias con acceso protegido</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vender captaciones inmobiliarias permite a agentes y agencias monetizar oportunidades reales sin exponer datos sensibles desde el primer momento. Compra Captación ayuda a publicar captaciones, encontrar profesionales con demanda activa y abrir colaboraciones con trazabilidad comercial.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/property-defaults/casa-chalet-default.jpg" alt="Vender captaciones inmobiliarias con acceso protegido"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Esta pagina esta pensada para profesionales que ya tienen una captacion inmobiliaria, un propietario vendedor, una oportunidad discreta o un inmueble con potencial de salida. En lugar de regalar toda la informacion desde el inicio, la plataforma permite publicar una ficha orientativa, controlar el acceso y decidir con quien avanzar.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Publica una captacion inmobiliaria</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Publicar captaciones inmobiliarias en Compra Captación significa convertir una oportunidad en una ficha profesional lista para ser cruzada con demandas activas. La informacion publica debe despertar interes sin comprometer la confidencialidad: tipologia, territorio aproximado, precio, superficie, habitaciones, estado comercial, modalidad y condiciones de colaboracion.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Tipo de inmueble, provincia, municipio y zona aproximada.</li><li>Precio estimado, superficie, habitaciones, banos y estado comercial.</li><li>Modalidad: colaboracion, venta de captacion o acceso protegido.</li><li>Condiciones de honorarios, reparto previsto y exclusividad.</li><li>Documentacion disponible y nivel de confidencialidad necesario.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Convierte propietarios vendedores en oportunidades</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La captacion de propietarios es una de las tareas mas valiosas del negocio inmobiliario. Cuando un propietario vendedor confia en un profesional, esa relacion puede transformarse en una oportunidad comercial para otros agentes con comprador, inversores o agencias que trabajan esa zona.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Vende una captacion 100%</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>En algunos casos el profesional puede querer vender una captacion 100%, ceder la gestion completa o transferir la oportunidad a otro operador mejor posicionado. Esta modalidad debe explicarse con claridad para que el comprador entienda que adquiere una oportunidad profesional, no una simple direccion ni un dato sin contexto.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Comparte captaciones en colaboracion inmobiliaria</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Tambien puedes compartir captaciones inmobiliarias mediante colaboracion 50/50, reparto de honorarios u otro acuerdo entre profesionales. Compra Captación ayuda a ordenar la solicitud, la disponibilidad, el acceso a datos y la trazabilidad de cada paso antes de abrir informacion sensible.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Protege los datos sensibles del propietario</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La direccion exacta, los datos personales del propietario, documentacion privada, referencia catastral completa y contactos directos deben quedar protegidos hasta que exista una contraparte cualificada. Esa proteccion aumenta la confianza del captador y reduce el riesgo de fugas de informacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como funciona ofrecer una captacion</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El flujo recomendado es sencillo: preparas la ficha, publicas la captacion, recibes solicitudes de profesionales interesados, revisas compatibilidad y avanzas solo cuando el contexto tiene sentido. Asi la captacion inmobiliaria pasa de ser un dato aislado a una oportunidad estructurada dentro de un marketplace inmobiliario B2B.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Ventajas para agentes y agencias</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Monetizar captaciones inmobiliarias sin perder control del activo.</li><li>Conectar con agentes que tienen demanda o cliente comprador.</li><li>Publicar oportunidades sin depender solo de portales generalistas.</li><li>Trabajar colaboraciones con mas contexto, reglas y trazabilidad.</li><li>Relacionar la captacion con el <a href="/marketplace/">marketplace</a>, las <a href="/buscar-captaciones/">demandas activas</a>, el flujo de <a href="/como-funciona/">como funciona</a>, los <a href="/planes/">planes</a>, los <a href="/recursos/">recursos</a> y las <a href="/normas-publicacion/">normas de publicacion</a>.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Buenas practicas antes de vender captaciones inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Comprueba que tienes legitimidad para compartir la oportunidad.</li><li>Evita publicar datos personales, telefono del propietario o direccion exacta.</li><li>Define si buscas colaboracion 50/50, cesion, acceso puntual o reparto de honorarios.</li><li>Usa las <a href="/normas-publicacion/">normas de publicacion inmobiliaria</a> para mantener calidad.</li><li>Consulta los <a href="/planes/">planes para agentes inmobiliarios</a> si necesitas publicar de forma recurrente.</li></ul><!-- /wp:list -->
HTML,
        'como-funciona' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Como funciona una plataforma inmobiliaria B2B</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Como funciona una plataforma inmobiliaria B2B como Compra Captación: permite a agentes, agencias e inversores publicar captaciones, crear demandas activas, cruzar oportunidades compatibles y colaborar con acceso protegido. El objetivo es ordenar lo que normalmente se gestiona por llamadas, mensajes privados y contactos dispersos.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Como funciona una plataforma inmobiliaria B2B para agentes"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>La plataforma no esta pensada como un portal abierto para particulares, sino como un entorno profesional donde la informacion sensible se comparte por fases. Primero se publica el contexto comercial necesario; despues se valida el interes, la compatibilidad y las condiciones de colaboracion antes de avanzar.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Publica una captacion o una demanda</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El profesional puede publicar una captacion inmobiliaria cuando tiene una oportunidad de propietario vendedor, un inmueble, un activo discreto o una operacion que puede interesar a otros agentes. Tambien puede crear una demanda inmobiliaria cuando tiene un cliente comprador o inversor que busca una propiedad concreta.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Cruza oportunidades compatibles</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El sistema ayuda a relacionar captaciones y demandas por criterios como comunidad autonoma, provincia, tipologia, precio, habitaciones, banos, superficie, estado comercial, modalidad de colaboracion y contexto de operacion. Asi, un agente con comprador puede encontrar producto y un captador puede detectar profesionales con demanda real.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>3. Solicita acceso protegido</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los datos sensibles no deben mostrarse desde el primer momento. La direccion exacta, los datos del propietario, documentos privados y contactos directos quedan protegidos hasta que exista una solicitud con contexto profesional y una razon clara para avanzar.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>4. Define condiciones de colaboracion</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Antes de compartir informacion completa, las partes pueden revisar si la operacion sera una colaboracion 50/50, una venta de captacion, un acceso puntual o una relacion comercial con condiciones especificas. Esta claridad reduce malentendidos sobre honorarios, seguimiento y responsabilidades.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Trabaja la operacion con trazabilidad</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Cada solicitud, desbloqueo, favorito, tarea o avance debe quedar asociado a un flujo claro. La trazabilidad es importante para proteger al captador, ordenar la relacion con la contraparte y evitar que una oportunidad profesional se pierda entre conversaciones sueltas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Cierra mas operaciones entre profesionales</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El resultado buscado es que los agentes inmobiliarios puedan cerrar mas operaciones porque encuentran producto, demanda o colaboracion en el momento adecuado. Compra Captación conecta el <a href="/marketplace/">marketplace</a>, la opcion de <a href="/buscar-captaciones/">buscar captaciones</a>, la publicacion para <a href="/ofrecer-captacion/">ofrecer captacion</a>, los <a href="/recursos/">recursos profesionales</a>, los <a href="/planes/">planes</a> y el <a href="/area-privada/">area privada</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Objetivos de Compra Captación</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Crear una red profesional para captaciones inmobiliarias y demandas activas.</li><li>Proteger informacion sensible hasta que exista interes cualificado.</li><li>Ordenar la colaboracion entre agentes, agencias e inversores.</li><li>Reducir fugas de informacion y conversaciones sin seguimiento.</li><li>Dar mas valor al trabajo de captacion de propietarios vendedores.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Que lograras usando la plataforma</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Un agente puede encontrar propiedades para compradores, publicar oportunidades que no quiere trabajar solo, colaborar con otros profesionales y apoyarse en herramientas de productividad. Una agencia puede organizar mejor sus operaciones compartidas, ampliar red profesional y convertir captaciones o demandas en oportunidades mas estructuradas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Paginas clave para entender el flujo</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li><a href="/marketplace/">Marketplace inmobiliario B2B</a> para revisar captaciones y demandas.</li><li><a href="/buscar-captaciones/">Buscar captaciones inmobiliarias</a> para publicar necesidades de compradores.</li><li><a href="/ofrecer-captacion/">Vender captaciones inmobiliarias</a> para compartir oportunidades.</li><li><a href="/recursos/">Herramientas IA para agentes inmobiliarios</a> para mejorar productividad.</li><li><a href="/contacto/">Contacto Compra Captación</a> para dudas, acceso y colaboraciones.</li></ul><!-- /wp:list -->
HTML,
        'recursos' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Herramientas IA para agentes inmobiliarios</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las herramientas IA para agentes inmobiliarios ayudan a ahorrar tiempo, automatizar tareas repetitivas, mejorar la captacion, preparar textos comerciales y tomar mejores decisiones. En Compra Captación reunimos asistentes, calculadoras, plantillas y recursos practicos para que los profesionales inmobiliarios trabajen con mas productividad.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Herramientas IA para agentes inmobiliarios"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Esta seccion no debe funcionar como un simple repositorio de documentos. Recursos debe ser un centro de productividad inmobiliaria donde el agente pueda encontrar utilidades para captar mejor, responder antes, redactar fichas, cualificar compradores, calcular honorarios, preparar documentacion y usar inteligencia artificial en su trabajo diario.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Asistentes IA para inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Un asistente IA inmobiliario puede ayudar a responder preguntas frecuentes, preparar mensajes, organizar informacion de una captacion, resumir expedientes y generar borradores de comunicaciones. La clave es que el agente mantenga el control profesional mientras la IA reduce tareas repetitivas y mejora la velocidad de respuesta.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Herramientas para captar propiedades</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los recursos deben incluir herramientas para captar propiedades, preparar propuestas de captacion, responder objeciones del propietario, crear argumentarios comerciales y convertir una oportunidad en una ficha lista para colaborar o vender dentro del <a href="/marketplace/">marketplace inmobiliario B2B</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Generadores de textos inmobiliarios</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los generadores de textos inmobiliarios permiten redactar descripciones de inmuebles, mensajes para WhatsApp, emails de seguimiento, resumenes de captacion, publicaciones comerciales y textos para presentar oportunidades a otros profesionales con rapidez y coherencia.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Calculadoras inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las calculadoras inmobiliarias aportan valor inmediato al trabajo del agente. Pueden ayudar a estimar honorarios, reparto de comisiones, neto vendedor, rentabilidad de alquiler, escenarios hipotecarios o importes orientativos antes de avanzar en una operacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Plantillas para agentes inmobiliarios</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las plantillas para agentes inmobiliarios permiten trabajar con mas orden: NDA, acuerdos de colaboracion, checklist documental, ficha de captacion profesional, guias de publicacion y modelos para preparar demandas de compradores con mejor informacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Automatizacion y productividad inmobiliaria</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La automatizacion inmobiliaria debe ayudar al profesional a reducir tareas manuales sin perder criterio. Un buen hub de recursos puede apoyar la cualificacion de leads, la preparacion de visitas, la priorizacion de oportunidades y el seguimiento de operaciones compartidas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Recursos para colaboracion entre profesionales</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captación trabaja con captaciones, demandas y colaboracion entre profesionales. Por eso los recursos deben conectar con el <a href="/marketplace/">marketplace</a>, la pagina para <a href="/buscar-captaciones/">buscar captaciones</a>, la opcion de <a href="/ofrecer-captacion/">ofrecer captacion</a>, el flujo de <a href="/como-funciona/">como funciona</a>, los <a href="/planes/">planes</a> y el <a href="/area-privada/">area privada</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como usar IA en el trabajo diario del agente</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Generar una descripcion clara de una captacion inmobiliaria.</li><li>Preparar una propuesta para propietarios vendedores.</li><li>Cualificar una demanda de cliente comprador.</li><li>Crear mensajes de seguimiento para WhatsApp o email.</li><li>Calcular honorarios, repartos y escenarios de operacion.</li><li>Resumir informacion de una colaboracion antes de tomar decisiones.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Recursos conectados con captaciones y demandas</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las herramientas IA para agentes inmobiliarios tienen mas valor cuando se conectan con un flujo comercial real. Puedes usarlas para preparar una ficha antes de <a href="/ofrecer-captacion/">vender captaciones inmobiliarias</a>, redactar una necesidad antes de <a href="/buscar-captaciones/">buscar captaciones inmobiliarias</a> o documentar una colaboracion desde el <a href="/area-privada/">area privada inmobiliaria</a>.</p><!-- /wp:paragraph -->
HTML,
        'planes' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Planes para agentes inmobiliarios</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los planes para agentes inmobiliarios de Compra Captación estan pensados para profesionales, agencias y equipos que quieren usar una plataforma B2B para publicar captaciones, buscar demandas, acceder a recursos y trabajar oportunidades con mas orden. Starter permite empezar con acceso inicial y Professional activa un uso mas completo de la plataforma.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Planes para agentes inmobiliarios"/></figure><!-- /wp:image -->
<!-- wp:shortcode -->[captacion_stripe_plans]<!-- /wp:shortcode -->
<!-- wp:heading {"level":3} --><h3>Starter - 0 euros</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Starter esta orientado a profesionales que quieren conocer Compra Captación antes de contratar un acceso avanzado. Sirve para explorar la propuesta, revisar recursos, entender el <a href="/marketplace/">marketplace inmobiliario B2B</a> y empezar a organizar la actividad sin coste de entrada.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Que incluye el plan gratis</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Acceso inicial a la plataforma.</li><li>Exploracion del marketplace inmobiliario B2B.</li><li>Recursos gratuitos para agentes inmobiliarios.</li><li>Herramientas IA basicas para productividad.</li><li>Calculadoras y plantillas iniciales.</li><li>Perfil profesional basico.</li><li>Actualizaciones sobre nuevas funciones y oportunidades.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Plan Profesional</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El plan profesional esta pensado para agentes y agencias que quieren usar Compra Captación como herramienta recurrente. Permite trabajar captaciones inmobiliarias, demandas activas, colaboraciones y <a href="/recursos/">herramientas IA para agentes inmobiliarios</a> con mayor profundidad operativa.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Publicar captaciones inmobiliarias.</li><li>Publicar demandas de clientes compradores.</li><li>Solicitar acceso protegido a oportunidades compatibles.</li><li>Usar herramientas IA avanzadas para agentes inmobiliarios.</li><li>Generar textos comerciales, mensajes y descripciones.</li><li>Acceder a calculadoras, plantillas y documentos profesionales.</li><li>Recibir alertas y seguimiento de oportunidades.</li><li>Usar el area privada completa con favoritos, tareas y trazabilidad.</li><li>Contar con soporte prioritario segun disponibilidad del servicio.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Que plan elegir segun tu perfil</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Si quieres conocer la plataforma y revisar recursos, Starter es suficiente para empezar. Si ya trabajas con captaciones, compradores, inversores o colaboraciones entre agencias, Professional es el acceso recomendado para operar con mas continuidad.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Herramientas incluidas para agentes inmobiliarios</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los planes conectan con las secciones clave de la web: <a href="/marketplace/">marketplace</a>, <a href="/buscar-captaciones/">buscar captaciones</a>, <a href="/ofrecer-captacion/">ofrecer captacion</a>, <a href="/recursos/">recursos con inteligencia artificial</a>, <a href="/como-funciona/">como funciona</a> y <a href="/contacto/">contacto</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Preguntas frecuentes sobre los planes</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li><strong>Que plan debo elegir?</strong> Starter sirve para conocer la plataforma; Professional es mejor si trabajas captaciones y demandas de forma recurrente.</li><li><strong>Incluye recursos?</strong> Si, los planes conectan con recursos, calculadoras, plantillas y herramientas IA.</li><li><strong>Puedo publicar captaciones?</strong> El plan profesional esta orientado a publicar captaciones, demandas y solicitudes protegidas.</li><li><strong>Hay soporte para agencias?</strong> Si necesitas acceso, soporte comercial o una configuracion para agencia, puedes contactar con el equipo.</li><li><strong>Las funciones pueden cambiar?</strong> Si, las funcionalidades pueden evolucionar segun el despliegue final de la plataforma.</li></ul><!-- /wp:list -->
HTML,
        'contacto' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Contacto Compra Captación</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Contacto Compra Captación es la pagina para que agentes inmobiliarios, agencias, inversores y profesionales del sector soliciten acceso, resuelvan dudas sobre planes, propongan colaboraciones o pidan soporte sobre el uso de la plataforma.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Contacto Compra Captación para profesionales inmobiliarios"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Si quieres valorar el encaje de la plataforma con tu forma de trabajar, puedes escribir a <strong>info@compracaptacion.com</strong>. Cuanto mas claro sea el contexto, mas facil sera orientar la respuesta hacia <a href="/ofrecer-captacion/">captaciones inmobiliarias</a>, <a href="/buscar-captaciones/">demandas de compradores</a>, recursos, planes o colaboraciones profesionales.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Solicitar acceso a la plataforma</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El acceso guiado ayuda a entender como funciona Compra Captación, como se publican captaciones inmobiliarias, como se crean demandas de compradores y como se protege el acceso a informacion sensible dentro del marketplace inmobiliario B2B.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Resolver dudas sobre planes</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Si tienes dudas sobre Starter, Professional, Premium o una posible configuracion para agencia, la pagina de contacto debe servir como canal directo para aclarar alcance, funciones disponibles y siguientes pasos.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Proponer una colaboracion profesional</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captación puede recibir propuestas de colaboracion de agencias, redes inmobiliarias, proveedores de herramientas, profesionales especializados en captacion o equipos que quieran aportar valor al ecosistema.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Soporte para agentes y agencias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El contacto tambien debe cubrir dudas operativas sobre acceso, publicacion de oportunidades, recursos con inteligencia artificial, area privada, solicitudes protegidas o funcionamiento general de la web.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Que informacion conviene incluir</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Nombre, agencia o perfil profesional.</li><li>Zona principal de trabajo.</li><li>Si buscas captar propiedades, encontrar producto para compradores o colaborar con otros agentes.</li><li>Volumen aproximado de captaciones o demandas.</li><li>Interes en Starter, Professional, Premium, acceso guiado o partnership.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Canales de contacto</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El canal principal es <strong>info@compracaptacion.com</strong>. Tambien puedes revisar antes <a href="/como-funciona/">como funciona</a>, comparar los <a href="/planes/">planes</a>, explorar el <a href="/marketplace/">marketplace</a>, publicar una demanda en <a href="/buscar-captaciones/">buscar captaciones</a> o preparar una oportunidad en <a href="/ofrecer-captacion/">ofrecer captacion</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Antes de contactar</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Indica si eres agente, agencia, inversor profesional o proveedor.</li><li>Explica tu zona principal de trabajo y tipo de operaciones.</li><li>Senala si buscas publicar captaciones, encontrar producto para compradores o colaborar con otros agentes.</li><li>Incluye si te interesa Starter, Professional, acceso guiado o partnership.</li></ul><!-- /wp:list -->
HTML,
        'area-privada' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Area privada inmobiliaria para captaciones y demandas</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>El area privada inmobiliaria es donde Compra Captación deja de ser una promesa y se convierte en herramienta diaria. Aqui deben converger las captaciones aportadas, las demandas publicadas, las solicitudes recibidas, el seguimiento de operaciones y la trazabilidad de actividad.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>El area privada anticipa modulos clave como favoritos, tareas, alertas, comunicaciones internas y seguimiento de estados. El objetivo de esta pagina es explicar claramente que valor operativo obtiene el usuario una vez entra en el producto y empieza a trabajar oportunidades reales.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Que resuelve el area privada inmobiliaria</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Priorizar trabajo pendiente y oportunidades activas.</li><li>Evitar conversaciones dispersas fuera del contexto de cada expediente.</li><li>Concentrar evidencias, tareas y proxima accion de cada operacion.</li><li>Dar continuidad a la colaboracion entre profesionales sin perder control.</li><li>Conectar el <a href="/marketplace/">marketplace inmobiliario B2B</a> con alertas, favoritos y solicitudes.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Funciones previstas para profesionales</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Mis captaciones publicadas y estado de cada oportunidad.</li><li>Mis demandas activas y posibles coincidencias.</li><li>Solicitudes de acceso protegido enviadas y recibidas.</li><li>Favoritos para seguir oportunidades relevantes.</li><li>Alertas cuando aparezcan captaciones o demandas compatibles.</li></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>Desde el area privada, el usuario puede dar continuidad a lo iniciado en <a href="/buscar-captaciones/">buscar captaciones inmobiliarias</a>, <a href="/ofrecer-captacion/">vender captaciones inmobiliarias</a> o revisar los <a href="/planes/">planes para agentes inmobiliarios</a> si necesita mayor capacidad operativa.</p><!-- /wp:paragraph -->
HTML,
        'aviso-legal' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Aviso legal Compra Captación</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>El aviso legal Compra Captación recoge informacion sobre titularidad, condiciones de uso, responsabilidades y canales de contacto aplicables a la plataforma profesional B2B. La informacion societaria completa se facilitara en los documentos contractuales o canales oficiales correspondientes.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Los flujos, formularios y contenidos deben alinearse con la operativa final, las condiciones de uso y la politica de privacidad revisada por asesoria juridica.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Informacion operativa</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Mantener actualizados los datos oficiales del titular en los canales contractuales y documentos legales aplicables.</li><li>Revisar condiciones de uso, propiedad intelectual y limitaciones de responsabilidad.</li><li>Alinear textos con la operativa vigente y con la politica de privacidad publicada.</li></ul><!-- /wp:list -->
HTML,
        'privacidad' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Politica de privacidad Compra Captación</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>La politica de privacidad Compra Captación explica como se tratan los datos de profesionales, usuarios, leads y contrapartes dentro de la plataforma. Las consultas de privacidad se atienden mediante los canales oficiales publicados por Compra Captación.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Compra Captación trabaja con informacion comercial sensible y, potencialmente, con datos personales de profesionales, leads y contrapartes. Por eso la politica final debe definir con precision finalidades, bases juridicas, plazos de conservacion, destinatarios y derechos de los interesados.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Se recomienda limitar la captacion de datos al minimo imprescindible y evitar recoger informacion real de terceros hasta que el flujo de cumplimiento, seguridad y encargados del tratamiento este cerrado.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Recomendaciones operativas</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Usar formularios solo con datos adecuados, minimizados y autorizados.</li><li>Revisar textos de consentimiento y finalidades antes de activar nuevas campanas.</li><li>Mantener documentados encargados, copias de seguridad y control de accesos.</li></ul><!-- /wp:list -->
HTML,
        'cookies' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Politica de cookies Compra Captación</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>La politica de cookies Compra Captación se apoya en Complianz como fuente principal de consentimiento, bloqueo preventivo, inventario y declaracion de cookies. Las tecnologias no necesarias deben permanecer desactivadas hasta obtener consentimiento valido.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Necesarias: activas para funcionamiento, sesion y seguridad.</li><li>Preferencias: sujetas a la clasificacion final de Complianz.</li><li>Estadisticas: desactivadas hasta consentimiento.</li><li>Marketing: desactivado hasta consentimiento.</li></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>El sitio puede utilizar almacenamiento local para sesion, tema y datos operativos temporales. No se utiliza como consentimiento legal. El mapa usa Leaflet y teselas de OpenStreetMap como servicio tecnico solicitado y debe figurar en el inventario de tecnologias cuando corresponda.</p><!-- /wp:paragraph -->
<!-- wp:shortcode -->[cmplz-document type="cookie-statement" region="eu"]<!-- /wp:shortcode -->
<!-- wp:paragraph --><p><strong>Informacion legal.</strong> El inventario de cookies y tecnologias similares debe mantenerse actualizado mediante el wizard y escaner de Complianz, junto con los datos oficiales del titular cuando esten disponibles en los documentos contractuales o canales oficiales aplicables.</p><!-- /wp:paragraph -->
HTML,
        'normas-publicacion' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Normas de publicacion inmobiliaria</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las normas de publicacion inmobiliaria de Compra Captación ayudan a mantener un estandar alto de calidad en captaciones, demandas y colaboraciones. Estas normas protegen a quien publica, a quien compra informacion y a la reputacion del ecosistema profesional.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Publica solo informacion veraz, actualizada y comercialmente util.</li><li>No incluyas datos personales o de contacto del propietario en la ficha publica.</li><li>Indica con honestidad el estado documental, la urgencia y las condiciones de colaboracion.</li><li>No utilices la plataforma para captar datos de otros profesionales sin intencion real de operar.</li><li>Respeta la confidencialidad de todo expediente desbloqueado.</li></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>La calidad de las publicaciones es parte del producto. Cuanto mejor se define una captacion o una demanda, mejor funciona el matching y mas valor aporta la plataforma a todos los actores.</p><!-- /wp:paragraph -->
HTML,
        'condiciones-de-contratacion' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Condiciones de contratacion Compra Captación</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las condiciones de contratacion Compra Captación regulan la futura contratacion de planes, servicios de acceso y funcionalidades a traves del sitio web y de sus entornos autorizados. El texto definitivo debera revisarse y aprobarse antes del lanzamiento comercial.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Objeto</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captación ofrecerá acceso a funcionalidades de colaboración inmobiliaria B2B, publicación de captaciones y demandas, herramientas operativas, alertas, recursos y otros servicios vinculados a la plataforma según el plan contratado en cada momento.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Partes intervinientes</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La contratación se realizará entre la sociedad titular de Compra Captación, cuyos datos completos se incorporarán en la versión final, y la persona física o jurídica que complete correctamente el proceso de alta o contratación y acepte expresamente estas condiciones.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><strong>PENDIENTE DE COMPLETAR:</strong> razón social, NIF/CIF, domicilio y datos registrales del titular.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>3. Requisitos de contratación</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Aportar datos veraces, completos y actualizados.</li><li>Tener capacidad legal suficiente para contratar.</li><li>Utilizar la plataforma con fines profesionales legítimos.</li><li>Aceptar el aviso legal, la política de privacidad y las normas de publicación aplicables.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>4. Precio, pago y activación</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los precios, condiciones económicas, periodicidad de cobro, impuestos aplicables y alcance de cada plan se mostrarán en la página de planes o en la oferta particular correspondiente. El acceso podrá activarse una vez confirmado el pago, validada la cuenta y, en su caso, completado el proceso de revisión profesional.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Duración, renovación y baja</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los planes podrán tener duración mensual, anual o vinculada a servicios concretos. La versión final deberá indicar con claridad si existe renovación automática, plazo de cancelación y condiciones de baja o suspensión del servicio.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Obligaciones del cliente</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>No publicar información engañosa, ilícita o carente de legitimidad.</li><li>Custodiar sus credenciales y limitar el acceso a usuarios autorizados.</li><li>No intentar eludir los controles de acceso, pago, moderación o trazabilidad.</li><li>Respetar la confidencialidad de la información desbloqueada dentro de la plataforma.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>7. Limitaciones y responsabilidad</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captación no garantiza el cierre de operaciones, la veracidad material de cada publicación ni el comportamiento de terceros. La plataforma actúa como entorno de colaboración y gestión, sin asumir la posición de propietario, comprador, vendedor ni mediador universal en cada expediente.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>8. Resolución de conflictos y ley aplicable</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La versión definitiva deberá indicar la legislación aplicable, el fuero competente y, en su caso, los mecanismos de resolución extrajudicial que resulten procedentes según la normativa española y europea aplicable.</p><!-- /wp:paragraph -->
HTML,
        'canal-de-denuncias' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Canal de denuncias Compra Captación</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>El canal de denuncias Compra Captación preve disponer de un sistema interno de informacion para que empleados, colaboradores, proveedores, usuarios profesionales y terceros puedan comunicar de buena fe posibles incumplimientos normativos, irregularidades o conductas contrarias a las politicas internas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Finalidad del canal</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El canal servirá para recibir comunicaciones sobre hechos que puedan suponer infracciones legales, incumplimientos éticos, vulneraciones de confidencialidad, uso indebido de datos, conflictos de interés, fraudes o conductas contrarias a las normas internas de la plataforma.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Principios de funcionamiento</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Confidencialidad de la identidad de la persona informante y de las personas afectadas.</li><li>Prohibición de represalias frente a quien comunique de buena fe.</li><li>Recepción, análisis y tratamiento con medidas proporcionadas y trazables.</li><li>Respeto a los derechos de defensa, información y presunción de inocencia de las personas implicadas.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>3. Canales previstos</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Canal escrito habilitado por medios telemáticos.</li><li>Posibilidad de solicitar comunicación presencial o por videollamada cuando proceda.</li><li>Canales externos previstos por la normativa aplicable, cuando la persona informante lo considere oportuno.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>4. Ámbito subjetivo</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Podrán utilizarlo, en la medida en que resulte aplicable, personas trabajadoras, profesionales externos, proveedores, socios comerciales, usuarios profesionales de la plataforma y terceros con relación funcional con Compra Captación.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Protección de datos</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El tratamiento de los datos personales vinculados al sistema interno de información deberá ajustarse a la normativa vigente y a una política específica de privacidad del canal, con limitación de acceso, conservación restringida y medidas de seguridad reforzadas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Estado actual</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>En esta URL provisional el canal se mantiene como referencia estructural. Antes del lanzamiento deberá definirse la persona responsable del sistema, la herramienta utilizada, el procedimiento de gestión y la política específica asociada.</p><!-- /wp:paragraph -->
HTML,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $media_replacements = array(
        '/wp-content/themes/captacion-app/media/logo-compra-captacion.png' => captacion_app_media_url('media/logo-compra-captacion.png'),
        '/wp-content/themes/captacion-app/media/property-defaults/edificio-default.jpg' => captacion_app_media_url('media/property-defaults/edificio-default.jpg'),
        '/wp-content/themes/captacion-app/media/property-defaults/piso-default.jpg' => captacion_app_media_url('media/property-defaults/piso-default.jpg'),
        '/wp-content/themes/captacion-app/media/property-defaults/casa-chalet-default.jpg' => captacion_app_media_url('media/property-defaults/casa-chalet-default.jpg'),
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    foreach ($content_map as $slug => $content) {
        $content_map[$slug] = str_replace(array_keys($media_replacements), array_values($media_replacements), $content);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return $content_map;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_seed_pages() {
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return array(
        array('slug' => 'inicio', 'title' => 'Captaciones inmobiliarias', 'content' => $content_map['inicio']),
        array('slug' => 'marketplace', 'title' => 'Marketplace', 'content' => $content_map['marketplace']),
        array('slug' => 'buscar-captaciones', 'title' => 'Buscar captaciones', 'content' => $content_map['buscar-captaciones']),
        array('slug' => 'ofrecer-captacion', 'title' => 'Ofrecer captacion', 'content' => $content_map['ofrecer-captacion']),
        array('slug' => 'como-funciona', 'title' => 'Como funciona', 'content' => $content_map['como-funciona']),
        array('slug' => 'recursos', 'title' => 'Recursos', 'content' => $content_map['recursos']),
        array('slug' => 'planes', 'title' => 'Planes', 'content' => $content_map['planes']),
        array('slug' => 'contacto', 'title' => 'Contacto', 'content' => $content_map['contacto']),
        array('slug' => 'area-privada', 'title' => 'Area privada', 'content' => $content_map['area-privada']),
        array('slug' => 'aviso-legal', 'title' => 'Aviso legal', 'content' => $content_map['aviso-legal']),
        array('slug' => 'privacidad', 'title' => 'Privacidad', 'content' => $content_map['privacidad']),
        array('slug' => 'cookies', 'title' => 'Cookies', 'content' => $content_map['cookies']),
        array('slug' => 'normas-publicacion', 'title' => 'Normas de publicacion', 'content' => $content_map['normas-publicacion']),
        array('slug' => 'condiciones-de-contratacion', 'title' => 'Condiciones de contratacion', 'content' => $content_map['condiciones-de-contratacion']),
        array('slug' => 'canal-de-denuncias', 'title' => 'Canal de denuncias', 'content' => $content_map['canal-de-denuncias']),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rank_math_seo_map() {
    return array(
        'inicio' => array(
            'focus_keyword' => 'captaciones inmobiliarias',
            'title' => 'Captaciones inmobiliarias para profesionales | Compra Captación',
            'description' => 'Marketplace B2B para agentes inmobiliarios: publica captaciones, cruza demandas activas y colabora con acceso protegido y trazabilidad.',
        ),
        'marketplace' => array(
            'focus_keyword' => 'marketplace inmobiliario B2B',
            'title' => 'Marketplace inmobiliario B2B | Captaciones y demandas',
            'description' => 'Revisa captaciones inmobiliarias, demandas de compradores y oportunidades de colaboracion entre agentes en un marketplace B2B protegido.',
        ),
        'buscar-captaciones' => array(
            'focus_keyword' => 'buscar captaciones inmobiliarias',
            'title' => 'Buscar captaciones inmobiliarias para compradores',
            'description' => 'Publica demandas de compradores y encuentra captaciones inmobiliarias compatibles por zona, presupuesto, tipologia y condiciones de colaboracion.',
        ),
        'ofrecer-captacion' => array(
            'focus_keyword' => 'vender captaciones inmobiliarias',
            'title' => 'Vender captaciones inmobiliarias con acceso protegido',
            'description' => 'Publica y monetiza captaciones inmobiliarias con acceso protegido, control de datos sensibles y colaboracion profesional entre agencias.',
        ),
        'como-funciona' => array(
            'focus_keyword' => 'como funciona una plataforma inmobiliaria B2B',
            'title' => 'Como funciona una plataforma inmobiliaria B2B',
            'description' => 'Conoce como funciona Compra Captación: publica captaciones y demandas, detecta coincidencias y colabora con trazabilidad comercial.',
        ),
        'recursos' => array(
            'focus_keyword' => 'herramientas IA para agentes inmobiliarios',
            'title' => 'Herramientas IA para agentes inmobiliarios | Recursos',
            'description' => 'Herramientas IA, calculadoras, plantillas y recursos para agentes inmobiliarios que quieren captar mejor y trabajar con mas productividad.',
        ),
        'planes' => array(
            'focus_keyword' => 'planes para agentes inmobiliarios',
            'title' => 'Planes para agentes inmobiliarios | Compra Captación',
            'description' => 'Compara planes para agentes inmobiliarios: acceso inicial, recursos profesionales, publicacion de captaciones y demandas, y funciones avanzadas.',
        ),
        'contacto' => array(
            'focus_keyword' => 'contacto Compra Captación',
            'title' => 'Contacto Compra Captación | Acceso, planes y colaboraciones',
            'description' => 'Contacta con Compra Captación para solicitar acceso, resolver dudas sobre planes o proponer colaboraciones inmobiliarias profesionales.',
        ),
        'area-privada' => array(
            'focus_keyword' => 'area privada inmobiliaria',
            'title' => 'Area privada inmobiliaria | Gestion de captaciones y demandas',
            'description' => 'Area privada para gestionar captaciones, demandas, favoritos, solicitudes, alertas y trazabilidad de operaciones inmobiliarias.',
        ),
        'aviso-legal' => array(
            'focus_keyword' => 'aviso legal Compra Captación',
            'title' => 'Aviso legal Compra Captación',
            'description' => 'Aviso legal de Compra Captación con informacion del titular, condiciones de uso, responsabilidades y datos pendientes de validacion final.',
        ),
        'privacidad' => array(
            'focus_keyword' => 'politica de privacidad Compra Captación',
            'title' => 'Politica de privacidad Compra Captación',
            'description' => 'Politica de privacidad de Compra Captación para tratamiento de datos, finalidades, derechos, seguridad y acceso profesional a la plataforma.',
        ),
        'cookies' => array(
            'focus_keyword' => 'politica de cookies Compra Captación',
            'title' => 'Politica de cookies Compra Captación',
            'description' => 'Politica de cookies de Compra Captación con informacion sobre cookies necesarias, estadisticas, marketing y consentimiento mediante Complianz.',
        ),
        'normas-publicacion' => array(
            'focus_keyword' => 'normas de publicacion inmobiliaria',
            'title' => 'Normas de publicacion inmobiliaria | Compra Captación',
            'description' => 'Normas para publicar captaciones y demandas inmobiliarias con calidad, confidencialidad, legalidad y respeto entre profesionales.',
        ),
        'condiciones-de-contratacion' => array(
            'focus_keyword' => 'condiciones de contratacion Compra Captación',
            'title' => 'Condiciones de contratacion Compra Captación',
            'description' => 'Condiciones de contratacion de Compra Captación para planes, servicios, pagos, activacion, obligaciones y uso profesional de la plataforma.',
        ),
        'canal-de-denuncias' => array(
            'focus_keyword' => 'canal de denuncias Compra Captación',
            'title' => 'Canal de denuncias Compra Captación',
            'description' => 'Canal de denuncias de Compra Captación para comunicar incumplimientos, irregularidades o riesgos con confidencialidad y proteccion.',
        ),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_apply_rank_math_meta($post_id, $slug) {
    $seo_map = captacion_app_rank_math_seo_map();
    if (!isset($seo_map[$slug])) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $seo = $seo_map[$slug];
    update_post_meta($post_id, 'rank_math_focus_keyword', $seo['focus_keyword']);
    update_post_meta($post_id, 'rank_math_title', $seo['title']);
    update_post_meta($post_id, 'rank_math_description', $seo['description']);
    update_post_meta($post_id, 'rank_math_pillar_content', 'off');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_create_editable_pages() {
    if (!current_user_can('manage_options') || !check_admin_referer('captacion_app_create_pages')) {
        wp_die('No autorizado');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $created = 0;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    foreach (captacion_app_seed_pages() as $page) {
        $existing = get_page_by_path($page['slug'], OBJECT, 'page');
        $data = array(
            'post_title' => $page['title'],
            'post_name' => $page['slug'],
            'post_content' => $page['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        if ($existing) {
            $data['ID'] = $existing->ID;
            $post_id = wp_update_post($data);
            $updated++;
        } else {
            $post_id = wp_insert_post($data);
            $created++;
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        if (!is_wp_error($post_id) && $post_id) {
            captacion_app_apply_rank_math_meta((int) $post_id, $page['slug']);
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    wp_safe_redirect(add_query_arg(array(
        'page' => 'captacion-app-settings',
        'captacion_pages_created' => $created,
        'captacion_pages_updated' => $updated,
    ), admin_url('admin.php')));
    exit;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_prepare_production_cleanup() {
    if (!current_user_can('manage_options') || !check_admin_referer('captacion_app_prepare_production')) {
        wp_die('No autorizado');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    captacion_app_maybe_install_records_table();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    global $wpdb;
    $now = current_time('mysql');
    $records_table = captacion_app_records_table_name();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $records_updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$records_table}
         SET deleted_at = %s, status = CASE WHEN status = '' THEN 'deleted' ELSE status END
         WHERE deleted_at IS NULL
           AND (is_demo = 1 OR privacy_scope = 'global_demo' OR data_origin IN ('seed_demo','synthetic_demo','demo'))",
        $now
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $batches_updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$batches_table}
         SET deleted_at = %s, status = 'deleted', updated_at = %s
         WHERE deleted_at IS NULL
           AND (is_demo = 1 OR privacy_scope = 'global_demo' OR data_origin IN ('seed_demo','synthetic_demo','demo'))",
        $now,
        $now
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    wp_safe_redirect(add_query_arg(array(
        'page' => 'captacion-app-settings',
        'captacion_cleanup_records' => max(0, absint($records_updated)),
        'captacion_cleanup_batches' => max(0, absint($batches_updated)),
    ), admin_url('admin.php')));
    exit;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_reset_day_one() {
    if (!current_user_can('manage_options') || !check_admin_referer('captacion_app_reset_day_one')) {
        wp_die('No autorizado');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $confirm = sanitize_text_field(wp_unslash($_POST['captacion_reset_confirm'] ?? ''));
    $password = (string) wp_unslash($_POST['captacion_reset_admin_password'] ?? '');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($confirm !== 'RESET') {
        wp_die('Debes escribir RESET para ejecutar esta accion.');
    }
    if (strlen($password) < 8) {
        wp_die('La contrasena del administrador SaaS debe tener al menos 8 caracteres.');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    captacion_app_maybe_install_mail_events_table();
    captacion_app_maybe_install_records_table();
    captacion_app_maybe_install_import_batches_table();
    captacion_app_maybe_install_resource_events_table();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    global $wpdb;
    $tables = array(
        captacion_app_records_table_name(),
        captacion_app_import_batches_table_name(),
        captacion_app_access_log_table_name(),
        captacion_app_events_table_name(),
        captacion_app_resource_events_table_name(),
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $tables_reset = 0;
    foreach ($tables as $table) {
        $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));
        if ($exists === $table) {
            $wpdb->query("TRUNCATE TABLE {$table}");
            $tables_reset++;
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    require_once ABSPATH . 'wp-admin/includes/user.php';
    $current_user_id = get_current_user_id();
    $admin_user = get_user_by('email', $admin_email);
    $admin_user_id = $admin_user ? absint($admin_user->ID) : 0;
    $saas_user_ids = $wpdb->get_col("SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key LIKE 'captacion\_%'");
    $users_deleted = 0;
    foreach ($saas_user_ids as $user_id) {
        $user_id = absint($user_id);
        if (!$user_id || $user_id === $current_user_id || ($admin_user_id && $user_id === $admin_user_id)) continue;
        if (wp_delete_user($user_id, $current_user_id)) $users_deleted++;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($admin_user_id) {
        wp_update_user(array('ID' => $admin_user_id, 'user_email' => $admin_email, 'display_name' => 'Administrador SaaS'));
        wp_set_password($password, $admin_user_id);
    } else {
        $login = sanitize_user(current(explode('@', $admin_email)), true) ?: 'inmobia360';
        if (username_exists($login)) $login = 'inmobia360_saas';
        $admin_user_id = wp_insert_user(array(
            'user_login' => $login,
            'user_email' => $admin_email,
            'user_pass' => $password,
            'display_name' => 'Administrador SaaS',
            'role' => 'subscriber',
        ));
        if (is_wp_error($admin_user_id)) {
            wp_die($admin_user_id->get_error_message());
        }
        $admin_user_id = absint($admin_user_id);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    update_user_meta($admin_user_id, 'captacion_email_verified', '1');
    update_user_meta($admin_user_id, 'captacion_plan_type', 'premium');
    update_user_meta($admin_user_id, 'captacion_subscription_status', 'active');
    update_user_meta($admin_user_id, 'captacion_included_marketplace_accesses', 999999);
    update_user_meta($admin_user_id, 'captacion_used_marketplace_accesses', 0);
    update_user_meta($admin_user_id, 'captacion_extra_marketplace_accesses', 0);
    update_user_meta($admin_user_id, 'captacion_commercial_consent', '1');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    wp_safe_redirect(add_query_arg(array(
        'page' => 'captacion-app-settings',
        'captacion_reset_day_one' => 1,
        'captacion_reset_tables' => $tables_reset,
        'captacion_reset_users' => $users_deleted,
        'captacion_reset_admin' => $admin_email,
    ), admin_url('admin.php')));
    exit;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_is_configured_stripe_link($url) {
    return is_string($url)
        && preg_match('#^https://(buy|checkout)\.stripe\.com/#', $url)
        && strpos($url, 'REEMPLAZA_') === false;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_stripe_link_for_plan($plan) {
    $settings = captacion_app_settings();
    $map = array(
        'initial' => 'stripe_membership_initial_link',
        'initial_annual' => 'stripe_membership_initial_annual_link',
        'professional' => 'stripe_membership_professional_link',
        'agency' => 'stripe_membership_agency_link',
    );
    $key = $map[$plan] ?? '';
    return $key ? ($settings[$key] ?? '') : '';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_stripe_plan_button($plan, $label) {
    $url = captacion_app_stripe_link_for_plan($plan);
    if (captacion_app_is_configured_stripe_link($url)) {
        return '<a class="captacion-stripe-button" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $contact_email = captacion_app_setting('contact_email');
    $subjects = array(
        'initial' => 'Quiero activar Starter de Compra Captación',
        'professional' => 'Quiero informacion del plan Professional de Compra Captación',
        'agency' => 'Quiero informacion del plan Premium de Compra Captación',
    );
    $fallback_label = array(
        'initial' => 'Solicitar acceso',
        'professional' => 'Solicitar acceso comercial',
        'agency' => 'Hablar con ventas',
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($contact_email) {
        $mailto = 'mailto:' . rawurlencode($contact_email) . '?subject=' . rawurlencode($subjects[$plan] ?? 'Consulta sobre Compra Captación');
        return '<a class="captacion-stripe-button" href="' . esc_url($mailto) . '">' . esc_html($fallback_label[$plan] ?? 'Solicitar informacion') . '</a>';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return '<a class="captacion-stripe-button" href="' . esc_url(home_url('/contacto/')) . '">' . esc_html($fallback_label[$plan] ?? 'Solicitar informacion') . '</a>';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_stripe_plans_shortcode() {
    ob_start();
    ?>
    <div class="captacion-plans-grid">
        <section class="captacion-plan-card">
            <span class="captacion-plan-kicker">Starter</span>
            <h3>Starter</h3>
            <p class="captacion-plan-price">19 &euro; <small>/ mes</small></p>
            <p>3 meses gratis. Incluye 3 accesos al mes, buscador, publicaciones y dashboard básico.</p>
            <?php echo captacion_app_stripe_plan_button('initial', 'Empezar prueba gratis'); ?>
        </section>
        <section class="captacion-plan-card captacion-plan-card-featured">
            <span class="captacion-plan-kicker">Profesional</span>
            <h3>Profesional</h3>
            <p class="captacion-plan-price">29 &euro; <small>/ mes</small></p>
            <p>20 accesos mensuales, dashboard profesional, alertas y packs de 10 accesos por 5 EUR.</p>
            <?php echo captacion_app_stripe_plan_button('professional', 'Activar Profesional'); ?>
        </section>
        <section class="captacion-plan-card">
            <span class="captacion-plan-kicker">Premium</span>
            <h3>Premium</h3>
            <p class="captacion-plan-price">49 &euro; <small>/ mes</small></p>
            <p>30 accesos mensuales, dashboard completo y packs de 15 accesos por 5 EUR.</p>
            <?php echo captacion_app_stripe_plan_button('agency', 'Activar Premium'); ?>
        </section>
    </div>
    <?php
    return ob_get_clean();
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_providers() {
    return array(
        'openai' => array(
            'label' => 'OpenAI',
            'default_model' => 'gpt-4o-mini',
            'transport' => 'openai_compatible',
            'endpoint' => 'https://api.openai.com/v1/chat/completions',
        ),
        'anthropic' => array(
            'label' => 'Anthropic',
            'default_model' => 'claude-3-5-haiku-latest',
            'transport' => 'anthropic',
            'endpoint' => 'https://api.anthropic.com/v1/messages',
        ),
        'google' => array(
            'label' => 'Google',
            'default_model' => 'gemini-2.0-flash',
            'transport' => 'google',
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent',
        ),
        'groq' => array(
            'label' => 'Groq',
            'default_model' => 'llama-3.1-8b-instant',
            'transport' => 'openai_compatible',
            'endpoint' => 'https://api.groq.com/openai/v1/chat/completions',
        ),
        'openrouter' => array(
            'label' => 'OpenRouter',
            'default_model' => 'openai/gpt-4o-mini',
            'transport' => 'openai_compatible',
            'endpoint' => 'https://openrouter.ai/api/v1/chat/completions',
        ),
        'compatible' => array(
            'label' => 'Endpoint compatible',
            'default_model' => 'modelo-personalizado',
            'transport' => 'openai_compatible',
            'endpoint' => '',
        ),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_user_meta_key() {
    return 'captacion_app_ai_connection_v1';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_encrypt_secret($secret) {
    $secret = (string) $secret;
    if ($secret === '') {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $key = hash('sha256', wp_salt('secure_auth'), true);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    if (!$iv_length) {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $iv = random_bytes($iv_length);
    $ciphertext = openssl_encrypt($secret, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext === false) {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return base64_encode($iv . $ciphertext);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_decrypt_secret($payload) {
    $payload = (string) $payload;
    if ($payload === '') {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $raw = base64_decode($payload, true);
    if ($raw === false) {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $key = hash('sha256', wp_salt('secure_auth'), true);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    if (!$iv_length || strlen($raw) <= $iv_length) {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $iv = substr($raw, 0, $iv_length);
    $ciphertext = substr($raw, $iv_length);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return $plaintext === false ? '' : $plaintext;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_secret_fingerprint($secret) {
    $secret = trim((string) $secret);
    if ($secret === '') {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $suffix = substr($secret, -4);
    return '•••• ' . $suffix;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_sanitize_connection_payload($input, $existing = array()) {
    $providers = captacion_app_ai_providers();
    $provider = sanitize_key($input['provider'] ?? ($existing['provider'] ?? 'openai'));
    if (!isset($providers[$provider])) {
        return new WP_Error('captacion_ai_invalid_provider', 'Proveedor de IA no válido.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $alias = sanitize_text_field(wp_unslash($input['alias'] ?? ($existing['alias'] ?? '')));
    if ($alias === '') {
        $alias = $providers[$provider]['label'];
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $profile = sanitize_key($input['profile'] ?? ($existing['profile'] ?? 'general'));
    $allowed_profiles = array('general', 'copywriting', 'matching', 'documentos', 'automatizacion');
    if (!in_array($profile, $allowed_profiles, true)) {
        $profile = 'general';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $model = sanitize_text_field(wp_unslash($input['model'] ?? ($existing['model'] ?? $providers[$provider]['default_model'])));
    if ($model === '') {
        $model = $providers[$provider]['default_model'];
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $endpoint = esc_url_raw(wp_unslash($input['endpoint'] ?? ($existing['endpoint'] ?? '')));
    if ($provider !== 'compatible') {
        $endpoint = $providers[$provider]['endpoint'];
    } elseif ($endpoint === '') {
        return new WP_Error('captacion_ai_missing_endpoint', 'Debes indicar un endpoint compatible con OpenAI.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $secret = isset($input['api_key']) ? trim((string) wp_unslash($input['api_key'])) : '';
    $encrypted_secret = $existing['encrypted_secret'] ?? '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($secret !== '') {
        $encrypted_secret = captacion_app_ai_encrypt_secret($secret);
        if ($encrypted_secret === '') {
            return new WP_Error('captacion_ai_secret_error', 'No se pudo proteger la credencial de IA.', array('status' => 500));
        }
        $fingerprint = captacion_app_ai_secret_fingerprint($secret);
    } elseif (empty($encrypted_secret)) {
        return new WP_Error('captacion_ai_missing_secret', 'Debes indicar una API key o credencial válida.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return array(
        'provider' => $provider,
        'provider_label' => $providers[$provider]['label'],
        'alias' => $alias,
        'profile' => $profile,
        'model' => $model,
        'endpoint' => $endpoint,
        'transport' => $providers[$provider]['transport'],
        'encrypted_secret' => $encrypted_secret,
        'fingerprint' => $fingerprint,
        'active' => isset($input['active']) ? (bool) $input['active'] : (bool) ($existing['active'] ?? true),
        'status' => sanitize_key($existing['status'] ?? 'configured'),
        'last_validated_at' => isset($existing['last_validated_at']) ? absint($existing['last_validated_at']) : 0,
        'last_error' => sanitize_text_field($existing['last_error'] ?? ''),
        'created_at' => isset($existing['created_at']) ? absint($existing['created_at']) : time(),
        'updated_at' => time(),
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_get_user_connection($user_id, $with_secret = false) {
    $stored = get_user_meta($user_id, captacion_app_ai_user_meta_key(), true);
    if (!is_array($stored) || empty($stored['provider'])) {
        return null;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $response = array(
        'provider' => sanitize_key($stored['provider']),
        'provider_label' => sanitize_text_field($stored['provider_label'] ?? ''),
        'alias' => sanitize_text_field($stored['alias'] ?? ''),
        'profile' => sanitize_key($stored['profile'] ?? 'general'),
        'model' => sanitize_text_field($stored['model'] ?? ''),
        'endpoint' => esc_url_raw($stored['endpoint'] ?? ''),
        'transport' => sanitize_key($stored['transport'] ?? 'openai_compatible'),
        'fingerprint' => sanitize_text_field($stored['fingerprint'] ?? ''),
        'active' => !empty($stored['active']),
        'status' => sanitize_key($stored['status'] ?? 'configured'),
        'last_validated_at' => absint($stored['last_validated_at'] ?? 0),
        'last_error' => sanitize_text_field($stored['last_error'] ?? ''),
        'created_at' => absint($stored['created_at'] ?? 0),
        'updated_at' => absint($stored['updated_at'] ?? 0),
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($with_secret) {
        $response['encrypted_secret'] = (string) ($stored['encrypted_secret'] ?? '');
        $response['api_key'] = captacion_app_ai_decrypt_secret($response['encrypted_secret']);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return $response;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_save_user_connection($user_id, $connection) {
    update_user_meta($user_id, captacion_app_ai_user_meta_key(), $connection);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_delete_user_connection($user_id) {
    delete_user_meta($user_id, captacion_app_ai_user_meta_key());
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_log($message, $context = array()) {
    $payload = array();
    foreach ((array) $context as $key => $value) {
        if (stripos((string) $key, 'secret') !== false || stripos((string) $key, 'key') !== false) {
            continue;
        }
        $payload[$key] = is_scalar($value) ? $value : wp_json_encode($value, JSON_UNESCAPED_UNICODE);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    error_log('[Compra Captación AI] ' . $message . ' ' . wp_json_encode($payload, JSON_UNESCAPED_UNICODE));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_normalize_request_context($context) {
    if (is_string($context)) {
        return trim($context);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (empty($context)) {
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_build_user_prompt($prompt, $context = array()) {
    $prompt = trim((string) $prompt);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($context_string === '') {
        return $prompt;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return $prompt . "\n\nContexto adicional:\n" . $context_string;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_extract_text_from_response($provider, $body) {
    if ($provider === 'anthropic') {
        if (!empty($body['content']) && is_array($body['content'])) {
            $parts = array();
            foreach ($body['content'] as $item) {
                if (($item['type'] ?? '') === 'text' && !empty($item['text'])) {
                    $parts[] = $item['text'];
                }
            }
            return trim(implode("\n", $parts));
        }
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($provider === 'google') {
        if (!empty($body['candidates'][0]['content']['parts']) && is_array($body['candidates'][0]['content']['parts'])) {
            $parts = array();
            foreach ($body['candidates'][0]['content']['parts'] as $item) {
                if (!empty($item['text'])) {
                    $parts[] = $item['text'];
                }
            }
            return trim(implode("\n", $parts));
        }
        return '';
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!empty($body['choices'][0]['message']['content'])) {
        return trim((string) $body['choices'][0]['message']['content']);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!empty($body['choices'][0]['text'])) {
        return trim((string) $body['choices'][0]['text']);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return '';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_provider_request($connection, $payload) {
    $provider = $connection['provider'];
    $api_key = $connection['api_key'] ?? '';
    $system_instruction = trim((string) ($payload['system_instruction'] ?? ''));
    $prompt = trim((string) ($payload['prompt'] ?? ''));
    $context = $payload['context'] ?? array();
    $temperature = isset($payload['temperature']) ? (float) $payload['temperature'] : 0.3;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($api_key === '') {
        return new WP_Error('captacion_ai_missing_runtime_secret', 'No hay una credencial válida almacenada para este usuario.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $user_prompt = captacion_app_ai_build_user_prompt($prompt, $context);
    $timeout = 35;
    $headers = array();
    $request_body = array();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($provider === 'anthropic') {
        $headers = array(
            'Content-Type' => 'application/json',
            'x-api-key' => $api_key,
            'anthropic-version' => '2023-06-01',
        );
        $request_body = array(
            'model' => $connection['model'],
            'max_tokens' => max(128, $max_tokens),
            'temperature' => $temperature,
            'messages' => array(
                array(
                    'role' => 'user',
                    'content' => $user_prompt,
                ),
            ),
        );
        if ($system_instruction !== '') {
            $request_body['system'] = $system_instruction;
        }
    } elseif ($provider === 'google') {
        $endpoint = sprintf($endpoint, rawurlencode($connection['model'])) . '?key=' . rawurlencode($api_key);
        $headers = array(
            'Content-Type' => 'application/json',
        );
        $full_prompt = $system_instruction !== '' ? $system_instruction . "\n\n" . $user_prompt : $user_prompt;
        $request_body = array(
            'contents' => array(
                array(
                    'parts' => array(
                        array('text' => $full_prompt),
                    ),
                ),
            ),
            'generationConfig' => array(
                'temperature' => $temperature,
                'maxOutputTokens' => max(128, $max_tokens),
            ),
        );
    } else {
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $api_key,
        );
        if ($provider === 'openrouter') {
            $headers['HTTP-Referer'] = home_url('/');
            $headers['X-Title'] = 'Compra Captación';
        }
        $messages = array();
        if ($system_instruction !== '') {
            $messages[] = array(
                'role' => 'system',
                'content' => $system_instruction,
            );
        }
        $messages[] = array(
            'role' => 'user',
            'content' => $user_prompt,
        );
        $request_body = array(
            'model' => $connection['model'],
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => max(128, $max_tokens),
        );
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $response = wp_remote_post($endpoint, array(
        'timeout' => $timeout,
        'headers' => $headers,
        'body' => wp_json_encode($request_body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (is_wp_error($response)) {
        return new WP_Error('captacion_ai_transport_error', 'No se pudo conectar con el proveedor de IA.', array(
            'status' => 502,
            'provider_message' => $response->get_error_message(),
        ));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $raw_body = wp_remote_retrieve_body($response);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($status_code < 200 || $status_code >= 300) {
        $provider_message = '';
        if (!empty($decoded['error']['message'])) {
            $provider_message = (string) $decoded['error']['message'];
        } elseif (!empty($decoded['message'])) {
            $provider_message = (string) $decoded['message'];
        } elseif (is_string($raw_body) && $raw_body !== '') {
            $provider_message = wp_strip_all_tags($raw_body);
        'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

        return new WP_Error('captacion_ai_provider_error', 'El proveedor de IA devolvió un error.', array(
            'status' => $status_code ?: 502,
            'provider_message' => $provider_message,
        ));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $text = captacion_app_ai_extract_text_from_response($provider, is_array($decoded) ? $decoded : array());
    if ($text === '') {
        return new WP_Error('captacion_ai_empty_response', 'La respuesta del proveedor llegó vacía o no se pudo interpretar.', array('status' => 502));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return array(
        'provider' => $provider,
        'model' => $connection['model'],
        'text' => $text,
        'raw' => $decoded,
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_test_connection($connection) {
    return captacion_app_ai_provider_request($connection, array(
        'prompt' => 'Responde solo con la palabra OK.',
        'system_instruction' => 'Responde de forma mínima.',
        'context' => array('purpose' => 'connection_test'),
        'temperature' => 0,
        'max_tokens' => 20,
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_permission() {
    return is_user_logged_in();
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_get_config(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $connection = captacion_app_ai_get_user_connection($user_id, false);
    return rest_ensure_response(array(
        'connected' => !empty($connection),
        'connection' => $connection,
        'providers' => captacion_app_ai_providers(),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_save_config(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $existing = captacion_app_ai_get_user_connection($user_id, true);
    $sanitized = captacion_app_ai_sanitize_connection_payload($request->get_json_params(), is_array($existing) ? $existing : array());
    if (is_wp_error($sanitized)) {
        return $sanitized;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    captacion_app_ai_save_user_connection($user_id, $sanitized);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'saved' => true,
        'connection' => captacion_app_ai_get_user_connection($user_id, false),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_delete_config(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    captacion_app_ai_delete_user_connection($user_id);
    captacion_app_ai_log('Configuración IA eliminada.', array('user_id' => $user_id));
    return rest_ensure_response(array('deleted' => true));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_set_status(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $existing = captacion_app_ai_get_user_connection($user_id, true);
    if (!$existing) {
        return new WP_Error('captacion_ai_not_configured', 'No hay una conexión IA configurada para este usuario.', array('status' => 404));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $existing['active'] = (bool) $request->get_param('active');
    $existing['updated_at'] = time();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'updated' => true,
        'connection' => captacion_app_ai_get_user_connection($user_id, false),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_test(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $existing = captacion_app_ai_get_user_connection($user_id, true);
    if (!$existing) {
        return new WP_Error('captacion_ai_not_configured', 'Primero debes guardar una conexión IA.', array('status' => 404));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $result = captacion_app_ai_test_connection($existing);
    if (is_wp_error($result)) {
        $existing['status'] = 'error';
        $existing['last_error'] = sanitize_text_field($result->get_error_message());
        $existing['updated_at'] = time();
        captacion_app_ai_save_user_connection($user_id, $existing);
        captacion_app_ai_log('Prueba de conexión IA fallida.', array(
            'user_id' => $user_id,
            'provider' => $existing['provider'],
            'error' => $result->get_error_message(),
        ));
        return $result;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $existing['status'] = 'connected';
    $existing['last_error'] = '';
    $existing['last_validated_at'] = time();
    $existing['updated_at'] = time();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Conexión validada correctamente.',
        'connection' => captacion_app_ai_get_user_connection($user_id, false),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_request(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $connection = captacion_app_ai_get_user_connection($user_id, true);
    if (!$connection || empty($connection['active'])) {
        return new WP_Error('captacion_ai_not_connected', 'No tienes una conexión IA activa. Configúrala en el área privada.', array('status' => 409));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $params = $request->get_json_params();
    $prompt = trim((string) ($params['prompt'] ?? ''));
    if ($prompt === '') {
        return new WP_Error('captacion_ai_missing_prompt', 'La solicitud no incluye un prompt válido.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $payload = array(
        'prompt' => $prompt,
        'system_instruction' => (string) ($params['system_instruction'] ?? ''),
        'context' => $params['context'] ?? array(),
        'temperature' => $params['temperature'] ?? 0.3,
        'max_tokens' => $params['max_tokens'] ?? 700,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $result = captacion_app_ai_provider_request($connection, $payload);
    if (is_wp_error($result)) {
        $connection['status'] = 'error';
        $connection['last_error'] = sanitize_text_field($result->get_error_message());
        $connection['updated_at'] = time();
        captacion_app_ai_save_user_connection($user_id, $connection);
        captacion_app_ai_log('Solicitud IA fallida.', array(
            'user_id' => $user_id,
            'provider' => $connection['provider'],
            'task_type' => sanitize_key($params['task_type'] ?? 'general'),
            'error' => $result->get_error_message(),
        ));
        return $result;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $connection['status'] = 'connected';
    $connection['last_error'] = '';
    $connection['last_validated_at'] = time();
    $connection['updated_at'] = time();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'success' => true,
        'provider' => $result['provider'],
        'model' => $result['model'],
        'text' => $result['text'],
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_ai_rest_routes() {
    register_rest_route('captacion-app/v1', '/ai/config', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'captacion_app_ai_rest_get_config',
            'permission_callback' => 'captacion_app_ai_rest_permission',
        ),
        array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => 'captacion_app_ai_rest_save_config',
            'permission_callback' => 'captacion_app_ai_rest_permission',
        ),
        array(
            'methods' => WP_REST_Server::DELETABLE,
            'callback' => 'captacion_app_ai_rest_delete_config',
            'permission_callback' => 'captacion_app_ai_rest_permission',
        ),
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    register_rest_route('captacion-app/v1', '/ai/config/status', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_set_status',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    register_rest_route('captacion-app/v1', '/ai/test', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_test',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    register_rest_route('captacion-app/v1', '/ai/request', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_request',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    register_rest_route('captacion-app/v1', '/ai/admin-request', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_admin_request',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    register_rest_route('captacion-app/v1', '/ai/match-explanation', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_match_explanation',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    register_rest_route('captacion-app/v1', '/ai/enhance-listing', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_enhance_listing',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    ));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_admin_is_configured() {
    $settings = captacion_app_settings();
    return !empty($settings['ai_admin_provider']) && !empty($settings['ai_admin_api_key']);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_admin_get_connection() {
    if (!captacion_app_ai_admin_is_configured()) {
        return null;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $settings = captacion_app_settings();
    $providers = captacion_app_ai_providers();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!isset($providers[$provider])) {
        return null;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $model = sanitize_text_field($settings['ai_admin_model']);
    if ($model === '') {
        $model = $providers[$provider]['default_model'];
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return array(
        'provider' => $provider,
        'provider_label' => $providers[$provider]['label'],
        'api_key' => $settings['ai_admin_api_key'],
        'model' => $model,
        'endpoint' => $providers[$provider]['endpoint'],
        'transport' => $providers[$provider]['transport'],
        'active' => true,
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_admin_request($payload) {
    $connection = captacion_app_ai_admin_get_connection();
    if (!$connection) {
        return new WP_Error('captacion_ai_admin_not_configured', 'La IA centralizada no está configurada. El administrador debe configurar un proveedor en Ajustes de Compra Captación.', array('status' => 412));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $defaults = array(
        'prompt' => '',
        'system_instruction' => '',
        'context' => array(),
        'temperature' => 0.3,
        'max_tokens' => 700,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return captacion_app_ai_provider_request($connection, wp_parse_args($payload, $defaults));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_admin_request(WP_REST_Request $request) {
    if (!captacion_app_ai_admin_is_configured()) {
        return new WP_Error('captacion_ai_admin_not_configured', 'La IA centralizada no está configurada.', array('status' => 412));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $params = $request->get_json_params();
    $prompt = trim((string) ($params['prompt'] ?? ''));
    if ($prompt === '') {
        return new WP_Error('captacion_ai_missing_prompt', 'La solicitud no incluye un prompt válido.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $payload = array(
        'prompt' => $prompt,
        'system_instruction' => (string) ($params['system_instruction'] ?? ''),
        'context' => $params['context'] ?? array(),
        'temperature' => $params['temperature'] ?? 0.3,
        'max_tokens' => $params['max_tokens'] ?? 700,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $result = captacion_app_ai_admin_request($payload);
    if (is_wp_error($result)) {
        return $result;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    captacion_app_ai_log('Solicitud IA centralizada completada.', array(
        'user_id' => get_current_user_id(),
        'provider' => $result['provider'],
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'success' => true,
        'provider' => $result['provider'],
        'model' => $result['model'],
        'text' => $result['text'],
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_match_explanation(WP_REST_Request $request) {
    if (!captacion_app_ai_admin_is_configured()) {
        return new WP_Error('captacion_ai_admin_not_configured', 'La IA centralizada no está configurada.', array('status' => 412));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $params = $request->get_json_params();
    $property = $params['property'] ?? array();
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (empty($property) || empty($need)) {
        return new WP_Error('captacion_ai_missing_data', 'Debes proporcionar datos de propiedad y demanda.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $propertyTitle = sanitize_text_field($property['title'] ?? $property['property_title'] ?? 'Propiedad');
    $propertyType = sanitize_text_field($property['property_type'] ?? $property['type'] ?? '');
    $propertyPrice = isset($property['indicative_price']) ? (float) $property['indicative_price'] : (float) ($property['price'] ?? 0);
    $propertyBedrooms = (int) ($property['rooms'] ?? $property['bedrooms'] ?? 0);
    $propertyBathrooms = (int) ($property['bathrooms'] ?? 0);
    $propertySurface = (float) ($property['total_area_m2'] ?? $property['surface'] ?? 0);
    $propertyProvince = sanitize_text_field($property['province'] ?? $property['location'] ?? '');
    $propertyMunicipality = sanitize_text_field($property['municipality'] ?? '');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $needTitle = sanitize_text_field($need['title'] ?? $need['need_title'] ?? 'Demanda');
    $needType = sanitize_text_field($need['property_type'] ?? $need['type'] ?? '');
    $needBudget = isset($need['max_budget']) ? (float) $need['max_budget'] : (float) ($need['budget'] ?? 0);
    $needBedrooms = (int) ($need['min_rooms'] ?? $need['bedrooms'] ?? 0);
    $needBathrooms = (int) ($need['min_bathrooms'] ?? $need['bathrooms'] ?? 0);
    $needSurface = (float) ($need['desired_area_min_m2'] ?? $need['surface'] ?? 0);
    $needProvince = sanitize_text_field($need['province'] ?? $need['location'] ?? '');
    $needMunicipality = sanitize_text_field($need['municipality'] ?? '');
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $prompt = "Genera una explicacion breve y profesional (maximo 3 lineas) de por que esta propiedad coincide con esta demanda inmobiliaria.\n\n";
    $prompt .= "PROPIEDAD:\n";
    $prompt .= "- Titulo: {$propertyTitle}\n";
    $prompt .= "- Tipo: {$propertyType}\n";
    $prompt .= "- Precio: " . number_format($propertyPrice, 0, ',', '.') . " EUR\n";
    if ($propertyBedrooms) { $prompt .= "- Habitaciones: {$propertyBedrooms}\n"; }
    if ($propertyBathrooms) { $prompt .= "- Banos: {$propertyBathrooms}\n"; }
    if ($propertySurface) { $prompt .= "- Superficie: {$propertySurface} m2\n"; }
    $prompt .= "- Ubicacion: " . implode(', ', array_filter(array($propertyProvince, $propertyMunicipality, $propertyPostalCode))) . "\n\n";
    $prompt .= "DEMANDA:\n";
    $prompt .= "- Titulo: {$needTitle}\n";
    $prompt .= "- Tipo: {$needType}\n";
    $prompt .= "- Presupuesto maximo: " . number_format($needBudget, 0, ',', '.') . " EUR\n";
    if ($needBedrooms) { $prompt .= "- Habitaciones minimas: {$needBedrooms}\n"; }
    if ($needBathrooms) { $prompt .= "- Banos minimos: {$needBathrooms}\n"; }
    if ($needSurface) { $prompt .= "- Superficie minima: {$needSurface} m2\n"; }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $result = captacion_app_ai_admin_request(array(
        'prompt' => $prompt,
        'system_instruction' => $systemInstruction,
        'temperature' => 0.2,
        'max_tokens' => 300,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (is_wp_error($result)) {
        captacion_app_ai_log('Error generando explicacion de match.', array(
            'error' => $result->get_error_message(),
        ));
        return $result;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'success' => true,
        'explanation' => $result['text'],
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_ai_rest_enhance_listing(WP_REST_Request $request) {
    if (!captacion_app_ai_admin_is_configured()) {
        return new WP_Error('captacion_ai_admin_not_configured', 'La IA centralizada no esta configurada.', array('status' => 412));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $params = $request->get_json_params();
    $current_title = trim((string) ($params['title'] ?? ''));
    $current_description = trim((string) ($params['description'] ?? ''));
    $property_type = sanitize_text_field($params['property_type'] ?? $params['type'] ?? 'inmueble');
    $location = sanitize_text_field($params['location'] ?? '');
    $price = isset($params['price']) ? (float) $params['price'] : 0;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($current_title === '' && $current_description === '') {
        return new WP_Error('captacion_ai_missing_content', 'Proporciona al menos un titulo o descripcion actual para mejorar.', array('status' => 400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $prompt = "Eres un copywriter inmobiliario experto en marketing digital B2B.\n\n";
    $prompt .= "Mejora el titulo y la descripcion de este inmueble para hacerlos mas atractivos, profesionales y efectivos en un marketplace B2B entre agentes inmobiliarios.\n\n";
    $prompt .= "TIPO: {$property_type}\n";
    if ($location) { $prompt .= "UBICACION: {$location}\n"; }
    if ($price) { $prompt .= "PRECIO: " . number_format($price, 0, ',', '.') . " EUR\n"; }
    if ($features) { $prompt .= "CARACTERISTICAS: {$features}\n"; }
    $prompt .= "\nTITULO ACTUAL: {$current_title}\n";
    $prompt .= "DESCRIPCION ACTUAL: {$current_description}\n\n";
    $prompt .= "Devuelve SOLO el siguiente formato JSON sin explicaciones:\n";
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $result = captacion_app_ai_admin_request(array(
        'prompt' => $prompt,
        'system_instruction' => $systemInstruction,
        'temperature' => 0.3,
        'max_tokens' => 600,
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (is_wp_error($result)) {
        captacion_app_ai_log('Error mejorando listing.', array(
            'error' => $result->get_error_message(),
        ));
        return $result;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $json_text = trim($result['text']);
    $json_text = preg_replace('/^```(?:json)?\s*|\s*```$/i', '', $json_text);
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if (!is_array($parsed) || empty($parsed['title'])) {
        $parsed = array(
            'title' => $current_title ?: $property_type . ' en ' . ($location ?: 'venta'),
            'description' => $current_description ?: $json_text,
        );
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return rest_ensure_response(array(
        'success' => true,
        'title' => sanitize_text_field($parsed['title']),
        'description' => sanitize_textarea_field($parsed['description'] ?? ''),
        'raw' => $result['text'],
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

/* Official territorial synchronization: INE + CartoCiudad/CNIG. */
function captacion_app_territory_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_territories';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_postal_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_territory_postal_codes';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_install_territory_table() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $table = captacion_app_territory_table_name();
    $postal_table = captacion_app_territory_postal_table_name();
    $charset_collate = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE {$table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        comunidad_codigo VARCHAR(2) NOT NULL,
        comunidad_nombre VARCHAR(190) NOT NULL,
        provincia_codigo VARCHAR(2) NOT NULL,
        provincia_nombre VARCHAR(190) NOT NULL,
        municipio_codigo_ine VARCHAR(5) NOT NULL,
        municipio_nombre VARCHAR(190) NOT NULL,
        codigo_postal VARCHAR(5) NULL,
        source VARCHAR(190) NOT NULL DEFAULT 'INE 2026',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY municipio_codigo_ine (municipio_codigo_ine),
        KEY comunidad_codigo (comunidad_codigo),
        KEY provincia_codigo (provincia_codigo),
        KEY municipio_nombre (municipio_nombre),
        KEY codigo_postal (codigo_postal)
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $legacy_indexes = $wpdb->get_col("SHOW INDEX FROM {$table}", 2);
    foreach (array('level_code','parent_level','name') as $legacy_index) {
        if (in_array($legacy_index, $legacy_indexes, true)) $wpdb->query("ALTER TABLE {$table} DROP INDEX {$legacy_index}");
    }
    $legacy_columns = $wpdb->get_col("SHOW COLUMNS FROM {$table}", 0);
    foreach (array('level','code','parent_code','name','ine_code','extra') as $legacy_column) {
        if (in_array($legacy_column, $legacy_columns, true)) $wpdb->query("ALTER TABLE {$table} DROP COLUMN {$legacy_column}");
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    dbDelta("CREATE TABLE {$postal_table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        municipio_codigo_ine VARCHAR(5) NOT NULL,
        codigo_postal VARCHAR(5) NOT NULL,
        source VARCHAR(190) NOT NULL,
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        UNIQUE KEY municipio_postal (municipio_codigo_ine, codigo_postal),
        KEY codigo_postal (codigo_postal),
        KEY municipio_codigo_ine (municipio_codigo_ine)
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    update_option('captacion_territory_table_version', '20260620');
}
add_action('after_switch_theme', 'captacion_app_install_territory_table');
function captacion_app_maybe_install_territory_table() {
    if (get_option('captacion_territory_table_version') !== '20260620') {
        captacion_app_install_territory_table();
    }
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_prepare_territory_catalog() {
    captacion_app_maybe_install_territory_table();
    captacion_app_maybe_seed_territories();
}
add_action('after_switch_theme', 'captacion_app_prepare_territory_catalog', 20);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_reference_maps() {
    $communities = array(
        '01'=>'Andalucía','02'=>'Aragón','03'=>'Asturias','04'=>'Illes Balears','05'=>'Canarias','06'=>'Cantabria','07'=>'Castilla y León','08'=>'Castilla-La Mancha','09'=>'Cataluña','10'=>'Comunitat Valenciana','11'=>'Extremadura','12'=>'Galicia','13'=>'Comunidad de Madrid','14'=>'Región de Murcia','15'=>'Comunidad Foral de Navarra','16'=>'País Vasco','17'=>'La Rioja','18'=>'Ciudad Autónoma de Ceuta','19'=>'Ciudad Autónoma de Melilla'
    );
    $province_names = array(
        '01'=>'Araba/Álava','02'=>'Albacete','03'=>'Alicante/Alacant','04'=>'Almería','05'=>'Ávila','06'=>'Badajoz','07'=>'Illes Balears','08'=>'Barcelona','09'=>'Burgos','10'=>'Cáceres','11'=>'Cádiz','12'=>'Castellón/Castelló','13'=>'Ciudad Real','14'=>'Córdoba','15'=>'A Coruña','16'=>'Cuenca','17'=>'Girona','18'=>'Granada','19'=>'Guadalajara','20'=>'Gipuzkoa','21'=>'Huelva','22'=>'Huesca','23'=>'Jaén','24'=>'León','25'=>'Lleida','26'=>'La Rioja','27'=>'Lugo','28'=>'Madrid','29'=>'Málaga','30'=>'Murcia','31'=>'Navarra','32'=>'Ourense','33'=>'Asturias','34'=>'Palencia','35'=>'Las Palmas','36'=>'Pontevedra','37'=>'Salamanca','38'=>'Santa Cruz de Tenerife','39'=>'Cantabria','40'=>'Segovia','41'=>'Sevilla','42'=>'Soria','43'=>'Tarragona','44'=>'Teruel','45'=>'Toledo','46'=>'Valencia/València','47'=>'Valladolid','48'=>'Bizkaia','49'=>'Zamora','50'=>'Zaragoza','51'=>'Ceuta','52'=>'Melilla'
    );
    $province_to_community = array(
        '04'=>'01','11'=>'01','14'=>'01','18'=>'01','21'=>'01','23'=>'01','29'=>'01','41'=>'01','22'=>'02','44'=>'02','50'=>'02','33'=>'03','07'=>'04','35'=>'05','38'=>'05','39'=>'06','05'=>'07','09'=>'07','24'=>'07','34'=>'07','37'=>'07','40'=>'07','42'=>'07','47'=>'07','49'=>'07','02'=>'08','13'=>'08','16'=>'08','19'=>'08','45'=>'08','08'=>'09','17'=>'09','25'=>'09','43'=>'09','03'=>'10','12'=>'10','46'=>'10','06'=>'11','10'=>'11','15'=>'12','27'=>'12','32'=>'12','36'=>'12','28'=>'13','30'=>'14','31'=>'15','01'=>'16','20'=>'16','48'=>'16','26'=>'17','51'=>'18','52'=>'19'
    );
    return compact('communities', 'province_names', 'province_to_community');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_normalize_header($value) {
    $value = remove_accents(strtolower(trim((string) $value)));
    return preg_replace('/[^a-z0-9]+/', '_', $value);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_read_csv($path) {
    $handle = fopen($path, 'rb');
    if (!$handle) return new WP_Error('territory_csv_open', 'No se pudo abrir el CSV.');
    $sample = fgets($handle);
    rewind($handle);
    $delimiters = array(';', ',', "\t", '|');
    $delimiter = ';'; $max = -1;
    foreach ($delimiters as $candidate) {
        $count = substr_count((string) $sample, $candidate);
        if ($count > $max) { $max = $count; $delimiter = $candidate; }
    }
    $rows = array();
    while (($row = fgetcsv($handle, 0, $delimiter)) !== false) $rows[] = $row;
    fclose($handle);
    return $rows;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_xlsx_column_index($ref) {
    preg_match('/^[A-Z]+/i', (string) $ref, $match);
    $letters = strtoupper($match[0] ?? 'A'); $index = 0;
    for ($i = 0; $i < strlen($letters); $i++) $index = $index * 26 + (ord($letters[$i]) - 64);
    return max(0, $index - 1);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_read_xlsx($path) {
    if (!class_exists('ZipArchive')) return new WP_Error('territory_xlsx_zip', 'ZipArchive no está disponible para leer XLSX.');
    $zip = new ZipArchive();
    if ($zip->open($path) !== true) return new WP_Error('territory_xlsx_open', 'No se pudo abrir el XLSX.');
    $shared = array();
    $shared_xml = $zip->getFromName('xl/sharedStrings.xml');
    if ($shared_xml) {
        $xml = simplexml_load_string($shared_xml);
        foreach ($xml->si ?? array() as $item) {
            $parts = array(); foreach ($item->xpath('.//t') ?: array() as $text) $parts[] = (string) $text;
            $shared[] = implode('', $parts);
        }
    }
    $sheet_files = array();
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $name = $zip->getNameIndex($index);
        if (preg_match('#^xl/worksheets/sheet\d+\.xml$#', (string) $name)) $sheet_files[] = $name;
    }
    natsort($sheet_files);
    $rows = array();
    foreach ($sheet_files as $sheet_file) {
        $sheet_xml = $zip->getFromName($sheet_file);
        if (!$sheet_xml) continue;
        $sheet = simplexml_load_string($sheet_xml);
        foreach ($sheet->sheetData->row ?? array() as $row) {
            $values = array();
            foreach ($row->c as $cell) {
                $index = captacion_app_territory_xlsx_column_index((string) $cell['r']);
                $type = (string) $cell['t'];
                $value = $type === 'inlineStr' ? implode('', array_map('strval', $cell->xpath('.//t') ?: array())) : (string) $cell->v;
                if ($type === 's') $value = $shared[(int) $value] ?? '';
                $values[$index] = $value;
            }
            if ($values) {
                ksort($values);
                $max = max(array_keys($values));
                $rows[] = array_replace(array_fill(0, $max + 1, ''), $values);
            }
        }
    }
    $zip->close();
    if (!$rows) return new WP_Error('territory_xlsx_sheet', 'El XLSX no contiene hojas legibles.');
    return $rows;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_parse_file($path) {
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension === 'xlsx') return captacion_app_territory_read_xlsx($path);
    if (in_array($extension, array('csv', 'txt'), true)) return captacion_app_territory_read_csv($path);
    return new WP_Error('territory_file_type', 'Formato no compatible. Usa CSV o XLSX.');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_territory_row_value($row, $headers, $aliases) {
    foreach ($aliases as $alias) {
        $key = captacion_app_territory_normalize_header($alias);
        if (isset($headers[$key]) && isset($row[$headers[$key]])) return trim((string) $row[$headers[$key]]);
    }
    return '';
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_insert_territory_row($row) {
    global $wpdb;
    $table = captacion_app_territory_table_name();
    $now = current_time('mysql');
    $municipality_code = preg_replace('/\D/', '', (string) ($row['municipio_codigo_ine'] ?? ''));
    if (strlen($municipality_code) !== 5) {
        return false;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    return false !== $wpdb->replace($table, array(
        'comunidad_codigo' => str_pad(substr(preg_replace('/\D/', '', (string) ($row['comunidad_codigo'] ?? '')), 0, 2), 2, '0', STR_PAD_LEFT),
        'comunidad_nombre' => sanitize_text_field($row['comunidad_nombre'] ?? ''),
        'provincia_codigo' => str_pad(substr(preg_replace('/\D/', '', (string) ($row['provincia_codigo'] ?? '')), 0, 2), 2, '0', STR_PAD_LEFT),
        'provincia_nombre' => sanitize_text_field($row['provincia_nombre'] ?? ''),
        'municipio_codigo_ine' => $municipality_code,
        'municipio_nombre' => sanitize_text_field($row['municipio_nombre'] ?? ''),
        'codigo_postal' => null,
        'source' => sanitize_text_field($row['source'] ?? 'INE 2026'),
        'created_at' => $row['created_at'] ?? $now,
        'updated_at' => $now,
    ), array('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s'));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_maybe_seed_territories() {
    global $wpdb;
    $table = captacion_app_territory_table_name();
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE municipio_codigo_ine <> ''");
    if ($count >= 8000 || get_option('captacion_territory_seed_version') === 'INE-2026-8132') {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $path = get_template_directory() . '/src/data/territorios-espana.json';
    $catalog = file_exists($path) ? json_decode(file_get_contents($path), true) : array();
    if (!is_array($catalog) || !$catalog) {
        return;
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $wpdb->query("TRUNCATE TABLE {$table}");
    $inserted = 0;
    foreach ($catalog as $community) {
        foreach ((array) ($community['provinces'] ?? array()) as $province) {
            foreach ((array) ($province['municipalities'] ?? array()) as $municipality) {
                if (captacion_app_insert_territory_row(array(
                    'comunidad_codigo' => $community['id'] ?? '',
                    'comunidad_nombre' => $community['name'] ?? '',
                    'provincia_codigo' => $province['id'] ?? '',
                    'provincia_nombre' => $province['name'] ?? '',
                    'municipio_codigo_ine' => $municipality['ine_code'] ?? $municipality['id'] ?? '',
                    'municipio_nombre' => $municipality['name'] ?? '',
                    'source' => 'INE 2026 - 26codmun.xlsx',
                ))) {
                    $inserted++;
                }
            }
        }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    if ($inserted >= 8000) {
        update_option('captacion_territory_seed_version', 'INE-2026-8132');
        update_option('captacion_territory_last_sync', current_time('mysql'));
        update_option('captacion_territory_source', 'INE 2026 - 26codmun.xlsx');
        delete_transient('captacion_territory_catalog');
    }
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_import_ine_territories($source, $replace = true) {
    global $wpdb;
    captacion_app_maybe_install_territory_table();
    $temporary = '';
    if (preg_match('#^https?://#i', (string) $source)) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $temporary = download_url(esc_url_raw($source), 60);
        if (is_wp_error($temporary)) return $temporary;
        $path = $temporary;
        $url_path = parse_url($source, PHP_URL_PATH);
        $ext = pathinfo((string) $url_path, PATHINFO_EXTENSION);
        if ($ext) {
            $renamed = $temporary . '.' . sanitize_key($ext);
            if (@rename($temporary, $renamed)) {
                $path = $renamed;
                $temporary = $renamed;
            }
        }
    } else {
        $path = wp_normalize_path((string) $source);
    }
    if (!is_readable($path)) return new WP_Error('territory_source_missing', 'No se encuentra el archivo territorial indicado.');
    $rows = captacion_app_territory_parse_file($path);
    if ($temporary && file_exists($temporary)) @unlink($temporary);
    if (is_wp_error($rows) || count($rows) < 2) return is_wp_error($rows) ? $rows : new WP_Error('territory_empty', 'El archivo no contiene filas suficientes.');
    $header_index = null;
    foreach ($rows as $index => $candidate) {
        $normalized = array_map('captacion_app_territory_normalize_header', $candidate);
        if (in_array('cpro', $normalized, true) && in_array('nombre', $normalized, true)) {
            $header_index = $index;
            break;
        }
    }
    if ($header_index === null) return new WP_Error('territory_headers', 'No se encontraron las columnas CPRO y NOMBRE del archivo oficial INE.');
    $header_row = $rows[$header_index]; $headers = array();
    foreach ($header_row as $index => $header) $headers[captacion_app_territory_normalize_header($header)] = $index;
    $maps = captacion_app_territory_reference_maps();
    $table = captacion_app_territory_table_name();
    if ($replace) $wpdb->query("TRUNCATE TABLE {$table}");
    $counts = array('ccaa'=>0,'province'=>0,'municipality'=>0,'postal_code'=>0);
    $seen_communities = array();
    $seen_provinces = array();
    foreach ($rows as $row) {
        $province_code = preg_replace('/\D/', '', captacion_app_territory_row_value($row, $headers, array('CPRO','codigo_provincia','cod_provincia','province_code','provincia_codigo')));
        $municipality_part = preg_replace('/\D/', '', captacion_app_territory_row_value($row, $headers, array('CMUN','codigo_municipio','cod_municipio','municipality_code','municipio_codigo')));
        $combined_code = preg_replace('/\D/', '', captacion_app_territory_row_value($row, $headers, array('CODIGO_INE','ine_code','codigo','codmun','municipality_ine_code')));
        $municipality_name = captacion_app_territory_row_value($row, $headers, array('NOMBRE','nombre_municipio','municipio','municipality_name','denominacion'));
        if (!$province_code && strlen($combined_code) >= 5) $province_code = substr($combined_code, 0, 2);
        $province_code = str_pad(substr($province_code, 0, 2), 2, '0', STR_PAD_LEFT);
        if (!$combined_code && $province_code && $municipality_part) $combined_code = $province_code . str_pad(substr($municipality_part, 0, 3), 3, '0', STR_PAD_LEFT);
        $municipality_code = str_pad(substr($combined_code, 0, 5), 5, '0', STR_PAD_LEFT);
        if ($municipality_name && $province_code !== '00' && isset($maps['province_to_community'][$province_code])) {
            $ccaa_code = $maps['province_to_community'][$province_code];
            if (captacion_app_insert_territory_row(array(
                'comunidad_codigo' => $ccaa_code,
                'comunidad_nombre' => $maps['communities'][$ccaa_code],
                'provincia_codigo' => $province_code,
                'provincia_nombre' => $maps['province_names'][$province_code] ?? $province_code,
                'municipio_codigo_ine' => $municipality_code,
                'municipio_nombre' => $municipality_name,
                'source' => 'INE 2026 - ' . sanitize_file_name(wp_basename((string) $source)),
            ))) {
                $counts['municipality']++;
                $seen_communities[$ccaa_code] = true;
                $seen_provinces[$province_code] = true;
            }
        }
    }
    $counts['ccaa'] = count($seen_communities);
    $counts['province'] = count($seen_provinces);
    // 26codmun.xlsx no contiene codigos postales. Nunca se infieren ni se inventan aqui.
    $counts['postal_code'] = 0;
    update_option('captacion_territory_last_sync', current_time('mysql'));
    update_option('captacion_territory_source', sanitize_text_field((string) $source));
    delete_transient('captacion_territory_catalog');
    return $counts;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_get_territory_catalog() {
    global $wpdb;
    $cached = get_transient('captacion_territory_catalog');
    if (is_array($cached)) return $cached;
    captacion_app_maybe_install_territory_table();
    $table = captacion_app_territory_table_name();
    $postal_table = captacion_app_territory_postal_table_name();
    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    if (!$count) {
        $path = get_template_directory() . '/src/data/territorios-espana.json';
        $fallback = file_exists($path) ? json_decode(file_get_contents($path), true) : array();
        return is_array($fallback) ? $fallback : array();
    }
    $rows = $wpdb->get_results("SELECT comunidad_codigo, comunidad_nombre, provincia_codigo, provincia_nombre, municipio_codigo_ine, municipio_nombre FROM {$table} ORDER BY comunidad_nombre, provincia_nombre, municipio_nombre", ARRAY_A);
    $postal_rows = $wpdb->get_results("SELECT municipio_codigo_ine, codigo_postal FROM {$postal_table} ORDER BY codigo_postal", ARRAY_A);
    $communities = array(); $provinces = array(); $municipalities = array(); $postal = array();
    foreach ($postal_rows as $postal_row) {
        if (preg_match('/^[0-9]{5}$/', (string) $postal_row['codigo_postal'])) {
            $postal[$postal_row['municipio_codigo_ine']][] = $postal_row['codigo_postal'];
        }
    }
    foreach ($rows as $row) {
        $community_code = $row['comunidad_codigo'];
        $province_code = $row['provincia_codigo'];
        $municipality_code = $row['municipio_codigo_ine'];
        if (!isset($communities[$community_code])) $communities[$community_code] = array('id'=>$community_code,'name'=>$row['comunidad_nombre'],'provinces'=>array());
        if (!isset($provinces[$province_code])) $provinces[$province_code] = array('id'=>$province_code,'parent'=>$community_code,'name'=>$row['provincia_nombre'],'municipalities'=>array());
        $municipalities[$municipality_code] = array('id'=>$municipality_code,'ine_code'=>$municipality_code,'parent'=>$province_code,'name'=>$row['municipio_nombre'],'postalCodes'=>array_values(array_unique($postal[$municipality_code] ?? array())));
    }
    foreach ($municipalities as $municipality) { $parent = $municipality['parent']; unset($municipality['parent']); if (isset($provinces[$parent])) $provinces[$parent]['municipalities'][] = $municipality; }
    foreach ($provinces as $province) { $parent = $province['parent']; unset($province['parent']); if (isset($communities[$parent])) $communities[$parent]['provinces'][] = $province; }
    $catalog = array_values($communities); set_transient('captacion_territory_catalog', $catalog, DAY_IN_SECONDS); return $catalog;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_get_normalized_territories() {
    $catalog = captacion_app_get_territory_catalog();
    $normalized = array('ccaa'=>array(), 'provinces'=>array(), 'municipalities'=>array(), 'postalCodes'=>array());
    foreach ($catalog as $community) {
        $normalized['ccaa'][] = array('id'=>$community['id'] ?? '', 'name'=>$community['name'] ?? '');
        foreach ($community['provinces'] ?? array() as $province) {
            $normalized['provinces'][] = array('id'=>$province['id'] ?? '', 'ccaaId'=>$community['id'] ?? '', 'name'=>$province['name'] ?? '');
            foreach ($province['municipalities'] ?? array() as $municipality) {
                $municipality_id = $municipality['id'] ?? '';
                $normalized['municipalities'][] = array('id'=>$municipality_id, 'provinceId'=>$province['id'] ?? '', 'ineCode'=>$municipality['ine_code'] ?? $municipality_id, 'name'=>$municipality['name'] ?? '');
                foreach ($municipality['postalCodes'] ?? array() as $postal_code) {
                    $normalized['postalCodes'][] = array('code'=>$postal_code, 'municipalityId'=>$municipality_id);
                }
            }
        }
    }
    return $normalized;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_export_territory_catalog($destination = '') {
    $destination = $destination ?: get_template_directory() . '/src/data/territorios-espana.json';
    $directory = dirname($destination);
    if (!is_dir($directory) && !wp_mkdir_p($directory)) return new WP_Error('territory_export_directory', 'No se pudo crear el directorio de exportación.');
    $json = wp_json_encode(captacion_app_get_territory_catalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (file_put_contents($destination, $json . PHP_EOL, LOCK_EX) === false) return new WP_Error('territory_export_write', 'No se pudo escribir el JSON territorial.');
    return wp_normalize_path($destination);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_territories() {
    global $wpdb;
    $table = captacion_app_territory_table_name();
    $database_count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    return rest_ensure_response(array(
        'ok'=>true,
        'source'=>$database_count ? 'database: INE 2026' : 'fallback: territorios-espana.json',
        'postalCodeSource'=>'pending_official_source',
        'lastSync'=>get_option('captacion_territory_last_sync', ''),
        'catalog'=>captacion_app_get_territory_catalog(),
        'normalized'=>captacion_app_get_normalized_territories(),
    ));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_territory_provinces(WP_REST_Request $request) {
    global $wpdb;
    $community = str_pad((string) absint($request->get_param('community')), 2, '0', STR_PAD_LEFT);
    $table = captacion_app_territory_table_name();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT provincia_codigo AS id, provincia_nombre AS name FROM {$table} WHERE comunidad_codigo = %s ORDER BY provincia_nombre",
        $community
    ), ARRAY_A);
    return rest_ensure_response(array('ok'=>true,'community'=>$community,'provinces'=>$rows ?: array()));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_territory_municipalities(WP_REST_Request $request) {
    global $wpdb;
    $province = str_pad((string) absint($request->get_param('province')), 2, '0', STR_PAD_LEFT);
    $table = captacion_app_territory_table_name();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT municipio_codigo_ine AS id, municipio_codigo_ine AS ineCode, municipio_nombre AS name, codigo_postal AS postalCode FROM {$table} WHERE provincia_codigo = %s ORDER BY municipio_nombre",
        $province
    ), ARRAY_A);
    foreach ($rows as &$row) {
        $row['postalCode'] = preg_match('/^[0-9]{5}$/', (string) $row['postalCode']) ? $row['postalCode'] : null;
    }
    unset($row);
    return rest_ensure_response(array('ok'=>true,'province'=>$province,'municipalities'=>$rows ?: array()));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_admin_permission(WP_REST_Request $request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('captacion_rest_forbidden', 'Solo administradores pueden ejecutar esta accion.', array('status'=>403));
    }
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('captacion_rest_invalid_nonce', 'Nonce no valido.', array('status'=>403));
    }
    return true;
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_import_territories(WP_REST_Request $request) {
    $source = sanitize_text_field((string) $request->get_param('source'));
    if (!$source) return new WP_Error('territory_source_required', 'Indica una fuente INE CSV o XLSX.', array('status'=>400));
    $result = captacion_app_import_ine_territories($source, true);
    return is_wp_error($result) ? $result : rest_ensure_response(array('ok'=>true,'counts'=>$result));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_parse_jsonp($body) {
    $body = trim((string) $body);
    $start_array = strpos($body, '['); $start_object = strpos($body, '{');
    if ($start_array === false || ($start_object !== false && $start_object < $start_array)) { $start = $start_object; $end = strrpos($body, '}'); }
    else { $start = $start_array; $end = strrpos($body, ']'); }
    if ($start === false || $end === false) return null;
    return json_decode(substr($body, $start, $end - $start + 1), true);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_rest_validate_cartociudad(WP_REST_Request $request) {
    $params = $request->get_json_params(); $params = is_array($params) ? $params : array();
    $postal_code = preg_replace('/\D/', '', (string) ($params['postalCode'] ?? ''));
    if ($postal_code !== '' && !preg_match('/^[0-9]{5}$/', $postal_code)) {
        return new WP_Error('territory_invalid_postal_code', 'El codigo postal debe contener exactamente 5 digitos.', array('status'=>400));
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $remote_address = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $rate_key = 'captacion_address_rate_' . substr(hash('sha256', $remote_address), 0, 24);
    $rate = (int) get_transient($rate_key);
    if ($rate >= 12) {
        return new WP_Error('territory_address_rate_limit', 'Demasiadas validaciones. Espera unos minutos.', array('status'=>429));
    }
    'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

    $query = sanitize_text_field(implode(' ', array_filter(array($params['address'] ?? '', $params['postalCode'] ?? '', $params['municipality'] ?? '', $params['province'] ?? ''))));
    if (strlen($query) < 3) return new WP_Error('territory_address_query', 'Indica dirección, código postal o municipio.', array('status'=>400));
    $url = add_query_arg(array('q'=>$query, 'limit'=>5), 'https://www.cartociudad.es/geocoder/api/geocoder/findJsonp');
    $response = wp_remote_get($url, array('timeout'=>15, 'user-agent'=>'Compra Captación WordPress territorial validator'));
    if (is_wp_error($response)) return $response;
    $data = captacion_app_parse_jsonp(wp_remote_retrieve_body($response));
    if (!is_array($data)) return new WP_Error('territory_cartociudad_parse', 'CartoCiudad no devolvió una respuesta válida.', array('status'=>502));
    return rest_ensure_response(array('ok'=>true,'provider'=>'CartoCiudad/CNIG','query'=>$query,'results'=>array_slice(array_values($data),0,5)));
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_validate_two_digit_code($value) {
    return (bool) preg_match('/^[0-9]{1,2}$/', (string) $value);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_territory_routes() {
    register_rest_route('captacion/v1', '/territories', array('methods'=>WP_REST_Server::READABLE,'callback'=>'captacion_app_rest_territories','permission_callback'=>'__return_true'));
    register_rest_route('captacion/v1', '/territories/provinces', array(
        'methods'=>WP_REST_Server::READABLE,
        'callback'=>'captacion_app_rest_territory_provinces',
        'permission_callback'=>'__return_true',
        'args'=>array('community'=>array('required'=>true,'sanitize_callback'=>'absint','validate_callback'=>'captacion_app_validate_two_digit_code')),
    ));
    register_rest_route('captacion/v1', '/territories/municipalities', array(
        'methods'=>WP_REST_Server::READABLE,
        'callback'=>'captacion_app_rest_territory_municipalities',
        'permission_callback'=>'__return_true',
        'args'=>array('province'=>array('required'=>true,'sanitize_callback'=>'absint','validate_callback'=>'captacion_app_validate_two_digit_code')),
    ));
    register_rest_route('captacion/v1', '/address/validate', array('methods'=>WP_REST_Server::CREATABLE,'callback'=>'captacion_app_rest_validate_cartociudad','permission_callback'=>'captacion_app_rest_public_nonce_permission'));
    register_rest_route('captacion/v1', '/territories/validate-address', array('methods'=>WP_REST_Server::CREATABLE,'callback'=>'captacion_app_rest_validate_cartociudad','permission_callback'=>'captacion_app_rest_public_nonce_permission'));
    register_rest_route('captacion/v1', '/territories/import', array('methods'=>WP_REST_Server::CREATABLE,'callback'=>'captacion_app_rest_import_territories','permission_callback'=>'captacion_app_rest_admin_permission'));
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_privacy_exporter($email, $page = 1) {
    global $wpdb;
    $table = captacion_app_records_table_name();
    $user = get_user_by('email', $email);
    if (!$user) {
        return array('data' => array(), 'done' => true);
    }
    $user_id = $user->ID;
    $limit = 100;
    $offset = ($page - 1) * $limit;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE (owner_user_id = %d OR user_id = %d OR user_email = %s) AND deleted_at IS NULL ORDER BY id ASC LIMIT %d OFFSET %d",
        $user_id, $user_id, $email, $limit, $offset
    ), ARRAY_A);
    $total = absint($wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$table} WHERE (owner_user_id = %d OR user_id = %d OR user_email = %s) AND deleted_at IS NULL",
        $user_id, $user_id, $email
    )));
    $export_items = array();
    foreach ($rows as $row) {
        $item_id = 'captacion-record-' . $row['id'];
        $group_id = 'captacion_app_records';
        $group_label = 'Compra Captación - Registros';
        $data = array(
            array('name' => 'ID', 'value' => $row['id']),
            array('name' => 'Tipo de registro', 'value' => $row['record_type']),
            array('name' => 'Clave', 'value' => $row['record_key']),
            array('name' => 'Título', 'value' => $row['title']),
            array('name' => 'Estado', 'value' => $row['status']),
            array('name' => 'Fecha creación', 'value' => $row['created_at']),
            array('name' => 'Origen', 'value' => $row['data_origin']),
            array('name' => 'Ámbito privacidad', 'value' => $row['privacy_scope']),
            array('name' => 'Es demo', 'value' => $row['is_demo'] ? 'Sí' : 'No'),
        );
        $export_items[] = array(
            'item_id' => $item_id,
            'group_id' => $group_id,
            'group_label' => $group_label,
            'data' => $data,
        );
    }
    $done = ($offset + $limit) >= $total;
    return array('data' => $export_items, 'done' => $done);
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_privacy_exporter($exporters) {
    $exporters['captacion-app-records'] = array(
        'exporter_friendly_name' => 'Compra Captación - Registros',
        'callback' => 'captacion_app_privacy_exporter',
    );
    return $exporters;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_privacy_eraser($email, $page = 1) {
    global $wpdb;
    $table = captacion_app_records_table_name();
    $batches_table = captacion_app_import_batches_table_name();
    $user = get_user_by('email', $email);
    if (!$user) {
        return array('items_removed' => false, 'items_retained' => false, 'messages' => array(), 'done' => true);
    }
    $user_id = $user->ID;
    $now = current_time('mysql');
    $records_updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$table} SET deleted_at = %s WHERE (owner_user_id = %d OR user_id = %d OR user_email = %s) AND deleted_at IS NULL",
        $now, $user_id, $user_id, $email
    ));
    $batches_updated = $wpdb->query($wpdb->prepare(
        "UPDATE {$batches_table} SET deleted_at = %s, status = 'deleted', updated_at = %s WHERE owner_user_id = %d AND deleted_at IS NULL",
        $now, $now, $user_id
    ));
    $items_removed = ($records_updated > 0 || $batches_updated > 0);
    return array(
        'items_removed' => $items_removed,
        'items_retained' => false,
        'messages' => array(sprintf('Se han marcado %d registros como eliminados.', $records_updated)),
        'done' => true,
    );
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

function captacion_app_register_privacy_eraser($erasers) {
    $erasers['captacion-app-records'] = array(
        'eraser_friendly_name' => 'Compra Captación - Registros',
        'callback' => 'captacion_app_privacy_eraser',
    );
    return $erasers;
}
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',

if (defined('WP_CLI') && WP_CLI) {
    class Captacion_App_Territory_CLI_Command {
        public function import($args, $assoc_args) {
            $source = $args[0] ?? ''; if (!$source) WP_CLI::error('Uso: wp captacion territory import <archivo.csv|archivo.xlsx|url>');
            $result = captacion_app_import_ine_territories($source, empty($assoc_args['append']));
            if (is_wp_error($result)) WP_CLI::error($result->get_error_message());
            WP_CLI::success('Territorios importados: ' . wp_json_encode($result));
        }
        public function update($args, $assoc_args) {
            $source = $assoc_args['source'] ?? get_option('captacion_territory_source', '');
            if (!$source) WP_CLI::error('Configura --source=<URL/archivo oficial INE> en la primera actualización.');
            $result = captacion_app_import_ine_territories($source, true);
            if (is_wp_error($result)) WP_CLI::error($result->get_error_message());
            WP_CLI::success('Actualización territorial anual completada: ' . wp_json_encode($result));
        }
        public function export($args, $assoc_args) {
            $destination = $args[0] ?? '';
            $result = captacion_app_export_territory_catalog($destination);
            if (is_wp_error($result)) WP_CLI::error($result->get_error_message());
            WP_CLI::success('JSON territorial generado: ' . $result);
        }
    }
    WP_CLI::add_command('captacion territory', 'Captacion_App_Territory_CLI_Command');
'hero_title' => 'Compra, vende y comparte <span class="text-blue">captaciones</span> entre profesionales',
