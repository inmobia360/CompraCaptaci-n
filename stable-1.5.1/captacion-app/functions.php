<?php
if (!defined('ABSPATH')) {
    exit;
}

function captacion_app_defaults() {
    return array(
        'brand_name' => 'Captacion.app',
        'site_title' => 'Captaciones inmobiliarias | Compra, vende y colabora entre profesionales',
        'meta_description' => 'Compra Captacion es un marketplace B2B de captaciones inmobiliarias para profesionales. Publica oportunidades, busca demandas activas y colabora con acceso protegido.',
        'hero_kicker' => 'Red privada B2B para profesionales inmobiliarios',
        'hero_title' => 'Portal de <span class="text-blue">captaciones inmobiliarias</span> para profesionales',
        'hero_description' => 'Compra Captacion ayuda a agentes, agencias e inversores a publicar captaciones, cruzar demandas activas y colaborar con acceso protegido, trazabilidad comercial y mejor contexto de operacion.',
        'primary_cta' => 'Entender el recorrido',
        'secondary_cta' => 'Ver el producto en accion',
        'contact_email' => 'contacto@captacion.app',
        'stripe_payment_link' => '',
        'stripe_membership_initial_link' => '',
        'stripe_membership_professional_link' => '',
        'stripe_membership_agency_link' => '',
        'stripe_marketplace_single_link' => '',
        'stripe_marketplace_plus_pack_link' => '',
        'stripe_marketplace_premium_pack_link' => '',
        'mailchimp_api_key' => '',
        'mailchimp_audience_id' => '',
        'mailchimp_double_optin' => '0',
    );
}

function captacion_app_settings() {
    $saved = get_option('captacion_app_settings', array());
    $defaults = captacion_app_defaults();
    $settings = wp_parse_args(is_array($saved) ? $saved : array(), $defaults);

    foreach ($defaults as $key => $default) {
        if (strpos($key, 'stripe_') === 0 && $default && strpos((string) $default, 'REEMPLAZA_') === false && (!$settings[$key] || strpos((string) $settings[$key], 'REEMPLAZA_') !== false)) {
            $settings[$key] = $default;
        }
    }

    return $settings;
}

function captacion_app_setting($key) {
    $settings = captacion_app_settings();
    return isset($settings[$key]) ? $settings[$key] : '';
}

function captacion_app_sanitize_settings($input) {
    $defaults = captacion_app_defaults();
    $output = array();

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
        } elseif ($key === 'mailchimp_api_key' || $key === 'mailchimp_audience_id') {
            $output[$key] = sanitize_text_field($value);
        } elseif ($key === 'mailchimp_double_optin') {
            $output[$key] = !empty($value) ? '1' : '0';
        } else {
            $output[$key] = sanitize_text_field($value);
        }
    }

    return $output;
}

function captacion_app_admin_menu() {
    add_menu_page(
        'Captacion.app',
        'Captacion.app',
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
add_action('admin_menu', 'captacion_app_admin_menu');

function captacion_app_register_settings() {
    register_setting('captacion_app_settings_group', 'captacion_app_settings', array(
        'sanitize_callback' => 'captacion_app_sanitize_settings',
        'default' => captacion_app_defaults(),
    ));
}
add_action('admin_init', 'captacion_app_register_settings');

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
}

function captacion_app_register_resource_settings() {
    register_setting('captacion_app_resources_group', 'captacion_app_resource_settings', array(
        'sanitize_callback' => 'captacion_app_sanitize_resource_settings',
        'default' => array(),
    ));
}
add_action('admin_init', 'captacion_app_register_resource_settings');

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
}

function captacion_app_render_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings = captacion_app_settings();
    ?>
    <div class="wrap">
        <h1>Captacion.app</h1>
        <p>Edita los textos principales y la pasarela Stripe de la web.</p>
        <?php if (isset($_GET['captacion_pages_created']) || isset($_GET['captacion_pages_updated'])) : ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    Paginas procesadas. Creadas: <?php echo esc_html(absint($_GET['captacion_pages_created'] ?? 0)); ?>.
                    Actualizadas: <?php echo esc_html(absint($_GET['captacion_pages_updated'] ?? 0)); ?>.
                </p>
            </div>
        <?php endif; ?>

        <div class="card" style="max-width: 900px; margin-top: 16px; padding: 18px;">
            <h2>Crear paginas editables</h2>
            <p>Crea o actualiza la estructura base de paginas de Captacion.app para poder editarlas desde WordPress.</p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('captacion_app_create_pages'); ?>
                <input type="hidden" name="action" value="captacion_app_create_pages">
                <?php submit_button('Crear / actualizar paginas editables', 'secondary', 'submit', false); ?>
            </form>
        </div>

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
                    <th scope="row"><label for="captacion_stripe_payment_link">Payment Link de Stripe</label></th>
                    <td>
                        <input id="captacion_stripe_payment_link" class="large-text" type="url" name="captacion_app_settings[stripe_payment_link]" value="<?php echo esc_attr($settings['stripe_payment_link']); ?>">
                        <p class="description">Pago de desbloqueo/compra de captacion. Ejemplo: https://buy.stripe.com/xxxxxxxxxxxx. No introduzcas claves secretas de Stripe.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_initial_link">Stripe Starter</label></th>
                    <td>
                        <input id="captacion_stripe_membership_initial_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_initial_link]" value="<?php echo esc_attr($settings['stripe_membership_initial_link']); ?>">
                        <p class="description">Opcional. Si lo dejas vacio, Starter se mantiene como registro gratuito.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_professional_link">Stripe Plan Profesional</label></th>
                    <td>
                        <input id="captacion_stripe_membership_professional_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_professional_link]" value="<?php echo esc_attr($settings['stripe_membership_professional_link']); ?>">
                        <p class="description">Payment Link de Stripe para la membresia Professional mensual.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_membership_agency_link">Stripe Plan Premium</label></th>
                    <td>
                        <input id="captacion_stripe_membership_agency_link" class="large-text" type="url" name="captacion_app_settings[stripe_membership_agency_link]" value="<?php echo esc_attr($settings['stripe_membership_agency_link']); ?>">
                        <p class="description">Payment Link de Stripe para la membresia Premium mensual.</p>
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
                    <th scope="row"><label for="captacion_stripe_marketplace_plus_pack_link">Stripe pack Professional</label></th>
                    <td>
                        <input id="captacion_stripe_marketplace_plus_pack_link" class="large-text" type="url" name="captacion_app_settings[stripe_marketplace_plus_pack_link]" value="<?php echo esc_attr($settings['stripe_marketplace_plus_pack_link']); ?>">
                        <p class="description">Payment Link de 5 EUR para 15 accesos extra.</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="captacion_stripe_marketplace_premium_pack_link">Stripe pack Premium</label></th>
                    <td>
                        <input id="captacion_stripe_marketplace_premium_pack_link" class="large-text" type="url" name="captacion_app_settings[stripe_marketplace_premium_pack_link]" value="<?php echo esc_attr($settings['stripe_marketplace_premium_pack_link']); ?>">
                        <p class="description">Payment Link de 5 EUR para 30 accesos extra.</p>
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
                    <th scope="row">Mailchimp double opt-in</th>
                    <td>
                        <label><input type="checkbox" name="captacion_app_settings[mailchimp_double_optin]" value="1" <?php checked($settings['mailchimp_double_optin'], '1'); ?>> Enviar confirmacion antes de suscribir</label>
                        <p class="description">Desmarcado: el contacto entra directamente como suscrito. Marcado: queda pendiente de confirmar.</p>
                    </td>
                </tr>

            </table>
            <?php submit_button('Guardar cambios'); ?>
        </form>
    </div>
    <?php
}

function captacion_app_setup() {
    add_theme_support('title-tag');
}
add_action('after_setup_theme', 'captacion_app_setup');

function captacion_app_output_theme_favicon() {
    $favicon_uri = get_template_directory_uri() . '/media/favicon-compra-captacion.png';
    echo '<link rel="icon" type="image/png" sizes="512x512" href="' . esc_url($favicon_uri) . '">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url($favicon_uri) . '">' . "\n";
}
add_action('wp_head', 'captacion_app_output_theme_favicon', 1);

function captacion_app_enqueue_assets() {
    wp_enqueue_style(
        'captacion-app-theme',
        get_stylesheet_uri(),
        array(),
        '1.5.3'
    );
}
add_action('wp_enqueue_scripts', 'captacion_app_enqueue_assets');


function captacion_app_mailchimp_datacenter($api_key) {
    if (!is_string($api_key) || strpos($api_key, '-') === false) {
        return '';
    }
    $parts = explode('-', $api_key);
    return sanitize_key(end($parts));
}

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
}


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
}

function captacion_app_events_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_mail_events';
}

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
add_action('admin_init', 'captacion_app_maybe_install_mail_events_table');
add_action('init', 'captacion_app_maybe_install_mail_events_table');


function captacion_app_records_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_app_records';
}

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
        PRIMARY KEY (id),
        UNIQUE KEY record_type_key (record_type, record_key),
        KEY record_type (record_type),
        KEY user_email (user_email),
        KEY status (status),
        KEY related_id (related_id),
        KEY updated_at (updated_at)
    ) {$charset_collate};";
    dbDelta($sql);
    update_option('captacion_app_records_table_version', '20260616');
}
add_action('after_switch_theme', 'captacion_app_install_records_table');

function captacion_app_maybe_install_records_table() {
    if (get_option('captacion_app_records_table_version') !== '20260616') {
        captacion_app_install_records_table();
    }
}
add_action('admin_init', 'captacion_app_maybe_install_records_table');
add_action('init', 'captacion_app_maybe_install_records_table');

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
}

function captacion_app_resource_catalog_defaults() {
    return array(
        'nda' => array('resource_id'=>'nda','title'=>'Modelo de acuerdo de confidencialidad / NDA','description'=>'Modelo orientativo para proteger la información compartida durante una colaboración.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/01_Modelo_acuerdo_confidencialidad_NDA.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','tax_id','email','phone','date','reference','notes')),
        'collaboration' => array('resource_id'=>'collaboration','title'=>'Modelo de acuerdo de colaboración entre profesionales','description'=>'Base para documentar funciones, honorarios y reglas de colaboración.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/02_Modelo_acuerdo_colaboracion_profesionales.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','tax_id','email','phone','date','reference','notes')),
        'capture-checklist' => array('resource_id'=>'capture-checklist','title'=>'Checklist documental de captación','description'=>'Lista de comprobación para preparar un expediente de captación.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/03_Checklist_documental_captacion.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','email','phone','date','reference','notes')),
        'capture-sheet' => array('resource_id'=>'capture-sheet','title'=>'Ficha profesional de captación','description'=>'Ficha estructurada para resumir producto, situación y condiciones.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/04_Ficha_profesional_captacion.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','tax_id','email','phone','date','reference','notes')),
        'demand-sheet' => array('resource_id'=>'demand-sheet','title'=>'Ficha de demanda activa','description'=>'Documento para registrar una búsqueda profesional y sus criterios.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/05_Ficha_demanda_activa.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','email','phone','date','reference','notes')),
        'safe-collaboration-guide' => array('resource_id'=>'safe-collaboration-guide','title'=>'Guía de colaboración segura','description'=>'Guía práctica para compartir información y avanzar operaciones con trazabilidad.','static_pdf_attachment_id'=>0,'static_pdf_url'=>'https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/06_Guia_colaboracion_segura.pdf','docx_template_attachment_id'=>0,'docx_template_url'=>'','plan_access'=>'basic','editable_fields_schema'=>array('professional_name','company','email','phone','date','reference','notes')),
    );
}

function captacion_app_resource_catalog() {
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
    return $catalog;
}

function captacion_app_resource_events_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_resource_events';
}

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
add_action('init', 'captacion_app_maybe_install_resource_events_table');

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
}

class Captacion_PDF_Generator {
    public static function generate($title, $fields) {
        $lines = array($title, '', 'Documento orientativo generado por el usuario.', 'Revisar antes de firmar o utilizar.', '');
        foreach ($fields as $label => $value) if ($value !== '') $lines[] = $label . ': ' . $value;
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
}

function captacion_app_resource_access_check($resource_id, $require_create = false) {
    $catalog = captacion_app_resource_catalog();
    if (!isset($catalog[$resource_id])) return new WP_Error('captacion_resource_missing', 'Recurso no encontrado.', array('status'=>404));
    if (!is_user_logged_in()) return new WP_Error('captacion_resource_login', 'Debes iniciar sesion.', array('status'=>401));
    if (!captacion_app_is_email_verified(get_current_user_id())) return new WP_Error('captacion_resource_verify', 'Debes verificar tu correo.', array('status'=>403));
    $state = captacion_app_get_user_access_state(get_current_user_id());
    $levels = array('basic'=>0, 'professional_plus'=>1, 'premium'=>2);
    $minimum_plan = $catalog[$resource_id]['plan_access'] ?? 'basic';
    if (($levels[$state['plan_type']] ?? 0) < ($levels[$minimum_plan] ?? 0)) return new WP_Error('captacion_resource_plan', 'Tu plan no incluye este recurso.', array('status'=>403));
    if ($require_create && !in_array($state['plan_type'], array('professional_plus','premium'), true)) return new WP_Error('captacion_resource_plan', 'Crear PDF requiere Professional Plus o Premium.', array('status'=>403));
    return $catalog[$resource_id];
}

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
    $download_url = wp_nonce_url(admin_url('admin-post.php?action=captacion_generated_pdf_download&file_id=' . absint($record_id)), 'captacion_generated_pdf_' . absint($record_id));
    captacion_app_log_resource_event($resource, 'generate_pdf', array('generated_file_id'=>$record_id,'generated_file_url'=>$download_url,'form_data'=>$fields));
    return rest_ensure_response(array('ok'=>true,'downloadUrl'=>$download_url));
}

function captacion_app_render_create_pdf_page() {
    $path = trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
    if ($path !== 'recursos/crear-pdf') return;
    $resource_id = sanitize_key($_GET['resource'] ?? '');
    $resource = captacion_app_resource_access_check($resource_id, true);
    if (is_wp_error($resource)) {
        if (!is_user_logged_in()) wp_safe_redirect(home_url('/#/inicio'));
        else wp_die(esc_html($resource->get_error_message()), 'Captacion.app', array('response'=>$resource->get_error_data()['status'] ?? 403));
        exit;
    }
    captacion_app_log_resource_event($resource, 'open_create_pdf');
    $user = wp_get_current_user();
    $endpoint = rest_url('captacion/v1/resources/generate');
    $nonce = wp_create_nonce('wp_rest');
    ?><!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?php echo esc_html($resource['title']); ?></title><style>body{font-family:Arial,sans-serif;background:#f1f5f9;color:#10233c;margin:0}.wrap{max-width:760px;margin:40px auto;padding:24px}.panel{background:#fff;border:1px solid #dbe5ef;padding:28px;border-radius:8px}label{display:block;font-size:13px;font-weight:700;margin-top:15px}input,textarea{width:100%;box-sizing:border-box;margin-top:6px;padding:12px;border:1px solid #cbd5e1;border-radius:7px}button,a.button{display:inline-block;margin-top:18px;padding:12px 18px;border:0;border-radius:7px;background:#1b67d6;color:#fff;text-decoration:none;font-weight:700;cursor:pointer}.secondary{background:#10233c}.note{font-size:12px;color:#64748b;margin-top:18px}.error{color:#b91c1c;font-size:13px}</style></head><body><main class="wrap"><a href="<?php echo esc_url(home_url('/#/recursos')); ?>">Volver a recursos</a><div class="panel"><h1><?php echo esc_html($resource['title']); ?></h1><p><?php echo esc_html($resource['description']); ?></p><form id="pdf-form"><label>Nombre profesional *<input name="professional_name" required value="<?php echo esc_attr($user->display_name); ?>"></label><label>Agencia/empresa opcional<input name="company"></label><label>NIF/CIF opcional<input name="tax_id"></label><label>Email *<input name="email" type="email" required value="<?php echo esc_attr($user->user_email); ?>"></label><label>Teléfono<input name="phone" value="<?php echo esc_attr(get_user_meta($user->ID, 'captacion_phone', true)); ?>"></label><label>Fecha<input name="date" type="date" value="<?php echo esc_attr(current_time('Y-m-d')); ?>"></label><label>Referencia interna<input name="reference"></label><label>Observaciones<textarea name="notes" rows="5"></textarea></label><button type="button" class="secondary" onclick="preview()">Vista previa</button> <button type="submit">Generar PDF</button><div id="result"></div></form><p class="note">Documento orientativo generado por el usuario. Revisar antes de firmar o utilizar.</p></div></main><script>const form=document.getElementById('pdf-form'),result=document.getElementById('result');function preview(){const d=new FormData(form);result.innerHTML='<div class="note"><strong>Vista previa</strong><br>'+[...d.entries()].map(x=>x[0]+': '+String(x[1]).replace(/[<>&]/g,'')).join('<br>')+'</div>'}form.addEventListener('submit',async e=>{e.preventDefault();result.textContent='Generando...';const payload=Object.fromEntries(new FormData(form));payload.resource_id=<?php echo wp_json_encode($resource_id); ?>;try{const r=await fetch(<?php echo wp_json_encode($endpoint); ?>,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':<?php echo wp_json_encode($nonce); ?>},body:JSON.stringify(payload)});const d=await r.json();if(!r.ok||!d.ok)throw new Error(d.message||'No se pudo generar');result.innerHTML='<a class="button" href="'+d.downloadUrl+'">Descargar PDF</a>'}catch(err){result.innerHTML='<p class="error">'+err.message+'</p>'}});</script></body></html><?php
    exit;
}
add_action('template_redirect', 'captacion_app_render_create_pdf_page', 2);

function captacion_app_download_generated_pdf() {
    global $wpdb;
    $file_id = absint($_GET['file_id'] ?? 0);
    check_admin_referer('captacion_generated_pdf_' . $file_id);
    $table = captacion_app_records_table_name();
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id=%d AND record_type='generated_pdf'", $file_id), ARRAY_A);
    if (!$row || (!current_user_can('manage_options') && absint($row['user_id']) !== get_current_user_id())) wp_die('Acceso denegado.', 403);
    $payload = json_decode($row['payload'], true);
    $path = $payload['path'] ?? '';
    if (!$path || !is_file($path)) wp_die('Archivo no disponible.', 404);
    $resource = captacion_app_resource_catalog()[$payload['resource_id']] ?? array('resource_id'=>'unknown','title'=>$row['title']);
    captacion_app_log_resource_event($resource, 'download_generated_pdf', array('generated_file_id'=>$file_id));
    nocache_headers();
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . sanitize_file_name($row['title']) . '.pdf"');
    header('Content-Length: ' . filesize($path));
    readfile($path);
    exit;
}
add_action('admin_post_captacion_generated_pdf_download', 'captacion_app_download_generated_pdf');

function captacion_app_access_log_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_marketplace_access_log';
}

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
add_action('after_switch_theme', 'captacion_app_install_access_log_table');

function captacion_app_maybe_install_access_log_table() {
    if (get_option('captacion_access_log_table_version') !== '20260620') captacion_app_install_access_log_table();
}
add_action('admin_init', 'captacion_app_maybe_install_access_log_table');
add_action('init', 'captacion_app_maybe_install_access_log_table');

function captacion_app_plan_config($plan_type) {
    $plans = array(
        'basic' => array('included' => 0, 'extra_pack' => 0, 'extra_price' => 10, 'checkout_key' => 'stripe_marketplace_single_link'),
        'professional_plus' => array('included' => 30, 'extra_pack' => 15, 'extra_price' => 5, 'checkout_key' => 'stripe_marketplace_plus_pack_link'),
        'premium' => array('included' => 60, 'extra_pack' => 30, 'extra_price' => 5, 'checkout_key' => 'stripe_marketplace_premium_pack_link'),
    );
    return $plans[$plan_type] ?? $plans['basic'];
}

function captacion_app_normalize_user_plan($plan_type) {
    $plan_type = sanitize_key((string) $plan_type);
    $legacy = array('initial' => 'basic', 'professional' => 'professional_plus', 'agency' => 'premium');
    $plan_type = $legacy[$plan_type] ?? $plan_type;
    return in_array($plan_type, array('basic', 'professional_plus', 'premium'), true) ? $plan_type : 'basic';
}

function captacion_app_ensure_user_access_meta($user_id) {
    $user_id = absint($user_id);
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
}

function captacion_app_get_user_access_state($user_id) {
    $user_id = absint($user_id);
    if (!$user_id) return array('plan_type'=>'basic','included_marketplace_accesses'=>0,'used_marketplace_accesses'=>0,'extra_marketplace_accesses'=>0,'remaining_marketplace_accesses'=>0,'credits_purchased'=>0,'last_reset_at'=>'','subscription_status'=>'guest');
    captacion_app_ensure_user_access_meta($user_id);
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
}

function captacion_app_get_access_history($user_id, $limit = 50) {
    global $wpdb;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT opportunity_id, access_type, created_at FROM " . captacion_app_access_log_table_name() . " WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
        absint($user_id), min(100, max(1, absint($limit)))
    ), ARRAY_A);
    $remaining = captacion_app_get_user_access_state($user_id)['remaining_marketplace_accesses'];
    foreach ($rows as $index => &$row) $row['balance_remaining'] = $remaining + $index;
    return $rows;
}

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
}

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
}

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
    return (bool) $inserted;
}

function captacion_app_rest_public_nonce_permission(WP_REST_Request $request) {
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('captacion_invalid_nonce', 'La sesion del formulario ha caducado. Recarga la pagina.', array('status'=>403));
    }
    return true;
}

function captacion_app_rest_private_permission(WP_REST_Request $request) {
    $nonce_check = captacion_app_rest_public_nonce_permission($request);
    if (is_wp_error($nonce_check)) return $nonce_check;
    if (!is_user_logged_in()) return new WP_Error('captacion_auth_required', 'Debes iniciar sesion.', array('status'=>401));
    if (!current_user_can('read')) return new WP_Error('captacion_permission_required', 'Tu cuenta no tiene permisos para esta accion.', array('status'=>403));
    if (!captacion_app_is_email_verified(get_current_user_id())) return new WP_Error('captacion_email_unverified', 'Confirma tu correo electronico para acceder.', array('status'=>403));
    return true;
}

function captacion_app_rest_rate_limit($scope, $limit = 10, $ttl = 600) {
    $remote_address = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key = 'captacion_rate_' . sanitize_key($scope) . '_' . substr(hash('sha256', $remote_address), 0, 24);
    $count = (int) get_transient($key);
    if ($count >= $limit) return false;
    set_transient($key, $count + 1, $ttl);
    return true;
}

function captacion_app_is_email_verified($user_id) {
    $value = get_user_meta(absint($user_id), 'captacion_email_verified', true);
    return $value === '' || $value === '1';
}

function captacion_app_send_verification_email($user_id) {
    $user = get_userdata(absint($user_id));
    if (!$user) return new WP_Error('captacion_verify_user', 'No se encontro la cuenta.', array('status'=>404));
    $token = bin2hex(random_bytes(32));
    update_user_meta($user->ID, 'captacion_email_verification_hash', hash('sha256', $token));
    update_user_meta($user->ID, 'captacion_email_verification_expires', time() + DAY_IN_SECONDS);
    $url = add_query_arg(array('captacion_verify_email'=>'1','uid'=>$user->ID,'token'=>$token), home_url('/'));
    $subject = 'Confirma tu registro en Captacion.app';
    $body = "Hola {$user->display_name},\n\nConfirma tu registro durante las proximas 24 horas:\n{$url}\n\nSi no solicitaste esta cuenta, ignora este mensaje.";
    return wp_mail($user->user_email, $subject, $body, array('Content-Type: text/plain; charset=UTF-8'))
        ? true
        : new WP_Error('captacion_verify_mail', 'No se pudo enviar el correo de confirmacion.', array('status'=>500));
}

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
add_action('template_redirect', 'captacion_app_handle_email_verification', 1);

function captacion_app_rest_register_professional(WP_REST_Request $request) {
    if (!captacion_app_rest_rate_limit('register', 6, 15 * MINUTE_IN_SECONDS)) {
        return new WP_Error('captacion_register_rate_limit', 'Demasiados intentos de registro. Espera unos minutos.', array('status'=>429));
    }

    $name = sanitize_text_field((string) $request->get_param('name'));
    $email = sanitize_email((string) $request->get_param('email'));
    $phone = preg_replace('/[^0-9+]/', '', (string) $request->get_param('phone'));
    $password = (string) $request->get_param('password');
    $privacy = filter_var($request->get_param('privacyAccepted'), FILTER_VALIDATE_BOOLEAN);
    $commercial_consent = filter_var($request->get_param('commercialConsent'), FILTER_VALIDATE_BOOLEAN);

    if (strlen($name) < 3) return new WP_Error('captacion_register_name', 'Indica nombre y apellidos.', array('status'=>422));
    if (!is_email($email)) return new WP_Error('captacion_register_email', 'Introduce un correo electronico valido.', array('status'=>422));
    if (email_exists($email)) return new WP_Error('captacion_register_exists', 'Ya existe una cuenta con este correo.', array('status'=>409));
    if (!preg_match('/^\+[1-9][0-9]{7,14}$/', $phone)) return new WP_Error('captacion_register_phone', 'Introduce un numero de contacto en formato internacional.', array('status'=>422));
    if (strlen($password) < 8) return new WP_Error('captacion_register_password', 'La contrasena debe tener al menos 8 caracteres.', array('status'=>422));
    if (!$privacy) return new WP_Error('captacion_register_privacy', 'Debes aceptar la politica de privacidad.', array('status'=>422));

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
    if (is_wp_error($user_id)) return $user_id;

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
    }

    captacion_app_log_mail_event(array(
        'category'=>'registro','source'=>'registro-profesional','email'=>$email,'name'=>$name,'phone'=>$phone,
        'message'=>'Alta profesional con perfil pendiente de completar.','tags'=>array('registro-inicio'),
        'payload'=>array('user_id'=>$user_id,'profile_complete'=>false),
    ));
    captacion_app_notify_internal_mail_event(array('category'=>'registro','source'=>'registro-profesional','email'=>$email,'name'=>$name,'phone'=>$phone));

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
}

function captacion_app_register_professional_role() {
    if (!get_role('captacion_agent')) add_role('captacion_agent', 'Profesional Captacion.app', array('read' => true, 'upload_files' => true));
}
add_action('init', 'captacion_app_register_professional_role');

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
}

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
}

function captacion_app_rest_logout() {
    wp_logout();
    return rest_ensure_response(array('ok'=>true));
}

function captacion_app_rest_access_status(WP_REST_Request $request) {
    $opportunity_id = sanitize_text_field((string) $request->get_param('opportunity_id'));
    return rest_ensure_response(array(
        'ok'=>true,
        'accessState'=>captacion_app_get_user_access_state(get_current_user_id()),
        'accessHistory'=>captacion_app_get_access_history(get_current_user_id()),
        'opportunityUnlocked'=>$opportunity_id ? captacion_app_user_has_opportunity_access(get_current_user_id(), $opportunity_id) : false,
    ));
}

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
    wp_mail($email, 'Hemos recibido tu reporte', "Hola {$name},\n\nTu reporte {$key} está en trámite y será revisado. Recibirás una respuesta en breve.\n\nCaptacion.app");
    return rest_ensure_response(array('ok'=>true,'id'=>$record_id,'reference'=>$key,'message'=>'Reporte enviado correctamente. Recibirás una confirmación por correo.'));
}

function captacion_app_user_has_opportunity_access($user_id, $opportunity_id) {
    global $wpdb;
    $table = captacion_app_access_log_table_name();
    return (bool) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE user_id = %d AND opportunity_id = %s", absint($user_id), sanitize_text_field($opportunity_id)));
}

function captacion_app_rest_consume_access(WP_REST_Request $request) {
    global $wpdb;
    $user_id = get_current_user_id();
    $opportunity_id = sanitize_text_field((string) $request->get_param('opportunity_id'));
    if (!$opportunity_id || strlen($opportunity_id) > 190) return new WP_Error('captacion_access_opportunity', 'Oportunidad no valida.', array('status'=>422));
    captacion_app_maybe_install_access_log_table();
    if (captacion_app_user_has_opportunity_access($user_id, $opportunity_id)) return rest_ensure_response(array('ok'=>true, 'already_unlocked'=>true, 'accessState'=>captacion_app_get_user_access_state($user_id)));
    $state = captacion_app_get_user_access_state($user_id);
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
    return rest_ensure_response(array('ok'=>true, 'already_unlocked'=>false, 'access_type'=>$access_type, 'accessState'=>captacion_app_get_user_access_state($user_id)));
}

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
        'message'=>$checkout_url ? 'Continua al checkout. Los accesos se concederan solo tras confirmacion del webhook.' : 'Checkout en preproduccion. Configura el Payment Link y el webhook antes de cobrar.',
    ));
}

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
}

function captacion_app_rest_list_tasks() {
    global $wpdb;
    captacion_app_maybe_install_records_table();
    $table = captacion_app_records_table_name();
    $rows = $wpdb->get_results($wpdb->prepare("SELECT record_key, title, status, related_id, payload, created_at, updated_at FROM {$table} WHERE record_type = 'task' AND user_id = %d ORDER BY updated_at DESC LIMIT 200", get_current_user_id()), ARRAY_A);
    foreach ($rows as &$row) $row['payload'] = json_decode($row['payload'] ?: '{}', true);
    return rest_ensure_response(array('ok'=>true,'tasks'=>$rows));
}

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
    }

    $settings = captacion_app_settings();
    $recipient = sanitize_email($settings['contact_email'] ?? get_option('admin_email')) ?: get_option('admin_email');
    $body = "Nombre: {$name}\nEmail: {$email}\nTelefono: {$phone}\nPreferencia: {$preference}\n\nMensaje:\n{$message}";
    $sent = wp_mail($recipient, 'Nuevo mensaje de contacto en Captacion.app', $body, array('Content-Type: text/plain; charset=UTF-8','Reply-To: ' . $name . ' <' . $email . '>'));
    captacion_app_log_mail_event(array('category'=>'contacto','source'=>'contacto','email'=>$email,'name'=>$name,'phone'=>$phone,'message'=>$message,'tags'=>array('contacto'),'payload'=>array('preference'=>$preference,'sent'=>(bool)$sent)));
    return rest_ensure_response(array('ok'=>(bool)$sent,'message'=>$sent ? 'Mensaje enviado correctamente.' : 'El mensaje se ha registrado, pero el correo no pudo enviarse.'));
}

function captacion_app_property_types() {
    return array('Piso', 'Casa / chalet', 'Ático', 'Dúplex', 'Apartamento', 'Estudio', 'Finca rústica con vivienda', 'Edificio residencial', 'Local comercial', 'Nave', 'Oficina', 'Terreno / solar', 'Garaje', 'Trastero');
}

function captacion_app_normalize_property_type($value) {
    $value = sanitize_text_field((string) $value);
    $legacy = array(
        'Casa/Chalet' => 'Casa / chalet',
        'Casa / Chalet' => 'Casa / chalet',
        'Local Comercial' => 'Local comercial',
        'Edificio' => 'Edificio residencial',
        'Suelo/Terreno' => 'Terreno / solar',
        'Suelo / Terreno' => 'Terreno / solar',
    );
    return $legacy[$value] ?? $value;
}

function captacion_app_property_conditions() {
    return array('Lista para entrar / operar', 'Buen estado', 'De origen', 'Sin reforma necesaria', 'Necesita actualización', 'Reforma menor', 'Reforma mayor', 'Reforma integral', 'En obras', 'Obra nueva', 'No califica');
}

function captacion_app_offer_mandates() {
    return array('Sí, con exclusividad', 'Encargo de agente único', 'Exclusiva compartida', 'No, nota de encargo abierta', 'Sin exclusiva formalizada', 'Pendiente de confirmar');
}

function captacion_app_need_mandates() {
    return array('Con exclusividad', 'Encargo de agente único', 'Exclusiva compartida', 'Nota de encargo abierta', 'Sin exclusiva formalizada', 'Pendiente de confirmar', 'Cualquiera');
}

function captacion_app_urgencies() {
    return array('Alta', 'Media', 'Baja', 'Sin urgencia definida');
}

function captacion_app_documentation_levels() {
    return array('Nota simple únicamente', 'Nota simple + planos', 'Nota simple + certificado energético', 'Nota simple + planos + certificado energético', 'Expediente jurídico completo', 'Tasación disponible', 'Expediente jurídico completo + tasación', 'No califica');
}

function captacion_app_residential_types() {
    return array('Piso', 'Casa / chalet', 'Ático', 'Dúplex', 'Apartamento', 'Estudio', 'Finca rústica con vivienda', 'Edificio residencial');
}

function captacion_app_conditions_for_type($type) {
    if (in_array($type, captacion_app_residential_types(), true)) return captacion_app_property_conditions();
    if (in_array($type, array('Local comercial', 'Nave', 'Oficina'), true)) return array('Lista para entrar / operar', 'Buen estado', 'Necesita actualización', 'Reforma menor', 'Reforma mayor', 'Reforma integral', 'En obras', 'No califica');
    if ($type === 'Terreno / solar') return array('No califica');
    if (in_array($type, array('Garaje', 'Trastero'), true)) return array('Buen estado', 'Necesita actualización', 'No califica');
    return array('No califica');
}

function captacion_app_enum_value($value, $allowed, $field_label) {
    $value = sanitize_text_field((string) $value);
    if (!in_array($value, $allowed, true)) {
        return new WP_Error('captacion_invalid_enum', sprintf('El valor de %s no es válido.', $field_label), array('status' => 422));
    }
    return $value;
}

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
}

function captacion_app_positive_number($value, $field_label) {
    $number = is_numeric($value) ? (float) $value : 0;
    if ($number <= 0) {
        return new WP_Error('captacion_invalid_number', sprintf('%s debe ser mayor que cero.', $field_label), array('status' => 422));
    }
    return $number;
}

function captacion_app_sanitize_real_estate_payload($record_type, $payload) {
    $payload = is_array($payload) ? $payload : array();
    $type = captacion_app_normalize_property_type($payload['property_type'] ?? $payload['type'] ?? '');
    $type = captacion_app_enum_value($type, captacion_app_property_types(), 'tipo de inmueble');
    if (is_wp_error($type)) return $type;

    $title = sanitize_text_field((string) ($payload['title'] ?? ''));
    $description = sanitize_textarea_field((string) ($payload['description'] ?? ''));
    if (strlen($title) < 8) return new WP_Error('captacion_short_title', 'El título debe tener al menos 8 caracteres.', array('status' => 422));
    if (strlen($description) < 30) return new WP_Error('captacion_short_description', 'La descripción debe tener al menos 30 caracteres.', array('status' => 422));

    $community_code = sanitize_text_field((string) ($payload['community_code'] ?? $payload['autonomous_community_id'] ?? ''));
    $province_code = sanitize_text_field((string) ($payload['province_code'] ?? $payload['province_id'] ?? ''));
    $municipality_code = sanitize_text_field((string) ($payload['municipality_code'] ?? $payload['municipality_ine_code'] ?? $payload['municipality_id'] ?? ''));
    if (!$community_code || !$province_code || !$municipality_code) {
        return new WP_Error('captacion_required_territory', 'Comunidad autónoma, provincia y municipio son obligatorios.', array('status' => 422));
    }

    $is_property = $record_type === 'property';
    $area = captacion_app_positive_number($is_property ? ($payload['total_area_m2'] ?? $payload['superficie_construida'] ?? $payload['surface'] ?? 0) : ($payload['desired_area_min_m2'] ?? $payload['surface'] ?? 0), $is_property ? 'La superficie total' : 'La superficie mínima');
    $amount = captacion_app_positive_number($is_property ? ($payload['indicative_price'] ?? $payload['price'] ?? 0) : ($payload['max_budget'] ?? $payload['budget'] ?? 0), $is_property ? 'El precio orientativo' : 'El presupuesto máximo');
    if (is_wp_error($area)) return $area;
    if (is_wp_error($amount)) return $amount;

    $commission = sanitize_text_field((string) ($is_property ? ($payload['offered_commission'] ?? $payload['fee'] ?? '') : ($payload['accepted_commission'] ?? $payload['feeSplit'] ?? '')));
    if (!$commission || strlen($commission) > 60) return new WP_Error('captacion_invalid_commission', 'Indica una comisión o colaboración válida.', array('status' => 422));

    $residential = in_array($type, captacion_app_residential_types(), true);
    $bathrooms_apply = $residential || in_array($type, array('Local comercial', 'Nave', 'Oficina'), true);
    $rooms = absint($is_property ? ($payload['rooms'] ?? $payload['bedrooms'] ?? 0) : ($payload['min_rooms'] ?? $payload['bedrooms'] ?? 0));
    $bathrooms = absint($is_property ? ($payload['bathrooms'] ?? 0) : ($payload['min_bathrooms'] ?? $payload['bathrooms'] ?? 0));
    if ($residential && $type !== 'Estudio' && $rooms < 1) return new WP_Error('captacion_required_rooms', 'El número de habitaciones es obligatorio para vivienda.', array('status' => 422));
    if ($bathrooms_apply && $bathrooms < 1) return new WP_Error('captacion_required_bathrooms', 'El número de baños es obligatorio para este tipo de inmueble.', array('status' => 422));
    if (!$residential) $rooms = 0;
    if (!$bathrooms_apply) $bathrooms = 0;

    $urgency = captacion_app_enum_value($is_property ? ($payload['sale_urgency'] ?? $payload['urgency'] ?? '') : ($payload['search_urgency'] ?? $payload['urgency'] ?? ''), captacion_app_urgencies(), $is_property ? 'urgencia de venta' : 'urgencia de búsqueda');
    $docs = captacion_app_enum_value($is_property ? ($payload['documentation_level'] ?? $payload['docs'] ?? '') : ($payload['required_documentation_level'] ?? ''), captacion_app_documentation_levels(), 'nivel de documentación');
    if (is_wp_error($urgency)) return $urgency;
    if (is_wp_error($docs)) return $docs;

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
    $clean['bathrooms'] = $bathrooms;

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
}

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
    $now = current_time('mysql');
    $row = array(
        'record_type' => $type,
        'record_key' => $record_key,
        'user_id' => absint($data['user_id'] ?? get_current_user_id()),
        'user_email' => sanitize_email($data['user_email'] ?? ''),
        'title' => sanitize_text_field($data['title'] ?? ''),
        'status' => sanitize_text_field($data['status'] ?? ''),
        'related_id' => sanitize_text_field($data['related_id'] ?? ''),
        'payload' => wp_json_encode($payload),
        'updated_at' => $now,
    );
    $existing_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE record_type = %s AND record_key = %s", $type, $record_key));
    if ($existing_id) {
        $wpdb->update($table, $row, array('id' => absint($existing_id)));
        return absint($existing_id);
    }
    $row['created_at'] = $now;
    $wpdb->insert($table, $row);
    return absint($wpdb->insert_id);
}

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
    $result = captacion_app_upsert_record(array(
        'record_type' => $record_type,
        'record_key' => $payload['record_key'] ?? '',
        'user_id' => get_current_user_id(),
        'user_email' => wp_get_current_user()->user_email,
        'title' => $record_payload['title'] ?? $payload['title'] ?? '',
        'status' => $payload['status'] ?? '',
        'related_id' => $payload['related_id'] ?? '',
        'payload' => $record_payload,
    ));
    if (is_wp_error($result)) {
        return $result;
    }
    return rest_ensure_response(array('ok' => true, 'id' => $result));
}

function captacion_app_rest_list_records(WP_REST_Request $request) {
    global $wpdb;
    captacion_app_maybe_install_records_table();
    $table = captacion_app_records_table_name();
    $type = sanitize_key((string) $request->get_param('record_type'));
    $email = sanitize_email((string) $request->get_param('user_email'));
    $limit = min(200, max(1, absint($request->get_param('limit') ?: 100)));
    $where = array('1=1');
    $params = array();
    if (!current_user_can('manage_options')) { $where[] = 'user_id = %d'; $params[] = get_current_user_id(); }
    if ($type) { $where[] = 'record_type = %s'; $params[] = $type; }
    if ($email && current_user_can('manage_options')) { $where[] = 'user_email = %s'; $params[] = $email; }
    $sql = "SELECT * FROM {$table} WHERE " . implode(' AND ', $where) . " ORDER BY updated_at DESC LIMIT %d";
    $params[] = $limit;
    $rows = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    foreach ($rows as &$row) {
        $row['payload'] = json_decode($row['payload'] ?: '{}', true);
    }
    return rest_ensure_response(array('ok' => true, 'records' => $rows));
}

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
}
add_action('rest_api_init', 'captacion_app_register_records_routes');

function captacion_app_generic_lost_password_error($errors) {
    if (!is_wp_error($errors) || !$errors->has_errors()) return $errors;
    $generic = 'Si el correo está registrado, recibirás instrucciones para restablecer tu contraseña.';
    foreach ($errors->get_error_codes() as $code) {
        $errors->remove($code);
        $errors->add($code, $generic);
    }
    return $errors;
}
add_filter('lostpassword_errors', 'captacion_app_generic_lost_password_error');

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
}

function captacion_app_notify_internal_mail_event($data) {
    $admin_email = 'inmobia360@gmail.com';
    $category = sanitize_key($data['category'] ?? 'general');
    $source = sanitize_text_field($data['source'] ?? '');
    $subject_map = array(
        'registro' => 'Nuevo registro en Captacion.app',
        'contacto' => 'Nuevo mensaje de contacto en Captacion.app',
        'reporte_denuncia' => 'Nuevo reporte en el canal de denuncias',
        'busco_captacion' => 'Nueva demanda publicada en Captacion.app',
        'ofrecer_captacion' => 'Nueva captacion publicada en Captacion.app',
    );
    $subject = $subject_map[$category] ?? 'Nuevo evento en Captacion.app';
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
}


function captacion_app_notification_templates() {
    return array(
        'welcome' => array(
            'category' => 'registro',
            'subject' => 'Bienvenido a Captacion.app',
            'body' => "Hola {name},\n\nTu cuenta profesional en Captacion.app se ha creado correctamente. Desde tu panel privado podras publicar demandas, revisar captaciones compatibles y recibir notificaciones cuando aparezcan oportunidades relevantes.\n\nEquipo Captacion.app",
        ),
        'contact_received' => array(
            'category' => 'contacto',
            'subject' => 'Hemos recibido tu mensaje en Captacion.app',
            'body' => "Hola {name},\n\nHemos recibido tu mensaje y lo estamos revisando. En breve recibiras una comunicacion referente a tu consulta.\n\nEquipo Captacion.app",
        ),
        'report_received' => array(
            'category' => 'reporte_denuncia',
            'subject' => 'Tu reporte esta en tramite',
            'body' => "Hola {name},\n\nTu reporte asociado a tu cuenta ha quedado registrado con la referencia {reference}. El mensaje esta en tramite y recibiras una respuesta en breve.\n\nEquipo Captacion.app",
        ),
        'match_need' => array(
            'category' => 'busco_captacion',
            'subject' => 'Nueva captacion compatible con tu demanda',
            'body' => "Hola {name},\n\nSe ha detectado una captacion compatible con tu demanda: {reference}. Puedes revisar la oportunidad desde tu panel privado, en la seccion Notificaciones.\n\nEquipo Captacion.app",
        ),
        'match_property' => array(
            'category' => 'ofrecer_captacion',
            'subject' => 'Nueva demanda compatible con tu captacion',
            'body' => "Hola {name},\n\nSe ha detectado una demanda compatible con tu captacion: {reference}. Puedes revisar la oportunidad desde tu panel privado, en la seccion Notificaciones.\n\nEquipo Captacion.app",
        ),
        'no_match_watch' => array(
            'category' => 'general',
            'subject' => 'Alerta activada para futuras coincidencias',
            'body' => "Hola {name},\n\nPor ahora no se han detectado coincidencias directas para {reference}. La alerta queda activa y te avisaremos en tu panel privado, seccion Notificaciones, cuando aparezca una compatibilidad real.\n\nEquipo Captacion.app",
        ),
    );
}

function captacion_app_render_notification_text($template, $data) {
    $name = sanitize_text_field($data['name'] ?? '');
    $reference = sanitize_text_field($data['reference'] ?? 'tu publicacion');
    $message = sanitize_textarea_field($data['message'] ?? '');
    return strtr((string) $template, array(
        '{name}' => $name ? $name : 'Hola',
        '{reference}' => $reference ? $reference : 'tu publicacion',
        '{message}' => $message,
    ));
}

function captacion_app_send_notification_email(WP_REST_Request $request) {
    $email = sanitize_email((string) $request->get_param('email'));
    if (!is_email($email)) {
        return new WP_Error('captacion_notification_invalid_email', 'Email no valido.', array('status' => 400));
    }

    $type = sanitize_key((string) $request->get_param('type'));
    $templates = captacion_app_notification_templates();
    if (!isset($templates[$type])) {
        $type = 'no_match_watch';
    }

    $name = sanitize_text_field((string) $request->get_param('name'));
    $agency = sanitize_text_field((string) $request->get_param('agency'));
    $reference = sanitize_text_field((string) $request->get_param('reference'));
    $message = sanitize_textarea_field((string) $request->get_param('message'));
    $template = $templates[$type];
    $subject = captacion_app_render_notification_text($template['subject'], compact('name', 'reference', 'message'));
    $body = captacion_app_render_notification_text($template['body'], compact('name', 'reference', 'message'));

    $headers = array(
        'Content-Type: text/plain; charset=UTF-8',
        'Bcc: inmobia360@gmail.com',
    );
    $sent = wp_mail($email, $subject, $body, $headers);

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
    captacion_app_notify_internal_mail_event($event_data);

    return rest_ensure_response(array(
        'ok' => (bool) $sent,
        'type' => $type,
        'email' => $email,
    ));
}

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
add_action('rest_api_init', 'captacion_app_register_notification_routes');

function captacion_app_mailchimp_subscribe(WP_REST_Request $request) {
    $settings = captacion_app_settings();
    $api_key = trim((string) ($settings['mailchimp_api_key'] ?? ''));
    $audience_id = trim((string) ($settings['mailchimp_audience_id'] ?? ''));
    $email = sanitize_email((string) $request->get_param('email'));
    $commercial_consent = filter_var($request->get_param('commercialConsent'), FILTER_VALIDATE_BOOLEAN);

    if (!$commercial_consent) {
        return new WP_Error('captacion_mailchimp_consent_required', 'Se requiere consentimiento comercial separado para suscribirse.', array('status' => 422));
    }

    if (!$api_key || !$audience_id) {
        return new WP_Error('captacion_mailchimp_missing_settings', 'Mailchimp no esta configurado.', array('status' => 400));
    }
    if (!is_email($email)) {
        return new WP_Error('captacion_mailchimp_invalid_email', 'Email no valido.', array('status' => 400));
    }

    $datacenter = captacion_app_mailchimp_datacenter($api_key);
    if (!$datacenter) {
        return new WP_Error('captacion_mailchimp_invalid_key', 'API Key de Mailchimp no valida.', array('status' => 400));
    }

    $allowed_tags = captacion_app_mailchimp_allowed_tags();
    $raw_tags = $request->get_param('tags');
    $raw_tags = is_array($raw_tags) ? $raw_tags : array($request->get_param('tag'));
    $tags = array_values(array_intersect($allowed_tags, array_map('sanitize_key', array_filter($raw_tags))));
    if (!$tags) {
        $tags = array('contacto');
    }

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
    }

    $member_hash = md5(strtolower($email));
    $url = sprintf('https://%s.api.mailchimp.com/3.0/lists/%s/members/%s', $datacenter, rawurlencode($audience_id), $member_hash);
    $status = !empty($settings['mailchimp_double_optin']) && $settings['mailchimp_double_optin'] === '1' ? 'pending' : 'subscribed';

    $body = array(
        'email_address' => $email,
        'status_if_new' => $status,
        'status' => $status,
        'merge_fields' => array_filter(array(
            'FNAME' => $name,
        )),
        'tags' => $tags,
    );

    $response = wp_remote_request($url, array(
        'method' => 'PUT',
        'headers' => array(
            'Authorization' => 'Basic ' . base64_encode('captacion:' . $api_key),
            'Content-Type' => 'application/json',
        ),
        'body' => wp_json_encode($body),
        'timeout' => 12,
    ));

    if (is_wp_error($response)) {
        return new WP_Error('captacion_mailchimp_request_failed', $response->get_error_message(), array('status' => 502));
    }

    $code = wp_remote_retrieve_response_code($response);
    $payload = json_decode(wp_remote_retrieve_body($response), true);
    if ($code < 200 || $code >= 300) {
        $api_message = is_array($payload) && !empty($payload['detail']) ? $payload['detail'] : 'Mailchimp no pudo guardar el contacto.';
        return new WP_Error('captacion_mailchimp_api_error', $api_message, array('status' => $code ?: 502));
    }

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
    }

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
    captacion_app_notify_internal_mail_event($event_data);

    return rest_ensure_response(array(
        'ok' => true,
        'email' => $email,
        'tags' => $tags,
        'mailchimp_id' => is_array($payload) && isset($payload['id']) ? $payload['id'] : '',
    ));
}

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
add_action('rest_api_init', 'captacion_app_register_mailchimp_routes');


function captacion_app_is_demo_environment() {
    if (defined('CAPTACION_APP_STAGING') && CAPTACION_APP_STAGING) {
        return true;
    }

    $environment = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
    return in_array($environment, array('local', 'development', 'staging'), true);
}

function captacion_app_wp_robots($robots) {
    if (captacion_app_is_demo_environment()) {
        $robots['noindex'] = true;
        $robots['nofollow'] = true;
        $robots['noarchive'] = true;
        $robots['max-image-preview'] = 'none';
        $robots['max-snippet'] = -1;
        $robots['max-video-preview'] = -1;
    }

    return $robots;
}
add_filter('wp_robots', 'captacion_app_wp_robots');

function captacion_app_send_demo_headers() {
    if (captacion_app_is_demo_environment()) {
        header('X-Robots-Tag: noindex, nofollow, noarchive', true);
    }
}
add_action('send_headers', 'captacion_app_send_demo_headers');

function captacion_app_page_meta_descriptions() {
    return array(
        'inicio' => 'Compra Captacion es un marketplace B2B de captaciones inmobiliarias para profesionales. Publica oportunidades, busca demandas activas y colabora con acceso protegido.',
        'marketplace' => 'Compra Captacion es un marketplace inmobiliario B2B para agentes y agencias. Revisa captaciones, publica demandas y colabora con acceso protegido.',
        'buscar-captaciones' => 'Publica demandas inmobiliarias y encuentra captaciones compatibles para clientes compradores. Compra Captacion conecta agentes con oportunidades protegidas.',
        'ofrecer-captacion' => 'Publica y vende captaciones inmobiliarias con acceso protegido. Compra Captacion ayuda a agentes y agencias a compartir oportunidades y colaborar.',
        'como-funciona' => 'Descubre como funciona Compra Captacion: publica captaciones, crea demandas, cruza oportunidades y colabora con acceso protegido y trazabilidad.',
        'recursos' => 'Encuentra herramientas IA para agentes inmobiliarios: asistentes, calculadoras, plantillas, generadores de textos y recursos para mejorar productividad.',
        'planes' => 'Elige tu plan en Compra Captacion: acceso inicial gratis, herramientas para agentes inmobiliarios y plan profesional para publicar, buscar y colaborar.',
        'contacto' => 'Contacta con Compra Captacion para solicitar acceso, resolver dudas sobre planes o valorar colaboraciones profesionales en captaciones inmobiliarias.',
        'area-privada' => 'Vision funcional del area privada de Captacion.app: solicitudes, favoritos, tareas, alertas y trazabilidad de operaciones.',
        'aviso-legal' => 'Aviso legal base de Captacion.app pendiente de completar con los datos mercantiles verificados de la sociedad titular.',
        'privacidad' => 'Politica de privacidad base de Captacion.app con foco en minimizacion de datos, trazabilidad y acceso profesional.',
        'cookies' => 'Politica de cookies de Captacion.app con tecnologias necesarias y activacion condicionada de analitica y marketing.',
        'normas-publicacion' => 'Normas de publicacion para compartir captaciones y demandas con calidad, legalidad y confidencialidad dentro de Captacion.app.',
        'condiciones-de-contratacion' => 'Condiciones de contratacion de Captacion.app para acceso a planes, servicios, altas y uso de la plataforma en su version final.',
        'canal-de-denuncias' => 'Canal interno de informacion de Captacion.app para comunicar incumplimientos, irregularidades o preocupaciones de forma confidencial.',
    );
}

function captacion_app_output_meta_description() {
    if (defined('RANK_MATH_VERSION')) {
        return;
    }

    if (!is_page()) {
        return;
    }

    $post = get_queried_object();
    if (!$post || empty($post->post_name)) {
        return;
    }

    $descriptions = captacion_app_page_meta_descriptions();
    if (!isset($descriptions[$post->post_name])) {
        return;
    }

    echo '<meta name="description" content="' . esc_attr($descriptions[$post->post_name]) . '">' . "\n";
}
add_action('wp_head', 'captacion_app_output_meta_description', 1);

function captacion_app_seed_content_map() {
    return array(
        'inicio' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Captaciones inmobiliarias para profesionales</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Captaciones inmobiliarias para agentes, agencias e inversores que quieren compartir oportunidades, cruzar demandas y colaborar con control. Compra Captacion conecta profesionales del sector para publicar captaciones, buscar oportunidades y trabajar operaciones con trazabilidad.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Captaciones inmobiliarias para profesionales en Compra Captacion"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>La plataforma esta pensada para un mercado donde una parte puede tener la captacion y otra puede tener el comprador, el inversor o la demanda activa. En lugar de depender de mensajes sueltos, llamadas informales o contactos sin seguimiento, Compra Captacion organiza cada oportunidad en un entorno B2B con informacion limitada, acceso protegido y reglas claras para colaborar.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Comprar captaciones inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Comprar captaciones inmobiliarias no significa saltarse el trabajo profesional de otro agente. Significa acceder a una oportunidad validada, revisar sus condiciones de colaboracion y decidir si tiene encaje comercial antes de avanzar. El objetivo es que el profesional que busca producto pueda encontrar captaciones compatibles por zona, tipologia, precio y contexto de operacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Vender captaciones inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vender captaciones inmobiliarias permite a quien ha generado una oportunidad monetizar su trabajo sin exponer datos sensibles desde el primer momento. La ficha publica puede mostrar la informacion comercial necesaria para despertar interes, mientras los datos privados, contactos, documentos y condiciones completas se reservan para fases posteriores del flujo.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Colaboracion inmobiliaria 50/50</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La colaboracion inmobiliaria 50/50 es una de las formas mas simples de trabajar entre profesionales: un agente aporta la captacion y otro aporta comprador o demanda. Compra Captacion ayuda a ordenar ese proceso, dejar constancia de cada paso y reducir malentendidos sobre acceso, honorarios, disponibilidad y seguimiento de la operacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Marketplace inmobiliario B2B</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Como marketplace inmobiliario B2B, la web no esta orientada al publico final, sino a profesionales que necesitan revisar oportunidades, publicar demandas, comparar opciones y construir relaciones comerciales con mas seguridad. Por eso la propuesta combina captaciones inmobiliarias, demandas activas, recursos, planes profesionales y area privada.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Desde aqui puedes visitar el <a href="/marketplace/">marketplace</a>, publicar una demanda en <a href="/buscar-captaciones/">buscar captaciones</a>, revisar como <a href="/ofrecer-captacion/">ofrecer una captacion</a>, consultar <a href="/recursos/">recursos profesionales</a> o comparar los <a href="/planes/">planes disponibles</a>.</p><!-- /wp:paragraph -->
HTML,
        'marketplace' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Marketplace inmobiliario B2B de captaciones y demandas</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Un marketplace inmobiliario B2B permite a agentes y agencias compartir captaciones, publicar demandas activas y encontrar oportunidades de colaboracion sin exponer informacion sensible desde el primer momento. Compra Captacion conecta profesionales que tienen producto con profesionales que tienen comprador.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/property-defaults/edificio-default.jpg" alt="Marketplace inmobiliario B2B de captaciones y demandas"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Esta pagina debe explicar el centro de la plataforma: un espacio privado para revisar captaciones inmobiliarias, demandas inmobiliarias y colaboraciones entre profesionales. No funciona como un portal abierto para particulares, sino como una red inmobiliaria profesional con reglas de acceso, contexto comercial y trazabilidad.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Captaciones inmobiliarias activas</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las captaciones inmobiliarias activas son oportunidades publicadas por profesionales que tienen relacion con un propietario, un inmueble o un activo vendible. En el marketplace se presentan con informacion orientativa: tipologia, zona, precio, modalidad, estado comercial y condiciones de colaboracion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Demandas inmobiliarias de compradores</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las demandas inmobiliarias permiten que un agente con cliente comprador indique que tipo de propiedad busca. Al cruzar demanda y captacion, Compra Captacion ayuda a detectar coincidencias y abrir conversaciones con mas probabilidad de cierre.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Colaboracion entre agentes y agencias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La colaboracion inmobiliaria es el eje del marketplace. Un profesional puede aportar la captacion y otro puede aportar comprador, inversor o demanda activa. La plataforma ayuda a ordenar la solicitud, el acceso, las condiciones y el seguimiento de cada oportunidad.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Marketplace privado, no portal para particulares</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captacion no compite con un portal inmobiliario tradicional para publico final. Su objetivo es facilitar trabajo B2B entre agentes inmobiliarios, agencias, inversores profesionales y equipos que necesitan compartir producto captado con mayor control.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Oportunidades fuera de mercado</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Muchas oportunidades inmobiliarias no se publican de forma masiva en portales abiertos. El marketplace puede ayudar a trabajar propiedades fuera de mercado, activos discretos, captaciones exclusivas o colaboraciones que requieren privacidad antes de compartir datos sensibles.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como se protege cada operacion</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La direccion exacta, los datos del propietario, documentos privados y contactos directos deben quedar reservados hasta que exista un profesional cualificado y una solicitud con contexto. Esta capa de proteccion permite que el captador mantenga el control y que la contraparte revise la oportunidad con trazabilidad.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Ventajas frente a un portal inmobiliario tradicional</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Trabajar solo con profesionales y no con trafico generalista.</li><li>Publicar captaciones inmobiliarias con informacion limitada y acceso protegido.</li><li>Conectar demandas inmobiliarias con oportunidades compatibles.</li><li>Ordenar la colaboracion entre agencias y agentes con reglas claras.</li><li>Relacionar el marketplace con <a href="/buscar-captaciones/">buscar captaciones</a>, <a href="/ofrecer-captacion/">ofrecer captacion</a>, <a href="/como-funciona/">como funciona</a>, <a href="/planes/">planes</a>, <a href="/recursos/">recursos</a> y <a href="/normas-publicacion/">normas de publicacion</a>.</li></ul><!-- /wp:list -->
HTML,
        'buscar-captaciones' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Buscar captaciones inmobiliarias para clientes compradores</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Buscar captaciones inmobiliarias es clave cuando un agente tiene un cliente comprador, una demanda activa o una oportunidad de venta y necesita encontrar una propiedad compatible. Compra Captacion permite publicar lo que busca tu cliente y cruzarlo con captaciones disponibles dentro de un entorno profesional, protegido y trazable.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/property-defaults/piso-default.jpg" alt="Buscar captaciones inmobiliarias para clientes compradores"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Esta seccion esta pensada para agentes inmobiliarios, agencias, personal shopper inmobiliario y profesionales que representan a un comprador real. El objetivo no es navegar inventario sin criterio, sino transformar una necesidad concreta en una demanda inmobiliaria clara: zona, presupuesto, tipo de propiedad, urgencia, solvencia y condiciones de colaboracion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Publica una demanda inmobiliaria activa</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Una demanda inmobiliaria activa describe lo que busca un cliente comprador y permite que otros profesionales detecten si tienen una captacion compatible. Cuanto mejor se define la demanda, mas facil es encontrar propiedades que encajen con el comprador y evitar conversaciones improductivas.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Tipo de inmueble que busca el cliente comprador.</li><li>Municipio, zona o radio de busqueda prioritario.</li><li>Presupuesto minimo y maximo con margen realista.</li><li>Habitaciones, superficie, estado, uso previsto y requisitos no negociables.</li><li>Timing de compra, solvencia y condiciones de colaboracion.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Encuentra captaciones compatibles</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El valor de buscar captaciones inmobiliarias dentro de Compra Captacion esta en el cruce entre oferta y demanda. Si otro profesional tiene una propiedad, un activo discreto o una captacion fuera de mercado que encaja con tu comprador, la plataforma ayuda a identificar la coincidencia y ordenar el siguiente paso sin exponer datos sensibles antes de tiempo.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Trabaja como agente del comprador</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El agente del comprador necesita herramientas para localizar oportunidades, comparar alternativas y negociar con informacion suficiente. Esta pagina debe comunicar que Compra Captacion no es solo una lista de inmuebles, sino un marketplace inmobiliario B2B donde la demanda profesional tambien tiene protagonismo.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Accede a propiedades fuera de mercado</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Muchas oportunidades no aparecen en portales abiertos o todavia no estan preparadas para publicarse de forma masiva. Por eso una red de captaciones protegidas puede ser util para encontrar propiedades fuera de mercado, activos discretos o colaboraciones que solo tienen sentido entre profesionales verificados.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como funciona la busqueda de captaciones</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El flujo recomendado es simple: defines la demanda, el sistema la cruza con captaciones inmobiliarias disponibles, revisas coincidencias, solicitas acceso protegido y avanzas solo cuando existe encaje real. Este modelo reduce ruido, protege al captador y ayuda al profesional con comprador a concentrarse en oportunidades con mas probabilidad de cierre.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Ventajas para agentes con cliente comprador</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Convertir una necesidad del comprador en una busqueda profesional estructurada.</li><li>Acceder a captaciones compatibles sin depender solo de portales generalistas.</li><li>Trabajar demandas inmobiliarias con trazabilidad y contexto comercial.</li><li>Encontrar oportunidades para colaborar con otros agentes de forma ordenada.</li><li>Conectar la busqueda con el <a href="/marketplace/">marketplace</a>, la pagina para <a href="/ofrecer-captacion/">ofrecer captacion</a>, el flujo de <a href="/como-funciona/">como funciona</a>, los <a href="/planes/">planes</a> y los <a href="/recursos/">recursos profesionales</a>.</li></ul><!-- /wp:list -->
HTML,
        'ofrecer-captacion' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Vender captaciones inmobiliarias con acceso protegido</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Vender captaciones inmobiliarias permite a agentes y agencias monetizar oportunidades reales sin exponer datos sensibles desde el primer momento. Compra Captacion ayuda a publicar captaciones, encontrar profesionales con demanda activa y abrir colaboraciones con trazabilidad.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/property-defaults/casa-chalet-default.jpg" alt="Vender captaciones inmobiliarias con acceso protegido"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Esta pagina esta pensada para profesionales que ya tienen una captacion inmobiliaria, un propietario vendedor, una oportunidad discreta o un inmueble con potencial de salida. En lugar de regalar toda la informacion desde el inicio, la plataforma permite publicar una ficha orientativa, controlar el acceso y decidir con quien avanzar.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Publica una captacion inmobiliaria</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Publicar captaciones inmobiliarias en Compra Captacion significa convertir una oportunidad en una ficha profesional lista para ser cruzada con demandas activas. La informacion publica debe ser suficiente para despertar interes: tipologia, zona aproximada, precio, estado de la operacion, condiciones de colaboracion y nivel de exclusividad.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Tipo de inmueble, municipio y zona de actuacion.</li><li>Precio estimado, superficie, habitaciones y estado comercial.</li><li>Modalidad: colaboracion, venta de captacion o acceso protegido.</li><li>Condiciones de honorarios y reparto previsto.</li><li>Documentacion disponible y nivel de confidencialidad necesario.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Convierte propietarios vendedores en oportunidades</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La captacion de propietarios es una de las tareas mas valiosas del negocio inmobiliario. Cuando un propietario vendedor confia en un profesional, esa relacion puede transformarse en una oportunidad comercial para otros agentes con comprador, inversores o agencias que trabajan esa zona.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Vende una captacion 100%</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>En algunos casos el profesional puede querer vender una captacion 100%, ceder la gestion completa o transferir la oportunidad a otro operador mejor posicionado. Esta modalidad debe explicarse con claridad para que el comprador entienda que adquiere una oportunidad profesional, no una simple direccion o dato sin contexto.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Comparte captaciones en colaboracion inmobiliaria</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Tambien puedes compartir captaciones inmobiliarias mediante colaboracion 50/50, reparto de honorarios u otro acuerdo entre profesionales. Compra Captacion ayuda a ordenar la solicitud, la disponibilidad, el acceso a datos y la trazabilidad de cada paso antes de abrir informacion sensible.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Protege los datos sensibles del propietario</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La direccion exacta, los datos personales del propietario, documentacion privada, referencia catastral completa y contactos directos deben quedar protegidos hasta que exista una contraparte cualificada. Esa proteccion aumenta la confianza del captador y reduce el riesgo de fugas de informacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como funciona ofrecer una captacion</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El flujo recomendado es sencillo: preparas la ficha, publicas la captacion, recibes solicitudes de profesionales interesados, revisas compatibilidad y avanzas solo cuando el contexto tiene sentido. Asi la captacion inmobiliaria pasa de ser un dato aislado a una oportunidad estructurada dentro de un marketplace inmobiliario B2B.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Ventajas para agentes y agencias</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Monetizar captaciones inmobiliarias sin perder control del activo.</li><li>Conectar con agentes que tienen demanda o cliente comprador.</li><li>Publicar oportunidades sin depender solo de portales generalistas.</li><li>Trabajar colaboraciones con mas contexto, reglas y trazabilidad.</li><li>Relacionar la captacion con el <a href="/marketplace/">marketplace</a>, las <a href="/buscar-captaciones/">demandas activas</a>, el flujo de <a href="/como-funciona/">como funciona</a>, los <a href="/planes/">planes</a>, los <a href="/recursos/">recursos</a> y las <a href="/normas-publicacion/">normas de publicacion</a>.</li></ul><!-- /wp:list -->
HTML,
        'como-funciona' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Como funciona Compra Captacion</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captacion funciona como una plataforma inmobiliaria B2B para agentes, agencias y profesionales que quieren publicar captaciones, crear demandas activas, cruzar oportunidades compatibles y colaborar con acceso protegido. El objetivo es ordenar lo que normalmente se gestiona por llamadas, mensajes privados y contactos dispersos.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Como funciona una plataforma inmobiliaria B2B"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>La plataforma no esta pensada como un portal abierto para particulares, sino como un entorno profesional donde la informacion sensible se comparte por fases. Primero se publica el contexto comercial necesario; despues se valida el interes, la compatibilidad y las condiciones de colaboracion antes de avanzar.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Publica una captacion o una demanda</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El profesional puede publicar una captacion inmobiliaria cuando tiene una oportunidad de propietario vendedor, un inmueble, un activo discreto o una operacion que puede interesar a otros agentes. Tambien puede crear una demanda inmobiliaria cuando tiene un cliente comprador o inversor que busca una propiedad concreta.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Cruza oportunidades compatibles</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El sistema ayuda a relacionar captaciones y demandas por criterios como zona, tipologia, precio, estado comercial, modalidad de colaboracion y contexto de operacion. Asi, un agente con comprador puede encontrar producto y un captador puede detectar profesionales con demanda real.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>3. Solicita acceso protegido</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los datos sensibles no deben mostrarse desde el primer momento. La direccion exacta, los datos del propietario, documentos privados y contactos directos quedan protegidos hasta que exista una solicitud con contexto profesional y una razon clara para avanzar.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>4. Define condiciones de colaboracion</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Antes de compartir informacion completa, las partes pueden revisar si la operacion sera una colaboracion 50/50, una venta de captacion, un acceso puntual o una relacion comercial con condiciones especificas. Esta claridad reduce malentendidos sobre honorarios, seguimiento y responsabilidades.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Trabaja la operacion con trazabilidad</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Cada solicitud, desbloqueo, favorito, tarea o avance debe quedar asociado a un flujo claro. La trazabilidad es importante para proteger al captador, ordenar la relacion con la contraparte y evitar que una oportunidad profesional se pierda entre conversaciones sueltas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Cierra mas operaciones entre profesionales</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El resultado buscado es que los agentes inmobiliarios puedan cerrar mas operaciones porque encuentran producto, demanda o colaboracion en el momento adecuado. Compra Captacion conecta el <a href="/marketplace/">marketplace</a>, la opcion de <a href="/buscar-captaciones/">buscar captaciones</a>, la publicacion para <a href="/ofrecer-captacion/">ofrecer captacion</a>, los <a href="/recursos/">recursos profesionales</a>, los <a href="/planes/">planes</a> y el <a href="/area-privada/">area privada</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Objetivos de Compra Captacion</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Crear una red profesional para captaciones inmobiliarias y demandas activas.</li><li>Proteger informacion sensible hasta que exista interes cualificado.</li><li>Ordenar la colaboracion entre agentes, agencias e inversores.</li><li>Reducir fugas de informacion y conversaciones sin seguimiento.</li><li>Dar mas valor al trabajo de captacion de propietarios vendedores.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Que lograras usando la plataforma</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Un agente puede encontrar propiedades para compradores, publicar oportunidades que no quiere trabajar solo, colaborar con otros profesionales y apoyarse en herramientas de productividad. Una agencia puede organizar mejor sus operaciones compartidas, ampliar red profesional y convertir captaciones o demandas en oportunidades mas estructuradas.</p><!-- /wp:paragraph -->
HTML,
        'recursos' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Herramientas IA para agentes inmobiliarios</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las herramientas IA para agentes inmobiliarios ayudan a ahorrar tiempo, automatizar tareas repetitivas, mejorar la captacion, preparar textos comerciales y tomar mejores decisiones. En Compra Captacion reunimos asistentes, calculadoras, plantillas y recursos practicos para que los profesionales inmobiliarios trabajen con mas productividad.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Herramientas IA para agentes inmobiliarios"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Esta seccion no debe funcionar como un simple repositorio de documentos. Recursos debe ser un centro de productividad inmobiliaria donde el agente pueda encontrar utilidades para captar mejor, responder antes, redactar fichas, cualificar compradores, calcular honorarios, preparar documentacion y usar inteligencia artificial en su trabajo diario.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Asistentes IA para inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Un asistente IA inmobiliario puede ayudar a responder preguntas frecuentes, preparar mensajes, organizar informacion de una captacion, resumir expedientes y generar borradores de comunicaciones. La clave es que el agente mantenga el control profesional mientras la IA reduce tareas repetitivas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Herramientas para captar propiedades</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los recursos deben incluir herramientas para captar propiedades, preparar propuestas de captacion, responder objeciones del propietario, crear argumentarios comerciales y convertir una oportunidad en una ficha lista para colaborar o vender dentro del marketplace.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Generadores de textos inmobiliarios</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los generadores de textos inmobiliarios permiten redactar descripciones de inmuebles, mensajes para WhatsApp, emails de seguimiento, resumenes de captacion, publicaciones comerciales y textos para presentar oportunidades a otros profesionales con rapidez y coherencia.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Calculadoras inmobiliarias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las calculadoras inmobiliarias aportan valor inmediato al trabajo del agente. Pueden ayudar a estimar honorarios, reparto de comisiones, neto vendedor, rentabilidad de alquiler, escenarios hipotecarios o importes orientativos antes de avanzar en una operacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Plantillas para agentes inmobiliarios</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Las plantillas para agentes inmobiliarios permiten trabajar con mas orden: NDA, acuerdos de colaboracion, checklist documental, ficha de captacion profesional, guias de publicacion y modelos para preparar demandas de compradores con mejor informacion.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Automatizacion y productividad inmobiliaria</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La automatizacion inmobiliaria debe ayudar al profesional a reducir tareas manuales sin perder criterio. Un buen hub de recursos puede apoyar la cualificacion de leads, la preparacion de visitas, la priorizacion de oportunidades y el seguimiento de operaciones compartidas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Recursos para colaboracion entre profesionales</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captacion trabaja con captaciones, demandas y colaboracion entre profesionales. Por eso los recursos deben conectar con el <a href="/marketplace/">marketplace</a>, la pagina para <a href="/buscar-captaciones/">buscar captaciones</a>, la opcion de <a href="/ofrecer-captacion/">ofrecer captacion</a>, el flujo de <a href="/como-funciona/">como funciona</a>, los <a href="/planes/">planes</a> y el <a href="/area-privada/">area privada</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Como usar IA en el trabajo diario del agente</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Generar una descripcion clara de una captacion inmobiliaria.</li><li>Preparar una propuesta para propietarios vendedores.</li><li>Cualificar una demanda de cliente comprador.</li><li>Crear mensajes de seguimiento para WhatsApp o email.</li><li>Calcular honorarios, repartos y escenarios de operacion.</li><li>Resumir informacion de una colaboracion antes de tomar decisiones.</li></ul><!-- /wp:list -->
HTML,
        'planes' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Planes para agentes inmobiliarios</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los planes de Compra Captacion estan pensados para agentes inmobiliarios, agencias y profesionales que quieren usar una plataforma B2B para publicar captaciones, buscar demandas, acceder a recursos y trabajar oportunidades con mas orden. Starter permite empezar gratis y Professional activa un uso mas completo de la plataforma.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Planes para agentes inmobiliarios"/></figure><!-- /wp:image -->
<!-- wp:shortcode -->[captacion_stripe_plans]<!-- /wp:shortcode -->
<!-- wp:heading {"level":3} --><h3>Starter - 0 euros</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Starter esta orientado a profesionales que quieren conocer Compra Captacion antes de contratar un acceso avanzado. Sirve para explorar la propuesta, revisar recursos, entender el marketplace inmobiliario B2B y empezar a organizar la actividad sin coste de entrada.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Que incluye el plan gratis</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Acceso inicial a la plataforma.</li><li>Exploracion del marketplace inmobiliario B2B.</li><li>Recursos gratuitos para agentes inmobiliarios.</li><li>Herramientas IA basicas para productividad.</li><li>Calculadoras y plantillas iniciales.</li><li>Perfil profesional basico.</li><li>Actualizaciones sobre nuevas funciones y oportunidades.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Plan Profesional</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El plan profesional esta pensado para agentes y agencias que quieren usar Compra Captacion como herramienta recurrente. Permite trabajar captaciones inmobiliarias, demandas activas, colaboraciones y recursos de inteligencia artificial con mayor profundidad operativa.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Publicar captaciones inmobiliarias.</li><li>Publicar demandas de clientes compradores.</li><li>Solicitar acceso protegido a oportunidades compatibles.</li><li>Usar herramientas IA avanzadas para agentes inmobiliarios.</li><li>Generar textos comerciales, mensajes y descripciones.</li><li>Acceder a calculadoras, plantillas y documentos profesionales.</li><li>Recibir alertas y seguimiento de oportunidades.</li><li>Usar el area privada completa con favoritos, tareas y trazabilidad.</li><li>Contar con soporte prioritario segun disponibilidad del servicio.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Que plan elegir segun tu perfil</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Si quieres conocer la plataforma y revisar recursos, Starter es suficiente para empezar. Si ya trabajas con captaciones, compradores, inversores o colaboraciones entre agencias, Professional es el acceso recomendado para operar con mas continuidad.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Herramientas incluidas para agentes inmobiliarios</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Los planes conectan con las secciones clave de la web: <a href="/marketplace/">marketplace</a>, <a href="/buscar-captaciones/">buscar captaciones</a>, <a href="/ofrecer-captacion/">ofrecer captacion</a>, <a href="/recursos/">recursos con inteligencia artificial</a>, <a href="/como-funciona/">como funciona</a> y <a href="/contacto/">contacto</a>.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Preguntas frecuentes sobre los planes</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Starter esta previsto como acceso de entrada a 0 euros.</li><li>Professional esta orientado al uso recurrente de captaciones, demandas y recursos.</li><li>Si necesitas acceso, soporte comercial o una configuracion para agencia, puedes contactar con el equipo.</li><li>Las funcionalidades pueden evolucionar segun el despliegue final de la plataforma.</li></ul><!-- /wp:list -->
HTML,
        'contacto' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Contacto con Compra Captacion</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>La pagina de contacto permite que agentes inmobiliarios, agencias, inversores y profesionales del sector se pongan en contacto con Compra Captacion para solicitar acceso, resolver dudas sobre planes, proponer colaboraciones o recibir soporte sobre el uso de la plataforma.</p><!-- /wp:paragraph -->
<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="/wp-content/themes/captacion-app/media/logo-compra-captacion.png" alt="Contacto Compra Captacion para profesionales inmobiliarios"/></figure><!-- /wp:image -->
<!-- wp:paragraph --><p>Si quieres valorar el encaje de la plataforma con tu forma de trabajar, puedes escribir a <strong>contacto@captacion.app</strong>. Cuanto mas claro sea el contexto, mas facil sera orientar la respuesta hacia captaciones, demandas, recursos, planes o colaboraciones profesionales.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Solicitar acceso a la plataforma</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El acceso guiado ayuda a entender como funciona Compra Captacion, como se publican captaciones inmobiliarias, como se crean demandas de compradores y como se protege el acceso a informacion sensible dentro del marketplace inmobiliario B2B.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Resolver dudas sobre planes</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Si tienes dudas sobre Starter, Professional, Premium o una posible configuracion para agencia, la pagina de contacto debe servir como canal directo para aclarar alcance, funciones disponibles y siguientes pasos.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Proponer una colaboracion profesional</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Compra Captacion puede recibir propuestas de colaboracion de agencias, redes inmobiliarias, proveedores de herramientas, profesionales especializados en captacion o equipos que quieran aportar valor al ecosistema.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Soporte para agentes y agencias</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El contacto tambien debe cubrir dudas operativas sobre acceso, publicacion de oportunidades, recursos con inteligencia artificial, area privada, solicitudes protegidas o funcionamiento general de la web.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Que informacion conviene incluir</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Nombre, agencia o perfil profesional.</li><li>Zona principal de trabajo.</li><li>Si buscas captar propiedades, encontrar producto para compradores o colaborar con otros agentes.</li><li>Volumen aproximado de captaciones o demandas.</li><li>Interes en Starter, Professional, Premium, acceso guiado o partnership.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>Canales de contacto</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El canal principal es <strong>contacto@captacion.app</strong>. Tambien puedes revisar antes <a href="/como-funciona/">como funciona</a>, comparar los <a href="/planes/">planes</a>, explorar el <a href="/marketplace/">marketplace</a>, publicar una demanda en <a href="/buscar-captaciones/">buscar captaciones</a> o preparar una oportunidad en <a href="/ofrecer-captacion/">ofrecer captacion</a>.</p><!-- /wp:paragraph -->
HTML,
        'area-privada' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Area privada orientada a operativa real</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>El area privada es donde Captacion.app deja de ser una promesa y se convierte en herramienta diaria. Aqui deben converger las captaciones aportadas, las demandas publicadas, las solicitudes recibidas, el seguimiento de operaciones y la trazabilidad de actividad.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>El area privada anticipa modulos clave como favoritos, tareas, alertas, comunicaciones internas y seguimiento de estados. El objetivo de esta pagina es explicar claramente que valor operativo obtiene el usuario una vez entra en el producto.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Que deberia resolver el panel privado</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Priorizar trabajo pendiente y oportunidades activas.</li><li>Evitar conversaciones dispersas fuera del contexto de cada expediente.</li><li>Concentrar evidencias, tareas y proxima accion de cada operacion.</li><li>Dar continuidad a la colaboracion entre profesionales sin perder control.</li></ul><!-- /wp:list -->
HTML,
        'aviso-legal' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Aviso legal</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><strong>TODO LEGAL — sustituir antes de produccion.</strong> Titular provisional de staging: EMPRESA PENDIENTE DE DEFINIR, S.L.; NIF/CIF B00000000; domicilio social pendiente de completar; privacidad@captacion.app; contacto@captacion.app. El dominio final tambien esta pendiente.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Los flujos, formularios y contenidos deben alinearse con la operativa final, las condiciones de uso y la politica de privacidad revisada por asesoria juridica.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Pendientes antes del lanzamiento</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Insertar datos mercantiles y de contacto del titular real.</li><li>Revisar condiciones de uso, propiedad intelectual y limitaciones de responsabilidad.</li><li>Alinear textos con la operativa final y con la politica de privacidad definitiva.</li></ul><!-- /wp:list -->
HTML,
        'privacidad' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Privacidad y tratamiento de datos</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><strong>TODO LEGAL — sustituir antes de produccion.</strong> Responsable provisional de staging: EMPRESA PENDIENTE DE DEFINIR, S.L.; NIF/CIF B00000000; domicilio social pendiente de completar; privacidad@captacion.app. DPO no designado salvo confirmacion.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Captacion.app trabaja con informacion comercial sensible y, potencialmente, con datos personales de profesionales, leads y contrapartes. Por eso la politica final debe definir con precision finalidades, bases juridicas, plazos de conservacion, destinatarios y derechos de los interesados.</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Se recomienda limitar la captacion de datos al minimo imprescindible y evitar recoger informacion real de terceros hasta que el flujo de cumplimiento, seguridad y encargados del tratamiento este cerrado.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>Recomendaciones para esta fase</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Usar formularios solo con datos de prueba o leads controlados.</li><li>Revisar textos de consentimiento y finalidades antes de campañas reales.</li><li>Definir politica de encargados, copias de seguridad y control de accesos.</li></ul><!-- /wp:list -->
HTML,
        'cookies' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Politica de cookies</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Complianz es la fuente principal de consentimiento, bloqueo preventivo, inventario y declaracion de cookies de Captacion.app. Las tecnologias no necesarias deben permanecer desactivadas hasta obtener consentimiento valido.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Necesarias: activas para funcionamiento, sesion y seguridad.</li><li>Preferencias: sujetas a la clasificacion final de Complianz.</li><li>Estadisticas: desactivadas hasta consentimiento.</li><li>Marketing: desactivado hasta consentimiento.</li></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>El entorno de preproduccion utiliza localStorage para sesion demo, tema y datos operativos temporales. No se utiliza como consentimiento legal. El mapa usa Leaflet y teselas de OpenStreetMap como servicio tecnico solicitado; debe figurar en el inventario y revisarse antes de produccion.</p><!-- /wp:paragraph -->
<!-- wp:shortcode -->[cmplz-document type="cookie-statement" region="eu"]<!-- /wp:shortcode -->
<!-- wp:paragraph --><p><strong>TODO LEGAL — sustituir antes de produccion.</strong> Completar titular, NIF/CIF, domicilio, emails, dominio final e inventario definitivo mediante el wizard y escaner de Complianz.</p><!-- /wp:paragraph -->
HTML,
        'normas-publicacion' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Normas de publicacion para profesionales</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Captacion.app debe mantener un estandar alto de calidad en todo lo que entra a la plataforma. Estas normas ayudan a proteger a quien publica, a quien compra informacion y a la reputacion del ecosistema.</p><!-- /wp:paragraph -->
<!-- wp:list --><ul><li>Publica solo informacion veraz, actualizada y comercialmente util.</li><li>No incluyas datos personales o de contacto del propietario en la ficha publica.</li><li>Indica con honestidad el estado documental, la urgencia y las condiciones de colaboracion.</li><li>No utilices la plataforma para captar datos de otros profesionales sin intencion real de operar.</li><li>Respeta la confidencialidad de todo expediente desbloqueado.</li></ul><!-- /wp:list -->
<!-- wp:paragraph --><p>La calidad de las publicaciones es parte del producto. Cuanto mejor se define una captacion o una demanda, mejor funciona el matching y mas valor aporta la plataforma a todos los actores.</p><!-- /wp:paragraph -->
HTML,
        'condiciones-de-contratacion' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Condiciones de contratación de Captacion.app</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Estas condiciones regulan la futura contratación de planes, servicios de acceso y funcionalidades de Captacion.app a través de su sitio web y de sus entornos autorizados. El texto definitivo deberá revisarse y aprobarse antes del lanzamiento comercial.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Objeto</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Captacion.app ofrecerá acceso a funcionalidades de colaboración inmobiliaria B2B, publicación de captaciones y demandas, herramientas operativas, alertas, recursos y otros servicios vinculados a la plataforma según el plan contratado en cada momento.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Partes intervinientes</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La contratación se realizará entre la sociedad titular de Captacion.app, cuyos datos completos se incorporarán en la versión final, y la persona física o jurídica que complete correctamente el proceso de alta o contratación y acepte expresamente estas condiciones.</p><!-- /wp:paragraph -->
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
<!-- wp:paragraph --><p>Captacion.app no garantiza el cierre de operaciones, la veracidad material de cada publicación ni el comportamiento de terceros. La plataforma actúa como entorno de colaboración y gestión, sin asumir la posición de propietario, comprador, vendedor ni mediador universal en cada expediente.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>8. Resolución de conflictos y ley aplicable</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>La versión definitiva deberá indicar la legislación aplicable, el fuero competente y, en su caso, los mecanismos de resolución extrajudicial que resulten procedentes según la normativa española y europea aplicable.</p><!-- /wp:paragraph -->
HTML,
        'canal-de-denuncias' => <<<'HTML'
<!-- wp:heading {"level":2} --><h2>Canal de denuncias y sistema interno de información</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p>Captacion.app prevé disponer de un canal interno de información para que empleados, colaboradores, proveedores, usuarios profesionales y terceros puedan comunicar de buena fe posibles incumplimientos normativos, irregularidades o conductas contrarias a las políticas internas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>1. Finalidad del canal</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El canal servirá para recibir comunicaciones sobre hechos que puedan suponer infracciones legales, incumplimientos éticos, vulneraciones de confidencialidad, uso indebido de datos, conflictos de interés, fraudes o conductas contrarias a las normas internas de la plataforma.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>2. Principios de funcionamiento</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Confidencialidad de la identidad de la persona informante y de las personas afectadas.</li><li>Prohibición de represalias frente a quien comunique de buena fe.</li><li>Recepción, análisis y tratamiento con medidas proporcionadas y trazables.</li><li>Respeto a los derechos de defensa, información y presunción de inocencia de las personas implicadas.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>3. Canales previstos</h3><!-- /wp:heading -->
<!-- wp:list --><ul><li>Canal escrito habilitado por medios telemáticos.</li><li>Posibilidad de solicitar comunicación presencial o por videollamada cuando proceda.</li><li>Canales externos previstos por la normativa aplicable, cuando la persona informante lo considere oportuno.</li></ul><!-- /wp:list -->
<!-- wp:heading {"level":3} --><h3>4. Ámbito subjetivo</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>Podrán utilizarlo, en la medida en que resulte aplicable, personas trabajadoras, profesionales externos, proveedores, socios comerciales, usuarios profesionales de la plataforma y terceros con relación funcional con Captacion.app.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>5. Protección de datos</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>El tratamiento de los datos personales vinculados al sistema interno de información deberá ajustarse a la normativa vigente y a una política específica de privacidad del canal, con limitación de acceso, conservación restringida y medidas de seguridad reforzadas.</p><!-- /wp:paragraph -->
<!-- wp:heading {"level":3} --><h3>6. Estado actual</h3><!-- /wp:heading -->
<!-- wp:paragraph --><p>En esta URL provisional el canal se mantiene como referencia estructural. Antes del lanzamiento deberá definirse la persona responsable del sistema, la herramienta utilizada, el procedimiento de gestión y la política específica asociada.</p><!-- /wp:paragraph -->
HTML,
    );
}

function captacion_app_seed_pages() {
    $content_map = captacion_app_seed_content_map();

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
}

function captacion_app_rank_math_seo_map() {
    return array(
        'inicio' => array(
            'focus_keyword' => 'captaciones inmobiliarias',
            'title' => 'Captaciones inmobiliarias | Compra, vende y colabora entre profesionales',
            'description' => 'Compra Captacion es un marketplace B2B de captaciones inmobiliarias para profesionales. Publica oportunidades, busca demandas activas y colabora con acceso protegido.',
        ),
        'marketplace' => array(
            'focus_keyword' => 'marketplace inmobiliario B2B',
            'title' => 'Marketplace inmobiliario B2B | Captaciones y demandas para profesionales',
            'description' => 'Compra Captacion es un marketplace inmobiliario B2B para agentes y agencias. Revisa captaciones, publica demandas y colabora con acceso protegido.',
        ),
        'buscar-captaciones' => array(
            'focus_keyword' => 'buscar captaciones inmobiliarias',
            'title' => 'Buscar captaciones inmobiliarias | Encuentra propiedades para compradores',
            'description' => 'Publica demandas inmobiliarias y encuentra captaciones compatibles para clientes compradores. Compra Captacion conecta agentes con oportunidades protegidas.',
        ),
        'ofrecer-captacion' => array(
            'focus_keyword' => 'vender captaciones inmobiliarias',
            'title' => 'Vender captaciones inmobiliarias | Publica oportunidades para profesionales',
            'description' => 'Publica y vende captaciones inmobiliarias con acceso protegido. Compra Captacion ayuda a agentes y agencias a compartir oportunidades y colaborar.',
        ),
        'como-funciona' => array(
            'focus_keyword' => 'como funciona una plataforma inmobiliaria B2B',
            'title' => 'Como funciona una plataforma inmobiliaria B2B | Compra Captacion',
            'description' => 'Descubre como funciona Compra Captacion: publica captaciones, crea demandas, cruza oportunidades y colabora con acceso protegido y trazabilidad.',
        ),
        'recursos' => array(
            'focus_keyword' => 'herramientas IA para agentes inmobiliarios',
            'title' => 'Herramientas IA para agentes inmobiliarios | Recursos y asistentes',
            'description' => 'Encuentra herramientas IA para agentes inmobiliarios: asistentes, calculadoras, plantillas, generadores de textos y recursos para mejorar productividad.',
        ),
        'planes' => array(
            'focus_keyword' => 'planes para agentes inmobiliarios',
            'title' => 'Planes para agentes inmobiliarios | Gratis y Profesional',
            'description' => 'Elige tu plan en Compra Captacion: acceso inicial gratis, herramientas para agentes inmobiliarios y plan profesional para publicar, buscar y colaborar.',
        ),
        'contacto' => array(
            'focus_keyword' => 'contacto Compra Captacion',
            'title' => 'Contacto Compra Captacion | Acceso, planes y colaboraciones',
            'description' => 'Contacta con Compra Captacion para solicitar acceso, resolver dudas sobre planes o valorar colaboraciones profesionales en captaciones inmobiliarias.',
        ),
    );
}

function captacion_app_apply_rank_math_meta($post_id, $slug) {
    $seo_map = captacion_app_rank_math_seo_map();
    if (!isset($seo_map[$slug])) {
        return;
    }

    $seo = $seo_map[$slug];
    update_post_meta($post_id, 'rank_math_focus_keyword', $seo['focus_keyword']);
    update_post_meta($post_id, 'rank_math_title', $seo['title']);
    update_post_meta($post_id, 'rank_math_description', $seo['description']);
    update_post_meta($post_id, 'rank_math_pillar_content', 'off');
}

function captacion_app_create_editable_pages() {
    if (!current_user_can('manage_options') || !check_admin_referer('captacion_app_create_pages')) {
        wp_die('No autorizado');
    }

    $created = 0;
    $updated = 0;

    foreach (captacion_app_seed_pages() as $page) {
        $existing = get_page_by_path($page['slug'], OBJECT, 'page');
        $data = array(
            'post_title' => $page['title'],
            'post_name' => $page['slug'],
            'post_content' => $page['content'],
            'post_status' => 'publish',
            'post_type' => 'page',
        );

        if ($existing) {
            $data['ID'] = $existing->ID;
            $post_id = wp_update_post($data);
            $updated++;
        } else {
            $post_id = wp_insert_post($data);
            $created++;
        }

        if (!is_wp_error($post_id) && $post_id) {
            captacion_app_apply_rank_math_meta((int) $post_id, $page['slug']);
        }
    }

    wp_safe_redirect(add_query_arg(array(
        'page' => 'captacion-app-settings',
        'captacion_pages_created' => $created,
        'captacion_pages_updated' => $updated,
    ), admin_url('admin.php')));
    exit;
}
add_action('admin_post_captacion_app_create_pages', 'captacion_app_create_editable_pages');

function captacion_app_is_configured_stripe_link($url) {
    return is_string($url)
        && preg_match('#^https://(buy|checkout)\.stripe\.com/#', $url)
        && strpos($url, 'REEMPLAZA_') === false;
}

function captacion_app_stripe_link_for_plan($plan) {
    $settings = captacion_app_settings();
    $map = array(
        'initial' => 'stripe_membership_initial_link',
        'professional' => 'stripe_membership_professional_link',
        'agency' => 'stripe_membership_agency_link',
    );
    $key = $map[$plan] ?? '';
    return $key ? ($settings[$key] ?? '') : '';
}

function captacion_app_stripe_plan_button($plan, $label) {
    $url = captacion_app_stripe_link_for_plan($plan);
    if (captacion_app_is_configured_stripe_link($url)) {
        return '<a class="captacion-stripe-button" href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a>';
    }

    $contact_email = captacion_app_setting('contact_email');
    $subjects = array(
        'initial' => 'Quiero activar Starter de Captacion.app',
        'professional' => 'Quiero informacion del plan Professional de Captacion.app',
        'agency' => 'Quiero informacion del plan Premium de Captacion.app',
    );
    $fallback_label = array(
        'initial' => 'Solicitar acceso',
        'professional' => 'Solicitar acceso comercial',
        'agency' => 'Hablar con ventas',
    );

    if ($contact_email) {
        $mailto = 'mailto:' . rawurlencode($contact_email) . '?subject=' . rawurlencode($subjects[$plan] ?? 'Consulta sobre Captacion.app');
        return '<a class="captacion-stripe-button" href="' . esc_url($mailto) . '">' . esc_html($fallback_label[$plan] ?? 'Solicitar informacion') . '</a>';
    }

    return '<a class="captacion-stripe-button" href="' . esc_url(home_url('/contacto/')) . '">' . esc_html($fallback_label[$plan] ?? 'Solicitar informacion') . '</a>';
}

function captacion_app_stripe_plans_shortcode() {
    ob_start();
    ?>
    <div class="captacion-plans-grid">
        <section class="captacion-plan-card">
            <span class="captacion-plan-kicker">Starter</span>
            <h3>Starter</h3>
            <p class="captacion-plan-price">0 &euro; <small>/ mes</small></p>
            <p>Acceso gratuito, buscador, publicaciones, visualización de oportunidades y dashboard básico.</p>
            <?php echo captacion_app_stripe_plan_button('initial', 'Comenzar gratis'); ?>
        </section>
        <section class="captacion-plan-card captacion-plan-card-featured">
            <span class="captacion-plan-kicker">Profesional</span>
            <h3>Professional</h3>
            <p class="captacion-plan-price">29 &euro; <small>/ mes</small></p>
            <p>30 accesos mensuales, dashboard profesional, alertas y packs de 15 accesos por 5 EUR.</p>
            <?php echo captacion_app_stripe_plan_button('professional', 'Activar Professional'); ?>
        </section>
        <section class="captacion-plan-card">
            <span class="captacion-plan-kicker">Premium</span>
            <h3>Premium</h3>
            <p class="captacion-plan-price">49 &euro; <small>/ mes</small></p>
            <p>Incluye 60 accesos a oportunidades del marketplace al mes. Packs adicionales: 5 EUR por 30 accesos extra.</p>
            <?php echo captacion_app_stripe_plan_button('agency', 'Activar Premium'); ?>
        </section>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('captacion_stripe_plans', 'captacion_app_stripe_plans_shortcode');

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
}

function captacion_app_ai_user_meta_key() {
    return 'captacion_app_ai_connection_v1';
}

function captacion_app_ai_encrypt_secret($secret) {
    $secret = (string) $secret;
    if ($secret === '') {
        return '';
    }

    $key = hash('sha256', wp_salt('secure_auth'), true);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    if (!$iv_length) {
        return '';
    }

    $iv = random_bytes($iv_length);
    $ciphertext = openssl_encrypt($secret, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
    if ($ciphertext === false) {
        return '';
    }

    return base64_encode($iv . $ciphertext);
}

function captacion_app_ai_decrypt_secret($payload) {
    $payload = (string) $payload;
    if ($payload === '') {
        return '';
    }

    $raw = base64_decode($payload, true);
    if ($raw === false) {
        return '';
    }

    $key = hash('sha256', wp_salt('secure_auth'), true);
    $iv_length = openssl_cipher_iv_length('aes-256-cbc');
    if (!$iv_length || strlen($raw) <= $iv_length) {
        return '';
    }

    $iv = substr($raw, 0, $iv_length);
    $ciphertext = substr($raw, $iv_length);
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    return $plaintext === false ? '' : $plaintext;
}

function captacion_app_ai_secret_fingerprint($secret) {
    $secret = trim((string) $secret);
    if ($secret === '') {
        return '';
    }

    $suffix = substr($secret, -4);
    return '•••• ' . $suffix;
}

function captacion_app_ai_sanitize_connection_payload($input, $existing = array()) {
    $providers = captacion_app_ai_providers();
    $provider = sanitize_key($input['provider'] ?? ($existing['provider'] ?? 'openai'));
    if (!isset($providers[$provider])) {
        return new WP_Error('captacion_ai_invalid_provider', 'Proveedor de IA no válido.', array('status' => 400));
    }

    $alias = sanitize_text_field(wp_unslash($input['alias'] ?? ($existing['alias'] ?? '')));
    if ($alias === '') {
        $alias = $providers[$provider]['label'];
    }

    $profile = sanitize_key($input['profile'] ?? ($existing['profile'] ?? 'general'));
    $allowed_profiles = array('general', 'copywriting', 'matching', 'documentos', 'automatizacion');
    if (!in_array($profile, $allowed_profiles, true)) {
        $profile = 'general';
    }

    $model = sanitize_text_field(wp_unslash($input['model'] ?? ($existing['model'] ?? $providers[$provider]['default_model'])));
    if ($model === '') {
        $model = $providers[$provider]['default_model'];
    }

    $endpoint = esc_url_raw(wp_unslash($input['endpoint'] ?? ($existing['endpoint'] ?? '')));
    if ($provider !== 'compatible') {
        $endpoint = $providers[$provider]['endpoint'];
    } elseif ($endpoint === '') {
        return new WP_Error('captacion_ai_missing_endpoint', 'Debes indicar un endpoint compatible con OpenAI.', array('status' => 400));
    }

    $secret = isset($input['api_key']) ? trim((string) wp_unslash($input['api_key'])) : '';
    $encrypted_secret = $existing['encrypted_secret'] ?? '';
    $fingerprint = $existing['fingerprint'] ?? '';

    if ($secret !== '') {
        $encrypted_secret = captacion_app_ai_encrypt_secret($secret);
        if ($encrypted_secret === '') {
            return new WP_Error('captacion_ai_secret_error', 'No se pudo proteger la credencial de IA.', array('status' => 500));
        }
        $fingerprint = captacion_app_ai_secret_fingerprint($secret);
    } elseif (empty($encrypted_secret)) {
        return new WP_Error('captacion_ai_missing_secret', 'Debes indicar una API key o credencial válida.', array('status' => 400));
    }

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
}

function captacion_app_ai_get_user_connection($user_id, $with_secret = false) {
    $stored = get_user_meta($user_id, captacion_app_ai_user_meta_key(), true);
    if (!is_array($stored) || empty($stored['provider'])) {
        return null;
    }

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
    );

    if ($with_secret) {
        $response['encrypted_secret'] = (string) ($stored['encrypted_secret'] ?? '');
        $response['api_key'] = captacion_app_ai_decrypt_secret($response['encrypted_secret']);
    }

    return $response;
}

function captacion_app_ai_save_user_connection($user_id, $connection) {
    update_user_meta($user_id, captacion_app_ai_user_meta_key(), $connection);
}

function captacion_app_ai_delete_user_connection($user_id) {
    delete_user_meta($user_id, captacion_app_ai_user_meta_key());
}

function captacion_app_ai_log($message, $context = array()) {
    $payload = array();
    foreach ((array) $context as $key => $value) {
        if (stripos((string) $key, 'secret') !== false || stripos((string) $key, 'key') !== false) {
            continue;
        }
        $payload[$key] = is_scalar($value) ? $value : wp_json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    error_log('[Captacion.app AI] ' . $message . ' ' . wp_json_encode($payload, JSON_UNESCAPED_UNICODE));
}

function captacion_app_ai_normalize_request_context($context) {
    if (is_string($context)) {
        return trim($context);
    }

    if (empty($context)) {
        return '';
    }

    return wp_json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

function captacion_app_ai_build_user_prompt($prompt, $context = array()) {
    $prompt = trim((string) $prompt);
    $context_string = captacion_app_ai_normalize_request_context($context);

    if ($context_string === '') {
        return $prompt;
    }

    return $prompt . "\n\nContexto adicional:\n" . $context_string;
}

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
    }

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
    }

    if (!empty($body['choices'][0]['message']['content'])) {
        return trim((string) $body['choices'][0]['message']['content']);
    }

    if (!empty($body['choices'][0]['text'])) {
        return trim((string) $body['choices'][0]['text']);
    }

    return '';
}

function captacion_app_ai_provider_request($connection, $payload) {
    $provider = $connection['provider'];
    $api_key = $connection['api_key'] ?? '';
    $system_instruction = trim((string) ($payload['system_instruction'] ?? ''));
    $prompt = trim((string) ($payload['prompt'] ?? ''));
    $context = $payload['context'] ?? array();
    $temperature = isset($payload['temperature']) ? (float) $payload['temperature'] : 0.3;
    $max_tokens = isset($payload['max_tokens']) ? absint($payload['max_tokens']) : 600;

    if ($api_key === '') {
        return new WP_Error('captacion_ai_missing_runtime_secret', 'No hay una credencial válida almacenada para este usuario.', array('status' => 400));
    }

    $user_prompt = captacion_app_ai_build_user_prompt($prompt, $context);
    $timeout = 35;
    $headers = array();
    $request_body = array();
    $endpoint = $connection['endpoint'];

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
            $headers['X-Title'] = 'Captacion.app';
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
    }

    $response = wp_remote_post($endpoint, array(
        'timeout' => $timeout,
        'headers' => $headers,
        'body' => wp_json_encode($request_body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
    ));

    if (is_wp_error($response)) {
        return new WP_Error('captacion_ai_transport_error', 'No se pudo conectar con el proveedor de IA.', array(
            'status' => 502,
            'provider_message' => $response->get_error_message(),
        ));
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $raw_body = wp_remote_retrieve_body($response);
    $decoded = json_decode($raw_body, true);

    if ($status_code < 200 || $status_code >= 300) {
        $provider_message = '';
        if (!empty($decoded['error']['message'])) {
            $provider_message = (string) $decoded['error']['message'];
        } elseif (!empty($decoded['message'])) {
            $provider_message = (string) $decoded['message'];
        } elseif (is_string($raw_body) && $raw_body !== '') {
            $provider_message = wp_strip_all_tags($raw_body);
        }

        return new WP_Error('captacion_ai_provider_error', 'El proveedor de IA devolvió un error.', array(
            'status' => $status_code ?: 502,
            'provider_message' => $provider_message,
        ));
    }

    $text = captacion_app_ai_extract_text_from_response($provider, is_array($decoded) ? $decoded : array());
    if ($text === '') {
        return new WP_Error('captacion_ai_empty_response', 'La respuesta del proveedor llegó vacía o no se pudo interpretar.', array('status' => 502));
    }

    return array(
        'provider' => $provider,
        'model' => $connection['model'],
        'text' => $text,
        'raw' => $decoded,
    );
}

function captacion_app_ai_test_connection($connection) {
    return captacion_app_ai_provider_request($connection, array(
        'prompt' => 'Responde solo con la palabra OK.',
        'system_instruction' => 'Responde de forma mínima.',
        'context' => array('purpose' => 'connection_test'),
        'temperature' => 0,
        'max_tokens' => 20,
    ));
}

function captacion_app_ai_rest_permission() {
    return is_user_logged_in();
}

function captacion_app_ai_rest_get_config(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $connection = captacion_app_ai_get_user_connection($user_id, false);
    return rest_ensure_response(array(
        'connected' => !empty($connection),
        'connection' => $connection,
        'providers' => captacion_app_ai_providers(),
    ));
}

function captacion_app_ai_rest_save_config(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $existing = captacion_app_ai_get_user_connection($user_id, true);
    $sanitized = captacion_app_ai_sanitize_connection_payload($request->get_json_params(), is_array($existing) ? $existing : array());
    if (is_wp_error($sanitized)) {
        return $sanitized;
    }

    captacion_app_ai_save_user_connection($user_id, $sanitized);
    captacion_app_ai_log('Configuración IA actualizada.', array('user_id' => $user_id, 'provider' => $sanitized['provider']));

    return rest_ensure_response(array(
        'saved' => true,
        'connection' => captacion_app_ai_get_user_connection($user_id, false),
    ));
}

function captacion_app_ai_rest_delete_config(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    captacion_app_ai_delete_user_connection($user_id);
    captacion_app_ai_log('Configuración IA eliminada.', array('user_id' => $user_id));
    return rest_ensure_response(array('deleted' => true));
}

function captacion_app_ai_rest_set_status(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $existing = captacion_app_ai_get_user_connection($user_id, true);
    if (!$existing) {
        return new WP_Error('captacion_ai_not_configured', 'No hay una conexión IA configurada para este usuario.', array('status' => 404));
    }

    $existing['active'] = (bool) $request->get_param('active');
    $existing['updated_at'] = time();
    captacion_app_ai_save_user_connection($user_id, $existing);

    return rest_ensure_response(array(
        'updated' => true,
        'connection' => captacion_app_ai_get_user_connection($user_id, false),
    ));
}

function captacion_app_ai_rest_test(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $existing = captacion_app_ai_get_user_connection($user_id, true);
    if (!$existing) {
        return new WP_Error('captacion_ai_not_configured', 'Primero debes guardar una conexión IA.', array('status' => 404));
    }

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
    }

    $existing['status'] = 'connected';
    $existing['last_error'] = '';
    $existing['last_validated_at'] = time();
    $existing['updated_at'] = time();
    captacion_app_ai_save_user_connection($user_id, $existing);

    return rest_ensure_response(array(
        'success' => true,
        'message' => 'Conexión validada correctamente.',
        'connection' => captacion_app_ai_get_user_connection($user_id, false),
    ));
}

function captacion_app_ai_rest_request(WP_REST_Request $request) {
    $user_id = get_current_user_id();
    $connection = captacion_app_ai_get_user_connection($user_id, true);
    if (!$connection || empty($connection['active'])) {
        return new WP_Error('captacion_ai_not_connected', 'No tienes una conexión IA activa. Configúrala en el área privada.', array('status' => 409));
    }

    $params = $request->get_json_params();
    $prompt = trim((string) ($params['prompt'] ?? ''));
    if ($prompt === '') {
        return new WP_Error('captacion_ai_missing_prompt', 'La solicitud no incluye un prompt válido.', array('status' => 400));
    }

    $payload = array(
        'prompt' => $prompt,
        'system_instruction' => (string) ($params['system_instruction'] ?? ''),
        'context' => $params['context'] ?? array(),
        'temperature' => $params['temperature'] ?? 0.3,
        'max_tokens' => $params['max_tokens'] ?? 700,
    );

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
    }

    $connection['status'] = 'connected';
    $connection['last_error'] = '';
    $connection['last_validated_at'] = time();
    $connection['updated_at'] = time();
    captacion_app_ai_save_user_connection($user_id, $connection);

    return rest_ensure_response(array(
        'success' => true,
        'provider' => $result['provider'],
        'model' => $result['model'],
        'text' => $result['text'],
    ));
}

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
    ));

    register_rest_route('captacion-app/v1', '/ai/config/status', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_set_status',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    ));

    register_rest_route('captacion-app/v1', '/ai/test', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_test',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    ));

    register_rest_route('captacion-app/v1', '/ai/request', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'captacion_app_ai_rest_request',
        'permission_callback' => 'captacion_app_ai_rest_permission',
    ));
}
add_action('rest_api_init', 'captacion_app_register_ai_rest_routes');


/* Official territorial synchronization: INE + CartoCiudad/CNIG. */
function captacion_app_territory_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_territories';
}

function captacion_app_territory_postal_table_name() {
    global $wpdb;
    return $wpdb->prefix . 'captacion_territory_postal_codes';
}

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
    ) {$charset_collate};");

    $legacy_indexes = $wpdb->get_col("SHOW INDEX FROM {$table}", 2);
    foreach (array('level_code','parent_level','name') as $legacy_index) {
        if (in_array($legacy_index, $legacy_indexes, true)) $wpdb->query("ALTER TABLE {$table} DROP INDEX {$legacy_index}");
    }
    $legacy_columns = $wpdb->get_col("SHOW COLUMNS FROM {$table}", 0);
    foreach (array('level','code','parent_code','name','ine_code','extra') as $legacy_column) {
        if (in_array($legacy_column, $legacy_columns, true)) $wpdb->query("ALTER TABLE {$table} DROP COLUMN {$legacy_column}");
    }

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
    ) {$charset_collate};");

    update_option('captacion_territory_table_version', '20260620');
}
add_action('after_switch_theme', 'captacion_app_install_territory_table');
function captacion_app_maybe_install_territory_table() {
    if (get_option('captacion_territory_table_version') !== '20260620') {
        captacion_app_install_territory_table();
    }
    captacion_app_maybe_seed_territories();
}
add_action('admin_init', 'captacion_app_maybe_install_territory_table');
add_action('init', 'captacion_app_maybe_install_territory_table');

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
}

function captacion_app_territory_normalize_header($value) {
    $value = remove_accents(strtolower(trim((string) $value)));
    return preg_replace('/[^a-z0-9]+/', '_', $value);
}

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
}

function captacion_app_territory_xlsx_column_index($ref) {
    preg_match('/^[A-Z]+/i', (string) $ref, $match);
    $letters = strtoupper($match[0] ?? 'A'); $index = 0;
    for ($i = 0; $i < strlen($letters); $i++) $index = $index * 26 + (ord($letters[$i]) - 64);
    return max(0, $index - 1);
}

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
}

function captacion_app_territory_parse_file($path) {
    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if ($extension === 'xlsx') return captacion_app_territory_read_xlsx($path);
    if (in_array($extension, array('csv', 'txt'), true)) return captacion_app_territory_read_csv($path);
    return new WP_Error('territory_file_type', 'Formato no compatible. Usa CSV o XLSX.');
}

function captacion_app_territory_row_value($row, $headers, $aliases) {
    foreach ($aliases as $alias) {
        $key = captacion_app_territory_normalize_header($alias);
        if (isset($headers[$key]) && isset($row[$headers[$key]])) return trim((string) $row[$headers[$key]]);
    }
    return '';
}

function captacion_app_insert_territory_row($row) {
    global $wpdb;
    $table = captacion_app_territory_table_name();
    $now = current_time('mysql');
    $municipality_code = preg_replace('/\D/', '', (string) ($row['municipio_codigo_ine'] ?? ''));
    if (strlen($municipality_code) !== 5) {
        return false;
    }

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
}

function captacion_app_maybe_seed_territories() {
    global $wpdb;
    $table = captacion_app_territory_table_name();
    if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table)) !== $table) {
        return;
    }

    $count = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE municipio_codigo_ine <> ''");
    if ($count >= 8000 || get_option('captacion_territory_seed_version') === 'INE-2026-8132') {
        return;
    }

    $path = get_template_directory() . '/src/data/territorios-espana.json';
    $catalog = file_exists($path) ? json_decode(file_get_contents($path), true) : array();
    if (!is_array($catalog) || !$catalog) {
        return;
    }

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
    }

    if ($inserted >= 8000) {
        update_option('captacion_territory_seed_version', 'INE-2026-8132');
        update_option('captacion_territory_last_sync', current_time('mysql'));
        update_option('captacion_territory_source', 'INE 2026 - 26codmun.xlsx');
        delete_transient('captacion_territory_catalog');
    }
}

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
}

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
}

function captacion_app_get_territory_catalog_json() { return wp_json_encode(captacion_app_get_territory_catalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); }

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
}

function captacion_app_export_territory_catalog($destination = '') {
    $destination = $destination ?: get_template_directory() . '/src/data/territorios-espana.json';
    $directory = dirname($destination);
    if (!is_dir($directory) && !wp_mkdir_p($directory)) return new WP_Error('territory_export_directory', 'No se pudo crear el directorio de exportación.');
    $json = wp_json_encode(captacion_app_get_territory_catalog(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    if (file_put_contents($destination, $json . PHP_EOL, LOCK_EX) === false) return new WP_Error('territory_export_write', 'No se pudo escribir el JSON territorial.');
    return wp_normalize_path($destination);
}

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
}

function captacion_app_rest_territory_provinces(WP_REST_Request $request) {
    global $wpdb;
    $community = str_pad((string) absint($request->get_param('community')), 2, '0', STR_PAD_LEFT);
    $table = captacion_app_territory_table_name();
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT provincia_codigo AS id, provincia_nombre AS name FROM {$table} WHERE comunidad_codigo = %s ORDER BY provincia_nombre",
        $community
    ), ARRAY_A);
    return rest_ensure_response(array('ok'=>true,'community'=>$community,'provinces'=>$rows ?: array()));
}

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
}

function captacion_app_rest_admin_permission(WP_REST_Request $request) {
    if (!current_user_can('manage_options')) {
        return new WP_Error('captacion_rest_forbidden', 'Solo administradores pueden ejecutar esta accion.', array('status'=>403));
    }
    $nonce = $request->get_header('X-WP-Nonce');
    if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
        return new WP_Error('captacion_rest_invalid_nonce', 'Nonce no valido.', array('status'=>403));
    }
    return true;
}

function captacion_app_rest_import_territories(WP_REST_Request $request) {
    $source = sanitize_text_field((string) $request->get_param('source'));
    if (!$source) return new WP_Error('territory_source_required', 'Indica una fuente INE CSV o XLSX.', array('status'=>400));
    $result = captacion_app_import_ine_territories($source, true);
    return is_wp_error($result) ? $result : rest_ensure_response(array('ok'=>true,'counts'=>$result));
}

function captacion_app_parse_jsonp($body) {
    $body = trim((string) $body);
    $start_array = strpos($body, '['); $start_object = strpos($body, '{');
    if ($start_array === false || ($start_object !== false && $start_object < $start_array)) { $start = $start_object; $end = strrpos($body, '}'); }
    else { $start = $start_array; $end = strrpos($body, ']'); }
    if ($start === false || $end === false) return null;
    return json_decode(substr($body, $start, $end - $start + 1), true);
}

function captacion_app_rest_validate_cartociudad(WP_REST_Request $request) {
    $params = $request->get_json_params(); $params = is_array($params) ? $params : array();
    $postal_code = preg_replace('/\D/', '', (string) ($params['postalCode'] ?? ''));
    if ($postal_code !== '' && !preg_match('/^[0-9]{5}$/', $postal_code)) {
        return new WP_Error('territory_invalid_postal_code', 'El codigo postal debe contener exactamente 5 digitos.', array('status'=>400));
    }

    $remote_address = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $rate_key = 'captacion_address_rate_' . substr(hash('sha256', $remote_address), 0, 24);
    $rate = (int) get_transient($rate_key);
    if ($rate >= 12) {
        return new WP_Error('territory_address_rate_limit', 'Demasiadas validaciones. Espera unos minutos.', array('status'=>429));
    }
    set_transient($rate_key, $rate + 1, 10 * MINUTE_IN_SECONDS);

    $query = sanitize_text_field(implode(' ', array_filter(array($params['address'] ?? '', $params['postalCode'] ?? '', $params['municipality'] ?? '', $params['province'] ?? ''))));
    if (strlen($query) < 3) return new WP_Error('territory_address_query', 'Indica dirección, código postal o municipio.', array('status'=>400));
    $url = add_query_arg(array('q'=>$query, 'limit'=>5), 'https://www.cartociudad.es/geocoder/api/geocoder/findJsonp');
    $response = wp_remote_get($url, array('timeout'=>15, 'user-agent'=>'Captacion.app WordPress territorial validator'));
    if (is_wp_error($response)) return $response;
    $data = captacion_app_parse_jsonp(wp_remote_retrieve_body($response));
    if (!is_array($data)) return new WP_Error('territory_cartociudad_parse', 'CartoCiudad no devolvió una respuesta válida.', array('status'=>502));
    return rest_ensure_response(array('ok'=>true,'provider'=>'CartoCiudad/CNIG','query'=>$query,'results'=>array_slice(array_values($data),0,5)));
}

function captacion_app_validate_two_digit_code($value) {
    return (bool) preg_match('/^[0-9]{1,2}$/', (string) $value);
}

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
add_action('rest_api_init', 'captacion_app_register_territory_routes');

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
}
