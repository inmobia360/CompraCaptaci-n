<?php
/*
Template Name: Captacion.app interactiva
*/
if (!defined('ABSPATH')) { exit; }
$captacion_theme_uri = get_template_directory_uri();
$captacion_brand_name = captacion_app_setting('brand_name');
$captacion_contact_email = captacion_app_setting('contact_email');
$captacion_stripe_link = captacion_app_setting('stripe_payment_link');
$captacion_membership_links = array(
  'initial' => captacion_app_setting('stripe_membership_initial_link'),
  'professional' => captacion_app_setting('stripe_membership_professional_link'),
  'premium' => captacion_app_setting('stripe_membership_agency_link'),
);
$captacion_resource_cards = array();
foreach (captacion_app_resource_catalog() as $resource) {
  $resource_id = $resource['resource_id'];
  $captacion_resource_cards[] = array(
    'resource_id' => $resource_id,
    'title' => $resource['title'],
    'description' => $resource['description'],
    'plan_access' => $resource['plan_access'],
    'has_static_pdf' => !empty($resource['static_pdf_url']) || !empty($resource['static_pdf_attachment_id']),
    'pdf_url' => esc_url_raw($resource['static_pdf_url']),
    'create_url' => home_url('/recursos/crear-pdf/?resource=' . rawurlencode($resource_id)),
  );
}
$captacion_user = wp_get_current_user();
$captacion_user_display_name = '';
if (is_user_logged_in()) {
  $display_name = trim((string) $captacion_user->display_name);
  $full_name = trim((string) $captacion_user->first_name . ' ' . (string) $captacion_user->last_name);
  $login_name = trim((string) $captacion_user->user_login);
  $email = trim((string) $captacion_user->user_email);
  $captacion_user_display_name = ($display_name && strcasecmp($display_name, $email) !== 0)
    ? $display_name
    : ($full_name ?: ($login_name ?: $email));
}
$captacion_mailchimp_config = array(
  'endpoint' => esc_url_raw(rest_url('captacion/v1/mailchimp/subscribe')),
  'notificationsEndpoint' => esc_url_raw(rest_url('captacion/v1/notifications/send')),
  'recordsEndpoint' => esc_url_raw(rest_url('captacion/v1/records')),
  'registerEndpoint' => esc_url_raw(rest_url('captacion/v1/register')),
  'loginEndpoint' => esc_url_raw(rest_url('captacion/v1/login')),
  'resendVerificationEndpoint' => esc_url_raw(rest_url('captacion/v1/verification/resend')),
  'logoutEndpoint' => esc_url_raw(rest_url('captacion/v1/logout')),
  'accessStatusEndpoint' => esc_url_raw(rest_url('captacion/v1/marketplace-access/status')),
  'accessConsumeEndpoint' => esc_url_raw(rest_url('captacion/v1/marketplace-access/consume')),
  'accessPurchaseEndpoint' => esc_url_raw(rest_url('captacion/v1/marketplace-access/purchase-intent')),
  'tasksEndpoint' => esc_url_raw(rest_url('captacion/v1/tasks')),
  'contactEndpoint' => esc_url_raw(rest_url('captacion/v1/contact')),
  'reportEndpoint' => esc_url_raw(rest_url('captacion/v1/reports')),
  'lostPasswordUrl' => esc_url_raw(wp_lostpassword_url(home_url('/'))),
  'territoriesEndpoint' => esc_url_raw(rest_url('captacion/v1/territories')),
  'territoryValidationEndpoint' => esc_url_raw(rest_url('captacion/v1/address/validate')),
  'loggedIn' => is_user_logged_in(),
  'emailVerified' => is_user_logged_in() ? captacion_app_is_email_verified(get_current_user_id()) : false,
  'commercialConsent' => is_user_logged_in() ? get_user_meta(get_current_user_id(), 'captacion_commercial_consent', true) === '1' : false,
  'currentUser' => is_user_logged_in() ? array(
    'name' => $captacion_user_display_name,
    'displayName' => $captacion_user_display_name,
    'firstName' => $captacion_user->first_name,
    'lastName' => $captacion_user->last_name,
    'username' => $captacion_user->user_login,
    'email' => $captacion_user->user_email,
    'phone' => get_user_meta(get_current_user_id(), 'captacion_phone', true),
  ) : null,
  'accessState' => is_user_logged_in() ? captacion_app_get_user_access_state(get_current_user_id()) : null,
  'resources' => $captacion_resource_cards,
  'nonce' => wp_create_nonce('wp_rest'),
);
$captacion_territories_path = get_template_directory() . '/src/data/territorios-espana.json';
$captacion_territories_json = function_exists('captacion_app_get_territory_catalog_json') ? captacion_app_get_territory_catalog_json() : (file_exists($captacion_territories_path) ? file_get_contents($captacion_territories_path) : '[]');
$captacion_rest_nonce = is_user_logged_in() ? wp_create_nonce('wp_rest') : '';
$captacion_current_user = wp_get_current_user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="color-scheme" content="light dark" />
  <meta id="theme-color-meta" name="theme-color" content="#eef3f8" />
  <?php if (!defined('RANK_MATH_VERSION')) : ?>
  <meta name="description" content="<?php echo esc_attr(captacion_app_setting('meta_description')); ?>" />
  <title><?php echo esc_html(captacion_app_setting('site_title')); ?></title>
  <?php endif; ?>
  
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            navy: {
              DEFAULT: '#10233c',
              light: '#172f50',
              dark: '#0d1c30',
            },
            blue: {
              DEFAULT: '#1b67d6',
              dark: '#0d4eae',
              light: '#e8f4ff',
            },
            green: {
              DEFAULT: '#15936a',
              light: '#e2f8ef',
            },
            amber: {
              DEFAULT: '#d98b13',
              light: '#fff2d8',
            }
          },
          borderRadius: {
            'premium': '20px',
          }
        }
      }
    }
  </script>
  
  <!-- Preferencia visual persistente: claro / oscuro -->
  <script>
    (function () {
      try {
        const storedTheme = localStorage.getItem('captacion_theme_v1');
        document.documentElement.dataset.theme = storedTheme || 'dark';
      } catch (error) {
        document.documentElement.dataset.theme = 'dark';
      }
    })();
  </script>

  <!-- Google Fonts: Inter -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <!-- Leaflet: mapa georreferenciado de España -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
  
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
    .scrollbar-hidden::-webkit-scrollbar {
      display: none;
    }
    .scrollbar-hidden {
      -ms-overflow-style: none;
      scrollbar-width: none;
    }
    /* Transición suave para cambio de páginas */
    .page-section {
      animation: fadeIn 0.25s ease-in-out forwards;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(8px); }
      to { opacity: 1; transform: translateY(0); }
    }
    #home-map, #marketplace-map, #needs-map { min-height: 430px; }
    .leaflet-container { font-family: 'Inter', sans-serif; background: #e8eef5; }
    .leaflet-popup-content-wrapper { border-radius: 16px; }
    .leaflet-popup-content { margin: 14px 16px; }
    .map-label-div-icon { background: transparent; border: 0; }
    .map-price-pill, .map-demand-pill {
      display: inline-flex;
      min-width: 52px;
      align-items: center;
      justify-content: center;
      padding: 5px 8px;
      border-radius: 999px;
      border: 2px solid rgba(255,255,255,.95);
      color: white;
      font-size: 10px;
      font-weight: 900;
      line-height: 1;
      letter-spacing: -.02em;
      box-shadow: 0 5px 12px rgba(15, 23, 42, .24);
      white-space: nowrap;
    }
    .map-price-pill { background: #b00016; }
    .map-demand-pill { background: #087653; }
    .map-view-active { background: #10233c; color: white; border-color: #10233c; }
    .auth-tab-active { background: #10233c; color: white; box-shadow: 0 4px 12px rgba(16,35,60,.16); }
    .map-filter-active { background: #10233c; color: white; border-color: #10233c; }

    /* =========================================================
       TEMA VISUAL GLOBAL: CLARO CON CONTRASTE Y MODO OSCURO
       ========================================================= */
    :root {
      color-scheme: light;
      --app-bg: #eef3f8;
      --app-surface: #ffffff;
      --app-surface-soft: #f5f8fc;
      --app-surface-muted: #e8eef5;
      --app-border: #cbd5e1;
      --app-border-soft: #dbe3ed;
      --app-text: #24364b;
      --app-text-muted: #52657a;
      --app-shadow: 0 12px 30px rgba(15, 35, 60, .10);
    }
    html[data-theme="dark"] {
      color-scheme: dark;
      --app-bg: #091321;
      --app-surface: #111f31;
      --app-surface-soft: #16263a;
      --app-surface-muted: #1b2c42;
      --app-border: #33465d;
      --app-border-soft: #26394e;
      --app-text: #e8f0fa;
      --app-text-muted: #a9b8ca;
      --app-shadow: 0 14px 34px rgba(0, 0, 0, .28);
    }
    body {
      background: var(--app-bg) !important;
      color: var(--app-text);
      transition: background-color .22s ease, color .22s ease;
    }
    body *:not(.cmplz-cookiebanner):not(.cmplz-cookiebanner *):not(.cmplz-manage-consent):not(.cmplz-manage-consent *),
    body *::before,
    body *::after {
      transition-property: background-color, border-color, color, box-shadow, opacity, transform;
      transition-duration: .18s;
      transition-timing-function: ease;
    }
    .theme-toggle-button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      border: 1px solid var(--app-border);
      border-radius: 12px;
      background: var(--app-surface-soft);
      color: var(--app-text);
      padding: 0;
      font-size: 11px;
      font-weight: 800;
      box-shadow: 0 4px 10px rgba(15, 35, 60, .06);
    }
    .theme-toggle-button:hover { transform: translateY(-1px); border-color: #1b67d6; }
    .theme-toggle-icon { font-size: 14px; line-height: 1; }
    .brand-logo-full {
      display: block;
      height: 48px;
      width: auto;
      max-width: min(46vw, 320px);
      object-fit: contain;
    }
    .brand-logo-mark {
      display: block;
      height: 46px;
      width: 46px;
      object-fit: contain;
      border-radius: 12px;
    }
    #home-explainer-video-slot {
      aspect-ratio: 16 / 8.9;
    }
    #home-explainer-video-slot video {
      transform: scale(1.1);
      transform-origin: center center;
      object-position: center center;
    }
    .hero-title {
      max-width: 12ch;
      text-wrap: balance;
    }
    .font-black,
    .font-extrabold {
      letter-spacing: -0.02em;
    }
    .font-black { font-weight: 800 !important; }
    .font-extrabold { font-weight: 800 !important; }
    h1, h2, h3, h4, strong {
      text-wrap: balance;
    }

    /* CTA legibles: peso fuerte pero no excesivamente condensado */
    button.bg-blue, button.bg-navy, button.bg-green,
    a.bg-blue, a.bg-navy, a.bg-green,
    button[class*="from-purple-600"], button[class*="from-blue"] {
      font-family: 'Inter', sans-serif !important;
      font-weight: 700 !important;
      letter-spacing: .012em !important;
      line-height: 1.3;
    }

    /* Etiquetas de datos inmobiliarios: negrita legible sin densidad excesiva */
    .metric-label {
      display: block;
      margin-top: .125rem;
      color: #64748b;
      font-family: 'Inter', sans-serif;
      font-size: 10px;
      font-weight: 650;
      line-height: 1.25;
      letter-spacing: .012em;
    }
    .metric-value {
      display: block;
      color: #10233c;
      font-family: 'Inter', sans-serif;
      font-weight: 700;
      line-height: 1.22;
      letter-spacing: -.006em;
    }


    /* Carruseles compactos de la Home: 5 fichas visibles en escritorio */
    .home-carousel-shell { position: relative; }
    .home-carousel-track {
      display: flex;
      gap: 1rem;
      overflow-x: auto;
      scroll-behavior: smooth;
      scroll-snap-type: x mandatory;
      padding: .2rem .15rem .65rem;
    }
    .home-carousel-card {
      flex: 0 0 100%;
      min-width: 0;
      scroll-snap-align: start;
    }
    .home-carousel-nav {
      position: absolute;
      top: 50%;
      z-index: 10;
      display: inline-flex;
      width: 2.5rem;
      height: 2.5rem;
      align-items: center;
      justify-content: center;
      transform: translateY(-50%);
      border: 1px solid var(--app-border);
      border-radius: 999px;
      background: var(--app-surface);
      color: var(--app-text);
      box-shadow: 0 8px 18px rgba(15, 35, 60, .16);
      font-size: 1rem;
      font-weight: 800;
    }
    .home-carousel-nav:hover { border-color: #1b67d6; color: #1b67d6; }
    .home-carousel-nav-prev { left: -.7rem; }
    .home-carousel-nav-next { right: -.7rem; }
    @media (min-width: 640px) {
      .home-carousel-card { flex-basis: calc((100% - 1rem) / 2); }
    }
    @media (min-width: 1024px) {
      .home-carousel-card { flex-basis: calc((100% - 4rem) / 5); }
    }
    html[data-theme="dark"] .metric-label { color: #a9b8ca; }
    html[data-theme="dark"] .metric-value { color: #edf5ff; }

    /* Modo claro con separación visual más definida */
    html[data-theme="light"] body { background: #eef3f8 !important; }
    html[data-theme="light"] .bg-white { background-color: #ffffff !important; }
    html[data-theme="light"] .bg-slate-50 { background-color: #f4f7fb !important; }
    html[data-theme="light"] .bg-slate-100 { background-color: #e8eef5 !important; }
    html[data-theme="light"] .border-slate-100 { border-color: #dbe3ed !important; }
    html[data-theme="light"] .border-slate-200 { border-color: #cbd5e1 !important; }
    html[data-theme="light"] .border-slate-300 { border-color: #b7c4d4 !important; }
    html[data-theme="light"] .shadow-sm,
    html[data-theme="light"] .shadow-md,
    html[data-theme="light"] .shadow-lg,
    html[data-theme="light"] .shadow-xl { box-shadow: var(--app-shadow) !important; }
    html[data-theme="light"] .text-slate-700 { color: #34485d !important; }
    html[data-theme="light"] .text-slate-600 { color: #415569 !important; }
    html[data-theme="light"] .text-slate-500 { color: #4c6074 !important; }
    html[data-theme="light"] .text-slate-400 { color: #607489 !important; }
    html[data-theme="light"] .text-navy { color: #10233c !important; }
    html[data-theme="light"] input,
    html[data-theme="light"] select,
    html[data-theme="light"] textarea { background-color: #ffffff; border-color: #b9c7d8 !important; color: #24364b; }

    /* Modo oscuro coherente para superficies, formularios y mapas */
    html[data-theme="dark"] body { background: #091321 !important; color: #e8f0fa; }
    html[data-theme="dark"] header { background: rgba(13, 28, 48, .96) !important; border-color: #33465d !important; }
    html[data-theme="dark"] .bg-white { background-color: #111f31 !important; }
    html[data-theme="dark"] .bg-white\/95 { background-color: rgba(17, 31, 49, .95) !important; }
    html[data-theme="dark"] .bg-white\/90 { background-color: rgba(17, 31, 49, .90) !important; }
    html[data-theme="dark"] .bg-white\/80 { background-color: rgba(17, 31, 49, .80) !important; }
    html[data-theme="dark"] .bg-slate-50 { background-color: #16263a !important; }
    html[data-theme="dark"] .bg-slate-50\/50 { background-color: rgba(22, 38, 58, .72) !important; }
    html[data-theme="dark"] .bg-slate-50\/70 { background-color: rgba(22, 38, 58, .86) !important; }
    html[data-theme="dark"] .bg-slate-100 { background-color: #1b2c42 !important; }
    html[data-theme="dark"] .bg-slate-200 { background-color: #33465d !important; }
    html[data-theme="dark"] .bg-blue-light { background-color: #17375e !important; }
    html[data-theme="dark"] .bg-green-light { background-color: #123d34 !important; }
    html[data-theme="dark"] .bg-amber-light { background-color: #4a3513 !important; }
    html[data-theme="dark"] .text-navy { color: #edf5ff !important; }
    html[data-theme="dark"] .text-slate-800 { color: #e8f0fa !important; }
    html[data-theme="dark"] .text-slate-700 { color: #d0dbea !important; }
    html[data-theme="dark"] .text-slate-600 { color: #d2dceb !important; }
    html[data-theme="dark"] .text-slate-500 { color: #c1cede !important; }
    html[data-theme="dark"] .text-slate-400 { color: #aebfd3 !important; }
    html[data-theme="dark"] .text-blue { color: #79b7ff !important; }
    html[data-theme="dark"] .text-green { color: #5ad8ad !important; }
    html[data-theme="dark"] .text-amber { color: #f6c668 !important; }
    html[data-theme="dark"] .border-slate-100 { border-color: #26394e !important; }
    html[data-theme="dark"] .border-slate-200 { border-color: #33465d !important; }
    html[data-theme="dark"] .border-slate-300 { border-color: #496079 !important; }
    html[data-theme="dark"] .shadow-sm,
    html[data-theme="dark"] .shadow-md,
    html[data-theme="dark"] .shadow-lg,
    html[data-theme="dark"] .shadow-xl,
    html[data-theme="dark"] .shadow-2xl { box-shadow: var(--app-shadow) !important; }
    html[data-theme="dark"] input,
    html[data-theme="dark"] select,
    html[data-theme="dark"] textarea {
      background-color: #0d1c30 !important;
      border-color: #41566e !important;
      color: #edf5ff !important;
    }
    html[data-theme="dark"] input::placeholder,
    html[data-theme="dark"] textarea::placeholder { color: #8191a6 !important; }
    html[data-theme="dark"] option { background-color: #0d1c30; color: #edf5ff; }
    html[data-theme="dark"] .leaflet-container { background: #16263a; }
    html[data-theme="dark"] .leaflet-tile { filter: brightness(.72) contrast(1.12) saturate(.78); }
    html[data-theme="dark"] .leaflet-popup-content-wrapper,
    html[data-theme="dark"] .leaflet-popup-tip,
    html[data-theme="dark"] .leaflet-control-zoom a { background: #111f31; color: #edf5ff; border-color: #33465d; }
    html[data-theme="dark"] #page-inicio > section:first-child {
      background: linear-gradient(135deg, #10233c 0%, #0d1c30 54%, #091321 100%) !important;
    }


    /* =========================================================
       MODO OSCURO HOMOGÉNEO: FONDO IA MODERNO Y CONSISTENTE
       ========================================================= */
    html[data-theme="dark"] body {
      background:
        radial-gradient(circle at 12% 8%, rgba(27, 103, 214, .20), transparent 34%),
        radial-gradient(circle at 88% 14%, rgba(124, 58, 237, .14), transparent 28%),
        radial-gradient(circle at 52% 94%, rgba(21, 147, 106, .10), transparent 34%),
        #07111f !important;
      background-attachment: fixed !important;
      color: #e8f0fa;
    }
    html[data-theme="dark"] .page-section,
    html[data-theme="dark"] .page-section > section,
    html[data-theme="dark"] #page-inicio > section:first-child {
      background: transparent !important;
    }
    html[data-theme="dark"] header {
      background: rgba(7, 17, 31, .88) !important;
      border-color: rgba(100, 139, 190, .28) !important;
      backdrop-filter: blur(18px);
      -webkit-backdrop-filter: blur(18px);
    }
    html[data-theme="dark"] footer {
      background: rgba(7, 17, 31, .82) !important;
      border-color: rgba(100, 139, 190, .24) !important;
    }
    html[data-theme="dark"] .bg-white,
    html[data-theme="dark"] .legal-card,
    html[data-theme="dark"] .ai-connection-card {
      background: rgba(15, 29, 48, .88) !important;
      border-color: rgba(100, 139, 190, .28) !important;
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
    }
    html[data-theme="dark"] .bg-slate-50,
    html[data-theme="dark"] .bg-slate-50\/50,
    html[data-theme="dark"] .bg-slate-50\/70,
    html[data-theme="dark"] .bg-slate-100 {
      background-color: rgba(18, 35, 57, .78) !important;
    }
    html[data-theme="dark"] .ai-provider-chip {
      background: rgba(24, 48, 78, .82);
      border-color: rgba(121, 183, 255, .32);
    }
    .ai-provider-chip {
      border: 1px solid var(--app-border);
      border-radius: 1rem;
      background: var(--app-surface-soft);
      padding: .9rem;
    }
    .ai-connection-card {
      border: 1px solid var(--app-border);
      border-radius: 1rem;
      background: var(--app-surface);
      padding: 1rem;
    }


    /* =========================================================
       CAPA LEGAL Y DE CUMPLIMIENTO: RGPD / LOPDGDD / LSSI / DSA
       ========================================================= */
    .legal-placeholder {
      display: inline-flex;
      align-items: center;
      gap: .35rem;
      border: 1px solid #f59e0b;
      border-radius: .55rem;
      background: #fff7d6;
      color: #8a4b00;
      padding: .2rem .45rem;
      font-size: .72rem;
      font-weight: 800;
      line-height: 1.25;
    }
    .legal-card {
      border: 1px solid var(--app-border);
      border-radius: 1rem;
      background: var(--app-surface);
      padding: 1.15rem;
      box-shadow: 0 8px 18px rgba(15,35,60,.06);
    }
    .legal-card h3 { color: var(--app-text); font-weight: 800; }
    .legal-card p, .legal-card li { color: var(--app-text-muted); }
    .legal-card ul { padding-left: 1.1rem; list-style: disc; }
    .legal-link { color: #1b67d6; font-weight: 700; }
    .legal-link:hover { text-decoration: underline; }
    .legal-footer {
      border-top: 1px solid var(--app-border);
      background: var(--app-surface);
      color: var(--app-text-muted);
    }
    .legal-footer a, .legal-footer button { color: var(--app-text-muted); font-weight: 700; }
    .legal-footer a:hover, .legal-footer button:hover { color: #1b67d6; }
    html[data-theme="dark"] .legal-placeholder {
      border-color: #d98b13;
      background: rgba(217,139,19,.15);
      color: #ffd58a;
    }
    .legal-consent-box {
      border: 1px solid var(--app-border-soft);
      border-radius: .9rem;
      background: var(--app-surface-soft);
      padding: .8rem .9rem;
    }


    /* =========================================================
       DASHBOARD PRIVADO DEL AGENTE: CAPTACIÓN + DEMANDA
       ========================================================= */
    .private-dashboard-shell { display:grid; gap:1.25rem; }
    @media (min-width: 1024px) { .private-dashboard-shell { grid-template-columns: 248px minmax(0, 1fr); } }
    .private-dashboard-sidebar {
      border:1px solid var(--app-border); border-radius:1.25rem; background:var(--app-surface);
      box-shadow:var(--app-shadow); padding:.75rem; height:max-content; position:sticky; top:6rem;
    }
    #page-area-privada { font-size:16px; }
    .private-dashboard-nav { width:100%; display:flex; align-items:center; gap:.7rem; padding:.72rem .78rem; border-radius:.85rem; color:var(--app-text-muted); font-size:.875rem; font-weight:750; text-align:left; }
    .private-dashboard-nav:hover { background:var(--app-surface-soft); color:#1b67d6; }
    .private-dashboard-nav.active { background:#10233c; color:#fff; box-shadow:0 7px 16px rgba(16,35,60,.18); }
    html[data-theme="dark"] .private-dashboard-nav.active { background:linear-gradient(135deg,#1b67d6,#5b3fd1); }
    .private-dashboard-panel { display:none; }
    .private-dashboard-panel.active { display:block; animation:fadeIn .22s ease both; }
    .private-kpi-card { border:1px solid var(--app-border); border-radius:1rem; background:var(--app-surface); padding:1rem; box-shadow:0 8px 20px rgba(15,35,60,.06); }
    .private-kpi-card button { text-align:left; width:100%; }
    .private-section-card { border:1px solid var(--app-border); border-radius:1.15rem; background:var(--app-surface); box-shadow:0 10px 24px rgba(15,35,60,.06); }
    .private-priority-high { border-left:4px solid #dc2626; }
    .private-priority-medium { border-left:4px solid #d98b13; }
    .private-priority-low { border-left:4px solid #1b67d6; }
    .private-status-pill { display:inline-flex; align-items:center; border-radius:999px; padding:.28rem .55rem; font-size:.62rem; font-weight:800; line-height:1; }
    .private-table th { color:#64748b; font-size:.64rem; font-weight:800; letter-spacing:.05em; text-transform:uppercase; white-space:nowrap; }
    .private-table td { color:var(--app-text-muted); font-size:.73rem; vertical-align:top; }
    .private-table tr:hover td { background:var(--app-surface-soft); }
    .private-dashboard-mobile-select { border:1px solid var(--app-border); border-radius:.85rem; background:var(--app-surface); color:var(--app-text); padding:.78rem .9rem; width:100%; font-size:.8rem; font-weight:700; }
    .private-mini-card { border:1px solid var(--app-border); border-radius:1rem; background:var(--app-surface-soft); padding:.85rem; }
    .private-progress-track { height:.45rem; overflow:hidden; border-radius:999px; background:var(--app-surface-muted); }
    .private-progress-bar { height:100%; border-radius:999px; background:linear-gradient(90deg,#1b67d6,#15936a); }

    /* Resumen ejecutivo premium */
    #page-area-privada.executive-mode { background:#061224; color:#eaf2ff; min-height:100vh; }
    #page-area-privada.executive-mode > section { max-width:1680px; padding-top:.5rem; }
    #page-area-privada.executive-mode .private-area-legacy-header { display:none; }
    #page-area-privada.executive-mode .private-dashboard-shell { gap:0; grid-template-columns:218px minmax(0,1fr); overflow:hidden; border:1px solid rgba(126,159,205,.12); border-radius:1.4rem; background:#08162a; box-shadow:0 32px 80px rgba(0,0,0,.32); }
    #page-area-privada.executive-mode .private-dashboard-sidebar { height:100%; min-height:920px; top:0; border:0; border-right:1px solid rgba(126,159,205,.12); border-radius:0; padding:1rem .7rem; background:linear-gradient(180deg,#061225,#07162a); box-shadow:none; }
    #page-area-privada.executive-mode .private-dashboard-nav { color:#b8c7dc; padding:.72rem; border-radius:.65rem; font-size:.875rem; }
    #page-area-privada.executive-mode .private-dashboard-nav:hover { background:rgba(56,115,255,.1); color:#fff; }
    #page-area-privada.executive-mode .private-dashboard-nav.active { background:linear-gradient(135deg,rgba(45,100,230,.7),rgba(48,78,184,.76)); color:#fff; box-shadow:inset 0 0 0 1px rgba(87,142,255,.35),0 8px 18px rgba(13,64,190,.22); }
    #page-area-privada.executive-mode .private-dashboard-sidebar nav > div { border-color:rgba(126,159,205,.12); }
    .exec-sidebar-brand { display:none; padding:.5rem .55rem 1.15rem; }
    #page-area-privada.executive-mode .exec-sidebar-brand { display:flex; align-items:center; gap:.65rem; }
    .exec-brand-mark { position:relative; width:35px; height:31px; flex:0 0 35px; }
    .exec-brand-mark:before { content:''; position:absolute; inset:5px 3px 4px; border:5px solid #4382ff; border-bottom:0; border-radius:5px 5px 0 0; transform:rotate(45deg); }
    .exec-brand-mark:after { content:''; position:absolute; left:4px; right:3px; bottom:1px; height:5px; border-radius:999px; background:linear-gradient(90deg,#4382ff,#6f8fff); transform:rotate(5deg); }
    .exec-sidebar-profile { margin-top:1rem; padding:.85rem .6rem .2rem; border-top:1px solid rgba(126,159,205,.12); }
    #page-area-privada.executive-mode .exec-sidebar-profile { border-bottom:0 !important; }
    #page-area-privada.executive-mode #private-dashboard-agent-name { color:#f8fbff; }
    #page-area-privada.executive-mode #private-dashboard-agent-agency { color:#7f93b0; }
    .exec-dashboard { min-height:920px; padding:1.45rem; background:radial-gradient(circle at 52% -10%,rgba(50,103,218,.14),transparent 33%),linear-gradient(145deg,#0a1a31,#08172b 45%,#09182b); color:#eaf2ff; }
    #private-panel-overview > .exec-dashboard ~ * { display:none !important; }
    .exec-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; margin-bottom:1.25rem; }
    .exec-head h3 { margin:0; color:#f8fbff; font-size:1.75rem; line-height:1.1; font-weight:900; letter-spacing:-.035em; }
    .exec-head p { margin:.35rem 0 0; color:#aebed3; font-size:.94rem; }
    .exec-head-actions { display:flex; gap:.65rem; }
    .exec-control { display:inline-flex; align-items:center; justify-content:center; gap:.55rem; min-height:42px; padding:.65rem .9rem; border:1px solid rgba(131,160,203,.28); border-radius:.7rem; background:rgba(8,22,42,.6); color:#dce8fa; font-size:.94rem; font-weight:750; }
    .exec-control:hover { border-color:rgba(75,132,255,.7); background:rgba(28,61,112,.45); }
    .exec-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:.8rem; margin-bottom:1rem; }
    .exec-card { border:1px solid rgba(128,158,200,.22); border-radius:.95rem; background:linear-gradient(145deg,rgba(19,39,67,.92),rgba(10,26,49,.92)); box-shadow:0 15px 32px rgba(0,0,0,.15),inset 0 1px 0 rgba(255,255,255,.025); }
    .exec-kpi { position:relative; width:100%; min-height:170px; padding:1rem; overflow:hidden; text-align:left; cursor:pointer; }
    .exec-kpi:hover,.exec-kpi:focus-visible,.exec-clickable:hover,.exec-clickable:focus-visible { transform:translateY(-2px); border-color:rgba(92,147,255,.85); box-shadow:0 18px 38px rgba(0,0,0,.22),0 0 0 3px rgba(61,120,244,.15); outline:0; }
    .exec-kpi:after { content:''; position:absolute; width:95px; height:95px; right:-35px; top:-40px; border-radius:50%; background:var(--glow); filter:blur(14px); opacity:.24; }
    .exec-kpi-blue { --glow:#3477ff; border-color:rgba(52,119,255,.55); }
    .exec-kpi-green { --glow:#25bb81; border-color:rgba(37,187,129,.42); }
    .exec-kpi-yellow { --glow:#f2b91f; border-color:rgba(242,185,31,.44); }
    .exec-kpi-violet { --glow:#7046ed; border-color:rgba(112,70,237,.47); }
    .exec-kpi-top { display:flex; align-items:center; gap:.65rem; }
    .exec-icon { display:grid; place-items:center; width:40px; height:40px; flex:0 0 40px; border-radius:50%; color:#fff; font-size:1.15rem; font-weight:900; background:var(--glow); box-shadow:0 8px 20px rgba(30,70,150,.32); }
    .exec-kpi-label { color:#e2ecfa; font-size:.85rem; font-weight:900; text-transform:uppercase; line-height:1.25; }
    .exec-kpi strong { display:block; margin-top:.25rem; color:#f7fbff; font-size:2rem; line-height:1; letter-spacing:-.03em; }
    .exec-kpi-value { margin-top:.75rem; color:#b6c5d9; font-size:.88rem; }
    .exec-trend { display:flex; align-items:center; gap:.35rem; margin-top:.65rem; color:#b4c3d6; font-size:.82rem; }
    .exec-card-cta { display:inline-flex; align-items:center; gap:.35rem; margin-top:.65rem; color:#8bb5ff; font-size:.88rem; font-weight:850; }
    .exec-trend b { color:#37d98d; }
    .exec-trend.neutral b { color:#dce7f7; }
    .exec-pipeline { min-height:154px; padding:1rem 1.15rem .55rem; }
    .exec-pipeline-label { color:#dce8f8; font-size:.84rem; font-weight:900; text-align:center; text-transform:uppercase; }
    .exec-pipeline strong { display:block; color:#f8fbff; font-size:1.48rem; text-align:center; margin:.35rem 0 .1rem; letter-spacing:-.035em; }
    .exec-sparkline { width:100%; height:56px; overflow:visible; }
    .exec-sparkline .area { fill:url(#execSparkGradient); }
    .exec-sparkline .line { fill:none; stroke:#4c83ff; stroke-width:2; }
    .exec-months { display:flex; justify-content:space-between; color:#8296b3; font-size:.53rem; }
    .exec-central { display:grid; grid-template-columns:.95fr 1.25fr; gap:.9rem; margin-bottom:.9rem; }
    .exec-panel { padding:1.1rem; }
    .exec-panel-title { margin:0 0 .9rem; color:#eef5ff; font-size:1rem; font-weight:900; text-transform:uppercase; }
    .exec-distribution { display:grid; grid-template-columns:minmax(180px,.9fr) 1fr; align-items:center; gap:1rem; }
    .exec-donut { position:relative; width:min(210px,100%); aspect-ratio:1; margin:auto; border-radius:50%; box-shadow:0 18px 35px rgba(0,0,0,.18); }
    .exec-donut-svg { width:100%; height:100%; transform:rotate(-90deg); overflow:visible; }
    .exec-donut-segment { fill:none; stroke-width:24; cursor:pointer; transition:stroke-width .18s ease,filter .18s ease,opacity .18s ease; }
    .exec-donut-segment:hover,.exec-donut-segment:focus-visible { stroke-width:29; filter:drop-shadow(0 0 6px currentColor); opacity:.96; outline:0; }
    .exec-donut-hole { position:absolute; inset:23%; border-radius:50%; background:#0c1c33; box-shadow:inset 0 0 16px rgba(0,0,0,.32); }
    .exec-donut-center { position:absolute; inset:0; z-index:1; display:grid; place-content:center; text-align:center; pointer-events:none; }
    .exec-donut-center strong { color:#f8fbff; font-size:1.9rem; line-height:1; }
    .exec-donut-center span { margin-top:.3rem; color:#879bb7; font-size:.62rem; }
    .exec-legend { display:grid; gap:.95rem; }
    .exec-legend-row { display:grid; width:100%; grid-template-columns:auto 1fr auto; align-items:center; gap:.55rem; padding:.45rem .5rem; border-radius:.55rem; color:#d4dfed; font-size:.9rem; text-align:left; cursor:pointer; }
    .exec-legend-row:hover,.exec-legend-row:focus-visible { background:rgba(61,120,244,.12); color:#fff; outline:2px solid rgba(92,147,255,.45); }
    .exec-dot { width:10px; height:10px; border-radius:50%; box-shadow:0 0 10px currentColor; }
    .exec-legend-row b { color:#f7fbff; font-size:.86rem; }
    .exec-funnel-grid { display:grid; grid-template-columns:minmax(190px,.82fr) 1.18fr; align-items:center; gap:1rem; }
    .exec-funnel { display:flex; flex-direction:column; align-items:center; gap:4px; padding:.1rem 0; }
    .exec-funnel-step { height:47px; border:0; cursor:pointer; clip-path:polygon(0 0,100% 0,88% 100%,12% 100%); filter:drop-shadow(0 8px 12px rgba(0,0,0,.18)); }
    .exec-funnel-step:hover,.exec-funnel-step:focus-visible { filter:brightness(1.14) drop-shadow(0 9px 13px rgba(0,0,0,.28)); outline:0; }
    .exec-funnel-step:nth-child(1){width:100%;background:linear-gradient(90deg,#1d50be,#3d7af2)}
    .exec-funnel-step:nth-child(2){width:75%;background:linear-gradient(90deg,#188d61,#38c28a)}
    .exec-funnel-step:nth-child(3){width:53%;background:linear-gradient(90deg,#d89d08,#ffc72e)}
    .exec-funnel-step:nth-child(4){width:32%;background:linear-gradient(90deg,#5423c1,#8253f2)}
    .exec-funnel-step:nth-child(5){width:21%;height:34px;background:linear-gradient(90deg,#c8296d,#f25295)}
    .exec-funnel-table { width:100%; border-collapse:collapse; }
    .exec-funnel-table tr { border-bottom:1px solid rgba(128,158,200,.14); }
    .exec-funnel-table tr:last-child { border-bottom:0; }
    .exec-funnel-table tr { cursor:pointer; }
    .exec-funnel-table tr:hover,.exec-funnel-table tr:focus-within { background:rgba(61,120,244,.1); }
    .exec-funnel-table button { width:100%; color:inherit; text-align:left; }
    .exec-funnel-table td { padding:.67rem .35rem; color:#d3dfef; font-size:.88rem; }
    .exec-funnel-table td:nth-child(2),.exec-funnel-table td:nth-child(3){text-align:right;color:#f3f7fd;font-weight:850}
    .exec-lower { display:grid; grid-template-columns:1fr 1.05fr 1fr; gap:.9rem; margin-bottom:.9rem; }
    .exec-list-card { min-height:213px; padding:1rem; }
    .exec-list-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:.55rem; }
    .exec-list-head h4 { margin:0; color:#f2f7ff; font-size:.95rem; font-weight:900; text-transform:uppercase; }
    .exec-list-head button { color:#83b3ff; font-size:.84rem; font-weight:800; }
    .exec-row { display:flex; align-items:center; gap:.65rem; padding:.62rem 0; border-bottom:1px solid rgba(128,158,200,.13); }
    .exec-row:last-child { border-bottom:0; }
    .exec-avatar,.exec-thumb { display:grid; place-items:center; width:34px; height:34px; flex:0 0 34px; border-radius:50%; color:#fff; font-size:.57rem; font-weight:900; background:linear-gradient(135deg,#3477ff,#234aa7); }
    .exec-avatar.green { background:linear-gradient(135deg,#2fb98a,#247d68); }
    .exec-thumb { border-radius:.5rem; object-fit:cover; background:#17355e; }
    .exec-row-copy { min-width:0; flex:1; }
    .exec-row.exec-clickable { width:100%; text-align:left; cursor:pointer; border-radius:.55rem; padding-left:.3rem; padding-right:.3rem; }
    .exec-row-copy strong { display:block; overflow:hidden; color:#edf4ff; font-size:.88rem; text-overflow:ellipsis; white-space:nowrap; }
    .exec-row-copy span { display:block; overflow:hidden; margin-top:.18rem; color:#a9b9cf; font-size:.78rem; text-overflow:ellipsis; white-space:nowrap; }
    .exec-row-meta { flex:0 0 auto; color:#afbed1; font-size:.76rem; text-align:right; }
    .exec-pill { display:inline-flex; margin-top:.18rem; padding:.15rem .35rem; border:1px solid rgba(62,128,255,.42); border-radius:.4rem; background:rgba(52,119,255,.12); color:#9dc0ff; font-size:.48rem; }
    .exec-pill.green { border-color:rgba(46,204,134,.3); background:rgba(46,204,134,.13); color:#48dc98; }
    .exec-task-check { display:grid; place-items:center; width:19px; height:19px; flex:0 0 19px; border:1px solid #7690b5; border-radius:50%; color:#9fb3d0; font-size:.55rem; }
    .exec-summary { display:grid; grid-template-columns:repeat(3,1fr); padding:.7rem 1rem; }
    .exec-summary-item { display:flex; align-items:center; justify-content:center; gap:.8rem; min-height:56px; border-right:1px solid rgba(128,158,200,.15); }
    .exec-summary-item:nth-child(3n) { border-right:0; }
    .exec-summary-item:nth-child(-n+3) { border-bottom:1px solid rgba(128,158,200,.15); }
    .exec-summary-icon { display:grid; place-items:center; width:38px; height:38px; border-radius:50%; font-size:1.1rem; background:rgba(52,119,255,.12); color:#75a5ff; }
    .exec-summary-copy span { display:block; color:#bdcadd; font-size:.78rem; font-weight:850; text-transform:uppercase; }
    .exec-summary-copy strong { display:inline-block; margin-top:.15rem; color:#f8fbff; font-size:1.25rem; }
    .exec-summary-copy button { margin-left:.45rem; color:#9db5d6; font-size:.78rem; }
    .exec-summary-item.exec-clickable { cursor:pointer; border-radius:.65rem; }
    .exec-summary-item.exec-clickable:hover,.exec-summary-item.exec-clickable:focus-visible { background:rgba(61,120,244,.1); outline:2px solid rgba(92,147,255,.35); }
    .exec-exporting .exec-head-actions { display:none !important; }
    .exec-dashboard.exec-exporting { width:760px !important; min-height:0; padding:1rem !important; }
    .exec-exporting .exec-kpis { grid-template-columns:repeat(2,minmax(0,1fr)) !important; }
    .exec-exporting .exec-pipeline { grid-column:span 2 !important; }
    .exec-exporting .exec-central,.exec-exporting .exec-lower { grid-template-columns:1fr !important; }
    .exec-exporting .exec-summary { grid-template-columns:repeat(2,1fr) !important; }
    .exec-exporting .exec-summary-item:nth-child(3n) { border-right:1px solid rgba(128,158,200,.15); }
    .exec-exporting .exec-summary-item:nth-child(2n) { border-right:0; }
    .exec-pdf-meta { display:flex; justify-content:space-between; gap:1rem; margin:-.4rem 0 1rem; color:#aebed3; font-size:.82rem; }
    html[data-theme="light"] #page-area-privada.executive-mode { background:#eef3f8; color:#24364b; }
    html[data-theme="light"] #page-area-privada.executive-mode .private-dashboard-shell { border-color:#cbd5e1; background:#f8fafc; box-shadow:0 24px 60px rgba(15,35,60,.14); }
    html[data-theme="light"] #page-area-privada.executive-mode .private-dashboard-sidebar { border-color:#d5dee9; background:linear-gradient(180deg,#f8fbff,#edf3f9); }
    html[data-theme="light"] #page-area-privada.executive-mode .private-dashboard-nav { color:#42566d; }
    html[data-theme="light"] #page-area-privada.executive-mode .private-dashboard-nav:hover { background:#e2edfb; color:#0d4eae; }
    html[data-theme="light"] #page-area-privada.executive-mode #private-dashboard-agent-name { color:#10233c; }
    html[data-theme="light"] #page-area-privada.executive-mode #private-dashboard-agent-agency { color:#52657a; }
    html[data-theme="light"] .exec-dashboard { background:radial-gradient(circle at 52% -10%,rgba(50,103,218,.11),transparent 33%),linear-gradient(145deg,#f8fbff,#edf3f9 55%,#e9f0f7); color:#24364b; }
    html[data-theme="light"] .exec-head h3,html[data-theme="light"] .exec-panel-title,html[data-theme="light"] .exec-list-head h4 { color:#10233c; }
    html[data-theme="light"] .exec-head p,html[data-theme="light"] .exec-kpi-value,html[data-theme="light"] .exec-trend,html[data-theme="light"] .exec-row-copy span,html[data-theme="light"] .exec-row-meta { color:#52657a; }
    html[data-theme="light"] .exec-control { border-color:#b9c7d8; background:#fff; color:#24364b; }
    html[data-theme="light"] .exec-card { border-color:#c8d4e2; background:linear-gradient(145deg,#fff,#f5f8fc); box-shadow:0 12px 28px rgba(15,35,60,.08); }
    html[data-theme="light"] .exec-kpi-label,html[data-theme="light"] .exec-pipeline-label,html[data-theme="light"] .exec-row-copy strong,html[data-theme="light"] .exec-legend-row { color:#34485d; }
    html[data-theme="light"] .exec-kpi strong,html[data-theme="light"] .exec-pipeline strong,html[data-theme="light"] .exec-legend-row b,html[data-theme="light"] .exec-funnel-table td,html[data-theme="light"] .exec-summary-copy strong { color:#10233c; }
    html[data-theme="light"] .exec-donut-hole { background:#f8fbff; }
    html[data-theme="light"] .exec-donut-center strong { color:#10233c; }
    html[data-theme="light"] .exec-donut-center span,html[data-theme="light"] .exec-summary-copy span,html[data-theme="light"] .exec-summary-copy button { color:#52657a; }
    html[data-theme="light"] .exec-funnel-table tr,html[data-theme="light"] .exec-row,html[data-theme="light"] .exec-summary-item { border-color:#d8e1eb; }
    @media (max-width:1280px) {
      .exec-kpis { grid-template-columns:repeat(2,minmax(0,1fr)); }
      .exec-pipeline { grid-column:span 2; }
      .exec-central,.exec-lower { grid-template-columns:1fr; }
    }
    @media (max-width:1023px) {
      #page-area-privada.executive-mode > section { padding:0; }
      #page-area-privada.executive-mode .private-dashboard-shell { display:block; border-radius:0; border-left:0; border-right:0; }
      .exec-dashboard { padding:1rem; }
    }
    @media (max-width:680px) {
      .exec-head { flex-direction:column; }
      .exec-head h3 { font-size:1.45rem; }
      .exec-head-actions { width:100%; }
      .exec-control { flex:1; }
      .exec-kpis { grid-template-columns:1fr; }
      .exec-pipeline { grid-column:auto; }
      .exec-distribution,.exec-funnel-grid { grid-template-columns:1fr; }
      .exec-donut { max-width:180px; }
      .exec-summary { grid-template-columns:1fr 1fr; }
      .exec-summary-item { justify-content:flex-start; padding:.5rem; border-bottom:1px solid rgba(128,158,200,.15); }
      .exec-summary-item:nth-child(2n) { border-right:0; }
      .exec-summary-item:nth-child(n+5) { border-bottom:0; }
    }

  
    .home-kpi-card { position:relative; display:flex; min-height:140px; flex-direction:column; padding-bottom:4.35rem !important; }
    .home-kpi-row { display:block; margin-top:.65rem; }
    .home-kpi-copy { min-width:0; max-width:calc(100% - 8.75rem); }
    .metric-action-link { position:absolute; right:1.25rem; bottom:1.25rem; display:inline-flex; width:8.25rem; min-height:42px; align-items:center; justify-content:center; padding:7px 10px; border:1px solid #b9c8d8; border-radius:9px; background:#fff; color:#0d4eae; font-size:10px; line-height:1.25; font-weight:800; text-align:center; }
    .metric-action-link:hover { border-color:#1b67d6; background:#e8f4ff; }
    @media (max-width:640px) {
      .home-kpi-card { min-height:auto; padding-bottom:1.25rem !important; }
      .home-kpi-row { display:flex; align-items:stretch; flex-direction:column; gap:.85rem; }
      .home-kpi-copy { max-width:none; }
      .metric-action-link { position:static; width:100%; min-height:44px; }
    }
    .favorite-toggle { display:inline-flex; align-items:center; justify-content:center; width:34px; height:34px; flex:0 0 34px; border:1px solid #b9c8d8; border-radius:9px; background:rgba(255,255,255,.96); color:#52677d; font-size:17px; line-height:1; box-shadow:0 2px 8px rgba(16,35,60,.12); }
    .favorite-toggle:hover { color:#e11d48; border-color:#fb7185; }
    .favorite-toggle.is-active { color:#be123c; border-color:#fb7185; background:#fff1f2; }
    .bg-navy .text-slate-400, .bg-navy-dark .text-slate-400, .from-navy .text-slate-400 { color:#cbd5e1 !important; }
    .bg-navy .text-slate-300, .bg-navy-dark .text-slate-300, .from-navy .text-slate-300 { color:#e2e8f0 !important; }
    html[data-theme="light"] .bg-white.border-slate-200, html[data-theme="light"] .bg-slate-50.border-slate-100 { border-color:#c7d3df !important; }
    html[data-theme="dark"] .metric-action-link { background:#172f50; border-color:#45627f; color:#d9eaff; }

    .private-field-label { display:block; margin-bottom:5px; color:var(--app-muted); font-size:10px; font-weight:800; text-transform:uppercase; }
    .private-field-input { width:100%; padding:10px 12px; border:1px solid var(--app-border); border-radius:10px; background:var(--app-surface); color:var(--app-text); font-size:12px; }
    .private-field-input:focus { outline:none; border-color:#1b67d6; box-shadow:0 0 0 3px rgba(27,103,214,.12); }
</style>

<style>
  /* ==========================================
     CENTRO DE COMUNICACIÓN INTERNA Y TRAZABILIDAD · DEMO WORDPRESS
     ========================================== */
  .comm-stat-card{border:1px solid var(--app-border);background:var(--app-surface);border-radius:1rem;padding:.95rem;box-shadow:0 8px 18px rgba(15,35,60,.05)}
  .comm-channel-badge{display:inline-flex;align-items:center;gap:.25rem;border-radius:999px;padding:.28rem .55rem;font-size:.62rem;font-weight:800;line-height:1;border:1px solid var(--app-border)}
  .comm-channel-ok{background:#eaf8f2;color:#167453;border-color:#bde9d7}.comm-channel-pending{background:#fff6df;color:#9a6500;border-color:#f5d98b}.comm-channel-off{background:#f1f5f9;color:#64748b;border-color:#d8e0e8}
  .comm-thread-card{border:1px solid var(--app-border);background:var(--app-surface);border-radius:1rem;padding:1rem;transition:.18s ease;box-shadow:0 8px 18px rgba(15,35,60,.04)}
  .comm-thread-card:hover{transform:translateY(-2px);border-color:#9ac3f8;box-shadow:0 14px 26px rgba(27,103,214,.1)}
  .comm-message{max-width:82%;border-radius:1rem;padding:.72rem .85rem;font-size:.75rem;line-height:1.5}
  .comm-message-system{background:var(--app-surface-soft);color:var(--app-text-muted);border:1px solid var(--app-border)}
  .comm-message-me{margin-left:auto;background:#1b67d6;color:white}.comm-message-other{background:var(--app-surface-soft);color:var(--app-text);border:1px solid var(--app-border)}
  .comm-trace-line{position:relative;padding-left:1.25rem}.comm-trace-line:before{content:'';position:absolute;left:.25rem;top:.2rem;bottom:-.85rem;width:1px;background:var(--app-border)}.comm-trace-line:last-child:before{display:none}.comm-trace-line:after{content:'';position:absolute;left:0;top:.34rem;width:.54rem;height:.54rem;border-radius:999px;background:#1b67d6;box-shadow:0 0 0 3px rgba(27,103,214,.14)}
  .comm-safe-banner{border:1px solid rgba(21,147,106,.26);background:linear-gradient(135deg,rgba(21,147,106,.1),rgba(27,103,214,.07));border-radius:1rem;padding:1rem}
  .comm-flow-step{display:flex;align-items:center;gap:.45rem;color:#64748b;font-size:.67rem;font-weight:800}.comm-flow-step:before{content:'';width:.55rem;height:.55rem;border-radius:999px;background:#cbd5e1}.comm-flow-step.done{color:#167453}.comm-flow-step.done:before{background:#15936a}.comm-flow-step.current{color:#1b67d6}.comm-flow-step.current:before{background:#1b67d6;box-shadow:0 0 0 3px rgba(27,103,214,.14)}
  .comm-table th{font-size:.62rem;text-transform:uppercase;letter-spacing:.05em;color:#64748b;font-weight:800;white-space:nowrap}.comm-table td{font-size:.72rem;color:var(--app-text-muted);vertical-align:top}
  .opportunity-accordion{border:1px solid var(--app-border);background:var(--app-surface);border-radius:1.25rem;box-shadow:0 8px 20px rgba(15,35,60,.05);overflow:hidden}
  .opportunity-accordion summary{list-style:none;cursor:pointer}
  .opportunity-accordion summary::-webkit-details-marker{display:none}
  .opportunity-accordion[open] .opportunity-accordion-chevron{transform:rotate(180deg)}
  .opportunity-mini-row{border:1px solid var(--app-border-soft);background:var(--app-surface-soft);border-radius:1rem;padding:.85rem}
  .opportunity-showcase{border:1px solid var(--app-border);background:var(--app-surface);border-radius:1.4rem;box-shadow:0 10px 24px rgba(15,35,60,.06);padding:1.1rem 1.1rem 1.2rem}
  .opportunity-showcase-rail{display:grid;grid-auto-flow:column;grid-auto-columns:minmax(220px,1fr);gap:1rem;overflow-x:auto;padding-bottom:.35rem;scrollbar-width:thin;scroll-snap-type:x proximity}
  .opportunity-showcase-rail::-webkit-scrollbar{height:.45rem}
  .opportunity-showcase-rail::-webkit-scrollbar-thumb{background:rgba(100,116,139,.28);border-radius:999px}
  .opportunity-showcase-card{scroll-snap-align:start;border:1px solid rgba(148,163,184,.18);background:linear-gradient(180deg,#152847 0%,#12223c 100%);border-radius:1.35rem;overflow:hidden;box-shadow:0 18px 34px rgba(7,18,33,.18);min-height:100%;color:#f8fbff}
  .opportunity-showcase-card-image{position:relative;aspect-ratio:16/10;background:#0f172a;overflow:hidden}
  .opportunity-showcase-card-image img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .opportunity-showcase-card-image:after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(12,24,44,.02) 0%,rgba(12,24,44,.28) 55%,rgba(12,24,44,.72) 100%)}
  .opportunity-showcase-badge{position:absolute;left:.9rem;bottom:.9rem;z-index:2;display:inline-flex;align-items:center;border-radius:999px;padding:.34rem .62rem;background:rgba(18,40,71,.82);color:#93c5fd;font-size:.63rem;font-weight:900;letter-spacing:.02em;text-transform:uppercase}
  .opportunity-showcase-score{position:absolute;right:.9rem;top:.9rem;z-index:2;display:inline-flex;align-items:center;border-radius:999px;padding:.38rem .64rem;background:rgba(255,255,255,.94);color:#0f2746;font-size:.64rem;font-weight:900;box-shadow:0 10px 20px rgba(15,23,42,.16)}
  .opportunity-showcase-body{padding:1rem}
  .opportunity-showcase-meta{display:flex;align-items:center;justify-content:space-between;gap:.8rem;color:#b7c6da;font-size:.69rem;font-weight:700}
  .opportunity-showcase-title{display:block;margin-top:.75rem;color:#f8fbff;font-size:1.05rem;line-height:1.2;font-weight:900}
  .opportunity-showcase-copy{display:block;margin-top:.45rem;color:#d8e3f1;font-size:.76rem;line-height:1.45}
  .opportunity-showcase-footer{display:flex;align-items:end;justify-content:space-between;gap:.9rem;margin-top:1rem;padding-top:.9rem;border-top:1px solid rgba(255,255,255,.09)}
  .opportunity-showcase-price{display:block;color:#f8fbff;font-size:1.3rem;line-height:1;font-weight:900}
  .opportunity-showcase-note{display:block;margin-top:.25rem;color:#9fb3ca;font-size:.68rem;font-weight:700}
  .opportunity-showcase-shell{display:flex;flex-direction:column;gap:1rem}
  .opportunity-showcase-toolbar{display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:.75rem}
  .opportunity-showcase-controls{display:flex;align-items:center;flex-wrap:wrap;justify-content:flex-end;gap:.55rem}
  .opportunity-showcase-arrow{min-width:3rem;height:2.6rem;padding:0 .9rem;border-radius:999px;border:1px solid var(--app-border);background:var(--app-surface-soft);color:var(--app-text);font-size:.86rem;font-weight:900;display:inline-flex;align-items:center;justify-content:center;gap:.35rem;transition:.18s ease}
  .opportunity-showcase-arrow:hover{transform:translateY(-1px);border-color:#9ac3f8;color:#1b67d6;background:#eff6ff}
  .opportunity-showcase-arrow-label{font-size:.68rem;letter-spacing:.02em}
  .opportunity-category-explorer{border:1px solid var(--app-border);background:var(--app-surface);border-radius:1.4rem;box-shadow:0 10px 24px rgba(15,35,60,.06);padding:1.1rem}
  .opportunity-category-explorer-toolbar{display:flex;flex-wrap:wrap;align-items:end;justify-content:space-between;gap:1rem}
  .opportunity-category-search{width:min(100%,20rem);padding:.82rem .95rem;border-radius:1rem;border:1px solid var(--app-border);background:var(--app-surface-soft);color:var(--app-text);font-size:.78rem;font-weight:700}
  .opportunity-category-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:1rem;margin-top:1rem}
  .opportunity-category-card{display:flex;flex-direction:column;overflow:hidden;border:1px solid rgba(148,163,184,.18);border-radius:1.2rem;background:linear-gradient(180deg,#152847 0%,#12223c 100%);box-shadow:0 16px 30px rgba(7,18,33,.14);color:#f8fbff}
  .opportunity-category-card.is-hidden{display:none}
  .opportunity-category-card-image{position:relative;aspect-ratio:16/10;background:#0f172a}
  .opportunity-category-card-image img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .opportunity-category-card-image:after{content:'';position:absolute;inset:0;background:linear-gradient(180deg,rgba(12,24,44,.04) 0%,rgba(12,24,44,.36) 52%,rgba(12,24,44,.78) 100%)}
  .opportunity-category-card-badge{position:absolute;left:.9rem;bottom:.9rem;z-index:2;display:inline-flex;align-items:center;border-radius:999px;padding:.34rem .62rem;background:rgba(18,40,71,.82);color:#bfdbfe;font-size:.62rem;font-weight:900;letter-spacing:.03em;text-transform:uppercase}
  .opportunity-category-card-count{position:absolute;right:.9rem;top:.9rem;z-index:2;display:inline-flex;align-items:center;border-radius:999px;padding:.36rem .62rem;background:rgba(255,255,255,.94);color:#0f2746;font-size:.64rem;font-weight:900}
  .opportunity-category-card-body{display:flex;flex:1;flex-direction:column;gap:.6rem;padding:1rem}
  .opportunity-category-card-title{display:block;font-size:1rem;line-height:1.2;font-weight:900;color:#f8fbff}
  .opportunity-category-card-copy{display:block;color:#d8e3f1;font-size:.76rem;line-height:1.45}
  .opportunity-category-card-footer{display:flex;align-items:center;justify-content:space-between;gap:.75rem;margin-top:auto;padding-top:.85rem;border-top:1px solid rgba(255,255,255,.09)}
  .opportunity-category-card-note{display:block;color:#9fb3ca;font-size:.68rem;font-weight:700}
  .opportunity-category-card-action{padding:.7rem .95rem;border-radius:1rem;border:1px solid rgba(255,255,255,.14);background:rgba(255,255,255,.08);color:#fff;font-size:.7rem;font-weight:900;letter-spacing:.02em}
  .opportunity-category-empty{margin-top:1rem;padding:1rem 1.1rem;border-radius:1rem;border:1px dashed var(--app-border);background:var(--app-surface-soft);color:var(--app-text-muted);font-size:.78rem;font-weight:700}
  @media (max-width:768px){.opportunity-showcase-rail{grid-auto-columns:minmax(260px,88vw)}}
  .private-calendar-grid{display:grid;grid-template-columns:repeat(7,minmax(0,1fr));gap:.35rem}
  .private-calendar-day{min-height:4.4rem;border:1px solid var(--app-border-soft);border-radius:.9rem;background:var(--app-surface-soft);padding:.45rem;display:flex;flex-direction:column;gap:.2rem}
  .private-calendar-day.is-today{border-color:#1b67d6;box-shadow:0 0 0 1px rgba(27,103,214,.18) inset}
  .private-calendar-day.is-active{background:linear-gradient(180deg,rgba(27,103,214,.08),rgba(21,147,106,.04))}
  .private-calendar-dot{width:.42rem;height:.42rem;border-radius:999px;display:inline-block}
  .private-calendar-dot.task{background:#1b67d6}
  .private-calendar-dot.alert{background:#d98b13}
  .private-calendar-dot.op{background:#15936a}
  html[data-theme="dark"] .comm-message-me{background:linear-gradient(135deg,#1b67d6,#5b3fd1)}
  html[data-theme="dark"] .comm-channel-ok{background:rgba(21,147,106,.16);border-color:rgba(21,147,106,.36);color:#8ee6c4}
  html[data-theme="dark"] .comm-channel-pending{background:rgba(217,139,19,.14);border-color:rgba(217,139,19,.34);color:#ffd98b}
  html[data-theme="dark"] nav .group>div>div{background:#10233c;border-color:#29415f}
  html[data-theme="dark"] nav .group>div>div a{color:#dbe7f5}
  html[data-theme="dark"] nav .group>div>div a:hover{background:#193859;color:#93c5fd}
  .territory-scroll-hint{margin-top:.3rem;color:var(--app-text-muted);font-size:.62rem;font-weight:700}
</style>

<?php wp_head(); ?>
</head>
<body <?php body_class('bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col'); ?>>
<?php wp_body_open(); ?>

  <!-- SISTEMA DE NOTIFICACIONES FLOTANTES (Toast System) -->
  <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm pointer-events-none"></div>

  <!-- CABECERA / MENÚ DE NAVEGACIÓN PRINCIPAL -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
      
      <!-- Logotipo -->
      <a href="#/inicio" class="flex items-center gap-3 group min-w-0">
      <img src="<?php echo esc_url($captacion_theme_uri . '/media/logo-compra-captacion.png'); ?>" alt="<?php echo esc_attr($captacion_brand_name); ?>" class="brand-logo-full group-hover:scale-[1.01] transition-transform">
      </a>

      <!-- Botón menú móvil -->
      <button id="menu-btn" class="lg:hidden flex items-center gap-2 px-3 py-2 border border-slate-200 rounded-lg text-navy font-bold hover:bg-slate-50">
        <span id="menu-icon-text">☰</span> Menú
      </button>

      <!-- Enlaces de Navegación multipágina -->
      <nav id="nav-menu" class="hidden lg:flex items-center gap-5 xl:gap-6 text-xs xl:text-sm font-bold text-slate-600">
        <a href="#/inicio" class="nav-link py-2 border-b-2 border-transparent transition-all hover:text-blue">Inicio</a>
        <a href="#/buscar-captaciones" class="nav-link py-2 border-b-2 border-transparent transition-all hover:text-blue">Busco Captación</a>
        <a href="#/ofrecer-captacion" class="nav-link py-2 border-b-2 border-transparent transition-all hover:text-blue">Ofrecer captación</a>
        <a href="#/marketplace" class="nav-link py-2 border-b-2 border-transparent transition-all hover:text-blue">Marketplace</a>

        <!-- Recursos: sección principal con Cómo funciona como submenú -->
        <div class="relative group">
          <a href="#/recursos" onclick="setResourceCategory('captacion')" class="nav-link inline-flex items-center gap-1 py-2 border-b-2 border-transparent transition-all hover:text-blue" aria-haspopup="true">
            Recursos
            <svg class="w-3 h-3 transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
            </svg>
          </a>
          <div class="invisible absolute left-0 top-full z-50 w-60 pt-3 opacity-0 translate-y-1 transition-all duration-200 group-hover:visible group-hover:opacity-100 group-hover:translate-y-0 group-focus-within:visible group-focus-within:opacity-100 group-focus-within:translate-y-0">
            <div class="rounded-2xl border border-slate-200 bg-white p-2 shadow-xl">
              <a href="#/recursos" onclick="setResourceCategory('captacion')" class="block rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 transition-colors hover:bg-blue-light hover:text-blue">Herramientas de captación</a>
              <a href="#/como-funciona" class="block rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 transition-colors hover:bg-blue-light hover:text-blue">Cómo funciona</a>
              <a href="#/planes" class="block rounded-xl px-3 py-2.5 text-xs font-bold text-slate-700 transition-colors hover:bg-blue-light hover:text-blue">Planes</a>
            </div>
          </div>
        </div>

        <a href="#/contacto" class="nav-link py-2 border-b-2 border-transparent transition-all hover:text-blue">Contacto</a>
    <button id="theme-toggle-desktop" type="button" onclick="toggleTheme()" class="theme-toggle-button ml-1" aria-label="Cambiar apariencia" aria-pressed="false" title="Cambiar tema">
      <span id="theme-toggle-desktop-icon" class="theme-toggle-icon" aria-hidden="true">☀</span>
    </button>
        <button type="button" onclick="openProfessionalAccess()" class="ml-1 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-extrabold tracking-wide hover:bg-navy-light transition-all shadow-sm hover:scale-105">
          <span aria-hidden="true">👤</span><span>Acceder</span>
        </button>
      </nav>
    </div>

    <!-- Menú desplegable móvil -->
    <div id="mobile-nav" class="hidden border-t border-slate-100 bg-white px-4 py-4 space-y-3 shadow-lg lg:hidden">
      <a href="#/inicio" class="block py-2 text-slate-700 font-bold hover:text-blue">Inicio</a>
      <a href="#/buscar-captaciones" class="block py-2 text-slate-700 font-bold hover:text-blue">Busco Captación</a>
      <a href="#/ofrecer-captacion" class="block py-2 text-slate-700 font-bold hover:text-blue">Ofrecer captación</a>
      <a href="#/marketplace" class="block py-2 text-slate-700 font-bold hover:text-blue">Marketplace</a>

      <!-- Recursos móvil: sección principal con Cómo funciona como submenú -->
      <details class="group rounded-xl border border-slate-100 bg-slate-50/70 px-3 py-1">
        <summary class="flex cursor-pointer list-none items-center justify-between py-2 text-slate-700 font-bold hover:text-blue">
          <span>Recursos</span>
          <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
          </svg>
        </summary>
        <div class="mb-2 ml-1 space-y-1 border-l border-slate-200 pl-3">
          <a href="#/recursos" onclick="setResourceCategory('captacion')" class="block py-2 text-sm font-semibold text-slate-600 hover:text-blue">Herramientas de captación</a>
          <a href="#/como-funciona" class="block py-2 text-sm font-semibold text-slate-600 hover:text-blue">Cómo funciona</a>
          <a href="#/planes" class="block py-2 text-sm font-semibold text-slate-600 hover:text-blue">Planes</a>
        </div>
      </details>

      <a href="#/contacto" class="block py-2 text-slate-700 font-bold hover:text-blue">Contacto</a>
  <button id="theme-toggle-mobile" type="button" onclick="toggleTheme()" class="theme-toggle-button" aria-label="Cambiar apariencia" aria-pressed="false" title="Cambiar tema">
    <span id="theme-toggle-mobile-icon" class="theme-toggle-icon" aria-hidden="true">☀</span>
  </button>
      <button type="button" onclick="openProfessionalAccess()" class="flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-navy text-white font-extrabold text-sm shadow-sm hover:bg-navy-light transition-colors">
        <span aria-hidden="true">👤</span><span>Acceder</span>
      </button>
    </div>
  </header>

  <!-- CONTENEDOR MULTIPÁGINA PRINCIPAL -->
  <main class="flex-grow">
    
    <!-- PÁGINA 1: INICIO -->
    <div id="page-inicio" class="page-section hidden">
      <!-- Hero principal -->
      <section class="relative overflow-hidden bg-gradient-to-br from-blue-light/50 via-white to-white py-14 md:py-20">
        <div class="absolute -right-40 -bottom-40 w-96 h-96 rounded-full bg-blue-light/70 blur-3xl pointer-events-none"></div>
        <div class="absolute -left-28 -top-36 w-80 h-80 rounded-full bg-green-light/50 blur-3xl pointer-events-none"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
            <div class="lg:col-span-7 space-y-6">
              <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-light text-blue text-xs font-bold uppercase tracking-wider">
                <span class="w-2 h-2 rounded-full bg-blue animate-pulse"></span>
                Red privada B2B para profesionales inmobiliarios
              </div>
              <h1 class="hero-title text-4xl sm:text-5xl lg:text-6xl font-black text-navy leading-[1.04] tracking-tight">
                Conecta <span class="text-blue">captaciones</span> y <span class="text-green">demanda activa</span> antes que tu competencia
              </h1>
              <p class="text-base sm:text-lg text-slate-600 max-w-2xl leading-relaxed">
                Publica oportunidades, encuentra colaboradores y accede a una red profesional diseñada para generar más negocio inmobiliario.
              </p>

              <!-- Dos conceptos clave -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-2xl">
                <div class="p-4 rounded-2xl bg-white/90 border border-blue/15 shadow-sm">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-light text-blue flex items-center justify-center font-black">C</div>
                    <div>
                      <span class="block text-[10px] uppercase tracking-[0.22em] text-blue font-black">Captación</span>
                      <p class="text-xs text-slate-500 mt-1">Publica oportunidades con la información justa para despertar interes profesional y desbloquea el detalle cuando exista una opcion real de cierre.</p>
                    </div>
                  </div>
                </div>
                <div class="p-4 rounded-2xl bg-white/90 border border-green/15 shadow-sm">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-light text-green flex items-center justify-center font-black">D</div>
                    <div>
                      <span class="block text-[10px] uppercase tracking-[0.22em] text-green font-black">Demanda</span>
                      <p class="text-xs text-slate-500 mt-1">Activa necesidades reales, detecta producto compatible y abre conversaciones mejor preparadas desde el inicio.</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex flex-wrap gap-4 pt-1">
                <button type="button" onclick="openProfessionalSubscriptionModal('hero-starter')" class="px-6 py-3.5 rounded-xl bg-blue text-white font-bold text-sm hover:bg-blue-dark hover:-translate-y-0.5 transition-all shadow-lg shadow-blue/25">Comenzar gratis</button>
                <a href="#/como-funciona" class="px-6 py-3.5 rounded-xl bg-white border border-slate-200 text-navy font-bold text-sm hover:border-slate-400 hover:bg-slate-50 transition-all">Ver cómo funciona</a>
              </div>
            </div>

            <!-- Ficha destacada dinámica -->
            <div class="lg:col-span-5 relative space-y-4 lg:pt-1">
              <div class="absolute -inset-4 bg-gradient-to-br from-blue/10 to-green/10 rounded-[32px] blur-2xl"></div>
              <div id="home-featured-card" class="relative">
                <!-- Render dinámico -->
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- KPIs vivos -->
      <section class="relative -mt-4 md:-mt-6 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 bg-white/95 backdrop-blur p-4 rounded-3xl border border-slate-200/80 shadow-xl">
            <div class="home-kpi-card p-5 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Captaciones visibles</span>
              <div class="home-kpi-row">
                <div class="home-kpi-copy">
                  <strong id="home-stat-properties" class="block text-4xl font-black text-navy">0</strong>
                  <span id="home-stat-properties-value" class="block mt-1 text-xs font-semibold text-slate-500">0 € en valor visible</span>
                </div>
                <a href="#/marketplace" class="metric-action-link">Ver captaciones visibles</a>
              </div>
            </div>
            <div class="home-kpi-card p-5 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Demandas visibles</span>
              <div class="home-kpi-row">
                <div class="home-kpi-copy">
                  <strong id="home-stat-needs" class="block text-4xl font-black text-navy">0</strong>
                  <span id="home-stat-needs-value" class="block mt-1 text-xs font-semibold text-slate-500">0 € en demanda activa</span>
                </div>
                <a href="#/buscar-captaciones" class="metric-action-link">Ver demandas visibles</a>
              </div>
            </div>
            <div class="home-kpi-card p-5 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Zonas con cobertura</span>
              <div class="home-kpi-row"><div class="home-kpi-copy"><strong id="home-stat-zones" class="block text-4xl font-black text-navy">0</strong></div><button type="button" onclick="scrollToCoverageMap(event)" class="metric-action-link">Ver mapa de cobertura</button></div>
            </div>
            <div class="home-kpi-card p-5 rounded-2xl bg-slate-50 border border-slate-100">
              <span class="text-[10px] font-black uppercase tracking-[0.16em] text-slate-400">Coincidencias de Ventas</span>
              <div class="home-kpi-row"><div class="home-kpi-copy"><strong id="home-stat-sales-matches" class="block text-4xl font-black text-green">0</strong><span id="home-stat-sales-value" class="block mt-1 text-xs font-semibold text-slate-500">0 € estimados</span></div><a href="#/coincidencias-ventas" class="metric-action-link">Ver coincidencias</a></div>
            </div>
          </div>
          <p class="mt-3 text-[10px] text-slate-400 text-right">Indicadores orientados a validar el valor del recorrido y la estructura del producto. La version final conectara estas metricas con backend y persistencia reales.</p>
        </div>
      </section>

      <!-- Valores -->
      <section class="py-14 bg-white border-b border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-3xl">
            <span class="text-xs font-bold tracking-widest text-blue uppercase">Valores</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-navy tracking-tight mt-2">No necesitas mas anuncios. Necesitas mejores oportunidades.</h2>
            <p class="text-sm text-slate-500 mt-3 leading-relaxed">Captacion.app organiza la colaboración entre profesionales para conectar captaciones, demanda real e inversores sin exponer información sensible antes de tiempo.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-8">
            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
              <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase tracking-[0.16em]">1. Menos exposicion innecesaria</span>
              <h3 class="mt-4 text-xl font-black text-navy">Comparte lo necesario. Protege lo importante.</h3>
              <p class="mt-3 text-sm leading-relaxed text-slate-500">Presenta cada oportunidad con la información suficiente para despertar interes y reserva los datos sensibles para cuando exista un encaje profesional real.</p>
            </article>
            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
              <span class="inline-flex px-3 py-1 rounded-full bg-green-light text-green text-[10px] font-black uppercase tracking-[0.16em]">2. Mejor contexto comercial</span>
              <h3 class="mt-4 text-xl font-black text-navy">Menos contactos sin recorrido. Mas conversaciones con potencial.</h3>
              <p class="mt-3 text-sm leading-relaxed text-slate-500">Captacion.app conecta oferta y demanda con criterios claros para que los profesionales dediquen su tiempo a oportunidades con mayor encaje comercial.</p>
            </article>
            <article class="rounded-3xl border border-slate-200 bg-slate-50 p-6 shadow-sm">
              <span class="inline-flex px-3 py-1 rounded-full bg-amber-light text-amber text-[10px] font-black uppercase tracking-[0.16em]">3. Operativa mas ordenada</span>
              <h3 class="mt-4 text-xl font-black text-navy">Menos mensajes perdidos. Mas oportunidades bajo control.</h3>
              <p class="mt-3 text-sm leading-relaxed text-slate-500">Reune solicitudes, tareas, estados y acuerdos en un unico entorno para que cada colaboración tenga continuidad y un seguimiento claro.</p>
            </article>
          </div>
        </div>
      </section>

      <!-- Mapa España -->
      <section id="mapa-cobertura" class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-8">
            <div class="max-w-3xl">
              <span class="text-xs font-bold tracking-widest text-blue uppercase">Radar nacional de oportunidades</span>
              <h2 class="text-3xl sm:text-4xl font-extrabold text-navy tracking-tight mt-2">Mapa interactivo de captaciones y demandas</h2>
              <p class="text-sm text-slate-500 mt-3">Consulta la distribucion aproximada de oportunidades y demanda activa. Las ubicaciones exactas siguen protegidas para no convertir la plataforma en un volcado de datos sensibles.</p>
            </div>
            <div class="flex flex-wrap gap-2" aria-label="Filtros del mapa">
              <button id="map-filter-all" onclick="setHomeMapMode('all')" class="map-filter-active px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold transition-all">Todas</button>
              <button id="map-filter-properties" onclick="setHomeMapMode('properties')" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:border-blue transition-all">● Captaciones</button>
              <button id="map-filter-needs" onclick="setHomeMapMode('needs')" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-600 hover:border-green transition-all">● Demandas</button>
            </div>
          </div>
          <div class="mb-5 flex flex-col xl:flex-row xl:items-end gap-3 rounded-2xl border border-slate-200 bg-slate-50/70 p-3">
            <div class="w-full xl:max-w-xs">
              <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1">Buscar por Código Postal en el mapa</label>
              <div class="flex gap-2">
                <input id="home-map-postal-filter" type="search" inputmode="numeric" maxlength="5" onkeydown="if(event.key === 'Enter'){ event.preventDefault(); applyHomeMapPostalFilter(); }" placeholder="Ej.: 32002" class="min-w-0 flex-1 px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl focus:outline-none focus:ring-2 focus:ring-blue/20 bg-white" />
                <button onclick="applyHomeMapPostalFilter()" class="px-3 py-2 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-bold transition-all">Buscar CP</button>
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <button onclick="activateHomeAreaDraw()" class="px-3 py-2 rounded-xl border border-blue/30 bg-white text-blue text-xs font-bold hover:bg-blue-light transition-all">▱ Dibujar zona</button>
              <button onclick="clearHomeMapArea()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-slate-600 text-xs font-bold hover:text-navy hover:border-slate-300 transition-all">✕ Limpiar zona</button>
            </div>
            <p id="home-map-area-status" class="text-[11px] text-slate-500 leading-relaxed xl:ml-auto xl:max-w-sm">Sin zona dibujada. Se muestran las oportunidades compatibles con los filtros activos del mapa.</p>
          </div>
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <div class="lg:col-span-9 rounded-3xl overflow-hidden border border-slate-200 shadow-sm bg-slate-100">
              <div id="home-map" role="application" aria-label="Mapa interactivo de oportunidades inmobiliarias en España"></div>
            </div>
            <aside class="lg:col-span-3 rounded-3xl bg-navy text-white p-6 flex flex-col justify-between">
              <div>
                <span class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-light">Lectura del mapa</span>
                <h3 class="font-extrabold text-xl mt-2">Cobertura territorial</h3>
                <p class="text-xs text-slate-300 mt-3 leading-relaxed">Cada punto representa una captación o una demanda activa. La geolocalización es orientativa para proteger datos sensibles del expediente.</p>
              </div>
              <div class="mt-8 space-y-3 text-xs">
                <div class="flex items-center justify-between gap-4"><span class="text-slate-300">Captaciones mapeadas</span><strong id="home-map-properties" class="text-blue-light">0</strong></div>
                <div class="flex items-center justify-between gap-4"><span class="text-slate-300">Demandas mapeadas</span><strong id="home-map-needs" class="text-green-light">0</strong></div>
                <div class="flex items-center justify-between gap-4 border-t border-white/10 pt-3"><span class="text-slate-300">Zonas con actividad</span><strong id="home-map-zones">0</strong></div>
              </div>
            </aside>
          </div>
        </div>
      </section>

      <!-- Últimas publicaciones -->
      <section class="py-16 bg-slate-50 border-y border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-14">
          <div>
            <div class="mb-6">
              <span class="text-xs font-bold tracking-widest text-blue uppercase">Producto compartido</span>
              <h2 class="text-2xl sm:text-3xl font-extrabold text-navy mt-2">Últimas captaciones publicadas</h2>
            </div>
            <div class="home-carousel-shell">
              <button type="button" onclick="scrollHomeCarousel('home-latest-properties', -1)" class="home-carousel-nav home-carousel-nav-prev" aria-label="Ver captaciones anteriores">‹</button>
              <div id="home-latest-properties" class="home-carousel-track scrollbar-hidden" aria-label="Carrusel de últimas captaciones"></div>
              <button type="button" onclick="scrollHomeCarousel('home-latest-properties', 1)" class="home-carousel-nav home-carousel-nav-next" aria-label="Ver más captaciones">›</button>
            </div>
            <div class="mt-5 flex justify-center">
              <a href="#/marketplace" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-bold shadow-md transition-all">Ir al Marketplace →</a>
            </div>
          </div>

          <div>
            <div class="mb-6">
              <span class="text-xs font-bold tracking-widest text-green uppercase">Compradores cualificados</span>
              <h2 class="text-2xl sm:text-3xl font-extrabold text-navy mt-2">Últimas demandas publicadas</h2>
            </div>
            <div class="home-carousel-shell">
              <button type="button" onclick="scrollHomeCarousel('home-latest-needs', -1)" class="home-carousel-nav home-carousel-nav-prev" aria-label="Ver demandas anteriores">‹</button>
              <div id="home-latest-needs" class="home-carousel-track scrollbar-hidden" aria-label="Carrusel de últimas demandas"></div>
              <button type="button" onclick="scrollHomeCarousel('home-latest-needs', 1)" class="home-carousel-nav home-carousel-nav-next" aria-label="Ver más demandas">›</button>
            </div>
            <div class="mt-5 flex justify-center">
              <a href="#/buscar-captaciones" class="inline-flex items-center justify-center px-5 py-3 rounded-xl bg-navy hover:bg-navy-light text-white text-xs font-bold shadow-md transition-all">Ir a Buscar captaciones →</a>
            </div>
          </div>
        </div>
      </section>

      <!-- Registro / login -->
      <section class="py-16 md:py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            <div class="lg:col-span-5 rounded-3xl bg-gradient-to-br from-navy to-navy-light p-8 sm:p-10 text-white flex flex-col justify-between">
              <div>
                <span class="text-xs font-bold tracking-widest text-blue-light uppercase">Acceso profesional</span>
                <h2 class="text-3xl font-black mt-3 leading-tight">Activa un acceso profesional pensado para colaborar con más criterio</h2>
                <p class="text-sm text-slate-300 mt-4 leading-relaxed">El objetivo no es solo entrar en una plataforma, sino disponer de un entorno donde captar, filtrar, solicitar información y avanzar oportunidades con mejor orden comercial.</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-3 mt-8 text-xs">
                <div class="p-3 rounded-xl bg-white/5 border border-white/10">✓ Solicitudes con acceso gradual a la información</div>
                <div class="p-3 rounded-xl bg-white/5 border border-white/10">✓ Demandas, captaciones y coincidencias en un mismo flujo</div>
                <div class="p-3 rounded-xl bg-white/5 border border-white/10">✓ Seguimiento más claro de conversaciones y colaboraciones</div>
              </div>
            </div>

            <div class="lg:col-span-7 bg-slate-50 rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
              <div id="auth-guest-panel">
                <div class="h-full flex flex-col justify-center rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                  <span class="inline-flex self-start px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase tracking-wider">Suscripción para profesional</span>
                  <h3 class="mt-4 text-2xl font-black text-navy">Empieza con los datos imprescindibles</h3>
                  <p class="mt-3 text-sm leading-relaxed text-slate-500">Crea tu cuenta con nombre, correo, país, teléfono principal y contraseña. La agencia y tu zona de trabajo se completan después desde el perfil profesional.</p>
                  <form id="inline-professional-form" onsubmit="handleInlineProfessionalRegistration(event)" class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="block"><span class="block text-[11px] font-bold text-slate-500 mb-1">Nombre y apellidos *</span><input id="inline-register-name" type="text" required minlength="3" autocomplete="name" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm" /></label>
                    <label class="block"><span class="block text-[11px] font-bold text-slate-500 mb-1">Correo electrónico *</span><input id="inline-register-email" type="email" required autocomplete="email" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm" /></label>
                    <label class="block"><span class="block text-[11px] font-bold text-slate-500 mb-1">País *</span><select id="inline-register-country" autocomplete="tel-country-code" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm bg-white"><option value="+34" selected>España (+34)</option><option value="+351">Portugal (+351)</option><option value="+33">Francia (+33)</option><option value="+39">Italia (+39)</option><option value="+49">Alemania (+49)</option><option value="+44">Reino Unido (+44)</option><option value="+1">Estados Unidos/Canadá (+1)</option><option value="+52">México (+52)</option><option value="+54">Argentina (+54)</option><option value="+56">Chile (+56)</option><option value="+57">Colombia (+57)</option><option value="+51">Perú (+51)</option><option value="+58">Venezuela (+58)</option><option value="+593">Ecuador (+593)</option><option value="+598">Uruguay (+598)</option><option value="+595">Paraguay (+595)</option><option value="+55">Brasil (+55)</option><option value="+212">Marruecos (+212)</option></select></label>
                    <label class="block"><span class="block text-[11px] font-bold text-slate-500 mb-1">Número de contacto *</span><input id="inline-register-phone" type="tel" required autocomplete="tel-national" inputmode="tel" placeholder="600 000 000" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm" /></label>
                    <label class="block sm:col-span-2"><span class="block text-[11px] font-bold text-slate-500 mb-1">Contraseña *</span><div class="relative"><input id="inline-register-password" type="password" required minlength="8" autocomplete="new-password" class="w-full px-3 py-2.5 pr-20 rounded-xl border border-slate-200 text-sm" /><button type="button" onclick="togglePasswordVisibility('inline-register-password', this)" class="absolute inset-y-1 right-1 px-3 rounded-lg text-[10px] font-black text-blue hover:bg-blue-light">Mostrar</button></div></label>
                    <label class="sm:col-span-2 flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-600 cursor-pointer"><input id="inline-register-privacy" type="checkbox" required class="mt-0.5 h-5 w-5 shrink-0" /><span>He leído y acepto la <a href="#/privacidad" class="legal-link">Política de privacidad</a>.</span></label>
                    <label class="sm:col-span-2 flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-600 cursor-pointer"><input id="inline-register-marketing" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0" /><span>Quiero recibir novedades y comunicaciones comerciales de Captacion.app. Opcional y revocable.</span></label>
                    <p id="inline-register-error" class="hidden sm:col-span-2 rounded-xl bg-red-50 border border-red-100 px-3 py-2 text-xs text-red-700" role="alert"></p>
                    <button id="inline-register-submit" class="sm:col-span-2 w-full py-3.5 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md">Crear cuenta profesional</button>
                  </form>
                </div>
              </div>

              <div id="auth-session-panel" class="hidden h-full flex flex-col justify-between">
                <div>
                  <span class="inline-flex px-3 py-1 rounded-full bg-green-light text-green text-[10px] font-black uppercase">Sesión activa</span>
                  <h3 id="auth-session-name" class="text-2xl font-black text-navy mt-4">Bienvenido</h3>
                  <p id="auth-session-agency" class="text-sm text-slate-500 mt-1"></p>
                  <p class="text-xs text-slate-500 mt-5 leading-relaxed">Ya puedes acceder al área privada, revisar tus captaciones y continuar publicando oportunidades.</p>
                </div>
                <div class="flex flex-wrap gap-3 mt-8">
                  <a href="#/area-privada" class="px-5 py-3 rounded-xl bg-blue text-white text-xs font-black shadow-md">Ir al área privada</a>
                  <button onclick="logoutDemo()" class="px-5 py-3 rounded-xl border border-slate-200 bg-white text-slate-600 text-xs font-black">Cerrar sesión</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Propuesta de doble dirección comercial -->
      <section class="py-16 md:py-20 bg-slate-50 border-t border-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="text-center max-w-3xl mx-auto mb-12 space-y-4">
            <span class="text-xs font-bold tracking-widest text-blue uppercase">Doble dirección comercial</span>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-navy tracking-tight">Un mismo ecosistema para captar y vender mejor</h2>
            <p class="text-slate-500">La plataforma convierte el producto disponible y la demanda cualificada en dos flujos coordinados de colaboración profesional.</p>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-8 rounded-3xl bg-white border border-slate-200 shadow-sm">
              <div class="w-12 h-12 rounded-xl bg-blue-light text-blue flex items-center justify-center font-black text-xl">&#128269;</div>
              <h3 class="text-xl font-bold text-navy mt-5">Encuentra producto para tus compradores</h3>
              <p class="text-slate-500 text-sm leading-relaxed mt-3">Publica necesidades específicas y detecta captaciones compatibles filtradas por zona, tipo de inmueble, urgencia y condiciones de colaboración.</p>
              <a href="#/buscar-captaciones" class="mt-6 inline-flex text-xs font-black text-blue">Consultar demandas y oportunidades →</a>
            </div>
            <div class="p-8 rounded-3xl bg-white border border-slate-200 shadow-sm">
              <div class="w-12 h-12 rounded-xl bg-green-light text-green flex items-center justify-center font-black text-xl">&#128188;</div>
              <h3 class="text-xl font-bold text-navy mt-5">Monetiza captaciones que no puedes gestionar</h3>
              <p class="text-slate-500 text-sm leading-relaxed mt-3">Comparte activos de forma confidencial, controla los accesos y encuentra colaboradores con comprador o capacidad operativa en la zona.</p>
              <a href="#/ofrecer-captacion" class="mt-6 inline-flex text-xs font-black text-blue">Publicar oportunidad confidencial →</a>
            </div>
          </div>
        </div>
      </section>
    </div>

    <!-- PÁGINA 2: BUSCAR CAPTACIONES (Demandas de Búsqueda Activas) -->
    <div id="page-buscar-captaciones" class="page-section hidden">
      <section class="py-12 bg-slate-100/50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          
          <div class="mb-8">
            <h2 class="text-3xl font-black text-navy">Demandas y Necesidades de Búsqueda Activas</h2>
            <p class="text-sm text-slate-500 mt-2 max-w-4xl">
              ¿Tienes un comprador solvente en tu cartera pero careces del inmueble idóneo en tu zona o está fuera de tu radio de acción? 
              Publica aquí tu necesidad específica de búsqueda para que otros profesionales de nuestra de red que posean el activo puedan contactar contigo y cerrar una operación inmobiliaria compartida.
            </p>
          </div>

          <!-- MINI-DASHBOARD INTERACTIVO DE DEMANDAS (Click-To-Filter) -->
          <div id="needs-dashboard" class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <!-- Se carga dinámicamente con JavaScript -->
          </div>

          <!-- FORMULARIO PARA PUBLICAR NUEVA NECESIDAD CON CAMPOS DIN?MICOS, GEOGR?FICOS Y LOCALIDAD -->
          <div class="bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-md mb-12">
            <h3 class="text-lg font-bold text-navy border-b border-slate-100 pb-3 mb-4 flex items-center gap-2">
              <span>+</span> Publicar una nueva necesidad de búsqueda (Comprador Activo)
            </h3>
            <form id="need-publication-form" onsubmit="handleNewNeed(event)" class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Título de la búsqueda *</label>
                  <input type="text" id="need-pub-title" required minlength="8" placeholder="Ej: Busco local adaptado a restauración" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de Inmueble Buscado *</label>
                  <select id="need-pub-type" required onchange="updatePropertyFormDynamics('need')" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="Piso">Piso</option>
                    <option value="Casa / chalet">Casa / chalet</option>
                    <option value="Ático">Ático</option>
                    <option value="Dúplex">Dúplex</option>
                    <option value="Apartamento">Apartamento</option>
                    <option value="Estudio">Estudio</option>
                    <option value="Finca rústica con vivienda">Finca rústica con vivienda</option>
                    <option value="Edificio residencial">Edificio residencial</option>
                    <option value="Local comercial">Local comercial</option>
                    <option value="Nave">Nave</option>
                    <option value="Oficina">Oficina</option>
                    <option value="Terreno / solar">Terreno / solar</option>
                    <option value="Garaje">Garaje</option>
                    <option value="Trastero">Trastero</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de Operación *</label>
                  <select id="need-pub-operation" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="Venta">Venta</option>
                    <option value="Alquiler">Alquiler</option>
                    <option value="Alquiler con Opción a Compra">Alquiler con Opción a Compra</option>
                    <option value="Otra necesidad inmobiliaria">Otra necesidad inmobiliaria</option>
                  </select>
                </div>
              </div>

              <div class="space-y-3">
                <div>
                  <span class="block text-sm font-black text-navy">Ubicación del inmueble</span>
                  <p class="text-xs text-slate-500 mt-1">Define el encaje territorial de la búsqueda para recibir oportunidades compatibles. La dirección exacta no es necesaria en esta fase.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Comunidad o ciudad autónoma *</label>
                  <select id="need-pub-ccaa-sel" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="">Cargando CCAA...</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Provincia *</label>
                  <select id="need-pub-province-sel" required disabled class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white disabled:bg-slate-50 disabled:text-slate-400">
                    <option value="">Selecciona una comunidad autónoma</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Municipio *</label>
                  <input id="need-pub-municipality-sel" type="text" list="need-pub-municipality-list" required disabled autocomplete="off" placeholder="Selecciona o busca un municipio" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white disabled:bg-slate-50 disabled:text-slate-400" />
                  <datalist id="need-pub-municipality-list"></datalist>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Código Postal *</label>
                  <input type="text" id="need-pub-postal-code" required inputmode="numeric" pattern="[0-9]{5}" maxlength="5" placeholder="Ej: 32002" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Localidad o Barrio (Opcional)</label>
                  <input type="text" id="need-pub-locality" placeholder="Ej: Barrio de El Couto, Recoletos" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                </div>
              </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Habitaciones mínimas *</label>
                  <input type="number" id="need-pub-bedrooms" required min="0" step="1" value="0" placeholder="Ej: 3" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Baños mínimos *</label>
                  <input type="number" id="need-pub-bathrooms" required min="0" step="1" value="0" placeholder="Ej: 2" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Superficie mínima requerida (m²) *</label>
                  <input type="number" id="need-pub-surface" required min="1" step="1" placeholder="Ej: 85" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Presupuesto máximo / orientativo (€) *</label>
                  <input type="number" id="need-pub-budget" required min="1" placeholder="Ej: 300000" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de Comprador *</label>
                  <select id="need-pub-buyer-type" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="Profesional">Profesional</option>
                    <option value="Particular">Particular</option>
                    <option value="Inversor">Inversor</option>
                    <option value="Otros">Otros</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Urgencia de búsqueda *</label>
                  <select id="need-pub-urgency" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="Alta">Alta</option>
                    <option value="Media">Media</option>
                    <option value="Baja">Baja</option>
                    <option value="Sin urgencia definida">Sin urgencia definida</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Estado Financiero *</label>
                  <select id="need-pub-funding" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="Fondos propios / Al contado">Fondos propios / Al contado</option>
                    <option value="Financiación preaprobada">Financiación preaprobada</option>
                    <option value="Sujeto a hipoteca">Sujeto a hipoteca</option>
                    <option value="No requiere">No requiere</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Comisión aceptada / colaboración prevista *</label>
                  <select id="need-pub-fee" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="50/50">Reparto 50/50</option>
                    <option value="A consultar">A consultar</option>
                  </select>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Condiciones aceptadas del inmueble *</label>
                  <select id="need-pub-condition" required multiple size="4" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white"></select>
                  <p class="text-[11px] text-slate-500 mt-1">Puedes seleccionar una o varias condiciones.</p>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de captación aceptada *</label>
                  <select id="need-pub-mandate" required multiple size="4" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="Con exclusividad">Con exclusividad</option>
                    <option value="Encargo de agente único">Encargo de agente único</option>
                    <option value="Exclusiva compartida">Exclusiva compartida</option>
                    <option value="Nota de encargo abierta">Nota de encargo abierta</option>
                    <option value="Sin exclusiva formalizada">Sin exclusiva formalizada</option>
                    <option value="Pendiente de confirmar">Pendiente de confirmar</option>
                    <option value="Cualquiera">Cualquiera</option>
                  </select>
                </div>
                <div>
                  <label class="block text-xs font-bold text-slate-500 mb-1">Nivel mínimo de documentación requerido *</label>
                  <select id="need-pub-docs" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                    <option value="Nota simple únicamente">Nota simple únicamente</option>
                    <option value="Nota simple + planos">Nota simple + planos</option>
                    <option value="Nota simple + certificado energético">Nota simple + certificado energético</option>
                    <option value="Nota simple + planos + certificado energético">Nota simple + planos + certificado energético</option>
                    <option value="Expediente jurídico completo">Expediente jurídico completo</option>
                    <option value="Tasación disponible">Tasación disponible</option>
                    <option value="Expediente jurídico completo + tasación">Expediente jurídico completo + tasación</option>
                    <option value="No califica">No califica</option>
                  </select>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Descripción de la necesidad *</label>
                <textarea id="need-pub-desc" required minlength="30" rows="3" placeholder="Describe los requisitos esenciales del cliente (ej. altura, salida de humos, acceso de camiones, etc.)" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20"></textarea>
              </div>

              <label class="legal-consent-box flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
                <input id="need-pub-compliance" type="checkbox" required class="mt-0.5" />
                <span>Declaro que la demanda es lícita, exacta y profesional; que no incluye datos personales innecesarios; y que acepto las <a href="#/normas-publicacion" class="legal-link">Normas de publicación</a>. *</span>
              </label>
              <div class="flex justify-end pt-2">
                <button type="submit" class="px-6 py-2.5 bg-blue text-white rounded-xl text-xs font-bold hover:bg-blue-dark shadow-md transition-all">
                  Publicar Requisito de Búsqueda
                </button>
              </div>
            </form>
          </div>

          <!-- PANEL DE FILTRADO DIN?MICO DE NECESIDADES -->
          <div class="bg-white p-4 sm:p-6 rounded-2xl border border-slate-200 shadow-sm mb-6 space-y-4">
            <div class="flex flex-col lg:flex-row justify-between lg:items-center gap-4">
              <span class="text-sm font-extrabold text-navy">Filtros de demandas activas</span>
              
              <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl" role="group" aria-label="Modo de visualización de demandas">
                <button onclick="setNeedsLayout('mapa')" id="layout-mapa-btn" class="px-3 py-1.5 text-xs font-bold rounded-lg text-slate-500 hover:text-navy transition-all">Mapa</button>
                <button onclick="setNeedsLayout('bloque')" id="layout-bloque-btn" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-white text-navy shadow-sm transition-all">▦ Bloque</button>
                <button onclick="setNeedsLayout('lista')" id="layout-lista-btn" class="px-3 py-1.5 text-xs font-bold rounded-lg text-slate-500 hover:text-navy transition-all">☰ Lista</button>
              </div>
            </div>

            <!-- FILTROS GEOGR?FICOS DIN?MICOS Y CRITERIOS -->
            <div class="grid grid-cols-1 md:grid-cols-8 gap-3 pt-2">
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Tiempo publicación</label>
                <select id="need-filter-time" onchange="filterNeeds()" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs bg-white">
                  <option value="newest">Más recientes primero</option>
                  <option value="oldest">Más antiguos primero</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Categoría</label>
                <select id="need-filter-type" onchange="filterNeeds()" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs bg-white">
                  <option value="all">Todas las categorías</option>
                  <option value="Piso">Piso</option>
                  <option value="Casa / chalet">Casa / chalet</option>
                  <option value="Ático">Ático</option>
                  <option value="Dúplex">Dúplex</option>
                  <option value="Apartamento">Apartamento</option>
                  <option value="Estudio">Estudio</option>
                  <option value="Finca rústica con vivienda">Finca rústica con vivienda</option>
                  <option value="Edificio residencial">Edificio residencial</option>
                  <option value="Local comercial">Local comercial</option>
                  <option value="Nave">Nave</option>
                  <option value="Oficina">Oficina</option>
                  <option value="Terreno / solar">Terreno / solar</option>
                  <option value="Garaje">Garaje</option>
                  <option value="Trastero">Trastero</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Filtrar CCAA</label>
                <select id="need-filter-ccaa" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs bg-white">
                  <option value="all">Todas las CCAA</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Provincia</label>
                <select id="need-filter-province" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs bg-white">
                  <option value="all">Todas las provincias</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Municipio</label>
                <select id="need-filter-municipality" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs bg-white">
                  <option value="all">Todos los municipios</option>
                </select>
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Código Postal</label>
                <input type="search" id="need-filter-postal-code" oninput="filterNeeds()" inputmode="numeric" maxlength="5" placeholder="Ej: 32002" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs focus:ring-1 focus:ring-blue" />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Localidad (Búsqueda libre)</label>
                <input type="text" id="need-filter-locality" onkeyup="filterNeeds()" placeholder="Ej: Recoletos" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs focus:ring-1 focus:ring-blue" />
              </div>
              <div>
                <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Escala de precios</label>
                <select id="need-filter-price" onchange="filterNeeds()" class="w-full px-2 py-2 rounded-lg border border-slate-200 text-xs bg-white">
                  <option value="all">Cualquier presupuesto</option>
                  <option value="low">Menor de 150.000 €</option>
                  <option value="mid">150.000 € - 500.000 €</option>
                  <option value="high">Más de 500.000 €</option>
                </select>
              </div>
            </div>
            
            <div class="flex justify-end">
              <button onclick="clearAdvancedFilters()" class="px-4 py-2 rounded-xl text-xs font-bold text-slate-500 hover:text-navy hover:bg-slate-100 transition-all">
                Restablecer filtros
              </button>
            </div>
          </div>

          <div id="needs-map-panel" class="hidden mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4"><p class="text-xs text-slate-500">Vista mapa aproximada. Las ubicaciones exactas de compradores y profesionales permanecen protegidas.</p></div>
            <div id="needs-map" role="application" aria-label="Mapa aproximado de demandas inmobiliarias activas"></div>
          </div>

          <div id="needs-accordion-sections" class="space-y-4 mb-8"></div>

          <!-- CONTENEDOR DE LA LISTA DE DEMANDAS -->
          <div id="needs-list-container">
            <!-- Se renderiza dinámicamente según el formato de visualización -->
          </div>

          <div class="mt-10 rounded-lg border border-blue/20 bg-blue-light/40 p-6 text-center">
            <h3 class="text-xl font-black text-navy">¿Buscas una propiedad para un cliente o inversor?</h3>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-600">Publica tu necesidad y permite que otros profesionales te ayuden a encontrar oportunidades compatibles.</p>
            <button type="button" onclick="scrollToPlatformForm('need-publication-form')" class="mt-5 px-6 py-3 rounded-xl bg-blue text-white text-xs font-black shadow-sm">Publicar búsqueda</button>
          </div>
        </div>
      </section>
    </div>

    <!-- PÁGINA 3: OFRECER CAPTACIÓN -->
    <div id="page-ofrecer-captacion" class="page-section hidden">
      <section class="py-12 max-w-4xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10 space-y-3">
          <h2 class="text-3xl font-black text-navy tracking-tight">Monetiza tus captaciones inmobiliarias</h2>
          <p class="text-slate-500">Publica una oportunidad, define las condiciones de colaboración y conecta con profesionales verificados.</p>
        </div>

        <div class="bg-white p-6 sm:p-10 rounded-3xl border border-slate-200 shadow-xl">
          <form id="offer-publication-form" onsubmit="handleNewOffer(event)" class="space-y-6">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
              <h3 class="text-lg font-bold text-navy">Detalles Técnicos del Expediente B2B</h3>
              <!-- Asistente IA para redacción -->
              <button type="button" onclick="generateAIDescription()" id="ai-gen-btn" class="px-3.5 py-2 rounded-xl bg-gradient-to-r from-blue to-blue-dark text-white text-xs font-extrabold shadow-md hover:scale-105 transition-all flex items-center gap-1.5">
                <span>✨</span> Redactar con IA
              </button>
            </div>
            
            <div class="space-y-3">
              <div>
                <span class="block text-sm font-black text-navy">Ubicación del inmueble</span>
                <p class="text-xs text-slate-500 mt-1">Define el encaje territorial de la captación y limita la visibilidad pública al nivel administrativo que corresponde. La dirección exacta no será visible públicamente.</p>
              </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de Inmueble *</label>
                <select id="offer-type" required onchange="refreshOfferDefaultImagePreview();updatePropertyFormDynamics('offer')" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20">
                  <option value="Piso">Piso</option>
                  <option value="Casa / chalet">Casa / chalet</option>
                  <option value="Ático">Ático</option>
                  <option value="Dúplex">Dúplex</option>
                  <option value="Apartamento">Apartamento</option>
                  <option value="Estudio">Estudio</option>
                  <option value="Finca rústica con vivienda">Finca rústica con vivienda</option>
                  <option value="Edificio residencial">Edificio residencial</option>
                  <option value="Local comercial">Local comercial</option>
                  <option value="Nave">Nave</option>
                  <option value="Oficina">Oficina</option>
                  <option value="Terreno / solar">Terreno / solar</option>
                  <option value="Garaje">Garaje</option>
                  <option value="Trastero">Trastero</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Comunidad o ciudad autónoma *</label>
                <select id="offer-ccaa-sel" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white">
                  <!-- Dinámico -->
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Provincia *</label>
                <select id="offer-province-sel" required disabled class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white disabled:bg-slate-50 disabled:text-slate-400">
                  <option value="">Selecciona una comunidad autónoma</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Municipio *</label>
                <input id="offer-municipality-sel" type="text" list="offer-municipality-list" required disabled autocomplete="off" placeholder="Selecciona o busca un municipio" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20 bg-white disabled:bg-slate-50 disabled:text-slate-400" />
                <datalist id="offer-municipality-list"></datalist>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Código Postal *</label>
                <input type="text" id="offer-postal-code" required inputmode="numeric" pattern="[0-9]{5}" maxlength="5" placeholder="Ej: 32002" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Localidad o Barrio (Opcional)</label>
                <input type="text" id="offer-locality-input" placeholder="Ej: Casco Viejo / Zona Norte" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
            </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Número de habitaciones *</label>
                <input type="number" id="offer-bedrooms" required min="0" step="1" value="0" placeholder="Ej: 3" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Número de baños *</label>
                <input type="number" id="offer-bathrooms" required min="0" step="1" value="0" placeholder="Ej: 2" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Superficie total (m²) *</label>
                <input type="number" id="offer-surface" required min="1" step="1" placeholder="Ej: 95" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Precio orientativo de salida (€) *</label>
                <input type="number" id="offer-price" required min="1" placeholder="Ej: 245000" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Comisión ofrecida al colaborador (%) *</label>
                <input type="text" id="offer-fee" required placeholder="Ej: 3.5%" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Condición de la propiedad *</label>
                <select id="offer-condition" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20"></select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Tipo de encargo / exclusividad *</label>
                <select id="offer-mandate" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20">
                  <option value="Sí, con exclusividad">Sí, con exclusividad</option>
                  <option value="Encargo de agente único">Encargo de agente único</option>
                  <option value="Exclusiva compartida">Exclusiva compartida</option>
                  <option value="No, nota de encargo abierta">No, nota de encargo abierta</option>
                  <option value="Sin exclusiva formalizada">Sin exclusiva formalizada</option>
                  <option value="Pendiente de confirmar">Pendiente de confirmar</option>
                </select>
                <p class="territory-scroll-hint">↕ Desplaza para ver todas</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Urgencia de venta *</label>
                <select id="offer-urgency" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20">
                  <option value="Baja">Baja (Sin prisa)</option>
                  <option value="Media">Media (Plazo ordinario)</option>
                  <option value="Alta">Alta (Venta urgente)</option>
                  <option value="Sin urgencia definida">Sin urgencia definida</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nivel de documentación disponible *</label>
                <select id="offer-docs" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20">
                  <option value="Nota simple únicamente">Nota simple únicamente</option>
                  <option value="Nota simple + planos">Nota simple + planos</option>
                  <option value="Nota simple + certificado energético">Nota simple + certificado energético</option>
                  <option value="Nota simple + planos + certificado energético">Nota simple + planos + certificado energético</option>
                  <option value="Expediente jurídico completo">Expediente jurídico completo</option>
                  <option value="Tasación disponible">Tasación disponible</option>
                  <option value="Expediente jurídico completo + tasación">Expediente jurídico completo + tasación</option>
                  <option value="No califica">No califica</option>
                </select>
              </div>
            </div>

            <div class="space-y-2">
              <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Título de la captación *</label>
              <input type="text" id="offer-title" required minlength="8" placeholder="Ej: Vivienda con gran terraza para reformar cerca del Retiro" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
            </div>

            <div class="space-y-2">
              <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Descripción *</label>
              <textarea id="offer-description" required minlength="30" rows="4" placeholder="Describe de forma atractiva el potencial de venta, tipo de inquilino o las condiciones de financiación sugeridas para el comprador final." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20"></textarea>
            </div>

            <!-- IMAGEN DE PORTADA DEL MARKETPLACE: optimizada para web o predeterminada -->
            <div class="space-y-3">
              <div>
                <span class="text-sm font-semibold text-slate-600 block">Imagen de portada del Marketplace</span>
                <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">Puedes subir una imagen real o utilizar la imagen predeterminada según la tipología del inmueble. Una fotografía real del activo genera más confianza y puede mejorar la conversión comercial.</p>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <label class="flex items-start gap-2 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-blue transition-all">
                  <input id="offer-image-mode-upload" type="radio" name="offer-image-mode" value="upload" checked onchange="setOfferImageMode('upload')" class="mt-0.5" />
                  <span><strong class="block text-xs text-navy">Subir imagen real</strong><small class="block text-[10px] text-slate-400 mt-1">Se recortará a 1:1 y se comprimirá automáticamente para web.</small></span>
                </label>
                <label class="flex items-start gap-2 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-blue transition-all">
                  <input id="offer-image-mode-default" type="radio" name="offer-image-mode" value="default" onchange="setOfferImageMode('default')" class="mt-0.5" />
                  <span><strong class="block text-xs text-navy">Usar imagen predeterminada</strong><small class="block text-[10px] text-slate-400 mt-1">Se aplicará automáticamente la imagen optimizada que corresponde al tipo de propiedad.</small></span>
                </label>
              </div>
              <div id="offer-image-upload-panel" class="p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-300 text-center cursor-pointer hover:bg-slate-100 transition-colors" onclick="if (document.getElementById('offer-image-mode-upload').checked) document.getElementById('offer-file-input').click()">
                <input type="file" id="offer-file-input" accept="image/*,application/pdf" class="hidden" onchange="handleFileSelection(event)">
                <span class="text-sm font-semibold text-slate-500 block">Fotografía de portada o plano complementario (Opcional)</span>
                <span class="text-[10px] text-slate-400 block mt-1" id="file-upload-status">Selecciona JPG, PNG, WEBP o PDF. Las imágenes se convierten a formato web ligero automáticamente.</span>
                <button type="button" class="mt-3 px-3 py-1.5 rounded-lg border border-slate-300 bg-white text-xs font-bold text-navy">Examinar archivos</button>
                <div id="file-preview-zone" class="hidden mt-3 flex items-center justify-center gap-2 text-xs font-semibold text-green-700">
                  <span id="file-icon">PDF</span> <span id="file-name" class="underline">archivo.pdf</span>
                </div>
              </div>
              <div id="offer-default-image-preview" class="hidden rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
                <div class="grid grid-cols-1 md:grid-cols-[220px_1fr]">
                  <div class="relative min-h-[180px] bg-slate-100">
                    <img id="offer-default-image-preview-img" src="" alt="Imagen predeterminada por tipologia" class="absolute inset-0 h-full w-full object-cover" loading="lazy" decoding="async" />
                  </div>
                  <div class="p-5">
                    <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase tracking-[0.16em]">Imagen predeterminada activa</span>
                    <h4 class="mt-3 text-base font-extrabold text-navy">La portada visible se asignará según el tipo de inmueble</h4>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500">Esta previsualización muestra la imagen que verán otros profesionales si no subes una fotografía real del activo.</p>
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-light/40 px-4 py-3">
                      <strong class="block text-[11px] font-black uppercase tracking-[0.14em] text-amber">Antes de registrar la captación</strong>
                      <p class="mt-2 text-xs leading-relaxed text-slate-600">Debes disponer de autorización para comercializar el inmueble, evitar datos sensibles en la ficha pública y registrar la propiedad de forma segura dentro de la plataforma.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <label class="legal-consent-box flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
              <input id="offer-pub-compliance" type="checkbox" required class="mt-0.5" />
              <span>Declaro que dispongo de autorización o base legítima para compartir esta oportunidad; que la ficha pública excluye datos sensibles del propietario; y que acepto las <a href="#/normas-publicacion" class="legal-link">Normas de publicación</a>. *</span>
            </label>
            <button type="submit" class="w-full py-4 rounded-xl bg-blue hover:bg-blue-dark text-white font-extrabold text-sm transition-all shadow-lg shadow-blue/20">
              Registrar Propiedad de forma Segura
            </button>
          </form>
        </div>
        <div class="mt-10 rounded-lg border border-green/20 bg-green-light/40 p-6 text-center">
          <h3 class="text-xl font-black text-navy">¿Tienes una captación y quieres darle más visibilidad profesional?</h3>
          <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-600">Comparte la oportunidad de forma segura y permite que otros colaboradores te ayuden a generar negocio.</p>
          <button type="button" onclick="scrollToPlatformForm('offer-publication-form')" class="mt-5 px-6 py-3 rounded-xl bg-green text-white text-xs font-black shadow-sm">Publicar captación</button>
        </div>
      </section>
    </div>

    <!-- PÁGINA 4: CÓMO FUNCIONA -->
    <div id="page-como-funciona" class="page-section hidden">
      <section class="py-16 md:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
          <h2 class="text-3xl font-black text-navy">Más oportunidades de negocio inmobiliario mediante colaboración profesional</h2>
          <p class="text-slate-500">Captacion.app conecta captaciones, demanda activa y colaboradores para generar más negocio, proteger la información sensible y mantener un seguimiento completo de cada oportunidad.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
          <!-- Flujo de agentes que buscan oportunidades -->
          <div class="space-y-8">
            <h3 class="text-xl font-extrabold text-blue border-b border-blue/10 pb-3 flex items-center gap-2">
              <span>➔</span> Para agentes que buscan oportunidades para sus clientes
            </h3>
            <div class="space-y-6">
              <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-blue text-white flex items-center justify-center font-bold text-xs shrink-0">1</div>
                <div>
                  <h4 class="font-bold text-navy">Encuentra captaciones que encajan con tu demanda</h4>
                  <p class="text-xs text-slate-500 mt-1">Filtra por zona, tipo de inmueble, presupuesto y perfil comprador para localizar oportunidades que realmente pueden interesar a tus clientes o inversores.</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-blue text-white flex items-center justify-center font-bold text-xs shrink-0">2</div>
                <div>
                  <h4 class="font-bold text-navy">Accede a la información con compromiso profesional</h4>
                  <p class="text-xs text-slate-500 mt-1">Solicita más detalles de una captación solo cuando exista interés real, aceptando previamente las condiciones del acuerdo de confidencialidad y colaboración.</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-blue text-white flex items-center justify-center font-bold text-xs shrink-0">3</div>
                <div>
                  <h4 class="font-bold text-navy">Coordina visitas y avances con trazabilidad</h4>
                  <p class="text-xs text-slate-500 mt-1">Organiza el contacto con el agente que publica la oportunidad, registra cada paso del proceso y trabaja con mayor claridad sobre visitas, interesados y posibles honorarios compartidos.</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Flujo de agentes que publican captaciones -->
          <div class="space-y-8">
            <h3 class="text-xl font-extrabold text-navy border-b border-navy-light/10 pb-3 flex items-center gap-2">
              <span>➔</span> Para agentes que publican captaciones
            </h3>
            <div class="space-y-6">
              <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xs shrink-0">1</div>
                <div>
                  <h4 class="font-bold text-navy">Publica sin exponer datos sensibles</h4>
                  <p class="text-xs text-slate-500 mt-1">Muestra la oportunidad de forma profesional sin revelar desde el inicio información delicada como dirección exacta, datos del propietario, teléfono o referencias internas.</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-bold text-xs shrink-0">2</div>
                <div>
                  <h4 class="font-bold text-navy">Presenta captaciones más ordenadas y fiables</h4>
                  <p class="text-xs text-slate-500 mt-1">Estructura cada ficha con datos clave, documentación de apoyo y criterios de calidad para que otros profesionales entiendan mejor la oportunidad antes de solicitar acceso.</p>
                </div>
              </div>
              <div class="flex gap-4">
                <div class="w-8 h-8 rounded-full bg-navy text-white flex items-center justify-center font-black text-xs shadow-md">3</div>
                <div>
                  <h4 class="font-bold text-navy">Controla quién accede y cómo avanza la operación</h4>
                  <p class="text-xs text-slate-500 mt-1">Decide qué profesionales pueden ampliar información, registra interacciones relevantes y mantén el control del proceso hasta la visita, negociación o cierre.</p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="mt-14 rounded-lg bg-navy p-7 sm:p-10 text-center text-white">
          <h3 class="text-2xl sm:text-3xl font-black">Convierte captaciones y demanda activa en nuevas oportunidades de negocio</h3>
          <p class="mx-auto mt-4 max-w-3xl text-sm leading-relaxed text-slate-200">Únete a una red profesional diseñada para conectar captaciones, demanda activa y colaboradores mediante un entorno más seguro, organizado y trazable. Captacion.app te ayuda a identificar oportunidades, proteger la información sensible y gestionar mejor cada relación comercial desde una única plataforma.</p>
          <div class="mx-auto mt-6 grid max-w-3xl gap-3 text-left text-sm sm:grid-cols-2"><span>✓ Publica captaciones de forma estructurada y profesional</span><span>✓ Encuentra demanda activa con mayor rapidez</span><span>✓ Controla accesos, seguimiento y colaboración profesional</span><span>✓ Escala tu actividad con herramientas, IA y seguimiento comercial</span></div>
          <div class="mt-7 flex flex-wrap justify-center gap-3"><button type="button" onclick="openProfessionalSubscriptionModal('como-funciona-starter')" class="px-6 py-3 rounded-xl bg-blue text-white text-xs font-black">Comenzar gratis</button><a href="#/planes" class="px-6 py-3 rounded-xl border border-white/30 text-white text-xs font-black hover:bg-white/10">Ver planes y funcionalidades</a></div>
          <p class="mt-3 text-xs text-slate-300">Accede al Plan Starter y descubre cómo funciona la plataforma.</p>
          <div class="mt-8 border-t border-white/15 pt-7"><h4 class="font-black">¿Para quién está diseñada Captacion.app?</h4><div class="mt-4 flex flex-wrap justify-center gap-2 text-xs"><?php foreach (array('Agentes inmobiliarios','Agencias','Captadores','Inversores','Colaboradores comerciales','Equipos de expansión') as $profile) : ?><span class="rounded-full border border-white/20 px-3 py-2"><?php echo esc_html($profile); ?></span><?php endforeach; ?></div><p class="mx-auto mt-5 max-w-3xl text-sm text-slate-200">Más que un marketplace inmobiliario, una plataforma diseñada para profesionalizar la colaboración entre quienes generan oportunidades y quienes las necesitan.</p></div>
        </div>
      </section>
    </div>

    <!-- PÁGINA 5: MARKETPLACE (Catálogo general de activos) -->
    <div id="page-marketplace" class="page-section hidden">
      <section class="py-12 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="mb-8 flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div><h2 class="text-3xl font-black text-navy">Catálogo General de Captaciones</h2><p class="text-sm text-slate-500 mt-2 max-w-4xl">Últimas oportunidades agregadas por nuestra red de agentes certificados. Utiliza el resumen, la búsqueda y los cambios de vista para revisar rápidamente lo disponible.</p></div>
            <a href="#/coincidencias-ventas" class="shrink-0 px-5 py-3 rounded-xl bg-green text-white text-xs font-black shadow-sm hover:opacity-90">Coincidencias de Ventas</a>
          </div>

          <div id="marketplace-carousel" class="mb-8"></div>


          <!-- FILTROS DE OFERTAS DISPONIBLES -->
          <div class="bg-white p-6 rounded-3xl border border-slate-200 shadow-sm mb-8">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-5">
              <h3 class="text-base font-extrabold text-navy">Filtros de ofertas disponibles</h3>
              <div class="flex flex-wrap items-center gap-1 rounded-xl bg-slate-100 p-1" role="group" aria-label="Modo de visualización del Marketplace">
                <button id="marketplace-view-map-btn" type="button" onclick="setMarketplaceView('map')" class="px-3 py-2 rounded-lg text-xs font-black text-slate-500 transition-all">Mapa</button>
                <button id="marketplace-layout-block-btn" type="button" onclick="setMarketplaceLayout('block')" class="map-view-active px-3 py-2 rounded-lg text-xs font-black transition-all">▦ Bloques</button>
                <button id="marketplace-layout-list-btn" type="button" onclick="setMarketplaceLayout('list')" class="px-3 py-2 rounded-lg text-xs font-black text-slate-500 transition-all">☰ Lista</button>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-10 gap-3">
              <div class="xl:col-span-2">
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Buscar oferta</label>
                <input id="market-search-filter" type="search" oninput="refreshMarketplaceView()" placeholder="Ej.: piso, Madrid, local, REF..." class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl focus:outline-none focus:ring-2 focus:ring-blue/20 bg-white" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Categoría</label>
                <select id="market-category-filter" onchange="refreshMarketplaceView()" class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl focus:outline-none bg-white">
                  <option value="all">Todas las categorías</option>
                  <option value="Piso">Piso</option>
                  <option value="Casa/Chalet">Casa / Chalet</option>
                  <option value="Local Comercial">Local Comercial</option>
                  <option value="Nave">Nave</option>
                  <option value="Oficina">Oficina</option>
                  <option value="Edificio">Edificio</option>
                  <option value="Suelo/Terreno">Suelo / Terreno</option>
                  <option value="Otros">Otros</option>
                </select>
              </div>
              <div><label class="block text-xs font-bold text-slate-400 uppercase mb-1">CCAA</label><select id="market-ccaa-filter" class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl bg-white"></select></div>
              <div><label class="block text-xs font-bold text-slate-400 uppercase mb-1">Provincia</label><select id="market-province-filter" disabled class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl bg-white"></select></div>
              <div><label class="block text-xs font-bold text-slate-400 uppercase mb-1">Municipio</label><select id="market-municipality-filter" disabled class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl bg-white"></select></div>
              <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Referencia</label>
                <input id="market-reference-filter" type="search" oninput="refreshMarketplaceView()" placeholder="Ej.: REF-00123456" class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl focus:outline-none focus:ring-2 focus:ring-blue/20 bg-white" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Código Postal</label>
                <input id="market-postal-code-filter" type="search" oninput="refreshMarketplaceView()" inputmode="numeric" maxlength="5" placeholder="Ej.: 32002" class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl focus:outline-none focus:ring-2 focus:ring-blue/20 bg-white" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Escala de precios</label>
                <select id="market-price-filter" onchange="refreshMarketplaceView()" class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl focus:outline-none bg-white">
                  <option value="all">Cualquier precio</option>
                  <option value="0-150000">Hasta 150.000 €</option>
                  <option value="150000-300000">150.000 € - 300.000 €</option>
                  <option value="300000-600000">300.000 € - 600.000 €</option>
                  <option value="600000-999999999">Más de 600.000 €</option>
                </select>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Ordenar por</label>
                <select id="market-sort" onchange="sortMarketplace()" class="w-full px-3 py-2 border border-slate-200 text-xs font-bold rounded-xl focus:outline-none bg-white">
                  <option value="newest">Más recientes</option>
                  <option value="oldest">Más antiguos</option>
                  <option value="price-low">Precio: Menor a Mayor</option>
                  <option value="price-high">Precio: Mayor a Menor</option>
                  <option value="score">Calidad de verificación</option>
                </select>
              </div>
            </div>
            <div class="mt-5 flex justify-end">
              <button onclick="clearMarketplaceFilters()" class="text-xs font-bold text-slate-500 hover:text-blue transition-all">Restablecer filtros</button>
            </div>
          </div>


          <!-- MINI-DASHBOARD INTERACTIVO DE CAPTACIONES (Click-To-Filter) -->
          <div id="marketplace-dashboard" class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-8">
            <!-- Se carga dinámicamente con JavaScript -->
          </div>

          <div id="marketplace-accordion-sections" class="space-y-4 mb-8"></div>

          <div id="marketplace-map-panel" class="hidden mb-8 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-100 px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
              <p class="text-xs text-slate-500">Vista mapa aproximada. Las ubicaciones exactas permanecen protegidas por confidencialidad.</p>
              <span class="shrink-0 text-[10px] font-black uppercase tracking-wider text-blue">Ubicación protegida</span>
            </div>
            <div id="marketplace-map" role="application" aria-label="Mapa aproximado de captaciones disponibles"></div>
          </div>

          <!-- Grid general de Marketplace -->
          <div id="marketplace-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Dinámico -->
          </div>
          <div class="mt-10 rounded-lg border border-blue/20 bg-white p-6 text-center shadow-sm">
            <h3 class="text-xl font-black text-navy">Explora oportunidades publicadas por otros profesionales</h3>
            <p class="mx-auto mt-2 max-w-2xl text-sm text-slate-600">Accede a la red profesional de oportunidades y encuentra captaciones alineadas con la demanda de tus clientes.</p>
            <button type="button" onclick="document.getElementById('marketplace-grid')?.scrollIntoView({behavior:'smooth',block:'start'})" class="mt-5 px-6 py-3 rounded-xl bg-blue text-white text-xs font-black">Acceder al Marketplace</button>
          </div>
        </div>
      </section>
    </div>

    <!-- PÁGINA: COINCIDENCIAS DE VENTAS -->
    <div id="page-coincidencias-ventas" class="page-section hidden">
      <section class="py-12 bg-slate-50/70">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-8">
            <div class="max-w-4xl"><span class="text-[10px] font-black uppercase tracking-[0.18em] text-green">Cruce oferta-demanda</span><h2 class="text-3xl font-black text-navy mt-2">Coincidencias de Ventas</h2><p class="text-sm text-slate-500 mt-2 leading-relaxed">Visualiza oportunidades donde una captación disponible puede encajar con una demanda activa. Detecta posibles operaciones, revisa coincidencias y activa nuevas colaboraciones profesionales.</p></div>
            <a href="#/marketplace" class="px-4 py-3 rounded-xl border border-slate-200 bg-white text-xs font-bold text-navy">Volver al Marketplace</a>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
            <article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm"><span class="text-[10px] font-black uppercase text-slate-400">Matches detectados</span><strong id="sales-match-count" class="block text-3xl font-black text-green mt-2">0</strong><span class="text-[11px] text-slate-500">Coincidencias calculadas con los datos disponibles.</span></article>
            <article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm"><span class="text-[10px] font-black uppercase text-slate-400">Valor económico estimado</span><strong id="sales-match-value" class="block text-3xl font-black text-navy mt-2">0 €</strong><span class="text-[11px] text-slate-500">Estimación, no representa operaciones cerradas.</span></article>
          </div>
          <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-8 gap-3">
              <input id="sales-match-search" oninput="renderSalesMatches()" type="search" placeholder="Buscar título, zona o referencia" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs" />
              <select id="sales-match-type" onchange="renderSalesMatches()" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white"><option value="all">Todos los tipos</option><option>Piso</option><option>Casa/Chalet</option><option>Local Comercial</option><option>Nave</option><option>Oficina</option><option>Edificio</option><option>Suelo/Terreno</option></select>
              <select id="sales-match-ccaa" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white"></select>
              <select id="sales-match-province" disabled class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white"></select>
              <select id="sales-match-municipality" disabled class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white"></select>
              <select id="sales-match-level" onchange="renderSalesMatches()" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white"><option value="all">Cualquier nivel</option><option value="high">Alta: 75% o más</option><option value="medium">Media: 60% a 74%</option></select>
              <select id="sales-match-sort" onchange="renderSalesMatches()" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white"><option value="newest">Más recientes</option><option value="score">Mayor coincidencia</option><option value="value">Mayor valor</option></select>
            </div>
          </div>
          <div id="sales-matches-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"></div>
        </div>
      </section>
    </div>

    <!-- PÁGINA 6: PLANES -->
    <div id="page-planes" class="page-section hidden">
      <section class="py-16 md:py-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="max-w-3xl mx-auto mb-12 space-y-4">
          <span class="text-xs font-bold tracking-widest text-blue uppercase">Planes Captacion.app</span>
          <h2 class="text-3xl sm:text-4xl font-extrabold text-navy">Empieza gratis y escala según tu actividad</h2>
          <p class="text-slate-600 font-semibold">No necesitas grandes inversiones iniciales. Elige el plan que mejor se adapte a tu volumen de oportunidades.</p>
          <h3 class="text-xl font-black text-navy">Elige el nivel que mejor se adapta a tu actividad</h3>
          <p class="text-slate-500">Captacion.app está diseñada para acompañarte desde tus primeras oportunidades hasta una gestión profesional de captaciones y demanda activa.</p>
          <p class="text-slate-500">Empieza gratis, escala cuando lo necesites y paga únicamente por el nivel de acceso que realmente utilices.</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto text-left items-stretch">
          <!-- Gratuito -->
          <div class="bg-white border border-slate-200 p-8 rounded-2xl flex flex-col justify-between shadow-sm">
            <div>
              <h3 class="text-lg font-bold text-navy mb-1">Starter</h3>
              <p class="text-xs text-slate-500 mb-6">Para descubrir oportunidades y conocer la plataforma.</p>
              <div class="text-4xl font-black text-navy mb-6">0 € <span class="text-xs text-slate-400 font-semibold">/ mes</span></div>
              <ul class="space-y-3 text-sm text-slate-600 border-t border-slate-100 pt-6">
                <li>✔ Acceso gratuito a Captacion.app</li><li>✔ Buscador básico de oportunidades</li><li>✔ Publicación de solicitudes y captaciones</li><li>✔ Marketplace y dashboard básico</li><li>✔ Visualización de oportunidades disponibles</li><li>✔ Acceso individual a oportunidades bajo demanda</li><li>✔ Ideal para conocer la plataforma antes de pasar a Professional</li>
              </ul>
            </div>
            <button onclick="handleFreePlanAccess()" class="mt-8 w-full py-3 rounded-xl border border-slate-200 text-navy font-bold text-xs hover:bg-slate-50">Comenzar gratis</button>
          </div>

          <!-- Profesional -->
          <div class="bg-white border-2 border-blue p-8 rounded-2xl flex flex-col justify-between shadow-lg relative">
            <span class="absolute top-4 right-4 bg-blue text-white text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full">Más Popular</span>
            <div>
              <h3 class="text-lg font-bold text-navy mb-1">Professional</h3>
              <p class="text-xs text-slate-500 mb-6">Para profesionales que generan negocio de forma activa.</p>
              <div class="text-4xl font-black text-navy mb-6">29 € <span class="text-xs text-slate-400 font-semibold">/ mes</span></div>
              <ul class="space-y-3 text-sm text-slate-600 border-t border-slate-100 pt-6">
                <li>✔ Todo lo incluido en Starter</li><li>✔ 30 accesos a oportunidades al mes</li><li>✔ Marketplace y dashboard profesional</li><li>✔ Alertas de nuevas captaciones y demandas activas</li><li>✔ Herramientas para gestionar oportunidades y colaboraciones</li><li>✔ Pack adicional disponible: 5 € por 15 accesos extra</li><li>✔ Mayor capacidad para generar nuevas operaciones</li>
              </ul>
            </div>
            <button onclick="return openMembershipPayment('professional', 'Professional')" class="mt-8 w-full py-3 rounded-xl bg-blue text-white font-bold text-xs hover:bg-blue-dark shadow-md text-center block">Activar Professional</button>
          </div>

          <!-- Premium -->
          <div class="bg-white border border-slate-200 p-8 rounded-2xl flex flex-col justify-between shadow-sm">
            <div>
              <h3 class="text-lg font-bold text-navy mb-1">Premium</h3>
              <p class="text-xs text-slate-500 mb-6">Para profesionales y equipos que buscan maximizar resultados.</p>
              <div class="text-4xl font-black text-navy mb-6">49 € <span class="text-xs text-slate-400 font-semibold">/ mes</span></div>
              <ul class="space-y-3 text-sm text-slate-600 border-t border-slate-100 pt-6">
                <li>✔ Todo lo incluido en Professional</li><li>✔ 60 accesos a oportunidades al mes</li><li>✔ Dashboard completo de actividad y rendimiento</li><li>✔ Alertas avanzadas de captaciones y demanda</li><li>✔ Herramientas de seguimiento comercial y productividad</li><li>✔ Calendario avanzado y exportación ICS</li><li>✔ Pack adicional disponible: 5 € por 30 accesos extra</li><li>✔ Diseñado para escalar tu actividad comercial</li>
              </ul>
            </div>
            <button onclick="openMembershipPayment('premium', 'Premium')" class="mt-8 w-full py-3 rounded-xl border border-slate-200 text-navy font-bold text-xs hover:bg-slate-50">Activar Premium</button>
          </div>
        </div>
        <div class="mt-12 max-w-4xl mx-auto text-left rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
          <h3 class="text-2xl font-black text-navy">¿Qué plan necesito?</h3>
          <div class="mt-5 grid gap-5 md:grid-cols-3 text-sm text-slate-600"><p><strong class="block text-navy mb-1">Starter</strong>Ideal para descubrir la plataforma y acceder a oportunidades puntuales.</p><p><strong class="block text-navy mb-1">Professional</strong>Pensado para agentes, captadores y colaboradores que trabajan oportunidades de forma constante y quieren generar negocio cada mes.</p><p><strong class="block text-navy mb-1">Premium</strong>Diseñado para profesionales de alto rendimiento, agencias, equipos comerciales e inversores que necesitan máxima capacidad de acceso y seguimiento.</p></div>
          <p class="mt-6 pt-5 border-t border-slate-200 text-sm font-bold text-blue">Siempre podrás ampliar tus accesos mediante packs adicionales sin necesidad de cambiar de plan.</p>
        </div>
      </section>
    </div>

    <!-- PÁGINA 7: RECURSOS -->
    <div id="page-recursos" class="page-section hidden">
      <section class="py-12 bg-slate-50/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-4xl mb-10">
            <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase tracking-[0.18em]">Biblioteca profesional</span>
            <h2 class="text-3xl sm:text-4xl font-black text-navy mt-4 leading-tight">Recursos descargables para usuarios profesionales</h2>
            <p class="text-sm sm:text-base text-slate-500 mt-4 leading-relaxed">Accede a modelos, checklists y guías de apoyo para documentar mejor tus captaciones, demandas y colaboraciones profesionales.</p>
          </div>
          <div id="professional-downloadable-resources" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5"></div>
        </div>
      </section>
      <section class="hidden py-12 bg-slate-50/60" aria-hidden="true">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div class="max-w-4xl mb-10">
            <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase tracking-[0.18em]">Caja de herramientas B2B</span>
            <h2 class="text-3xl sm:text-4xl font-black text-navy mt-4 leading-tight">Herramientas inmobiliarias para captar propietarios con mayor precisión</h2>
            <p class="text-sm sm:text-base text-slate-500 mt-4 leading-relaxed">Una caja de herramientas enfocada exclusivamente en la captación y valoración comercial. El objetivo es validar primero las utilidades más necesarias para captar propietarios, preparar expedientes y justificar recomendaciones antes de ampliar nuevas categorías.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm"><span class="text-[10px] uppercase tracking-wider text-slate-400 font-black">Catálogo de recursos</span><strong id="resource-stat-total" class="block text-3xl font-black text-navy mt-2">9</strong><p class="text-[11px] text-slate-500 mt-1">Recursos de captación y valoración.</p></article>
            <article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm"><span class="text-[10px] uppercase tracking-wider text-slate-400 font-black">Demos interactivas</span><strong id="resource-stat-demo" class="block text-3xl font-black text-green mt-2">3</strong><p class="text-[11px] text-slate-500 mt-1">Listas para probar en esta versión.</p></article>
            <article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm"><span class="text-[10px] uppercase tracking-wider text-slate-400 font-black">Área activa</span><strong class="block text-lg font-black text-blue mt-2">Captación</strong><p class="text-[11px] text-slate-500 mt-1">Valoración, documentación y seguimiento.</p></article>
            <article class="p-5 rounded-2xl bg-navy text-white shadow-sm"><span class="text-[10px] uppercase tracking-wider text-blue-light font-black">Desarrollo progresivo</span><strong class="block text-lg font-black mt-2">Validar antes de ampliar</strong><p class="text-[11px] text-slate-300 mt-1">Las demás categorías se incorporarán en siguientes fases.</p></article>
          </div>

          <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-4 sm:p-6 mb-8">
            <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4">
              <div class="flex items-center gap-2">
                <span class="inline-flex px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-bold whitespace-nowrap">Captación y valoración</span>
                <span class="hidden sm:inline text-xs text-slate-500">Las demás áreas se incorporarán progresivamente.</span>
              </div>
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 xl:w-[430px] shrink-0">
                <label class="relative block"><span class="sr-only">Buscar herramienta</span><input id="resource-search" oninput="renderResourceCatalog()" placeholder="Buscar herramienta..." class="w-full px-4 py-2.5 pl-9 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue/20" /><span class="absolute left-3 top-2.5 text-sm">&#128270;</span></label>
                <select id="resource-access-filter" onchange="renderResourceCatalog()" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white text-slate-600 font-bold"><option value="all">Todos los accesos</option><option value="publico">Acceso público</option><option value="registro">Registro gratuito</option><option value="profesional">Profesional verificado</option></select>
              </div>
            </div>
          </div>

          <section id="resources-featured-section" class="mb-10">
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
              <div><span class="text-xs font-black uppercase tracking-widest text-blue">Sección archivada</span><h3 class="text-2xl font-black text-navy mt-2">Catálogo anterior</h3></div>
              <p class="text-xs text-slate-500 max-w-lg">Estas tarjetas permiten probar las utilidades con mayor potencial de uso recurrente antes de desarrollar una infraestructura completa.</p>
            </div>
            <div id="resource-featured-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4"></div>
          </section>

          <section class="mb-12">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
              <div><h3 class="text-2xl font-black text-navy">Explorar caja de herramientas</h3><p class="text-xs text-slate-500 mt-1">Filtra los recursos de captación por nivel de acceso o palabra clave.</p></div>
              <span id="resource-count" class="px-3 py-1.5 rounded-full bg-blue-light text-blue text-xs font-black">0 recursos</span>
            </div>
            <div id="resources-catalog-grid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
          </section>

          <section id="resources-legal-documents" class="hidden rounded-3xl bg-white border border-slate-200 shadow-sm p-5 sm:p-8 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-4 mb-6">
              <div class="max-w-3xl"><span class="text-xs font-black uppercase tracking-widest text-blue">Legal y cumplimiento</span><h3 class="text-2xl font-black text-navy mt-2">Documentos y plantillas B2B</h3><p class="text-sm text-slate-500 mt-2 leading-relaxed">Descarga muestras anónimas para lectura previa. Cuando exista una colaboración confirmada, utiliza el botón de firma para preparar el documento definitivo, completar los campos operativos y generar un enlace seguro.</p></div>
              <span class="px-3 py-1.5 rounded-full bg-amber-light text-amber text-[10px] font-black uppercase">Revisión jurídica recomendada</span>
            </div>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
              <article data-legal-resource-category="legal" class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60">
                <div class="flex gap-3"><span class="text-2xl">&#128196;</span><div><h4 class="text-sm font-black text-navy">Plantilla de Acuerdo de Confidencialidad (NDA)</h4><p class="text-xs text-slate-500 mt-1 leading-relaxed">Muestra sin datos sensibles para revisar el acuerdo antes de compartir el expediente inmobiliario completo.</p></div></div>
                <div class="grid grid-cols-2 gap-2 mt-4 text-[10px]"><span class="px-2 py-1.5 bg-white rounded-lg border border-slate-200 text-slate-500"><b class="text-navy">Resultado:</b> PDF anónimo</span><span class="px-2 py-1.5 bg-white rounded-lg border border-slate-200 text-slate-500"><b class="text-navy">Acceso:</b> suscriptores</span></div>
                <div class="flex flex-wrap gap-2 mt-4"><a href="https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/01_Modelo_acuerdo_confidencialidad_NDA.pdf" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-[10px] font-black text-blue hover:bg-blue-light">Descargar plantilla PDF</a><button onclick="prepareLegalSignature('nda')" class="px-3 py-2 rounded-lg bg-navy text-[10px] font-black text-white hover:bg-navy-light">Preparar firma electrónica</button></div>
              </article>
              <article data-legal-resource-category="colaboración" class="p-5 rounded-2xl border border-slate-200 bg-slate-50/60">
                <div class="flex gap-3"><span class="text-2xl"></span><div><h4 class="text-sm font-black text-navy">Acuerdo de colaboración y honorarios compartidos</h4><p class="text-xs text-slate-500 mt-1 leading-relaxed">Muestra sin datos sensibles para conocer el reparto de honorarios, la trazabilidad y las cláusulas de no elusión.</p></div></div>
                <div class="grid grid-cols-2 gap-2 mt-4 text-[10px]"><span class="px-2 py-1.5 bg-white rounded-lg border border-slate-200 text-slate-500"><b class="text-navy">Resultado:</b> PDF anónimo</span><span class="px-2 py-1.5 bg-white rounded-lg border border-slate-200 text-slate-500"><b class="text-navy">Acceso:</b> suscriptores</span></div>
                <div class="flex flex-wrap gap-2 mt-4"><a href="https://lightblue-salamander-627943.hostingersite.com/wp-content/uploads/2026/06/02_Modelo_acuerdo_colaboracion_profesionales.pdf" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-[10px] font-black text-blue hover:bg-blue-light">Descargar plantilla PDF</a><button onclick="prepareLegalSignature('collaboration')" class="px-3 py-2 rounded-lg bg-navy text-[10px] font-black text-white hover:bg-navy-light">Preparar firma electrónica</button></div>
              </article>
            </div>
            <div class="mt-5 p-4 rounded-xl bg-amber-light/60 border border-amber/20 text-[10px] text-slate-600 leading-relaxed"><strong class="text-amber">Uso orientativo:</strong> los documentos son ejemplos anónimos. La versión definitiva deberá generarse desde servidor, registrar trazabilidad y ser revisada por asesoría jurídica antes de su utilización real.</div>
          </section>

          <section class="hidden rounded-3xl bg-navy text-white p-6 sm:p-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center"><div class="lg:col-span-8"><span class="text-xs font-black uppercase tracking-widest text-blue-light">Evolución de producto</span><h3 class="text-2xl font-black mt-2">De biblioteca estática a entorno de trabajo recurrente</h3><p class="text-sm text-slate-300 mt-3 leading-relaxed">La siguiente fase puede conectar cada recurso con expedientes, perfiles verificados, documentos guardados, alertas, reputación profesional y salas privadas de operación.</p></div><div class="lg:col-span-4 grid grid-cols-2 gap-3 text-center"><div class="p-4 rounded-2xl bg-white/5 border border-white/10"><strong class="block text-2xl font-black">6</strong><span class="text-[10px] text-slate-300 uppercase">Áreas de trabajo</span></div><div class="p-4 rounded-2xl bg-white/5 border border-white/10"><strong class="block text-2xl font-black">10</strong><span class="text-[10px] text-slate-300 uppercase">Prioridades MVP</span></div></div></div>
          </section>
        </div>
      </section>
    </div>

    <!-- PÁGINA 8: CONTACTO -->
    <div id="page-contacto" class="page-section hidden">
      <section class="py-12 max-w-5xl mx-auto px-4 sm:px-6">
        <div class="text-center mb-10 space-y-2">
          <h2 class="text-3xl font-black text-navy">¿Tienes dudas o necesitas un plan a medida?</h2>
          <p class="text-slate-500 text-sm">Nuestro equipo de soporte te responderá en menos de 24 horas.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-stretch">
          <!-- Datos -->
          <div class="md:col-span-5 bg-navy text-white p-8 rounded-3xl space-y-6">
            <h3 class="text-xl font-bold">Oficina de Soporte</h3>
            <p class="text-xs text-slate-300 leading-relaxed">Operamos de forma distribuida en toda España, ofreciendo asistencia rápida a agencias asociadas.</p>
            
            <div class="space-y-4 text-xs">
              <div>
                <span class="text-slate-400 block font-bold">Dirección Fiscal</span>
                <span>Madrid, España</span>
              </div>
              <div>
                <span class="text-slate-400 block font-bold">✉ Correo de contacto</span>
                <span class="text-blue-light">hola@captacion.app</span>
              </div>
              <div>
                <span class="text-slate-400 block font-bold"> Horario</span>
                <span>Lunes a Viernes • 09:00 - 18:00</span>
              </div>
            </div>
          </div>

          <!-- Formulario -->
          <div class="md:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200">
            <form onsubmit="handleContactSubmit(event)" class="space-y-4">
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Nombre y apellidos *</label>
                <input id="contact-name" type="text" required placeholder="Tu nombre" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Correo electronico *</label>
                <input id="contact-email" type="email" required autocomplete="email" placeholder="tu@email.com" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Telefono</label>
                <input id="contact-phone" type="tel" autocomplete="tel" placeholder="+34 600 000 000" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Preferencia de contacto</label>
                <select id="contact-preference" onchange="updateContactPhoneRequirement()" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-sm focus:ring-2 focus:ring-blue/20">
                  <option value="email" selected>Email</option><option value="call">Llamada</option><option value="whatsapp">WhatsApp</option>
                </select>
                <p class="territory-scroll-hint">↕ Desplaza para ver todas</p>
                <p id="contact-phone-help" class="mt-1 text-[10px] text-slate-400">Opcional para contacto por email.</p>
              </div>
              <div>
                <label class="block text-xs font-bold text-slate-500 mb-1">Mensaje *</label>
                <textarea id="contact-message" rows="3" required placeholder="Escribe aquí tu consulta..." class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20"></textarea>
              </div>
              <p id="contact-form-error" class="hidden rounded-xl bg-red-50 border border-red-100 px-3 py-2 text-xs text-red-700" role="alert"></p>
              <label class="legal-consent-box flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
                <input id="contact-privacy-consent" type="checkbox" required class="mt-0.5" />
                <span>He leído la <a href="#/privacidad" class="legal-link">Política de privacidad</a> y autorizo el tratamiento de mis datos para responder a esta consulta. *</span>
              </label>
              <label class="flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
                <input id="contact-marketing-consent" type="checkbox" class="mt-0.5" />
                <span>Quiero recibir novedades y comunicaciones comerciales de Captacion.app. Opcional y revocable.</span>
              </label>
              <button type="submit" class="w-full py-3 rounded-xl bg-blue hover:bg-blue-dark text-white font-extrabold text-xs transition-all shadow-md">Enviar consulta</button>
            </form>
          </div>
        </div>
      </section>
    </div>



    <!-- PÁGINA 9: AVISO LEGAL -->
    <div id="page-aviso-legal" class="page-section hidden">
      <section class="py-12 max-w-5xl mx-auto px-4 sm:px-6">
        <div class="space-y-3 mb-8">
          <span class="text-xs font-black uppercase tracking-widest text-blue">Centro legal</span>
          <h2 class="text-3xl font-black text-navy">Aviso legal</h2>
          <p class="text-sm text-slate-500 leading-relaxed">Base legal de preproducción pendiente de completar con datos societarios verificados, política de privacidad definitiva e inventario final de tecnologías.</p>
        </div>
        <div class="grid gap-5">
          <article class="legal-card">
            <h3 class="text-lg">1. Titular del sitio web</h3>
            <div class="mt-3 grid gap-2 text-sm">
              <p><span class="legal-placeholder">TODO LEGAL — sustituir antes de producción</span> EMPRESA PENDIENTE DE DEFINIR, S.L. · B00000000 · Domicilio social pendiente de completar · contacto@captacion.app.</p>
              <p><span class="legal-placeholder">PREPRODUCCIÓN</span> El acceso a flujos con datos reales quedará restringido hasta cerrar cumplimiento, seguridad y contratos aplicables.</p>
            </div>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">2. Objeto y naturaleza del servicio</h3>
            <p class="mt-2 text-sm leading-relaxed">Captacion.app es una plataforma digital B2B orientada a profesionales inmobiliarios. Facilita la publicación de oportunidades y demandas con información pública limitada, el cruce de coincidencias y la preparación de colaboraciones. La plataforma no sustituye la diligencia profesional de las partes, no garantiza la veracidad material de cada anuncio y no actúa como propietaria del inmueble.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">3. Responsabilidad del usuario anunciante</h3>
            <ul class="mt-2 space-y-1.5 text-sm leading-relaxed">
              <li>Publicar información veraz, actualizada, suficiente y lícita.</li>
              <li>Disponer de autorización o base legítima para compartir la oportunidad.</li>
              <li>No revelar públicamente datos personales, direcciones exactas, documentación sensible o referencias que permitan identificar al propietario sin base jurídica.</li>
              <li>Actualizar o retirar el anuncio cuando deje de estar disponible.</li>
              <li>Respetar la normativa inmobiliaria, fiscal, de consumo, igualdad y competencia que resulte aplicable.</li>
            </ul>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">4. Moderación y retirada de contenidos</h3>
            <p class="mt-2 text-sm leading-relaxed">La plataforma podrá revisar, limitar, suspender o retirar publicaciones que sean inexactas, duplicadas, engañosas, ilícitas o contrarias a estas normas. Cualquier usuario puede utilizar el canal de reporte para comunicar una incidencia. La retirada o limitación deberá documentarse internamente y comunicarse al usuario afectado cuando proceda.</p>
            <button type="button" onclick="openReportModal()" class="mt-4 px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-bold">Reportar contenido o incidencia</button>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">5. Condiciones de preproducción</h3>
            <p class="mt-2 text-sm leading-relaxed">Esta versión se utiliza para validación interna y parte de su operativa sigue apoyándose en almacenamiento local del navegador. Antes del despliegue productivo se activarán autenticación segura, persistencia en servidor, copias de seguridad, control de permisos y revisión jurídica final.</p>
          </article>
        </div>
      </section>
    </div>

    <!-- PÁGINA 10: PRIVACIDAD -->
    <div id="page-privacidad" class="page-section hidden">
      <section class="py-12 max-w-5xl mx-auto px-4 sm:px-6">
        <div class="space-y-3 mb-8">
          <span class="text-xs font-black uppercase tracking-widest text-green">RGPD y LOPDGDD</span>
          <h2 class="text-3xl font-black text-navy">Política de privacidad</h2>
          <p class="text-sm text-slate-500 leading-relaxed">Este texto resume el enfoque de privacidad previsto para la versión pública final. En esta URL provisional se mantiene como borrador interno de trabajo para alinear producto, captación y cumplimiento.</p>
        </div>
        <div class="grid gap-5">
          <article class="legal-card">
            <h3 class="text-lg">1. Responsable del tratamiento</h3>
            <p class="mt-2 text-sm"><span class="legal-placeholder">TODO LEGAL — sustituir antes de producción</span> EMPRESA PENDIENTE DE DEFINIR, S.L. · B00000000 · Domicilio social pendiente de completar · privacidad@captacion.app. DPO no designado salvo confirmación.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">2. Datos tratados</h3>
            <ul class="mt-2 space-y-1.5 text-sm leading-relaxed">
              <li>Datos profesionales de registro: nombre, agencia, correo y WhatsApp.</li>
              <li>Datos de contacto incluidos en formularios y solicitudes de colaboración.</li>
              <li>Información operativa de captaciones y demandas, procurando minimizar datos personales.</li>
              <li>Datos técnicos y preferencias necesarias para la sesión, seguridad y configuración.</li>
              <li>Registros de auditoría sobre accesos, solicitudes, moderación y firma electrónica cuando se implanten.</li>
            </ul>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">3. Finalidades y bases jurídicas</h3>
            <ul class="mt-2 space-y-1.5 text-sm leading-relaxed">
              <li>Gestionar cuentas, solicitudes y colaboraciones: ejecución de la relación contractual o medidas precontractuales.</li>
              <li>Responder consultas: consentimiento o interés legítimo según el contexto.</li>
              <li>Cumplir obligaciones legales y atender incidencias: obligación legal e interés legítimo.</li>
              <li>Enviar comunicaciones comerciales: consentimiento específico, separado y revocable.</li>
            </ul>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">4. Destinatarios y encargados</h3>
            <p class="mt-2 text-sm leading-relaxed">Podrán intervenir proveedores de alojamiento, correo, mensajería, firma electrónica, soporte, seguridad y pagos, mediante contratos adecuados. Los datos completos de un anunciante solo deben desbloquearse tras el flujo autorizado correspondiente. No deben compartirse datos personales con terceros sin base jurídica.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">5. Conservación</h3>
            <p class="mt-2 text-sm leading-relaxed">Los datos se conservarán durante la relación activa y posteriormente durante los plazos exigidos para atender obligaciones legales o posibles responsabilidades. En producción deberá aprobarse un cuadro de conservación por categoría de datos.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">6. Derechos de las personas</h3>
            <p class="mt-2 text-sm leading-relaxed">Las personas pueden solicitar acceso, rectificación, supresión, oposición, limitación, portabilidad y no ser objeto de decisiones individuales automatizadas cuando corresponda.</p>
            <p class="mt-2 text-sm">Canal provisional para ejercicio de derechos: <a class="legal-link" href="mailto:privacidad@captacion.app">privacidad@captacion.app</a>. <span class="legal-placeholder">TODO LEGAL — confirmar antes de producción</span>.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">7. Seguridad y confidencialidad</h3>
            <p class="mt-2 text-sm leading-relaxed">El acceso a datos privados deberá limitarse por roles, trazabilidad y necesidad profesional. Los colaboradores deberán mantener confidencialidad y firmar los acuerdos aplicables antes de acceder al expediente completo.</p>
          </article>
        </div>
      </section>
    </div>

    <!-- PÁGINA 11: COOKIES -->
    <div id="page-cookies" class="page-section hidden">
      <section class="py-12 max-w-5xl mx-auto px-4 sm:px-6">
        <div class="space-y-3 mb-8">
          <span class="text-xs font-black uppercase tracking-widest text-amber">Preferencias de navegación</span>
          <h2 class="text-3xl font-black text-navy">Política de cookies y almacenamiento local</h2>
          <p class="text-sm text-slate-500 leading-relaxed">Complianz es la fuente principal de consentimiento, bloqueo preventivo e inventario de cookies y tecnologías similares en este sitio.</p>
        </div>
        <div class="grid gap-5">
          <article class="legal-card">
            <h3 class="text-lg">1. Tecnologías utilizadas</h3>
            <ul class="mt-2 space-y-1.5 text-sm leading-relaxed">
              <li><strong>Necesarias:</strong> sesión, seguridad, preferencia de tema y almacenamiento técnico/operativo de preproducción.</li>
              <li><strong>Analítica:</strong> desactivada en esta versión.</li>
              <li><strong>Marketing:</strong> desactivado en esta versión.</li>
            </ul>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">2. Consentimiento</h3>
            <p class="mt-2 text-sm leading-relaxed">Las tecnologías estrictamente necesarias pueden utilizarse para prestar un servicio solicitado. Complianz mantiene desactivadas las finalidades de preferencias, estadísticas o marketing que requieran consentimiento hasta que la persona usuaria decida.</p>
            <button type="button" onclick="captacionOpenCookiePreferences()" class="mt-4 px-4 py-2.5 rounded-xl bg-blue text-white text-xs font-bold">Configurar preferencias</button>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">3. Proveedores externos</h3>
            <p class="mt-2 text-sm leading-relaxed">El mapa usa Leaflet y teselas de OpenStreetMap como servicio técnico solicitado para mostrar cobertura territorial. También se cargan Tailwind, Leaflet Draw y Google Fonts desde CDN. Estos servicios deben figurar en el inventario de Complianz; antes de producción se revisará su base jurídica y se priorizará el autoalojamiento cuando resulte viable.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">4. Actualización del inventario</h3>
            <p class="mt-2 text-sm leading-relaxed">La declaración principal es la generada por Complianz y se actualiza con su escáner. Los datos del titular permanecen como <span class="legal-placeholder">TODO LEGAL — sustituir antes de producción</span>.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">5. Declaración de cookies de Complianz</h3>
            <div class="mt-3 text-sm leading-relaxed"><?php echo shortcode_exists('cmplz-document') ? do_shortcode('[cmplz-document type="cookie-statement" region="eu"]') : '<p>Complianz debe estar instalado, activo y configurado para mostrar aquí la declaración dinámica de cookies.</p>'; ?></div>
          </article>
        </div>
      </section>
    </div>

    <!-- PÁGINA 12: NORMAS DE PUBLICACIÓN -->
    <div id="page-normas-publicacion" class="page-section hidden">
      <section class="py-12 max-w-5xl mx-auto px-4 sm:px-6">
        <div class="space-y-3 mb-8">
          <span class="text-xs font-black uppercase tracking-widest text-blue">Publicación responsable</span>
          <h2 class="text-3xl font-black text-navy">Normas de publicación y responsabilidad de la plataforma</h2>
          <p class="text-sm text-slate-500 leading-relaxed">Reglas operativas para reducir riesgos, proteger datos personales y facilitar la moderación de anuncios.</p>
        </div>
        <div class="grid gap-5">
          <article class="legal-card">
            <h3 class="text-lg">Antes de publicar</h3>
            <ul class="mt-2 space-y-1.5 text-sm leading-relaxed">
              <li>Confirmar la legitimidad de la captación o demanda y la autorización necesaria.</li>
              <li>Publicar únicamente información mínima no sensible en la ficha abierta.</li>
              <li>Evitar dirección exacta, teléfonos particulares, emails privados, documentación identificativa, datos catastrales completos y datos bancarios.</li>
              <li>Describir el inmueble con precisión, sin afirmaciones engañosas ni discriminatorias.</li>
              <li>Actualizar disponibilidad, precio y condiciones de colaboración.</li>
            </ul>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">Datos que pueden compartirse de forma limitada</h3>
            <p class="mt-2 text-sm leading-relaxed">Tipo de inmueble, zona aproximada, código postal cuando sea adecuado, precio, superficie, habitaciones, baños, score interno y condiciones generales de colaboración. El expediente completo debe permanecer bloqueado hasta completar el flujo profesional autorizado.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">Datos reservados</h3>
            <p class="mt-2 text-sm leading-relaxed">Identidad y contacto del propietario, dirección exacta, documentación personal, nota simple, referencias catastrales completas, contratos, datos bancarios y cualquier documento que contenga información innecesaria para la vista previa pública.</p>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">Moderación y canal de reporte</h3>
            <p class="mt-2 text-sm leading-relaxed">La plataforma debe ofrecer un mecanismo sencillo para reportar contenido presuntamente ilícito o incorrecto, registrar la incidencia, revisar la publicación y documentar la decisión adoptada.</p>
            <button type="button" onclick="openReportModal()" class="mt-4 px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-bold">Abrir canal de reporte</button>
          </article>
          <article class="legal-card">
            <h3 class="text-lg">Trazabilidad de profesionales</h3>
            <p class="mt-2 text-sm leading-relaxed">Antes de permitir colaboraciones reales, la versión productiva deberá verificar identidad profesional, datos de contacto, organización y evidencias básicas del usuario anunciante, aplicando un enfoque proporcional al servicio prestado.</p>
          </article>
        </div>
      </section>
    </div>

    <!-- PÁGINA 13: ÁREA PRIVADA · DASHBOARD DEL AGENTE -->
    <div id="page-area-privada" class="page-section hidden">
      <section class="py-10 max-w-[1500px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="private-area-legacy-header flex flex-col xl:flex-row xl:items-center justify-between gap-5 border-b border-slate-200 pb-6 mb-6">
          <div>
            <span class="text-xs font-bold text-blue uppercase tracking-widest">Área privada · Centro de operaciones</span>
            <h2 class="text-3xl font-black text-navy mt-1">Dashboard del agente</h2>
            <p class="text-sm text-slate-500 mt-2">Gestiona captaciones, demandas, coincidencias, solicitudes y operaciones desde un único espacio.</p>
          </div>
          <div class="flex flex-wrap items-center gap-2">
            <div class="px-3 py-2 rounded-xl bg-green-light text-green text-xs font-black">Perfil verificado</div>
            <div id="private-plan-access-badge" class="px-3 py-2 rounded-xl bg-blue-light text-blue text-xs font-black">Plan Básico · 0 accesos</div>
            <a href="#/ofrecer-captacion" class="px-4 py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-bold shadow-md">+ Publicar captación</a>
            <a href="#/buscar-captaciones" class="px-4 py-3 rounded-xl bg-navy hover:bg-navy-light text-white text-xs font-bold shadow-md">+ Publicar demanda</a>
          </div>
        </div>

        <div class="lg:hidden mb-5">
          <label class="block text-[10px] font-black uppercase tracking-wider text-slate-500 mb-2">Sección del dashboard</label>
          <select id="private-dashboard-mobile-select" onchange="switchPrivateDashboardPanel(this.value)" class="private-dashboard-mobile-select">
            <option value="overview">Inicio</option><option value="offers">Ofrezco captación</option><option value="demands">Busco captación</option><option value="requests">Solicitudes</option><option value="operations">Operaciones</option><option value="favorites">Mis favoritos</option><option value="tasks">Tareas</option><option value="notifications">Notificaciones</option><option value="subscriptions">Suscripciones y alertas</option><option value="communications">Comunicación interna</option><option value="traceability">Trazabilidad</option><option value="feeds">Feeds XML</option><option value="ai">Configuración IA</option><option value="profile">Perfil profesional</option>
                </select>
                <p class="territory-scroll-hint">↕ Desplaza para ver todos</p>
        </div>

        <div class="private-dashboard-shell">
          <aside class="private-dashboard-sidebar hidden lg:block">
            <div class="exec-sidebar-brand">
              <span class="exec-brand-mark"></span>
              <span><strong class="block text-[13px] tracking-wide text-white">CAPTACION.APP</strong><small class="block mt-0.5 text-[7px] tracking-wider text-slate-400">COLABORACIÓN INMOBILIARIA</small></span>
            </div>
            <div class="exec-sidebar-profile px-2 pb-3 mb-2 border-b border-slate-200">
              <p id="private-dashboard-agent-name" class="text-sm font-black text-navy">Agente profesional</p>
              <p id="private-dashboard-agent-agency" class="text-[11px] text-slate-500 mt-1">Captacion.app</p>
            </div>
            <nav class="space-y-1">
              <button type="button" data-private-panel="overview" onclick="switchPrivateDashboardPanel('overview')" class="private-dashboard-nav active"><span>▦</span><span>Inicio</span></button>
              <button type="button" data-private-panel="offers" onclick="switchPrivateDashboardPanel('offers')" class="private-dashboard-nav"><span></span><span>Ofrezco captación</span></button>
              <button type="button" data-private-panel="demands" onclick="switchPrivateDashboardPanel('demands')" class="private-dashboard-nav"><span>🔎</span><span>Busco captación</span></button>
              <button type="button" data-private-panel="requests" onclick="switchPrivateDashboardPanel('requests')" class="private-dashboard-nav"><span>✉</span><span>Solicitudes</span><span id="private-sidebar-requests" class="ml-auto px-2 py-0.5 rounded-full bg-amber-light text-amber text-[9px] font-black">0</span></button>
              <button type="button" data-private-panel="operations" onclick="switchPrivateDashboardPanel('operations')" class="private-dashboard-nav"><span>&#129309;</span><span>Operaciones</span></button>
              <button type="button" data-private-panel="favorites" onclick="switchPrivateDashboardPanel('favorites')" class="private-dashboard-nav"><span aria-hidden="true">♥</span><span>Mis favoritos</span></button>
              <button id="private-nav-tasks" type="button" data-private-panel="tasks" onclick="switchPrivateDashboardPanel('tasks')" class="private-dashboard-nav"><span>✓</span><span>Calendario</span><span id="private-sidebar-tasks" class="ml-auto px-2 py-0.5 rounded-full bg-blue-light text-blue text-[9px] font-black">0</span></button>
              <button type="button" data-private-panel="notifications" onclick="switchPrivateDashboardPanel('notifications')" class="private-dashboard-nav"><span>&#128276;</span><span>Notificaciones</span><span id="private-sidebar-notifications" class="ml-auto px-2 py-0.5 rounded-full bg-red-50 text-red-600 text-[9px] font-black">0</span></button>
              <div class="my-2 border-t border-slate-200"></div>
              <button type="button" data-private-panel="subscriptions" onclick="switchPrivateDashboardPanel('subscriptions')" class="private-dashboard-nav"><span>+</span><span>Suscripciones y alertas</span><span id="private-sidebar-subscriptions" class="ml-auto px-2 py-0.5 rounded-full bg-blue-light text-blue text-[9px] font-black">0</span></button>
              <button type="button" data-private-panel="communications" onclick="switchPrivateDashboardPanel('communications')" class="private-dashboard-nav"><span>💬</span><span>Comunicación interna</span><span id="private-sidebar-messages" class="ml-auto px-2 py-0.5 rounded-full bg-green-light text-green text-[9px] font-black">0</span></button>
              <button type="button" data-private-panel="traceability" onclick="switchPrivateDashboardPanel('traceability')" class="private-dashboard-nav"><span>⧉</span><span>Trazabilidad</span></button>
              <button type="button" data-private-panel="feeds" onclick="switchPrivateDashboardPanel('feeds')" class="private-dashboard-nav"><span>↻</span><span>Feeds XML</span></button>
              <button type="button" data-private-panel="data" onclick="switchPrivateDashboardPanel('data')" class="private-dashboard-nav"><span>🔒</span><span>Datos y privacidad</span></button>
              <button type="button" data-private-panel="ai" onclick="switchPrivateDashboardPanel('ai')" class="private-dashboard-nav"><span>✦</span><span>Configuración IA</span></button>
              <button type="button" data-private-panel="profile" onclick="switchPrivateDashboardPanel('profile')" class="private-dashboard-nav"><span>◉</span><span>Perfil profesional</span></button>
            </nav>
          </aside>

          <div class="min-w-0">
            <!-- RESUMEN -->
            <div id="private-panel-overview" class="private-dashboard-panel active">
              <div class="exec-dashboard">
                <header class="exec-head">
                  <div><h3>Resumen ejecutivo</h3><p>Visión general de tu actividad comercial</p></div>
                  <div class="exec-head-actions">
                    <button type="button" onclick="closeExecutiveDashboard()" class="exec-control" aria-label="Cerrar el resumen ejecutivo y volver al panel anterior"><span aria-hidden="true">×</span> Cerrar</button>
                    <button type="button" class="exec-control" aria-label="Periodo mostrado: últimos 30 días">Últimos 30 días <span aria-hidden="true">▣</span></button>
                    <button id="exec-export-button" type="button" onclick="exportExecutiveDashboard()" class="exec-control" aria-label="Exportar resumen ejecutivo en PDF"><span aria-hidden="true">⇩</span> Exportar PDF</button>
                  </div>
                </header>
                <section class="exec-kpis">
                  <button type="button" onclick="openExecutiveDestination('offers')" class="exec-card exec-kpi exec-kpi-blue" aria-label="Acceder a captaciones publicadas"><div class="exec-kpi-top"><span class="exec-icon">▥</span><div><span class="exec-kpi-label">Captaciones publicadas</span><strong id="exec-kpi-offers">291</strong></div></div><p id="exec-kpi-offers-value" class="exec-kpi-value">74.998.480 € estimados</p><p class="exec-trend"><b>↑ 12%</b> vs mes anterior</p><span class="exec-card-cta">Acceder a captaciones →</span></button>
                  <button type="button" onclick="openExecutiveDestination('demands')" class="exec-card exec-kpi exec-kpi-green" aria-label="Acceder a demandas activas"><div class="exec-kpi-top"><span class="exec-icon">⌘</span><div><span class="exec-kpi-label">Demandas activas</span><strong id="exec-kpi-demands">34</strong></div></div><p id="exec-kpi-demands-value" class="exec-kpi-value">21.735.000 € estimados</p><p class="exec-trend"><b>↑ 8%</b> vs mes anterior</p><span class="exec-card-cta">Acceder a demandas →</span></button>
                  <button type="button" onclick="openExecutiveDestination('matches')" class="exec-card exec-kpi exec-kpi-yellow" aria-label="Ver coincidencias"><div class="exec-kpi-top"><span class="exec-icon">◎</span><div><span class="exec-kpi-label">Coincidencias</span><strong id="exec-kpi-matches">1</strong></div></div><p id="exec-kpi-matches-value" class="exec-kpi-value">240.000 € estimados</p><p class="exec-trend neutral"><b>— 0%</b> vs mes anterior</p><span class="exec-card-cta">Ver coincidencias →</span></button>
                  <button type="button" onclick="openExecutiveDestination('operations')" class="exec-card exec-kpi exec-kpi-violet" aria-label="Ver operaciones en curso"><div class="exec-kpi-top"><span class="exec-icon">◇</span><div><span class="exec-kpi-label">Operaciones en curso</span><strong id="exec-kpi-operations">4</strong></div></div><p id="exec-kpi-operations-value" class="exec-kpi-value">4.210.000 € estimados</p><p class="exec-trend"><b>↑ 33%</b> vs mes anterior</p><span class="exec-card-cta">Ver operaciones →</span></button>
                  <button type="button" onclick="openExecutiveDestination('operations')" class="exec-card exec-pipeline exec-clickable" aria-label="Acceder al pipeline de operaciones"><p class="exec-pipeline-label">Valor total del pipeline</p><strong id="exec-pipeline-value">104.280.000 €</strong><svg class="exec-sparkline" viewBox="0 0 250 60" preserveAspectRatio="none" aria-label="Evolución del pipeline"><defs><linearGradient id="execSparkGradient" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#4c83ff" stop-opacity=".38"/><stop offset="1" stop-color="#4c83ff" stop-opacity="0"/></linearGradient></defs><path class="area" d="M3,52 L35,31 L68,38 L99,18 L130,33 L159,12 L190,29 L220,15 L247,4 L247,58 L3,58 Z"/><path class="line" d="M3,52 L35,31 L68,38 L99,18 L130,33 L159,12 L190,29 L220,15 L247,4"/><g fill="#4c83ff"><circle cx="3" cy="52" r="3"/><circle cx="35" cy="31" r="3"/><circle cx="68" cy="38" r="3"/><circle cx="99" cy="18" r="3"/><circle cx="130" cy="33" r="3"/><circle cx="159" cy="12" r="3"/><circle cx="190" cy="29" r="3"/><circle cx="220" cy="15" r="3"/><circle cx="247" cy="4" r="4"/></g></svg><div class="exec-months"><span>Ene</span><span>Feb</span><span>Mar</span><span>Abr</span><span>May</span><span>Jun</span></div><span class="exec-card-cta">Ver pipeline →</span></button>
                </section>
                <section class="exec-central">
                  <article class="exec-card exec-panel"><h4 class="exec-panel-title">Distribución general</h4><div class="exec-distribution"><div class="exec-donut"><svg class="exec-donut-svg" viewBox="0 0 100 100" role="group" aria-label="Distribución general interactiva"><circle tabindex="0" role="link" onclick="openExecutiveDestination('offers')" onkeydown="activateExecutiveKey(event,'offers')" aria-label="Captaciones, 72 por ciento" class="exec-donut-segment" cx="50" cy="50" r="38" pathLength="100" stroke="#3d78f4" stroke-dasharray="72 28" stroke-dashoffset="0"/><circle tabindex="0" role="link" onclick="openExecutiveDestination('demands')" onkeydown="activateExecutiveKey(event,'demands')" aria-label="Demandas, 18 por ciento" class="exec-donut-segment" cx="50" cy="50" r="38" pathLength="100" stroke="#32bd83" stroke-dasharray="18 82" stroke-dashoffset="-72"/><circle tabindex="0" role="link" onclick="openExecutiveDestination('requests')" onkeydown="activateExecutiveKey(event,'requests')" aria-label="Solicitudes, 8 por ciento" class="exec-donut-segment" cx="50" cy="50" r="38" pathLength="100" stroke="#f0b91c" stroke-dasharray="8 92" stroke-dashoffset="-90"/><circle tabindex="0" role="link" onclick="openExecutiveDestination('matches')" onkeydown="activateExecutiveKey(event,'matches')" aria-label="Coincidencias, 2 por ciento" class="exec-donut-segment" cx="50" cy="50" r="38" pathLength="100" stroke="#7247e8" stroke-dasharray="2 98" stroke-dashoffset="-98"/></svg><span class="exec-donut-hole" aria-hidden="true"></span><div class="exec-donut-center"><strong id="exec-total-opportunities">326</strong><span>Total oportunidades</span></div></div><div class="exec-legend"><button type="button" onclick="openExecutiveDestination('offers')" class="exec-legend-row" aria-label="Acceder a captaciones, 72 por ciento"><i class="exec-dot" style="background:#3d78f4;color:#3d78f4"></i><span>Captaciones</span><b>72% <small id="exec-legend-offers">(291)</small></b></button><button type="button" onclick="openExecutiveDestination('demands')" class="exec-legend-row" aria-label="Acceder a demandas, 18 por ciento"><i class="exec-dot" style="background:#32bd83;color:#32bd83"></i><span>Demandas</span><b>18% <small id="exec-legend-demands">(34)</small></b></button><button type="button" onclick="openExecutiveDestination('requests')" class="exec-legend-row" aria-label="Acceder a solicitudes, 8 por ciento"><i class="exec-dot" style="background:#f0b91c;color:#f0b91c"></i><span>Solicitudes</span><b>8% <small id="exec-legend-requests">(2)</small></b></button><button type="button" onclick="openExecutiveDestination('matches')" class="exec-legend-row" aria-label="Acceder a coincidencias, 2 por ciento"><i class="exec-dot" style="background:#7247e8;color:#7247e8"></i><span>Coincidencias</span><b>2% <small id="exec-legend-matches">(1)</small></b></button></div></div></article>
                  <article class="exec-card exec-panel"><h4 class="exec-panel-title">Embudo comercial</h4><div class="exec-funnel-grid"><div class="exec-funnel"><button type="button" onclick="openExecutiveDestination('offers')" class="exec-funnel-step" aria-label="Acceder a captaciones publicadas"></button><button type="button" onclick="openExecutiveDestination('requests')" class="exec-funnel-step" aria-label="Acceder a solicitudes recibidas"></button><button type="button" onclick="openExecutiveDestination('matches')" class="exec-funnel-step" aria-label="Acceder a coincidencias"></button><button type="button" onclick="openExecutiveDestination('operations')" class="exec-funnel-step" aria-label="Acceder a operaciones en curso"></button><button type="button" onclick="openExecutiveDestination('operations-closed')" class="exec-funnel-step" aria-label="Acceder a operaciones cerradas"></button></div><table class="exec-funnel-table"><tbody><tr><td><button type="button" onclick="openExecutiveDestination('offers')" aria-label="Acceder a captaciones publicadas">🔵 Captaciones publicadas</button></td><td id="exec-funnel-offers">291</td><td>100%</td></tr><tr><td><button type="button" onclick="openExecutiveDestination('requests')" aria-label="Acceder a solicitudes recibidas">🟢 Solicitudes recibidas</button></td><td id="exec-funnel-requests">2</td><td>0,7%</td></tr><tr><td><button type="button" onclick="openExecutiveDestination('matches')" aria-label="Acceder a coincidencias">🟡 Coincidencias</button></td><td id="exec-funnel-matches">1</td><td>0,3%</td></tr><tr><td><button type="button" onclick="openExecutiveDestination('operations')" aria-label="Acceder a operaciones en curso">🟣 Operaciones en curso</button></td><td id="exec-funnel-operations">4</td><td>1,4%</td></tr><tr><td><button type="button" onclick="openExecutiveDestination('operations-closed')" aria-label="Acceder a operaciones cerradas">🔴 Operaciones cerradas</button></td><td id="exec-funnel-closed">6</td><td>2,1%</td></tr></tbody></table></div></article>
                </section>
                <section class="exec-lower">
                  <article class="exec-card exec-list-card"><div class="exec-list-head"><h4>Últimas solicitudes</h4><button onclick="switchPrivateDashboardPanel('requests')">Ver todas</button></div><div id="exec-latest-requests"></div></article>
                  <article class="exec-card exec-list-card"><div class="exec-list-head"><h4>Últimas coincidencias</h4><button onclick="openExecutiveDestination('matches')" aria-label="Ver todas las coincidencias">Ver todas</button></div><div id="exec-latest-matches"></div></article>
                  <article class="exec-card exec-list-card"><div class="exec-list-head"><h4>Tareas pendientes</h4><button onclick="switchPrivateDashboardPanel('tasks')">Ver todas</button></div><div id="exec-pending-tasks"></div></article>
                </section>
                <section class="exec-card exec-summary">
                  <button type="button" onclick="openExecutiveDestination('requests')" class="exec-summary-item exec-clickable" aria-label="Ver solicitudes recibidas"><span class="exec-summary-icon" style="color:#f0b91c;background:rgba(240,185,28,.12)">⌁</span><div class="exec-summary-copy"><span>Solicitudes recibidas</span><strong id="exec-requests-count">2</strong><span class="exec-card-cta">Ver solicitudes →</span></div></button>
                  <button type="button" onclick="openExecutiveDestination('notifications')" class="exec-summary-item exec-clickable" aria-label="Ver avisos sin leer"><span class="exec-summary-icon" style="color:#f05a9a;background:rgba(240,90,154,.12)">✉</span><div class="exec-summary-copy"><span>Avisos sin leer</span><strong id="exec-unread-count">6</strong><span class="exec-card-cta">Ver avisos →</span></div></button>
                  <button type="button" onclick="openExecutiveDestination('favorites')" class="exec-summary-item exec-clickable" aria-label="Ver favoritos"><span class="exec-summary-icon" style="color:#f43f5e;background:rgba(244,63,94,.12)">♥</span><div class="exec-summary-copy"><span>Favoritos</span><strong id="exec-favorites-count">0</strong><span class="exec-card-cta">Ver favoritos →</span></div></button>
                  <button type="button" onclick="openExecutiveDestination('clients')" class="exec-summary-item exec-clickable" aria-label="Ver clientes asignados"><span class="exec-summary-icon">♙</span><div class="exec-summary-copy"><span>Clientes asignados</span><strong id="exec-clients-count">3</strong><span class="exec-card-cta">Ver clientes →</span></div></button>
                  <button type="button" onclick="openExecutiveDestination('leads')" class="exec-summary-item exec-clickable" aria-label="Ver leads activos"><span class="exec-summary-icon" style="color:#6ce39b;background:rgba(49,190,119,.12)">♟</span><div class="exec-summary-copy"><span>Leads activos</span><strong id="exec-leads-count">3</strong><span class="exec-card-cta">Ver leads →</span></div></button>
                  <button type="button" onclick="openExecutiveDestination('tasks')" class="exec-summary-item exec-clickable" aria-label="Ver tareas pendientes"><span class="exec-summary-icon" style="color:#8bb5ff;background:rgba(61,120,244,.12)">✓</span><div class="exec-summary-copy"><span>Tareas pendientes</span><strong id="exec-tasks-count">0</strong><span class="exec-card-cta">Ver tareas →</span></div></button>
                </section>
              </div>
              <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-3 mb-5">
                <div><h3 class="text-xl font-black text-navy">Resumen ejecutivo</h3><p class="text-xs text-slate-500 mt-1">Prioriza coincidencias, solicitudes y próximas acciones.</p></div>
                <div class="inline-flex rounded-xl border border-slate-200 bg-white p-1 text-xs font-bold">
                  <button type="button" id="private-view-general" onclick="setPrivateDashboardFocus('general')" class="px-3 py-2 rounded-lg bg-navy text-white">Vista general</button>
                  <button type="button" id="private-view-offers" onclick="setPrivateDashboardFocus('offers')" class="px-3 py-2 rounded-lg text-slate-500">Ofrezco captación</button>
                  <button type="button" id="private-view-demands" onclick="setPrivateDashboardFocus('demands')" class="px-3 py-2 rounded-lg text-slate-500">Busco captación</button>
                </div>
              </div>
              <div id="private-dashboard-kpis" class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-6 gap-3 mb-6"></div>
              <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">
                <section class="private-section-card p-5"><h4 class="text-sm font-black text-navy">Mis accesos disponibles</h4><div id="private-access-summary" class="mt-4"></div></section>
                <section class="private-section-card p-5"><h4 class="text-sm font-black text-navy">Actividad del mes</h4><div id="private-month-activity" class="mt-4 grid grid-cols-3 gap-3"></div></section>
              </div>
              <section class="private-section-card overflow-hidden mb-6"><div class="px-5 py-4 border-b border-slate-200"><h4 class="text-sm font-black text-navy">Historial de accesos</h4></div><div id="private-access-history" class="overflow-x-auto"></div></section>
              <div class="grid grid-cols-1 xl:grid-cols-[1.15fr_.85fr] gap-5 mb-6">
                <section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><div><h4 class="text-sm font-black text-navy">Requiere tu atención</h4><p class="text-[11px] text-slate-500 mt-1">Acciones ordenadas por prioridad</p></div><button onclick="switchPrivateDashboardPanel('tasks')" class="text-[11px] font-bold text-blue">Ver todas →</button></div><div id="private-attention-list" class="divide-y divide-slate-100"></div></section>
                <section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200"><h4 class="text-sm font-black text-navy">Próximas acciones</h4><p class="text-[11px] text-slate-500 mt-1">Tu agenda operativa inmediata</p></div><div id="private-overview-tasks" class="p-4 space-y-3"></div></section>
              </div>
              <section id="private-overview-calendar-section" class="private-section-card overflow-hidden mb-6"><div class="px-5 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3"><div><h4 class="text-sm font-black text-navy">Almanaque operativo</h4><p class="text-[11px] text-slate-500 mt-1">Tareas y alertas ordenadas por fecha.</p></div><button onclick="switchPrivateDashboardPanel('tasks')" class="text-[11px] font-bold text-blue">Abrir agenda →</button></div><div class="grid grid-cols-1 xl:grid-cols-[1fr_.8fr] gap-4 p-4"><div id="private-overview-calendar"></div><div id="private-overview-calendar-events" class="space-y-3"></div></div></section>
              <section class="private-section-card overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                  <div><h4 class="text-sm font-black text-navy">Coincidencias recomendadas</h4><p class="text-[11px] text-slate-500 mt-1">Cruce automático entre oferta y demanda</p></div>
                  <div class="inline-flex rounded-xl bg-slate-100 p-1 text-[11px] font-bold"><button id="private-match-offers-tab" onclick="setPrivateMatchesMode('offers')" class="px-3 py-2 rounded-lg bg-white text-navy shadow-sm">Mis captaciones con demanda</button><button id="private-match-demands-tab" onclick="setPrivateMatchesMode('demands')" class="px-3 py-2 rounded-lg text-slate-500">Mis demandas con oferta</button></div>
                </div>
                <div id="private-matches-list" class="grid grid-cols-1 lg:grid-cols-2 gap-4 p-4"></div>
              </section>
              <div class="grid grid-cols-1 xl:grid-cols-[1.25fr_.75fr] gap-5 mb-6">
                <section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h4 class="text-sm font-black text-navy">Operaciones recientes</h4><button onclick="switchPrivateDashboardPanel('operations')" class="text-[11px] font-bold text-blue">Gestionar →</button></div><div class="overflow-x-auto"><table class="private-table w-full"><thead><tr><th class="px-4 py-3">Operación</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Próxima acción</th><th class="px-4 py-3"></th></tr></thead><tbody id="private-overview-operations"></tbody></table></div></section>
                <section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h4 class="text-sm font-black text-navy">Actividad reciente</h4><button onclick="switchPrivateDashboardPanel('notifications')" class="text-[11px] font-bold text-blue">Avisos →</button></div><div id="private-overview-activity" class="p-4 space-y-3"></div></section>
              </div>
              <section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h4 class="text-sm font-black text-navy">Favoritos recientes</h4><button onclick="switchPrivateDashboardPanel('favorites')" class="text-[11px] font-bold text-blue">Ver favoritos →</button></div><div id="private-overview-favorites" class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4"></div></section>
            </div>

            <!-- OFREZCO CAPTACIÓN -->
            <div id="private-panel-offers" class="private-dashboard-panel">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5"><div><h3 class="text-xl font-black text-navy">Ofrezco captación</h3><p class="text-xs text-slate-500 mt-1">Inventario aportado, solicitudes y coincidencias activas.</p></div><a href="#/ofrecer-captacion" class="px-4 py-3 rounded-xl bg-blue text-white text-xs font-bold">+ Nueva captación</a></div>
              <div id="private-offers-summary" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5"></div>
              <div class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex flex-wrap gap-3 items-center justify-between"><h4 class="text-sm font-black text-navy">Mis captaciones</h4><input id="private-offers-search" oninput="renderPrivateOffers()" placeholder="Buscar referencia, título o zona" class="px-3 py-2 rounded-xl border border-slate-200 text-xs min-w-[240px]" /></div><div class="overflow-x-auto"><table class="private-table w-full"><thead><tr><th class="px-4 py-3">Ref.</th><th class="px-4 py-3">Propiedad</th><th class="px-4 py-3">Precio</th><th class="px-4 py-3">Score</th><th class="px-4 py-3">Coincidencias</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3"></th></tr></thead><tbody id="private-offers-table"></tbody></table></div></div>
            </div>

            <!-- BUSCO CAPTACIÓN -->
            <div id="private-panel-demands" class="private-dashboard-panel">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5"><div><h3 class="text-xl font-black text-navy">Busco captación</h3><p class="text-xs text-slate-500 mt-1">Demandas registradas y oportunidades compatibles.</p></div><a href="#/buscar-captaciones" class="px-4 py-3 rounded-xl bg-navy text-white text-xs font-bold">+ Nueva demanda</a></div>
              <div id="private-demands-summary" class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5"></div>
              <div class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex flex-wrap gap-3 items-center justify-between"><h4 class="text-sm font-black text-navy">Mis demandas</h4><input id="private-demands-search" oninput="renderPrivateDemands()" placeholder="Buscar intención, referencia o zona" class="px-3 py-2 rounded-xl border border-slate-200 text-xs min-w-[240px]" /></div><div class="overflow-x-auto"><table class="private-table w-full"><thead><tr><th class="px-4 py-3">Ref.</th><th class="px-4 py-3">Intención</th><th class="px-4 py-3">Presupuesto</th><th class="px-4 py-3">Coincidencias</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3"></th></tr></thead><tbody id="private-demands-table"></tbody></table></div></div>
            </div>

            <!-- SOLICITUDES -->
            <div id="private-panel-requests" class="private-dashboard-panel"><div class="mb-5"><h3 class="text-xl font-black text-navy">Solicitudes de información</h3><p class="text-xs text-slate-500 mt-1">Confirma disponibilidad y controla el acceso protegido a las captaciones.</p></div><div class="grid grid-cols-1 xl:grid-cols-2 gap-5"><section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200"><h4 class="text-sm font-black text-navy">Solicitudes recibidas</h4></div><div id="private-requests-received" class="p-4 space-y-3"></div></section><section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200"><h4 class="text-sm font-black text-navy">Solicitudes enviadas</h4></div><div id="private-requests-sent" class="p-4 space-y-3"></div></section></div></div>

            <!-- OPERACIONES -->
            <div id="private-panel-operations" class="private-dashboard-panel"><div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5"><div><h3 class="text-xl font-black text-navy">Operaciones</h3><p class="text-xs text-slate-500 mt-1">Expedientes, estados y próximas acciones.</p></div><select id="private-operation-status-filter" onchange="renderPrivateOperations()" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs bg-white"><option value="">Todos los estados</option><option>Nueva</option><option>Confirmación pendiente</option><option>Acuerdo de Confidencialidad (NDA) pendiente</option><option>Pago pendiente</option><option>Datos desbloqueados</option><option>En negociación</option><option>Reserva realizada</option><option>Documentación pendiente</option><option>Completada</option><option>Cancelada</option></select></div><div class="private-section-card overflow-hidden"><div class="overflow-x-auto"><table class="private-table w-full"><thead><tr><th class="px-4 py-3">Operación</th><th class="px-4 py-3">Propiedad / demanda</th><th class="px-4 py-3">Colaborador</th><th class="px-4 py-3">Estado</th><th class="px-4 py-3">Actualización</th><th class="px-4 py-3">Próxima acción</th><th class="px-4 py-3"></th></tr></thead><tbody id="private-operations-table"></tbody></table></div></div></div>

            <!-- FAVORITOS -->
            <div id="private-panel-favorites" class="private-dashboard-panel"><div class="mb-5"><h3 class="text-xl font-black text-navy">Mis favoritos</h3><p class="text-xs text-slate-500 mt-1">Demandas, captaciones y coincidencias guardadas para revisarlas desde un único lugar.</p></div><div id="private-favorites-grid" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5"></div></div>

            <!-- TAREAS -->
            <div id="private-panel-tasks" class="private-dashboard-panel"><div class="mb-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3"><div><h3 class="text-xl font-black text-navy">Calendario y tareas</h3><p class="text-xs text-slate-500 mt-1">Módulo Premium para agenda, recordatorios y calendarios compatibles.</p></div><div class="flex flex-wrap gap-2"><button onclick="linkExternalCalendar()" class="px-4 py-3 rounded-xl border border-slate-200 bg-white text-blue text-xs font-bold">Vincular calendario</button><button onclick="openNewTaskModal()" class="px-4 py-3 rounded-xl bg-blue text-white text-xs font-bold">Añadir nueva tarea</button></div></div><div id="private-tasks-premium-content" class="grid grid-cols-1 xl:grid-cols-[.95fr_1.05fr] gap-5"><section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between gap-3"><div><h4 class="text-sm font-black text-navy">Calendario de pendientes</h4><p class="text-[11px] text-slate-500 mt-1">Visualiza tareas y alertas por fecha.</p></div><button onclick="exportPrivateAgendaCalendar()" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-blue">Exportar ICS</button></div><div class="p-4"><div id="private-tasks-calendar"></div><div id="private-tasks-calendar-events" class="mt-4 space-y-3"></div></div></section><div id="private-tasks-list" class="space-y-3"></div></div><div id="private-tasks-premium-lock" class="hidden private-section-card p-8 text-center"><h4 class="text-lg font-black text-navy">Calendario avanzado incluido en Premium</h4><p class="text-sm text-slate-500 mt-2">Activa Premium para crear tareas, preparar notificaciones y vincular calendarios externos.</p><a href="#/planes" class="inline-flex mt-5 px-5 py-3 rounded-xl bg-blue text-white text-xs font-black">Ver plan Premium</a></div></div>

            <!-- NOTIFICACIONES -->
            <div id="private-panel-notifications" class="private-dashboard-panel"><div class="mb-5"><h3 class="text-xl font-black text-navy">Notificaciones</h3><p class="text-xs text-slate-500 mt-1">Oportunidades, operaciones, avisos administrativos y sistema.</p></div><div class="flex flex-wrap gap-2 mb-4"><button onclick="markAllPrivateNotificationsRead()" class="px-4 py-2.5 rounded-xl bg-navy text-white text-xs font-bold">Marcar todas como leídas</button></div><div id="private-notifications-list" class="space-y-3"></div></div>

            <!-- FEEDS XML -->

            <div id="private-panel-subscriptions" class="private-dashboard-panel">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5">
                <div><h3 class="text-xl font-black text-navy">Suscripciones y alertas multicanal</h3><p class="text-xs text-slate-500 mt-1">Recibe coincidencias por plataforma, email y WhatsApp sin revelar datos de contacto entre profesionales.</p></div>
                <button type="button" onclick="simulateProtectedMatchNotification()" class="px-4 py-3 rounded-xl bg-blue text-white text-xs font-bold shadow-sm">Simular nueva coincidencia</button>
              </div>
              <div id="private-comm-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5"></div>
              <div class="grid grid-cols-1 xl:grid-cols-[.85fr_1.15fr] gap-5">
                <section class="private-section-card p-5">
                  <h4 class="text-sm font-black text-navy">Preferencias de notificación</h4>
                  <p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Los canales externos solo envían avisos. La conversación y los documentos permanecen dentro de Captacion.app.</p>
                  <div class="mt-4 space-y-3 text-xs">
                    <label class="flex items-center justify-between gap-4"><span>Notificaciones dentro de la plataforma</span><input id="comm-pref-inapp" type="checkbox" onchange="saveCommunicationPreferences()" class="w-4 h-4" /></label>
                    <label class="flex items-center justify-between gap-4"><span>Avisos operativos por email</span><input id="comm-pref-email" type="checkbox" onchange="saveCommunicationPreferences()" class="w-4 h-4" /></label>
                    <label class="flex items-center justify-between gap-4"><span>Avisos operativos por WhatsApp</span><input id="comm-pref-whatsapp" type="checkbox" onchange="saveCommunicationPreferences()" class="w-4 h-4" /></label>
                    <label class="block"><span class="block mb-2">Frecuencia predeterminada</span><select id="comm-pref-frequency" onchange="saveCommunicationPreferences()" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 bg-white text-xs"><option value="instant">Inmediata</option><option value="daily">Resumen diario</option><option value="weekly">Resumen semanal</option></select></label>
                  </div>
                  <div class="comm-safe-banner mt-4"><strong class="block text-xs text-green">✓ Comunicación protegida</strong><p class="text-[11px] text-slate-500 mt-1 leading-relaxed">Email y WhatsApp conducen siempre a una pantalla segura. No incluyen nombre, teléfono, email ni dirección exacta de la contraparte.</p></div>
                </section>
                <section class="private-section-card overflow-hidden">
                  <div class="px-5 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3"><div><h4 class="text-sm font-black text-navy">Mis demandas suscritas</h4><p class="text-[11px] text-slate-500 mt-1">Alertas configuradas para búsquedas activas.</p></div><div class="flex gap-2"><select id="comm-demand-select" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs max-w-[230px]"></select><button onclick="subscribeSelectedDemand()" class="px-3 py-2 rounded-xl bg-navy text-white text-xs font-bold">Añadir</button></div></div>
                  <div class="overflow-x-auto"><table class="comm-table w-full"><thead><tr><th class="px-4 py-3 text-left">Demanda</th><th class="px-4 py-3 text-left">Coincidencias</th><th class="px-4 py-3 text-left">Canales</th><th class="px-4 py-3 text-left">Frecuencia</th><th class="px-4 py-3 text-left">Estado</th><th class="px-4 py-3"></th></tr></thead><tbody id="comm-subscriptions-table"></tbody></table></div>
                </section>
              </div>
              <section class="private-section-card overflow-hidden mt-5"><div class="px-5 py-4 border-b border-slate-200"><h4 class="text-sm font-black text-navy">Historial de envíos operativos</h4><p class="text-[11px] text-slate-500 mt-1">Trazabilidad de avisos generados por coincidencias, solicitudes y operaciones.</p></div><div id="comm-deliveries-list" class="p-4 space-y-3"></div></section>
            </div>

            <div id="private-panel-communications" class="private-dashboard-panel">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5"><div><h3 class="text-xl font-black text-navy">Comunicación interna protegida</h3><p class="text-xs text-slate-500 mt-1">Salas privadas con historial, control de estados y bloqueo de datos de contacto prematuros.</p></div><button onclick="switchPrivateDashboardPanel('subscriptions')" class="px-4 py-3 rounded-xl border border-slate-200 text-xs font-bold text-navy">Gestionar alertas</button></div>
              <div class="comm-safe-banner mb-5"><strong class="block text-sm text-green">La plataforma actúa como canal único de comunicación</strong><p class="text-[11px] text-slate-500 mt-1 leading-relaxed">No compartas teléfonos, emails, enlaces externos ni direcciones exactas antes de completar el flujo protegido. Los intentos quedan registrados para preservar la trazabilidad.</p></div>
              <div id="comm-threads-list" class="grid grid-cols-1 lg:grid-cols-2 gap-4"></div>
            </div>

            <div id="private-panel-traceability" class="private-dashboard-panel">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-5"><div><h3 class="text-xl font-black text-navy">Trazabilidad de procesos</h3><p class="text-xs text-slate-500 mt-1">Registro cronológico de coincidencias, alertas, mensajes, Acuerdo de Confidencialidad (NDA), pagos y accesos protegidos.</p></div><button onclick="exportCommunicationTrace()" class="px-4 py-3 rounded-xl bg-navy text-white text-xs font-bold">Exportar trazabilidad JSON</button></div>
              <div class="grid grid-cols-1 xl:grid-cols-[.78fr_1.22fr] gap-5">
                <section class="private-section-card p-5"><h4 class="text-sm font-black text-navy">Principios aplicados</h4><div class="mt-4 space-y-3 text-[11px] text-slate-500 leading-relaxed"><p>✓ Identidad de la contraparte oculta antes de la autorización.</p><p>✓ Email y WhatsApp funcionan como avisos, no como canal de contacto directo.</p><p>✓ Los mensajes quedan asociados a una referencia interna.</p><p>✓ El desbloqueo requiere el Acuerdo de Confidencialidad (NDA) y el pago configurado.</p><p>✓ Cada cambio relevante genera un evento de auditoría.</p></div></section>
                <section class="private-section-card overflow-hidden"><div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between"><h4 class="text-sm font-black text-navy">Registro de actividad protegido</h4><select id="comm-trace-filter" onchange="renderCommunicationTrace()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-xs"><option value="">Todos</option><option value="MATCH">Coincidencias</option><option value="MESSAGE">Mensajes</option><option value="FLOW">Flujo protegido</option><option value="NOTIFICATION">Notificaciones</option><option value="SECURITY">Seguridad</option></select></div><div id="comm-trace-list" class="p-5 space-y-4 max-h-[640px] overflow-y-auto"></div></section>
              </div>
            </div>

            <div id="private-panel-feeds" class="private-dashboard-panel">
              <div class="mb-5"><h3 class="text-xl font-black text-navy">Feeds XML</h3><p class="text-xs text-slate-500 mt-1">Importa, actualiza y elimina inventario externo.</p></div>
              <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-6">
                <div class="grid grid-cols-1 xl:grid-cols-[1fr_auto] gap-4 items-end">
                  <div>
                    <label for="private-xml-url" class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">URL del fichero XML</label>
                    <input id="private-xml-url" type="url" placeholder="https://dominio.es/feed-inmuebles.xml" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue/20" />
                  </div>
                  <button id="private-xml-save-btn" type="button" onclick="savePrivateXmlUrl()" class="px-5 py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md transition-all">Guardar e importar URL</button>
                </div>
                <div class="mt-5 grid grid-cols-1 xl:grid-cols-[1fr_auto_auto] gap-4 items-end">
                  <div>
                    <label for="private-feed-xml-file-name" class="block text-xs font-black uppercase tracking-wider text-slate-500 mb-2">Archivo XML local</label>
                    <input id="private-feed-xml-file-name" type="text" readonly placeholder="Selecciona un archivo XML desde tu equipo" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm" />
                    <input id="private-feed-xml-file" type="file" accept=".xml,application/xml,text/xml" class="hidden" onchange="handleFeedXmlFileSelected()" />
                  </div>
                  <button type="button" onclick="chooseFeedXmlFile()" class="px-5 py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-navy text-xs font-black shadow-sm">Explorar</button>
                  <button id="private-feed-xml-import-btn" type="button" onclick="importFeedXmlFile()" class="px-5 py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md">Importar XML</button>
                </div>
                <div id="private-feed-xml-import-result" class="mt-3 text-xs hidden"></div>
                <div class="mt-6 border-t border-slate-200 pt-5">
                  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3">
                    <h4 class="text-sm font-black text-navy">XML subidos</h4>
                    <button type="button" onclick="loadImportBatches()" class="px-3 py-2 rounded-xl border border-slate-200 bg-white text-blue text-[10px] font-black">Actualizar lista</button>
                  </div>
                  <div id="private-feed-import-batches-list" class="space-y-2"><p class="text-xs text-slate-400">Cargando XML subidos...</p></div>
                </div>
              </div>
            </div>
            <!-- DATOS Y PRIVACIDAD -->
            <div id="private-panel-data" class="private-dashboard-panel">
              <div class="mb-5"><h3 class="text-xl font-black text-navy">Datos y privacidad</h3><p class="text-xs text-slate-500 mt-1">Importa, exporta y administra tus datos personales según RGPD.</p></div>
              <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-6">
                <h4 class="text-sm font-black text-navy mb-3">Importar XML privado</h4>
                <p class="text-xs text-slate-500 mb-4">Selecciona un archivo XML con tus captaciones y demandas. Los datos se asignarán a tu usuario.</p>
                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                  <div class="flex-grow w-full">
                    <input id="private-data-xml-file" type="file" accept=".xml" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" />
                  </div>
                  <button type="button" onclick="importPrivateUserXml()" class="px-5 py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md shrink-0">Importar XML</button>
                </div>
                <div id="private-xml-import-result" class="mt-3 text-xs hidden"></div>
              </div>
              <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <div>
                    <h4 class="text-sm font-black text-navy">Exportar mis datos</h4>
                    <p class="text-xs text-slate-500 mt-1">Descarga un XML con todos tus registros privados.</p>
                  </div>
                  <button type="button" onclick="exportMyPrivateData()" class="px-5 py-3 rounded-xl bg-navy hover:bg-navy-light text-white text-xs font-black shadow-md text-center">Exportar XML</button>
                </div>
              </div>
              <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-6">
                <h4 class="text-sm font-black text-navy mb-3">Mis lotes importados</h4>
                <div id="private-import-batches-list" class="space-y-2">
                  <p class="text-xs text-slate-400">Cargando...</p>
                </div>
              </div>
              <div class="bg-white rounded-2xl border border-red-50 shadow-sm p-5 sm:p-6 border border-red-200">
                <h4 class="text-sm font-black text-red-600 mb-3">Zona de peligro</h4>
                <p class="text-xs text-slate-500 mb-4">Elimina todos tus datos privados de la plataforma. Esta acción no se puede deshacer.</p>
                <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                  <div class="flex-grow w-full">
                    <input id="private-delete-confirm-input" type="text" placeholder="Escribe CONFIRMAR para eliminar" class="w-full px-4 py-3 rounded-xl border border-red-200 text-sm" />
                  </div>
                  <button type="button" onclick="deleteAllMyPrivateData()" class="px-5 py-3 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-black shadow-md shrink-0">Eliminar mis datos</button>
                </div>
                <div id="private-delete-result" class="mt-3 text-xs hidden"></div>
              </div>
            </div>

            <!-- IA -->
            <div id="private-panel-ai" class="private-dashboard-panel">
              <div class="mb-5"><h3 class="text-xl font-black text-navy">Configuración IA</h3><p class="text-xs text-slate-500 mt-1">Conecta tu propio proveedor para activar funciones asistidas sin que la plataforma asuma el coste variable de tus consultas.</p></div>
              <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6"><div class="flex flex-col xl:flex-row xl:items-start justify-between gap-5"><div class="max-w-4xl"><span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase tracking-wider">Bring your own AI</span><h3 class="text-lg font-black text-navy mt-3">Conecta tu proveedor de inteligencia artificial</h3><p class="text-xs text-slate-500 mt-2 leading-relaxed">Tus credenciales se usan solo para tus solicitudes. La API key se envía al backend y se guarda cifrada para tu usuario de WordPress.</p><p class="text-[11px] text-slate-500 mt-2 leading-relaxed">Puedes usar esta conexión para redactar captaciones, resumir demandas, analizar encajes y lanzar asistentes especializados.</p></div><button type="button" onclick="openAIConnectionModal()" class="shrink-0 px-5 py-3 rounded-xl bg-gradient-to-r from-blue to-purple-600 hover:opacity-90 text-white text-xs font-bold shadow-md">Conectar IA</button></div><div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-3 mt-5"><div class="ai-provider-chip"><strong class="block text-sm text-navy">OpenAI</strong><span class="text-[10px] text-slate-500">GPT y tareas generales</span></div><div class="ai-provider-chip"><strong class="block text-sm text-navy">Anthropic</strong><span class="text-[10px] text-slate-500">Lectura y síntesis</span></div><div class="ai-provider-chip"><strong class="block text-sm text-navy">Google</strong><span class="text-[10px] text-slate-500">Gemini</span></div><div class="ai-provider-chip"><strong class="block text-sm text-navy">Groq</strong><span class="text-[10px] text-slate-500">Alta velocidad</span></div><div class="ai-provider-chip"><strong class="block text-sm text-navy">OpenRouter</strong><span class="text-[10px] text-slate-500">Catálogo amplio</span></div><div class="ai-provider-chip"><strong class="block text-sm text-navy">Compatible</strong><span class="text-[10px] text-slate-500">Endpoint propio</span></div></div><div id="ai-connections-list" class="mt-5 space-y-3"></div></div>
            </div>

            <!-- PERFIL -->
            <div id="private-panel-profile" class="private-dashboard-panel">
              <div class="mb-5"><h3 class="text-xl font-black text-navy">Perfil profesional</h3><p class="text-xs text-slate-500 mt-1">Datos profesionales y fiscales privados para colaborar y gestionar la facturación entre partes autorizadas.</p></div>
              <div id="professional-profile-progress-notice" class="p-4 mb-5 rounded-2xl border border-green/20 bg-green-light text-xs text-slate-600 leading-relaxed"><strong class="text-green">Completa tu perfil profesional para mejorar coincidencias y validaciones.</strong> La agencia, comunidad autonoma, provincia, municipio, codigo postal y zona profesional se solicitan aqui de forma progresiva, despues del alta.</div>
              <div class="p-4 mb-5 rounded-2xl border border-blue/20 bg-blue-light/40 text-xs text-slate-600 leading-relaxed"><strong class="text-navy">Información privada:</strong> Captacion.app no emite facturas entre profesionales. Estos datos sirven para que las partes implicadas en una operación puedan gestionar su facturación directamente.</div>
              <form id="private-fiscal-profile-form" onsubmit="savePrivateFiscalProfile(event)" class="private-section-card p-5 sm:p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                  <label class="block"><span class="private-field-label">Nombre y apellidos / Razón social</span><input id="fiscal-legal-name" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Nombre comercial</span><input id="fiscal-trade-name" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Tipo de perfil</span><select id="fiscal-profile-type" class="private-field-input"><option value="">Pendiente de completar</option><option value="autonomo">Autónomo</option><option value="empresa">Empresa</option><option value="agencia">Agencia</option><option value="colaborador">Colaborador</option></select></label>
                  <label class="block"><span class="private-field-label">DNI / NIE / NIF / CIF</span><input id="fiscal-tax-id" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Email de facturación</span><input id="fiscal-billing-email" type="email" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Teléfono profesional</span><input id="fiscal-phone" type="tel" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block md:col-span-2"><span class="private-field-label">Dirección fiscal completa</span><input id="fiscal-address" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Código postal</span><input id="fiscal-postal-code" inputmode="numeric" maxlength="5" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Comunidad autónoma</span><select id="fiscal-ccaa" class="private-field-input"></select></label>
                  <label class="block"><span class="private-field-label">Provincia</span><select id="fiscal-province" class="private-field-input" disabled></select></label>
                  <label class="block"><span class="private-field-label">Municipio</span><select id="fiscal-municipality" class="private-field-input" disabled></select></label>
                  <label class="block"><span class="private-field-label">País</span><input id="fiscal-country" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Actividad profesional</span><input id="fiscal-activity" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block"><span class="private-field-label">Web</span><input id="fiscal-website" type="url" class="private-field-input" placeholder="Pendiente de completar" /></label>
                  <label class="block md:col-span-2 xl:col-span-3"><span class="private-field-label">Observaciones fiscales o comerciales</span><textarea id="fiscal-notes" rows="4" class="private-field-input resize-none" placeholder="Pendiente de completar"></textarea></label>
                </div>
                <div class="mt-5 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between"><div><p id="fiscal-profile-status" class="text-[11px] text-slate-500">Los campos vacíos se mostrarán como “Pendiente de completar”.</p><p id="fiscal-address-validation-status" class="text-[10px] text-slate-500 mt-1"></p></div><div class="flex flex-wrap gap-2"><button type="button" onclick="validateFiscalAddress()" class="px-4 py-3 rounded-xl border border-blue/30 text-blue text-xs font-bold">Validar dirección</button><button type="submit" class="px-5 py-3 rounded-xl bg-blue text-white text-xs font-black shadow-sm">Guardar perfil profesional</button></div></div>
              </form>
            </div>
          </div>
        </div>
      </section>
    </div>


    <section id="global-conversion-cta" class="border-t border-slate-200 bg-white py-12">
      <div class="mx-auto max-w-5xl px-4 text-center sm:px-6">
        <h2 class="text-2xl sm:text-3xl font-black text-navy">¿Listo para profesionalizar tu colaboración inmobiliaria?</h2>
        <p class="mx-auto mt-3 max-w-3xl text-sm text-slate-600">Empieza hoy mismo con Starter y descubre cómo Captacion.app puede ayudarte a generar nuevas oportunidades de negocio.</p>
        <div class="mt-6 flex flex-wrap justify-center gap-3">
          <button type="button" onclick="openProfessionalSubscriptionModal('prefooter-starter')" class="px-6 py-3 rounded-xl bg-blue text-white text-xs font-black shadow-sm">Crear cuenta gratuita</button>
          <a href="#/planes" class="px-6 py-3 rounded-xl border border-slate-300 text-navy text-xs font-black hover:bg-slate-50">Ver planes</a>
        </div>
      </div>
    </section>
  </main>


  <!-- PIE LEGAL GLOBAL -->
  <footer class="legal-footer">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="grid gap-5 lg:grid-cols-[1.2fr_1fr] lg:items-start">
        <div>
          <p class="text-sm font-black text-navy">Captacion.app · Plataforma inmobiliaria B2B</p>
          <p class="mt-2 max-w-3xl text-xs leading-relaxed">Genera oportunidades con mayor control: fichas públicas con datos limitados, protección de información sensible y seguimiento completo de cada relación comercial.</p>
          <p class="mt-2 text-[11px]"><span class="legal-placeholder">TODO LEGAL — sustituir antes de producción</span> Titular, NIF/CIF, domicilio y dominio final pendientes de confirmación.</p>
        </div>
        <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs lg:justify-end">
          <a href="#/aviso-legal">Aviso legal</a>
          <a href="#/privacidad">Privacidad</a>
          <a href="#/cookies">Política de cookies</a>
          <a href="#/normas-publicacion">Normas de publicación</a>
          <a href="<?php echo esc_url(home_url('/condiciones-de-contratacion/')); ?>">Condiciones de contratación</a>
          <a href="<?php echo esc_url(home_url('/canal-de-denuncias/')); ?>">Canal de denuncias</a>
          <button type="button" onclick="captacionOpenCookiePreferences()">Configurar cookies</button>
          <button type="button" onclick="openReportModal()">Reportar contenido</button>
        </div>
      </div>
    </div>
  </footer>


  <!-- MODAL DE PREVISUALIZACIÓN DE FICHA B2B ANTES DE PUBLICAR (Fiel a la foto adjuntada) -->
  <div id="preview-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-navy-dark/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative overflow-y-auto max-h-[90vh]">
      <button onclick="closePreviewModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      
      <div class="border-b border-slate-100 pb-3 mb-4">
        <h3 class="text-xl font-extrabold text-navy">Revisión de Captación</h3>
        <p class="text-xs text-slate-400 mt-1">Comprueba la visualización de la ficha tal y como aparecerá en el Marketplace público B2B.</p>
      </div>

      <!-- Contenedor dinámico de la tarjeta de previsualización -->
      <div id="card-preview-area" class="mb-6">
        <!-- Inyectado mediante JS de forma idéntica a la imagen del hito -->
      </div>

      <div class="flex justify-end gap-3 border-t border-slate-100 pt-4">
        <button id="preview-back-btn" onclick="closePreviewModal()" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50">
          Modificar datos
        </button>
        <button id="preview-publish-btn" onclick="confirmAndPublish()" class="px-5 py-2.5 rounded-xl bg-blue text-white text-xs font-black hover:bg-blue-dark shadow-md flex items-center gap-1.5">
          Aprobar y publicar
        </button>
      </div>
    </div>
  </div>


  <!-- MODAL DE COMPRA DE CAPTACION CON STRIPE -->
  <div id="access-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-navy-dark/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative">
      <button onclick="closeAccessModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Acceso a oportunidad</span>
      <h3 id="access-modal-title" class="text-xl font-extrabold text-navy mt-4">Acceder a captación</h3>
      <div id="access-modal-summary" class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 text-xs text-slate-600 leading-relaxed"></div>
      <form onsubmit="handleMarketplaceAccess(event)" class="space-y-4 mt-5">
        <input type="hidden" id="access-property-id" />
        <div class="rounded-2xl border border-amber-200 bg-amber-light/60 p-4 text-[11px] leading-relaxed text-slate-600">
          <span id="access-modal-plan-message">Comprobando accesos disponibles...</span>
        </div>
        <label class="flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
          <input type="checkbox" required class="mt-0.5" />
          <span>Acepto mantener la confidencialidad de los datos de la captación y consumir el acceso cuando corresponda.</span>
        </label>
        <button id="stripe-payment-button" class="w-full py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md">Continuar al pago seguro</button>
      </form>
    </div>
  </div>


  <!-- MODAL: PROPONER CAPTACIÓN COMPATIBLE -->
  <div id="need-collaboration-modal" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4 bg-navy-dark/70 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative overflow-y-auto max-h-[92vh]">
      <button type="button" onclick="closeNeedCollaborationModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-700 text-xl font-black">×</button>
      <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Colaboración profesional</span>
      <h3 class="text-xl font-extrabold text-navy mt-4">Proponer captación compatible</h3>
      <p class="text-xs text-slate-500 mt-2 leading-relaxed">Tienes una captación que puede encajar con esta demanda. Selecciona una oportunidad disponible en Marketplace y enviaremos una notificación al agente demandante.</p>
      <div id="need-collaboration-summary" class="mt-5 p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600"></div>
      <form onsubmit="submitNeedCollaboration(event)" class="mt-5 space-y-4">
        <input id="need-collaboration-need-id" type="hidden" />
        <div><label class="block text-xs font-bold text-slate-500 mb-1">Captación disponible *</label><select id="need-collaboration-property" required class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white text-sm"></select></div>
        <div><label class="block text-xs font-bold text-slate-500 mb-1">Mensaje opcional</label><textarea id="need-collaboration-message" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm resize-none">Tengo una captación publicada en Marketplace que podría encajar con tu búsqueda. Puedes revisarla y solicitar acceso si te interesa.</textarea></div>
        <button class="w-full py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md">Enviar propuesta de colaboración</button>
      </form>
    </div>
  </div>

  <!-- MODAL DE MATCHMAKER IA -->
  <div id="ai-match-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-navy-dark/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative overflow-y-auto max-h-[90vh]">
      <button onclick="closeAiMatchModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      <div class="border-b border-slate-100 pb-4">
        <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Asistente de cruce comercial</span>
        <h3 class="text-xl font-extrabold text-navy mt-3">Informe de compatibilidad de cartera</h3>
      </div>
      <div id="ai-loading" class="py-12 text-center">
        <div class="text-3xl animate-pulse">✨</div>
        <p class="text-sm font-bold text-navy mt-3">Analizando coincidencias de producto y demanda...</p>
      </div>
      <div id="ai-report" class="hidden py-4">
        <div id="ai-report-content" class="text-sm text-slate-600 leading-relaxed"></div>
        <div class="flex justify-end mt-6 pt-4 border-t border-slate-100">
          <button onclick="copyAiReport()" class="px-4 py-2 rounded-xl bg-navy text-white text-xs font-black">Copiar informe</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MODAL DE HERRAMIENTA B2B -->
  <div id="resource-tool-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-navy-dark/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative overflow-y-auto max-h-[92vh]">
      <button onclick="closeResourceToolModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      <span id="resource-tool-kicker" class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Herramienta inmobiliaria</span>
      <h3 id="resource-tool-title" class="text-xl sm:text-2xl font-black text-navy mt-4">Herramienta B2B</h3>
      <p id="resource-tool-description" class="text-xs text-slate-500 mt-2 leading-relaxed"></p>
      <div id="resource-tool-body" class="mt-5"></div>
    </div>
  </div>

  <!-- MODAL: PREPARAR DOCUMENTO PARA FIRMA ELECTRÓNICA -->
  <div id="legal-signature-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-navy-dark/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative overflow-y-auto max-h-[90vh]">
      <button onclick="closeLegalSignatureModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Flujo documental</span>
      <h3 id="legal-signature-title" class="text-xl font-extrabold text-navy mt-4">Preparar documento para firma electrónica</h3>
      <p class="text-xs text-slate-500 mt-2 leading-relaxed">Completa los datos esenciales. En producción se generará un enlace seguro para revisar el documento, completar los campos restantes y firmar electrónicamente con trazabilidad.</p>
      <form onsubmit="generateLegalSignatureLink(event)" class="space-y-4 mt-5">
        <input type="hidden" id="legal-document-type" value="nda" />
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="block text-xs font-bold text-slate-500 mb-1">Referencia de operación *</label><input id="legal-operation-reference" required placeholder="Ej.: REF-00123456" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></div>
          <div><label class="block text-xs font-bold text-slate-500 mb-1">Código Postal</label><input id="legal-postal-code" inputmode="numeric" maxlength="5" placeholder="Ej.: 32002" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></div>
        </div>
        <div><label class="block text-xs font-bold text-slate-500 mb-1">Nombre o razón social del firmante *</label><input id="legal-signer-name" required placeholder="Profesional o agencia colaboradora" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div><label class="block text-xs font-bold text-slate-500 mb-1">Correo *</label><input id="legal-signer-email" type="email" required placeholder="agente@agencia.es" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></div>
          <div><label class="block text-xs font-bold text-slate-500 mb-1">WhatsApp *</label><input id="legal-signer-whatsapp" required placeholder="+34 600 000 000" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></div>
        </div>
        <label class="flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed"><input type="checkbox" required class="mt-0.5" /><span>Confirmo que los datos son correctos y autorizo la preparación del enlace de firma electrónica.</span></label>
        <button class="w-full py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md">Preparar enlace seguro</button>
      </form>
      <div id="legal-signature-result" class="hidden mt-5 p-4 rounded-2xl bg-green-light border border-green/20 text-xs text-slate-600"></div>
    </div>
  </div>

  <!-- MODAL DE CONFIGURACIÓN DE COOKIES -->
  <!-- MODAL: CONECTAR PROVEEDOR DE IA -->
  <div id="ai-connection-modal" class="fixed inset-0 z-[75] hidden flex items-center justify-center p-4 bg-navy-dark/70 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative overflow-y-auto max-h-[92vh]">
      <button type="button" onclick="closeAIConnectionModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Bring your own AI</span>
      <h3 class="text-xl font-extrabold text-navy mt-4">Conecta tu proveedor de inteligencia artificial</h3>
      <p class="text-xs text-slate-500 mt-2 leading-relaxed">
        Guarda tu proveedor, modelo y credencial desde una sesión autenticada. La API key se envía al backend y se conserva cifrada para tu usuario de WordPress.
      </p>
      <form onsubmit="saveAIConnection(event)" class="space-y-4 mt-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Proveedor *</label>
            <select id="ai-provider-select" required onchange="syncAIProviderDefaults()" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm">
              <option value="openai">OpenAI</option>
              <option value="anthropic">Anthropic</option>
              <option value="google">Google</option>
              <option value="groq">Groq</option>
              <option value="openrouter">OpenRouter</option>
              <option value="compatible">Endpoint compatible con OpenAI</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Alias interno *</label>
            <input id="ai-connection-alias" required placeholder="Ej.: IA de mi agencia" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Perfil de uso *</label>
            <select id="ai-use-profile" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm">
              <option value="general">Asistente general inmobiliario</option>
              <option value="copywriting">Redacción de anuncios</option>
              <option value="matching">Cruce oferta–demanda</option>
              <option value="documentos">Análisis documental</option>
              <option value="automatizacion">Automatizaciones</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-slate-500 mb-1">Modelo preferido</label>
            <input id="ai-model-name" placeholder="Ej.: modelo recomendado" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" />
          </div>
        </div>
        <div id="ai-endpoint-wrap" class="hidden">
          <label class="block text-xs font-bold text-slate-500 mb-1">Endpoint compatible *</label>
          <input id="ai-backend-endpoint" type="url" placeholder="https://api.tudominio.com/v1/chat/completions" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" />
          <p class="text-[10px] text-slate-400 mt-1">Solo para endpoints compatibles con OpenAI. La llamada siempre se realizará desde backend.</p>
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 mb-1">API key o credencial *</label>
          <input id="ai-secret-input" type="password" required autocomplete="off" placeholder="Se almacenará cifrada para tu usuario" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" />
          <p class="text-[10px] text-slate-400 mt-1">Tus credenciales se usan solo para tus solicitudes y nunca se guardan en localStorage.</p>
        </div>
        <label class="flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed">
          <input id="ai-security-confirmation" type="checkbox" required class="mt-0.5" />
          <span>Confirmo que esta conexión me pertenece y autorizo su uso solo para mis acciones asistidas dentro de Captacion.app.</span>
        </label>
        <div class="flex flex-col sm:flex-row gap-3">
          <button id="ai-save-connection-btn" type="submit" class="flex-1 py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-bold shadow-md">Guardar conexión</button>
          <button type="button" onclick="closeAIConnectionModal()" class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:border-slate-300">Cancelar</button>
        </div>
      </form>
    </div>
  </div>


  <!-- MODAL PRIVADO: EXPEDIENTE DE OPERACIÓN -->

  <div id="comm-thread-modal" class="fixed inset-0 z-[95] hidden flex items-center justify-center p-4 bg-navy-dark/75 backdrop-blur-sm">
    <div class="w-full max-w-4xl max-h-[92vh] overflow-hidden rounded-3xl bg-white shadow-2xl flex flex-col">
      <div class="px-5 py-4 border-b border-slate-200 flex items-start justify-between gap-4"><div><span class="text-[10px] font-black uppercase tracking-wider text-blue">Sala privada de colaboración</span><h3 id="comm-thread-title" class="text-lg font-black text-navy mt-1">Conversación protegida</h3><p id="comm-thread-subtitle" class="text-[11px] text-slate-500 mt-1"></p></div><button onclick="closeProtectedThread()" class="w-9 h-9 rounded-xl border border-slate-200 text-slate-500">✕</button></div>
      <div class="grid grid-cols-1 lg:grid-cols-[1fr_270px] min-h-0 flex-1">
        <section class="min-h-0 flex flex-col border-r border-slate-200"><div class="comm-safe-banner m-4 mb-0"><strong class="block text-xs text-green">🔒 Conversación trazable</strong><p class="text-[10px] text-slate-500 mt-1">No compartas teléfonos, emails ni enlaces externos. La plataforma bloqueará el envío y registrará el intento.</p></div><div id="comm-thread-messages" class="p-4 space-y-3 overflow-y-auto flex-1 min-h-[300px] max-h-[470px]"></div><div class="p-4 border-t border-slate-200"><textarea id="comm-thread-input" rows="3" placeholder="Escribe un mensaje dentro de la sala protegida..." class="w-full px-3 py-3 rounded-xl border border-slate-200 text-xs resize-none"></textarea><div class="flex flex-wrap items-center justify-between gap-2 mt-2"><span class="text-[10px] text-slate-400">Los mensajes se asocian al expediente.</span><button onclick="sendProtectedThreadMessage()" class="px-4 py-2.5 rounded-xl bg-blue text-white text-xs font-bold">Enviar mensaje</button></div></div></section>
        <aside class="p-4 overflow-y-auto"><h4 class="text-xs font-black text-navy">Flujo protegido</h4><div id="comm-thread-flow" class="mt-4 space-y-3"></div><div class="mt-5 p-3 rounded-xl bg-slate-50 border border-slate-200"><span class="block text-[10px] text-slate-500">Contraparte</span><strong class="block text-xs text-navy mt-1">Profesional verificado · identidad protegida</strong><span class="block text-[10px] text-slate-500 mt-1">Contacto directo no disponible</span></div><button id="comm-thread-progress-btn" onclick="advanceProtectedFlow()" class="w-full mt-4 px-4 py-3 rounded-xl bg-navy text-white text-xs font-bold"></button><button onclick="closeProtectedThread();switchPrivateDashboardPanel('traceability')" class="w-full mt-2 px-4 py-3 rounded-xl border border-slate-200 text-navy text-xs font-bold">Ver trazabilidad</button></aside>
      </div>
    </div>
  </div>

  <div id="private-operation-modal" class="fixed inset-0 z-[80] hidden flex items-center justify-center p-4 bg-navy-dark/70 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-3xl w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative overflow-y-auto max-h-[92vh]">
      <button type="button" onclick="closePrivateOperationModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Expediente privado</span>
      <h3 id="private-operation-modal-title" class="text-xl font-black text-navy mt-4">Operación</h3>
      <div id="private-operation-modal-body" class="mt-5"></div>
    </div>
  </div>

  <!-- MODAL DE REPORTE DE CONTENIDO -->
  <div id="content-report-modal" class="fixed inset-0 z-[70] hidden flex items-center justify-center p-4 bg-navy-dark/60 backdrop-blur-sm">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-slate-100 shadow-2xl relative">
      <button onclick="closeReportModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xl font-bold">×</button>
      <span class="inline-flex px-3 py-1 rounded-full bg-amber-light text-amber text-[10px] font-black uppercase">Canal de reporte</span>
      <h3 class="text-xl font-extrabold text-navy mt-4">Reportar contenido o incidencia</h3>
      <p class="text-xs text-slate-500 mt-2 leading-relaxed">Describe la incidencia para que el equipo pueda revisarla. El reporte quedará asociado a tu cuenta.</p>
      <form onsubmit="submitContentReport(event)" class="mt-5 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Nombre *</span><input id="report-name" required autocomplete="name" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Email *</span><input id="report-email" type="email" required autocomplete="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label></div>
        <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Teléfono</span><input id="report-phone" type="tel" autocomplete="tel" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label>
        <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">URL opcional</span><input id="report-content-reference" type="url" placeholder="https://..." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label>
        <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Comentario *</span><textarea id="report-content-description" required minlength="10" rows="4" placeholder="Describe la incidencia con suficiente detalle." class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm"></textarea></label>
        <input id="report-website" type="text" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true" />
        <label class="flex items-start gap-2 text-[11px] text-slate-500 leading-relaxed"><input type="checkbox" required class="mt-0.5" /><span>Declaro que este reporte se realiza de buena fe y que la información aportada es correcta.</span></label>
        <button class="w-full py-3 rounded-xl bg-navy text-white text-xs font-bold">Enviar reporte</button>
      </form>
    </div>
  </div>

  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
  <script id="captacion-territories-data" type="application/json"><?php echo wp_json_encode(json_decode($captacion_territories_json, true), JSON_UNESCAPED_UNICODE); ?></script>
  <script>
    window.CAPTACION_APP_AI = <?php echo wp_json_encode(array(
      'restBase' => rest_url('captacion-app/v1/ai/'),
      'nonce' => $captacion_rest_nonce,
      'isLoggedIn' => is_user_logged_in(),
      'userLabel' => is_user_logged_in() ? $captacion_current_user->display_name : '',
    ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>

  <!-- Lógica completa de negocio, geografía y servicios IA en un script unificado -->
  <script>
    // ==========================================
    // 1. CAT?LOGO TERRITORIAL DE ESPAÑA
    // ==========================================
    let TERRITORY_CATALOG = [];
    try {
      const territoryCatalogNode = document.getElementById('captacion-territories-data');
      TERRITORY_CATALOG = territoryCatalogNode?.textContent ? JSON.parse(territoryCatalogNode.textContent) : [];
    } catch (error) {
      TERRITORY_CATALOG = [];
      console.warn('No se pudo cargar el catálogo territorial.', error);
    }
    const territoryCatalog = Array.isArray(TERRITORY_CATALOG) ? TERRITORY_CATALOG : [];
    const geoDb = territoryCatalog.reduce((acc, community) => {
      acc[community.name] = (community.provinces || []).reduce((provinceAcc, province) => {
        provinceAcc[province.name] = (province.municipalities || []).map(municipality => municipality.name);
        return provinceAcc;
      }, {});
      return acc;
    }, {});

    function normalizeTerritoryText(value = '') {
      return String(value || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim();
    }

    const TERRITORY_NAME_ALIASES = {
      'andalucia': 'andalucia',
      'aragon': 'aragon',
      'asturias': 'asturias',
      'baleares': 'illes balears',
      'illes balears': 'illes balears',
      'canarias': 'canarias',
      'cantabria': 'cantabria',
      'castilla y leon': 'castilla y leon',
      'castilla-la mancha': 'castilla-la mancha',
      'castilla la mancha': 'castilla-la mancha',
      'cataluna': 'cataluna',
      'comunidad valenciana': 'comunidad valenciana',
      'extremadura': 'extremadura',
      'galicia': 'galicia',
      'la rioja': 'la rioja',
      'madrid': 'comunidad de madrid',
      'comunidad de madrid': 'comunidad de madrid',
      'murcia': 'region de murcia',
      'region de murcia': 'region de murcia',
      'navarra': 'comunidad foral de navarra',
      'comunidad foral de navarra': 'comunidad foral de navarra',
      'pais vasco': 'pais vasco',
      'ceuta': 'ciudad autonoma de ceuta',
      'ciudad autonoma de ceuta': 'ciudad autonoma de ceuta',
      'melilla': 'ciudad autonoma de melilla',
      'ciudad autonoma de melilla': 'ciudad autonoma de melilla'
    };

    const PROVINCE_NAME_ALIASES = {
      'a coruna': 'a coruna',
      'araba': 'araba/alava',
      'alava': 'araba/alava',
      'castellon': 'castellon',
      'guipuzcoa': 'gipuzkoa',
      'vizcaya': 'bizkaia',
      'valencia': 'valencia',
      'ourense': 'ourense',
      'coruna': 'a coruna'
    };

    function getSortedCommunities() {
      return [...territoryCatalog].sort((a, b) => a.name.localeCompare(b.name, 'es'));
    }

    function getCommunityByName(name = '') {
      const normalized = TERRITORY_NAME_ALIASES[normalizeTerritoryText(name)] || normalizeTerritoryText(name);
      return territoryCatalog.find(item => normalizeTerritoryText(item.name) === normalized) || null;
    }

    function getProvinceByName(communityName = '', provinceName = '') {
      const community = getCommunityByName(communityName);
      if (!community) return null;
      const normalized = PROVINCE_NAME_ALIASES[normalizeTerritoryText(provinceName)] || normalizeTerritoryText(provinceName);
      return (community.provinces || []).find(item => {
        const candidate = normalizeTerritoryText(item.name);
        return candidate === normalized || candidate.split('/').includes(normalized);
      }) || null;
    }

    function getMunicipalityByName(communityName = '', provinceName = '', municipalityName = '') {
      const province = getProvinceByName(communityName, provinceName);
      if (!province) return null;
      const normalized = normalizeTerritoryText(municipalityName);
      return (province.municipalities || []).find(item => {
        const candidate = normalizeTerritoryText(item.name);
        return candidate === normalized || candidate.split('/').includes(normalized);
      }) || null;
    }


    class TerritorySelector {
      static instances = {};
      static existing = {};

      constructor({ name, ccaaId, provinceId, municipalityId, postalCodeId = '', allowAll = false, onChange = null }) {
        this.name = name;
        this.ccaa = document.getElementById(ccaaId);
        this.province = document.getElementById(provinceId);
        this.municipality = document.getElementById(municipalityId);
        this.postalCode = postalCodeId ? document.getElementById(postalCodeId) : null;
        this.allowAll = allowAll;
        this.onChange = onChange;
        if (!this.ccaa || !this.province || !this.municipality) return;
        this.populateCommunities();
        this.ccaa.addEventListener('change', () => { this.populateProvinces(); this.emitChange(); });
        this.province.addEventListener('change', () => { this.populateMunicipalities(); this.emitChange(); });
        this.municipality.addEventListener('change', () => { this.applyPostalCodes(); this.emitChange(); });
        TerritorySelector.instances[name] = this;
      }

      option(value, label) { return `<option value="${escapeHTML(value)}">${escapeHTML(label)}</option>`; }
      placeholder(label) { return this.option(this.allowAll ? 'all' : '', label); }

      populateCommunities(selected = '') {
        if (!this.ccaa) return;
        const current = selected || this.ccaa.dataset.initialValue || this.ccaa.value;
        this.ccaa.innerHTML = this.placeholder(this.allowAll ? 'Todas las CCAA' : 'Selecciona una comunidad autónoma') + getSortedCommunities().map(item => this.option(item.name, item.name)).join('');
        if (current) this.ccaa.value = current;
        this.populateProvinces('', false);
      }

      populateProvinces(selected = '', resetMunicipality = true) {
        const community = getCommunityByName(this.ccaa?.value || '');
        const current = selected || this.province?.dataset.initialValue || this.province?.value || '';
        const provinces = community ? [...(community.provinces || [])].sort((a,b)=>a.name.localeCompare(b.name,'es')) : [];
        this.province.innerHTML = this.placeholder(this.allowAll ? 'Todas las provincias' : 'Selecciona una provincia') + provinces.map(item => this.option(item.name, item.name)).join('');
        this.province.disabled = !community;
        if (current) this.province.value = current;
        this.populateMunicipalities('', resetMunicipality);
      }

      populateMunicipalities(selected = '', reset = true) {
        const province = getProvinceByName(this.ccaa?.value || '', this.province?.value || '');
        const current = selected || this.municipality?.dataset.initialValue || (!reset ? this.municipality?.value : '') || '';
        const municipalities = province ? [...(province.municipalities || [])].sort((a,b)=>a.name.localeCompare(b.name,'es')) : [];
        this.municipality.innerHTML = this.placeholder(this.allowAll ? 'Todos los municipios' : 'Selecciona un municipio') + municipalities.map(item => this.option(item.name, item.name)).join('');
        this.municipality.disabled = !province;
        if (current) this.municipality.value = current;
        this.applyPostalCodes(false);
      }

      applyPostalCodes(overwrite = true) {
        if (!this.postalCode) return;
        const municipality = getMunicipalityByName(this.ccaa?.value || '', this.province?.value || '', this.municipality?.value || '');
        const codes = Array.isArray(municipality?.postalCodes) ? municipality.postalCodes : [];
        this.postalCode.dataset.validPostalCodes = codes.join(',');
        if (overwrite && codes.length === 1 && !this.postalCode.value) this.postalCode.value = codes[0];
        this.postalCode.placeholder = codes.length ? `Ej.: ${codes[0]}` : 'Código postal (5 dígitos)';
      }

      setValues(values = {}) {
        if (!this.ccaa) return;
        this.ccaa.value = values.ccaa || values.autonomousCommunity || '';
        this.populateProvinces(values.province || '', false);
        this.populateMunicipalities(values.municipality || '', false);
        if (this.postalCode && values.postalCode) this.postalCode.value = values.postalCode;
      }

      getValue() {
        const territory = resolveTerritorySelection(this.ccaa?.value || '', this.province?.value || '', this.municipality?.value || '');
        return { ...territory, ccaa:this.ccaa?.value || '', province:this.province?.value || '', municipality:this.municipality?.value || '', postalCode:cleanText(this.postalCode?.value || '') };
      }

      emitChange() { if (typeof this.onChange === 'function') this.onChange(this.getValue()); }

      static attachExisting(name, ids) { TerritorySelector.existing[name] = ids; }
    }

    function initTerritorySelectors() {
      new TerritorySelector({ name:'fiscal-profile', ccaaId:'fiscal-ccaa', provinceId:'fiscal-province', municipalityId:'fiscal-municipality', postalCodeId:'fiscal-postal-code' });
      new TerritorySelector({ name:'marketplace-filter', ccaaId:'market-ccaa-filter', provinceId:'market-province-filter', municipalityId:'market-municipality-filter', postalCodeId:'market-postal-code-filter', allowAll:true, onChange:()=>refreshMarketplaceView() });
      new TerritorySelector({ name:'sales-filter', ccaaId:'sales-match-ccaa', provinceId:'sales-match-province', municipalityId:'sales-match-municipality', allowAll:true, onChange:()=>renderSalesMatches() });
      TerritorySelector.attachExisting('offer-form', { ccaa:'offer-ccaa-sel', province:'offer-province-sel', municipality:'offer-municipality-sel', postalCode:'offer-postal-code' });
      TerritorySelector.attachExisting('need-form', { ccaa:'need-pub-ccaa-sel', province:'need-pub-province-sel', municipality:'need-pub-municipality-sel', postalCode:'need-pub-postal-code' });
      TerritorySelector.attachExisting('needs-filter', { ccaa:'need-filter-ccaa', province:'need-filter-province', municipality:'need-filter-municipality', postalCode:'need-filter-postal-code' });
    }

    function maskPublicPostalCode(value = '') {
      const code = String(value || '').replace(/\D/g, '').slice(0,5);
      return code.length === 5 ? `${code.slice(0,2)}***` : 'Zona protegida';
    }

    async function validateAddressWithCartoCiudad({ address = '', postalCode = '', municipality = '', province = '' } = {}) {
      if (!CAPTACION_MAILCHIMP?.territoryValidationEndpoint) return { ok:false, results:[] };
      try {
        const response = await fetch(CAPTACION_MAILCHIMP.territoryValidationEndpoint, { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body:JSON.stringify({address,postalCode,municipality,province}) });
        if (!response.ok) throw new Error('validation_failed');
        return await response.json();
      } catch (error) { return { ok:false, results:[] }; }
    }

    async function validateFiscalAddress() {
      const selector = TerritorySelector.instances['fiscal-profile']; const value = selector?.getValue() || {};
      const status = document.getElementById('fiscal-address-validation-status'); if (status) status.textContent = 'Validando con CartoCiudad/CNIG...';
      const result = await validateAddressWithCartoCiudad({ address:document.getElementById('fiscal-address')?.value || '', postalCode:value.postalCode, municipality:value.municipality, province:value.province });
      if (status) status.textContent = result.ok && result.results?.length ? 'Dirección fiscal validada de forma aproximada.' : 'No se encontró una coincidencia oficial. Puedes guardar y revisarla manualmente.';
    }

    // ==========================================
    // 2. COORDENADAS GEOGRÁFICAS (mapa público)
    // ==========================================
    // Coordenadas aproximadas para el mapa público. En producción deberán llegar del backend geoespacial.
    const geoCenters = {
      "Andalucía": [37.45, -4.65], "Aragón": [41.35, -0.65], "Asturias": [43.36, -5.85],
      "Baleares": [39.60, 2.95], "Canarias": [28.30, -15.70], "Cantabria": [43.18, -4.05],
      "Castilla y León": [41.65, -4.75], "Castilla-La Mancha": [39.50, -3.00], "Cataluña": [41.75, 1.65],
      "Comunidad Valenciana": [39.50, -0.55], "Extremadura": [39.15, -6.15], "Galicia": [42.75, -8.00],
      "La Rioja": [42.30, -2.50], "Madrid": [40.42, -3.70], "Murcia": [37.98, -1.13],
      "Navarra": [42.70, -1.65], "País Vasco": [43.00, -2.55], "Ceuta": [35.89, -5.32], "Melilla": [35.29, -2.94],
      "Ourense": [42.34, -7.86], "Madrid ciudad": [40.42, -3.70], "Barcelona": [41.39, 2.17],
      "Elche": [38.27, -0.70], "Pozuelo de Alarcón": [40.44, -3.81], "Vigo": [42.24, -8.72],
      "Valencia": [39.47, -0.38], "A Coruña": [43.37, -8.40], "Sevilla": [37.39, -5.99], "Málaga": [36.72, -4.42]
    };

    // ==========================================
    // 3. MAPA DE RUTAS Y VARIABLES DE ESTADO GLOBALES
    // ==========================================
    const routes = {
      '#/inicio': 'page-inicio',
      '#/buscar-captaciones': 'page-buscar-captaciones',
      '#/ofrecer-captacion': 'page-ofrecer-captacion',
      '#/como-funciona': 'page-como-funciona',
      '#/marketplace': 'page-marketplace',
      '#/coincidencias-ventas': 'page-coincidencias-ventas',
      '#/planes': 'page-planes',
      '#/recursos': 'page-recursos',
      '#/contacto': 'page-contacto',
      '#/aviso-legal': 'page-aviso-legal',
      '#/privacidad': 'page-privacidad',
      '#/cookies': 'page-cookies',
      '#/normas-publicacion': 'page-normas-publicacion',
      '#/area-privada': 'page-area-privada'
    };

    // Stripe Payment Link real. Sustituye este valor por el enlace creado en Stripe.
    const STRIPE_PAYMENT_LINK_URL = <?php echo wp_json_encode($captacion_stripe_link); ?>;
    const STRIPE_MEMBERSHIP_LINKS = <?php echo wp_json_encode($captacion_membership_links); ?>;
    const STRIPE_PROFESSIONAL_PLUS_URL = '';
    const STRIPE_PREMIUM_URL = STRIPE_MEMBERSHIP_LINKS?.premium || '';
    const STRIPE_PAYMENT_PRODUCT_NAME = 'Desbloqueo de captacion profesional';
    const CAPTACION_MAILCHIMP = <?php echo wp_json_encode($captacion_mailchimp_config); ?>;
    window.CAPTACION_API = {
      restUrl: <?php echo wp_json_encode(rtrim(rest_url(), '/')); ?>,
      nonce: <?php echo wp_json_encode($captacion_rest_nonce); ?>,
      currentUserId: <?php echo wp_json_encode(get_current_user_id()); ?>,
      endpoints: {
        importXmlUrl: <?php echo wp_json_encode(rest_url('captacion/v1/xml-feeds/import-url')); ?>,
        uploadXmlFile: <?php echo wp_json_encode(rest_url('captacion/v1/xml/user/import')); ?>,
        listXmlFeeds: <?php echo wp_json_encode(rest_url('captacion/v1/import-batches')); ?>,
        xmlFeed: <?php echo wp_json_encode(rest_url('captacion/v1/import-batches/')); ?>,
        syncXmlFeed: <?php echo wp_json_encode(rest_url('captacion/v1/xml-feeds/')); ?>,
        exportUserXml: <?php echo wp_json_encode(rest_url('captacion/v1/xml/user/export')); ?>,
        deleteMyData: <?php echo wp_json_encode(rest_url('captacion/v1/my-data')); ?>
      }
    };

    let currentNeedsLayout = 'bloque';
    let currentHash = '#/inicio';
    let tempPropertyToPublish = null; // Almacén temporal para previsualización
    let uploadedFileBase64 = null; // Almacén temporal de la imagen Base64
    let homeMap = null;
    let homeMapLayer = null;
    let homeMapMode = 'all';
    let homeMapSelectionLayer = null;
    let homeMapSelectedBounds = null;
    let homeMapDrawHandler = null;
    let homeMapPostalCodeFilter = '';
    let marketplaceMap = null;
    let marketplaceMapLayer = null;
    let marketplaceMapSelectionLayer = null;
    let marketplaceMapSelectedBounds = null;
    let marketplaceMapDrawHandler = null;
    let marketplaceViewMode = 'cards';
    let marketplaceLayoutMode = 'block';
    let needsMap = null;
    let needsMapLayer = null;
    let needsMapVisible = false;
    let needsMapSelectionLayer = null;
    let needsMapSelectedBounds = null;
    let needsMapDrawHandler = null;
    let needsMapPostalCodeFilter = '';
    let lastFilteredNeeds = [];
    const LIST_BATCH_SIZE = 12;
    const MARKETPLACE_CAROUSEL_SIZE = 4;
    let marketplaceVisibleLimit = LIST_BATCH_SIZE;
    let marketplaceCarouselOffset = 0;
    let needsVisibleLimit = LIST_BATCH_SIZE;
    const SPAIN_DEFAULT_MAP_CENTER = [40.1, -3.7];
    const SPAIN_DEFAULT_MAP_ZOOM = 5.7;

    // Imágenes virtuales ligeras por tipo de inmueble para la demo y para captaciones sin fotografía.
    // Se generan como SVG embebidos para evitar archivos pesados y conservar una carga rápida.
    const VIRTUAL_IMAGE_PRESETS = {
      'Piso': { label: 'Piso', from: '#143c6d', to: '#4b8fd8', icon: '<rect x="298" y="210" width="304" height="380" rx="18" fill="none" stroke="#ffffff" stroke-width="26"/><path d="M350 285h58m84 0h58m-200 88h58m84 0h58m-200 88h58m84 0h58" stroke="#ffffff" stroke-width="22" stroke-linecap="round"/><path d="M420 590V490h60v100" fill="none" stroke="#ffffff" stroke-width="24"/>' },
      'Casa/Chalet': { label: 'Casa / Chalet', from: '#1b5e57', to: '#61b89f', icon: '<path d="M235 418 450 238l215 180v190a35 35 0 0 1-35 35H270a35 35 0 0 1-35-35Z" fill="none" stroke="#ffffff" stroke-width="28" stroke-linejoin="round"/><path d="M385 642V486h130v156" fill="none" stroke="#ffffff" stroke-width="28"/><path d="M555 324v-76h58v124" fill="none" stroke="#ffffff" stroke-width="24"/>' },
      'Local Comercial': { label: 'Local comercial', from: '#7a3d16', to: '#e49a4b', icon: '<rect x="222" y="284" width="456" height="350" rx="24" fill="none" stroke="#ffffff" stroke-width="26"/><path d="M205 340h490l-48-94H253Z" fill="none" stroke="#ffffff" stroke-width="26" stroke-linejoin="round"/><path d="M300 634V438h300v196M300 438h300" fill="none" stroke="#ffffff" stroke-width="24"/>' },
      'Nave': { label: 'Nave industrial', from: '#334155', to: '#728197', icon: '<path d="M206 414 450 242l244 172v220H206Z" fill="none" stroke="#ffffff" stroke-width="28" stroke-linejoin="round"/><path d="M315 634V450h270v184M360 510h180m-180 64h180" fill="none" stroke="#ffffff" stroke-width="24"/>' },
      'Oficina': { label: 'Oficina', from: '#3e3b79', to: '#8179c7', icon: '<rect x="285" y="208" width="330" height="430" rx="18" fill="none" stroke="#ffffff" stroke-width="26"/><path d="M350 280h50m100 0h50m-200 85h50m100 0h50m-200 85h50m100 0h50m-125 188V520h50v118" stroke="#ffffff" stroke-width="22" stroke-linecap="round"/>' },
      'Edificio': { label: 'Edificio', from: '#12344d', to: '#427899', icon: '<path d="M248 638V258h260v380M508 638V330h145v308" fill="none" stroke="#ffffff" stroke-width="27" stroke-linejoin="round"/><path d="M312 326h54m82 0h-30m-106 82h54m82 0h-30m-106 82h54m82 0h-30m151-92h34m-34 82h34m-34 82h34" stroke="#ffffff" stroke-width="21" stroke-linecap="round"/>' },
      'Suelo/Terreno': { label: 'Suelo / Terreno', from: '#47672a', to: '#91bd54', icon: '<path d="M160 606 328 410l116 118 122-170 174 248Z" fill="none" stroke="#ffffff" stroke-width="28" stroke-linejoin="round"/><circle cx="642" cy="252" r="54" fill="none" stroke="#ffffff" stroke-width="25"/><path d="M186 650h528" stroke="#ffffff" stroke-width="26" stroke-linecap="round"/>' },
      'Garaje': { label: 'Garaje', from: '#374151', to: '#7b8796', icon: '<rect x="220" y="264" width="460" height="370" rx="26" fill="none" stroke="#ffffff" stroke-width="26"/><path d="M292 634V420h316v214M292 490h316m-210 70h104" fill="none" stroke="#ffffff" stroke-width="24" stroke-linecap="round"/>' },
      'Activo inmobiliario': { label: 'Activo inmobiliario', from: '#10233c', to: '#1b67d6', icon: '<path d="M210 458 450 262l240 196v224a42 42 0 0 1-42 42H252a42 42 0 0 1-42-42V458Z" fill="none" stroke="#ffffff" stroke-width="34" stroke-linejoin="round"/><path d="M375 724V514h150v210" fill="none" stroke="#ffffff" stroke-width="34" stroke-linejoin="round"/>' }
    };

    function normalizeVirtualPropertyType(type = '') {
      const value = cleanText(type || '').toLowerCase();
      if (value.includes('piso') || value.includes('apartamento') || value.includes('estudio')) return 'Piso';
      if (value.includes('casa') || value.includes('chalet') || value.includes('villa')) return 'Casa/Chalet';
      if (value.includes('local') || value.includes('comercial')) return 'Local Comercial';
      if (value.includes('nave') || value.includes('industrial')) return 'Nave';
      if (value.includes('oficina') || value.includes('despacho')) return 'Oficina';
      if (value.includes('edificio')) return 'Edificio';
      if (value.includes('suelo') || value.includes('terreno') || value.includes('parcela') || value.includes('solar')) return 'Suelo/Terreno';
      if (value.includes('garaje') || value.includes('parking')) return 'Garaje';
      return 'Activo inmobiliario';
    }

    function buildVirtualMarketplaceImage(type = 'Activo inmobiliario') {
      const normalizedType = normalizeVirtualPropertyType(type);
      const preset = VIRTUAL_IMAGE_PRESETS[normalizedType] || VIRTUAL_IMAGE_PRESETS['Activo inmobiliario'];
      return `data:image/svg+xml;charset=UTF-8,${encodeURIComponent(`
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 900 900">
          <defs>
            <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
              <stop offset="0%" stop-color="${preset.from}"/>
              <stop offset="100%" stop-color="${preset.to}"/>
            </linearGradient>
          </defs>
          <rect width="900" height="900" fill="url(#bg)"/>
          <circle cx="744" cy="148" r="170" fill="#ffffff" opacity="0.08"/>
          <circle cx="118" cy="760" r="240" fill="#ffffff" opacity="0.06"/>
          <rect x="50" y="54" width="232" height="62" rx="31" fill="#ffffff" opacity="0.94"/>
          <text x="166" y="94" text-anchor="middle" fill="#10233c" font-family="Arial, sans-serif" font-size="27" font-weight="700">Imagen virtual</text>
          <g opacity="0.97">${preset.icon}</g>
          <text x="450" y="790" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-size="46" font-weight="700">${preset.label}</text>
          <text x="450" y="842" text-anchor="middle" fill="#ffffff" opacity="0.85" font-family="Arial, sans-serif" font-size="28" font-weight="600">Captacion.app · Demo</text>
        </svg>`)};`;
    }

    const VIRTUAL_MARKETPLACE_IMAGES = Object.fromEntries(Object.keys(VIRTUAL_IMAGE_PRESETS).map(type => [type, buildVirtualMarketplaceImage(type)]));
    const DEFAULT_PROPERTY_IMAGES = {
      'Piso': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/piso-default.jpg'); ?>',
      'Casa/Chalet': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/casa-chalet-default.jpg'); ?>',
      'Local Comercial': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/comercial-default.jpg'); ?>',
      'Nave': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/nave-default.jpg'); ?>',
      'Oficina': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/oficina-default.jpg'); ?>',
      'Edificio': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/edificio-default.jpg'); ?>',
      'Suelo/Terreno': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/terreno-default.jpg'); ?>',
      'Garaje': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/piso-default.jpg'); ?>',
      'Activo inmobiliario': '<?php echo esc_js($captacion_theme_uri . '/media/property-defaults/piso-default.jpg'); ?>'
    };
    function getVirtualMarketplaceImage(type = '') {
      const normalizedType = normalizeVirtualPropertyType(type);
      return DEFAULT_PROPERTY_IMAGES[normalizedType] || DEFAULT_PROPERTY_IMAGES['Activo inmobiliario'];
    }
    const DEFAULT_MARKETPLACE_IMAGE = getVirtualMarketplaceImage('Activo inmobiliario');
    window.DEFAULT_MARKETPLACE_IMAGE = DEFAULT_MARKETPLACE_IMAGE;
    window.getVirtualMarketplaceImage = getVirtualMarketplaceImage;
    const MAX_MARKETPLACE_IMAGE_SIZE = 900;
    const MARKETPLACE_IMAGE_QUALITY = 0.78;

    // ==========================================
    // 4. CARGA DE LA BASE DE DATOS LOCAL
    // ==========================================
    let properties = [];
    try {
      properties = JSON.parse(localStorage.getItem('captacion_properties_v3'));
    } catch (e) {}
    if (!Array.isArray(properties)) properties = [];

    let needs = [];
    try {
      needs = JSON.parse(localStorage.getItem('captacion_needs_v3'));
    } catch (e) {}
    if (!Array.isArray(needs)) needs = [];

    let closedOperations = [];
    try {
      closedOperations = JSON.parse(localStorage.getItem('captacion_closed_operations_v4'));
    } catch (e) {}
    if (!Array.isArray(closedOperations)) closedOperations = [];

    properties = properties.map((property, index) => normalizePropertyRecord(property, index));
    needs = needs.map((need, index) => normalizeNeedRecord(need, index));
    if (!localStorage.getItem('captacion_production_cleanup_v3')) {
      const legacyPropertyIds = new Set(['prop-1', 'prop-2', 'prop-3']);
      const legacyNeedIds = new Set(['need-1', 'need-2', 'need-3']);
      const isLegacyProperty = p => p.demoBatch || String(p.id).startsWith('demo-') || legacyPropertyIds.has(String(p.id));
      const isLegacyNeed = n => n.demoBatch || String(n.id).startsWith('demo-') || legacyNeedIds.has(String(n.id));
      const oldPropsCount = properties.filter(isLegacyProperty).length;
      const oldNeedsCount = needs.filter(isLegacyNeed).length;
      if (oldPropsCount || oldNeedsCount) {
        properties = properties.filter(p => !isLegacyProperty(p));
        needs = needs.filter(n => !isLegacyNeed(n));
        closedOperations = [];
        try { localStorage.removeItem('captacion_properties_v3'); } catch (e) {}
        try { localStorage.removeItem('captacion_needs_v3'); } catch (e) {}
        try { localStorage.removeItem('captacion_closed_operations_v4'); } catch (e) {}
        try { localStorage.removeItem('captacion_agent_private_dashboard_v2'); } catch (e) {}
        try { localStorage.removeItem('captacion_internal_communications_v1'); } catch (e) {}
        try { localStorage.removeItem('captacion_spain_scale_demo_v1'); } catch (e) {}
        try { localStorage.removeItem('captacion_requested_demo_v1'); } catch (e) {}
        try { localStorage.removeItem('captacion_demo_owners_v1'); } catch (e) {}
        try { localStorage.removeItem('captacion_demo_demanders_v1'); } catch (e) {}
      }
      try { localStorage.removeItem('captacion_agent_private_dashboard_v2'); } catch (e) {}
      try { localStorage.removeItem('captacion_internal_communications_v1'); } catch (e) {}
      try { localStorage.setItem('captacion_production_cleanup_v3', '1'); } catch (e) {}
    }
    persistDemoState();


    // ==========================================
    // 5. ENRUTADOR INTERNO (CON RESPALDO SEGURO ANTE SANDBOX IFRAMES)
    // ==========================================
    function handleRoute() {
      let hash = '#/inicio';
      try {
        hash = window.location.hash || currentHash;
      } catch (e) {
        hash = currentHash;
      }
      
      if (!hash.startsWith('#/')) {
        hash = '#/inicio';
      }

      currentHash = hash;

      // Ocultar todas las páginas
      document.querySelectorAll('.page-section').forEach(section => {
        section.classList.add('hidden');
      });

      // Mostrar la activa
      let activePageId = routes[hash] || 'page-inicio';
      if (activePageId === 'page-area-privada' && !getDemoSession?.()) {
        showRegistrationPrompt(true);
        showToast('Inicia sesión para entrar al panel privado.', 'info');
        activePageId = 'page-inicio';
        try { window.location.hash = '#/inicio'; } catch (error) {}
      }
      const activeSection = document.getElementById(activePageId);
      if (activeSection) {
        activeSection.classList.remove('hidden');
        window.scrollTo({ top: 0 });
        repairMojibakeInDOM(activeSection);
      }
      document.getElementById('global-conversion-cta')?.classList.toggle('hidden', ['page-area-privada','page-aviso-legal','page-privacidad','page-cookies','page-normas-publicacion'].includes(activePageId));

      if (activePageId === 'page-inicio') {
        setTimeout(() => {
          renderHome();
          initHomeMap();
        }, 0);
      }
      if (activePageId === 'page-marketplace') {
        setTimeout(() => {
          refreshMarketplaceView();
          if (marketplaceViewMode === 'map') initMarketplaceMap();
        }, 0);
      }
      if (activePageId === 'page-coincidencias-ventas') {
        setTimeout(renderSalesMatches, 0);
      }
      if (activePageId === 'page-buscar-captaciones' && currentNeedsLayout === 'mapa') {
        setTimeout(initNeedsMap, 0);
      }
      if (activePageId === 'page-area-privada') {
        setTimeout(() => { renderDashboard(); renderAIConnections(); renderPrivateXmlFeeds(); }, 0);
      }

      // Sincronizar clases visuales de los links del menú
      document.querySelectorAll('.nav-link').forEach(link => {
        const href = link.getAttribute('href');
        if (href === hash) {
          link.classList.add('text-blue', 'border-blue');
          link.classList.remove('text-slate-600', 'border-transparent');
        } else {
          link.classList.remove('text-blue', 'border-blue');
          link.classList.add('text-slate-600', 'border-transparent');
        }
      });
    }

    // --- MENÚ MÓVIL ---
    function toggleMenu() {
      const mobileNav = document.getElementById('mobile-nav');
      if (!mobileNav) return;
      mobileNav.classList.toggle('hidden');
      const isOpen = !mobileNav.classList.contains('hidden');
      const textEl = document.getElementById('menu-icon-text');
      if (textEl) {
        textEl.innerText = isOpen ? '✕' : '☰';
      }
    }

    // ==========================================
    // ==========================================
    // 6. GESTIÓN GEOGR?FICA DE FORMULARIOS
    // ==========================================
    function fillSelectOptions(select, options, placeholder, allowAll = false) {
      if (!select) return;
      const baseValue = allowAll ? 'all' : '';
      select.innerHTML = `<option value="${baseValue}">${placeholder}</option>`;
      options.forEach(option => {
        select.innerHTML += `<option value="${escapeHTML(option.name)}">${escapeHTML(option.name)}</option>`;
      });
    }

    function setFieldDisabled(field, disabled) {
      if (!field) return;
      field.disabled = disabled;
    }

    function fillMunicipalityDatalist(input, list, municipalities = []) {
      if (!input || !list) return;
      list.innerHTML = municipalities.map(item => `<option value="${escapeHTML(item.name)}"></option>`).join('');
    }

    function resetMunicipalityInput(input, list, placeholder, disabled = true) {
      if (!input) return;
      input.value = '';
      input.placeholder = placeholder;
      input.disabled = disabled;
      input.setCustomValidity('');
      if (list) list.innerHTML = '';
    }

    function validateMunicipalityInput(input, communityName, provinceName) {
      if (!input || input.disabled) return true;
      const rawValue = cleanText(input.value);
      if (!rawValue) {
        input.setCustomValidity('Selecciona o busca un municipio.');
        return false;
      }
      const municipality = getMunicipalityByName(communityName, provinceName, rawValue);
      if (!municipality) {
        input.setCustomValidity('Selecciona un municipio válido de la provincia elegida.');
        return false;
      }
      input.value = municipality.name;
      input.setCustomValidity('');
      return true;
    }

    function resolveTerritorySelection(ccaaName = '', provinceName = '', municipalityName = '') {
      const community = getCommunityByName(ccaaName);
      if (!community) return { valid: false, message: 'Selecciona una comunidad o ciudad autónoma válida.' };
      const province = getProvinceByName(community.name, provinceName);
      if (!province) return { valid: false, message: 'Selecciona una provincia válida dentro de la comunidad elegida.' };
      const municipality = getMunicipalityByName(community.name, province.name, municipalityName);
      if (!municipality) return { valid: false, message: 'Selecciona un municipio válido de la provincia elegida.' };
      return {
        valid: true,
        autonomous_community_id: String(community.id || ''),
        autonomous_community_name: community.name,
        province_id: String(province.id || ''),
        province_name: province.name,
        municipality_id: String(municipality.id || municipality.ine_code || ''),
        municipality_ine_code: String(municipality.ine_code || municipality.id || ''),
        municipality_name: municipality.name
      };
    }

    function initGeoSelectors() {
      const needPubCcaa = document.getElementById('need-pub-ccaa-sel');
      const needPubProvince = document.getElementById('need-pub-province-sel');
      const needPubMunicipality = document.getElementById('need-pub-municipality-sel');
      const needPubMunicipalityList = document.getElementById('need-pub-municipality-list');

      const needFilterCcaa = document.getElementById('need-filter-ccaa');
      const needFilterProvince = document.getElementById('need-filter-province');
      const needFilterMunicipality = document.getElementById('need-filter-municipality');

      const offerCcaa = document.getElementById('offer-ccaa-sel');
      const offerProvince = document.getElementById('offer-province-sel');
      const offerMunicipality = document.getElementById('offer-municipality-sel');
      const offerMunicipalityList = document.getElementById('offer-municipality-list');

      const communities = getSortedCommunities();

      if (needPubCcaa) {
        fillSelectOptions(needPubCcaa, communities, 'Selecciona una comunidad autónoma');
        fillSelectOptions(needPubProvince, [], 'Selecciona una comunidad autónoma');
        setFieldDisabled(needPubProvince, true);
        resetMunicipalityInput(needPubMunicipality, needPubMunicipalityList, 'Selecciona una provincia', true);
        needPubCcaa.addEventListener('change', () => updateGeoDropdowns('form-need'));
        needPubProvince.addEventListener('change', () => updateGeoDropdowns('form-need', true));
        needPubMunicipality.addEventListener('input', () => validateMunicipalityInput(needPubMunicipality, needPubCcaa.value, needPubProvince.value));
        needPubMunicipality.addEventListener('blur', () => validateMunicipalityInput(needPubMunicipality, needPubCcaa.value, needPubProvince.value));
      }

      if (offerCcaa) {
        fillSelectOptions(offerCcaa, communities, 'Selecciona una comunidad autónoma');
        fillSelectOptions(offerProvince, [], 'Selecciona una comunidad autónoma');
        setFieldDisabled(offerProvince, true);
        resetMunicipalityInput(offerMunicipality, offerMunicipalityList, 'Selecciona una provincia', true);
        offerCcaa.addEventListener('change', () => updateGeoDropdowns('form-offer'));
        offerProvince.addEventListener('change', () => updateGeoDropdowns('form-offer', true));
        offerMunicipality.addEventListener('input', () => validateMunicipalityInput(offerMunicipality, offerCcaa.value, offerProvince.value));
        offerMunicipality.addEventListener('blur', () => validateMunicipalityInput(offerMunicipality, offerCcaa.value, offerProvince.value));
      }

      if (needFilterCcaa) {
        fillSelectOptions(needFilterCcaa, communities, 'Todas las CCAA', true);
        fillSelectOptions(needFilterProvince, [], 'Todas las provincias', true);
        fillSelectOptions(needFilterMunicipality, [], 'Todos los municipios', true);
        needFilterCcaa.addEventListener('change', () => {
          updateGeoDropdowns('filter');
          filterNeeds();
        });
        needFilterProvince.addEventListener('change', () => {
          updateGeoDropdowns('filter', true);
          filterNeeds();
        });
        needFilterMunicipality.addEventListener('change', filterNeeds);
      }
    }

    function updateGeoDropdowns(context, provinceChangedOnly = false) {
      const needPubCcaa = document.getElementById('need-pub-ccaa-sel');
      const needPubProvince = document.getElementById('need-pub-province-sel');
      const needPubMunicipality = document.getElementById('need-pub-municipality-sel');
      const needPubMunicipalityList = document.getElementById('need-pub-municipality-list');

      const offerCcaa = document.getElementById('offer-ccaa-sel');
      const offerProvince = document.getElementById('offer-province-sel');
      const offerMunicipality = document.getElementById('offer-municipality-sel');
      const offerMunicipalityList = document.getElementById('offer-municipality-list');

      const needFilterCcaa = document.getElementById('need-filter-ccaa');
      const needFilterProvince = document.getElementById('need-filter-province');
      const needFilterMunicipality = document.getElementById('need-filter-municipality');

      if (context === 'form-need') {
        const community = getCommunityByName(needPubCcaa?.value || '');
        if (!provinceChangedOnly) {
          fillSelectOptions(needPubProvince, community ? [...(community.provinces || [])].sort((a, b) => a.name.localeCompare(b.name, 'es')) : [], 'Selecciona una provincia');
          setFieldDisabled(needPubProvince, !community);
          resetMunicipalityInput(needPubMunicipality, needPubMunicipalityList, community ? 'Selecciona o busca un municipio' : 'Selecciona una provincia', true);
        }
        const province = getProvinceByName(needPubCcaa?.value || '', needPubProvince?.value || '');
        fillMunicipalityDatalist(needPubMunicipality, needPubMunicipalityList, province ? [...(province.municipalities || [])].sort((a, b) => a.name.localeCompare(b.name, 'es')) : []);
        setFieldDisabled(needPubMunicipality, !province);
        needPubMunicipality.placeholder = province ? 'Selecciona o busca un municipio' : 'Selecciona una provincia';
        needPubMunicipality.value = '';
        needPubMunicipality.setCustomValidity('');
        return;
      }

      if (context === 'form-offer') {
        const community = getCommunityByName(offerCcaa?.value || '');
        if (!provinceChangedOnly) {
          fillSelectOptions(offerProvince, community ? [...(community.provinces || [])].sort((a, b) => a.name.localeCompare(b.name, 'es')) : [], 'Selecciona una provincia');
          setFieldDisabled(offerProvince, !community);
          resetMunicipalityInput(offerMunicipality, offerMunicipalityList, community ? 'Selecciona o busca un municipio' : 'Selecciona una provincia', true);
        }
        const province = getProvinceByName(offerCcaa?.value || '', offerProvince?.value || '');
        fillMunicipalityDatalist(offerMunicipality, offerMunicipalityList, province ? [...(province.municipalities || [])].sort((a, b) => a.name.localeCompare(b.name, 'es')) : []);
        setFieldDisabled(offerMunicipality, !province);
        offerMunicipality.placeholder = province ? 'Selecciona o busca un municipio' : 'Selecciona una provincia';
        offerMunicipality.value = '';
        offerMunicipality.setCustomValidity('');
        return;
      }

      const community = getCommunityByName(needFilterCcaa?.value || '');
      if (!provinceChangedOnly) {
        fillSelectOptions(needFilterProvince, community ? [...(community.provinces || [])].sort((a, b) => a.name.localeCompare(b.name, 'es')) : [], 'Todas las provincias', true);
        fillSelectOptions(needFilterMunicipality, [], 'Todos los municipios', true);
      }
      const province = getProvinceByName(needFilterCcaa?.value || '', needFilterProvince?.value || '');
      fillSelectOptions(needFilterMunicipality, province ? [...(province.municipalities || [])].sort((a, b) => a.name.localeCompare(b.name, 'es')) : [], 'Todos los municipios', true);
    }
    // ==========================================
    // 7. RENDERIZACIÓN DE MOCKUPS Y DASHBOARDS
    // ==========================================

    // ==========================================
    // 7.1 UTILIDADES, HOME DINMICA Y FUNCIONES DE APOYO
    // ==========================================
    function cleanText(value = "") {
      return String(value).replace(/[<>]/g, '').replace(/[\u0000-\u001F\u007F]/g, ' ').trim();
    }

    const UI_MOJIBAKE_REPLACEMENTS = [];

    function repairMojibakeString(value = '') {
      let result = String(value);
      UI_MOJIBAKE_REPLACEMENTS.forEach(([from, to]) => {
        result = result.split(from).join(to);
      });
      return result;
    }

    function repairMojibakeInDOM(root = document.body) {
      if (!root) return;
      const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
      const textNodes = [];
      while (walker.nextNode()) textNodes.push(walker.currentNode);
      textNodes.forEach(node => {
        if (!node.nodeValue || !node.nodeValue.trim()) return;
        const repaired = repairMojibakeString(node.nodeValue);
        if (repaired !== node.nodeValue) node.nodeValue = repaired;
      });
      root.querySelectorAll('input, textarea, button, a, span, p, label, option, select, summary').forEach(element => {
        ['placeholder', 'title', 'aria-label'].forEach(attribute => {
          const current = element.getAttribute(attribute);
          if (!current) return;
          const repaired = repairMojibakeString(current);
          if (repaired !== current) element.setAttribute(attribute, repaired);
        });
      });
    }

    function enrichTerritoryFields(record = {}) {
      const territory = resolveTerritorySelection(record.autonomous_community_name || record.ccaa || '', record.province_name || record.province || '', record.municipality_name || record.municipality || '');
      if (!territory.valid) {
        return {
          autonomous_community_id: cleanText(record.autonomous_community_id || ''),
          autonomous_community_name: cleanText(record.autonomous_community_name || record.ccaa || ''),
          province_id: cleanText(record.province_id || ''),
          province_name: cleanText(record.province_name || record.province || ''),
          municipality_id: cleanText(record.municipality_id || ''),
          municipality_ine_code: cleanText(record.municipality_ine_code || ''),
          municipality_name: cleanText(record.municipality_name || record.municipality || '')
        };
      }
      return territory;
    }

    function escapeHTML(value = "") {
      return String(value).replace(/[&<>'"]/g, char => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
      }[char]));
    }

    function formatCurrency(value) {
      return new Intl.NumberFormat('de-DE', { maximumFractionDigits: 0 }).format(Number(value) || 0) + ' €';
    }

    function formatPropertyFeatures(record = {}, compact = false) {
      const bedrooms = Number(record.bedrooms) || 0;
      const bathrooms = Number(record.bathrooms) || 0;
      const surface = Number(record.surface) || 0;
      if (compact) return `${bedrooms} hab. · ${bathrooms} baños · ${surface || 'N/D'} m²`;
      return `Habitaciones: ${bedrooms} · Baños: ${bathrooms} · Superficie: ${surface || 'N/D'} m²`;
    }

    function resolveMarketplaceImage(image = '', type = 'Activo inmobiliario') {
      const value = String(image || '').trim();
      return value || getVirtualMarketplaceImage(type);
    }

    function formatRelativeTime(timestamp) {
      const hours = Math.max(0, Math.round((Date.now() - Number(timestamp || Date.now())) / 3600000));
      if (hours < 1) return 'Hace unos minutos';
      if (hours < 24) return `Hace ${hours} h`;
      const days = Math.round(hours / 24);
      return `Hace ${days} día${days === 1 ? '' : 's'}`;
    }

    function formatRelativeTime(timestamp) {
      const diffMs = Math.max(0, Date.now() - Number(timestamp || Date.now()));
      const minutes = Math.round(diffMs / 60000);
      if (minutes < 60) return minutes <= 1 ? 'Hace 1 minuto' : `Hace ${minutes} minutos`;
      const hours = Math.round(diffMs / 3600000);
      if (hours < 24) return `Hace ${hours} h`;
      const days = Math.round(hours / 24);
      if (days < 30) return `Hace ${days} día${days === 1 ? '' : 's'}`;
      const months = Math.max(1, Math.round(days / 30));
      return months === 1 ? 'Hace 1 mes' : `Hace ${months} meses`;
    }

    const OPPORTUNITY_CATEGORY_ORDER = ['Piso', 'Casa/Chalet', 'Local Comercial', 'Nave', 'Oficina', 'Edificio', 'Suelo/Terreno', 'Otros'];

    function normalizeOpportunityCategory(value = '') {
      const raw = cleanText(value);
      if (!raw) return 'Otros';
      const normalized = normalizeMatchText(raw);
      if (normalized.includes('piso')) return 'Piso';
      if (normalized.includes('casa') || normalized.includes('chalet')) return 'Casa/Chalet';
      if (normalized.includes('local')) return 'Local Comercial';
      if (normalized.includes('nave')) return 'Nave';
      if (normalized.includes('oficina')) return 'Oficina';
      if (normalized.includes('edificio')) return 'Edificio';
      if (normalized.includes('suelo') || normalized.includes('terreno') || normalized.includes('solar')) return 'Suelo/Terreno';
      return 'Otros';
    }

    function getOpportunityCategoryRank(value = '') {
      const category = normalizeOpportunityCategory(value);
      const index = OPPORTUNITY_CATEGORY_ORDER.indexOf(category);
      return index >= 0 ? index : OPPORTUNITY_CATEGORY_ORDER.length;
    }

    function calculatePublicationOpportunityScore(record = {}, kind = 'property') {
      let score = kind === 'need' ? 50 : 48;
      const description = cleanText(record.description || '');
      const price = Number(record.price || record.budget) || 0;
      if (cleanText(record.title).length >= 18) score += 8;
      if (description.length >= 90) score += 10;
      if (price > 0) score += 8;
      if (cleanText(record.postalCode).length === 5) score += 8;
      if (cleanText(record.province)) score += 6;
      if (cleanText(record.municipality)) score += 6;
      if (Number(record.surface) > 0) score += 4;
      if (Number(record.bedrooms) > 0) score += 3;
      if (Number(record.bathrooms) > 0) score += 3;
      if (kind === 'property') {
        if (cleanText(record.docs) && !/pendiente/i.test(String(record.docs))) score += 8;
        if (record.exclusive) score += 6;
        if (cleanText(record.urgency).toLowerCase() === 'alta') score += 4;
      } else {
        if (cleanText(record.funding)) score += 6;
        if (cleanText(record.feeSplit)) score += 4;
        if (cleanText(record.buyerType)) score += 4;
        if (cleanText(record.urgency).toLowerCase() === 'alta') score += 3;
      }
      return Math.max(55, Math.min(98, score));
    }

    function createPropertyReference(property = {}, index = 0) {
      const explicitReference = cleanText(property.reference || property.xmlReference || '');
      if (explicitReference) return explicitReference;
      const source = String(property.id || `${property.title || 'captacion'}-${property.location || property.province || 'espana'}-${index}`);
      let hash = 2166136261;
      for (let charIndex = 0; charIndex < source.length; charIndex++) {
        hash ^= source.charCodeAt(charIndex);
        hash = Math.imul(hash, 16777619);
      }
      return `REF-${String(hash >>> 0).slice(-8).padStart(8, '0')}`;
    }

    const RESIDENTIAL_PROPERTY_TYPES = ['Piso', 'Casa / chalet', 'Ático', 'Dúplex', 'Apartamento', 'Estudio', 'Finca rústica con vivienda', 'Edificio residencial'];
    const BATHROOM_PROPERTY_TYPES = [...RESIDENTIAL_PROPERTY_TYPES, 'Local comercial', 'Nave', 'Oficina'];
    const ALL_PROPERTY_CONDITIONS = ['Lista para entrar / operar', 'Buen estado', 'De origen', 'Sin reforma necesaria', 'Necesita actualización', 'Reforma menor', 'Reforma mayor', 'Reforma integral', 'En obras', 'Obra nueva', 'No califica'];
    const COMMERCIAL_PROPERTY_CONDITIONS = ['Lista para entrar / operar', 'Buen estado', 'Necesita actualización', 'Reforma menor', 'Reforma mayor', 'Reforma integral', 'En obras', 'No califica'];
    const STORAGE_PROPERTY_CONDITIONS = ['Buen estado', 'Necesita actualización', 'No califica'];

    function normalizePropertyType(value = '') {
      const legacy = {
        'Casa/Chalet': 'Casa / chalet',
        'Casa / Chalet': 'Casa / chalet',
        'Local Comercial': 'Local comercial',
        'Edificio': 'Edificio residencial',
        'Suelo/Terreno': 'Terreno / solar',
        'Suelo / Terreno': 'Terreno / solar'
      };
      return legacy[value] || cleanText(value || 'Activo inmobiliario');
    }

    function conditionsForPropertyType(type = '') {
      const normalizedType = normalizePropertyType(type);
      if (RESIDENTIAL_PROPERTY_TYPES.includes(normalizedType)) return ALL_PROPERTY_CONDITIONS;
      if (['Local comercial', 'Nave', 'Oficina'].includes(normalizedType)) return COMMERCIAL_PROPERTY_CONDITIONS;
      if (normalizedType === 'Terreno / solar') return ['No califica'];
      if (['Garaje', 'Trastero'].includes(normalizedType)) return STORAGE_PROPERTY_CONDITIONS;
      return ['No califica'];
    }

    function selectedValues(select) {
      return select ? Array.from(select.selectedOptions || []).map(option => cleanText(option.value)).filter(Boolean) : [];
    }

    function updatePropertyFormDynamics(mode = 'offer') {
      const isNeed = mode === 'need';
      const prefix = isNeed ? 'need-pub' : 'offer';
      const type = normalizePropertyType(document.getElementById(`${prefix}-type`)?.value || '');
      const rooms = document.getElementById(`${prefix}-${isNeed ? 'bedrooms' : 'bedrooms'}`);
      const bathrooms = document.getElementById(`${prefix}-${isNeed ? 'bathrooms' : 'bathrooms'}`);
      const roomWrap = rooms?.closest('div');
      const bathroomWrap = bathrooms?.closest('div');
      const requiresRooms = RESIDENTIAL_PROPERTY_TYPES.includes(type);
      const requiresBathrooms = BATHROOM_PROPERTY_TYPES.includes(type);
      if (roomWrap) roomWrap.classList.toggle('hidden', !requiresRooms);
      if (rooms) {
        rooms.required = requiresRooms;
        rooms.min = type === 'Estudio' ? '0' : '1';
        if (!requiresRooms) rooms.value = '';
      }
      if (bathroomWrap) bathroomWrap.classList.toggle('hidden', !requiresBathrooms);
      if (bathrooms) {
        bathrooms.required = requiresBathrooms;
        bathrooms.min = requiresBathrooms ? '1' : '0';
        if (!requiresBathrooms) bathrooms.value = '';
      }
      const conditionSelect = document.getElementById(isNeed ? 'need-pub-condition' : 'offer-condition');
      if (conditionSelect) {
        const previous = selectedValues(conditionSelect);
        const options = conditionsForPropertyType(type);
        conditionSelect.innerHTML = options.map(option => `<option value="${escapeHTML(option)}">${escapeHTML(option)}</option>`).join('');
        options.forEach((option, index) => {
          conditionSelect.options[index].selected = previous.includes(option) || (!previous.length && index === 0);
        });
      }
    }

    function normalizePropertyRecord(property = {}, index = 0) {
      const neighborhoodParts = String(property.neighborhood || '').split('·').map(part => part.trim());
      const rawLocation = property.location || property.province || 'España';
      let ccaa = property.ccaa || rawLocation;
      if (rawLocation === 'Barcelona') ccaa = 'Cataluña';
      if (rawLocation === 'Ourense') ccaa = 'Galicia';
      const province = property.province || (rawLocation === 'Galicia' ? 'Ourense' : rawLocation);
      const municipality = property.municipality || neighborhoodParts[0] || province;
      const locality = property.locality || neighborhoodParts[1] || '';
      const territory = enrichTerritoryFields({
        ...property,
        ccaa,
        province,
        municipality
      });
      return {
        ...property,
        id: cleanText(property.id || `prop-${Date.now()}-${index}`),
        reference: createPropertyReference(property, index),
        title: cleanText(property.title || 'Captación inmobiliaria'),
        type: normalizePropertyType(property.property_type || property.type || 'Activo inmobiliario'),
        property_type: normalizePropertyType(property.property_type || property.type || 'Activo inmobiliario'),
        ccaa: cleanText(territory.autonomous_community_name || ccaa),
        province: cleanText(territory.province_name || province),
        municipality: cleanText(territory.municipality_name || municipality),
        autonomous_community_id: cleanText(territory.autonomous_community_id || ''),
        autonomous_community_name: cleanText(territory.autonomous_community_name || ccaa),
        province_id: cleanText(territory.province_id || ''),
        province_name: cleanText(territory.province_name || province),
        municipality_id: cleanText(territory.municipality_id || ''),
        municipality_ine_code: cleanText(territory.municipality_ine_code || ''),
        municipality_name: cleanText(territory.municipality_name || municipality),
        locality: cleanText(locality),
        postalCode: cleanText(property.postalCode || property.zipCode || property.zip || property.codigoPostal || property.codigo_postal || ''),
        bedrooms: Number(property.rooms ?? property.bedrooms ?? property.habitaciones ?? property.dormitorios) || 0,
        rooms: Number(property.rooms ?? property.bedrooms ?? property.habitaciones ?? property.dormitorios) || 0,
        bathrooms: Number(property.bathrooms ?? property.banos ?? property['baños']) || 0,
        surface: Number(property.total_area_m2 ?? property.superficie_construida ?? property.surface ?? property.surfaceM2 ?? property.superficie ?? property.metros) || 0,
        total_area_m2: Number(property.total_area_m2 ?? property.superficie_construida ?? property.surface ?? property.surfaceM2 ?? property.superficie ?? property.metros) || 0,
        location: cleanText(property.location || province),
        neighborhood: cleanText(property.neighborhood || `${province}${locality ? ' · ' + locality : ''}`),
        fee: cleanText(property.offered_commission || property.fee || 'A consultar'),
        description: cleanText(property.description || ''),
        badgeText: cleanText(property.badgeText || 'Colaboración B2B'),
        property_condition: cleanText(property.property_condition || (property.necesita_reforma_integral || property.rehab ? 'Reforma integral' : '')),
        mandate_type: cleanText(property.mandate_type || (property.exclusive ? 'Exclusiva compartida' : 'No, nota de encargo abierta')),
        urgency: cleanText(property.sale_urgency || property.urgency || 'Media'),
        sale_urgency: cleanText(property.sale_urgency || property.urgency || 'Media'),
        docs: cleanText(property.documentation_level || property.docs || 'No califica'),
        documentation_level: cleanText(property.documentation_level || property.docs || 'No califica'),
        fundingConditions: cleanText(property.fundingConditions || ''),
        date: Number(property.date) || Date.now() - (index + 1) * 3600000 * 8,
        score: Number(property.score) || 80,
        price: Number(property.indicative_price ?? property.price) || 0,
        indicative_price: Number(property.indicative_price ?? property.price) || 0,
        offered_commission: cleanText(property.offered_commission || property.fee || 'A consultar'),
        image: property.image || '',
        imageIsDefault: Boolean(property.imageIsDefault || !property.image)
      };
    }

    function normalizeNeedRecord(need = {}, index = 0) {
      const territory = enrichTerritoryFields(need);
      return {
        ...need,
        id: cleanText(need.id || `need-${Date.now()}-${index}`),
        title: cleanText(need.title || 'Demanda inmobiliaria activa'),
        type: normalizePropertyType(need.property_type || need.type || 'Activo inmobiliario'),
        property_type: normalizePropertyType(need.property_type || need.type || 'Activo inmobiliario'),
        operation: cleanText(need.operation || 'Venta'),
        buyerType: cleanText(need.buyerType || 'Comprador'),
        urgency: cleanText(need.search_urgency || need.urgency || 'Media'),
        search_urgency: cleanText(need.search_urgency || need.urgency || 'Media'),
        funding: cleanText(need.funding || 'A consultar'),
        ccaa: cleanText(territory.autonomous_community_name || need.ccaa || 'España'),
        province: cleanText(territory.province_name || need.province || ''),
        municipality: cleanText(territory.municipality_name || need.municipality || ''),
        autonomous_community_id: cleanText(territory.autonomous_community_id || ''),
        autonomous_community_name: cleanText(territory.autonomous_community_name || need.ccaa || ''),
        province_id: cleanText(territory.province_id || ''),
        province_name: cleanText(territory.province_name || need.province || ''),
        municipality_id: cleanText(territory.municipality_id || ''),
        municipality_ine_code: cleanText(territory.municipality_ine_code || ''),
        municipality_name: cleanText(territory.municipality_name || need.municipality || ''),
        locality: cleanText(need.locality || ''),
        postalCode: cleanText(need.postalCode || need.zipCode || need.zip || need.codigoPostal || need.codigo_postal || ''),
        bedrooms: Number(need.min_rooms ?? need.bedrooms ?? need.rooms ?? need.habitaciones ?? need.dormitorios) || 0,
        min_rooms: Number(need.min_rooms ?? need.bedrooms ?? need.rooms ?? need.habitaciones ?? need.dormitorios) || 0,
        bathrooms: Number(need.min_bathrooms ?? need.bathrooms ?? need.banos ?? need['baños']) || 0,
        min_bathrooms: Number(need.min_bathrooms ?? need.bathrooms ?? need.banos ?? need['baños']) || 0,
        surface: Number(need.desired_area_min_m2 ?? need.surface ?? need.surfaceM2 ?? need.superficie ?? need.metros) || 0,
        desired_area_min_m2: Number(need.desired_area_min_m2 ?? need.surface ?? need.surfaceM2 ?? need.superficie ?? need.metros) || 0,
        feeSplit: cleanText(need.accepted_commission || need.feeSplit || 'A consultar'),
        description: cleanText(need.description || ''),
        accepted_commission: cleanText(need.accepted_commission || need.feeSplit || 'A consultar'),
        accepted_property_conditions: Array.isArray(need.accepted_property_conditions) ? need.accepted_property_conditions : [],
        accepted_mandate_types: Array.isArray(need.accepted_mandate_types) ? need.accepted_mandate_types : [],
        required_documentation_level: cleanText(need.required_documentation_level || 'No califica'),
        budget: Number(need.max_budget ?? need.budget) || 0,
        max_budget: Number(need.max_budget ?? need.budget) || 0,
        date: Number(need.date) || Date.now() - (index + 1) * 3600000 * 6,
        agency: cleanText(need.agency || 'Agencia verificada')
      };
    }

    function persistDemoState() {
      try {
        localStorage.setItem('captacion_properties_v3', JSON.stringify(properties));
        localStorage.setItem('captacion_needs_v3', JSON.stringify(needs));
        localStorage.setItem('captacion_closed_operations_v4', JSON.stringify(closedOperations));
      } catch (error) {
        console.warn('No se pudo persistir el estado local de la demo.', error);
      }
    }

    function renderHome() {
      renderHomeCounters();
      renderHomeFeaturedProperty();
      renderHomeLatestProperties();
      renderHomeLatestNeeds();
      updateAuthModule();
      if (homeMap) renderHomeMapMarkers();
    }

    function renderHomeCounters() {
      const mappings = [
        ['home-stat-properties', properties.length],
        ['home-stat-needs', needs.length],
        ['home-map-properties', properties.length],
        ['home-map-needs', needs.length]
      ];
      mappings.forEach(([id, value]) => {
        const el = document.getElementById(id);
        if (el) el.textContent = value;
      });
      const propertiesValueEl = document.getElementById('home-stat-properties-value');
      if (propertiesValueEl) {
        const totalPropertiesValue = properties.reduce((sum, item) => sum + (Number(item.price) || 0), 0);
        propertiesValueEl.textContent = `${formatCurrency(totalPropertiesValue)} en valor visible`;
      }
      const needsValueEl = document.getElementById('home-stat-needs-value');
      if (needsValueEl) {
        const totalNeedsValue = needs.reduce((sum, item) => sum + (Number(item.budget) || 0), 0);
        needsValueEl.textContent = `${formatCurrency(totalNeedsValue)} en demanda activa`;
      }
      const salesMatches = getSalesMatchRecords();
      const salesCountEl = document.getElementById('home-stat-sales-matches'); if (salesCountEl) salesCountEl.textContent = salesMatches.length;
      const salesValueEl = document.getElementById('home-stat-sales-value'); if (salesValueEl) salesValueEl.textContent = `${formatCurrency(salesMatches.reduce((sum,item)=>sum+item.estimatedValue,0))} estimados`;
      const zones = new Set([
        ...properties.map(item => item.province || item.location).filter(Boolean),
        ...needs.map(item => item.province || item.ccaa).filter(Boolean)
      ]);
      const zonesEl = document.getElementById('home-map-zones');
      if (zonesEl) zonesEl.textContent = zones.size;
      const statZonesEl = document.getElementById('home-stat-zones');
      if (statZonesEl) statZonesEl.textContent = zones.size;
    }

    function renderHomeFeaturedProperty() {
      const container = document.getElementById('home-featured-card');
      if (!container) return;
      container.innerHTML = `
        <div class="overflow-hidden rounded-[24px] border border-slate-200/60 bg-white shadow-xl">
          <div id="home-explainer-video-slot" class="aspect-video overflow-hidden bg-slate-100">
            <video class="h-full w-full object-cover" autoplay muted loop playsinline controls preload="metadata" aria-label="Video de presentación de Captacion.app">
              <source src="<?php echo esc_url($captacion_theme_uri . '/media/'); ?>video-explicativo-captacion-app.mp4" type="video/mp4">
              Tu navegador no puede reproducir este video.
            </video>
            <!--
              Sustituir el contenido interior de #home-explainer-video-slot por un reproductor cuando el vídeo esté disponible.
              Ejemplo recomendado:
              <video class="h-full w-full object-cover" controls preload="metadata" poster="<?php echo esc_url($captacion_theme_uri . '/media/'); ?>poster-video-captacion-app.webp">
                <source src="<?php echo esc_url($captacion_theme_uri . '/media/'); ?>video-explicativo-captacion-app.mp4" type="video/mp4">
                <source src="<?php echo esc_url($captacion_theme_uri . '/media/'); ?>video-explicativo-captacion-app.webm" type="video/webm">
              </video>
            -->
          </div>
        </div>`;
      const video = container.querySelector('video');
      if (video) {
        const tryPlay = () => video.play().catch(() => {});
        video.muted = true;
        video.defaultMuted = true;
        video.loop = true;
        video.autoplay = true;
        if ('IntersectionObserver' in window) {
          const observer = new IntersectionObserver((entries) => {
            if (entries.some(entry => entry.isIntersecting)) {
              tryPlay();
              observer.disconnect();
            }
          }, { rootMargin: '120px' });
          observer.observe(video);
        } else {
          tryPlay();
        }
      }
    }

    function scrollHomeCarousel(trackId, direction = 1) {
      const track = document.getElementById(trackId);
      if (!track) return;
      const firstCard = track.querySelector('.home-carousel-card');
      const gap = Number.parseFloat(window.getComputedStyle(track).gap) || 16;
      const amount = (firstCard ? firstCard.getBoundingClientRect().width : track.clientWidth) + gap;
      track.scrollBy({ left: direction * amount, behavior: 'smooth' });
    }

    function renderHomeFeaturedProperty() {
      const container = document.getElementById('home-featured-card');
      if (!container) return;
      container.innerHTML = `
        <div class="space-y-4 lg:space-y-5">
          <div class="overflow-hidden rounded-[24px] border border-slate-200/60 bg-white shadow-xl">
            <div id="home-explainer-video-slot" class="aspect-video overflow-hidden bg-slate-100">
              <video class="h-full w-full object-cover object-top" autoplay muted loop playsinline controls preload="metadata" aria-label="Video de presentación de Captacion.app">
                <source src="<?php echo esc_url($captacion_theme_uri . '/media/'); ?>video-explicativo-captacion-app.mp4" type="video/mp4">
                Tu navegador no puede reproducir este video.
              </video>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-1 gap-3">
            <div class="p-4 rounded-2xl bg-white/95 border border-slate-200 shadow-sm">
              <span class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 font-black">Control de acceso</span>
              <p class="text-xs text-slate-600 mt-3 leading-relaxed">La informacion sensible no se comparte: se desbloquea cuando el contexto comercial está validado.</p>
            </div>
            <div class="p-4 rounded-2xl bg-white/95 border border-slate-200 shadow-sm">
              <span class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 font-black">Trazabilidad</span>
              <p class="text-xs text-slate-600 mt-3 leading-relaxed">Cada solicitud, interes y paso operativo queda registrado para reducir friccion, duplicidades y malentendidos.</p>
            </div>
            <div class="p-4 rounded-2xl bg-white/95 border border-slate-200 shadow-sm">
              <span class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 font-black">Colaboracion util</span>
              <p class="text-xs text-slate-600 mt-3 leading-relaxed">No se trata de listar inmuebles sin más, sino de activar coincidencias con mejores criterios comerciales y más opciones de cierre.</p>
            </div>
          </div>
        </div>`;
      const video = container.querySelector('video');
      if (video) {
        const tryPlay = () => video.play().catch(() => {});
        video.muted = true;
        video.defaultMuted = true;
        video.loop = true;
        video.autoplay = true;
        if ('IntersectionObserver' in window) {
          const observer = new IntersectionObserver((entries) => {
            if (entries.some(entry => entry.isIntersecting)) {
              tryPlay();
              observer.disconnect();
            }
          }, { rootMargin: '120px' });
          observer.observe(video);
        } else {
          tryPlay();
        }
      }
    }

    function renderHomeLatestProperties() {
      const container = document.getElementById('home-latest-properties');
      if (!container) return;
      const latest = [...properties].sort((a, b) => b.date - a.date).slice(0, 30);
      if (!latest.length) {
        container.innerHTML = '<div class="home-carousel-card p-8 rounded-2xl bg-white border border-slate-200 text-sm text-slate-500">No hay captaciones activas publicadas.</div>';
        return;
      }
      container.innerHTML = latest.map(property => {
        const cardImage = escapeHTML(resolveMarketplaceImage(property.image, property.type));
        return `
        <article class="home-carousel-card bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition-all">
          <div>
            <div class="relative h-36 overflow-hidden bg-slate-100">
              <img src="${cardImage}" data-virtual-type="${escapeHTML(property.type || 'Activo inmobiliario')}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" class="absolute inset-0 w-full h-full object-cover" alt="Imagen de ${escapeHTML(property.title)}" />
              <div class="absolute inset-0 bg-gradient-to-t from-navy/75 via-transparent to-transparent"></div>
              <span class="absolute left-3 bottom-3 px-2 py-1 rounded-full bg-white/90 text-blue text-[10px] font-bold uppercase">${escapeHTML(property.type || 'Activo')}</span>
            </div>
            <div class="p-4">
              <div class="flex items-center justify-between gap-3">
                <span class="text-[10px] text-slate-400">${formatRelativeTime(property.date)}</span>
                <span class="text-[10px] text-blue font-bold">C.P. ${escapeHTML(maskPublicPostalCode(property.postalCode))}</span>
              </div>
              <h3 class="text-sm font-extrabold text-navy leading-snug mt-3 line-clamp-2">${escapeHTML(property.title)}</h3>
              <p class="text-[10px] text-slate-500 mt-2">${formatPropertyFeatures(property, true)}</p>
              <div class="flex items-end justify-between gap-3 mt-4 pt-3 border-t border-slate-100">
                <div><span class="metric-label">Precio</span><strong class="metric-value text-sm">${formatCurrency(property.price)}</strong></div>
                <button onclick="openAccessModal('${property.id}')" class="px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Solicitar acceso</button>
              </div>
            </div>
          </div>
        </article>`;
      }).join('');
      container.scrollLeft = 0;
    }

    function renderHomeLatestNeeds() {
      const container = document.getElementById('home-latest-needs');
      if (!container) return;
      const latest = [...needs].sort((a, b) => b.date - a.date).slice(0, 30);
      if (!latest.length) {
        container.innerHTML = '<div class="home-carousel-card p-8 rounded-2xl bg-white border border-slate-200 text-sm text-slate-500">No hay demandas activas publicadas.</div>';
        return;
      }
      container.innerHTML = latest.map(need => `
        <article class="home-carousel-card bg-white rounded-2xl border border-slate-200 shadow-sm p-5 flex flex-col justify-between hover:shadow-md transition-all">
          <div>
            <div class="flex items-center justify-between gap-3">
              <span class="px-2 py-1 rounded-full bg-green-light text-green text-[10px] font-black uppercase">${escapeHTML(need.buyerType || 'Comprador')}</span>
              <span class="text-[10px] text-slate-400">${formatRelativeTime(need.date)}</span>
            </div>
            <h3 class="text-base font-extrabold text-navy leading-snug mt-4">${escapeHTML(need.title)}</h3>
            <p class="text-xs text-slate-500 mt-2 line-clamp-2">${escapeHTML(need.description)}</p>
            <p class="text-[10px] text-green font-black mt-3">C.P. ${escapeHTML(maskPublicPostalCode(need.postalCode))} · ${formatPropertyFeatures(need, true)}</p>
          </div>
          <div class="flex items-end justify-between gap-4 mt-5 pt-4 border-t border-slate-100">
            <div><span class="block text-[9px] text-slate-400 uppercase font-black">Presupuesto máximo</span><strong class="text-sm text-navy">${formatCurrency(need.budget)}</strong></div>
            <button type="button" onclick="openHomeNeedMatches('${need.id}')" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-black">Ver demanda y coincidencias</button>
          </div>
        </article>`).join('');
      container.scrollLeft = 0;
    }

    function getApproximatePoint(item, index = 0) {
      const keyCandidates = [item.municipality, item.province === 'Madrid' ? 'Madrid ciudad' : item.province, item.ccaa, item.location];
      let point = null;
      for (const key of keyCandidates) {
        if (key && geoCenters[key]) {
          point = geoCenters[key];
          break;
        }
      }
      point = point || [40.2, -3.7];
      const seed = String(item.id || index).split('').reduce((sum, char) => sum + char.charCodeAt(0), 0);
      const latOffset = ((seed % 7) - 3) * 0.035;
      const lngOffset = (((seed * 3) % 7) - 3) * 0.035;
      return [point[0] + latOffset, point[1] + lngOffset];
    }

    function formatMapAmount(value) {
      const amount = Number(value) || 0;
      if (amount >= 1000000) {
        const millions = amount / 1000000;
        return `${millions >= 10 ? Math.round(millions) : millions.toFixed(1).replace('.0', '')}M`;
      }
      if (amount >= 1000) return `${Math.round(amount / 1000)}K`;
      return `${Math.round(amount)}€`;
    }

    function createMapAmountIcon(value, kind = 'property') {
      const pillClass = kind === 'property' ? 'map-price-pill' : 'map-demand-pill';
      const label = escapeHTML(formatMapAmount(value));
      return L.divIcon({
        className: 'map-label-div-icon',
        html: `<span class="${pillClass}">${label}</span>`,
        iconSize: [64, 28],
        iconAnchor: [32, 14]
      });
    }

    function addBaseTileLayer(map) {
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(map);
    }

    function fitMapToApproximatePoints(map, points, fallbackZoom = 5.7) {
      if (!map || !points.length) {
        map?.setView(SPAIN_DEFAULT_MAP_CENTER, fallbackZoom);
        return;
      }
      if (points.length === 1) {
        map.setView(points[0], 11);
        return;
      }
      map.fitBounds(L.latLngBounds(points), { padding: [32, 32], maxZoom: 12 });
    }

    function resetMapToSpain(map) {
      if (!map) return;
      setTimeout(() => {
        map.invalidateSize?.();
        map.setView(SPAIN_DEFAULT_MAP_CENTER, SPAIN_DEFAULT_MAP_ZOOM, { animate: true });
      }, 80);
    }

    function updateHomeMapAreaStatus(message = '') {
      const status = document.getElementById('home-map-area-status');
      if (!status) return;
      status.textContent = message || (homeMapSelectedBounds
        ? 'Zona dibujada activa. Solo se muestran las oportunidades incluidas dentro del perímetro seleccionado.'
        : 'Sin zona dibujada. Se muestran las oportunidades compatibles con los filtros activos del mapa.');
    }

    function homeMapItemMatchesFilters(item, index) {
      const postalCode = cleanText(homeMapPostalCodeFilter || '');
      const postalMatches = !postalCode || String(item.postalCode || '').includes(postalCode);
      const withinSelectedArea = !homeMapSelectedBounds || !window.L
        || homeMapSelectedBounds.contains(L.latLng(getApproximatePoint(item, index)));
      return postalMatches && withinSelectedArea;
    }

    function applyHomeMapPostalFilter() {
      const input = document.getElementById('home-map-postal-filter');
      const postalCode = cleanText(input?.value || '').replace(/\D/g, '').slice(0, 5);
      if (input) input.value = postalCode;
      if (!postalCode) {
        showToast('Introduce un Código Postal para filtrar el mapa de oportunidades.', 'info');
        return;
      }
      homeMapPostalCodeFilter = postalCode;
      renderHomeMapMarkers();
      updateHomeMapAreaStatus(`Filtro por C.P. ${postalCode} activo. Puedes combinarlo con una zona dibujada.`);
    }

    function bindHomeAreaDrawEvents() {
      if (!homeMap || homeMap._captacionAreaDrawBound || !window.L?.Draw) return;
      homeMap._captacionAreaDrawBound = true;
      homeMap.on(L.Draw.Event.CREATED, event => {
        if (event.layerType !== 'rectangle') return;
        if (homeMapSelectionLayer) homeMap.removeLayer(homeMapSelectionLayer);
        homeMapSelectionLayer = event.layer.addTo(homeMap);
        homeMapSelectedBounds = homeMapSelectionLayer.getBounds();
        updateHomeMapAreaStatus('Zona dibujada activa. Solo se muestran las oportunidades incluidas dentro del rectángulo seleccionado.');
        renderHomeMapMarkers();
      });
    }

    function activateHomeAreaDraw() {
      if (!homeMap) initHomeMap();
      if (!homeMap || !window.L?.Draw?.Rectangle) {
        showToast('No se pudo activar el dibujo de zona. Revisa la conexión cartográfica.', 'info');
        return;
      }
      if (homeMapDrawHandler) homeMapDrawHandler.disable();
      homeMapDrawHandler = new L.Draw.Rectangle(homeMap, {
        shapeOptions: { color: '#1b67d6', weight: 2, fillColor: '#1b67d6', fillOpacity: 0.12 }
      });
      homeMapDrawHandler.enable();
      updateHomeMapAreaStatus('Dibujo activado: arrastra el ratón sobre el mapa para delimitar la zona que deseas consultar.');
    }

    function clearHomeMapArea() {
      if (homeMap && homeMapSelectionLayer) homeMap.removeLayer(homeMapSelectionLayer);
      homeMapSelectionLayer = null;
      homeMapSelectedBounds = null;
      if (homeMapDrawHandler) homeMapDrawHandler.disable();
      homeMapDrawHandler = null;
      homeMapPostalCodeFilter = '';
      const input = document.getElementById('home-map-postal-filter');
      if (input) input.value = '';
      updateHomeMapAreaStatus();
      renderHomeMapMarkers();
      resetMapToSpain(homeMap);
    }

    function initHomeMap() {
      const mapEl = document.getElementById('home-map');
      if (!mapEl || mapEl.offsetParent === null) return;
      if (!window.L) {
        mapEl.innerHTML = '<div class="p-8 text-sm text-slate-500">No se pudo cargar el mapa interactivo. Revisa la conexión o integra un proveedor cartográfico en el despliegue final.</div>';
        return;
      }
      if (!homeMap) {
        homeMap = L.map('home-map', { scrollWheelZoom: true, boxZoom: true }).setView(SPAIN_DEFAULT_MAP_CENTER, SPAIN_DEFAULT_MAP_ZOOM);
        addBaseTileLayer(homeMap);
        homeMapLayer = L.layerGroup().addTo(homeMap);
        homeMap.scrollWheelZoom.enable();
        bindHomeAreaDrawEvents();
      }
      setTimeout(() => homeMap.invalidateSize(), 60);
      renderHomeMapMarkers();
    }

    function renderHomeMapMarkers() {
      if (!homeMap || !homeMapLayer || !window.L) return;
      homeMapLayer.clearLayers();
      const points = [];
      const addMarker = (item, kind, index) => {
        if (!homeMapItemMatchesFilters(item, index)) return;
        const point = getApproximatePoint(item, index);
        points.push(point);
        const isProperty = kind === 'property';
        const openFullCardAction = isProperty ? `openMapPropertyCard('${escapeHTML(item.id)}')` : `openMapNeedCard('${escapeHTML(item.id)}')`;
        const marker = L.marker(point, { icon: createMapAmountIcon(isProperty ? item.price : item.budget, kind) });
        marker.bindPopup(`
          <div style="min-width:220px">
            <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:${isProperty ? '#b00016' : '#087653'}">${isProperty ? 'Captación disponible' : 'Demanda activa'}</div>
            <div style="font-size:13px;font-weight:800;color:#10233c;margin-top:5px">${escapeHTML(item.title)}</div>
            <div style="font-size:11px;color:#64748b;margin-top:5px">${escapeHTML(item.province || item.location || item.ccaa || 'España')} · C.P. ${escapeHTML(maskPublicPostalCode(item.postalCode))} · ubicación aproximada</div>
            <div style="font-size:11px;color:#64748b;margin-top:5px">${formatPropertyFeatures(item, true)}</div>
            <div style="font-size:12px;font-weight:800;color:#10233c;margin-top:7px">${formatCurrency(isProperty ? item.price : item.budget)}</div>
            <button onclick="${openFullCardAction}" style="margin-top:9px;width:100%;border:0;border-radius:9px;background:#1b67d6;color:#fff;padding:8px 10px;font-size:11px;font-weight:700;cursor:pointer">Ver ficha completa</button>
          </div>`);
        marker.addTo(homeMapLayer);
      };
      if (homeMapMode === 'all' || homeMapMode === 'properties') properties.forEach((item, index) => addMarker(item, 'property', index));
      if (homeMapMode === 'all' || homeMapMode === 'needs') needs.forEach((item, index) => addMarker(item, 'need', index + properties.length));
      if (homeMapSelectedBounds) {
        homeMap.fitBounds(homeMapSelectedBounds.pad(0.05), { maxZoom: 14 });
      } else {
        fitMapToApproximatePoints(homeMap, points);
      }
    }

    function setHomeMapMode(mode) {
      homeMapMode = mode;
      ['all', 'properties', 'needs'].forEach(option => {
        const button = document.getElementById(`map-filter-${option}`);
        if (!button) return;
        button.classList.toggle('map-filter-active', option === mode);
      });
      renderHomeMapMarkers();
    }

    function getMarketplaceVisibleProperties() {
      const searchQuery = cleanText(document.getElementById('market-search-filter')?.value || '').toLowerCase();
      const referenceQuery = cleanText(document.getElementById('market-reference-filter')?.value || '').toLowerCase();
      const postalCodeQuery = cleanText(document.getElementById('market-postal-code-filter')?.value || '');
      const categoryFilter = document.getElementById('market-category-filter')?.value || 'all';
      const ccaaFilter = document.getElementById('market-ccaa-filter')?.value || 'all';
      const provinceFilter = document.getElementById('market-province-filter')?.value || 'all';
      const municipalityFilter = document.getElementById('market-municipality-filter')?.value || 'all';
      const priceFilter = document.getElementById('market-price-filter')?.value || 'all';
      const sortValue = document.getElementById('market-sort')?.value || 'newest';
      const [minPrice, maxPrice] = priceFilter === 'all' ? [0, Number.POSITIVE_INFINITY] : priceFilter.split('-').map(Number);
      const filtered = properties.filter((prop, index) => {
        const price = Number(prop.price) || 0;
        const haystack = [prop.title, prop.reference, prop.type, prop.ccaa, prop.province, prop.municipality, prop.location, prop.postalCode, prop.description]
          .map(value => String(value || '').toLowerCase()).join(' ');
        const withinSelectedMapArea = !marketplaceMapSelectedBounds || !window.L
          || marketplaceMapSelectedBounds.contains(L.latLng(getApproximatePoint(prop, index)));
        return (!searchQuery || haystack.includes(searchQuery))
          && (!referenceQuery || String(prop.reference || '').toLowerCase().includes(referenceQuery))
          && (!postalCodeQuery || String(prop.postalCode || '').includes(postalCodeQuery))
          && (categoryFilter === 'all' || normalizeOpportunityCategory(prop.type) === categoryFilter)
          && (ccaaFilter === 'all' || prop.ccaa === ccaaFilter)
          && (provinceFilter === 'all' || prop.province === provinceFilter)
          && (municipalityFilter === 'all' || prop.municipality === municipalityFilter)
          && price >= minPrice && price <= maxPrice
          && withinSelectedMapArea;
      });
      return filtered.sort((a, b) => {
        if (sortValue === 'price-low') return (Number(a.price) || 0) - (Number(b.price) || 0);
        if (sortValue === 'price-high') return (Number(b.price) || 0) - (Number(a.price) || 0);
        if (sortValue === 'score') return (Number(b.score || calculatePublicationOpportunityScore(b, 'property')) || 0) - (Number(a.score || calculatePublicationOpportunityScore(a, 'property')) || 0);
        if (sortValue === 'category') {
          return getOpportunityCategoryRank(a.type) - getOpportunityCategoryRank(b.type)
            || (Number(b.date) || 0) - (Number(a.date) || 0);
        }
        if (sortValue === 'oldest') return (Number(a.date) || 0) - (Number(b.date) || 0);
        return (Number(b.date) || 0) - (Number(a.date) || 0);
      });
    }

    function updateMarketplaceViewButtons() {
      const mapBtn = document.getElementById('marketplace-view-map-btn');
      const blockBtn = document.getElementById('marketplace-layout-block-btn');
      const listBtn = document.getElementById('marketplace-layout-list-btn');
      const states = [
        [mapBtn, marketplaceViewMode === 'map'],
        [blockBtn, marketplaceViewMode === 'cards' && marketplaceLayoutMode === 'block'],
        [listBtn, marketplaceViewMode === 'cards' && marketplaceLayoutMode === 'list']
      ];
      states.forEach(([button, active]) => {
        button?.classList.toggle('map-view-active', active);
        button?.classList.toggle('text-slate-500', !active);
      });
    }

    function setMarketplaceView(mode) {
      marketplaceViewMode = mode === 'map' ? 'map' : 'cards';
      const mapPanel = document.getElementById('marketplace-map-panel');
      const cardsGrid = document.getElementById('marketplace-grid');
      mapPanel?.classList.toggle('hidden', marketplaceViewMode !== 'map');
      cardsGrid?.classList.toggle('hidden', marketplaceViewMode === 'map');
      updateMarketplaceViewButtons();
      if (marketplaceViewMode === 'map') setTimeout(initMarketplaceMap, 0);
    }

    function setMarketplaceLayout(layout = 'block') {
      marketplaceLayoutMode = layout === 'list' ? 'list' : 'block';
      marketplaceViewMode = 'cards';
      renderMarketplace();
      setMarketplaceView('cards');
    }

    function refreshMarketplaceView() {
      marketplaceVisibleLimit = LIST_BATCH_SIZE;
      marketplaceCarouselOffset = 0;
      const mapPostalInput = document.getElementById('market-map-postal-filter');
      const mainPostalInput = document.getElementById('market-postal-code-filter');
      if (mapPostalInput && document.activeElement !== mapPostalInput) mapPostalInput.value = mainPostalInput?.value || '';
      renderMarketplaceDashboard();
      renderMarketplace();
      if (marketplaceViewMode === 'map') setTimeout(initMarketplaceMap, 0);
    }

    function clearMarketplaceFilters() {
      const setters = {
        'market-search-filter': '', 'market-reference-filter': '', 'market-postal-code-filter': '', 'market-map-postal-filter': '', 'market-price-filter': 'all', 'market-category-filter': 'all', 'market-ccaa-filter': 'all', 'market-province-filter': 'all', 'market-municipality-filter': 'all', 'market-sort': 'newest'
      };
      Object.entries(setters).forEach(([id, value]) => { const element = document.getElementById(id); if (element) element.value = value; });
      TerritorySelector.instances['marketplace-filter']?.setValues({ccaa:'all',province:'all',municipality:'all',postalCode:''});
      clearMarketplaceMapArea(true);
      refreshMarketplaceView();
    }

    function filterMarketplaceByDashboard(type, value) {
      const searchEl = document.getElementById('market-search-filter');
      const refEl = document.getElementById('market-reference-filter');
      const cpEl = document.getElementById('market-postal-code-filter');
      if (refEl) refEl.value = '';
      if (cpEl) cpEl.value = '';
      if (searchEl) searchEl.value = value || '';
      refreshMarketplaceView();
    }

    function updateMarketplaceMapAreaStatus(message = '') {
      const status = document.getElementById('marketplace-map-area-status');
      if (!status) return;
      status.textContent = message || (marketplaceMapSelectedBounds
        ? 'Zona dibujada activa. Solo se muestran las ofertas situadas dentro del perímetro seleccionado.'
        : 'Sin zona dibujada. Se muestran las ofertas compatibles con los filtros activos.');
    }

    function applyMarketplaceMapPostalFilter() {
      const mapPostalInput = document.getElementById('market-map-postal-filter');
      const mainPostalInput = document.getElementById('market-postal-code-filter');
      const postalCode = cleanText(mapPostalInput?.value || '').replace(/\D/g, '').slice(0, 5);
      if (mapPostalInput) mapPostalInput.value = postalCode;
      if (!postalCode) {
        showToast('Introduce un Código Postal para filtrar las ofertas del mapa.', 'info');
        return;
      }
      if (mainPostalInput) mainPostalInput.value = postalCode;
      refreshMarketplaceView();
      setMarketplaceView('map');
      updateMarketplaceMapAreaStatus(`Filtro por C.P. ${postalCode} activo. Puedes combinarlo con una zona dibujada.`);
    }

    function bindMarketplaceAreaDrawEvents() {
      if (!marketplaceMap || marketplaceMap._captacionAreaDrawBound || !window.L?.Draw) return;
      marketplaceMap._captacionAreaDrawBound = true;
      marketplaceMap.on(L.Draw.Event.CREATED, event => {
        if (event.layerType !== 'rectangle') return;
        if (marketplaceMapSelectionLayer) marketplaceMap.removeLayer(marketplaceMapSelectionLayer);
        marketplaceMapSelectionLayer = event.layer.addTo(marketplaceMap);
        marketplaceMapSelectedBounds = marketplaceMapSelectionLayer.getBounds();
        marketplaceVisibleLimit = LIST_BATCH_SIZE;
        updateMarketplaceMapAreaStatus('Zona dibujada activa. Solo se muestran las ofertas incluidas dentro del rectángulo seleccionado.');
        renderMarketplace();
        renderMarketplaceMapMarkers();
      });
    }

    function activateMarketplaceAreaDraw() {
      if (!marketplaceMap) initMarketplaceMap();
      if (!marketplaceMap || !window.L?.Draw?.Rectangle) {
        showToast('No se pudo activar el dibujo de zona. Revisa la conexión cartográfica.', 'info');
        return;
      }
      if (marketplaceMapDrawHandler) marketplaceMapDrawHandler.disable();
      marketplaceMapDrawHandler = new L.Draw.Rectangle(marketplaceMap, {
        shapeOptions: { color: '#1b67d6', weight: 2, fillColor: '#1b67d6', fillOpacity: 0.12 }
      });
      marketplaceMapDrawHandler.enable();
      updateMarketplaceMapAreaStatus('Dibujo activado: arrastra el ratón sobre el mapa para delimitar la zona que deseas consultar.');
    }

    function clearMarketplaceMapArea(skipRefresh = false) {
      if (marketplaceMap && marketplaceMapSelectionLayer) marketplaceMap.removeLayer(marketplaceMapSelectionLayer);
      marketplaceMapSelectionLayer = null;
      marketplaceMapSelectedBounds = null;
      if (marketplaceMapDrawHandler) marketplaceMapDrawHandler.disable();
      marketplaceMapDrawHandler = null;
      updateMarketplaceMapAreaStatus();
      if (!skipRefresh) refreshMarketplaceView();
      resetMapToSpain(marketplaceMap);
    }

    function initMarketplaceMap() {
      const mapEl = document.getElementById('marketplace-map');
      if (!mapEl || mapEl.offsetParent === null) return;
      if (!window.L) {
        mapEl.innerHTML = '<div class="p-8 text-sm text-slate-500">No se pudo cargar el mapa de captaciones. Revisa la conexión cartográfica.</div>';
        return;
      }
      if (!marketplaceMap) {
        marketplaceMap = L.map('marketplace-map', { scrollWheelZoom: true, boxZoom: true }).setView(SPAIN_DEFAULT_MAP_CENTER, SPAIN_DEFAULT_MAP_ZOOM);
        addBaseTileLayer(marketplaceMap);
        marketplaceMapLayer = L.layerGroup().addTo(marketplaceMap);
        marketplaceMap.scrollWheelZoom.enable();
        bindMarketplaceAreaDrawEvents();
      }
      setTimeout(() => marketplaceMap.invalidateSize(), 60);
      renderMarketplaceMapMarkers();
    }

    function renderMarketplaceMapMarkers() {
      if (!marketplaceMap || !marketplaceMapLayer || !window.L) return;
      marketplaceMapLayer.clearLayers();
      const points = [];
      getMarketplaceVisibleProperties().forEach((property, index) => {
        const point = getApproximatePoint(property, index);
        points.push(point);
        const marker = L.marker(point, { icon: createMapAmountIcon(property.price, 'property') });
        marker.bindPopup(`
          <div style="min-width:230px">
            <div style="font-size:10px;font-weight:900;text-transform:uppercase;color:#b00016">Oferta disponible · Ref. ${escapeHTML(property.reference || '')}</div>
            <div style="font-size:13px;font-weight:900;color:#10233c;margin-top:5px">${escapeHTML(property.title)}</div>
            <div style="font-size:11px;color:#64748b;margin-top:5px">${escapeHTML(property.province || property.location || 'España')} · C.P. ${escapeHTML(maskPublicPostalCode(property.postalCode))} · ubicación aproximada</div>
            <div style="font-size:11px;color:#64748b;margin-top:5px">${formatPropertyFeatures(property, true)}</div>
            <div style="font-size:13px;font-weight:900;color:#10233c;margin-top:7px">${formatCurrency(property.price)}</div>
            <button onclick="openMapPropertyCard('${escapeHTML(property.id)}')" style="margin-top:9px;width:100%;border:0;border-radius:9px;background:#1b67d6;color:#fff;padding:8px 10px;font-size:11px;font-weight:700;cursor:pointer">Ver ficha completa</button>
          </div>`);
        marker.addTo(marketplaceMapLayer);
      });
      if (marketplaceMapSelectedBounds) {
        marketplaceMap.fitBounds(marketplaceMapSelectedBounds.pad(0.05), { maxZoom: 14 });
      } else {
        fitMapToApproximatePoints(marketplaceMap, points);
      }
    }

    function updateNeedsMapAreaStatus(message = '') {
      const status = document.getElementById('needs-map-area-status');
      if (!status) return;
      status.textContent = message || (needsMapSelectedBounds
        ? 'Zona dibujada activa. Solo se muestran las demandas situadas dentro del perímetro seleccionado.'
        : 'Sin zona dibujada. Se muestran las demandas compatibles con los filtros activos.');
    }

    function getNeedsMapVisibleList(list = needs) {
      const postalCode = cleanText(needsMapPostalCodeFilter || '');
      return list.filter((need, index) => {
        const globalIndex = needs.findIndex(item => String(item.id) === String(need.id));
        const coordinateIndex = properties.length + (globalIndex >= 0 ? globalIndex : index);
        const postalMatches = !postalCode || String(need.postalCode || '').includes(postalCode);
        const withinSelectedArea = !needsMapSelectedBounds || !window.L
          || needsMapSelectedBounds.contains(L.latLng(getApproximatePoint(need, coordinateIndex)));
        return postalMatches && withinSelectedArea;
      });
    }

    function applyNeedsMapPostalFilter() {
      const mapInput = document.getElementById('needs-map-postal-filter');
      const mainInput = document.getElementById('need-filter-postal-code');
      const postalCode = cleanText(mapInput?.value || '').replace(/\D/g, '').slice(0, 5);
      if (mapInput) mapInput.value = postalCode;
      if (!postalCode) {
        showToast('Introduce un Código Postal para filtrar el mapa de demandas.', 'info');
        return;
      }
      needsMapPostalCodeFilter = postalCode;
      if (mainInput) mainInput.value = postalCode;
      filterNeeds();
      updateNeedsMapAreaStatus(`Filtro por C.P. ${postalCode} activo. Puedes combinarlo con una zona dibujada.`);
    }

    function bindNeedsAreaDrawEvents() {
      if (!needsMap || needsMap._captacionAreaDrawBound || !window.L?.Draw) return;
      needsMap._captacionAreaDrawBound = true;
      needsMap.on(L.Draw.Event.CREATED, event => {
        if (event.layerType !== 'rectangle') return;
        if (needsMapSelectionLayer) needsMap.removeLayer(needsMapSelectionLayer);
        needsMapSelectionLayer = event.layer.addTo(needsMap);
        needsMapSelectedBounds = needsMapSelectionLayer.getBounds();
        updateNeedsMapAreaStatus('Zona dibujada activa. Solo se muestran las demandas incluidas dentro del rectángulo seleccionado.');
        filterNeeds();
      });
    }

    function activateNeedsAreaDraw() {
      if (!needsMap) initNeedsMap();
      if (!needsMap || !window.L?.Draw?.Rectangle) {
        showToast('No se pudo activar el dibujo de zona. Revisa la conexión cartográfica.', 'info');
        return;
      }
      if (needsMapDrawHandler) needsMapDrawHandler.disable();
      needsMapDrawHandler = new L.Draw.Rectangle(needsMap, {
        shapeOptions: { color: '#15936a', weight: 2, fillColor: '#15936a', fillOpacity: 0.12 }
      });
      needsMapDrawHandler.enable();
      updateNeedsMapAreaStatus('Dibujo activado: arrastra el ratón sobre el mapa para delimitar la zona que deseas consultar.');
    }

    function clearNeedsMapArea() {
      if (needsMap && needsMapSelectionLayer) needsMap.removeLayer(needsMapSelectionLayer);
      needsMapSelectionLayer = null;
      needsMapSelectedBounds = null;
      if (needsMapDrawHandler) needsMapDrawHandler.disable();
      needsMapDrawHandler = null;
      needsMapPostalCodeFilter = '';
      const mapInput = document.getElementById('needs-map-postal-filter');
      const mainInput = document.getElementById('need-filter-postal-code');
      if (mapInput) mapInput.value = '';
      if (mainInput) mainInput.value = '';
      updateNeedsMapAreaStatus();
      filterNeeds();
      resetMapToSpain(needsMap);
    }

    function toggleNeedsMap() {
      needsMapVisible = !needsMapVisible;
      const panel = document.getElementById('needs-map-panel');
      const button = document.getElementById('needs-map-toggle-btn');
      panel?.classList.toggle('hidden', !needsMapVisible);
      if (button) button.textContent = needsMapVisible ? '✕ Ocultar mapa de demandas' : '🗺 Mostrar mapa de demandas';
      if (needsMapVisible) setTimeout(initNeedsMap, 0);
    }

    function initNeedsMap() {
      const mapEl = document.getElementById('needs-map');
      if (!mapEl || mapEl.offsetParent === null) return;
      if (!window.L) {
        mapEl.innerHTML = '<div class="p-8 text-sm text-slate-500">No se pudo cargar el mapa de demandas. Revisa la conexión cartográfica.</div>';
        return;
      }
      if (!needsMap) {
        needsMap = L.map('needs-map', { scrollWheelZoom: true, boxZoom: true }).setView(SPAIN_DEFAULT_MAP_CENTER, SPAIN_DEFAULT_MAP_ZOOM);
        addBaseTileLayer(needsMap);
        needsMapLayer = L.layerGroup().addTo(needsMap);
        needsMap.scrollWheelZoom.enable();
        bindNeedsAreaDrawEvents();
      }
      setTimeout(() => needsMap.invalidateSize(), 60);
      renderNeedsMapMarkers(lastFilteredNeeds);
    }

    function renderNeedsMapMarkers(list = needs) {
      if (!needsMap || !needsMapLayer || !window.L) return;
      needsMapLayer.clearLayers();
      const points = [];
      getNeedsMapVisibleList(list).forEach((need, index) => {
        const globalIndex = needs.findIndex(item => String(item.id) === String(need.id));
        const coordinateIndex = properties.length + (globalIndex >= 0 ? globalIndex : index);
        const point = getApproximatePoint(need, coordinateIndex);
        points.push(point);
        const marker = L.marker(point, { icon: createMapAmountIcon(need.budget, 'need') });
        marker.bindPopup(`
          <div style="min-width:230px">
            <div style="font-size:10px;font-weight:900;text-transform:uppercase;color:#087653">Demanda activa</div>
            <div style="font-size:13px;font-weight:900;color:#10233c;margin-top:5px">${escapeHTML(need.title)}</div>
            <div style="font-size:11px;color:#64748b;margin-top:5px">${escapeHTML(need.province || need.ccaa || 'España')} · C.P. ${escapeHTML(maskPublicPostalCode(need.postalCode))} · ubicación aproximada</div>
            <div style="font-size:11px;color:#64748b;margin-top:5px">${formatPropertyFeatures(need, true)}</div>
            <div style="font-size:13px;font-weight:900;color:#10233c;margin-top:7px">Presupuesto: ${formatCurrency(need.budget)}</div>
            <button onclick="openMapNeedCard('${escapeHTML(need.id)}')" style="margin-top:9px;width:100%;border:0;border-radius:9px;background:#10233c;color:#fff;padding:8px 10px;font-size:11px;font-weight:700;cursor:pointer">Ver ficha completa</button>
          </div>`);
        marker.addTo(needsMapLayer);
      });
      if (needsMapSelectedBounds) {
        needsMap.fitBounds(needsMapSelectedBounds.pad(0.05), { maxZoom: 14 });
      } else {
        fitMapToApproximatePoints(needsMap, points);
      }
    }

    function toggleAuthPanel(mode) {
      const loginForm = document.getElementById('auth-login-form');
      const registerForm = document.getElementById('auth-register-form');
      const loginTab = document.getElementById('auth-login-tab');
      const registerTab = document.getElementById('auth-register-tab');
      if (!loginForm || !registerForm) return;
      const isLogin = mode === 'login';
      loginForm.classList.toggle('hidden', !isLogin);
      registerForm.classList.toggle('hidden', isLogin);
      loginTab?.classList.toggle('auth-tab-active', isLogin);
      registerTab?.classList.toggle('auth-tab-active', !isLogin);
      loginTab?.classList.toggle('text-slate-500', !isLogin);
      registerTab?.classList.toggle('text-slate-500', isLogin);
    }

    async function hashText(text) {
      if (window.crypto?.subtle) {
        const buffer = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(text));
        return Array.from(new Uint8Array(buffer)).map(byte => byte.toString(16).padStart(2, '0')).join('');
      }
      let hash = 0;
      for (let index = 0; index < text.length; index++) hash = ((hash << 5) - hash) + text.charCodeAt(index) | 0;
      return `fallback-${Math.abs(hash)}`;
    }

    function getDemoUsers() {
      try { return JSON.parse(localStorage.getItem('captacion_demo_users_v4')) || {}; }
      catch (error) { return {}; }
    }

    function getDemoSession() {
      try { return JSON.parse(localStorage.getItem('captacion_demo_session_v4')) || null; }
      catch (error) { return null; }
    }


    let registrationPromptTimer = null;
    let registrationPromptDismissedAt = 0;
    let registrationPromptDismissedForSession = false;
    let registrationPromptStarted = false;
    let registrationExitIntentShown = false;
    let registrationMobileIntentTimer = null;

    function hasActiveProfessionalSession() {
      return Boolean((CAPTACION_MAILCHIMP?.loggedIn && CAPTACION_MAILCHIMP?.emailVerified) || getDemoSession?.()?.emailVerified);
    }

    function getRegistrationPrompt() {
      let modal = document.getElementById('registration-required-modal');
      if (modal) return modal;
      modal = document.createElement('div');
      modal.id = 'registration-required-modal';
      modal.className = 'fixed inset-0 z-[120] hidden flex items-center justify-center p-4 bg-navy-dark/70 backdrop-blur-sm';
      modal.innerHTML = `
        <div class="relative w-full max-w-md rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 text-center">
          <button type="button" onclick="dismissRegistrationPrompt()" aria-label="Cerrar" class="absolute top-3 right-4 text-slate-400 hover:text-slate-700 text-xl font-black">x</button>
          <span class="inline-flex px-3 py-1 rounded-full bg-green-light text-green text-[10px] font-black uppercase tracking-wider">Acceso profesional</span>
          <h3 class="text-xl font-black text-navy mt-4">Accede a oportunidades inmobiliarias profesionales</h3>
          <p class="text-sm text-slate-500 mt-3 leading-relaxed">Únete a Captacion.app y conecta captaciones, demandas activas y colaboradores B2B con más control.</p>
          <div class="mt-6 grid grid-cols-1 gap-3">
            <button type="button" onclick="goToRegisterFromPrompt()" class="px-4 py-3 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md">Crear cuenta profesional</button>
            <button type="button" onclick="dismissRegistrationPrompt()" class="px-4 py-3 rounded-xl border border-slate-200 text-slate-600 text-xs font-black">Ahora no</button>
          </div>
        </div>`;
      document.body.appendChild(modal);
      return modal;
    }

    function getProfessionalSubscriptionModal() {
      let modal = document.getElementById('professional-subscription-modal');
      if (modal) return modal;
      modal = document.createElement('div');
      modal.id = 'professional-subscription-modal';
      modal.className = 'fixed inset-0 z-[130] hidden flex items-center justify-center p-4 bg-navy-dark/65 backdrop-blur-sm';
      modal.innerHTML = `
        <div class="relative w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8">
          <button type="button" onclick="closeProfessionalSubscriptionModal()" aria-label="Cerrar" class="absolute top-3 right-4 text-slate-400 hover:text-slate-700 text-xl font-black">x</button>
          <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase tracking-wider">Suscripción para profesional</span>
          <h3 class="text-2xl font-black text-navy mt-4">Crea tu cuenta profesional</h3>
          <p class="text-sm text-slate-500 mt-2">Solo pedimos los datos necesarios. Podras completar agencia y zona desde tu perfil.</p>
          <form id="professional-subscription-form" onsubmit="handleProfessionalRegistration(event)" class="mt-6 space-y-4">
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Nombre y apellidos *</span><input id="professional-register-name" type="text" required autocomplete="name" minlength="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label>
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Correo electronico *</span><input id="professional-register-email" type="email" required autocomplete="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label>
            <div class="grid grid-cols-1 sm:grid-cols-[0.95fr_1.05fr] gap-3"><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">País *</span><select id="professional-register-country" autocomplete="tel-country-code" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm bg-white">${countryCodeOptionsHtml()}</select></label><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Número de contacto *</span><input id="professional-register-phone" type="tel" required autocomplete="tel-national" inputmode="tel" placeholder="600 000 000" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label></div>
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Contrasena *</span><div class="relative"><input id="professional-register-password" type="password" required autocomplete="new-password" minlength="8" placeholder="Minimo 8 caracteres" class="w-full px-4 py-3 pr-24 rounded-xl border border-slate-200 text-sm" /><button type="button" onclick="togglePasswordVisibility('professional-register-password', this)" class="absolute inset-y-1 right-1 px-3 rounded-lg text-[10px] font-black text-blue hover:bg-blue-light">Mostrar</button></div></label>
            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-xs leading-relaxed text-slate-600 cursor-pointer"><input id="professional-register-privacy" type="checkbox" required class="mt-0.5 h-5 w-5 shrink-0" /><span>Acepto la <a href="#/privacidad" class="legal-link">politica de privacidad</a> y el tratamiento necesario para crear mi cuenta profesional. *</span></label>
            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-xs leading-relaxed text-slate-600 cursor-pointer"><input id="professional-register-marketing" type="checkbox" class="mt-0.5 h-5 w-5 shrink-0" /><span>Quiero recibir novedades y comunicaciones comerciales de Captacion.app. Opcional y revocable.</span></label>
            <p id="professional-register-error" class="hidden rounded-xl bg-red-50 border border-red-100 px-3 py-2 text-xs text-red-700" role="alert"></p>
            <button id="professional-register-submit" type="submit" class="w-full py-3.5 rounded-xl bg-blue hover:bg-blue-dark text-white text-xs font-black shadow-md">Crear cuenta profesional</button>
          </form>
        </div>`;
      document.body.appendChild(modal);
      return modal;
    }

    function openProfessionalSubscriptionModal(source = 'manual') {
      if (hasActiveProfessionalSession()) {
        window.location.hash = '#/area-privada';
        return;
      }
      getRegistrationPrompt().classList.add('hidden');
      const modal = getProfessionalSubscriptionModal();
      modal.dataset.source = source;
      modal.classList.remove('hidden');
      setTimeout(() => document.getElementById('professional-register-name')?.focus(), 50);
    }

    function closeProfessionalSubscriptionModal() {
      getProfessionalSubscriptionModal().classList.add('hidden');
    }

    function countryCodeOptionsHtml(selected = '+34') {
      const countries = [
        ['+34','España'], ['+351','Portugal'], ['+33','Francia'], ['+39','Italia'], ['+49','Alemania'], ['+44','Reino Unido'],
        ['+1','Estados Unidos/Canadá'], ['+52','México'], ['+54','Argentina'], ['+56','Chile'], ['+57','Colombia'], ['+51','Perú'],
        ['+58','Venezuela'], ['+593','Ecuador'], ['+598','Uruguay'], ['+595','Paraguay'], ['+55','Brasil'], ['+212','Marruecos']
      ];
      return countries.map(([code, name]) => `<option value="${code}" ${code === selected ? 'selected' : ''}>${name} (${code})</option>`).join('');
    }

    function buildInternationalPhone(countryId, phoneId) {
      const countryCode = cleanText(document.getElementById(countryId)?.value || '+34').replace(/[^0-9+]/g, '');
      let phone = cleanText(document.getElementById(phoneId)?.value || '').replace(/[^0-9+]/g, '');
      if (phone.startsWith('+')) return phone;
      phone = phone.replace(/^0+/, '');
      return `${countryCode}${phone}`;
    }

    function togglePasswordVisibility(inputId, button) {
      const input = document.getElementById(inputId);
      if (!input) return;
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      if (button) button.textContent = show ? 'Ocultar' : 'Mostrar';
    }

    async function registerProfessionalAccount(fields, ui = {}) {
      const { name, email, phone, password, privacyAccepted, commercialConsent } = fields;
      const fail = message => { if (ui.errorBox) { ui.errorBox.textContent = message; ui.errorBox.classList.remove('hidden'); } };
      if (name.length < 3 || !/^\S+@\S+\.\S+$/.test(email)) return fail('Revisa el nombre y el correo electrónico.');
      if (!/^\+[1-9][0-9]{7,14}$/.test(phone)) return fail('Indica el número de contacto en formato internacional.');
      if (password.length < 8) return fail('La contraseña debe tener al menos 8 caracteres.');
      if (!privacyAccepted) return fail('Debes aceptar la Política de privacidad.');
      ui.errorBox?.classList.add('hidden');
      if (ui.submit) { ui.submit.disabled = true; ui.submit.textContent = 'Creando cuenta...'; }
      let backendReached = false;
      try {
        if (!CAPTACION_MAILCHIMP?.registerEndpoint) throw new Error('backend_unavailable');
        const response = await fetch(CAPTACION_MAILCHIMP.registerEndpoint, { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body:JSON.stringify({name,email,phone,password,privacyAccepted,commercialConsent}) });
        backendReached = true;
        const data = await response.json();
        if (!response.ok || !data?.ok) throw new Error(data?.message || 'No se pudo crear la cuenta.');
        closeProfessionalSubscriptionModal();
        document.getElementById('professional-access-modal')?.classList.add('hidden');
        getRegistrationPrompt().classList.add('hidden');
        ui.form?.reset();
        showToast(data.message || 'Te hemos enviado un correo electrónico para confirmar tu registro. Revisa tu bandeja de entrada y valida tu cuenta para acceder.', 'success');
        return true;
      } catch (error) {
        if (backendReached) fail(error.message || 'No se pudo crear la cuenta.');
        else fail('No se pudo conectar con WordPress. El registro no se ha creado.');
        return false;
      } finally {
        if (ui.submit) { ui.submit.disabled = false; ui.submit.textContent = 'Crear cuenta profesional'; }
      }
    }

    async function handleProfessionalRegistration(event) {
      event.preventDefault();
      return registerProfessionalAccount({
        name: cleanText(document.getElementById('professional-register-name')?.value || ''),
        email: cleanText(document.getElementById('professional-register-email')?.value || '').toLowerCase(),
        phone: buildInternationalPhone('professional-register-country', 'professional-register-phone'),
        password: document.getElementById('professional-register-password')?.value || '',
        privacyAccepted: Boolean(document.getElementById('professional-register-privacy')?.checked),
        commercialConsent: Boolean(document.getElementById('professional-register-marketing')?.checked)
      }, { form:event.target, errorBox:document.getElementById('professional-register-error'), submit:document.getElementById('professional-register-submit') });
    }

    async function handleInlineProfessionalRegistration(event) {
      event.preventDefault();
      return registerProfessionalAccount({
        name: cleanText(document.getElementById('inline-register-name')?.value || ''),
        email: cleanText(document.getElementById('inline-register-email')?.value || '').toLowerCase(),
        phone: buildInternationalPhone('inline-register-country', 'inline-register-phone'),
        password: document.getElementById('inline-register-password')?.value || '',
        privacyAccepted: Boolean(document.getElementById('inline-register-privacy')?.checked),
        commercialConsent: Boolean(document.getElementById('inline-register-marketing')?.checked)
      }, { form:event.target, errorBox:document.getElementById('inline-register-error'), submit:document.getElementById('inline-register-submit') });
    }

    function showRegistrationPrompt(force = false) {
      if (hasActiveProfessionalSession()) return;
      if (captacionIsComplianzVisible()) {
        if (!force) scheduleRegistrationPrompt(15000);
        return;
      }
      if (force) {
        if (registrationExitIntentShown || sessionStorage.getItem('captacion_exit_prompt_seen') === '1') return;
        registrationExitIntentShown = true;
        sessionStorage.setItem('captacion_exit_prompt_seen','1');
      } else if (registrationPromptDismissedForSession || sessionStorage.getItem('captacion_subscription_prompt_dismissed') === '1') {
        return;
      }
      const modal = getRegistrationPrompt();
      modal.dataset.exitIntent = force ? '1' : '0';
      modal.classList.remove('hidden');
    }

    function dismissRegistrationPrompt() {
      registrationPromptDismissedAt = Date.now();
      registrationPromptDismissedForSession = true;
      sessionStorage.setItem('captacion_subscription_prompt_dismissed','1');
      getRegistrationPrompt().classList.add('hidden');
    }

    function goToRegisterFromPrompt() {
      getRegistrationPrompt().classList.add('hidden');
      openProfessionalSubscriptionModal('subscription-prompt');
    }

    async function handlePromptLogin(event) {
      event.preventDefault();
      const email = cleanText(document.getElementById('prompt-login-email')?.value || '').toLowerCase();
      const password = document.getElementById('prompt-login-password')?.value || '';
      const user = getDemoUsers()[email];
      if (!user || user.passwordHash !== await hashText(password)) {
        showToast('Credenciales no válidas.', 'info');
        return;
      }
      localStorage.setItem('captacion_demo_session_v4', JSON.stringify({ name: user.name, agency: user.agency, email, whatsapp: user.whatsapp || '', startedAt: Date.now() }));
      getRegistrationPrompt().classList.add('hidden');
      updateAuthModule();
      showToast('Sesión iniciada correctamente.', 'success');
    }

    function scheduleRegistrationPrompt(delay = 60000) {
      if (registrationPromptTimer) clearTimeout(registrationPromptTimer);
      registrationPromptTimer = setTimeout(() => showRegistrationPrompt(false), delay);
    }

    function startRegistrationPromptCycle() {
      if (registrationPromptStarted) return;
      registrationPromptStarted = true;
      if (hasActiveProfessionalSession()) return;
      scheduleRegistrationPrompt(60000);
      document.addEventListener('mouseleave', event => {
        if (event.clientY <= 8 && !document.hidden) showRegistrationPrompt(true);
      });
      const scheduleMobileExitIntent = () => {
        if (window.innerWidth > 768 || registrationExitIntentShown || hasActiveProfessionalSession()) return;
        const progress = (window.scrollY + window.innerHeight) / Math.max(document.documentElement.scrollHeight, 1);
        if (progress < 0.65) return;
        if (registrationMobileIntentTimer) clearTimeout(registrationMobileIntentTimer);
        registrationMobileIntentTimer = setTimeout(() => showRegistrationPrompt(true), 20000);
      };
      window.addEventListener('scroll', scheduleMobileExitIntent, {passive:true});
      window.addEventListener('touchstart', () => {
        if (registrationMobileIntentTimer) clearTimeout(registrationMobileIntentTimer);
      }, {passive:true});
    }

    function requireRegisteredAction(actionLabel = 'realizar esta accion') {
      if (hasActiveProfessionalSession()) return true;
      showRegistrationPrompt(true);
      showToast(`Inicia sesión o crea una cuenta para ${actionLabel}.`, 'info');
      return false;
    }



    function syncMailchimpContact(payload) {
      if (!CAPTACION_MAILCHIMP?.endpoint || !payload?.email || payload?.commercialConsent !== true) return Promise.resolve(false);
      return fetch(CAPTACION_MAILCHIMP.endpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
      })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(data => Boolean(data?.ok))
        .catch(() => false);
    }


    function sendNotificationEmail(type, payload = {}) {
      const session = getDemoSession?.();
      const email = payload.email || session?.email || '';
      if (!CAPTACION_MAILCHIMP?.notificationsEndpoint || !email) return Promise.resolve(false);
      return fetch(CAPTACION_MAILCHIMP.notificationsEndpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          type,
          email,
          name: payload.name || session?.name || '',
          agency: payload.agency || session?.agency || '',
          reference: payload.reference || '',
          message: payload.message || ''
        })
      })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(data => Boolean(data?.ok))
        .catch(() => false);
    }

    function persistWpRecord(recordType, payload = {}, options = {}) {
      if (!CAPTACION_MAILCHIMP?.recordsEndpoint) return Promise.resolve(false);
      const session = getDemoSession?.();
      const recordKey = options.recordKey || payload?.id || payload?.reference || `${recordType}-${Date.now()}-${Math.random().toString(36).slice(2, 7)}`;
      return fetch(CAPTACION_MAILCHIMP.recordsEndpoint, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          ...(CAPTACION_MAILCHIMP.nonce ? { 'X-WP-Nonce': CAPTACION_MAILCHIMP.nonce } : {})
        },
        body: JSON.stringify({
          record_type: recordType,
          record_key: String(recordKey),
          user_email: options.userEmail || payload?.userEmail || session?.email || '',
          title: options.title || payload?.title || payload?.reference || payload?.id || recordType,
          status: options.status || payload?.status || '',
          related_id: options.relatedId || payload?.relatedId || payload?.propertyId || payload?.needId || '',
          payload
        })
      })
        .then(response => response.ok ? response.json() : Promise.reject(response))
        .then(data => Boolean(data?.ok))
        .catch(() => false);
    }

    function canUseWordPressRecords() {
      return Boolean(CAPTACION_MAILCHIMP?.loggedIn && CAPTACION_MAILCHIMP?.recordsEndpoint && CAPTACION_MAILCHIMP?.nonce);
    }

    async function fetchWpRecords(recordType, limit = 200) {
      if (!canUseWordPressRecords()) return [];
      const url = new URL(CAPTACION_MAILCHIMP.recordsEndpoint, window.location.origin);
      url.searchParams.set('record_type', recordType);
      url.searchParams.set('limit', String(limit));
      const response = await fetch(url.toString(), {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'X-WP-Nonce': CAPTACION_MAILCHIMP.nonce
        }
      });
      if (!response.ok) throw new Error(`No se pudieron cargar registros ${recordType}.`);
      const data = await response.json();
      return Array.isArray(data?.records) ? data.records : [];
    }

    function payloadFromWpRecord(record = {}) {
      const payload = record.payload && typeof record.payload === 'object' ? record.payload : {};
      return {
        ...payload,
        id: payload.id || record.record_key || `record-${record.id}`,
        userEmail: payload.userEmail || record.user_email || '',
        wpRecordId: record.id || '',
        importBatchId: record.import_batch_id || '',
        dataOrigin: record.data_origin || '',
        wpStatus: record.status || '',
        wpUpdatedAt: record.updated_at || ''
      };
    }

    function mergeRecordsById(currentRows, serverRows, normalizeFn) {
      const merged = [];
      const seen = new Set();
      serverRows.map(payloadFromWpRecord).map(normalizeFn).forEach(row => {
        if (!row?.id || seen.has(row.id)) return;
        seen.add(row.id);
        merged.push(row);
      });
      currentRows.filter(row => !row.wpRecordId && !row.importBatchId).forEach(row => {
        if (!row?.id || seen.has(row.id)) return;
        seen.add(row.id);
        merged.push(row);
      });
      return merged;
    }

    async function loadWordPressRealEstateRecords() {
      if (!canUseWordPressRecords()) return false;
      try {
        const [propertyRecords, needRecords] = await Promise.all([
          fetchWpRecords('property'),
          fetchWpRecords('need')
        ]);
        properties = mergeRecordsById(properties, propertyRecords, normalizePropertyRecord);
        needs = mergeRecordsById(needs, needRecords, normalizeNeedRecord);
        persistDemoState();
        renderMarketplace();
        renderDashboard();
        filterNeeds();
        renderHome();
        return true;
      } catch (error) {
        console.warn('[Captacion.app] Persistencia WordPress no disponible; se mantiene fallback local.', error);
        showToast('No se pudieron cargar tus registros guardados en WordPress. Se mantiene la vista local de preproducción.', 'info');
        return false;
      }
    }

    function syncMailchimpSession(tag, source, extra = {}) {
      if (CAPTACION_MAILCHIMP?.commercialConsent !== true) return Promise.resolve(false);
      const session = getDemoSession?.();
      return syncMailchimpContact({
        email: extra.email || session?.email || '',
        name: extra.name || session?.name || '',
        agency: extra.agency || session?.agency || '',
        phone: extra.phone || session?.whatsapp || '',
        source,
        tags: [tag],
        commercialConsent: true
      });
    }

    async function handleLogin(event) {
      event.preventDefault();
      const email = cleanText(document.getElementById('auth-login-email').value).toLowerCase();
      const password = document.getElementById('auth-login-password').value;
      const user = getDemoUsers()[email];
      if (!user || user.passwordHash !== await hashText(password)) {
        showToast('Credenciales no válidas en esta demo local.', 'info');
        return;
      }
      localStorage.setItem('captacion_demo_session_v4', JSON.stringify({ name: user.name, agency: user.agency, email, whatsapp: user.whatsapp || '', startedAt: Date.now() }));
      event.target.reset();
      updateAuthModule();
      showToast('Sesión iniciada correctamente.', 'success');
    }

    async function logoutDemo() {
      if (CAPTACION_MAILCHIMP?.loggedIn && CAPTACION_MAILCHIMP?.logoutEndpoint) {
        try { await fetch(CAPTACION_MAILCHIMP.logoutEndpoint,{method:'POST',credentials:'same-origin',headers:{'X-WP-Nonce':CAPTACION_MAILCHIMP.nonce}}); } catch(error) {}
      }
      CAPTACION_MAILCHIMP.loggedIn = false;
      localStorage.removeItem('captacion_demo_session_v4');
      sessionStorage.removeItem('captacion_professional_registered');
      updateAuthModule();
      showToast('Sesión cerrada correctamente.', 'info');
    }

    function updateAuthModule() {
      const guest = document.getElementById('auth-guest-panel');
      const sessionPanel = document.getElementById('auth-session-panel');
      if (!guest || !sessionPanel) return;
      const session = getDemoSession();
      guest.classList.toggle('hidden', Boolean(session));
      sessionPanel.classList.toggle('hidden', !session);
      if (session) {
        const name = document.getElementById('auth-session-name');
        const agency = document.getElementById('auth-session-agency');
        if (name) name.textContent = `Hola, ${session.name}`;
        if (agency) agency.textContent = `${session.agency} · ${session.email}${session.whatsapp ? ` · WhatsApp ${session.whatsapp}` : ''}`;
      }
    }

    function ensureWordPressSession() {
      if (!CAPTACION_MAILCHIMP?.loggedIn || !CAPTACION_MAILCHIMP?.emailVerified || !CAPTACION_MAILCHIMP?.currentUser || getDemoSession()) return;
      const user = CAPTACION_MAILCHIMP.currentUser;
      localStorage.setItem('captacion_demo_session_v4', JSON.stringify({ name:user.name || 'Profesional', email:user.email || '', whatsapp:user.phone || '', agency:'Perfil profesional', planType:CAPTACION_MAILCHIMP.accessState?.plan_type || 'basic', emailVerified:true, startedAt:Date.now(), source:'wordpress' }));
      sessionStorage.setItem('captacion_professional_registered','1');
    }

    function getProfessionalAccessModal() {
      let modal = document.getElementById('professional-access-modal');
      if (modal) return modal;
      modal = document.createElement('div');
      modal.id = 'professional-access-modal';
      modal.className = 'fixed inset-0 z-[135] hidden flex items-center justify-center p-4 bg-navy-dark/70 backdrop-blur-sm';
      modal.innerHTML = `
        <div class="relative w-full max-w-lg max-h-[92vh] overflow-y-auto rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8">
          <button type="button" onclick="closeProfessionalAccessModal()" class="absolute top-3 right-4 text-slate-400 text-xl font-black" aria-label="Cerrar">x</button>
          <span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Acceso profesional</span>
          <div class="mt-5 grid grid-cols-2 gap-1 rounded-xl bg-slate-100 p-1"><button id="professional-access-login-tab" type="button" onclick="toggleProfessionalAccessMode('login')" class="px-3 py-2 rounded-lg bg-white text-navy text-xs font-black shadow-sm">Iniciar sesión</button><button id="professional-access-register-tab" type="button" onclick="toggleProfessionalAccessMode('register')" class="px-3 py-2 rounded-lg text-slate-500 text-xs font-black">Crear cuenta</button></div>
          <form id="professional-access-login-form" onsubmit="handleProfessionalLogin(event)" class="mt-5 space-y-4">
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Correo electrónico *</span><input id="professional-login-email" type="email" required autocomplete="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label>
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Contraseña *</span><div class="relative"><input id="professional-login-password" type="password" required autocomplete="current-password" class="w-full px-4 py-3 pr-24 rounded-xl border border-slate-200 text-sm" /><button type="button" onclick="togglePasswordVisibility('professional-login-password', this)" class="absolute inset-y-1 right-1 px-3 rounded-lg text-[10px] font-black text-blue hover:bg-blue-light">Mostrar</button></div></label>
            <p id="professional-login-error" class="hidden rounded-xl bg-red-50 border border-red-100 px-3 py-2 text-xs text-red-700" role="alert"></p>
            <button id="professional-login-submit" class="w-full py-3.5 rounded-xl bg-navy text-white text-xs font-black">Acceder</button><a href="${CAPTACION_MAILCHIMP.lostPasswordUrl}" class="block text-center text-xs font-bold text-blue">¿Has olvidado tu contraseña?</a><button type="button" onclick="toggleProfessionalAccessMode('register')" class="w-full text-xs font-bold text-blue">¿No tienes cuenta? Crear cuenta profesional</button>
          </form>
          <form id="professional-access-register-form" onsubmit="handleAccessProfessionalRegistration(event)" class="hidden mt-5 space-y-4">
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Nombre y apellidos *</span><input id="access-register-name" type="text" required minlength="3" autocomplete="name" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label>
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Correo electrónico *</span><input id="access-register-email" type="email" required autocomplete="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label>
            <div class="grid grid-cols-1 sm:grid-cols-[0.95fr_1.05fr] gap-3"><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">País *</span><select id="access-register-country" autocomplete="tel-country-code" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm bg-white">${countryCodeOptionsHtml()}</select></label><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Número de contacto *</span><input id="access-register-phone" type="tel" required autocomplete="tel-national" placeholder="600 000 000" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label></div>
            <label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Contraseña *</span><div class="relative"><input id="access-register-password" type="password" required minlength="8" autocomplete="new-password" class="w-full px-4 py-3 pr-24 rounded-xl border border-slate-200 text-sm" /><button type="button" onclick="togglePasswordVisibility('access-register-password', this)" class="absolute inset-y-1 right-1 px-3 rounded-lg text-[10px] font-black text-blue hover:bg-blue-light">Mostrar</button></div></label>
            <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-white p-3 text-xs text-slate-600 cursor-pointer"><input id="access-register-privacy" type="checkbox" required class="mt-0.5 h-5 w-5 shrink-0" /><span>He leído y acepto la <a href="#/privacidad" class="legal-link">Política de privacidad</a>.</span></label>
            <p id="access-register-error" class="hidden rounded-xl bg-red-50 border border-red-100 px-3 py-2 text-xs text-red-700" role="alert"></p>
            <button id="access-register-submit" class="w-full py-3.5 rounded-xl bg-blue text-white text-xs font-black">Crear cuenta profesional</button><button type="button" onclick="toggleProfessionalAccessMode('login')" class="w-full text-xs font-bold text-blue">¿Ya tienes cuenta? Iniciar sesión</button>
          </form>
        </div>`;
      document.body.appendChild(modal);
      return modal;
    }

    function toggleProfessionalAccessMode(mode = 'login') {
      const isLogin = mode === 'login';
      document.getElementById('professional-access-login-form')?.classList.toggle('hidden', !isLogin);
      document.getElementById('professional-access-register-form')?.classList.toggle('hidden', isLogin);
      document.getElementById('professional-access-login-tab')?.classList.toggle('bg-white', isLogin);
      document.getElementById('professional-access-register-tab')?.classList.toggle('bg-white', !isLogin);
    }

    function closeProfessionalAccessModal() { getProfessionalAccessModal().classList.add('hidden'); }

    async function handleProfessionalLogin(event) {
      event.preventDefault();
      const email = cleanText(document.getElementById('professional-login-email')?.value || '').toLowerCase();
      const password = document.getElementById('professional-login-password')?.value || '';
      const errorBox = document.getElementById('professional-login-error');
      const submit = document.getElementById('professional-login-submit');
      const fail = message => { errorBox.textContent = message; errorBox.classList.remove('hidden'); };
      if (!/^\S+@\S+\.\S+$/.test(email) || !password) return fail('Completa correo y contraseña.');
      errorBox.classList.add('hidden'); submit.disabled = true; submit.textContent = 'Accediendo...';
      try {
        if (!CAPTACION_MAILCHIMP?.loginEndpoint) throw new Error('backend_unavailable');
        const response = await fetch(CAPTACION_MAILCHIMP.loginEndpoint, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({email,password})});
        const data = await response.json();
        if (!response.ok || !data?.ok) throw new Error(data?.message || 'No se pudo iniciar sesión.');
        CAPTACION_MAILCHIMP.loggedIn = true; CAPTACION_MAILCHIMP.emailVerified = true; CAPTACION_MAILCHIMP.accessState = data.accessState; CAPTACION_MAILCHIMP.nonce = data.nonce || CAPTACION_MAILCHIMP.nonce;
        localStorage.setItem('captacion_demo_session_v4', JSON.stringify({name:data.displayName,email:data.email,whatsapp:data.phone || '',agency:'Perfil profesional',profileComplete:data.profileComplete,planType:data.accessState?.plan_type || 'basic',emailVerified:true,startedAt:Date.now(),source:'wordpress'}));
        sessionStorage.setItem('captacion_professional_registered','1'); event.target.reset(); closeProfessionalAccessModal(); updateAuthModule(); applyDashboardPlanAccess(); loadWordPressRealEstateRecords(); window.location.hash='#/area-privada'; showToast('Sesión iniciada correctamente.', 'success');
      } catch (error) {
        fail(error.message === 'backend_unavailable' ? 'El acceso no esta disponible temporalmente. Intentalo de nuevo.' : (error.message || 'No se pudo iniciar sesión.'));
        if (/confirmar tu correo|correo electronico antes/i.test(error.message || '')) {
          errorBox.innerHTML = `${escapeHTML(error.message)} <button type="button" onclick="resendVerificationEmail('${escapeHTML(email)}')" class="block mt-2 font-black text-blue">Reenviar correo de verificación</button>`;
        }
      } finally { submit.disabled=false; submit.textContent='Acceder'; }
    }

    async function handleAccessProfessionalRegistration(event) {
      event.preventDefault();
      return registerProfessionalAccount({name:cleanText(document.getElementById('access-register-name')?.value||''),email:cleanText(document.getElementById('access-register-email')?.value||'').toLowerCase(),phone:buildInternationalPhone('access-register-country','access-register-phone'),password:document.getElementById('access-register-password')?.value||'',privacyAccepted:Boolean(document.getElementById('access-register-privacy')?.checked)}, {form:event.target,errorBox:document.getElementById('access-register-error'),submit:document.getElementById('access-register-submit')});
    }

    async function resendVerificationEmail(email) {
      try {
        const response = await fetch(CAPTACION_MAILCHIMP.resendVerificationEndpoint, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json'},body:JSON.stringify({email})});
        const data = await response.json();
        if (!response.ok || !data?.ok) throw new Error(data?.message || 'No se pudo reenviar el correo.');
        showToast(data.message, 'success');
      } catch (error) { showToast(error.message, 'info'); }
    }

    // ==========================================
    // 7.2 FUNCIONES QUE FALTABAN EN EL PROTOTIPO ORIGINAL
    // ==========================================
    function openProfessionalAccess() {
      if (hasActiveProfessionalSession()) {
        window.location.hash = '#/area-privada';
        return;
      }
      const modal = getProfessionalAccessModal();
      toggleProfessionalAccessMode('login');
      modal.classList.remove('hidden');
      setTimeout(() => document.getElementById('professional-login-email')?.focus(), 50);
    }

    function scrollToPlatformForm(formId) {
      const form = document.getElementById(formId);
      if (!form) return;
      form.scrollIntoView({ behavior:'smooth', block:'start' });
      setTimeout(() => form.querySelector('input,select,textarea')?.focus({ preventScroll:true }), 450);
    }

    function handleFreePlanAccess() {
      if (getDemoSession?.()) {
        window.location.hash = '#/area-privada';
        return;
      }
      showRegistrationPrompt(true);
    }

    function getActiveMarketplaceProperties() {
      const closedIds = new Set((closedOperations || []).map(item => item.propertyId).filter(Boolean));
      return properties.filter(property => {
        const status = normalizeMatchText(property.status || 'activa');
        return !closedIds.has(property.id) && !['cerrada','cerrado','caducada','caducado','bloqueada','bloqueado','vendida','vendido'].some(value => status.includes(value));
      });
    }

    function openNeedCollaborationModal(needId) {
      if (!requireRegisteredAction('colaborar con esta demanda')) return;
      const need = needs.find(item => item.id === needId);
      const modal = document.getElementById('need-collaboration-modal');
      const select = document.getElementById('need-collaboration-property');
      if (!need || !modal || !select) return;
      document.getElementById('need-collaboration-need-id').value = need.id;
      document.getElementById('need-collaboration-summary').innerHTML = `<strong class="block text-navy mb-2">${escapeHTML(need.title)}</strong><div class="grid grid-cols-1 sm:grid-cols-2 gap-2"><span><strong>Tipo:</strong> ${escapeHTML(need.type || 'No disponible')}</span><span><strong>Zona:</strong> ${escapeHTML([need.province, need.municipality].filter(Boolean).join(' · ') || 'No disponible')}</span><span><strong>Presupuesto:</strong> ${formatCurrency(need.budget)}</span><span><strong>Urgencia:</strong> ${escapeHTML(need.urgency || 'Media')}</span><span class="sm:col-span-2"><strong>Criterios:</strong> ${formatPropertyFeatures(need, true)}</span></div>`;
      const active = getActiveMarketplaceProperties();
      const compatible = getCompatiblePropertiesForNeed(need, active.length).map(item => item.property.id);
      const ordered = [...active].sort((a,b) => Number(compatible.includes(b.id)) - Number(compatible.includes(a.id)) || Number(b.date||0)-Number(a.date||0));
      select.innerHTML = ordered.map(property => `<option value="${escapeHTML(property.id)}">${compatible.includes(property.id) ? 'Compatible · ' : ''}${escapeHTML(property.title)} · ${escapeHTML(property.province || property.location || 'España')} · ${formatCurrency(property.price)}</option>`).join('') || '<option value="">No hay captaciones activas disponibles</option>';
      select.disabled = !ordered.length;
      modal.classList.remove('hidden');
    }

    function closeNeedCollaborationModal() {
      document.getElementById('need-collaboration-modal')?.classList.add('hidden');
    }

    function submitNeedCollaboration(event) {
      event.preventDefault();
      const need = needs.find(item => item.id === document.getElementById('need-collaboration-need-id')?.value);
      const property = properties.find(item => item.id === document.getElementById('need-collaboration-property')?.value);
      const message = cleanText(document.getElementById('need-collaboration-message')?.value || '');
      if (!need || !property) { showToast('Selecciona una captación activa para enviar la propuesta.', 'info'); return; }
      const proposal = { id:`COL-${Date.now()}`, needId:need.id, propertyId:property.id, title:'Nueva propuesta de colaboración', message, status:'pendiente', createdAt:Date.now() };
      persistWpRecord('access_request', proposal, { recordKey:proposal.id, userEmail:need.userEmail || '', title:proposal.title, status:'pendiente', relatedId:need.id });
      persistWpRecord('notification', { ...proposal, detail:'Un profesional tiene una captación disponible en Marketplace que podría encajar con tu demanda.' }, { recordKey:`notification-${proposal.id}`, userEmail:need.userEmail || '', title:'Nueva propuesta de colaboración', status:'unread', relatedId:property.id });
      addPrivateNotification({ category:'Colaboración', title:'Nueva propuesta de colaboración', detail:'Un profesional tiene una captación disponible en Marketplace que podría encajar con tu demanda. Revisa la coincidencia y continúa con el flujo de Comprar captación si te interesa.', target:'demands', propertyId:property.id, needId:need.id, dueAt:Date.now(), dedupeKey:`collaboration-${need.id}-${property.id}` });
      addPrivateActivity('✓','Propuesta de colaboración enviada',`${property.title} se ha propuesto para la demanda ${need.title}.`);
      closeNeedCollaborationModal();
      showToast('Propuesta de colaboración enviada correctamente.', 'success');
      setTimeout(() => openMapPropertyCard(property.id), 180);
    }

    function handleNewNeed(event) {
      event.preventDefault();
      if (!requireRegisteredAction('publicar una demanda')) return;
      const title = cleanText(document.getElementById('need-pub-title').value);
      const description = cleanText(document.getElementById('need-pub-desc').value);
      const type = normalizePropertyType(document.getElementById('need-pub-type').value);
      const acceptedConditions = selectedValues(document.getElementById('need-pub-condition'));
      const acceptedMandates = selectedValues(document.getElementById('need-pub-mandate'));
      if (title.length < 8) { showToast('El título de la búsqueda debe tener al menos 8 caracteres.', 'info'); return; }
      if (description.length < 30) { showToast('La descripción de la necesidad debe tener al menos 30 caracteres.', 'info'); return; }
      if (!acceptedConditions.length || !acceptedMandates.length) { showToast('Selecciona al menos una condición y un tipo de captación aceptada.', 'info'); return; }
      const territory = resolveTerritorySelection(
        document.getElementById('need-pub-ccaa-sel').value,
        document.getElementById('need-pub-province-sel').value,
        document.getElementById('need-pub-municipality-sel').value
      );
      if (!territory.valid) {
        showToast(territory.message, 'info');
        return;
      }
      const need = normalizeNeedRecord({
        id: `user-need-${Date.now()}`,
        title,
        type,
        property_type: type,
        operation: cleanText(document.getElementById('need-pub-operation').value),
        ccaa: territory.autonomous_community_name,
        province: territory.province_name,
        municipality: territory.municipality_name,
        autonomous_community_id: territory.autonomous_community_id,
        community_code: territory.autonomous_community_id,
        autonomous_community_name: territory.autonomous_community_name,
        province_id: territory.province_id,
        province_code: territory.province_id,
        province_name: territory.province_name,
        municipality_id: territory.municipality_id,
        municipality_ine_code: territory.municipality_ine_code,
        municipality_code: territory.municipality_ine_code || territory.municipality_id,
        municipality_name: territory.municipality_name,
        locality: cleanText(document.getElementById('need-pub-locality').value),
        postalCode: cleanText(document.getElementById('need-pub-postal-code').value),
        bedrooms: Number(document.getElementById('need-pub-bedrooms').value) || 0,
        min_rooms: Number(document.getElementById('need-pub-bedrooms').value) || 0,
        bathrooms: Number(document.getElementById('need-pub-bathrooms').value) || 0,
        min_bathrooms: Number(document.getElementById('need-pub-bathrooms').value) || 0,
        surface: Number(document.getElementById('need-pub-surface').value),
        desired_area_min_m2: Number(document.getElementById('need-pub-surface').value),
        budget: Number(document.getElementById('need-pub-budget').value),
        max_budget: Number(document.getElementById('need-pub-budget').value),
        buyerType: cleanText(document.getElementById('need-pub-buyer-type').value),
        urgency: cleanText(document.getElementById('need-pub-urgency').value),
        search_urgency: cleanText(document.getElementById('need-pub-urgency').value),
        funding: cleanText(document.getElementById('need-pub-funding').value),
        feeSplit: cleanText(document.getElementById('need-pub-fee').value),
        accepted_commission: cleanText(document.getElementById('need-pub-fee').value),
        accepted_property_conditions: acceptedConditions,
        accepted_mandate_types: acceptedMandates,
        required_documentation_level: cleanText(document.getElementById('need-pub-docs').value),
        description,
        agency: getDemoSession()?.agency || 'Agencia verificada',
        userEmail: getDemoSession()?.email || '',
        date: Date.now()
      });
      needs.unshift(need);
      persistDemoState();
      persistWpRecord('need', need, { recordKey: need.id, title: need.title, status: 'activa' })
        .then(ok => { if (!ok && canUseWordPressRecords()) showToast('La demanda queda visible localmente, pero no se pudo sincronizar con WordPress.', 'info'); });
      syncMailchimpSession('busco-captacion', 'busco-captacion');
      syncAlertsForNeed(need);
      event.target.reset();
      updateGeoDropdowns('form-need');
      updatePropertyFormDynamics('need');
      filterNeeds();
      renderHome();
      showToast('Demanda publicada correctamente en la red profesional.', 'success');
      setTimeout(() => openPostPublishCompatibilityReport('need', need), 250);
    }

    function filterNeeds() {
      needsVisibleLimit = LIST_BATCH_SIZE;
      const time = document.getElementById('need-filter-time')?.value || 'newest';
      const category = document.getElementById('need-filter-type')?.value || 'all';
      const ccaa = document.getElementById('need-filter-ccaa')?.value || 'all';
      const province = document.getElementById('need-filter-province')?.value || 'all';
      const municipality = document.getElementById('need-filter-municipality')?.value || 'all';
      const locality = cleanText(document.getElementById('need-filter-locality')?.value || '').toLowerCase();
      const postalCode = cleanText(document.getElementById('need-filter-postal-code')?.value || '');
      const price = document.getElementById('need-filter-price')?.value || 'all';
      let list = needs.filter(need => {
        const localityText = `${need.locality || ''} ${need.municipality || ''}`.toLowerCase();
        const priceOk = price === 'all' || (price === 'low' && need.budget < 150000) || (price === 'mid' && need.budget >= 150000 && need.budget <= 500000) || (price === 'high' && need.budget > 500000);
        return (ccaa === 'all' || need.ccaa === ccaa)
          && (category === 'all' || normalizeOpportunityCategory(need.type) === category)
          && (province === 'all' || need.province === province)
          && (municipality === 'all' || need.municipality === municipality)
          && (!locality || localityText.includes(locality))
          && (!postalCode || String(need.postalCode || '').includes(postalCode))
          && (!needsMapSelectedBounds || !window.L || needsMapSelectedBounds.contains(L.latLng(getApproximatePoint(need, properties.length + needs.indexOf(need)))))
          && priceOk;
      });
      list.sort((a, b) => time === 'oldest' ? a.date - b.date : b.date - a.date);
      lastFilteredNeeds = list;
      renderNeedsDashboard();
      const needsAccordion = document.getElementById('needs-accordion-sections');
      if (needsAccordion) needsAccordion.innerHTML = '';
      renderNeedsUI(list);
      if (needsMap) renderNeedsMapMarkers(list);
    }

    function setNeedsLayout(layout) {
      currentNeedsLayout = ['mapa','lista'].includes(layout) ? layout : 'bloque';
      const mapBtn = document.getElementById('layout-mapa-btn');
      const blockBtn = document.getElementById('layout-bloque-btn');
      const listBtn = document.getElementById('layout-lista-btn');
      [[mapBtn,'mapa'],[blockBtn,'bloque'],[listBtn,'lista']].forEach(([button,mode]) => {
        const active = currentNeedsLayout === mode;
        button?.classList.toggle('bg-white', active);
        button?.classList.toggle('shadow-sm', active);
        button?.classList.toggle('text-navy', active);
        button?.classList.toggle('text-slate-500', !active);
      });
      const panel = document.getElementById('needs-map-panel');
      const listContainer = document.getElementById('needs-list-container');
      panel?.classList.toggle('hidden', currentNeedsLayout !== 'mapa');
      listContainer?.classList.toggle('hidden', currentNeedsLayout === 'mapa');
      filterNeeds();
      if (currentNeedsLayout === 'mapa') setTimeout(initNeedsMap, 0);
    }

    function clearAdvancedFilters() {
      const setters = {
        'need-filter-time': 'newest', 'need-filter-type': 'all', 'need-filter-ccaa': 'all', 'need-filter-province': 'all',
        'need-filter-municipality': 'all', 'need-filter-postal-code': '', 'need-filter-locality': '', 'need-filter-price': 'all'
      };
      Object.entries(setters).forEach(([id, value]) => { const element = document.getElementById(id); if (element) element.value = value; });
      updateGeoDropdowns('filter');
      if (needsMap && needsMapSelectionLayer) needsMap.removeLayer(needsMapSelectionLayer);
      needsMapSelectionLayer = null;
      needsMapSelectedBounds = null;
      needsMapPostalCodeFilter = '';
      const needsMapPostalInput = document.getElementById('needs-map-postal-filter');
      if (needsMapPostalInput) needsMapPostalInput.value = '';
      updateNeedsMapAreaStatus();
      filterNeeds();
    }

    function findCcaaForProvince(province) {
      return Object.keys(geoDb).find(ccaa => Object.prototype.hasOwnProperty.call(geoDb[ccaa], province)) || 'all';
    }

    function findProvinceForMunicipality(municipality) {
      for (const [ccaa, provinces] of Object.entries(geoDb)) {
        for (const [province, municipalities] of Object.entries(provinces)) {
          if (municipalities.includes(municipality)) return { ccaa, province };
        }
      }
      return { ccaa: 'all', province: 'all' };
    }

    function filterByDashboard(type, value) {
      const ccaaEl = document.getElementById('need-filter-ccaa');
      const provinceEl = document.getElementById('need-filter-province');
      const municipalityEl = document.getElementById('need-filter-municipality');
      if (type === 'ccaa') {
        ccaaEl.value = value;
        updateGeoDropdowns('filter');
      } else if (type === 'province') {
        ccaaEl.value = findCcaaForProvince(value);
        updateGeoDropdowns('filter');
        provinceEl.value = value;
        updateGeoDropdowns('filter', true);
      } else if (type === 'municipality') {
        const location = findProvinceForMunicipality(value);
        ccaaEl.value = location.ccaa;
        updateGeoDropdowns('filter');
        provinceEl.value = location.province;
        updateGeoDropdowns('filter', true);
        municipalityEl.value = value;
      }
      filterNeeds();
    }

    function toggleCardDetails(id) {
      if (!requireRegisteredAction('ver más detalles')) return;
      const details = document.getElementById(`details-${id}`);
      const button = document.getElementById(`toggle-btn-${id}`);
      if (!details) return;
      details.classList.toggle('hidden');
      if (button) button.textContent = details.classList.contains('hidden') ? 'Ver más detalles ▾' : 'Ocultar detalles ▴';
    }

    function openMapPropertyCard(propertyId) {
      if (!requireRegisteredAction('ver la ficha completa')) return;
      const selectedProperty = properties.find(item => item.id === propertyId);
      if (!selectedProperty) return;
      try { window.location.hash = '#/marketplace'; } catch (error) { currentHash = '#/marketplace'; }
      currentHash = '#/marketplace';
      handleRoute();
      setTimeout(() => {
        const referenceInput = document.getElementById('market-reference-filter');
        const postalInput = document.getElementById('market-postal-code-filter');
        let visibleProperties = getMarketplaceVisibleProperties();
        if (!visibleProperties.some(item => item.id === propertyId)) {
          if (referenceInput) referenceInput.value = '';
          if (postalInput) postalInput.value = '';
          visibleProperties = getMarketplaceVisibleProperties();
        }
        marketplaceVisibleLimit = Math.max(LIST_BATCH_SIZE, visibleProperties.findIndex(item => item.id === propertyId) + 1);
        setMarketplaceView('cards');
        renderMarketplace();
        const card = document.getElementById(`market-card-${propertyId}`);
        card?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const details = document.getElementById(`details-${propertyId}`);
        if (details?.classList.contains('hidden')) toggleCardDetails(propertyId);
      }, 140);
    }

    function openMapNeedCard(needId) {
      if (!requireRegisteredAction('ver la ficha completa')) return;
      const selectedNeed = needs.find(item => item.id === needId);
      if (!selectedNeed) return;
      try { window.location.hash = '#/buscar-captaciones'; } catch (error) { currentHash = '#/buscar-captaciones'; }
      currentHash = '#/buscar-captaciones';
      handleRoute();
      setTimeout(() => {
        const candidateList = lastFilteredNeeds.some(item => item.id === needId) ? lastFilteredNeeds : needs;
        needsVisibleLimit = Math.max(LIST_BATCH_SIZE, candidateList.findIndex(item => item.id === needId) + 1);
        renderNeedsUI(candidateList);
        const card = document.getElementById(`need-card-${needId}`);
        card?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        const details = document.getElementById(`details-${needId}`);
        if (details?.classList.contains('hidden')) toggleCardDetails(needId);
      }, 140);
    }

    let marketplaceAccessState = CAPTACION_MAILCHIMP?.accessState || { plan_type:'basic', included_marketplace_accesses:0, used_marketplace_accesses:0, extra_marketplace_accesses:0, remaining_marketplace_accesses:0 };
    let marketplaceAccessHistory = [];
    let activeOpportunityUnlocked = false;

    function marketplacePlanLabel(plan = marketplaceAccessState?.plan_type) {
      return plan === 'premium' ? 'Premium' : plan === 'professional_plus' ? 'Professional' : 'Starter';
    }

    function marketplaceAccessCta(state = marketplaceAccessState, unlocked = false) {
      if (unlocked) return 'Acceso ya desbloqueado';
      if (Number(state?.remaining_marketplace_accesses) > 0) return 'Usar 1 acceso disponible';
      if (state?.plan_type === 'premium') return 'Comprar 30 accesos extra por 5 €';
      if (state?.plan_type === 'professional_plus') return 'Comprar 15 accesos extra por 5 €';
      return 'Comprar acceso por 10 €';
    }

    async function fetchMarketplaceAccessState(opportunityId = '') {
      if (!CAPTACION_MAILCHIMP?.accessStatusEndpoint || !CAPTACION_MAILCHIMP?.loggedIn) return { accessState:marketplaceAccessState, opportunityUnlocked:false };
      const url = new URL(CAPTACION_MAILCHIMP.accessStatusEndpoint);
      if (opportunityId) url.searchParams.set('opportunity_id', opportunityId);
      const response = await fetch(url.toString(), {credentials:'same-origin',headers:{'X-WP-Nonce':CAPTACION_MAILCHIMP.nonce}});
      const data = await response.json();
      if (!response.ok || !data?.ok) throw new Error(data?.message || 'No se pudo consultar el saldo de accesos.');
      marketplaceAccessState = data.accessState || marketplaceAccessState;
      marketplaceAccessHistory = data.accessHistory || marketplaceAccessHistory;
      CAPTACION_MAILCHIMP.accessState = marketplaceAccessState;
      return data;
    }

    async function openAccessModal(propertyId) {
      if (!requireRegisteredAction('solicitar acceso a una captacion')) return;
      const property = properties.find(item => item.id === propertyId);
      if (!property) return;
      const modal = document.getElementById('access-modal');
      document.getElementById('access-property-id').value = property.id;
      document.getElementById('access-modal-title').textContent = `Acceder a captación: ${property.title}`;
      let statusError = '';
      try {
        const status = await fetchMarketplaceAccessState(property.id);
        activeOpportunityUnlocked = Boolean(status.opportunityUnlocked);
      } catch (error) { statusError = error.message; activeOpportunityUnlocked = false; }
      document.getElementById('access-modal-summary').innerHTML = `
        <strong class="text-navy">${escapeHTML(property.type || 'Activo inmobiliario')}</strong><br>
        Zona aproximada: ${escapeHTML(property.province || property.location || 'España')} - C.P. ${escapeHTML(property.postalCode || 'N/D')}<br>
        Precio orientativo: <strong class="text-navy">${formatCurrency(property.price)}</strong><br>
        Honorarios de colaboración: <strong class="text-blue">${escapeHTML(property.fee || 'A consultar')}</strong><br>
        Referencia: <strong class="text-navy">${escapeHTML(property.reference || property.id)}</strong><br>
        Plan: <strong class="text-navy">${escapeHTML(marketplacePlanLabel())}</strong><br>
        Accesos disponibles: <strong class="text-blue">${Number(marketplaceAccessState?.remaining_marketplace_accesses || 0)}</strong>${statusError ? `<br><span class="text-amber">${escapeHTML(statusError)}</span>` : ''}`;
      const planMessage = document.getElementById('access-modal-plan-message');
      if (planMessage) planMessage.textContent = activeOpportunityUnlocked ? 'Esta oportunidad ya está desbloqueada para tu usuario y no volverá a consumir crédito.' : Number(marketplaceAccessState?.remaining_marketplace_accesses) > 0 ? 'Se consumirá una unidad de acceso del marketplace al confirmar.' : 'No hay accesos disponibles. El checkout permanece en preproducción hasta configurar Payment Link y webhook.';
      const stripeButton = document.getElementById('stripe-payment-button');
      if (stripeButton) stripeButton.textContent = marketplaceAccessCta(marketplaceAccessState, activeOpportunityUnlocked);
      modal?.classList.remove('hidden');
    }

    async function handleMarketplaceAccess(event) {
      event.preventDefault();
      const opportunityId = document.getElementById('access-property-id')?.value || '';
      const property = properties.find(item => item.id === opportunityId);
      if (!property) return;
      const canConsume = activeOpportunityUnlocked || Number(marketplaceAccessState?.remaining_marketplace_accesses) > 0;
      const endpoint = canConsume ? CAPTACION_MAILCHIMP.accessConsumeEndpoint : CAPTACION_MAILCHIMP.accessPurchaseEndpoint;
      try {
        const response = await fetch(endpoint, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':CAPTACION_MAILCHIMP.nonce},body:JSON.stringify({opportunity_id:opportunityId})});
        const data = await response.json();
        if (!response.ok || !data?.ok) throw new Error(data?.message || 'No se pudo completar la acción.');
        if (canConsume) {
          marketplaceAccessState = data.accessState || marketplaceAccessState;
          activeOpportunityUnlocked = true;
          await fetchMarketplaceAccessState();
          renderDashboard();
          closeAccessModal();
          addPrivateNotification({category:'Marketplace',title:'Oportunidad desbloqueada',detail:`Ya puedes continuar el flujo protegido de ${property.title}.`,target:'operations',dedupeKey:`unlocked-${property.id}`});
          showToast(data.already_unlocked ? 'Esta oportunidad ya estaba desbloqueada; no se ha descontado otro acceso.' : 'Oportunidad desbloqueada. Se ha consumido 1 acceso.', 'success');
        } else if (data.checkoutConfigured && data.checkoutUrl) {
          window.open(data.checkoutUrl, '_blank', 'noopener,noreferrer');
          showToast('Checkout abierto. Los créditos se concederán solo tras confirmación del webhook.', 'info');
        } else showToast(data.message || 'Checkout en preproducción.', 'info');
      } catch (error) { showToast(error.message || 'No se pudo procesar el acceso.', 'info'); }
    }

    function closeAccessModal() {
      document.getElementById('access-modal')?.classList.add('hidden');
    }

    function isStripePaymentConfigured() {
      return /^https:\/\/(buy\.stripe\.com|checkout\.stripe\.com)\//.test(STRIPE_PAYMENT_LINK_URL)
        && !STRIPE_PAYMENT_LINK_URL.includes('REEMPLAZA_ESTE_ENLACE');
    }

    function isStripeMembershipConfigured(plan) {
      const url = getStripeMembershipBaseUrl(plan);
      return /^https:\/\/(buy\.stripe\.com|checkout\.stripe\.com)\//.test(url)
        && !url.includes('REEMPLAZA_');
    }

    function getStripeMembershipBaseUrl(plan) {
      if (plan === 'premium') return STRIPE_PREMIUM_URL;
      const configuredUrl = STRIPE_MEMBERSHIP_LINKS?.[plan] || '';
      if (plan === 'professional' && (!configuredUrl || configuredUrl.includes('REEMPLAZA_'))) {
        return STRIPE_PROFESSIONAL_PLUS_URL;
      }
      return configuredUrl;
    }

    function getStripeMembershipUrl(plan) {
      const url = new URL(getStripeMembershipBaseUrl(plan));
      url.searchParams.set('utm_source', 'captacion_app');
      url.searchParams.set('utm_medium', 'membership');
      url.searchParams.set('utm_campaign', plan);
      if (plan === 'professional') {
        url.searchParams.set('client_reference_id', getDemoSession?.()?.email || 'profesional_plus');
      }
      return url.toString();
    }

    function openMembershipPayment(plan, planName) {
      if (plan === 'initial' && !isStripeMembershipConfigured(plan)) {
        subscribeToast(planName);
        return false;
      }
      if (!isStripeMembershipConfigured(plan)) {
        showToast('Pega primero el Payment Link real de Stripe para este plan en el panel Captacion.app.', 'info');
        return false;
      }
      window.open(getStripeMembershipUrl(plan), '_blank', 'noopener,noreferrer');
      showToast(`Pago iniciado para ${planName}.`, 'success');
      return false;
    }

    function hasProfessionalMembershipAccess() {
      try {
        const session = getDemoSession?.();
        return Boolean(session && ['professional_plus','premium'].includes(CAPTACION_MAILCHIMP?.accessState?.plan_type || marketplaceAccessState?.plan_type));
      } catch (error) {
        return false;
      }
    }

    function requireProfessionalMembership(itemTitle = 'este recurso') {
      if (!getDemoSession?.()) {
        showToast('Crea o inicia sesión profesional antes de activar Professional.', 'info');
        location.hash = '#/inicio';
        return;
      }
      if (!isStripeMembershipConfigured('professional')) {
        showToast('El enlace de Stripe para Professional todavía no está configurado.', 'info');
        return;
      }
      openMembershipPayment('professional', 'Professional');
      showToast(`Completa el pago para desbloquear ${itemTitle} y el resto de recursos profesionales.`, 'info');
    }

    function activateProfessionalMembershipFromReturn() {
      try {
        const params = new URLSearchParams(window.location.search);
        const hashParams = new URLSearchParams((window.location.hash.split('?')[1] || ''));
        const membership = params.get('membership') || params.get('plan') || hashParams.get('membership') || hashParams.get('plan');
        if (membership !== 'professional') return;
        if (params.has('membership') || params.has('plan')) {
          window.history.replaceState({}, document.title, window.location.pathname + '#/recursos');
        }
        setTimeout(() => {
          showToast('Retorno de checkout detectado. La activación queda pendiente de confirmación segura por webhook.', 'info');
        }, 800);
      } catch (error) {}
    }

    function getStripePaymentUrl(property) {
      const url = new URL(STRIPE_PAYMENT_LINK_URL);
      const reference = property.reference || property.id;
      url.searchParams.set('client_reference_id', reference);
      url.searchParams.set('utm_source', 'captacion_app');
      url.searchParams.set('utm_medium', 'marketplace');
      url.searchParams.set('utm_campaign', 'compra_captacion');
      url.searchParams.set('captacion_ref', reference);
      url.searchParams.set('captacion_id', property.id);
      return url.toString();
    }

    function confirmStripePayment(event) {
      event.preventDefault();
      if (!requireRegisteredAction('continuar con el pago')) return;
      const propertyId = document.getElementById('access-property-id').value;
      const property = properties.find(item => item.id === propertyId);
      if (!property) return;
      if (!isStripePaymentConfigured()) {
        showToast('Pega primero tu Payment Link real de Stripe en STRIPE_PAYMENT_LINK_URL.', 'info');
        return;
      }
      window.open(getStripePaymentUrl(property), '_blank', 'noopener,noreferrer');
      persistWpRecord('access_request', { id:`access-${property.id}-${Date.now()}`, propertyId:property.id, title:property.title, reference:property.reference || property.id, status:'payment_started', createdAt:Date.now() }, { title:property.title, status:'payment_started', relatedId:property.id });
      closeAccessModal();
      addPrivateNotification({ category:'Operaciones', title:'Pago iniciado para desbloquear captación', detail:`Se ha iniciado el pago de acceso para ${property.title}.`, target:'operations', dueAt:Date.now()+3600000*2, dedupeKey:`payment-${property.id}` });
      addPrivateTask({ title:'Confirmar pago y acceso protegido', detail:`Revisa el estado del pago y del expediente de ${property.title}.`, priority:'high', due:'Hoy', dueAt:Date.now()+3600000*4, target:'operations', dedupeKey:`task-payment-${property.id}` });
      showToast(`Pago iniciado para ${property.title}.`, 'success');
    }
    function renderNeedsDashboard() {
      const dashContainer = document.getElementById('needs-dashboard');
      if (!dashContainer) return;

      const totalNeeds = needs.length;
      const totalNeedsValue = needs.reduce((sum, item) => sum + (Number(item.budget) || 0), 0);
      const ccaaCounts = {};
      const provinceCounts = {};
      const municipalityCounts = {};

      needs.forEach(n => {
        if (n.ccaa) ccaaCounts[n.ccaa] = (ccaaCounts[n.ccaa] || 0) + 1;
        if (n.province) provinceCounts[n.province] = (provinceCounts[n.province] || 0) + 1;
        if (n.municipality) municipalityCounts[n.municipality] = (municipalityCounts[n.municipality] || 0) + 1;
      });

      let ccaasHtml = '<span class="text-xs text-slate-400">Sin datos de CCAA</span>';
      if (Object.keys(ccaaCounts).length > 0) {
        ccaasHtml = Object.entries(ccaaCounts).map(([ccaa, count]) => `
          <button onclick="filterByDashboard('ccaa', '${ccaa}')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-light hover:text-blue border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition-all flex items-center">
            ${ccaa} <span class="bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded-full ml-1.5 text-[9px] font-black">${count}</span>
          </button>
        `).join('');
      }

      let provincesHtml = '<span class="text-xs text-slate-400">Sin datos</span>';
      if (Object.keys(provinceCounts).length > 0) {
        provincesHtml = Object.entries(provinceCounts).map(([prov, count]) => `
          <button onclick="filterByDashboard('province', '${prov}')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-light hover:text-blue border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition-all flex items-center">
            ${prov} <span class="bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded-full ml-1.5 text-[9px] font-black">${count}</span>
          </button>
        `).join('');
      }

      let municipalitiesHtml = '<span class="text-xs text-slate-400">Sin datos</span>';
      if (Object.keys(municipalityCounts).length > 0) {
        municipalitiesHtml = Object.entries(municipalityCounts).map(([mun, count]) => `
          <button onclick="filterByDashboard('municipality', '${mun}')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-light hover:text-blue border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition-all flex items-center">
            ${mun} <span class="bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded-full ml-1.5 text-[9px] font-black">${count}</span>
          </button>
        `).join('');
      }

      dashContainer.innerHTML = `
        <div class="bg-gradient-to-br from-navy to-navy-light text-white p-6 rounded-3xl shadow-sm flex flex-col justify-between">
          <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-300">Demanda Total Activa</span>
            <strong class="block text-4xl sm:text-5xl font-black mt-2 text-white">${totalNeeds}</strong>
            <span class="block text-xs text-slate-200 mt-2">Potencial estimado de operaciones: <strong class="text-white">${formatCurrency(totalNeedsValue)}</strong></span>
          </div>
          <p class="text-[11px] text-slate-300 mt-4 leading-relaxed font-semibold">Necesidades de compra activas en la red B2B nacional.</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-3 block">Por Comunidad Autónoma</span>
          <div class="flex flex-wrap gap-2 overflow-y-auto max-h-28 scrollbar-hidden">${ccaasHtml}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-3 block">Por Provincia</span>
          <div class="flex flex-wrap gap-2 overflow-y-auto max-h-28 scrollbar-hidden">${provincesHtml}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-3 block">Por Municipio</span>
          <div class="flex flex-wrap gap-2 overflow-y-auto max-h-28 scrollbar-hidden">${municipalitiesHtml}</div>
        </div>
      `;
    }

    function renderMarketplaceDashboard() {
      const dashContainer = document.getElementById('marketplace-dashboard');
      if (!dashContainer) return;

      const totalProperties = properties.length;
      const totalPropertiesValue = properties.reduce((sum, item) => sum + (Number(item.price) || 0), 0);
      const ccaaCounts = {};
      const provinceCounts = {};
      const municipalityCounts = {};

      properties.forEach(prop => {
        if (prop.ccaa) ccaaCounts[prop.ccaa] = (ccaaCounts[prop.ccaa] || 0) + 1;
        if (prop.province) provinceCounts[prop.province] = (provinceCounts[prop.province] || 0) + 1;
        if (prop.municipality) municipalityCounts[prop.municipality] = (municipalityCounts[prop.municipality] || 0) + 1;
      });

      const renderPills = (entries, type, emptyText = 'Sin datos') => {
        if (!entries.length) return `<span class="text-xs text-slate-400">${emptyText}</span>`;
        return entries.slice(0, 8).map(([label, count]) => `
          <button onclick="filterMarketplaceByDashboard('${type}', '${escapeHTML(String(label))}')" class="px-2.5 py-1.5 bg-slate-50 hover:bg-blue-light hover:text-blue border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 transition-all flex items-center">
            ${escapeHTML(label)} <span class="bg-slate-200 text-slate-700 px-1.5 py-0.5 rounded-full ml-1.5 text-[9px] font-black">${count}</span>
          </button>
        `).join('');
      };

      const topCcaa = Object.entries(ccaaCounts).sort((a, b) => b[1] - a[1]);
      const topProvinces = Object.entries(provinceCounts).sort((a, b) => b[1] - a[1]);
      const topMunicipalities = Object.entries(municipalityCounts).sort((a, b) => b[1] - a[1]);

      dashContainer.innerHTML = `
        <div class="bg-gradient-to-br from-navy to-navy-light text-white p-6 rounded-3xl shadow-sm flex flex-col justify-between">
          <div>
            <span class="text-[10px] font-black uppercase tracking-wider text-slate-300">Captación Total Activa</span>
            <strong class="block text-4xl sm:text-5xl font-black mt-2 text-white">${totalProperties}</strong>
            <span class="block text-xs text-slate-200 mt-2">Valor estimado de captaciones: <strong class="text-white">${formatCurrency(totalPropertiesValue)}</strong></span>
          </div>
          <p class="text-[11px] text-slate-300 mt-4 leading-relaxed font-semibold">Ofertas de captación disponibles en la red B2B nacional.</p>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-3 block">Por Comunidad Autónoma</span>
          <div class="flex flex-wrap gap-2 overflow-y-auto max-h-28 scrollbar-hidden">${renderPills(topCcaa, 'ccaa', 'Sin datos de CCAA')}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-3 block">Por Provincia</span>
          <div class="flex flex-wrap gap-2 overflow-y-auto max-h-28 scrollbar-hidden">${renderPills(topProvinces, 'province')}</div>
        </div>
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col">
          <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 mb-3 block">Por Municipio</span>
          <div class="flex flex-wrap gap-2 overflow-y-auto max-h-28 scrollbar-hidden">${renderPills(topMunicipalities, 'municipality')}</div>
        </div>
      `;
    }

    function appendLoadMoreControl(container, shown, total, clickHandler, noun) {
      if (!container || total <= shown) return;
      const control = document.createElement('div');
      control.className = 'mt-8 flex flex-col items-center justify-center gap-2';
      control.innerHTML = `
        <button type="button" onclick="${clickHandler}()" class="px-6 py-3 rounded-xl bg-navy hover:bg-navy-light text-white text-sm font-bold shadow-md transition-all">Ver más</button>
        <span class="text-[11px] font-semibold text-slate-500">Mostrando ${shown} de ${total} ${noun}. Se cargarán ${Math.min(LIST_BATCH_SIZE, total - shown)} más.</span>`;
      container.appendChild(control);
    }

    function loadMoreNeeds() {
      needsVisibleLimit += LIST_BATCH_SIZE;
      renderNeedsUI(lastFilteredNeeds.length ? lastFilteredNeeds : needs);
    }

    function loadMoreMarketplace() {
      marketplaceVisibleLimit += LIST_BATCH_SIZE;
      renderMarketplace();
    }

    function normalizeMatchText(value) {
      return String(value || '').trim().toLocaleLowerCase('es');
    }

    function numbersWithinTolerance(value, target, toleranceRatio) {
      const current = Number(value) || 0;
      const expected = Number(target) || 0;
      if (!expected) return true;
      if (!current) return false;
      const min = expected * (1 - toleranceRatio);
      const max = expected * (1 + toleranceRatio);
      return current >= min && current <= max;
    }

    function integerWithinDelta(value, target, delta = 1) {
      const current = Number(value) || 0;
      const expected = Number(target) || 0;
      if (!expected) return true;
      if (!current) return false;
      return Math.abs(current - expected) <= delta;
    }

    function getCompatibilityHardChecks(property, need) {
      const propertyType = normalizeMatchText(normalizePropertyType(property.property_type || property.type));
      const needType = normalizeMatchText(normalizePropertyType(need.property_type || need.type));
      const propertyCcaa = normalizeMatchText(property.ccaa || property.autonomous_community || property.autonomousCommunity);
      const needCcaa = normalizeMatchText(need.ccaa || need.autonomous_community || need.autonomousCommunity);
      const propertyProvince = normalizeMatchText(property.province || property.location);
      const needProvince = normalizeMatchText(need.province || need.location);
      const propertyMunicipality = normalizeMatchText(property.municipality || property.municipality_name);
      const needMunicipality = normalizeMatchText(need.municipality || need.municipality_name);
      const propertyPrice = Number(property.indicative_price ?? property.price) || 0;
      const needBudget = Number(need.max_budget ?? need.budget) || 0;
      const propertyBedrooms = Number(property.rooms ?? property.bedrooms) || 0;
      const needBedrooms = Number(need.min_rooms ?? need.bedrooms) || 0;
      const propertyBathrooms = Number(property.bathrooms) || 0;
      const needBathrooms = Number(need.min_bathrooms ?? need.bathrooms) || 0;
      const propertySurface = Number(property.total_area_m2 ?? property.surface) || 0;
      const needSurface = Number(need.desired_area_min_m2 ?? need.surface) || 0;
      const acceptedConditions = Array.isArray(need.accepted_property_conditions) ? need.accepted_property_conditions : [];
      const acceptedMandates = Array.isArray(need.accepted_mandate_types) ? need.accepted_mandate_types : [];
      const propertyCondition = cleanText(property.property_condition || '');
      const propertyMandate = cleanText(property.mandate_type || '');
      const requiredDocs = cleanText(need.required_documentation_level || '');
      const propertyDocs = cleanText(property.documentation_level || property.docs || '');

      return {
        type: !propertyType || !needType || propertyType === needType,
        ccaa: Boolean(propertyCcaa && needCcaa && propertyCcaa === needCcaa),
        province: Boolean(propertyProvince && needProvince && propertyProvince === needProvince),
        municipality: !propertyMunicipality || !needMunicipality || propertyMunicipality === needMunicipality,
        bedrooms: !needBedrooms || propertyBedrooms >= needBedrooms,
        bathrooms: !needBathrooms || propertyBathrooms >= needBathrooms,
        surface: !needSurface || propertySurface >= needSurface,
        budget: !needBudget || (propertyPrice > 0 && propertyPrice <= needBudget),
        condition: !acceptedConditions.length || !propertyCondition || acceptedConditions.includes(propertyCondition),
        mandate: !acceptedMandates.length || !propertyMandate || acceptedMandates.includes('Cualquiera') || acceptedMandates.includes(propertyMandate) || (propertyMandate === 'Sí, con exclusividad' && acceptedMandates.includes('Con exclusividad')) || (propertyMandate === 'No, nota de encargo abierta' && acceptedMandates.includes('Nota de encargo abierta')),
        documentation: !requiredDocs || requiredDocs === 'No califica' || !propertyDocs || propertyDocs === requiredDocs
      };
    }

    function calculatePropertyNeedCompatibility(property, need) {
      if (!property || !need) return 0;
      const checks = getCompatibilityHardChecks(property, need);
      if (!Object.values(checks).every(Boolean)) return 0;

      let score = 55;
      const propertyPostalCode = String(property.postalCode || '').trim();
      const needPostalCode = String(need.postalCode || '').trim();
      const propertyMunicipality = normalizeMatchText(property.municipality);
      const needMunicipality = normalizeMatchText(need.municipality);
      const propertyPrice = Number(property.indicative_price ?? property.price) || 0;
      const needBudget = Number(need.max_budget ?? need.budget) || 0;
      const propertySurface = Number(property.total_area_m2 ?? property.surface) || 0;
      const needSurface = Number(need.desired_area_min_m2 ?? need.surface) || 0;

      if (checks.type) score += 10;
      score += 20; // CCAA, provincia y municipio validados como requisitos territoriales.
      if (propertyPostalCode && needPostalCode && propertyPostalCode === needPostalCode) score += 10;
      else if (propertyMunicipality && needMunicipality && propertyMunicipality === needMunicipality) score += 6;

      if (propertyPrice && needBudget) {
        score += Math.round(15 * Math.min(1, propertyPrice / needBudget));
      }
      if (propertySurface && needSurface) {
        score += Math.round(10 * Math.min(1, needSurface / propertySurface));
      }

      return Math.max(60, Math.min(100, score));
    }

    function getCompatiblePropertiesForNeed(need, limit = 3) {
      return properties
        .map(property => ({ property, score: calculatePropertyNeedCompatibility(property, need) }))
        .filter(match => match.score > 0)
        .sort((a, b) => b.score - a.score || Number(a.property.price || 0) - Number(b.property.price || 0))
        .slice(0, limit);
    }

    function getCompatibleNeedsForProperty(property, limit = 3) {
      return needs
        .map(need => ({ need, score: calculatePropertyNeedCompatibility(property, need) }))
        .filter(match => match.score > 0)
        .sort((a, b) => b.score - a.score || Number(b.need.budget || 0) - Number(a.need.budget || 0))
        .slice(0, limit);
    }

    function getFavoriteStorageKey(type) {
      const email = (getDemoSession?.()?.email || 'guest').toLowerCase().replace(/[^a-z0-9@._-]/g, '');
      const names = { demand:'favoriteDemands', capture:'favoriteCaptures', match:'favoriteMatches' };
      return `captacion_${names[type] || 'favorites'}_${email}`;
    }

    function getFavoriteIds(type) {
      try { return JSON.parse(localStorage.getItem(getFavoriteStorageKey(type))) || []; }
      catch (error) { return []; }
    }

    function isFavorite(type, id) { return getFavoriteIds(type).includes(String(id)); }

    function persistFavoriteCollections() {
      const userEmail = getDemoSession?.()?.email || '';
      const payload = { favoriteDemands:getFavoriteIds('demand'), favoriteCaptures:getFavoriteIds('capture'), favoriteMatches:getFavoriteIds('match'), updatedAt:Date.now() };
      persistWpRecord('user_preferences', payload, { recordKey:`favorites-${userEmail || 'guest'}`, userEmail, title:'Mis favoritos', status:'active' });
    }

    function toggleFavorite(type, id) {
      if (!requireRegisteredAction('guardar favoritos')) return;
      const key = getFavoriteStorageKey(type);
      const values = getFavoriteIds(type);
      const normalizedId = String(id);
      const index = values.indexOf(normalizedId);
      const added = index < 0;
      if (added) values.unshift(normalizedId); else values.splice(index, 1);
      localStorage.setItem(key, JSON.stringify(values));
      persistFavoriteCollections();
      if (type === 'demand') renderNeedsUI(lastFilteredNeeds.length ? lastFilteredNeeds : needs);
      if (type === 'capture') renderMarketplace();
      if (type === 'match') renderSalesMatches();
      renderPrivateFavorites();
      showToast(added ? 'Añadido a Mis favoritos.' : 'Eliminado de Mis favoritos.', 'success');
    }

    function favoriteButton(type, id, label = 'Guardar en favoritos') {
      const active = isFavorite(type, id);
      const accessibleLabel = active ? 'Quitar de favoritos' : 'Añadir a favoritos';
      return `<button type="button" onclick="event.stopPropagation();toggleFavorite('${type}','${escapeHTML(String(id))}')" class="favorite-toggle ${active ? 'is-active' : ''}" aria-label="${accessibleLabel}" aria-pressed="${active ? 'true' : 'false'}" title="${accessibleLabel}">${active ? '♥' : '♡'}</button>`;
    }

    function getSalesMatchRecords() {
      const rows = [];
      getActiveMarketplaceProperties().forEach(property => {
        getCompatibleNeedsForProperty(property, 200).forEach(({ need, score }) => rows.push({
          id:`${property.id}-${need.id}`, property, need, score,
          date:Math.max(Number(property.date)||0, Number(need.date)||0),
          estimatedValue:Number(property.price)||Number(need.budget)||0
        }));
      });
      return rows;
    }

    function openSalesMatchDetails(matchId) {
      if (!requireRegisteredAction('ver los detalles de esta coincidencia')) return;
      const match = getSalesMatchRecords().find(item => item.id === matchId);
      if (!match) return;
      openPostPublishCompatibilityReport('property', match.property);
    }

    function renderSalesMatches() {
      const container = document.getElementById('sales-matches-grid');
      if (!container) return;
      const search = normalizeMatchText(document.getElementById('sales-match-search')?.value || '');
      const type = document.getElementById('sales-match-type')?.value || 'all';
      const ccaa = document.getElementById('sales-match-ccaa')?.value || 'all';
      const province = document.getElementById('sales-match-province')?.value || 'all';
      const municipality = document.getElementById('sales-match-municipality')?.value || 'all';
      const level = document.getElementById('sales-match-level')?.value || 'all';
      const sort = document.getElementById('sales-match-sort')?.value || 'newest';
      let matches = getSalesMatchRecords().filter(item => {
        const haystack = normalizeMatchText(`${item.property.title} ${item.property.reference} ${item.property.province} ${item.property.municipality} ${item.need.title}`);
        const levelOk = level === 'all' || (level === 'high' ? item.score >= 75 : item.score >= 60 && item.score < 75);
        return (!search || haystack.includes(search)) && (type === 'all' || item.property.type === type) && (ccaa === 'all' || item.property.ccaa === ccaa) && (province === 'all' || item.property.province === province) && (municipality === 'all' || item.property.municipality === municipality) && levelOk;
      });
      matches.sort((a,b) => sort === 'score' ? b.score-a.score : sort === 'value' ? b.estimatedValue-a.estimatedValue : b.date-a.date);
      const allMatches = getSalesMatchRecords();
      const count = document.getElementById('sales-match-count'); if (count) count.textContent = allMatches.length;
      const value = document.getElementById('sales-match-value'); if (value) value.textContent = formatCurrency(allMatches.reduce((sum,item)=>sum+item.estimatedValue,0));
      container.innerHTML = matches.map(item => `<article class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5"><div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-black uppercase text-blue">${escapeHTML(item.property.reference || item.property.id)}</span><h3 class="text-sm font-black text-navy mt-1">${escapeHTML(item.property.title)}</h3></div><div class="flex items-center gap-2"><span class="px-2.5 py-1 rounded-full border text-[10px] font-black ${getCompatibilityBadgeClasses(item.score)}">${item.score}%</span>${favoriteButton('match', item.id, 'Guardar coincidencia en favoritos')}</div></div><div class="mt-4 space-y-2 text-[11px] text-slate-500"><p><strong class="text-navy">Demanda:</strong> ${escapeHTML(item.need.title)}</p><p><strong class="text-navy">Zona:</strong> ${escapeHTML([item.property.province,item.property.municipality].filter(Boolean).join(' · '))}</p><p><strong class="text-navy">Valor estimado:</strong> ${formatCurrency(item.estimatedValue)}</p><p><strong class="text-navy">Encaje:</strong> tipo, ubicación y parámetros económicos compatibles.</p></div><button type="button" onclick="openSalesMatchDetails('${item.id}')" class="mt-4 w-full py-2.5 rounded-xl bg-blue text-white text-xs font-black">Ver detalles</button></article>`).join('') || '<div class="md:col-span-2 xl:col-span-3 p-8 rounded-2xl bg-white border border-slate-200 text-sm text-slate-500">No hay coincidencias con los filtros seleccionados.</div>';
    }

    function getCompatibilityBadgeClasses(score) {
      if (score >= 75) return 'bg-green-light text-green border-green/20';
      if (score >= 55) return 'bg-blue-light text-blue border-blue/20';
      return 'bg-amber-light text-amber border-amber/20';
    }

    function renderLinkedPropertiesForNeed(need) {
      const matches = getCompatiblePropertiesForNeed(need, 3);
      if (!matches.length) {
        return `<div class="mt-3 p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-[11px] text-slate-500"><strong class="block text-navy mb-1">Cruce automático de cartera</strong>No se han detectado captaciones compatibles en la base local. La demanda puede mantenerse activa para recibir nuevas propuestas.</div>`;
      }
      return `<div class="mt-3 p-3 rounded-xl border border-blue/15 bg-blue-light/35">
        <div class="flex items-center justify-between gap-3 mb-2"><strong class="text-[11px] text-navy">Captaciones compatibles detectadas</strong><span class="text-[10px] font-bold text-blue">${matches.length} coincidencia${matches.length === 1 ? '' : 's'}</span></div>
        <div class="space-y-2">${matches.map(({ property, score }) => `
          <div class="p-2.5 rounded-lg bg-white border border-slate-200 flex items-center justify-between gap-3">
             <div class="min-w-0"><span class="block text-[10px] font-bold text-blue">Ref. ${escapeHTML(property.reference || property.id)}</span><strong class="block text-[11px] text-navy truncate">${escapeHTML(property.title)}</strong><span class="block text-[10px] text-slate-500">${formatCurrency(property.price)} · C.P. ${escapeHTML(maskPublicPostalCode(property.postalCode))}</span></div>
            <div class="shrink-0 text-right"><span class="inline-flex px-2 py-1 rounded-full border text-[10px] font-bold ${getCompatibilityBadgeClasses(score)}">${score}% match</span><button type="button" onclick="openMapPropertyCard('${property.id}')" class="block mt-1 ml-auto text-[10px] font-bold text-blue hover:underline">Ver propiedad</button></div>
          </div>`).join('')}</div>
      </div>`;
    }

    function renderLinkedNeedsForProperty(property) {
      const matches = getCompatibleNeedsForProperty(property, 3);
      if (!matches.length) {
        return `<div class="mt-3 p-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 text-[11px] text-slate-500"><strong class="block text-navy mb-1">Demandas vinculables</strong>No se han detectado demandas compatibles en la base local. La captación seguirá disponible para futuras coincidencias.</div>`;
      }
      return `<div class="mt-3 p-3 rounded-xl border border-green/15 bg-green-light/35">
        <div class="flex items-center justify-between gap-3 mb-2"><strong class="text-[11px] text-navy">Demandas vinculables detectadas</strong><span class="text-[10px] font-bold text-green">${matches.length} coincidencia${matches.length === 1 ? '' : 's'}</span></div>
        <div class="space-y-2">${matches.map(({ need, score }) => `
          <div class="p-2.5 rounded-lg bg-white border border-slate-200 flex items-center justify-between gap-3">
             <div class="min-w-0"><span class="block text-[10px] font-bold text-green">Intención de búsqueda</span><strong class="block text-[11px] text-navy truncate">${escapeHTML(need.title)}</strong><span class="block text-[10px] text-slate-500">Hasta ${formatCurrency(need.budget)} · C.P. ${escapeHTML(maskPublicPostalCode(need.postalCode))}</span></div>
            <div class="shrink-0 text-right"><span class="inline-flex px-2 py-1 rounded-full border text-[10px] font-bold ${getCompatibilityBadgeClasses(score)}">${score}% match</span><button type="button" onclick="openMapNeedCard('${need.id}')" class="block mt-1 ml-auto text-[10px] font-bold text-green hover:underline">Ver demanda</button></div>
          </div>`).join('')}</div>
      </div>`;
    }

    function openHomeNeedMatches(needId) {
      const selectedNeed = needs.find(item => item.id === needId);
      if (!selectedNeed) return;
      const matches = getCompatiblePropertiesForNeed(selectedNeed, 3);
      openMapNeedCard(needId);
      setTimeout(() => showToast(matches.length ? `Demanda abierta: se han detectado ${matches.length} captaciones compatibles.` : 'Demanda abierta: no se han detectado captaciones compatibles todavía.', matches.length ? 'success' : 'info'), 220);
    }

    function buildOpportunityAccordion(title, subtitle, rowsHtml, buttonHtml = '', open = false) {
      return `<details class="opportunity-accordion" ${open ? 'open' : ''}>
        <summary class="px-5 py-4 flex flex-wrap items-center justify-between gap-3">
          <div>
            <strong class="block text-sm text-navy font-black">${escapeHTML(title)}</strong>
            <span class="block text-[11px] text-slate-500 mt-1">${escapeHTML(subtitle)}</span>
          </div>
          <span class="opportunity-accordion-chevron text-slate-400 text-sm transition-transform">▾</span>
        </summary>
        <div class="px-4 pb-4 space-y-3">
          ${rowsHtml}
          ${buttonHtml}
        </div>
      </details>`;
    }

    function renderMarketplaceAccordionSections(list) {
      const container = document.getElementById('marketplace-accordion-sections');
      if (!container) return;
      if (!list.length) {
        container.innerHTML = '';
        return;
      }
      const latestRows = list.slice(0, 6).map(prop => {
        const score = Number(prop.score || calculatePublicationOpportunityScore(prop, 'property')) || 0;
        return `<article class="opportunity-mini-row flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="min-w-0">
            <span class="block text-[10px] font-black uppercase tracking-wider text-blue">${escapeHTML(normalizeOpportunityCategory(prop.type))}</span>
            <strong class="block text-sm text-navy mt-1 truncate">${escapeHTML(prop.title)}</strong>
            <span class="block text-[11px] text-slate-500 mt-1">${escapeHTML(prop.province || prop.location)} · C.P. ${escapeHTML(maskPublicPostalCode(prop.postalCode))} · ${formatRelativeTime(prop.date)}</span>
          </div>
          <div class="flex flex-wrap items-center gap-2 shrink-0">
            <span class="private-status-pill bg-blue-light text-blue">★ ${score}/100</span>
            <button onclick="openMapPropertyCard('${prop.id}')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-navy">Abrir</button>
          </div>
        </article>`;
      }).join('');
      const groups = OPPORTUNITY_CATEGORY_ORDER.map(category => ({
        category,
        items: list.filter(item => normalizeOpportunityCategory(item.type) === category)
      })).filter(group => group.items.length);
      const accordions = [
        buildOpportunityAccordion('Últimas captaciones publicadas', 'Ordenadas por tiempo de publicación para detectar producto nuevo con rapidez.', latestRows, `<div class="pt-1 flex justify-end"><button onclick="document.getElementById('market-sort').value='newest';refreshMarketplaceView();document.getElementById('marketplace-grid')?.scrollIntoView({behavior:'smooth',block:'start'});" class="px-4 py-2 rounded-xl bg-blue text-white text-[11px] font-bold">Ver todas las recientes</button></div>`, true)
      ];
      groups.forEach(group => {
        const rows = group.items.slice(0, 4).map(prop => `<article class="opportunity-mini-row flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="min-w-0">
            <strong class="block text-sm text-navy truncate">${escapeHTML(prop.title)}</strong>
            <span class="block text-[11px] text-slate-500 mt-1">${formatCurrency(prop.price)} · ${escapeHTML(prop.province || prop.location)} · ${formatRelativeTime(prop.date)}</span>
          </div>
          <div class="flex flex-wrap items-center gap-2 shrink-0">
            <span class="private-status-pill bg-green-light text-green">${getCompatibleNeedsForProperty(prop, 10).length} match</span>
            <button onclick="openMapPropertyCard('${prop.id}')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-navy">Abrir</button>
          </div>
        </article>`).join('');
        accordions.push(buildOpportunityAccordion(group.category, `${group.items.length} propiedad${group.items.length === 1 ? '' : 'es'} en esta categoría.`, rows, `<div class="pt-1 flex justify-end"><button onclick="applyMarketplaceCategoryFilter('${group.category}')" class="px-4 py-2 rounded-xl border border-slate-200 text-[11px] font-bold text-blue">Ver todas las de ${escapeHTML(group.category)}</button></div>`));
      });
      container.innerHTML = accordions.join('');
    }

    function renderNeedsAccordionSections(list) {
      const container = document.getElementById('needs-accordion-sections');
      if (!container) return;
      if (!list.length) {
        container.innerHTML = '';
        return;
      }
      const latestRows = list.slice(0, 6).map(need => {
        const score = calculatePublicationOpportunityScore(need, 'need');
        return `<article class="opportunity-mini-row flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="min-w-0">
            <span class="block text-[10px] font-black uppercase tracking-wider text-green">${escapeHTML(normalizeOpportunityCategory(need.type))}</span>
            <strong class="block text-sm text-navy mt-1 truncate">${escapeHTML(need.title)}</strong>
            <span class="block text-[11px] text-slate-500 mt-1">Hasta ${formatCurrency(need.budget)} · C.P. ${escapeHTML(maskPublicPostalCode(need.postalCode))} · ${formatRelativeTime(need.date)}</span>
          </div>
          <div class="flex flex-wrap items-center gap-2 shrink-0">
            <span class="private-status-pill bg-green-light text-green">★ ${score}/100</span>
            <button onclick="openHomeNeedMatches('${need.id}')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-navy">Abrir</button>
          </div>
        </article>`;
      }).join('');
      const groups = OPPORTUNITY_CATEGORY_ORDER.map(category => ({
        category,
        items: list.filter(item => normalizeOpportunityCategory(item.type) === category)
      })).filter(group => group.items.length);
      const accordions = [
        buildOpportunityAccordion('Últimas captaciones solicitadas', 'Demandas nuevas agrupadas por recencia para detectar encajes cuanto antes.', latestRows, `<div class="pt-1 flex justify-end"><button onclick="document.getElementById('need-filter-time').value='newest';filterNeeds();document.getElementById('needs-list-container')?.scrollIntoView({behavior:'smooth',block:'start'});" class="px-4 py-2 rounded-xl bg-navy text-white text-[11px] font-bold">Ver todas las recientes</button></div>`, true)
      ];
      groups.forEach(group => {
        const rows = group.items.slice(0, 4).map(need => `<article class="opportunity-mini-row flex flex-col lg:flex-row lg:items-center justify-between gap-3">
          <div class="min-w-0">
            <strong class="block text-sm text-navy truncate">${escapeHTML(need.title)}</strong>
            <span class="block text-[11px] text-slate-500 mt-1">Hasta ${formatCurrency(need.budget)} · ${escapeHTML(need.province || 'España')} · ${formatRelativeTime(need.date)}</span>
          </div>
          <div class="flex flex-wrap items-center gap-2 shrink-0">
            <span class="private-status-pill bg-blue-light text-blue">${getCompatiblePropertiesForNeed(need, 10).length} match</span>
            <button onclick="openHomeNeedMatches('${need.id}')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-navy">Abrir</button>
          </div>
        </article>`).join('');
        accordions.push(buildOpportunityAccordion(group.category, `${group.items.length} solicitud${group.items.length === 1 ? '' : 'es'} en esta categoría.`, rows, `<div class="pt-1 flex justify-end"><button onclick="applyNeedsCategoryFilter('${group.category}')" class="px-4 py-2 rounded-xl border border-slate-200 text-[11px] font-bold text-green">Ver todas las de ${escapeHTML(group.category)}</button></div>`));
      });
      container.innerHTML = accordions.join('');
    }

    function createOpportunityRailId(prefix, key = '') {
      return `${prefix}-rail-${String(key || 'latest').toLowerCase().replace(/[^a-z0-9]+/g, '-')}`;
    }

    function scrollOpportunityRail(railId, direction = 1) {
      const rail = document.getElementById(railId);
      if (!rail) return;
      const amount = Math.max(rail.clientWidth * 0.85, 260) * direction;
      rail.scrollBy({ left: amount, behavior:'smooth' });
    }

    function buildOpportunityCategoryNav(groups, mode = 'market') {
      if (!groups.length) return '';
      const chipClass = mode === 'market' ? 'is-market' : 'is-need';
      const action = mode === 'market' ? 'applyMarketplaceCategoryFilter' : 'applyNeedsCategoryFilter';
      return `<div class="opportunity-category-nav">${groups.map(group => `<button type="button" onclick="${action}('${escapeHTML(group.category)}')" class="opportunity-category-chip ${chipClass}">${escapeHTML(group.category)} <span class="ml-1 opacity-60">${group.items.length}</span></button>`).join('')}</div>`;
    }

    function buildOpportunityAccordion(title, subtitle, rowsHtml, buttonHtml = '', open = false, railId = '') {
      const safeRailId = railId || createOpportunityRailId('opportunity', title);
      return `<section class="opportunity-showcase">
        <div class="opportunity-showcase-toolbar mb-4">
          <div>
            <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-blue">${escapeHTML(open ? 'Producto compartido' : 'Categoria activa')}</span>
            <strong class="block text-xl text-navy font-black mt-1">${escapeHTML(title)}</strong>
            <span class="block text-[11px] text-slate-500 mt-1">${escapeHTML(subtitle)}</span>
          </div>
          <div class="opportunity-showcase-controls">
            ${buttonHtml}
            <button type="button" onclick="scrollOpportunityRail('${safeRailId}', -1)" class="opportunity-showcase-arrow" aria-label="Ver anteriores">‹</button>
            <button type="button" onclick="scrollOpportunityRail('${safeRailId}', 1)" class="opportunity-showcase-arrow" aria-label="Ver siguientes">›</button>
          </div>
        </div>
        <div id="${safeRailId}" class="opportunity-showcase-rail">${rowsHtml}</div>
      </section>`;
    }

    function buildOpportunityCategoryNav(groups, mode = 'market') {
      if (!groups.length) return '';
      const scopeId = `opportunity-category-${mode}`;
      const action = mode === 'market' ? 'applyMarketplaceCategoryFilter' : 'applyNeedsCategoryFilter';
      const actionLabel = mode === 'market' ? 'Abrir ofertas' : 'Abrir demandas';
      const searchPlaceholder = mode === 'market' ? 'Buscar categoria: piso, nave, local...' : 'Buscar demanda: piso, casa, oficina...';
      const descriptions = {
        'Piso': 'Accesos rapidos a pisos y viviendas urbanas activas.',
        'Casa/Chalet': 'Demandas o captaciones de vivienda unifamiliar y chalet.',
        'Local Comercial': 'Producto comercial para negocio, retail o rentabilidad.',
        'Nave': 'Activos industriales y logisticos con uso profesional.',
        'Oficina': 'Espacios de trabajo y despachos para actividad empresarial.',
        'Edificio': 'Bloques completos y activos de mayor escala.',
        'Suelo/Terreno': 'Parcelas y suelo con potencial de desarrollo.',
        'Otros': 'Activos no encajados en una categoria principal.'
      };
      return `<section class="opportunity-category-explorer" id="${scopeId}">
        <div class="opportunity-category-explorer-toolbar">
          <div>
            <span class="block text-[10px] font-black uppercase tracking-[0.18em] ${mode === 'market' ? 'text-blue' : 'text-green'}">${mode === 'market' ? 'Grupos de captacion' : 'Grupos de demanda'}</span>
            <strong class="block text-xl text-navy font-black mt-1">Categorias explorables</strong>
            <span class="block text-[11px] text-slate-500 mt-1">Cada tipo aparece como ficha seleccionable y puedes filtrarlo por nombre antes de abrir el listado completo.</span>
          </div>
          <input type="search" class="opportunity-category-search" placeholder="${searchPlaceholder}" oninput="filterOpportunityCategoryCards('${scopeId}', this.value)" />
        </div>
        <div class="opportunity-category-grid">${groups.map(group => {
          const image = escapeHTML(getVirtualMarketplaceImage(group.category));
          const copy = descriptions[group.category] || descriptions['Otros'];
          return `<article class="opportunity-category-card" data-category-card data-search="${escapeHTML((group.category + ' ' + copy).toLowerCase())}">
            <div class="opportunity-category-card-image">
              <img src="${image}" data-virtual-type="${escapeHTML(group.category)}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" alt="Categoria ${escapeHTML(group.category)}" />
              <span class="opportunity-category-card-badge">${mode === 'market' ? 'Categoria' : 'Grupo'}</span>
              <span class="opportunity-category-card-count">${group.items.length}</span>
            </div>
            <div class="opportunity-category-card-body">
              <strong class="opportunity-category-card-title">${escapeHTML(group.category)}</strong>
              <span class="opportunity-category-card-copy">${escapeHTML(copy)}</span>
              <div class="opportunity-category-card-footer">
                <div><span class="opportunity-category-card-note">${group.items.length} ${mode === 'market' ? 'captaciones' : 'solicitudes'}</span></div>
                <button type="button" onclick="${action}('${escapeHTML(group.category)}')" class="opportunity-category-card-action">${actionLabel}</button>
              </div>
            </div>
          </article>`;
        }).join('')}</div>
        <div class="opportunity-category-empty hidden" data-category-empty>No hay categorias que coincidan con la busqueda.</div>
      </section>`;
    }

    function filterOpportunityCategoryCards(scopeId, query = '') {
      const scope = document.getElementById(scopeId);
      if (!scope) return;
      const value = cleanText(query || '').toLowerCase();
      let visible = 0;
      scope.querySelectorAll('[data-category-card]').forEach(card => {
        const haystack = card.getAttribute('data-search') || '';
        const matches = value === '' || haystack.includes(value);
        card.classList.toggle('is-hidden', !matches);
        if (matches) visible += 1;
      });
      const empty = scope.querySelector('[data-category-empty]');
      if (empty) empty.classList.toggle('hidden', visible !== 0);
    }

    function buildOpportunityAccordion(title, subtitle, rowsHtml, buttonHtml = '', open = false, railId = '') {
      const safeRailId = railId || createOpportunityRailId('opportunity', title);
      return `<section class="opportunity-showcase">
        <div class="opportunity-showcase-toolbar mb-4">
          <div>
            <span class="block text-[10px] font-black uppercase tracking-[0.18em] text-blue">${escapeHTML(open ? 'Producto compartido' : 'Categoria activa')}</span>
            <strong class="block text-xl text-navy font-black mt-1">${escapeHTML(title)}</strong>
            <span class="block text-[11px] text-slate-500 mt-1">${escapeHTML(subtitle)}</span>
          </div>
          <div class="opportunity-showcase-controls">
            <button type="button" onclick="scrollOpportunityRail('${safeRailId}', -1)" class="opportunity-showcase-arrow" aria-label="Ver anteriores"><span aria-hidden="true">&lsaquo;</span><span class="opportunity-showcase-arrow-label">Izquierda</span></button>
            ${buttonHtml}
            <button type="button" onclick="scrollOpportunityRail('${safeRailId}', 1)" class="opportunity-showcase-arrow" aria-label="Ver siguientes"><span class="opportunity-showcase-arrow-label">Derecha</span><span aria-hidden="true">&rsaquo;</span></button>
          </div>
        </div>
        <div id="${safeRailId}" class="opportunity-showcase-rail">${rowsHtml}</div>
      </section>`;
    }

    function renderMarketplaceShowcaseCard(prop, variant = 'latest') {
      const score = Number(prop.score || calculatePublicationOpportunityScore(prop, 'property')) || 0;
      const image = escapeHTML(resolveMarketplaceImage(prop.image, prop.type));
      const location = escapeHTML(prop.province || prop.location || 'Ubicacion reservada');
      const postalCode = escapeHTML(maskPublicPostalCode(prop.postalCode));
      const publishedText = formatRelativeTime(prop.date);
      const price = formatCurrency(prop.price);
      const note = variant === 'latest' ? `${location} · C.P. ${postalCode}` : `${getCompatibleNeedsForProperty(prop, 10).length} match · ${location}`;
      return `<article class="opportunity-showcase-card">
        <div class="opportunity-showcase-card-image">
          <img src="${image}" data-virtual-type="${escapeHTML(prop.type || 'Activo inmobiliario')}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" alt="Imagen de ${escapeHTML(prop.title)}" />
          <span class="opportunity-showcase-badge">${escapeHTML(normalizeOpportunityCategory(prop.type))}</span>
          <span class="opportunity-showcase-score">★ ${score}/100</span>
          <div class="absolute left-3 top-3 z-20">${favoriteButton('capture', prop.id, 'Guardar captación en favoritos')}</div>
        </div>
        <div class="opportunity-showcase-body">
          <div class="opportunity-showcase-meta"><span>${escapeHTML(publishedText)}</span><span>C.P. ${postalCode}</span></div>
          <strong class="opportunity-showcase-title">${escapeHTML(prop.title)}</strong>
          <span class="opportunity-showcase-copy">${note}</span>
          <div class="opportunity-showcase-footer">
            <div>
              <span class="opportunity-showcase-note">Precio</span>
              <strong class="opportunity-showcase-price">${price}</strong>
            </div>
            <button onclick="openMapPropertyCard('${prop.id}')" class="px-4 py-2 rounded-xl bg-white/12 border border-white/12 text-white text-[11px] font-black">Solicitar acceso</button>
          </div>
        </div>
      </article>`;
    }

    function renderNeedShowcaseCard(need, variant = 'latest') {
      const score = calculatePublicationOpportunityScore(need, 'need');
      const image = escapeHTML(getVirtualMarketplaceImage(need.type || 'Demanda activa'));
      const province = escapeHTML(need.province || 'España');
      const postalCode = escapeHTML(maskPublicPostalCode(need.postalCode));
      const publishedText = formatRelativeTime(need.date);
      const budget = formatCurrency(need.budget);
      const note = variant === 'latest' ? `${province} · C.P. ${postalCode}` : `${getCompatiblePropertiesForNeed(need, 10).length} match · ${province}`;
      return `<article class="opportunity-showcase-card">
        <div class="opportunity-showcase-card-image">
          <img src="${image}" data-virtual-type="${escapeHTML(need.type || 'Demanda activa')}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" alt="Referencia visual de ${escapeHTML(need.title)}" />
          <span class="opportunity-showcase-badge">${escapeHTML(normalizeOpportunityCategory(need.type))}</span>
          <span class="opportunity-showcase-score">★ ${score}/100</span>
          <div class="absolute left-3 top-3 z-20">${favoriteButton('demand', need.id, 'Guardar demanda en favoritos')}</div>
        </div>
        <div class="opportunity-showcase-body">
          <div class="opportunity-showcase-meta"><span>${escapeHTML(publishedText)}</span><span>C.P. ${postalCode}</span></div>
          <strong class="opportunity-showcase-title">${escapeHTML(need.title)}</strong>
          <span class="opportunity-showcase-copy">${note}</span>
          <div class="opportunity-showcase-footer">
            <div>
              <span class="opportunity-showcase-note">Presupuesto</span>
              <strong class="opportunity-showcase-price">${budget}</strong>
            </div>
            <button onclick="openHomeNeedMatches('${need.id}')" class="px-4 py-2 rounded-xl bg-white/12 border border-white/12 text-white text-[11px] font-black">${variant === 'latest' ? 'Ver demanda' : 'Ver compatibles'}</button>
          </div>
        </div>
      </article>`;
    }

    function renderMarketplaceAccordionSections(list) {
      const container = document.getElementById('marketplace-accordion-sections');
      if (!container) return;
      if (!list.length) {
        container.innerHTML = '';
        return;
      }
      const latestRows = list.slice(0, 6).map(prop => renderMarketplaceShowcaseCard(prop, 'latest')).join('');
      const groups = OPPORTUNITY_CATEGORY_ORDER.map(category => ({
        category,
        items: list.filter(item => normalizeOpportunityCategory(item.type) === category)
      })).filter(group => group.items.length);
      const sections = [
        `<div class="opportunity-showcase-shell">${buildOpportunityAccordion('Ultimas captaciones publicadas', 'Ordenadas por tiempo de publicacion para detectar producto nuevo con rapidez.', latestRows, `<button onclick="document.getElementById('market-sort').value='newest';refreshMarketplaceView();document.getElementById('marketplace-grid')?.scrollIntoView({behavior:'smooth',block:'start'});" class="px-4 py-2 rounded-xl bg-blue text-white text-[11px] font-bold">Ver todas las recientes</button>`, true, createOpportunityRailId('market', 'latest'))}<div>${buildOpportunityCategoryNav(groups, 'market')}</div></div>`
      ];
      groups.forEach(group => {
        const rows = group.items.slice(0, 5).map(prop => renderMarketplaceShowcaseCard(prop, 'category')).join('');
        sections.push(buildOpportunityAccordion(group.category, `${group.items.length} propiedad${group.items.length === 1 ? '' : 'es'} en esta categoria.`, rows, `<button onclick="applyMarketplaceCategoryFilter('${group.category}')" class="px-4 py-2 rounded-xl border border-slate-200 text-[11px] font-bold text-blue">Ver todas las de ${escapeHTML(group.category)}</button>`, false, createOpportunityRailId('market', group.category)));
      });
      container.innerHTML = sections.join('');
    }

    function renderNeedsAccordionSections(list) {
      const container = document.getElementById('needs-accordion-sections');
      if (!container) return;
      if (!list.length) {
        container.innerHTML = '';
        return;
      }
      const latestRows = list.slice(0, 6).map(need => renderNeedShowcaseCard(need, 'latest')).join('');
      const groups = OPPORTUNITY_CATEGORY_ORDER.map(category => ({
        category,
        items: list.filter(item => normalizeOpportunityCategory(item.type) === category)
      })).filter(group => group.items.length);
      const sections = [
        `<div class="opportunity-showcase-shell">${buildOpportunityAccordion('Ultimas captaciones solicitadas', 'Demandas nuevas agrupadas por recencia para detectar encajes cuanto antes.', latestRows, `<button onclick="document.getElementById('need-filter-time').value='newest';filterNeeds();document.getElementById('needs-list-container')?.scrollIntoView({behavior:'smooth',block:'start'});" class="px-4 py-2 rounded-xl bg-navy text-white text-[11px] font-bold">Ver todas las recientes</button>`, true, createOpportunityRailId('need', 'latest'))}<div>${buildOpportunityCategoryNav(groups, 'need')}</div></div>`
      ];
      groups.forEach(group => {
        const rows = group.items.slice(0, 5).map(need => renderNeedShowcaseCard(need, 'category')).join('');
        sections.push(buildOpportunityAccordion(group.category, `${group.items.length} solicitud${group.items.length === 1 ? '' : 'es'} en esta categoria.`, rows, `<button onclick="applyNeedsCategoryFilter('${group.category}')" class="px-4 py-2 rounded-xl border border-slate-200 text-[11px] font-bold text-green">Ver todas las de ${escapeHTML(group.category)}</button>`, false, createOpportunityRailId('need', group.category)));
      });
      container.innerHTML = sections.join('');
    }

    function applyMarketplaceCategoryFilter(category) {
      const categorySelect = document.getElementById('market-category-filter');
      const sortSelect = document.getElementById('market-sort');
      if (categorySelect) categorySelect.value = category;
      if (sortSelect) sortSelect.value = 'category';
      refreshMarketplaceView();
      document.getElementById('marketplace-grid')?.scrollIntoView({ behavior:'smooth', block:'start' });
    }

    function applyNeedsCategoryFilter(category) {
      const categorySelect = document.getElementById('need-filter-type');
      const timeSelect = document.getElementById('need-filter-time');
      if (categorySelect) categorySelect.value = category;
      if (timeSelect) timeSelect.value = 'newest';
      filterNeeds();
      document.getElementById('needs-list-container')?.scrollIntoView({ behavior:'smooth', block:'start' });
    }

    function renderNeedsUI(list) {
      const container = document.getElementById('needs-list-container');
      if (!container) return;
      container.innerHTML = '';

      if (list.length === 0) {
        const needsAccordion = document.getElementById('needs-accordion-sections');
        if (needsAccordion) needsAccordion.innerHTML = '';
        container.innerHTML = `
          <div class="text-center py-16 bg-white rounded-3xl border border-slate-200 shadow-sm">
            <span class="text-4xl block mb-3">&#128269;</span>
            <h4 class="text-navy font-bold text-base">No hay necesidades con estos criterios</h4>
            <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Prueba a cambiar los filtros o publica tu mismo una necesidad arriba.</p>
          </div>`;
        return;
      }

      const visibleNeeds = list.slice(0, needsVisibleLimit);
      const grid = document.createElement('div');
      grid.className = "grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4";

      visibleNeeds.forEach(need => {
        const timeText = formatRelativeTime(need.date);
        const locationLabel = need.locality ? `${need.municipality} (${need.locality})` : need.municipality;
        const card = document.createElement('article');
        card.id = `need-card-${need.id}`;
        card.className = "bg-white rounded-2xl border border-slate-200/80 shadow-sm p-5 hover:border-blue/30 hover:shadow-md transition-all";
        card.innerHTML = `
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <span class="text-[10px] font-black uppercase tracking-wider text-blue">Busco captación</span>
              <h3 class="text-base font-black text-navy mt-1 leading-snug">${escapeHTML(need.title)}</h3>
            </div>
            <div class="shrink-0 flex items-center gap-2"><span class="px-2 py-1 rounded-full bg-green-light text-green text-[10px] font-black">Verificada</span>${favoriteButton('demand', need.id, 'Guardar demanda en favoritos')}</div>
          </div>
          <p class="text-[11px] text-slate-400 mt-2 font-semibold">${timeText} ? ${escapeHTML(need.agency || 'Agencia verificada')}</p>
          <div class="mt-4 flex flex-wrap gap-2 text-[10px] font-bold">
            <span class="px-2.5 py-1 rounded-full bg-blue-light text-blue">${escapeHTML(need.type)}</span>
            <span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-600">${escapeHTML(need.operation || 'Venta')}</span>
            <span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-600">${escapeHTML(need.province || 'España')}</span>
            <span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200 text-slate-600">C.P. ${escapeHTML(maskPublicPostalCode(need.postalCode))}</span>
          </div>
          <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3"><span class="block text-[10px] uppercase font-black text-slate-400">Presupuesto</span><strong class="block text-navy mt-1">${formatCurrency(need.budget)}</strong></div>
            <div class="rounded-xl bg-slate-50 border border-slate-200 p-3"><span class="block text-[10px] uppercase font-black text-slate-400">Zona</span><strong class="block text-navy mt-1 truncate">${escapeHTML(locationLabel || need.province || 'N/D')}</strong></div>
          </div>
          <div id="details-${need.id}" class="hidden mt-4 pt-4 border-t border-slate-100 text-xs text-slate-600 space-y-2">
            <div><strong>Descripción:</strong> ${escapeHTML(need.description || 'Sin descripción adicional.')}</div>
            <div><strong>Características mínimas:</strong> ${formatPropertyFeatures(need, true)}</div>
            <div><strong>Financiación:</strong> ${escapeHTML(need.funding || 'A consultar')}</div>
            <div><strong>Urgencia:</strong> ${escapeHTML(need.urgency || 'Media')}</div>
            ${renderLinkedPropertiesForNeed(need)}
          </div>
          <div class="mt-4 flex flex-col sm:flex-row gap-2">
            <button onclick="toggleCardDetails('${need.id}')" id="toggle-btn-${need.id}" class="flex-1 px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:text-navy">Ver más detalles</button>
            <button onclick="runAIMatchmaker('${need.id}')" class="flex-1 px-4 py-2 rounded-xl bg-navy text-white text-xs font-black">Match IA</button>
            <button onclick="openNeedCollaborationModal('${need.id}')" class="flex-1 px-4 py-2 rounded-xl bg-blue text-white text-xs font-black">Colaborar</button>
          </div>`;
        grid.appendChild(card);
      });

      container.appendChild(grid);
      appendLoadMoreControl(container, visibleNeeds.length, list.length, 'loadMoreNeeds', 'demandas');
    }

    function getMarketplaceScoreVisual(score) {
      const value = Math.max(0, Math.min(100, Number(score) || 0));
      if (value < 20) return { value, label: 'Ranking bajo', classes: 'bg-red-600 text-white border-red-700' };
      if (value < 60) return { value, label: 'Ranking bajo medio', classes: 'bg-amber-400 text-navy border-amber-500' };
      if (value < 85) return { value, label: 'Ranking medio alto', classes: 'bg-blue text-white border-blue-dark' };
      return { value, label: 'Ranking alto', classes: 'bg-green text-white border-emerald-700' };
    }


    function buildMarketplaceCarouselDetails(prop) {
      const location = [prop.province || prop.location, prop.municipality, prop.locality].filter(Boolean).join(' · ');
      const condition = typeof prop.rehab === 'boolean' ? (prop.rehab ? 'Reforma declarada' : 'Sin reforma integral declarada') : '';
      const rows = [
        ['Tipo de inmueble', prop.type],
        ['Zona aproximada', location],
        ['Precio', Number(prop.price) ? formatCurrency(prop.price) : ''],
        ['Estado', condition],
        ['Características', formatPropertyFeatures(prop, true)],
        ['Urgencia', prop.urgency],
        ['Colaboración', prop.fee ? `Honorarios: ${prop.fee}` : ''],
        ['Estado documental', prop.docs],
        ['Score de calidad', prop.score ? `${prop.score}/100` : ''],
        ['Descripción', prop.description]
      ].filter(([, value]) => value && String(value).trim());
      return rows.map(([label, value]) => `<div><strong class="text-navy">${escapeHTML(label)}:</strong> ${escapeHTML(value)}</div>`).join('');
    }

    function toggleMarketplaceCarouselDetails(propertyId) {
      const panel = document.getElementById(`marketplace-carousel-details-${propertyId}`);
      const button = document.getElementById(`marketplace-carousel-details-btn-${propertyId}`);
      if (!panel) return;
      const shouldOpen = panel.classList.contains('hidden');
      document.querySelectorAll('.marketplace-carousel-detail-panel').forEach(item => item.classList.add('hidden'));
      document.querySelectorAll('.marketplace-carousel-detail-button').forEach(item => { item.textContent = 'Ver más detalles'; });
      panel.classList.toggle('hidden', !shouldOpen);
      if (button) button.textContent = shouldOpen ? 'Ocultar detalles' : 'Ver más detalles';
    }

    function getMarketplaceMatchLevel(score) {
      if (score >= 75) return 'Alto';
      if (score >= 55) return 'Medio';
      return 'Bajo';
    }

    function buildMarketplacePropertyMatchReport(property) {
      if (!Array.isArray(needs) || !needs.length) {
        return `<h3 class="text-lg font-black text-blue mt-4 mb-2">Match IA de la captación</h3><p>Todavía no hay demandas activas suficientes para calcular coincidencias.</p>`;
      }
      const matches = getCompatibleNeedsForProperty(property, 5);
      if (!matches.length) {
        return `<h3 class="text-lg font-black text-blue mt-4 mb-2">Match IA de la captación</h3><p>No se han encontrado demandas compatibles con esta captación en este momento.</p>${buildMatchNotificationNotice('property')}`;
      }
      return `<h3 class="text-lg font-black text-blue mt-4 mb-2">Demandas compatibles</h3><p>El cruce utiliza los criterios activos de ubicación, tipología, presupuesto, superficie, dormitorios y baños.</p><div class="mt-4 space-y-3">${matches.map(({ need, score }) => {
        const sameMunicipality = normalizeMatchText(property.municipality) && normalizeMatchText(property.municipality) === normalizeMatchText(need.municipality);
        const reason = `${sameMunicipality ? 'Mismo municipio' : 'Misma comunidad autónoma y provincia'}, presupuesto compatible y características principales dentro de los márgenes definidos.`;
        return `<article class="p-4 rounded-2xl border border-slate-200 bg-slate-50"><div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3"><div class="min-w-0"><span class="block text-[10px] font-black text-green">${escapeHTML(need.buyerType || 'Demanda activa')}</span><strong class="block text-sm text-navy mt-1">${escapeHTML(need.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">${escapeHTML([need.province, need.municipality].filter(Boolean).join(' · ') || 'Zona no disponible')} · Hasta ${formatCurrency(need.budget)}</span></div><span class="shrink-0 inline-flex px-3 py-1 rounded-full border text-[10px] font-black ${getCompatibilityBadgeClasses(score)}">${getMarketplaceMatchLevel(score)} · ${score}%</span></div><p class="text-[11px] text-slate-500 mt-3">${escapeHTML(reason)}</p><button type="button" onclick="openMapNeedCard('${need.id}')" class="mt-3 px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Ver demanda</button></article>`;
      }).join('')}</div>`;
    }

    function runMarketplacePropertyMatch(propertyId) {
      if (!requireRegisteredAction('usar el Match IA')) return;
      const property = properties.find(item => item.id === propertyId);
      const modal = document.getElementById('ai-match-modal');
      const loader = document.getElementById('ai-loading');
      const report = document.getElementById('ai-report');
      const reportContent = document.getElementById('ai-report-content');
      if (!property || !modal || !reportContent) return;
      modal.classList.remove('hidden');
      loader?.classList.add('hidden');
      report?.classList.remove('hidden');
      reportContent.innerHTML = buildMarketplacePropertyMatchReport(property);
    }

    function renderMarketplaceCarousel(list = []) {
      const container = document.getElementById('marketplace-carousel');
      if (!container) return;
      const latest = [...list].sort((a, b) => (Number(b.date) || 0) - (Number(a.date) || 0));
      if (!latest.length) {
        container.innerHTML = '';
        return;
      }
      if (marketplaceCarouselOffset >= latest.length) marketplaceCarouselOffset = 0;
      const page = Array.from({ length: Math.min(MARKETPLACE_CAROUSEL_SIZE, latest.length) }, (_, index) => latest[(marketplaceCarouselOffset + index) % latest.length]);
      container.innerHTML = `
        <section class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
            <div>
              <span class="text-[10px] font-black uppercase tracking-[0.18em] text-blue">Últimas publicadas</span>
              <h3 class="text-xl font-black text-navy mt-1">Carrusel de captaciones recientes</h3>
            </div>
            <div class="flex gap-2">
              <button type="button" aria-label="Captaciones anteriores" onclick="moveMarketplaceCarousel(-1)" class="w-10 h-10 rounded-xl border border-slate-200 text-navy font-black hover:border-blue hover:text-blue">‹</button>
              <button type="button" aria-label="Captaciones siguientes" onclick="moveMarketplaceCarousel(1)" class="w-10 h-10 rounded-xl border border-slate-200 text-navy font-black hover:border-blue hover:text-blue">›</button>
            </div>
          </div>
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-start">
            ${page.map(prop => {
              const image = escapeHTML(resolveMarketplaceImage(prop.image, prop.type));
              return `<article class="rounded-2xl overflow-hidden border border-slate-200 bg-slate-50 shadow-sm">
                <div class="aspect-[4/3] relative bg-slate-100">
                  <img src="${image}" data-virtual-type="${escapeHTML(prop.type)}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" class="absolute inset-0 w-full h-full object-cover" alt="Imagen de ${escapeHTML(prop.title)}" />
                  <div class="absolute inset-0 bg-gradient-to-t from-navy/80 via-transparent to-transparent"></div>
                  <div class="absolute right-3 top-3 z-20">${favoriteButton('capture', prop.id, 'Guardar captación en favoritos')}</div>
                  <span class="absolute left-3 bottom-3 px-2 py-1 rounded-full bg-white/90 text-blue text-[10px] font-black uppercase">${escapeHTML(prop.type)}</span>
                </div>
                <div class="p-4">
                  <h4 class="text-sm font-black text-navy leading-snug line-clamp-2">${escapeHTML(prop.title)}</h4>
                  <p class="text-[11px] text-slate-500 mt-2">${escapeHTML(prop.province || prop.location || 'España')} · ${formatCurrency(prop.price)}</p>
                  <div class="mt-3 grid grid-cols-1 gap-2">
                    <button id="marketplace-carousel-details-btn-${prop.id}" type="button" onclick="toggleMarketplaceCarouselDetails('${prop.id}')" class="marketplace-carousel-detail-button w-full py-2 rounded-xl border border-slate-200 bg-white text-navy text-xs font-black hover:border-blue hover:text-blue">Ver más detalles</button>
                    <button type="button" onclick="runMarketplacePropertyMatch('${prop.id}')" class="w-full py-2 rounded-xl bg-blue text-white text-xs font-black hover:bg-blue-dark">Match IA</button>
                  </div>
                  <div id="marketplace-carousel-details-${prop.id}" class="marketplace-carousel-detail-panel hidden mt-3 pt-3 border-t border-slate-200 text-[11px] text-slate-600 leading-relaxed space-y-1.5">${buildMarketplaceCarouselDetails(prop)}</div>
                </div>
              </article>`;
            }).join('')}
          </div>
        </section>`;
    }

    function moveMarketplaceCarousel(direction = 1) {
      const list = getMarketplaceVisibleProperties();
      if (!list.length) return;
      marketplaceCarouselOffset = (marketplaceCarouselOffset + direction * MARKETPLACE_CAROUSEL_SIZE + list.length) % list.length;
      renderMarketplaceCarousel(list);
    }

    function renderMarketplace() {
      const grid = document.getElementById('marketplace-grid');
      if (!grid) return;
      grid.innerHTML = '';

      const marketplaceProperties = getMarketplaceVisibleProperties();
      renderMarketplaceDashboard();
      renderMarketplaceCarousel(marketplaceProperties);
      const marketplaceAccordion = document.getElementById('marketplace-accordion-sections');
      if (marketplaceAccordion) marketplaceAccordion.innerHTML = '';
      if (marketplaceMap) renderMarketplaceMapMarkers();

      grid.className = marketplaceLayoutMode === 'list' ? 'grid grid-cols-1 gap-3' : 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6';
      grid.classList.toggle('hidden', marketplaceViewMode === 'map');
      updateMarketplaceViewButtons();

      if (!marketplaceProperties.length) {
        const marketplaceAccordion = document.getElementById('marketplace-accordion-sections');
        if (marketplaceAccordion) marketplaceAccordion.innerHTML = '';
        grid.innerHTML = `<div class="p-8 rounded-2xl bg-white border border-slate-200 text-sm text-slate-500">No hay captaciones disponibles con los filtros aplicados.</div>`;
        return;
      }

      const visibleMarketplaceProperties = marketplaceProperties.slice(0, marketplaceVisibleLimit);

      visibleMarketplaceProperties.forEach(prop => {
        const scoreVisual = getMarketplaceScoreVisual(prop.score || calculatePublicationOpportunityScore(prop, 'property'));
        const marketplaceImage = escapeHTML(resolveMarketplaceImage(prop.image, prop.type));
        const publishedText = formatRelativeTime(prop.date);
        const headerHtml = `
          <div class="aspect-square relative overflow-hidden bg-slate-100">
            <img src="${marketplaceImage}" data-virtual-type="${escapeHTML(prop.type)}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" class="absolute inset-0 w-full h-full object-cover" alt="Imagen de ${escapeHTML(prop.title)}" />
            <div class="absolute inset-0 bg-gradient-to-t from-navy/85 via-navy/15 to-transparent"></div>
            <div class="absolute top-3 left-3 z-20">${favoriteButton('capture', prop.id, 'Guardar captación en favoritos')}</div>
            <div class="absolute top-3 right-3 z-20 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full border text-[10px] font-black shadow-lg ${scoreVisual.classes}" title="${scoreVisual.label}">★ ${scoreVisual.value}/100</div>
            <span class="absolute left-3 bottom-3 z-10 px-2 py-1 rounded-full bg-white/90 text-blue text-[10px] font-bold uppercase">${escapeHTML(prop.type || 'Activo')}</span>
          </div>
        `;

        const listHeaderHtml = `
          <div class="relative h-32 w-full md:h-auto md:w-44 shrink-0 overflow-hidden bg-slate-100 rounded-2xl md:rounded-r-none">
            <img src="${marketplaceImage}" data-virtual-type="${escapeHTML(prop.type)}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" class="absolute inset-0 w-full h-full object-cover" alt="Imagen de ${escapeHTML(prop.title)}" />
            <div class="absolute inset-0 bg-gradient-to-t from-navy/80 via-transparent to-transparent"></div>
            <div class="absolute top-3 left-3 z-20">${favoriteButton('capture', prop.id, 'Guardar captación en favoritos')}</div>
            <div class="absolute top-3 right-3 z-20 inline-flex items-center gap-1 px-2.5 py-1.5 rounded-full border text-[10px] font-black shadow-lg ${scoreVisual.classes}" title="${scoreVisual.label}">★ ${scoreVisual.value}/100</div>
            <span class="absolute left-3 bottom-3 z-10 px-2 py-1 rounded-full bg-white/90 text-blue text-[10px] font-bold uppercase">${escapeHTML(prop.type || 'Activo')}</span>
          </div>
        `;

        const detailsHtml = `
          <div id="details-${prop.id}" class="hidden pt-3 border-t border-slate-100 text-xs text-slate-600 space-y-2">
            <div><strong>Referencia:</strong> ${escapeHTML(prop.reference)}</div>
            <div><strong>Código Postal:</strong> ${escapeHTML(maskPublicPostalCode(prop.postalCode))}</div>
            <div><strong>Características:</strong> ${formatPropertyFeatures(prop)}</div>
            <div><strong>Comentarios técnicos:</strong> ${prop.description}</div>
            <div><strong>Condiciones de Financiación:</strong> ${prop.fundingConditions || "Sujeto a verificación del perfil de riesgo."}</div>
            <div><strong>Nivel de Documentación:</strong> ${prop.docs || "Completo"}</div>
            <div><strong>Urgencia:</strong> ${prop.urgency || "Media"}</div>
            ${renderLinkedNeedsForProperty(prop)}
          </div>
        `;

        const metricsHtml = marketplaceLayoutMode === 'list' ? `
          <div class="grid grid-cols-2 md:grid-cols-4 gap-2 border border-slate-150 rounded-xl bg-slate-50/50 p-2.5 text-left text-xs">
            <div class="px-2 py-1"><strong class="metric-value text-[11px]">${new Intl.NumberFormat('de-DE').format(prop.price)} €</strong><span class="metric-label">Precio</span></div>
            <div class="px-2 py-1"><strong class="metric-value text-[11px]">${escapeHTML(prop.fee)}</strong><span class="metric-label">Honorarios</span></div>
            <div class="px-2 py-1"><strong class="metric-value text-[11px] truncate">${escapeHTML(prop.location)}</strong><span class="metric-label">Zona</span></div>
            <div class="px-2 py-1"><strong class="metric-value text-[11px] truncate">${escapeHTML(maskPublicPostalCode(prop.postalCode))}</strong><span class="metric-label">C.P.</span></div>
          </div>
        ` : `
          <div class="grid grid-cols-4 divide-x divide-slate-200 border border-slate-150 rounded-xl bg-slate-50/50 p-2.5 text-center text-xs">
            <div><strong class="metric-value text-[11px]">${new Intl.NumberFormat('de-DE').format(prop.price)} €</strong><span class="metric-label">Precio</span></div>
            <div><strong class="metric-value text-[11px]">${escapeHTML(prop.fee)}</strong><span class="metric-label">Honorarios</span></div>
            <div><strong class="metric-value text-[11px] truncate">${escapeHTML(prop.location)}</strong><span class="metric-label">Zona</span></div>
            <div><strong class="metric-value text-[11px] truncate">${escapeHTML(maskPublicPostalCode(prop.postalCode))}</strong><span class="metric-label">C.P.</span></div>
          </div>
        `;

        const card = document.createElement('div');
        card.id = `market-card-${prop.id}`;

        if (marketplaceLayoutMode === 'list') {
          card.className = "bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden hover:shadow-md transition-all";
          card.innerHTML = `
            <div class="flex flex-col md:flex-row">
              ${listHeaderHtml}
              <div class="flex-1 p-4 space-y-3">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                  <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-blue">Ref. ${escapeHTML(prop.reference)}</div>
                    <h3 class="text-base font-extrabold text-navy leading-snug mt-1">${escapeHTML(prop.title)}</h3>
                    <p class="text-[10px] text-slate-400 mt-1 font-semibold">${publishedText}</p>
                    <p class="text-xs text-slate-500 mt-2 line-clamp-2">${prop.description}</p>
                  </div>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-light text-green shrink-0 w-fit">Verificada</span>
                </div>
                <div class="flex flex-wrap gap-2 text-[10px] font-bold text-slate-600"><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${prop.bedrooms} hab.</span><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${prop.bathrooms} baños</span><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${prop.surface || 'N/D'} m²</span></div>
                ${metricsHtml}
                ${detailsHtml}
                <div class="flex flex-col sm:flex-row gap-2 justify-end">
                  <button onclick="toggleCardDetails('${prop.id}')" id="toggle-btn-${prop.id}" class="px-4 py-2 text-center text-xs font-bold text-slate-500 hover:text-navy border border-slate-200 rounded-xl transition-all">Ver más detalles ▾</button>
                  <button type="button" onclick="runMarketplacePropertyMatch('${prop.id}')" class="px-4 py-2 rounded-xl bg-navy text-white text-xs font-black">Match IA</button>
                  <button onclick="openAccessModal('${prop.id}')" id="btn-market-${prop.id}" class="px-4 py-2.5 bg-blue hover:bg-blue-dark text-white font-extrabold text-xs rounded-xl shadow-md">Comprar Captación</button>
                </div>
              </div>
            </div>
          `;
        } else {
          card.className = "bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md transition-all";
          card.innerHTML = `
            <div>
              ${headerHtml}
              <div class="p-4 space-y-3">
                <div class="flex items-start justify-between gap-3">
                  <div>
                    <div class="text-[10px] font-black uppercase tracking-wider text-blue">Ref. ${escapeHTML(prop.reference)}</div>
                    <h3 class="text-sm font-extrabold text-navy leading-snug mt-2 line-clamp-2">${escapeHTML(prop.title)}</h3>
                    <p class="text-[10px] text-slate-400 mt-1 font-semibold">${publishedText}</p>
                  </div>
                  <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-light text-green shrink-0">Verificada</span>
                </div>
                <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">${prop.description}</p>
                <div class="flex flex-wrap gap-2 text-[10px] font-bold text-slate-600"><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${prop.bedrooms} hab.</span><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${prop.bathrooms} baños</span><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${prop.surface || 'N/D'} m²</span></div>
                ${metricsHtml}
                ${detailsHtml}
              </div>
            </div>
            <div class="p-4 pt-0 space-y-2">
              <button onclick="toggleCardDetails('${prop.id}')" id="toggle-btn-${prop.id}" class="w-full py-2 text-center text-xs font-bold text-slate-500 hover:text-navy border border-slate-200 rounded-xl transition-all">Ver más detalles ▾</button>
              <button type="button" onclick="runMarketplacePropertyMatch('${prop.id}')" class="w-full py-2 rounded-xl bg-navy text-white text-xs font-black">Match IA</button>
              <button onclick="openAccessModal('${prop.id}')" id="btn-market-${prop.id}" class="w-full py-2.5 bg-blue hover:bg-blue-dark text-white font-extrabold text-xs rounded-xl shadow-md">Comprar Captación</button>
            </div>
          `;
        }
        grid.appendChild(card);
      });

      appendLoadMoreControl(grid, visibleMarketplaceProperties.length, marketplaceProperties.length, 'loadMoreMarketplace', 'captaciones');
    }

    function sortMarketplace() {
      refreshMarketplaceView();
    }

    // ==========================================
    // 7. GESTIÓN DEL ARCHIVO ADJUNTO (Ofrecer Captación)
    // ==========================================
    function setOfferImageMode(mode) {
      const uploadPanel = document.getElementById('offer-image-upload-panel');
      const input = document.getElementById('offer-file-input');
      const statusText = document.getElementById('file-upload-status');
      const previewZone = document.getElementById('file-preview-zone');
      const fileNameSpan = document.getElementById('file-name');
      const fileIconSpan = document.getElementById('file-icon');
      const useDefault = mode === 'default';

      if (uploadPanel) uploadPanel.classList.toggle('opacity-60', useDefault);
      if (uploadPanel) uploadPanel.classList.toggle('cursor-not-allowed', useDefault);
      if (input && useDefault) input.value = '';
      if (useDefault) uploadedFileBase64 = null;

      if (statusText) {
        statusText.classList.remove('hidden');
        statusText.textContent = useDefault
          ? 'Se utilizará la imagen predeterminada optimizada para esta captación.'
          : 'Selecciona JPG, PNG, WEBP o PDF. Las imágenes se convierten a formato web ligero automáticamente.';
      }
      if (previewZone) previewZone.classList.toggle('hidden', !useDefault);
      if (fileIconSpan && useDefault) fileIconSpan.textContent = 'Portada';
      if (fileNameSpan && useDefault) fileNameSpan.textContent = 'Imagen predeterminada Captacion.app';
      refreshOfferDefaultImagePreview();
    }

    function refreshOfferDefaultImagePreview() {
      const wrapper = document.getElementById('offer-default-image-preview');
      const image = document.getElementById('offer-default-image-preview-img');
      const mode = document.querySelector('input[name="offer-image-mode"]:checked')?.value || 'upload';
      const type = document.getElementById('offer-type')?.value || 'Activo inmobiliario';
      if (!wrapper || !image) return;
      const useDefault = mode === 'default';
      wrapper.classList.toggle('hidden', !useDefault);
      if (!useDefault) return;
      image.src = resolveMarketplaceImage('', type);
      image.alt = `Imagen predeterminada para ${type}`;
    }

    function loadImageFromFile(file) {
      return new Promise((resolve, reject) => {
        const image = new Image();
        const objectUrl = URL.createObjectURL(file);
        image.onload = () => {
          URL.revokeObjectURL(objectUrl);
          resolve(image);
        };
        image.onerror = () => {
          URL.revokeObjectURL(objectUrl);
          reject(new Error('No se pudo leer la imagen seleccionada.'));
        };
        image.src = objectUrl;
      });
    }

    async function optimizeMarketplaceImage(file) {
      const image = await loadImageFromFile(file);
      const width = image.naturalWidth || image.width;
      const height = image.naturalHeight || image.height;
      const cropSize = Math.min(width, height);
      if (!cropSize) throw new Error('La imagen seleccionada no tiene dimensiones válidas.');

      const outputSize = Math.min(MAX_MARKETPLACE_IMAGE_SIZE, cropSize);
      const canvas = document.createElement('canvas');
      canvas.width = outputSize;
      canvas.height = outputSize;
      const context = canvas.getContext('2d', { alpha: false });
      if (!context) throw new Error('No se pudo optimizar la imagen en este navegador.');

      const sourceX = (width - cropSize) / 2;
      const sourceY = (height - cropSize) / 2;
      context.drawImage(image, sourceX, sourceY, cropSize, cropSize, 0, 0, outputSize, outputSize);

      let dataUrl = canvas.toDataURL('image/webp', MARKETPLACE_IMAGE_QUALITY);
      if (!dataUrl.startsWith('data:image/webp')) {
        dataUrl = canvas.toDataURL('image/jpeg', MARKETPLACE_IMAGE_QUALITY);
      }
      return dataUrl;
    }

    async function handleFileSelection(e) {
      const file = e.target.files[0];
      const statusText = document.getElementById('file-upload-status');
      const previewZone = document.getElementById('file-preview-zone');
      const fileNameSpan = document.getElementById('file-name');
      const fileIconSpan = document.getElementById('file-icon');
      const uploadRadio = document.getElementById('offer-image-mode-upload');

      if (!file) return;
      if (uploadRadio) uploadRadio.checked = true;
      setOfferImageMode('upload');

      if (fileNameSpan) fileNameSpan.innerText = file.name;
      if (previewZone) previewZone.classList.remove('hidden');
      if (statusText) statusText.classList.remove('hidden');

      if (file.type.startsWith('image/')) {
        if (fileIconSpan) fileIconSpan.innerText = 'Imagen';
        if (statusText) statusText.textContent = 'Optimizando la imagen para web…';
        try {
          uploadedFileBase64 = await optimizeMarketplaceImage(file);
          if (statusText) statusText.textContent = 'Imagen optimizada en formato web y lista para publicar.';
          showToast('Imagen optimizada para web correctamente.', 'success');
        } catch (error) {
          uploadedFileBase64 = null;
          if (statusText) statusText.textContent = 'No se pudo optimizar la imagen. Se utilizará la imagen predeterminada.';
          showToast(error.message + ' Se utilizará la imagen predeterminada.', 'info');
        }
      } else {
        if (fileIconSpan) fileIconSpan.innerText = 'PDF';
        uploadedFileBase64 = null;
        if (statusText) statusText.textContent = 'Documento adjuntado. Como no es una fotografía, se utilizará la imagen predeterminada en Marketplace.';
        showToast('Documento PDF adjuntado. Marketplace utilizará la imagen predeterminada.', 'success');
      }
    }

    // ==========================================
    // 8. CONTROL DE PREVISUALIZACIÓN ANTES DE PUBLICAR
    // ==========================================
    function handleNewOffer(e) {
      e.preventDefault();
      if (!requireRegisteredAction('publicar una captacion')) return;

      const type = normalizePropertyType(document.getElementById('offer-type').value);
      const territory = resolveTerritorySelection(
        document.getElementById('offer-ccaa-sel').value,
        document.getElementById('offer-province-sel').value,
        document.getElementById('offer-municipality-sel').value
      );
      if (!territory.valid) {
        showToast(territory.message, 'info');
        return;
      }
      const ccaa = territory.autonomous_community_name;
      const province = territory.province_name;
      const municipality = territory.municipality_name;
      const locality = document.getElementById('offer-locality-input').value.trim();
      const postalCode = cleanText(document.getElementById('offer-postal-code').value);
      const bedrooms = Number(document.getElementById('offer-bedrooms').value) || 0;
      const bathrooms = Number(document.getElementById('offer-bathrooms').value) || 0;
      const surface = Number(document.getElementById('offer-surface').value) || 0;
      const price = parseFloat(document.getElementById('offer-price').value);
      const fee = cleanText(document.getElementById('offer-fee').value);
      const propertyCondition = cleanText(document.getElementById('offer-condition').value);
      const mandateType = cleanText(document.getElementById('offer-mandate').value);
      const rehab = propertyCondition === 'Reforma integral';
      const exclusive = ['Sí, con exclusividad', 'Encargo de agente único', 'Exclusiva compartida'].includes(mandateType);
      const urgency = cleanText(document.getElementById('offer-urgency').value);
      const docs = cleanText(document.getElementById('offer-docs').value);
      const title = cleanText(document.getElementById('offer-title').value);
      const description = cleanText(document.getElementById('offer-description').value);
      if (title.length < 8) { showToast('El título de la captación debe tener al menos 8 caracteres.', 'info'); return; }
      if (description.length < 30) { showToast('La descripción debe tener al menos 30 caracteres.', 'info'); return; }
      if (!surface || !price || !fee || !propertyCondition || !mandateType || !urgency || !docs) {
        showToast('Completa superficie, precio, comisión, condición, encargo, urgencia y documentación.', 'info');
        return;
      }

      const locationLabel = locality ? `${municipality} (${locality})` : municipality;
      const selectedImageMode = document.querySelector('input[name="offer-image-mode"]:checked')?.value || 'upload';
      const hasCustomImage = selectedImageMode === 'upload' && Boolean(uploadedFileBase64);

      // Creamos la captación temporal para la vista previa, incluyendo la foto adjunta
      tempPropertyToPublish = {
        id: 'user-prop-' + Date.now(),
        title: title || "Sin título definido",
        type: cleanText(type),
        property_type: cleanText(type),
        ccaa: cleanText(ccaa),
        province: cleanText(province),
        municipality: cleanText(municipality),
        autonomous_community_id: territory.autonomous_community_id,
        community_code: territory.autonomous_community_id,
        autonomous_community_name: territory.autonomous_community_name,
        province_id: territory.province_id,
        province_code: territory.province_id,
        province_name: territory.province_name,
        municipality_id: territory.municipality_id,
        municipality_ine_code: territory.municipality_ine_code,
        municipality_code: territory.municipality_ine_code || territory.municipality_id,
        municipality_name: territory.municipality_name,
        locality: cleanText(locality),
        postalCode,
        bedrooms,
        rooms: bedrooms,
        bathrooms,
        surface,
        total_area_m2: surface,
        location: cleanText(province),
        neighborhood: `${cleanText(province)} · ${cleanText(locationLabel)}`,
        date: Date.now(),
        price,
        indicative_price: price,
        fee,
        offered_commission: fee,
        rehab,
        exclusive,
        property_condition: propertyCondition,
        mandate_type: mandateType,
        urgency,
        sale_urgency: urgency,
        docs,
        documentation_level: docs,
        score: calculatePublicationOpportunityScore({
          title,
          description,
          price,
          postalCode,
          province,
          municipality,
          surface,
          bedrooms,
          bathrooms,
          docs,
          exclusive,
          urgency
        }, 'property'),
        description,
        badgeColor: "blue",
        badgeText: exclusive ? "Exclusiva compartida" : "Abierta a colaboración",
        fundingConditions: "Sujeto a viabilidad y estudio de solvencia del perfil inversor.",
        image: hasCustomImage ? uploadedFileBase64 : '', // Solo guardamos la imagen personalizada optimizada; la predeterminada se reutiliza sin duplicar memoria.
        imageIsDefault: !hasCustomImage,
        agency: getDemoSession()?.agency || 'Perfil profesional',
        userEmail: getDemoSession()?.email || CAPTACION_MAILCHIMP?.currentUser?.email || ''
      };

      // Cabecera dinámica que muestra la foto previa si se ha subido
      const previewImage = escapeHTML(resolveMarketplaceImage(tempPropertyToPublish.image, tempPropertyToPublish.type));
      const headerHtml = `
        <div class="aspect-square relative overflow-hidden flex flex-col justify-end p-6 bg-slate-100">
          <img src="${previewImage}" data-virtual-type="${escapeHTML(tempPropertyToPublish.type)}" loading="lazy" decoding="async" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" class="absolute inset-0 w-full h-full object-cover" alt="Imagen de portada" />
          <div class="absolute inset-0 bg-gradient-to-t from-navy/90 via-navy/30 to-transparent"></div>
          <h3 class="text-2xl font-extrabold text-white leading-tight relative z-10">${tempPropertyToPublish.title}</h3>
        </div>
      `;

      // Generar el bloque visual del modal fiel a la foto
      const previewArea = document.getElementById('card-preview-area');
      if (previewArea) {
        previewArea.innerHTML = `
          <div class="bg-white rounded-[24px] border border-slate-200/80 shadow-lg overflow-hidden">
            ${headerHtml}
            <div class="p-6 space-y-4">
              <div class="flex items-center justify-between">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-light text-green">
                  Verificada
                </span>
                <div class="w-10 h-10 rounded-xl bg-navy text-white flex items-center justify-center font-extrabold text-sm shadow-md">
                  ${tempPropertyToPublish.score}
                </div>
              </div>

              <p class="text-xs text-slate-500 leading-relaxed">
                Zona aproximada visible. Datos sensibles de contacto disponibles mediante solicitud válida.
              </p>

              <div class="flex flex-wrap gap-2">
                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-blue-light text-blue">${tempPropertyToPublish.badgeText}</span>
                <span class="px-3 py-1 rounded-full text-[11px] font-bold bg-amber-light text-amber">${urgency === 'Alta' ? 'Alta motivación' : 'Plazo ordinario'}</span>
              </div>
              <div class="flex flex-wrap gap-2 text-[10px] font-bold text-slate-600"><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${bedrooms} hab.</span><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${bathrooms} baños</span><span class="px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200">${surface || 'N/D'} m²</span></div>

              <!-- Tres columnas del hito de referencia -->
              <div class="grid grid-cols-4 divide-x divide-slate-200 border border-slate-150 rounded-xl bg-slate-50/50 p-3 text-center">
                <div>
                  <strong class="block text-sm text-navy font-black">${new Intl.NumberFormat('de-DE').format(price)} €</strong>
                  <span class="metric-label">Precio orientativo</span>
                </div>
                <div>
                  <strong class="block text-sm text-navy font-black">${fee}</strong>
                  <span class="metric-label">Honorarios</span>
                </div>
                <div>
                  <strong class="block text-sm text-navy font-black truncate">${province}</strong>
                  <span class="metric-label">Zona</span>
                </div>
                <div>
                  <strong class="block text-sm text-navy font-black truncate">${escapeHTML(postalCode || 'N/D')}</strong>
                  <span class="metric-label">C.P.</span>
                </div>
              </div>

              <button type="button" class="w-full py-3 bg-blue text-white font-extrabold text-xs rounded-xl shadow-md cursor-not-allowed opacity-85" disabled>
                Solicitar acceso
              </button>
            </div>
          </div>
        `;
      }

      // Desplegar el modal de Previsualización
      const modal = document.getElementById('preview-modal');
      if (modal) modal.classList.remove('hidden');
    }

    function closePreviewModal() {
      const modal = document.getElementById('preview-modal');
      if (modal) modal.classList.add('hidden');
    }

    // --- MEJORA UX: CONFIRMACIÓN Y TRANSICIÓN "ENVIADO" DEL MODAL ---
    function confirmAndPublish() {
      if (!tempPropertyToPublish) return;

      const publishBtn = document.getElementById('preview-publish-btn');
      const backBtn = document.getElementById('preview-back-btn');

      // 1. Mostrar estado intermedio de "Enviado" en el modal
      if (publishBtn) {
        publishBtn.innerHTML = `<span>✓</span> ¡Enviado!`;
        publishBtn.className = "px-5 py-2.5 rounded-xl bg-green text-white text-xs font-black shadow-md flex items-center gap-1.5 cursor-not-allowed";
        publishBtn.disabled = true;
      }
      if (backBtn) {
        backBtn.disabled = true;
        backBtn.className = "px-4 py-2.5 rounded-xl border border-slate-100 text-slate-300 text-xs font-bold cursor-not-allowed";
      }

      // 2. Simular un retardo breve para asentar la operación y dar feedback visual premium
      setTimeout(() => {
        // Se actualiza la UI al instante y se sincroniza con WordPress cuando hay sesion real.
        const publishedProperty = tempPropertyToPublish;
        properties.unshift(publishedProperty);
        syncMailchimpSession('ofrecer-captacion', 'ofrecer-captacion');
        localStorage.setItem('captacion_properties_v3', JSON.stringify(properties));
        persistWpRecord('property', publishedProperty, { recordKey: publishedProperty.id, title: publishedProperty.title, status: 'publicada' })
          .then(ok => { if (!ok && canUseWordPressRecords()) showToast('La captación queda visible localmente, pero no se pudo sincronizar con WordPress.', 'info'); });
        syncAlertsForProperty(publishedProperty);

        // Actualizar listados
        renderMarketplace();
        renderDashboard();
        renderHome();

        // Resetear formulario y estado de carga de archivo
        const form = document.querySelector('#page-ofrecer-captacion form');
        if (form) form.reset();

        const statusText = document.getElementById('file-upload-status');
        const previewZone = document.getElementById('file-preview-zone');
        if (statusText) statusText.classList.remove('hidden');
        if (previewZone) previewZone.classList.add('hidden');
        
        uploadedFileBase64 = null; // Reiniciar variable de imagen
        const uploadRadio = document.getElementById('offer-image-mode-upload');
        if (uploadRadio) uploadRadio.checked = true;
        setOfferImageMode('upload');

        // Cerrar el modal de previsualización
        closePreviewModal();

        // Restaurar estado de los botones para futuras operaciones
        if (publishBtn) {
          publishBtn.innerHTML = `Aprobar y publicar`;
          publishBtn.className = "px-5 py-2.5 rounded-xl bg-blue text-white text-xs font-black hover:bg-blue-dark shadow-md flex items-center gap-1.5";
          publishBtn.disabled = false;
        }
        if (backBtn) {
          backBtn.disabled = false;
          backBtn.className = "px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-xs font-bold hover:bg-slate-50";
        }

        showToast("¡La captación se ha publicado con éxito en el Marketplace! 🚀", "success");
        
        // Redirigir al Marketplace automáticamente
        openPostPublishCompatibilityReport('property', publishedProperty);
        window.location.hash = '#/marketplace';
      }, 1200);
    }

    // ==========================================
    // 9. CONTROL DE EVENTOS Y ACCIONES COMUNES
    // ==========================================
    function scrollToCoverageMap(event) {
      event?.preventDefault?.();
      const section = document.getElementById('mapa-cobertura');
      if (!section) {
        console.warn('[Captacion.app] No se encontro la seccion #mapa-cobertura.');
        return;
      }
      section.scrollIntoView({behavior:'smooth',block:'start'});
      setTimeout(() => homeMap?.invalidateSize?.(), 350);
    }

    function updateContactPhoneRequirement() {
      const preference = document.getElementById('contact-preference')?.value || 'email';
      const phone = document.getElementById('contact-phone');
      const help = document.getElementById('contact-phone-help');
      const required = preference === 'call' || preference === 'whatsapp';
      if (phone) phone.required = required;
      if (help) help.textContent = required ? 'El telefono es obligatorio para esta preferencia.' : 'Opcional para contacto por email.';
    }

    async function handleContactSubmit(e) {
      e.preventDefault();
      const contactName = cleanText(document.getElementById('contact-name')?.value || '');
      const contactEmail = cleanText(document.getElementById('contact-email')?.value || '').toLowerCase();
      const contactPhone = cleanText(document.getElementById('contact-phone')?.value || '');
      const preference = document.getElementById('contact-preference')?.value || 'email';
      const message = cleanText(document.getElementById('contact-message')?.value || '');
      const privacyAccepted = Boolean(document.getElementById('contact-privacy-consent')?.checked);
      const commercialConsent = Boolean(document.getElementById('contact-marketing-consent')?.checked);
      const errorBox = document.getElementById('contact-form-error');
      const fail = text => { if(errorBox){errorBox.textContent=text;errorBox.classList.remove('hidden');} };
      if (!contactName || !/^\S+@\S+\.\S+$/.test(contactEmail) || !message) return fail('Completa nombre, correo y mensaje.');
      if (!privacyAccepted) return fail('Debes aceptar la politica de privacidad.');
      if ((preference === 'call' || preference === 'whatsapp') && !/^\+?[0-9][0-9\s().-]{7,19}$/.test(contactPhone)) return fail('Indica un telefono valido para llamada o WhatsApp.');
      if (errorBox) errorBox.classList.add('hidden');
      try {
        const response = await fetch(CAPTACION_MAILCHIMP.contactEndpoint, {
          method:'POST', credentials:'same-origin',
          headers:{'Content-Type':'application/json'},
          body:JSON.stringify({name:contactName,email:contactEmail,phone:contactPhone,preference,message,privacyAccepted})
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data?.message || 'No se pudo enviar la consulta.');
        syncMailchimpContact({ email: contactEmail, name: contactName, phone:contactPhone, source: 'contacto', tags: ['contacto'], message:`Preferencia: ${preference}. ${message}`, commercialConsent });
        showToast(data?.message || 'Mensaje enviado. Nos pondremos en contacto contigo en breve.', data?.ok ? 'success' : 'info');
        e.target.reset(); updateContactPhoneRequirement();
      } catch (error) {
        fail(error.message || 'No se pudo enviar la consulta. Intentalo de nuevo.');
      }
    }

    function captacionOpenCookiePreferences() {
      if (typeof window.cmplz_open_preferences === 'function') {
        window.cmplz_open_preferences();
        return;
      }
      if (typeof window.cmplz_show_banner === 'function') {
        window.cmplz_show_banner();
        return;
      }
      document.dispatchEvent(new Event('cmplz_open_preferences'));
    }

    // Alias de compatibilidad: Complianz es la única fuente de consentimiento.
    function openCookieSettings() {
      captacionOpenCookiePreferences();
    }

    function captacionIsComplianzVisible() {
      const banner = document.querySelector('.cmplz-cookiebanner, #cmplz-cookiebanner-container');
      if (!banner) return false;
      const style = window.getComputedStyle(banner);
      return style.display !== 'none' && style.visibility !== 'hidden' && banner.getAttribute('aria-hidden') !== 'true';
    }

    function removeLegacyCookiePreferences() {
      try {
        localStorage.removeItem('captacion_cookie_preferences_v1');
        localStorage.removeItem('captacion_cookies_v3_accepted');
      } catch (error) {}
    }

    function openReportModal(reference = '') {
      if (!requireRegisteredAction('abrir un reporte')) return;
      const session = getDemoSession?.() || {};
      const input = document.getElementById('report-content-reference');
      if (input && /^https?:\/\//i.test(reference)) input.value = cleanText(reference);
      const name = document.getElementById('report-name'); if (name) name.value = session.name || CAPTACION_MAILCHIMP?.currentUser?.name || '';
      const email = document.getElementById('report-email'); if (email) email.value = session.email || CAPTACION_MAILCHIMP?.currentUser?.email || '';
      const phone = document.getElementById('report-phone'); if (phone) phone.value = session.whatsapp || CAPTACION_MAILCHIMP?.currentUser?.phone || '';
      document.getElementById('content-report-modal')?.classList.remove('hidden');
    }

    function closeReportModal() {
      document.getElementById('content-report-modal')?.classList.add('hidden');
    }

    async function submitContentReport(event) {
      event.preventDefault();
      try {
        const response = await fetch(CAPTACION_MAILCHIMP.reportEndpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':CAPTACION_MAILCHIMP.nonce},body:JSON.stringify({name:document.getElementById('report-name')?.value,email:document.getElementById('report-email')?.value,phone:document.getElementById('report-phone')?.value,url:document.getElementById('report-content-reference')?.value,comment:document.getElementById('report-content-description')?.value,website:document.getElementById('report-website')?.value})});
        const data = await response.json(); if(!response.ok||!data?.ok) throw new Error(data?.message||'No se pudo enviar el reporte.');
        event.target.reset(); closeReportModal(); showToast(data.message,'success');
      } catch(error) { showToast(error.message||'No se pudo enviar el reporte.','info'); }
    }

    function subscribeToast(planName) {
      showToast(`¡Solicitud enviada! Has elegido activar el ${planName}.`, "info");
    }

    function downloadResource(name) {
      if (!requireRegisteredAction('acceder a recursos')) return;
      showToast("Descarga de documento: " + name + " completada con éxito.", "success");
    }

    function prepareLegalSignature(type = 'nda') {
      const normalizedType = type === 'collaboration' ? 'collaboration' : 'nda';
      const modal = document.getElementById('legal-signature-modal');
      const title = document.getElementById('legal-signature-title');
      const typeInput = document.getElementById('legal-document-type');
      const result = document.getElementById('legal-signature-result');
      if (typeInput) typeInput.value = normalizedType;
      if (title) title.textContent = normalizedType === 'nda' ? 'Preparar Acuerdo de Confidencialidad (NDA) para firma electrónica' : 'Preparar acuerdo de colaboración para firma electrónica';
      result?.classList.add('hidden');
      if (result) result.innerHTML = '';
      modal?.classList.remove('hidden');
    }

    function closeLegalSignatureModal() {
      document.getElementById('legal-signature-modal')?.classList.add('hidden');
    }

    function scheduleAgreementCalendarPlan(type = 'nda', reference = '', postalCode = '') {
      const label = type === 'nda' ? 'Acuerdo de Confidencialidad (NDA)' : 'acuerdo de colaboración';
      const refText = reference || 'sin referencia';
      addPrivateTask({ title:`Revisar ${label}`, detail:`Validar el documento ${label} asociado a ${refText}.`, priority:'high', due:'Hoy', dueAt:Date.now()+3600000*3, target:'operations', dedupeKey:`agreement-review-${type}-${refText}` });
      addPrivateTask({ title:'Solicitar firma a las partes', detail:`Coordina la firma del expediente ${refText}${postalCode ? ` · C.P. ${postalCode}` : ''}.`, priority:'high', due:'Mañana', dueAt:Date.now()+86400000, target:'communications', dedupeKey:`agreement-sign-${type}-${refText}` });
      addPrivateTask({ title:'Confirmar próxima acción operativa', detail:`Registra el siguiente paso comercial tras generar el ${label}.`, priority:'medium', due:'Esta semana', dueAt:Date.now()+86400000*3, target:'operations', dedupeKey:`agreement-followup-${type}-${refText}` });
      addPrivateNotification({ category:'Operaciones', title:'Agenda creada tras generar acuerdo', detail:`Se ha creado una agenda operativa para ${label}${reference ? ` · ${reference}` : ''}.`, target:'tasks', dueAt:Date.now()+3600000*2, dedupeKey:`agreement-notif-${type}-${refText}` });
      exportPrivateAgendaCalendar();
      renderDashboard();
      showToast('Tareas creadas y agenda exportada al calendario.', 'success');
    }

    function generateLegalSignatureLink(event) {
      event.preventDefault();
      const type = document.getElementById('legal-document-type')?.value || 'nda';
      const reference = cleanText(document.getElementById('legal-operation-reference')?.value || '');
      const postalCode = cleanText(document.getElementById('legal-postal-code')?.value || '');
      const token = `${type}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
      const demoLink = `${window.location.origin}${window.location.pathname}#/firma/${encodeURIComponent(token)}`;
      const result = document.getElementById('legal-signature-result');
      if (result) {
        result.innerHTML = `<strong class="text-green">Enlace preparado</strong><br><span class="break-all">${escapeHTML(demoLink)}</span><br><span class="block mt-2 text-[10px]">Documento: ${type === 'nda' ? 'Acuerdo de Confidencialidad (NDA)' : 'Acuerdo de colaboración'} · Ref. ${escapeHTML(reference)}${postalCode ? ` · C.P. ${escapeHTML(postalCode)}` : ''}. En producción este enlace deberá generarse en servidor, registrar auditoría y conectarse con un prestador de firma electrónica.</span>`;
        result.classList.remove('hidden');
      }
      showToast('Enlace seguro preparado.', 'success');
    }

    function generateLegalSignatureLink(event) {
      event.preventDefault();
      const type = document.getElementById('legal-document-type')?.value || 'nda';
      const reference = cleanText(document.getElementById('legal-operation-reference')?.value || '');
      const postalCode = cleanText(document.getElementById('legal-postal-code')?.value || '');
      const token = `${type}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
      const demoLink = `${window.location.origin}${window.location.pathname}#/firma/${encodeURIComponent(token)}`;
      const result = document.getElementById('legal-signature-result');
      if (result) {
        result.innerHTML = `<strong class="text-green">Enlace preparado</strong><br><span class="break-all">${escapeHTML(demoLink)}</span><br><span class="block mt-2 text-[10px]">Documento: ${type === 'nda' ? 'Acuerdo de Confidencialidad (NDA)' : 'Acuerdo de colaboración'} · Ref. ${escapeHTML(reference)}${postalCode ? ` · C.P. ${escapeHTML(postalCode)}` : ''}. En producción este enlace deberá generarse en servidor, registrar auditoría y conectarse con un prestador de firma electrónica.</span><div class="mt-3 flex flex-wrap gap-2"><button type="button" onclick="scheduleAgreementCalendarPlan('${type}','${escapeHTML(reference)}','${escapeHTML(postalCode)}')" class="px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Agendar tareas pendientes</button><button type="button" onclick="switchPrivateDashboardPanel('tasks')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-blue">Ver agenda</button></div>`;
        result.classList.remove('hidden');
      }
      addPrivateNotification({ category:'Operaciones', title:'Documento preparado para firma', detail:`Se ha generado un enlace seguro para ${type === 'nda' ? 'Acuerdo de Confidencialidad (NDA)' : 'acuerdo de colaboración'}${reference ? ` · ${reference}` : ''}.`, target:'operations', dueAt:Date.now()+3600000*4, dedupeKey:`legal-link-${type}-${reference || token}` });
      showToast('Enlace seguro preparado.', 'success');
    }

    function scheduleAgreementCalendarPlan(type = 'nda', reference = '', postalCode = '') {
      const label = type === 'nda' ? 'Acuerdo de Confidencialidad (NDA)' : 'acuerdo de colaboración';
      const refText = reference || 'sin referencia';
      addPrivateTask({ title:`Revisar ${label}`, detail:`Validar el documento ${label} asociado a ${refText}.`, priority:'high', due:'Hoy', dueAt:Date.now() + 3600000 * 3, target:'operations', dedupeKey:`agreement-review-${type}-${refText}` });
      addPrivateTask({ title:'Solicitar firma a las partes', detail:`Coordina la firma del expediente ${refText}${postalCode ? ` · C.P. ${postalCode}` : ''}.`, priority:'high', due:'Manana', dueAt:Date.now() + 86400000, target:'communications', dedupeKey:`agreement-sign-${type}-${refText}` });
      addPrivateTask({ title:'Confirmar proxima accion operativa', detail:`Registra el siguiente paso comercial tras generar el ${label}.`, priority:'medium', due:'Esta semana', dueAt:Date.now() + 86400000 * 3, target:'operations', dedupeKey:`agreement-followup-${type}-${refText}` });
      addPrivateNotification({ category:'Operaciones', title:'Agenda creada tras generar acuerdo', detail:`Se ha creado una agenda operativa para ${label}${reference ? ` · ${reference}` : ''}.`, target:'tasks', dueAt:Date.now() + 3600000 * 2, dedupeKey:`agreement-notif-${type}-${refText}` });
      exportPrivateAgendaCalendar();
      renderDashboard();
      showToast('Tareas creadas y agenda exportada al calendario.', 'success');
    }

    function generateLegalSignatureLink(event) {
      event.preventDefault();
      const type = document.getElementById('legal-document-type')?.value || 'nda';
      const reference = cleanText(document.getElementById('legal-operation-reference')?.value || '');
      const postalCode = cleanText(document.getElementById('legal-postal-code')?.value || '');
      const token = `${type}-${Date.now().toString(36)}-${Math.random().toString(36).slice(2, 8)}`;
      const demoLink = `${window.location.origin}${window.location.pathname}#/firma/${encodeURIComponent(token)}`;
      const result = document.getElementById('legal-signature-result');
      if (result) {
        result.innerHTML = `<strong class="text-green">Enlace preparado</strong><br><span class="break-all">${escapeHTML(demoLink)}</span><br><span class="block mt-2 text-[10px]">Documento: ${type === 'nda' ? 'Acuerdo de Confidencialidad (NDA)' : 'Acuerdo de colaboración'} · Ref. ${escapeHTML(reference)}${postalCode ? ` · C.P. ${escapeHTML(postalCode)}` : ''}. En producción este enlace deberá generarse en servidor, registrar auditoría y conectarse con un prestador de firma electrónica.</span><div class="mt-3 flex flex-wrap gap-2"><button type="button" onclick="scheduleAgreementCalendarPlan('${type}','${escapeHTML(reference)}','${escapeHTML(postalCode)}')" class="px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Agendar tareas pendientes</button><button type="button" onclick="switchPrivateDashboardPanel('tasks')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-blue">Ver agenda</button></div>`;
        result.classList.remove('hidden');
      }
      addPrivateNotification({ category:'Operaciones', title:'Documento preparado para firma', detail:`Se ha generado un enlace seguro para ${type === 'nda' ? 'Acuerdo de Confidencialidad (NDA)' : 'acuerdo de colaboración'}${reference ? ` · ${reference}` : ''}.`, target:'operations', dueAt:Date.now() + 3600000 * 4, dedupeKey:`legal-link-${type}-${reference || token}` });
      showToast('Enlace seguro preparado.', 'success');
    }

    // --- NOTIFICACIONES INTERNAS TOAST ---
    function showToast(message, type = "success") {
      const container = document.getElementById('toast-container');
      if (!container) return;
      const toast = document.createElement('div');
      
      let bg = "bg-white border-slate-200 text-slate-800";
      let icon = "•";

      if (type === "success") {
        bg = "bg-emerald-50 border-emerald-200 text-emerald-900";
        icon = "✓";
      } else if (type === "info") {
        bg = "bg-blue-light border-blue/20 text-blue-dark";
        icon = "ℹ";
      }

      toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl border shadow-lg ${bg} transition-all duration-300 transform translate-y-2 opacity-0 pointer-events-auto`;
      toast.innerHTML = `
        <span class="font-black text-sm flex items-center justify-center w-5 h-5 rounded-full bg-white/40">${icon}</span>
        <span class="text-xs font-semibold">${message}</span>
      `;

      container.appendChild(toast);

      setTimeout(() => {
        toast.classList.remove('translate-y-2', 'opacity-0');
      }, 50);

      setTimeout(() => {
        toast.classList.add('translate-y-2', 'opacity-0');
        setTimeout(() => {
          toast.remove();
        }, 300);
      }, 4000);
    }

    // ==========================================
    // 10. CONSULTAS CON IA DEL USUARIO
    // ==========================================
    async function callUserAI(taskType, prompt, systemInstruction = "", context = {}) {
      if (!getAIClientConfig().isLoggedIn) throw new Error('Debes iniciar sesión en WordPress para usar tu conexión IA.');
      await loadAIConnection();
      if (!hasConnectedAI()) throw new Error('AI_NOT_CONNECTED');
      const response = await captacionAIRequest('request', 'POST', {
        task_type: taskType,
        prompt,
        system_instruction: systemInstruction,
        context,
        temperature: 0.3,
        max_tokens: 700
      });
      return response?.text || '';
    }


    function buildLocalPropertyCopy({ type, province, municipality, locality, postalCode, price, fee, rehab, exclusive, urgency }) {
      const title = `${type} con potencial comercial en ${municipality || province}`;
      const area = [municipality, locality].filter(Boolean).join(' · ');
      const description = `Oportunidad inmobiliaria orientada a colaboración profesional en ${area || province}${postalCode ? ' (C.P. ' + postalCode + ')' : ''}. Precio de salida aproximado: ${formatCurrency(price)}. ${rehab === 'yes' ? 'El activo admite una estrategia de reforma y reposicionamiento comercial.' : 'El inmueble puede comercializarse sin una reforma integral previa.'} ${exclusive === 'yes' ? 'Existe exclusiva compartida para ordenar el proceso de venta.' : 'La colaboración deberá formalizarse mediante un acuerdo específico antes de revelar información sensible.'} Honorarios para el colaborador: ${fee || 'a consultar'}. Urgencia declarada: ${urgency}.`;
      return { title, description };
    }

    function buildMatchNotificationNotice(kind = 'need') {
      const subject = kind === 'property' ? 'esta captación' : 'esta demanda';
      const target = kind === 'property' ? 'una demanda compatible' : 'una captación compatible';
      return `<div class="mt-4 p-4 rounded-2xl border border-blue/20 bg-blue-light/35 text-xs text-slate-600 leading-relaxed"><strong class="block text-navy mb-1">Alerta activada en tu panel privado</strong>Si mas adelante aparece ${target} para ${subject}, recibiras un aviso en tu Panel Privado, dentro de la seccion <strong>Notificaciones</strong>, para que puedas revisarlo y actuar desde alli.</div>`;
    }

    function buildNeedCompatibilityReport(need) {
      const matches = getCompatiblePropertiesForNeed(need, 5);
      if (!matches.length) {
        return `<h3 class="text-lg font-black text-blue mt-4 mb-2">Resultado del cruce local</h3><p>No se ha identificado una coincidencia directa con el mismo tipo y territorio, superficie y estancias mínimas, precio dentro del presupuesto y condiciones profesionales aceptadas.</p><h4 class="text-base font-extrabold text-navy mt-3 mb-1">Propuesta comercial</h4><p>Mantener la demanda activa para nuevas captaciones que sí cumplan esos parámetros.</p>${buildMatchNotificationNotice('need')}`;
      }
      return `<h3 class="text-lg font-black text-blue mt-4 mb-2">Coincidencias disponibles</h3><p>Se han detectado ${matches.length} captación${matches.length === 1 ? '' : 'es'} compatible${matches.length === 1 ? '' : 's'} con esta demanda. Puedes revisar la ficha y avanzar con la accion correspondiente.</p><div class="mt-4 space-y-3">${matches.map(({ property, score }) => `<article class="p-4 rounded-2xl border border-slate-200 bg-slate-50"><div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3"><div class="min-w-0"><span class="block text-[10px] font-black text-blue">Ref. ${escapeHTML(property.reference || property.id)}</span><strong class="block text-sm text-navy mt-1">${escapeHTML(property.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">${escapeHTML(property.province || property.location || 'N/D')} ? ${formatCurrency(property.price)} ? ${formatPropertyFeatures(property, true)}</span></div><span class="shrink-0 inline-flex px-3 py-1 rounded-full border text-[10px] font-black ${getCompatibilityBadgeClasses(score)}">${score}% match</span></div><div class="mt-3 flex flex-wrap gap-2"><button onclick="openMapPropertyCard('${property.id}')" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-bold">Ver propiedad</button><button onclick="openAccessModal('${property.id}')" class="px-3 py-2 rounded-lg border border-slate-200 text-navy text-[10px] font-bold">Solicitar acceso</button></div></article>`).join('')}</div>${buildMatchNotificationNotice('need')}`;
    }

    function buildPropertyCompatibilityReport(property) {
      const matches = getCompatibleNeedsForProperty(property, 5);
      if (!matches.length) {
        return `<h3 class="text-lg font-black text-blue mt-4 mb-2">Resultado del cruce local</h3><p>No se ha identificado una demanda activa con el mismo tipo y territorio, superficie y estancias mínimas, presupuesto suficiente y condiciones profesionales compatibles.</p><h4 class="text-base font-extrabold text-navy mt-3 mb-1">Propuesta comercial</h4><p>Mantener la captación activa en Marketplace para que pueda enlazarse cuando aparezca una demanda compatible.</p>${buildMatchNotificationNotice('property')}`;
      }
      return `<h3 class="text-lg font-black text-blue mt-4 mb-2">Demandas compatibles disponibles</h3><p>Se han detectado ${matches.length} demanda${matches.length === 1 ? '' : 's'} compatible${matches.length === 1 ? '' : 's'} con esta captación. Puedes revisar la demanda y actuar desde el panel.</p><div class="mt-4 space-y-3">${matches.map(({ need, score }) => `<article class="p-4 rounded-2xl border border-slate-200 bg-slate-50"><div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3"><div class="min-w-0"><span class="block text-[10px] font-black text-green">Intención de búsqueda</span><strong class="block text-sm text-navy mt-1">${escapeHTML(need.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">${escapeHTML(need.province || need.location || 'N/D')} ? Hasta ${formatCurrency(need.budget)} ? ${formatPropertyFeatures(need, true)}</span></div><span class="shrink-0 inline-flex px-3 py-1 rounded-full border text-[10px] font-black ${getCompatibilityBadgeClasses(score)}">${score}% match</span></div><div class="mt-3 flex flex-wrap gap-2"><button onclick="openMapNeedCard('${need.id}')" class="px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Ver demanda</button><button onclick="switchPrivateDashboardPanel('demands'); window.location.hash = '#/area-privada';" class="px-3 py-2 rounded-lg border border-slate-200 text-navy text-[10px] font-bold">Ir al panel</button></div></article>`).join('')}</div>${buildMatchNotificationNotice('property')}`;
    }

    function openPostPublishCompatibilityReport(kind, record) {
      const modal = document.getElementById('ai-match-modal');
      const loader = document.getElementById('ai-loading');
      const report = document.getElementById('ai-report');
      const reportContent = document.getElementById('ai-report-content');
      if (!modal || !reportContent) return;
      modal.classList.remove('hidden');
      if (loader) loader.classList.add('hidden');
      if (report) report.classList.remove('hidden');
      reportContent.innerHTML = kind === 'property' ? buildPropertyCompatibilityReport(record) : buildNeedCompatibilityReport(record);
    }

    function buildLocalMatchReport(need) {
      return buildNeedCompatibilityReport(need);
    }

    async function runAIMatchmaker(needId) {
      if (!requireRegisteredAction('usar el match inteligente')) return;
      const need = needs.find(n => n.id === needId);
      if (!need) return;

      const modal = document.getElementById('ai-match-modal');
      const loader = document.getElementById('ai-loading');
      const report = document.getElementById('ai-report');
      const reportContent = document.getElementById('ai-report-content');

      if (!modal) return;
      modal.classList.remove('hidden');
      if (loader) loader.classList.remove('hidden');
      if (report) report.classList.add('hidden');

      const systemPrompt = "Eres un consultor analítico de Real Estate y PropTech. Eres experto en operaciones cruzadas de co-exclusivas y acuerdos comerciales B2B.";
      const prompt = `Analiza la siguiente demanda de búsqueda (Comprador Activo):
      - Título: ${need.title}
      - Tipo: ${need.type}
      - Presupuesto máximo: ${need.budget} €
      - CCAA / Provincia / Municipio / Código Postal: ${need.ccaa} / ${need.province} / ${need.municipality} / ${need.postalCode || 'N/D'}
      - Tipo Comprador: ${need.buyerType}
      - Urgencia: ${need.urgency}
      - Estado Financiero: ${need.funding}
      - Reparto de Honorarios: ${need.feeSplit}
      - Requisitos descritos: ${need.description}

      Y contrástalo frente a nuestra cartera de Captaciones Disponibles del Marketplace:
      ${JSON.stringify(properties)}

      INSTRUCCIONES DE RESPUESTA (Responde en español con formato Markdown elegante):
      1. Evalúa si hay algún inmueble en la cartera que encaje (Match) con el comprador. Si hay coincidencias parciales de zona o precio, indícalas.
      2. Calcula el porcentaje estimado de éxito de la operación.
      3. Sugiere las condiciones del pacto de comisión co-exclusiva (ej: si es 50/50 o cómo repartir el esfuerzo si una agencia aporta el activo y otra el comprador).
      4. Redacta una carta de invitación B2B formal y sumamente profesional para proponer la co-exclusiva entre ambas agencias.`;

      try {
        const result = await callUserAI('need_matching', prompt, systemPrompt, {
          flow: 'buscar_captaciones',
          need,
          properties
        });
        let htmlContent = result
          .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
          .replace(/\*(.*?)\*/g, '<em>$1</em>')
          .replace(/### (.*?)\n/g, '<h4 class="text-base font-extrabold text-navy mt-3 mb-1">$1</h4>')
          .replace(/## (.*?)\n/g, '<h3 class="text-lg font-black text-blue mt-4 mb-2">$1</h3>')
          .replace(/# (.*?)\n/g, '<h2 class="text-xl font-black text-navy mt-5 mb-3">$1</h2>')
          .replace(/\n/g, '<br>');

        if (reportContent) reportContent.innerHTML = htmlContent;
        if (loader) loader.classList.add('hidden');
        if (report) report.classList.remove('hidden');
      } catch (err) {
        if (err.message === 'AI_NOT_CONNECTED') {
          if (reportContent) reportContent.innerHTML = buildLocalMatchReport(need);
        } else if (reportContent) {
          reportContent.innerHTML = `<div class="text-red-600 font-bold p-4 bg-red-50 rounded-xl">${escapeHTML(err.message)}</div>`;
        }
        if (loader) loader.classList.add('hidden');
        if (report) report.classList.remove('hidden');
      }
    }

    function closeAiMatchModal() {
      const modal = document.getElementById('ai-match-modal');
      if (modal) modal.classList.add('hidden');
    }

    // --- COPIAR REPORTE AL PORTAPAPELES (Respaldo por execCommand para iframes) ---
    function copyAiReport() {
      const content = document.getElementById('ai-report-content');
      if (!content) return;
      const text = content.innerText;
      const tempInput = document.createElement("textarea");
      tempInput.value = text;
      document.body.appendChild(tempInput);
      tempInput.select();
      document.execCommand("copy");
      document.body.removeChild(tempInput);
      showToast("¡Informe copiado al portapapeles! 📋", "success");
    }

    // ==========================================
    // 11. EVENTOS GENERALES & UTILITIES
    // ==========================================
    function renderDashboard() {
      const tbody = document.getElementById('dash-table-body');
      if (!tbody) return;
      const activeCount = document.getElementById('dash-active-count');
      const totalFees = document.getElementById('dash-total-fees');
      const closedCount = document.getElementById('dash-closed-count');

      tbody.innerHTML = '';
      if (activeCount) activeCount.innerText = properties.length;
      if (closedCount) closedCount.innerText = closedOperations.length;
      
      let calculatedVolume = 0;
      properties.forEach(p => {
        const percentageValue = parseFloat(p.fee) || 3.5;
        calculatedVolume += p.price * (percentageValue / 100);
      });

      if (totalFees) totalFees.innerText = new Intl.NumberFormat('de-DE').format(Math.round(calculatedVolume)) + " €";

      properties.forEach((prop) => {
        const tr = document.createElement('tr');
        tr.className = "border-b border-slate-100 hover:bg-slate-50 transition-colors";
        tr.innerHTML = `
          <td class="py-4 px-6 font-extrabold text-navy truncate max-w-xs">${prop.title}<span class="block text-[10px] text-slate-400 mt-0.5 font-semibold">${formatPropertyFeatures(prop, true)}</span></td>
          <td class="py-4 px-6">${prop.location}<span class="block text-[10px] text-slate-400 mt-0.5">C.P. ${escapeHTML(prop.postalCode || 'N/D')}</span></td>
          <td class="py-4 px-6 font-semibold">${new Intl.NumberFormat('de-DE').format(prop.price)} €</td>
          <td class="py-4 px-6 text-blue font-bold">${prop.fee}</td>
          <td class="py-4 px-6 text-center">
            <div class="flex items-center justify-center gap-2">
              <button onclick="closeListing('${prop.id}')" class="px-2.5 py-1.5 rounded-lg bg-green-light text-green hover:bg-emerald-100 font-bold transition-all">Cerrar</button>
              <button onclick="deleteListing('${prop.id}')" class="px-2.5 py-1.5 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-bold transition-all">Baja</button>
            </div>
          </td>
        `;
        tbody.appendChild(tr);
      });
    }

    function closeListing(id) {
      const property = properties.find(p => p.id === id);
      if (!property) return;
      closedOperations.unshift({ id: `closed-${Date.now()}`, title: property.title, zone: property.province || property.location, price: property.price, date: Date.now() });
      properties = properties.filter(p => p.id !== id);
      persistDemoState();
      renderMarketplace();
      renderDashboard();
      renderHome();
      showToast("Operación marcada como cerrada y retirada del inventario activo.", "success");
    }

    function deleteListing(id) {
      properties = properties.filter(p => p.id !== id);
      persistDemoState();
      renderMarketplace();
      renderDashboard();
      renderHome();
      showToast("La captación se ha dado de baja del sistema.", "info");
    }

    function calculateSplit() {
      const priceInput = document.getElementById('calc-price');
      const pctInput = document.getElementById('calc-pct');
      const splitInput = document.getElementById('calc-split');

      if (!priceInput || !pctInput || !splitInput) return;

      const price = parseFloat(priceInput.value) || 0;
      const pct = parseFloat(pctInput.value) || 0;
      const split = parseFloat(splitInput.value) || 0;

      const totalCommission = price * (pct / 100);
      const yourShare = totalCommission * (split / 100);

      const totalAmountEl = document.getElementById('calc-total-amount');
      const yourShareEl = document.getElementById('calc-your-share');

      if (totalAmountEl) totalAmountEl.innerText = new Intl.NumberFormat('de-DE').format(totalCommission) + " €";
      if (yourShareEl) yourShareEl.innerText = new Intl.NumberFormat('de-DE').format(yourShare) + " €";
    }

    function getPrivateXmlFeeds() {
      try {
        return JSON.parse(localStorage.getItem('captacion_private_xml_feeds_v2')) || [];
      } catch (error) {
        return [];
      }
    }

    function setPrivateXmlFeeds(feeds) {
      localStorage.setItem('captacion_private_xml_feeds_v2', JSON.stringify(feeds));
    }

    function createXmlFeedId(xmlUrl) {
      let hash = 0;
      for (let index = 0; index < xmlUrl.length; index++) {
        hash = ((hash << 5) - hash) + xmlUrl.charCodeAt(index);
        hash |= 0;
      }
      return `xml-feed-${Math.abs(hash)}`;
    }

    function getXmlNodeText(node, tagNames = []) {
      for (const tagName of tagNames) {
        const element = node.querySelector(tagName);
        const value = element?.textContent?.trim();
        if (value) return cleanText(value);
      }
      return '';
    }

    function getXmlNodeAttribute(node, attributeNames = []) {
      for (const attributeName of attributeNames) {
        const value = node.getAttribute(attributeName);
        if (value) return cleanText(value);
      }
      return '';
    }

    function sanitizeXmlPublicText(value = '') {
      return cleanText(value)
        .replace(/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/gi, '[dato de contacto protegido]')
        .replace(/(?:https?:\/\/|www\.)\S+/gi, '[enlace protegido]')
        .replace(/(?:\+?34[\s.-]?)?(?:[6789]\d{2}|9\d{2})[\s.-]?\d{3}[\s.-]?\d{3}/g, '[teléfono protegido]');
    }

    function parseXmlProperties(xmlText, xmlUrl) {
      const parser = new DOMParser();
      const xmlDocument = parser.parseFromString(xmlText, 'application/xml');
      if (xmlDocument.querySelector('parsererror')) {
        throw new Error('El fichero recibido no contiene un XML válido.');
      }

      const feedId = createXmlFeedId(xmlUrl);
      const candidateSelectors = ['property', 'inmueble', 'listing', 'anuncio', 'oferta', 'item'];
      let nodes = [];
      for (const selector of candidateSelectors) {
        const matches = Array.from(xmlDocument.querySelectorAll(selector));
        if (matches.length) {
          nodes = matches;
          break;
        }
      }
      if (!nodes.length) {
        throw new Error('No se han encontrado propiedades en el fichero XML.');
      }

      return nodes.map((node, index) => {
        const reference = getXmlNodeText(node, ['reference', 'referencia', 'ref', 'id']) || getXmlNodeAttribute(node, ['id', 'ref']) || String(index + 1);
        const province = getXmlNodeText(node, ['province', 'provincia']) || 'España';
        const municipality = getXmlNodeText(node, ['municipality', 'municipio', 'city', 'localidad']) || province;
        const locality = getXmlNodeText(node, ['neighborhood', 'barrio', 'zona']);
        const postalCode = getXmlNodeText(node, ['postal_code', 'postalCode', 'codigo_postal', 'codigopostal', 'cp', 'zip', 'zipcode']);
        const bedrooms = Number(getXmlNodeText(node, ['bedrooms', 'habitaciones', 'dormitorios', 'rooms'])) || 0;
        const bathrooms = Number(getXmlNodeText(node, ['bathrooms', 'banos', 'baños', 'aseos'])) || 0;
        const surface = Number(String(getXmlNodeText(node, ['surface', 'surface_m2', 'superficie', 'metros', 'm2'])).replace(/[^0-9.,-]/g, '').replace(',', '.')) || 0;
        const description = sanitizeXmlPublicText(getXmlNodeText(node, ['description', 'descripcion', 'observations', 'observaciones']));
        const title = sanitizeXmlPublicText(getXmlNodeText(node, ['title', 'titulo', 'name', 'nombre'])) || `Propiedad importada en ${municipality}`;
        const type = getXmlNodeText(node, ['type', 'tipo', 'property_type', 'tipo_inmueble']) || 'Activo inmobiliario';
        const rawPrice = getXmlNodeText(node, ['price', 'precio', 'importe']).replace(/[^0-9.,-]/g, '').replace(/\./g, '').replace(',', '.');
        const image = getXmlNodeText(node, ['image', 'imagen', 'photo', 'foto', 'picture']);
        const fee = getXmlNodeText(node, ['fee', 'comision', 'honorarios']) || 'A consultar';
        return normalizePropertyRecord({
          id: `xml-${feedId}-${reference}`,
          title,
          type,
          location: province,
          province,
          municipality,
          locality,
          postalCode,
          bedrooms,
          bathrooms,
          surface,
          neighborhood: `${municipality}${locality ? ' · ' + locality : ''}`,
          price: Number(rawPrice) || 0,
          fee,
          score: 80,
          rehab: false,
          exclusive: false,
          urgency: 'Media',
          docs: 'Pendiente de validación documental',
          description: description || 'Propiedad importada mediante fichero XML. La información privada permanece protegida.',
          badgeColor: 'blue',
          badgeText: 'Importada desde XML',
          fundingConditions: 'Condiciones disponibles mediante solicitud validada.',
          image,
          date: Date.now() - index,
          xmlFeedId: feedId,
          xmlSourceUrl: xmlUrl,
          xmlReference: reference
        }, index);
      });
    }

    function saveImportedXmlPropertiesToMarketplace(importedProperties, feedId) {
      let storedProperties = [];
      try {
        storedProperties = JSON.parse(localStorage.getItem('captacion_properties_v3')) || [];
      } catch (error) {
        storedProperties = [...properties];
      }

      const existingProperties = storedProperties
        .map((property, index) => normalizePropertyRecord(property, index))
        .filter(property => property.xmlFeedId !== feedId);

      const marketplaceProperties = importedProperties.map((property, index) => normalizePropertyRecord({
        ...property,
        status: 'active',
        marketplaceVisible: true,
        importedFromXml: true
      }, index));

      properties = [...marketplaceProperties, ...existingProperties];
      localStorage.setItem('captacion_properties_v3', JSON.stringify(properties));
      return marketplaceProperties.length;
    }

    function deletePrivateXmlFeed(feedId) {
      const feeds = getPrivateXmlFeeds();
      const feed = feeds.find(item => item.id === feedId);
      if (!feed) {
        showToast('La fuente XML seleccionada ya no existe.', 'info');
        return;
      }

      const propertiesToRemove = properties.filter(property => property.xmlFeedId === feedId);
      const confirmed = window.confirm(`¿Eliminar esta fuente XML y retirar sus ${propertiesToRemove.length} propiedades del Marketplace?`);
      if (!confirmed) return;

      properties = properties.filter(property => property.xmlFeedId !== feedId);
      persistDemoState();

      const remainingFeeds = feeds.filter(item => item.id !== feedId);
      setPrivateXmlFeeds(remainingFeeds);

      const savedUrl = localStorage.getItem('captacion_private_xml_url_v1') || '';
      if (savedUrl === feed.url) {
        const nextUrl = remainingFeeds[0]?.url || '';
        if (nextUrl) localStorage.setItem('captacion_private_xml_url_v1', nextUrl);
        else localStorage.removeItem('captacion_private_xml_url_v1');
        const input = document.getElementById('private-xml-url');
        if (input) input.value = nextUrl;
      }

      renderPrivateXmlFeeds();
      renderMarketplace();
      renderDashboard();
      renderHome();
      showToast(`XML eliminado correctamente: ${propertiesToRemove.length} propiedades retiradas del Marketplace.`, 'info');
    }

    function renderPrivateXmlFeeds() {
      const container = document.getElementById('private-xml-feeds-list');
      if (!container) return;
      const feeds = getPrivateXmlFeeds();
      if (!feeds.length) {
        container.innerHTML = '<p class="text-xs text-slate-400">Todavía no se ha creado ninguna fuente XML.</p>';
        return;
      }
      container.innerHTML = feeds.map(feed => `
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200">
          <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span class="inline-flex px-2 py-1 rounded-full bg-green-light text-green text-[10px] font-black uppercase">XML creado</span>
              <span class="text-[10px] text-slate-400">${new Date(feed.updatedAt).toLocaleString('es-ES')}</span>
            </div>
            <p class="text-xs font-bold text-navy mt-2 truncate" title="${escapeHTML(feed.url)}">${escapeHTML(feed.url)}</p>
          </div>
          <div class="shrink-0 flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
            <div class="px-3 py-2 rounded-xl bg-white border border-slate-200 text-center">
              <strong class="block text-xl font-black text-blue">${feed.propertyCount}</strong>
              <span class="block text-[9px] uppercase tracking-wider font-black text-slate-400">Propiedades subidas</span>
            </div>
            <button type="button" onclick="deletePrivateXmlFeed('${feed.id}')" class="px-3 py-2 rounded-xl border border-red-200 bg-red-50 text-red-600 hover:bg-red-100 text-[10px] font-black uppercase tracking-wider transition-all">
              Eliminar
            </button>
          </div>
        </div>
      `).join('');
    }

    // Proxy XML del mismo dominio. Debe subirse junto a este HTML como xml-proxy.php.
    // También puede sobrescribirse antes de cargar la app:
    // window.CAPTACION_XML_PROXY_URL = '/ruta-personalizada/xml-proxy.php?url={url}';
    function buildXmlProxyUrl(proxyTemplate, xmlUrl) {
      const encodedUrl = encodeURIComponent(xmlUrl);
      if (proxyTemplate.includes('{url}')) return proxyTemplate.replace('{url}', encodedUrl);
      const separator = proxyTemplate.includes('?') ? '&' : '?';
      return `${proxyTemplate}${separator}url=${encodedUrl}`;
    }

    async function fetchXmlResponseText(requestUrl, timeoutMs = 18000) {
      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), timeoutMs);
      try {
        const response = await fetch(requestUrl, { cache: 'no-store', signal: controller.signal });
        if (!response.ok) {
          const detail = await response.text().catch(() => '');
          throw new Error(`HTTP ${response.status}${detail ? ': ' + detail.slice(0, 180) : ''}`);
        }
        const xmlText = await response.text();
        if (!xmlText.trim()) throw new Error('El servidor ha devuelto una respuesta vacía.');
        return xmlText;
      } finally {
        clearTimeout(timeoutId);
      }
    }

    async function fetchXmlTextWithFallback(xmlUrl) {
      const customProxy = String(window.CAPTACION_XML_PROXY_URL || '').trim();
      const sameOriginProxy = customProxy || <?php echo wp_json_encode($captacion_theme_uri . '/xml-proxy.php?url={url}'); ?>;
      const attempts = [
        { mode: 'server-proxy', label: 'proxy XML del servidor', url: buildXmlProxyUrl(sameOriginProxy, xmlUrl) },
        { mode: 'direct', label: 'descarga directa', url: xmlUrl },
        // Respaldos solo para pruebas estáticas. En producción debe funcionar el proxy propio anterior.
        { mode: 'demo-proxy-corsproxy', label: 'proxy público de demostración 1', url: `https://corsproxy.io/?url=${encodeURIComponent(xmlUrl)}` },
        { mode: 'demo-proxy-allorigins', label: 'proxy público de demostración 2', url: `https://api.allorigins.win/raw?url=${encodeURIComponent(xmlUrl)}` }
      ];

      let lastError = null;
      const errors = [];
      for (const attempt of attempts) {
        try {
          const xmlText = await fetchXmlResponseText(attempt.url);
          return { xmlText, fetchMode: attempt.mode, fetchLabel: attempt.label };
        } catch (error) {
          lastError = error;
          errors.push(`${attempt.label}: ${error.message || error.name}`);
          console.warn(`Falló la ${attempt.label} del XML.`, error);
        }
      }

      const technicalReason = lastError?.name === 'AbortError'
        ? 'La descarga superó el tiempo máximo de espera.'
        : 'La URL puede ser pública, pero el navegador no puede leerla por CORS. Sube xml-proxy.php al mismo directorio que este HTML y comprueba que PHP esté activo en el hosting.';
      console.warn('Intentos de descarga XML fallidos:', errors);
      throw new Error(`No se pudo descargar el XML. ${technicalReason}`);
    }

    async function savePrivateXmlUrl() {
      const input = document.getElementById('private-xml-url');
      const button = document.getElementById('private-xml-save-btn');
      if (!input) return;
      const xmlUrl = input.value.trim();
      if (!xmlUrl) {
        showToast('Introduce la URL del fichero XML.', 'info');
        return;
      }
      try {
        const parsedUrl = new URL(xmlUrl);
        if (!['http:', 'https:'].includes(parsedUrl.protocol)) throw new Error('Protocolo no permitido');
      } catch (error) {
        showToast('Introduce una URL pública válida que empiece por http:// o https://.', 'info');
        return;
      }

      const originalButtonText = button?.textContent;
      if (button) {
        button.disabled = true;
        button.textContent = 'Importando XML...';
      }

      try {
        const response = await fetch(window.CAPTACION_API.endpoints.importXmlUrl, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': window.CAPTACION_API.nonce },
          body: JSON.stringify({ url: xmlUrl })
        });
        const data = await response.json();
        if (!response.ok || !data.ok) throw new Error(data.message || 'No se pudo importar el XML desde backend.');
        localStorage.setItem('captacion_private_xml_url_v1', xmlUrl);
        await loadImportBatches();
        await loadWordPressRealEstateRecords();
        showToast(`XML importado correctamente: ${data.imported} propiedades guardadas.`, 'success');
      } catch (error) {
        showToast(error.message || 'No se pudo importar el fichero XML desde el servidor.', 'info');
      } finally {
        if (button) {
          button.disabled = false;
          button.textContent = originalButtonText || 'Guardar e importar XML';
        }
      }
    }

    function loadPrivateXmlUrl() {
      const input = document.getElementById('private-xml-url');
      if (!input) return;
      input.value = localStorage.getItem('captacion_private_xml_url_v1') || '';
      renderPrivateXmlFeeds();
    }


    // ==========================================
    // 12. CAJA DE HERRAMIENTAS INMOBILIARIAS B2B
    // ==========================================
    const resourceCatalog = [
      { id:'sale-readiness', category:'captacion', icon:'✅', title:'Informe de preparación para la venta', description:'Evalúa documentación, cargas, certificado energético, fotografías, precio, ocupación e incidencias.', time:'5 min', result:'Puntúa 0–100', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'marketing-authorisation', category:'captacion', icon:'', title:'Autorización de comercialización', description:'Prepara una autorización de publicación o un mandato de colaboración B2B.', time:'5 min', result:'Genera documento', access:'profesional', revision:'Pendiente revisión jurídica', status:'roadmap' },
      { id:'capture-call-script', category:'captacion', icon:'☎', title:'Guion inteligente para captación', description:'Sugiere preguntas y argumentos según el perfil del propietario y la situación del inmueble.', time:'2 min', result:'Genera guion', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'owner-feedback', category:'captacion', icon:'', title:'Informe de feedback para propietarios', description:'Convierte comentarios de visitas en objeciones repetidas y acciones recomendadas.', time:'5 min', result:'Genera informe', access:'registro', revision:'Diseño funcional', status:'roadmap' },

      { id:'max-budget', category:'compraventa', icon:'', title:'Presupuesto máximo de compra', description:'Calcula capacidad económica a partir de ahorros, ingresos, financiación y gastos estimados.', time:'3 min', result:'Calcula presupuesto', access:'publico', revision:'Diseño funcional', status:'roadmap' },
      { id:'mortgage-scenarios', category:'compraventa', icon:'⌂', title:'Calculadora hipotecaria con escenarios', description:'Compara cuota ordinaria y escenario prudente si cambian las condiciones financieras.', time:'3 min', result:'Compara cuotas', access:'publico', revision:'Demo jun. 2026', status:'demo' },
      { id:'purchase-costs', category:'compraventa', icon:'🧾', title:'Gastos de compra por comunidad autónoma', description:'Diferencia vivienda nueva, usada, residencia habitual e inversión con parámetros actualizables.', time:'4 min', result:'Calcula gastos', access:'publico', revision:'Revisión normativa necesaria', status:'roadmap' },
      { id:'buyer-qualification', category:'compraventa', icon:'🎯', title:'Cualificación del comprador', description:'Recoge financiación, zona, plazo y criterios esenciales para asignar prioridad comercial.', time:'4 min', result:'Calcula prioridad', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'visit-sheet', category:'compraventa', icon:'📋', title:'Hoja de visita transparente', description:'Prepara una hoja con identificación, fecha, agente interviniente, protección de datos y honorarios.', time:'4 min', result:'Genera PDF', access:'registro', revision:'Pendiente revisión jurídica', status:'roadmap' },
      { id:'property-comparison', category:'compraventa', icon:'⚖', title:'Comparador de inmuebles', description:'Crea una tabla visual con precio, metros, ubicación, gastos, estado y ventajas.', time:'5 min', result:'Crea comparativa', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'purchase-offer', category:'compraventa', icon:'📨', title:'Propuesta de compra ordenada', description:'Genera una oferta con importe, vigencia, financiación, condiciones y observaciones.', time:'5 min', result:'Genera propuesta', access:'profesional', revision:'Pendiente revisión jurídica', status:'roadmap' },

      { id:'rental-type', category:'alquileres', icon:'🔑', title:'Identificador del tipo de alquiler', description:'Diferencia vivienda habitual, temporal, turístico, habitación y uso distinto de vivienda.', time:'3 min', result:'Crea orientación', access:'publico', revision:'Revisión normativa necesaria', status:'roadmap' },
      { id:'rental-ad-check', category:'alquileres', icon:'', title:'Verificador básico de anuncio inmobiliario', description:'Comprueba si el texto de venta o alquiler incorpora datos esenciales como precio, ubicación aproximada, superficie y certificado energético.', time:'3 min', result:'Crea revisión', access:'publico', revision:'Demo jun. 2026', status:'demo', priority:7 },
      { id:'rent-update', category:'alquileres', icon:'📆', title:'Actualización de renta', description:'Calcula una revisión orientativa y prepara una carta de comunicación al inquilino.', time:'3 min', result:'Calcula y redacta', access:'publico', revision:'Revisión normativa necesaria', status:'roadmap' },
      { id:'deposit-calculator', category:'alquileres', icon:'🛡', title:'Fianza y garantías adicionales', description:'Distingue supuestos y organiza la documentación para gestionar garantías.', time:'3 min', result:'Calcula garantía', access:'publico', revision:'Revisión normativa necesaria', status:'roadmap' },
      { id:'rental-profitability', category:'alquileres', icon:'📈', title:'Rentabilidad neta del alquiler', description:'Incluye IBI, comunidad, seguro, mantenimiento, vacíos, gestión y reformas estimadas.', time:'3 min', result:'Calcula rentabilidad', access:'publico', revision:'Demo jun. 2026', status:'demo', priority:9 },
      { id:'sell-or-rent', category:'alquileres', icon:'🔄', title:'Comparador vender o alquilar', description:'Muestra liquidez inmediata frente a ingresos periódicos, gastos y horizonte temporal.', time:'5 min', result:'Compara escenarios', access:'registro', revision:'Diseño funcional', status:'roadmap', priority:9 },
      { id:'rental-calendar', category:'alquileres', icon:'', title:'Calendario de vencimientos y avisos', description:'Organiza fin de contrato, renta, seguros, depósitos, certificados y comunicaciones.', time:'5 min', result:'Crea calendario', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'short-stay-checklist', category:'alquileres', icon:'🧳', title:'Checklist de corta duración', description:'Organiza advertencias y documentación antes de anunciar un alquiler de corta duración.', time:'4 min', result:'Crea checklist', access:'registro', revision:'Revisión normativa necesaria', status:'roadmap' },

      { id:'pbc-kyc', category:'legal', icon:'🪪', title:'Asistente PBC/KYC', description:'Guía la identificación, titular real, representación, actividad y documentación del cliente.', time:'6 min', result:'Crea expediente', access:'profesional', revision:'Revisión jurídica necesaria', status:'roadmap', priority:8 },
      { id:'funds-origin', category:'legal', icon:'💳', title:'Checklist de origen de fondos', description:'Organiza aportaciones familiares, transferencias internacionales, sociedades y pagos complejos.', time:'5 min', result:'Crea checklist', access:'profesional', revision:'Revisión jurídica necesaria', status:'roadmap' },
      { id:'pbc-file', category:'legal', icon:'', title:'Portada de expediente PBC', description:'Resume tareas realizadas, documentos pendientes, fechas y responsable interno.', time:'4 min', result:'Genera portada', access:'profesional', revision:'Revisión jurídica necesaria', status:'roadmap' },
      { id:'rgpd-pack', category:'legal', icon:'🔒', title:'Pack RGPD inmobiliario', description:'Agrupa cláusulas para formularios, visitas, compradores, propietarios y colaboradores.', time:'8 min', result:'Descarga pack', access:'profesional', revision:'Revisión jurídica necesaria', status:'roadmap' },
      { id:'energy-certificate', category:'legal', icon:'⚡', title:'Verificador del certificado energético', description:'Registra calificación, vigencia y alertas si falta información en el anuncio.', time:'3 min', result:'Crea alerta', access:'registro', revision:'Revisión normativa necesaria', status:'roadmap' },
      { id:'cadastral-reference', category:'legal', icon:'', title:'Asistente de valor de referencia catastral', description:'Organiza el dato oficial y prepara una nota informativa comprensible para el cliente.', time:'4 min', result:'Crea nota', access:'registro', revision:'Revisión normativa necesaria', status:'roadmap' },
      { id:'simple-note-reader', category:'legal', icon:'📑', title:'Lector asistido de nota simple', description:'Resume titulares, hipotecas, embargos, usufructos y cargas para revisión profesional.', time:'4 min', result:'Resume documento', access:'profesional', revision:'Revisión jurídica necesaria', status:'roadmap' },
      { id:'operation-checklist', category:'legal', icon:'☑', title:'Checklist por tipo de operación', description:'Genera pasos para herencia, hipoteca, inmueble alquilado, VPO, solar, local o sociedad.', time:'4 min', result:'Crea checklist', access:'registro', revision:'Revisión normativa necesaria', status:'roadmap' },
      { id:'keys-delivery', category:'legal', icon:'', title:'Acta de entrega de llaves', description:'Incluye estado, suministros, lecturas, llaves y observaciones.', time:'4 min', result:'Genera acta', access:'registro', revision:'Pendiente revisión jurídica', status:'roadmap' },
      { id:'photo-inventory', category:'legal', icon:'📷', title:'Inventario fotográfico para alquiler', description:'Organiza estancias, mobiliario y desperfectos con aceptación de las partes.', time:'8 min', result:'Crea inventario', access:'registro', revision:'Diseño funcional', status:'roadmap' },

      { id:'fee-split', category:'colaboración', icon:'🧮', title:'Reparto avanzado de honorarios', description:'Calcula comisión, IVA configurable y reparto entre intervinientes.', time:'2 min', result:'Calcula importes', access:'publico', revision:'Demo jun. 2026', status:'demo', priority:5 },
      { id:'collaboration-generator', category:'colaboración', icon:'', title:'Generador de acuerdo de colaboración', description:'Recoge inmueble, captador, agente comprador, porcentajes, hitos y condiciones de cobro.', time:'6 min', result:'Genera acuerdo', access:'profesional', revision:'Pendiente revisión jurídica', status:'legal', priority:6 },
      { id:'lead-referral', category:'colaboración', icon:'🔗', title:'Acta de derivación de contacto', description:'Registra cuándo se entrega un propietario y bajo qué condiciones se remunera.', time:'4 min', result:'Genera acta', access:'profesional', revision:'Pendiente revisión jurídica', status:'roadmap' },
      { id:'owner-sharing', category:'colaboración', icon:'📤', title:'Autorización para compartir oportunidad', description:'Documenta el consentimiento para publicar información mínima no sensible.', time:'4 min', result:'Genera autorización', access:'profesional', revision:'Pendiente revisión jurídica', status:'roadmap' },
      { id:'private-operation-room', category:'colaboración', icon:'🚪', title:'Sala privada de operación', description:'Reúne participantes, Acuerdo de Confidencialidad (NDA), documentos, tareas, ofertas y actividad reciente.', time:'Continuo', result:'Gestiona operación', access:'profesional', revision:'Roadmap', status:'roadmap', priority:10 },
      { id:'interaction-log', category:'colaboración', icon:'🕒', title:'Registro cronológico de interacciones', description:'Deja trazabilidad de presentación, visitas y transmisión de ofertas.', time:'Continuo', result:'Registra actividad', access:'profesional', revision:'Roadmap', status:'roadmap' },
      { id:'internal-liquidation', category:'colaboración', icon:'🧾', title:'Factura o liquidación interna', description:'Prepara la liquidación del reparto pactado con conceptos configurables.', time:'4 min', result:'Genera liquidación', access:'profesional', revision:'Pendiente revisión fiscal', status:'roadmap' },
      { id:'reputation', category:'colaboración', icon:'', title:'Reputación del colaborador', description:'Muestra identidad verificada, operaciones, respuesta, documentación y valoraciones.', time:'Continuo', result:'Consulta perfil', access:'profesional', revision:'Roadmap', status:'roadmap' },
      { id:'incidents', category:'colaboración', icon:'⚠', title:'Incidencias y mediación', description:'Registra discrepancias sobre honorarios, duplicidad de contactos o incumplimientos.', time:'5 min', result:'Abre incidencia', access:'profesional', revision:'Roadmap', status:'roadmap' },
      { id:'what-if', category:'colaboración', icon:'', title:'Asistente “¿qué ocurre si…?', description:'Organiza situaciones frecuentes: retirada, venta directa, expiración o financiación fallida.', time:'3 min', result:'Consulta escenarios', access:'profesional', revision:'Pendiente revisión jurídica', status:'roadmap' },

      { id:'portal-ads', category:'marketing', icon:'📣', title:'Generador de anuncios por portal', description:'Crea versiones para web, portales, redes sociales y WhatsApp con revisión previa.', time:'4 min', result:'Genera textos', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'photo-checklist', category:'marketing', icon:'📸', title:'Checklist fotográfico por inmueble', description:'Indica fotografías, orden recomendado y errores a evitar según el activo.', time:'3 min', result:'Crea checklist', access:'publico', revision:'Diseño funcional', status:'roadmap' },
      { id:'home-staging', category:'marketing', icon:'🛋', title:'Plan básico de home staging', description:'Propone mejoras de bajo coste antes de fotografiar o enseñar el inmueble.', time:'4 min', result:'Crea plan', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'follow-up-pack', category:'marketing', icon:'💬', title:'Mensajes de seguimiento', description:'Prepara textos para propietarios, compradores y colaboradores con tareas pendientes.', time:'3 min', result:'Genera mensajes', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'weekly-owner-report', category:'marketing', icon:'📬', title:'Informe semanal para propietarios', description:'Resume visitas, consultas, origen de leads, comentarios y recomendaciones.', time:'5 min', result:'Genera informe', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'daily-agenda', category:'marketing', icon:'📅', title:'Agenda diaria de oportunidades', description:'Ordena leads, documentación incompleta, visitas y operaciones próximas al cierre.', time:'Continuo', result:'Prioriza tareas', access:'registro', revision:'Roadmap', status:'roadmap' },
      { id:'reviews-request', category:'marketing', icon:'🌟', title:'Solicitador de reseñas', description:'Prepara mensajes posteriores a la operación y registra solicitudes enviadas.', time:'2 min', result:'Genera mensaje', access:'registro', revision:'Diseño funcional', status:'roadmap' },
      { id:'objections-library', category:'marketing', icon:'🧠', title:'Biblioteca de objeciones comerciales', description:'Ayuda a responder a objeciones sobre exclusiva, precio y honorarios.', time:'2 min', result:'Consulta respuestas', access:'publico', revision:'Diseño funcional', status:'roadmap' }
    ];

    let activeResourceCategory = 'captacion';
    const accessLabels = { publico:'Público', registro:'Registro gratuito', profesional:'Profesional verificado' };

    function getResourcesForActiveCategory() {
      return resourceCatalog.filter(item => item.category === 'captacion');
    }

    function updateResourceCategoryVisibility() {
      const scopedResources = getResourcesForActiveCategory();
      const total = document.getElementById('resource-stat-total');
      const demos = document.getElementById('resource-stat-demo');
      if (total) total.textContent = scopedResources.length;
      if (demos) demos.textContent = scopedResources.filter(item => item.status === 'demo').length;

      const legalSection = document.getElementById('resources-legal-documents');
      const showLegalSection = false;
      legalSection?.classList.toggle('hidden', !showLegalSection);
      document.querySelectorAll('[data-legal-resource-category]').forEach(card => {
        const cardCategory = card.dataset.legalResourceCategory;
        const showCard = activeResourceCategory === 'all' || cardCategory === activeResourceCategory;
        card.classList.toggle('hidden', !showCard);
      });
    }

    function initResourcesToolbox() {
      renderDownloadableResources();
      updateResourceCategoryVisibility();
      renderResourceFeatured();
      renderResourceCatalog();
    }

    function renderDownloadableResources() {
      const container = document.getElementById('professional-downloadable-resources');
      if (!container) return;
      const resources = CAPTACION_MAILCHIMP?.resources || [];
      const plan = CAPTACION_MAILCHIMP?.accessState?.plan_type || 'basic';
      const canCreate = ['professional_plus','premium'].includes(plan);
      const verified = Boolean(CAPTACION_MAILCHIMP?.loggedIn && CAPTACION_MAILCHIMP?.emailVerified);
      container.innerHTML = resources.map(item => {
        const download = !verified
          ? CAPTACION_MAILCHIMP?.loggedIn
            ? `<button type="button" onclick="showToast('Confirma tu correo electronico para acceder a los recursos.', 'info')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-black text-blue">Verificar correo</button>`
            : `<button type="button" onclick="openProfessionalAccess()" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-black text-blue">Iniciar sesión</button>`
          : item.has_static_pdf
            ? `<a href="${escapeHTML(item.pdf_url)}" target="_blank" rel="noopener noreferrer" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-black text-blue">Descargar PDF</a>`
            : `<button type="button" disabled title="TODO: publicar PDF estándar" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-black text-slate-400 cursor-not-allowed">PDF pendiente</button>`;
        const create = verified && canCreate
          ? `<a href="${escapeHTML(item.create_url)}" class="px-3 py-2 rounded-lg bg-navy text-[10px] font-black text-white">Crear PDF</a>`
          : '';
        return `<article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm flex flex-col justify-between"><div><div class="flex items-start justify-between gap-3"><span class="text-2xl">&#128196;</span><span class="px-2 py-1 rounded-full bg-blue-light text-blue text-[9px] font-black uppercase">${canCreate ? 'PDF + personalización' : 'PDF estándar'}</span></div><h3 class="text-sm font-black text-navy mt-4">${escapeHTML(item.title)}</h3><p class="text-[11px] text-slate-500 leading-relaxed mt-2">${escapeHTML(item.description)}</p></div><div class="flex flex-wrap gap-2 mt-5">${download}${create}</div></article>`;
      }).join('');
    }

    function setResourceCategory(category) {
      activeResourceCategory = 'captacion';
      document.querySelectorAll('.resource-category-btn').forEach(button => {
        const isActive = button.dataset.resourceCategory === activeResourceCategory;
        button.classList.toggle('bg-navy', isActive);
        button.classList.toggle('text-white', isActive);
        button.classList.toggle('border', !isActive);
        button.classList.toggle('border-slate-200', !isActive);
        button.classList.toggle('text-slate-600', !isActive);
      });
      updateResourceCategoryVisibility();
      renderResourceFeatured();
      renderResourceCatalog();
    }

    function resourceActionLabel(item) {
      if (item.access === 'profesional' && !hasProfessionalMembershipAccess()) return 'Activar Professional';
      if (item.status === 'demo') return 'Abrir demo';
      if (item.status === 'legal') return 'Ver plantilla';
      return 'Ver alcance';
    }

    function resourceStatusBadge(item) {
      if (item.access === 'profesional' && !hasProfessionalMembershipAccess()) return '<span class="px-2 py-1 rounded-full bg-amber-light text-amber text-[9px] font-black uppercase">Professional</span>';
      if (item.status === 'demo') return '<span class="px-2 py-1 rounded-full bg-green-light text-green text-[9px] font-black uppercase">Demo operativa</span>';
      if (item.status === 'legal') return '<span class="px-2 py-1 rounded-full bg-blue-light text-blue text-[9px] font-black uppercase">Plantilla disponible</span>';
      return '<span class="px-2 py-1 rounded-full bg-slate-100 text-slate-500 text-[9px] font-black uppercase">Roadmap</span>';
    }

    function renderResourceFeatured() {
      const section = document.getElementById('resources-featured-section');
      const container = document.getElementById('resource-featured-grid');
      if (!container) return;
      const featured = getResourcesForActiveCategory()
        .filter(item => item.priority)
        .sort((a, b) => a.priority - b.priority)
        .slice(0, 8);
      section?.classList.toggle('hidden', featured.length === 0);
      container.innerHTML = featured.map(item => `
        <article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
          <div><div class="flex items-start justify-between gap-3"><span class="text-2xl">${item.icon}</span><span class="flex h-7 w-7 items-center justify-center rounded-full bg-navy text-white text-[10px] font-black">${item.priority}</span></div><h4 class="text-sm font-black text-navy mt-4 leading-snug">${escapeHTML(item.title)}</h4><p class="text-[11px] text-slate-500 leading-relaxed mt-2">${escapeHTML(item.description)}</p></div>
          <button onclick="openResourceTool('${item.id}')" class="mt-4 w-full py-2 rounded-lg bg-blue text-white text-[10px] font-black hover:bg-blue-dark">${resourceActionLabel(item)}</button>
        </article>`).join('');
    }

    function renderResourceCatalog() {
      const container = document.getElementById('resources-catalog-grid');
      if (!container) return;
      const search = cleanText(document.getElementById('resource-search')?.value || '').toLowerCase();
      const access = document.getElementById('resource-access-filter')?.value || 'all';
      const filtered = resourceCatalog.filter(item => {
        const matchCategory = item.category === 'captacion';
        const matchAccess = access === 'all' || item.access === access;
        const content = `${item.title} ${item.description} ${item.result}`.toLowerCase();
        return matchCategory && matchAccess && (!search || content.includes(search));
      });
      const count = document.getElementById('resource-count');
      if (count) count.textContent = `${filtered.length} recurso${filtered.length === 1 ? '' : 's'}`;
      container.innerHTML = filtered.length ? filtered.map(item => `
        <article class="p-5 rounded-2xl bg-white border border-slate-200 shadow-sm hover:border-blue/40 hover:shadow-md transition-all flex flex-col justify-between">
          <div><div class="flex items-start justify-between gap-3"><span class="text-2xl">${item.icon}</span>${resourceStatusBadge(item)}</div><h4 class="text-sm font-black text-navy mt-4 leading-snug">${escapeHTML(item.title)}</h4><p class="text-[11px] text-slate-500 leading-relaxed mt-2">${escapeHTML(item.description)}</p><div class="grid grid-cols-2 gap-2 mt-4 text-[10px]"><span class="px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-100 text-slate-500"><b class="text-navy">Tiempo:</b> ${escapeHTML(item.time)}</span><span class="px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-100 text-slate-500"><b class="text-navy">Resultado:</b> ${escapeHTML(item.result)}</span><span class="col-span-2 px-2 py-1.5 rounded-lg bg-slate-50 border border-slate-100 text-slate-500"><b class="text-navy">Acceso:</b> ${accessLabels[item.access]} · <b class="text-navy">Revisión:</b> ${escapeHTML(item.revision)}</span></div></div>
          <button onclick="openResourceTool('${item.id}')" class="mt-4 w-full py-2.5 rounded-xl ${item.status === 'demo' ? 'bg-blue text-white hover:bg-blue-dark' : 'border border-slate-200 text-navy hover:bg-slate-50'} text-[10px] font-black transition-all">${resourceActionLabel(item)}</button>
        </article>`).join('') : '<div class="md:col-span-2 xl:col-span-3 p-8 rounded-2xl bg-white border border-slate-200 text-center text-sm text-slate-500">No hay herramientas que coincidan con los filtros seleccionados.</div>';
    }

    function closeResourceToolModal() { document.getElementById('resource-tool-modal')?.classList.add('hidden'); }

    function openResourceTool(id) {
      if (!requireRegisteredAction('usar herramientas profesionales')) return;
      const item = resourceCatalog.find(resource => resource.id === id);
      if (!item) return;
      if (item.access === 'profesional' && !hasProfessionalMembershipAccess()) {
        requireProfessionalMembership(item.title);
        return;
      }
      if (item.status === 'legal') {
        document.getElementById('resources-legal-documents')?.scrollIntoView({ behavior:'smooth', block:'start' });
        showToast('Consulta las plantillas legales y prepara la firma cuando exista una colaboración confirmada.', 'info');
        return;
      }
      const modal = document.getElementById('resource-tool-modal');
      const title = document.getElementById('resource-tool-title');
      const description = document.getElementById('resource-tool-description');
      const body = document.getElementById('resource-tool-body');
      if (!modal || !title || !description || !body) return;
      title.textContent = item.title;
      description.textContent = item.description;
      body.innerHTML = getResourceToolMarkup(item);
      modal.classList.remove('hidden');
      if (id === 'seller-net') calculateSellerNet();
      if (id === 'fee-split') calculateAdvancedFeeSplit();
      if (id === 'mortgage-scenarios') calculateMortgageScenarios();
      if (id === 'rental-profitability') calculateRentalProfitability();
    }

    function getResourceToolMarkup(item) {
      if (item.id === 'seller-net') return `<div class="space-y-4"><div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label class="text-xs font-bold text-slate-500">Precio de venta (€)<input id="seller-net-price" type="number" value="280000" oninput="calculateSellerNet()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Honorarios agencia (%)<input id="seller-net-fee" type="number" value="4" step="0.1" oninput="calculateSellerNet()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">IVA sobre honorarios (%)<input id="seller-net-vat" type="number" value="21" oninput="calculateSellerNet()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Cancelación hipotecaria (€)<input id="seller-net-mortgage" type="number" value="0" oninput="calculateSellerNet()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Otros gastos configurables (€)<input id="seller-net-other" type="number" value="0" oninput="calculateSellerNet()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label></div><div id="seller-net-result" class="p-4 rounded-2xl bg-green-light border border-green/20"></div><button onclick="showToast('Informe PDF de neto vendedor preparado en modo demostración.', 'success')" class="w-full py-3 rounded-xl bg-navy text-white text-xs font-black">Preparar informe PDF demo</button><p class="text-[10px] text-slate-400">Cálculo orientativo. No incluye impuestos personales del vendedor salvo que se incorporen posteriormente como parámetros configurables.</p></div>`;
      if (item.id === 'fee-split') return `<div class="space-y-4"><div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label class="text-xs font-bold text-slate-500">Precio de venta (€)<input id="adv-fee-price" type="number" value="300000" oninput="calculateAdvancedFeeSplit()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Comisión total (%)<input id="adv-fee-pct" type="number" value="5" step="0.1" oninput="calculateAdvancedFeeSplit()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">IVA (%)<input id="adv-fee-vat" type="number" value="21" oninput="calculateAdvancedFeeSplit()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Parte captador (%)<input id="adv-fee-share-a" type="number" value="50" oninput="calculateAdvancedFeeSplit()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label></div><div id="adv-fee-result" class="p-4 rounded-2xl bg-blue-light border border-blue/20"></div><button onclick="prepareLegalSignature('collaboration')" class="w-full py-3 rounded-xl bg-navy text-white text-xs font-black">Preparar acuerdo de colaboración</button></div>`;
      if (item.id === 'blocked-radar') return `<div class="space-y-3"><p class="text-xs text-slate-500">Marca las incidencias detectadas para obtener un semáforo preliminar.</p><div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">${['Falta nota simple actualizada','Discrepancias catastrales','Herencia pendiente','Hipoteca sin revisar','Falta certificado energético','Arrendatario vigente','Copropietarios sin confirmar','Documentación PBC incompleta','Ocupación o posesión dudosa','Precio sin validar'].map((label,index)=>`<label class="flex items-start gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50"><input type="checkbox" class="radar-issue mt-0.5" onchange="calculateBlockedOperationRadar()"><span>${label}</span></label>`).join('')}</div><div id="blocked-radar-result" class="p-4 rounded-2xl bg-green-light border border-green/20 text-xs"><strong class="text-green">Verde · Preparado para comercializar</strong><p class="mt-1 text-slate-600">No se han marcado incidencias.</p></div></div>`;
      if (item.id === 'document-checklist') return `<div class="space-y-3"><p class="text-xs text-slate-500">Selecciona las circunstancias del expediente.</p><div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">${['Existe hipoteca','Procede de herencia','Separación o divorcio','Existe usufructo','Hay inquilino vigente','Vivienda protegida','Varios titulares','Venta por sociedad'].map(label=>`<label class="flex items-start gap-2 p-3 rounded-xl border border-slate-200 bg-slate-50"><input type="checkbox" class="doc-checklist-case mt-0.5" value="${label}"><span>${label}</span></label>`).join('')}</div><button onclick="generateDocumentChecklist()" class="w-full py-3 rounded-xl bg-blue text-white text-xs font-black">Generar checklist personalizado</button><div id="document-checklist-result" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600"></div></div>`;
      if (item.id === 'mortgage-scenarios') return `<div class="space-y-4"><div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label class="text-xs font-bold text-slate-500">Capital financiado (€)<input id="mortgage-capital" type="number" value="220000" oninput="calculateMortgageScenarios()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Plazo (años)<input id="mortgage-years" type="number" value="30" oninput="calculateMortgageScenarios()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Interés ordinario (%)<input id="mortgage-rate" type="number" value="2.8" step="0.1" oninput="calculateMortgageScenarios()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Escenario prudente (%)<input id="mortgage-rate-stress" type="number" value="4" step="0.1" oninput="calculateMortgageScenarios()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label></div><div id="mortgage-result" class="p-4 rounded-2xl bg-blue-light border border-blue/20"></div><p class="text-[10px] text-slate-400">Simulación informativa: no constituye una oferta bancaria ni una recomendación financiera personalizada.</p></div>`;
      if (item.id === 'rental-profitability') return `<div class="space-y-4"><div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label class="text-xs font-bold text-slate-500">Precio de compra (€)<input id="rent-price" type="number" value="180000" oninput="calculateRentalProfitability()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Renta mensual (€)<input id="rent-monthly" type="number" value="850" oninput="calculateRentalProfitability()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Gastos anuales (€)<input id="rent-costs" type="number" value="1800" oninput="calculateRentalProfitability()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label><label class="text-xs font-bold text-slate-500">Meses vacíos estimados<input id="rent-vacancy" type="number" min="0" max="12" value="1" oninput="calculateRentalProfitability()" class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></label></div><div id="rent-profitability-result" class="p-4 rounded-2xl bg-green-light border border-green/20"></div></div>`;
      if (item.id === 'rental-ad-check') return `<div class="space-y-4"><label class="block text-xs font-bold text-slate-500">Texto del anuncio<textarea id="ad-check-text" rows="7" placeholder="Pega aquí el texto del anuncio para comprobar si incluye la información esencial..." class="mt-1 w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm"></textarea></label><button onclick="generateRentalAdCheck()" class="w-full py-3 rounded-xl bg-blue text-white text-xs font-black">Revisar anuncio</button><div id="ad-check-result" class="hidden p-4 rounded-2xl bg-slate-50 border border-slate-200 text-xs text-slate-600"></div><p class="text-[10px] text-slate-400">Revisión preliminar. El control normativo definitivo debe adaptarse al tipo de operación, territorio y fecha de publicación.</p></div>`;
      return `<div class="p-5 rounded-2xl bg-slate-50 border border-slate-200"><span class="inline-flex px-2 py-1 rounded-full bg-slate-200 text-slate-600 text-[9px] font-black uppercase">Roadmap funcional</span><p class="text-sm text-slate-600 leading-relaxed mt-4">Esta utilidad está incluida en la arquitectura de producto, pero todavía no ejecuta un flujo completo. La tarjeta permite validar interés, nivel de acceso y encaje comercial antes de desarrollar el módulo definitivo.</p><div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mt-4 text-[10px]"><span class="p-2 rounded-lg bg-white border border-slate-200"><b class="text-navy">Tiempo:</b><br>${escapeHTML(item.time)}</span><span class="p-2 rounded-lg bg-white border border-slate-200"><b class="text-navy">Resultado:</b><br>${escapeHTML(item.result)}</span><span class="p-2 rounded-lg bg-white border border-slate-200"><b class="text-navy">Acceso:</b><br>${accessLabels[item.access]}</span></div><button onclick="showToast('Interés registrado para priorizar este recurso en el roadmap.', 'success'); closeResourceToolModal();" class="w-full py-3 mt-4 rounded-xl bg-navy text-white text-xs font-black">Registrar interés</button></div>`;
    }

    function calculateSellerNet() {
      const price = Number(document.getElementById('seller-net-price')?.value) || 0;
      const feePct = Number(document.getElementById('seller-net-fee')?.value) || 0;
      const vatPct = Number(document.getElementById('seller-net-vat')?.value) || 0;
      const mortgage = Number(document.getElementById('seller-net-mortgage')?.value) || 0;
      const other = Number(document.getElementById('seller-net-other')?.value) || 0;
      const fee = price * feePct / 100;
      const vat = fee * vatPct / 100;
      const net = Math.max(0, price - fee - vat - mortgage - other);
      const result = document.getElementById('seller-net-result');
      if (result) result.innerHTML = `<span class="text-[10px] uppercase font-black text-green">Neto orientativo para el vendedor</span><strong class="block text-2xl font-black text-navy mt-1">${formatCurrency(net)}</strong><p class="text-[11px] text-slate-600 mt-2">Honorarios: ${formatCurrency(fee)} · IVA honorarios: ${formatCurrency(vat)} · Otros conceptos: ${formatCurrency(mortgage + other)}</p>`;
    }

    function calculateAdvancedFeeSplit() {
      const price = Number(document.getElementById('adv-fee-price')?.value) || 0;
      const pct = Number(document.getElementById('adv-fee-pct')?.value) || 0;
      const vat = Number(document.getElementById('adv-fee-vat')?.value) || 0;
      const shareA = Math.min(100, Math.max(0, Number(document.getElementById('adv-fee-share-a')?.value) || 0));
      const base = price * pct / 100;
      const tax = base * vat / 100;
      const gross = base + tax;
      const result = document.getElementById('adv-fee-result');
      if (result) result.innerHTML = `<span class="text-[10px] uppercase font-black text-blue">Liquidación orientativa</span><strong class="block text-xl font-black text-navy mt-1">Comisión base: ${formatCurrency(base)}</strong><p class="text-[11px] text-slate-600 mt-2">IVA: ${formatCurrency(tax)} · Total factura: ${formatCurrency(gross)}</p><div class="grid grid-cols-2 gap-2 mt-3"><span class="p-2 rounded-lg bg-white text-xs"><b>Captador ${shareA}%</b><br>${formatCurrency(base * shareA / 100)}</span><span class="p-2 rounded-lg bg-white text-xs"><b>Colaborador ${100-shareA}%</b><br>${formatCurrency(base * (100-shareA) / 100)}</span></div>`;
    }

    function calculateBlockedOperationRadar() {
      const selected = document.querySelectorAll('.radar-issue:checked').length;
      const result = document.getElementById('blocked-radar-result');
      if (!result) return;
      let state='Verde · Preparado para comercializar', color='green', text='No se han marcado incidencias relevantes.';
      if (selected >= 1 && selected <= 3) { state='?mbar · Publicable con tareas pendientes'; color='amber'; text=`Se han detectado ${selected} incidencias. Conviene asignar responsables y plazos.`; }
      if (selected >= 4) { state='Rojo · Resolver antes de aceptar ofertas'; color='red'; text=`Se han detectado ${selected} incidencias. Es recomendable revisar el expediente antes de continuar.`; }
      result.className = `p-4 rounded-2xl ${color === 'green' ? 'bg-green-light border-green/20' : color === 'amber' ? 'bg-amber-light border-amber/20' : 'bg-red-50 border-red-200'} border text-xs`;
      result.innerHTML = `<strong class="${color === 'green' ? 'text-green' : color === 'amber' ? 'text-amber' : 'text-red-600'}">${state}</strong><p class="mt-1 text-slate-600">${text}</p>`;
    }

    function generateDocumentChecklist() {
      const selected = Array.from(document.querySelectorAll('.doc-checklist-case:checked')).map(input => input.value);
      const base = ['Documento de identidad de titulares', 'Nota simple actualizada', 'Título de propiedad', 'Certificado energético', 'Últimos recibos de IBI y comunidad'];
      const extra = [];
      if (selected.includes('Existe hipoteca')) extra.push('Certificado de deuda pendiente y condiciones de cancelación');
      if (selected.includes('Procede de herencia')) extra.push('Escritura de adjudicación de herencia y justificantes fiscales');
      if (selected.includes('Separación o divorcio')) extra.push('Convenio regulador o documentación que acredite facultades de disposición');
      if (selected.includes('Existe usufructo')) extra.push('Documentación del usufructo y consentimiento de intervinientes');
      if (selected.includes('Hay inquilino vigente')) extra.push('Contrato de arrendamiento, anexos y estado de pagos');
      if (selected.includes('Vivienda protegida')) extra.push('Documentación de protección y limitaciones aplicables');
      if (selected.includes('Varios titulares')) extra.push('Identificación y consentimiento de todos los titulares');
      if (selected.includes('Venta por sociedad')) extra.push('Escrituras societarias, representación y titular real');
      const result = document.getElementById('document-checklist-result');
      if (result) { result.classList.remove('hidden'); result.innerHTML = `<strong class="text-navy">Checklist generado</strong><ul class="mt-2 space-y-1 list-disc pl-4">${[...base, ...extra].map(item => `<li>${item}</li>`).join('')}</ul><p class="mt-3 text-[10px] text-slate-400">Lista orientativa para revisión profesional según el expediente concreto.</p>`; }
    }

    function generateRentalAdCheck() {
      const text = cleanText(document.getElementById('ad-check-text')?.value || '').toLowerCase();
      const checks = [
        ['Precio visible', /\d/.test(text) && (text.includes('€') || text.includes('eur') || text.includes('precio'))],
        ['Ubicación aproximada', ['zona','municipio','barrio','provincia','ubicación'].some(word => text.includes(word))],
        ['Superficie o metros', text.includes('m²') || text.includes('m2') || text.includes('metros')],
        ['Tipo de operación', ['venta','alquiler','arrendamiento'].some(word => text.includes(word))],
        ['Certificado energético', text.includes('energ') || text.includes('certificado')],
        ['Condiciones principales', ['condiciones','fianza','honorarios','gastos','comisión'].some(word => text.includes(word))]
      ];
      const completed = checks.filter(item => item[1]).length;
      const result = document.getElementById('ad-check-result');
      if (!result) return;
      result.classList.remove('hidden');
      result.innerHTML = `<strong class="text-navy">Revisión preliminar: ${completed}/${checks.length} criterios detectados</strong><ul class="mt-3 space-y-1">${checks.map(([label,ok]) => `<li class="flex items-center gap-2"><span>${ok ? '✓' : '○'}</span><span>${label}</span></li>`).join('')}</ul>`;
    }

    function monthlyMortgagePayment(capital, years, annualRate) {
      const months = years * 12; if (!months || !capital) return 0;
      const monthly = annualRate / 100 / 12;
      if (!monthly) return capital / months;
      return capital * monthly * Math.pow(1 + monthly, months) / (Math.pow(1 + monthly, months) - 1);
    }

    function calculateMortgageScenarios() {
      const capital = Number(document.getElementById('mortgage-capital')?.value) || 0;
      const years = Number(document.getElementById('mortgage-years')?.value) || 0;
      const rate = Number(document.getElementById('mortgage-rate')?.value) || 0;
      const stress = Number(document.getElementById('mortgage-rate-stress')?.value) || 0;
      const normalPayment = monthlyMortgagePayment(capital, years, rate);
      const stressPayment = monthlyMortgagePayment(capital, years, stress);
      const result = document.getElementById('mortgage-result');
      if (result) result.innerHTML = `<span class="text-[10px] uppercase font-black text-blue">Comparación mensual</span><div class="grid grid-cols-2 gap-2 mt-2"><span class="p-3 rounded-xl bg-white text-xs"><b>Escenario ordinario</b><strong class="block text-lg text-navy mt-1">${formatCurrency(normalPayment)}</strong></span><span class="p-3 rounded-xl bg-white text-xs"><b>Escenario prudente</b><strong class="block text-lg text-navy mt-1">${formatCurrency(stressPayment)}</strong></span></div><p class="text-[11px] text-slate-600 mt-2">Diferencia estimada: ${formatCurrency(Math.max(0, stressPayment-normalPayment))} al mes.</p>`;
    }

    function calculateRentalProfitability() {
      const price = Number(document.getElementById('rent-price')?.value) || 0;
      const monthly = Number(document.getElementById('rent-monthly')?.value) || 0;
      const costs = Number(document.getElementById('rent-costs')?.value) || 0;
      const vacancy = Math.min(12, Math.max(0, Number(document.getElementById('rent-vacancy')?.value) || 0));
      const income = monthly * (12-vacancy);
      const net = income - costs;
      const profitability = price ? net / price * 100 : 0;
      const result = document.getElementById('rent-profitability-result');
      if (result) result.innerHTML = `<span class="text-[10px] uppercase font-black text-green">Rentabilidad neta orientativa</span><strong class="block text-2xl font-black text-navy mt-1">${profitability.toFixed(2)}%</strong><p class="text-[11px] text-slate-600 mt-2">Ingresos estimados: ${formatCurrency(income)} · Gastos: ${formatCurrency(costs)} · Neto anual: ${formatCurrency(net)}</p>`;
    }


    // --- INTERCEPTOR DE CLICS DE NAVEGACIÓN GLOBAL ---
    document.addEventListener('click', (e) => {
      const link = e.target.closest('a');
      if (link) {
        const href = link.getAttribute('href');
        if (href && href.startsWith('#/')) {
          if (href === '#/area-privada' && !getDemoSession?.()) {
            e.preventDefault();
            showRegistrationPrompt(true);
            showToast('Inicia sesión para entrar al panel privado.', 'info');
            return;
          }
          e.preventDefault();
          currentHash = href;
          try {
            window.location.hash = href;
          } catch (err) {}
          handleRoute();
          const mobileNav = document.getElementById('mobile-nav');
          if (mobileNav && !mobileNav.classList.contains('hidden')) {
            toggleMenu();
          }
        }
      }
    });


    // ==========================================
    // 13. PASARELA IA DEL AGENTE: BYO-AI REAL
    // ==========================================
    const AI_PROVIDER_CONFIG = {
      openai: { label: 'OpenAI', model: 'gpt-4o-mini', icon: '◉' },
      anthropic: { label: 'Anthropic', model: 'claude-3-5-haiku-latest', icon: '◈' },
      google: { label: 'Google', model: 'gemini-2.0-flash', icon: '✦' },
      groq: { label: 'Groq', model: 'llama-3.1-8b-instant', icon: '▣' },
      openrouter: { label: 'OpenRouter', model: 'openai/gpt-4o-mini', icon: '◎' },
      compatible: { label: 'Endpoint compatible', model: 'modelo-personalizado', icon: '⌘' }
    };
    let aiConnectionState = null;

    function getAIClientConfig() {
      return window.CAPTACION_APP_AI || {};
    }

    async function captacionAIRequest(path = '', method = 'GET', payload = null) {
      const config = getAIClientConfig();
      const headers = { 'Content-Type': 'application/json' };
      if (config.nonce) headers['X-WP-Nonce'] = config.nonce;
      const response = await fetch(`${config.restBase || ''}${path}`, {
        method,
        headers,
        credentials: 'same-origin',
        body: payload ? JSON.stringify(payload) : null
      });

      let body = {};
      try { body = await response.json(); } catch (error) {}
      if (!response.ok) {
        const message = body?.message || body?.data?.provider_message || 'No se pudo completar la solicitud de IA.';
        const err = new Error(message);
        err.status = response.status;
        err.payload = body;
        throw err;
      }
      return body;
    }

    function hasConnectedAI() {
      return !!(aiConnectionState && aiConnectionState.connected && aiConnectionState.connection && aiConnectionState.connection.active);
    }

    async function loadAIConnection(force = false) {
      if (aiConnectionState && !force) return aiConnectionState;
      if (!getAIClientConfig().isLoggedIn) {
        aiConnectionState = { connected: false, connection: null, authRequired: true };
        return aiConnectionState;
      }
      aiConnectionState = await captacionAIRequest('config');
      return aiConnectionState;
    }

    function resetAIConnectionForm() {
      const form = document.querySelector('#ai-connection-modal form');
      form2.reset();
      const secretInput = document.getElementById('ai-secret-input');
      if (secretInput) {
        secretInput.required = true;
        secretInput.placeholder = 'Se almacenará cifrada para tu usuario';
      }
      const saveBtn = document.getElementById('ai-save-connection-btn');
      if (saveBtn) saveBtn.textContent = 'Guardar conexión';
      syncAIProviderDefaults();
    }

    function openAIConnectionModal(provider = 'openai') {
      const modal = document.getElementById('ai-connection-modal');
      const select = document.getElementById('ai-provider-select');
      const connection = aiConnectionState?.connection || null;
      if (select) select.value = (connection?.provider && AI_PROVIDER_CONFIG[connection.provider]) ? connection.provider : provider;
      document.getElementById('ai-connection-alias').value = connection?.alias || '';
      document.getElementById('ai-use-profile').value = connection?.profile || 'general';
      document.getElementById('ai-model-name').value = connection?.model || '';
      document.getElementById('ai-backend-endpoint').value = connection?.provider === 'compatible' ? (connection?.endpoint || '') : '';
      const secretInput = document.getElementById('ai-secret-input');
      if (secretInput) {
        secretInput.value = '';
        secretInput.required = !connection;
        secretInput.placeholder = connection ? 'Déjalo vacío para mantener la credencial actual' : 'Se almacenará cifrada para tu usuario';
      }
      const saveBtn = document.getElementById('ai-save-connection-btn');
      if (saveBtn) saveBtn.textContent = connection ? 'Actualizar conexión' : 'Guardar conexión';
      syncAIProviderDefaults();
      modal?.classList.remove('hidden');
    }

    function closeAIConnectionModal() {
      const modal = document.getElementById('ai-connection-modal');
      modal?.classList.add('hidden');
      resetAIConnectionForm();
      const confirmation = document.getElementById('ai-security-confirmation');
      if (confirmation) confirmation.checked = false;
    }

    function syncAIProviderDefaults() {
      const provider = document.getElementById('ai-provider-select')?.value || 'openai';
      const config = AI_PROVIDER_CONFIG[provider] || AI_PROVIDER_CONFIG.openai;
      const alias = document.getElementById('ai-connection-alias');
      const model = document.getElementById('ai-model-name');
      const endpointWrap = document.getElementById('ai-endpoint-wrap');
      if (alias && !alias.value.trim()) alias.value = `${config.label} · mi agencia`;
      if (model && !model.value.trim()) model.value = config.model;
      if (endpointWrap) endpointWrap.classList.toggle('hidden', provider !== 'compatible');
    }

    async function saveAIConnection(event) {
      event.preventDefault();
      if (!getAIClientConfig().isLoggedIn) {
        showToast('Debes iniciar sesión en WordPress para guardar tu conexión IA.', 'info');
        return;
      }
      const provider = document.getElementById('ai-provider-select')?.value || 'openai';
      const alias = cleanText(document.getElementById('ai-connection-alias')?.value || '');
      const profile = cleanText(document.getElementById('ai-use-profile')?.value || 'general');
      const model = cleanText(document.getElementById('ai-model-name')?.value || '');
      const endpoint = cleanText(document.getElementById('ai-backend-endpoint')?.value || '');
      const secret = document.getElementById('ai-secret-input')?.value || '';
      const saveBtn = document.getElementById('ai-save-connection-btn');
      const hasExisting = !!aiConnectionState?.connection;
      if (!alias || (!secret && !hasExisting)) {
        showToast('Completa el alias y la credencial para guardar la conexión.', 'info');
        return;
      }
      if (provider === 'compatible' && !endpoint) {
        showToast('Indica un endpoint compatible con OpenAI para este proveedor.', 'info');
        return;
      }
      const original = saveBtn?.innerHTML || '';
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = 'Guardando...';
      }
      try {
        await captacionAIRequest('config', 'POST', {
          provider,
          alias,
          profile,
          model,
          endpoint,
          api_key: secret,
          active: true
        });
        await loadAIConnection(true);
        closeAIConnectionModal();
        renderAIConnections();
        showToast('Conexión IA guardada correctamente.', 'success');
      } catch (error) {
        showToast(error.message || 'No se pudo guardar la conexión IA.', 'info');
      } finally {
        if (saveBtn) {
          saveBtn.disabled = false;
          saveBtn.innerHTML = original;
        }
      }
    }

    async function removeAIConnection() {
      if (!getAIClientConfig().isLoggedIn) return;
      try {
        await captacionAIRequest('config', 'DELETE');
        aiConnectionState = { connected: false, connection: null };
        renderAIConnections();
        showToast('Configuración IA eliminada.', 'success');
      } catch (error) {
        showToast(error.message || 'No se pudo eliminar la configuración IA.', 'info');
      }
    }

    async function testAIConnection() {
      if (!getAIClientConfig().isLoggedIn) return;
      try {
        const result = await captacionAIRequest('test', 'POST', {});
        aiConnectionState = { connected: true, connection: result.connection || aiConnectionState?.connection || null };
        renderAIConnections();
        showToast(result.message || 'Conexión IA validada correctamente.', 'success');
      } catch (error) {
        showToast(error.payload?.data?.provider_message || error.message || 'La prueba de conexión falló.', 'info');
      }
    }

    async function setAIConnectionStatus(active) {
      if (!getAIClientConfig().isLoggedIn) return;
      try {
        const result = await captacionAIRequest('config/status', 'POST', { active: !!active });
        aiConnectionState = { connected: !!result.connection, connection: result.connection || null };
        renderAIConnections();
        showToast(active ? 'Conexión IA activada.' : 'Conexión IA desactivada.', 'success');
      } catch (error) {
        showToast(error.message || 'No se pudo actualizar el estado de la conexión IA.', 'info');
      }
    }

    async function renderAIConnections() {
      const container = document.getElementById('ai-connections-list');
      if (!container) return;
      if (!getAIClientConfig().isLoggedIn) {
        container.innerHTML = `<div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-500 leading-relaxed">Debes iniciar sesión en WordPress para guardar y utilizar una conexión IA personal.</div>`;
        return;
      }
      container.innerHTML = `<div class="p-4 rounded-xl border border-slate-200 bg-slate-50 text-xs text-slate-500">Cargando configuración IA...</div>`;
      try {
        const state = await loadAIConnection(true);
        const connection = state?.connection || null;
        if (!connection) {
          container.innerHTML = `<div class="p-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 text-xs text-slate-500 leading-relaxed">Todavía no has configurado un proveedor IA. Usa <strong>Conectar IA</strong> para activar funciones asistidas con tu propia cuenta.</div>`;
          return;
        }
        const provider = AI_PROVIDER_CONFIG[connection.provider] || AI_PROVIDER_CONFIG.compatible;
        const validatedAt = connection.last_validated_at ? new Date(connection.last_validated_at * 1000).toLocaleString('es-ES') : 'Pendiente';
        const statusLabel = connection.status === 'connected' ? 'Conectado' : (connection.status === 'error' ? 'Error' : (connection.active ? 'Configurado' : 'Desactivado'));
        const statusClass = connection.status === 'connected'
          ? 'bg-green-light text-green'
          : (connection.status === 'error' ? 'bg-red-50 text-red-600' : 'bg-slate-100 text-slate-600');
        container.innerHTML = `
          <article class="ai-connection-card flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div class="flex items-start gap-3">
              <span class="flex w-10 h-10 shrink-0 items-center justify-center rounded-xl bg-blue-light text-blue font-black">${provider.icon}</span>
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <strong class="text-sm text-navy">${escapeHTML(connection.alias || provider.label)}</strong>
                  <span class="px-2 py-1 rounded-full ${statusClass} text-[9px] font-black uppercase">${escapeHTML(statusLabel)}</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">${escapeHTML(connection.provider_label || provider.label)} · ${escapeHTML(connection.model || provider.model)} · Huella ${escapeHTML(connection.fingerprint || 'N/D')}</p>
                <p class="text-[10px] text-slate-400 mt-1">Perfil: ${escapeHTML(connection.profile || 'general')} · Última validación: ${escapeHTML(validatedAt)}</p>
                ${connection.last_error ? `<p class="text-[10px] text-red-600 mt-1">${escapeHTML(connection.last_error)}</p>` : ''}
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <button type="button" onclick="testAIConnection()" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-bold">Probar conexión</button>
              <button type="button" onclick="setAIConnectionStatus(${connection.active ? 'false' : 'true'})" class="px-3 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-[10px] font-bold">${connection.active ? 'Desactivar' : 'Activar'}</button>
              <button type="button" onclick="removeAIConnection()" class="px-3 py-2 rounded-lg border border-red-200 bg-red-50 text-red-600 text-[10px] font-bold">Eliminar</button>
            </div>
          </article>`;
      } catch (error) {
        container.innerHTML = `<div class="p-4 rounded-xl border border-red-200 bg-red-50 text-xs text-red-600 leading-relaxed">${escapeHTML(error.message || 'No se pudo cargar la configuración IA.')}</div>`;
      }
    }

    // ==========================================
    // 13. APARIENCIA GLOBAL: MODO CLARO / OSCURO
    // ==========================================
    function getCurrentTheme() {
      return document.documentElement.dataset.theme === 'dark' ? 'dark' : 'light';
    }

    function syncThemeToggleButtons() {
      const isDark = getCurrentTheme() === 'dark';
    const desktopButton = document.getElementById('theme-toggle-desktop');
    const mobileButton = document.getElementById('theme-toggle-mobile');
    const desktopIcon = document.getElementById('theme-toggle-desktop-icon');
    const mobileIcon = document.getElementById('theme-toggle-mobile-icon');
    const nextTheme = isDark ? 'claro' : 'oscuro';
      [desktopButton, mobileButton].forEach(button => {
        if (!button) return;
        button.setAttribute('aria-pressed', String(isDark));
        button.setAttribute('title', `Cambiar a modo ${nextTheme}`);
        button.setAttribute('aria-label', `Cambiar a modo ${nextTheme}`);
    });
    if (desktopIcon) desktopIcon.textContent = isDark ? '🌙' : '☀';
    if (mobileIcon) mobileIcon.textContent = isDark ? '🌙' : '☀';
    const themeMeta = document.getElementById('theme-color-meta');
      if (themeMeta) themeMeta.setAttribute('content', isDark ? '#07111f' : '#eef3f8');
      setTimeout(() => {
        homeMap?.invalidateSize?.();
        marketplaceMap?.invalidateSize?.();
        needsMap?.invalidateSize?.();
      }, 80);
    }

    function applyTheme(theme, persist = true) {
      const normalizedTheme = theme === 'dark' ? 'dark' : 'light';
      document.documentElement.dataset.theme = normalizedTheme;
      if (persist) {
        try { localStorage.setItem('captacion_theme_v1', normalizedTheme); } catch (error) {}
      }
      syncThemeToggleButtons();
    }

    function toggleTheme() {
      applyTheme(getCurrentTheme() === 'dark' ? 'light' : 'dark');
      showToast(`Modo ${getCurrentTheme() === 'dark' ? 'oscuro' : 'claro'} activado.`, 'info');
    }



    // ==========================================
    // 14. DASHBOARD PRIVADO INTEGRADO DEL AGENTE
    // ==========================================
    const PRIVATE_DASHBOARD_STORAGE_KEY = 'captacion_agent_private_dashboard_v2';
    let privateDashboardPanel = 'overview';
    let lastPrivateDashboardPanel = '';
    let privateDashboardFocus = 'general';
    let privateMatchesMode = 'offers';

    function createPrivateDashboardSeed() {
      return {
        operations: [],
        favorites: [],
        tasks: [],
        notifications: [],
        activities: [],
        requestsReceived: [],
        requestsSent: [],
        clients: [],
        leads: []
      };
    }

    function getPrivateDashboardState() {
      try {
        const stored = JSON.parse(localStorage.getItem(PRIVATE_DASHBOARD_STORAGE_KEY));
        if (stored && Array.isArray(stored.operations) && Array.isArray(stored.tasks)) return normalizePrivateDashboardState(stored);
      } catch (error) {}
      const seed = normalizePrivateDashboardState(createPrivateDashboardSeed());
      persistPrivateDashboardState(seed);
      return seed;
    }

    function persistPrivateDashboardState(state) {
      try { localStorage.setItem(PRIVATE_DASHBOARD_STORAGE_KEY, JSON.stringify(normalizePrivateDashboardState(state))); } catch (error) {}
    }

    function inferDueTimestamp(task = {}) {
      if (Number(task.dueAt)) return Number(task.dueAt);
      const base = Date.now();
      const dueText = normalizeMatchText(task.due || '');
      if (dueText.includes('hoy')) return base + 3600000 * 6;
      if (dueText.includes('mañ') || dueText.includes('man')) return base + 86400000;
      if (dueText.includes('semana')) return base + 86400000 * 3;
      return base + 86400000 * 2;
    }

    function normalizePrivateDashboardState(state = {}) {
      state.tasks = Array.isArray(state.tasks) ? state.tasks.map(task => ({ ...task, dueAt: inferDueTimestamp(task) })) : [];
      state.notifications = Array.isArray(state.notifications) ? state.notifications.map(item => ({ ...item, dueAt: Number(item.dueAt) || Number(item.createdAt) || Date.now() })) : [];
      state.operations = Array.isArray(state.operations) ? state.operations : [];
      state.activities = Array.isArray(state.activities) ? state.activities : [];
      state.requestsReceived = Array.isArray(state.requestsReceived) ? state.requestsReceived : [];
      state.requestsSent = Array.isArray(state.requestsSent) ? state.requestsSent : [];
      state.favorites = Array.isArray(state.favorites) ? state.favorites : [];
      state.clients = Array.isArray(state.clients) ? state.clients : [];
      state.leads = Array.isArray(state.leads) ? state.leads : [];
      state.fiscalProfile = state.fiscalProfile && typeof state.fiscalProfile === 'object' ? state.fiscalProfile : {};
      return state;
    }

    function currentPrivateUserEmail() { return (getDemoSession?.()?.email || CAPTACION_MAILCHIMP?.currentUser?.email || '').toLowerCase(); }
    function isOwnedByCurrentUser(item = {}) {
      const email = currentPrivateUserEmail();
      if (!email) return false;
      return String(item.userEmail || item.user_email || item.ownerEmail || '').toLowerCase() === email;
    }
    function privateProperties() { return properties.filter(isOwnedByCurrentUser); }
    function privateNeeds() { return needs.filter(isOwnedByCurrentUser); }
    function privatePropertyById(id) { return privateProperties().find(item => item.id === id) || properties.find(item => item.id === id) || null; }
    function privateNeedById(id) { return privateNeeds().find(item => item.id === id) || needs.find(item => item.id === id) || null; }
    function privateStatusClasses(status = '') {
      const normalized = String(status).toLowerCase();
      if (normalized.includes('complet') || normalized.includes('disponible') || normalized.includes('desbloque')) return 'bg-green-light text-green';
      if (normalized.includes('cancel') || normalized.includes('rechaz')) return 'bg-red-50 text-red-600';
      if (normalized.includes('pendiente') || normalized.includes('nda') || normalized.includes('pago')) return 'bg-amber-light text-amber';
      return 'bg-blue-light text-blue';
    }
    function privatePriorityClasses(priority = 'low') { return priority === 'high' ? 'private-priority-high' : priority === 'medium' ? 'private-priority-medium' : 'private-priority-low'; }
    function privatePriorityLabel(priority = 'low') { return priority === 'high' ? 'Alta' : priority === 'medium' ? 'Media' : 'Normal'; }
    function privateSafeDate(value) { return new Date(Number(value) || Date.now()).toLocaleDateString('es-ES', { day:'2-digit', month:'2-digit', year:'numeric' }); }

    function addPrivateNotification(entry = {}) {
      const state = getPrivateDashboardState();
      const dedupeKey = entry.dedupeKey || '';
      if (dedupeKey && (state.notifications || []).some(item => item.dedupeKey === dedupeKey)) return false;
      const notification = {
        id: `NOT-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
        category: entry.category || 'Sistema',
        title: entry.title || 'Aviso operativo',
        detail: entry.detail || '',
        createdAt: Date.now(),
        dueAt: Number(entry.dueAt) || Date.now(),
        read: false,
        target: entry.target || 'overview',
        propertyId: entry.propertyId || '',
        needId: entry.needId || '',
        dedupeKey
      };
      state.notifications.unshift(notification);
      persistWpRecord('notification', notification, { recordKey: notification.id, title: notification.title, status: notification.read ? 'read' : 'unread', relatedId: dedupeKey });
      persistPrivateDashboardState(state);
      return true;
    }

    function addPrivateTask(entry = {}) {
      const state = getPrivateDashboardState();
      const dedupeKey = entry.dedupeKey || '';
      if (dedupeKey && (state.tasks || []).some(item => item.dedupeKey === dedupeKey)) return false;
      state.tasks.unshift({
        id: `TASK-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`,
        title: entry.title || 'Seguimiento pendiente',
        detail: entry.detail || '',
        priority: entry.priority || 'medium',
        due: entry.due || 'Próximamente',
        dueAt: Number(entry.dueAt) || inferDueTimestamp({ due: entry.due || 'Próximamente' }),
        status: 'pending',
        target: entry.target || 'tasks',
        dedupeKey
      });
      persistPrivateDashboardState(state);
      return true;
    }

    function addPrivateActivity(icon, title, detail) {
      const state = getPrivateDashboardState();
      const activity = { id:`ACT-${Date.now()}-${Math.random().toString(36).slice(2, 6)}`, icon, title, detail, createdAt:Date.now() };
      state.activities.unshift(activity);
      persistWpRecord('activity', activity, { recordKey: activity.id, title: activity.title, status: 'logged' });
      persistPrivateDashboardState(state);
    }

    function syncAlertsForProperty(property) {
      const matches = getCompatibleNeedsForProperty(property, 8);
      if (!matches.length) {
        persistWpRecord('smart_match', { id:`watch-property-${property.id}`, kind:'property_without_match', propertyId:property.id, matches:[], createdAt:Date.now() }, { recordKey:`watch-property-${property.id}`, title: property.title || property.reference || property.id, status:'watching', relatedId: property.id });
        sendNotificationEmail('no_match_watch', { reference: property.reference || property.title || property.id, message: 'Captacion publicada sin coincidencias inmediatas.' });
        return;
      }
      const top = matches[0];
      persistWpRecord('smart_match', { id:`match-property-${property.id}`, kind:'property_match', propertyId:property.id, topScore:top.score, matches, createdAt:Date.now() }, { recordKey:`match-property-${property.id}`, title: property.title || property.reference || property.id, status:'detected', relatedId: property.id });
      sendNotificationEmail('match_property', { reference: property.reference || property.title || property.id, message: `${matches.length} demanda${matches.length === 1 ? '' : 's'} compatible${matches.length === 1 ? '' : 's'} detectada${matches.length === 1 ? '' : 's'}. Match principal: ${top.score}%.` });
      addPrivateNotification({
        category: 'Oportunidades',
        title: 'Nueva captación con demanda compatible',
        detail: `${property.title} encaja con ${matches.length} demanda${matches.length === 1 ? '' : 's'} activa${matches.length === 1 ? '' : 's'}. Match principal: ${top.score}%.`,
        target: 'offers',
        dueAt: Date.now() + 3600000 * 6,
        dedupeKey: `prop-match-${property.id}`
      });
      addPrivateTask({
        title: 'Revisar nueva captación vinculable',
        detail: `Valora ${property.title} frente a ${matches.length} demanda${matches.length === 1 ? '' : 's'} compatible${matches.length === 1 ? '' : 's'}.`,
        priority: top.score >= 75 ? 'high' : 'medium',
        due: 'Hoy',
        dueAt: Date.now() + 3600000 * 8,
        target: 'offers',
        dedupeKey: `task-prop-match-${property.id}`
      });
      addPrivateActivity('✦', 'Captación enlazada automáticamente', 'La plataforma detectó nuevas demandas compatibles con una publicación reciente.');
    }

    function syncAlertsForNeed(need) {
      const matches = getCompatiblePropertiesForNeed(need, 8);
      if (!matches.length) {
        persistWpRecord('smart_match', { id:`watch-need-${need.id}`, kind:'need_without_match', needId:need.id, matches:[], createdAt:Date.now() }, { recordKey:`watch-need-${need.id}`, title: need.title || need.id, status:'watching', relatedId: need.id });
        sendNotificationEmail('no_match_watch', { reference: need.title || need.id, message: 'Demanda publicada sin coincidencias inmediatas.' });
        return;
      }
      const top = matches[0];
      persistWpRecord('smart_match', { id:`match-need-${need.id}`, kind:'need_match', needId:need.id, topScore:top.score, matches, createdAt:Date.now() }, { recordKey:`match-need-${need.id}`, title: need.title || need.id, status:'detected', relatedId: need.id });
      sendNotificationEmail('match_need', { reference: need.title || need.id, message: `${matches.length} captacion${matches.length === 1 ? '' : 'es'} compatible${matches.length === 1 ? '' : 's'} detectada${matches.length === 1 ? '' : 's'}. Match principal: ${top.score}%.` });
      addPrivateNotification({
        category: 'Demandas',
        title: 'Nueva búsqueda con oferta compatible',
        detail: `${need.title} tiene ${matches.length} captación${matches.length === 1 ? '' : 'es'} compatible${matches.length === 1 ? '' : 's'}. Match principal: ${top.score}%.`,
        target: 'demands',
        dueAt: Date.now() + 3600000 * 6,
        dedupeKey: `need-match-${need.id}`
      });
      addPrivateTask({
        title: 'Revisar demanda con oferta enlazada',
        detail: `La necesidad ${need.title} ya cuenta con producto compatible para revisar.`,
        priority: top.score >= 75 ? 'high' : 'medium',
        due: 'Hoy',
        dueAt: Date.now() + 3600000 * 8,
        target: 'demands',
        dedupeKey: `task-need-match-${need.id}`
      });
      addPrivateActivity('🔔', 'Demanda enlazada automáticamente', 'Se generó una alerta interna por coincidencia entre búsqueda y captación.');
    }

    function getAgendaEntries(state = getPrivateDashboardState()) {
      const tasks = (state.tasks || []).filter(item => item.status !== 'done').map(item => ({
        kind: 'task',
        title: item.title,
        detail: item.detail,
        timestamp: Number(item.dueAt) || inferDueTimestamp(item),
        target: item.target || 'tasks',
        badge: privatePriorityLabel(item.priority || 'low')
      }));
      const alerts = (state.notifications || []).map(item => ({
        kind: 'alert',
        title: item.title,
        detail: item.detail,
        timestamp: Number(item.dueAt) || Number(item.createdAt) || Date.now(),
        target: item.target || 'notifications',
        badge: item.category || 'Aviso'
      }));
      return [...tasks, ...alerts].sort((a, b) => a.timestamp - b.timestamp);
    }

    function renderPrivateAgendaCalendar(calendarId, eventsId, limit = 6) {
      const calendar = document.getElementById(calendarId);
      const events = document.getElementById(eventsId);
      if (!calendar || !events) return;
      const state = getPrivateDashboardState();
      const entries = getAgendaEntries(state);
      const base = new Date();
      const year = base.getFullYear();
      const month = base.getMonth();
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const startWeekday = (firstDay.getDay() + 6) % 7;
      const daysInMonth = lastDay.getDate();
      const todayKey = new Date().toISOString().slice(0, 10);
      const grouped = entries.reduce((acc, item) => {
        const key = new Date(item.timestamp).toISOString().slice(0, 10);
        (acc[key] = acc[key] || []).push(item);
        return acc;
      }, {});
      const labels = ['L', 'M', 'X', 'J', 'V', 'S', 'D'].map(label => `<span class="text-[10px] font-black text-slate-400 text-center">${label}</span>`).join('');
      const cells = [];
      for (let i = 0; i < startWeekday; i++) cells.push('<div></div>');
      for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        const key = date.toISOString().slice(0, 10);
        const items = grouped[key] || [];
        const classes = ['private-calendar-day'];
        if (key === todayKey) classes.push('is-today');
        if (items.length) classes.push('is-active');
        const dots = items.slice(0, 3).map(item => `<span class="private-calendar-dot ${item.kind === 'task' ? 'task' : 'alert'}"></span>`).join('');
        cells.push(`<button type="button" onclick="focusAgendaDate('${key}')" class="${classes.join(' ')} text-left"><span class="text-[11px] font-bold text-navy">${day}</span><span class="flex flex-wrap gap-1 mt-auto">${dots}</span></button>`);
      }
      calendar.innerHTML = `<div><div class="flex items-center justify-between mb-3"><strong class="text-sm text-navy">${base.toLocaleDateString('es-ES', { month:'long', year:'numeric' })}</strong><span class="text-[10px] text-slate-400">Tareas y alertas</span></div><div class="private-calendar-grid mb-2">${labels}</div><div class="private-calendar-grid">${cells.join('')}</div></div>`;
      const nextEntries = entries.slice(0, limit);
      events.innerHTML = nextEntries.length ? nextEntries.map(item => `<article class="private-mini-card"><div class="flex items-start justify-between gap-3"><div><strong class="block text-xs text-navy">${escapeHTML(item.title)}</strong><span class="block text-[10px] text-slate-500 mt-1">${new Date(item.timestamp).toLocaleDateString('es-ES', { day:'2-digit', month:'2-digit' })} · ${escapeHTML(item.badge)}</span><p class="text-[11px] text-slate-500 mt-2">${escapeHTML(item.detail)}</p></div><button onclick="switchPrivateDashboardPanel('${item.target}')" class="text-[10px] font-bold text-blue shrink-0">Abrir</button></div></article>`).join('') : `<p class="text-xs text-slate-500">No hay elementos pendientes por fecha.</p>`;
      window.__captacionAgendaEntries = grouped;
    }

    function focusAgendaDate(dateKey) {
      const grouped = window.__captacionAgendaEntries || {};
      const list = grouped[dateKey] || [];
      const overview = document.getElementById('private-overview-calendar-events');
      const tasks = document.getElementById('private-tasks-calendar-events');
      const html = list.length ? list.map(item => `<article class="private-mini-card"><div class="flex items-start justify-between gap-3"><div><strong class="block text-xs text-navy">${escapeHTML(item.title)}</strong><span class="block text-[10px] text-slate-500 mt-1">${new Date(item.timestamp).toLocaleString('es-ES', { day:'2-digit', month:'2-digit', hour:'2-digit', minute:'2-digit' })}</span><p class="text-[11px] text-slate-500 mt-2">${escapeHTML(item.detail)}</p></div><button onclick="switchPrivateDashboardPanel('${item.target}')" class="text-[10px] font-bold text-blue shrink-0">Abrir</button></div></article>`).join('') : `<p class="text-xs text-slate-500">No hay eventos para esta fecha.</p>`;
      if (overview) overview.innerHTML = html;
      if (tasks) tasks.innerHTML = html;
    }

    function exportPrivateAgendaCalendar() {
      const entries = getAgendaEntries().slice(0, 12);
      if (!entries.length) {
        showToast('No hay eventos pendientes para exportar.', 'info');
        return;
      }
      const lines = ['BEGIN:VCALENDAR', 'VERSION:2.0', 'PRODID:-//Captacion.app//Agenda Demo//ES'];
      entries.forEach((item, index) => {
        const start = new Date(item.timestamp);
        const end = new Date(item.timestamp + 3600000);
        const toIcs = date => date.toISOString().replace(/[-:]/g, '').split('.')[0] + 'Z';
        lines.push('BEGIN:VEVENT', `UID:AGENDA-${index}-${Date.now()}@captacion.app`, `DTSTAMP:${toIcs(new Date())}`, `DTSTART:${toIcs(start)}`, `DTEND:${toIcs(end)}`, `SUMMARY:${item.title}`, `DESCRIPTION:${item.detail}`, 'END:VEVENT');
      });
      lines.push('END:VCALENDAR');
      const blob = new Blob([lines.join('\r\n')], { type:'text/calendar;charset=utf-8' });
      const url = URL.createObjectURL(blob);
      const link = document.createElement('a');
      link.href = url;
      link.download = `captacion-app-agenda-${Date.now()}.ics`;
      link.click();
      URL.revokeObjectURL(url);
      showToast('Agenda exportada al calendario.', 'success');
    }

    function switchPrivateDashboardPanel(panel = 'overview') {
      if (privateDashboardPanel !== panel && privateDashboardPanel !== 'overview') lastPrivateDashboardPanel = privateDashboardPanel;
      privateDashboardPanel = panel;
      const privateArea = document.getElementById('page-area-privada');
      if (privateArea) privateArea.classList.toggle('executive-mode', panel === 'overview');
      document.querySelectorAll('.private-dashboard-panel').forEach(item => item.classList.toggle('active', item.id === `private-panel-${panel}`));
      document.querySelectorAll('[data-private-panel]').forEach(button => button.classList.toggle('active', button.dataset.privatePanel === panel));
      const mobile = document.getElementById('private-dashboard-mobile-select');
      if (mobile) mobile.value = panel;
      if (panel === 'feeds') { loadPrivateXmlUrl(); renderPrivateXmlFeeds(); loadImportBatches(); }
      if (panel === 'data') loadImportBatches();
      if (panel === 'ai') renderAIConnections();
      renderDashboard();
    }

    function openExecutiveDestination(destination) {
      const panelDestinations = { offers:'offers', demands:'demands', requests:'requests', operations:'operations', favorites:'favorites', tasks:'tasks', notifications:'notifications' };
      if (destination === 'matches') {
        window.location.hash = '#/coincidencias-ventas';
        return;
      }
      if (destination === 'operations-closed') {
        switchPrivateDashboardPanel('operations');
        const filter = document.getElementById('private-operation-status-filter');
        if (filter) filter.value = 'Completada';
        renderPrivateOperations();
        return;
      }
      if (destination === 'clients' || destination === 'leads') {
        switchPrivateDashboardPanel('overview');
        showToast(`${destination === 'clients' ? 'Clientes asignados' : 'Leads activos'} se muestra en la vista consolidada del resumen.`, 'info');
        document.querySelector('.exec-summary')?.scrollIntoView({ behavior:'smooth', block:'center' });
        return;
      }
      switchPrivateDashboardPanel(panelDestinations[destination] || 'overview');
    }

    function activateExecutiveKey(event, destination) {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      openExecutiveDestination(destination);
    }

    function closeExecutiveDashboard() {
      if (lastPrivateDashboardPanel && lastPrivateDashboardPanel !== 'overview') {
        const destination = lastPrivateDashboardPanel;
        lastPrivateDashboardPanel = '';
        switchPrivateDashboardPanel(destination);
        return;
      }
      try {
        if (document.referrer && new URL(document.referrer).origin === window.location.origin && window.history.length > 1) {
          window.history.back();
          return;
        }
      } catch (error) {}
      window.location.hash = '#/inicio';
    }

    function setPrivateDashboardFocus(focus = 'general') {
      privateDashboardFocus = focus;
      ['general','offers','demands'].forEach(item => {
        const button = document.getElementById(`private-view-${item}`);
        if (!button) return;
        button.className = `px-3 py-2 rounded-lg ${item === focus ? 'bg-navy text-white' : 'text-slate-500'}`;
      });
      renderPrivateKPIs();
      renderPrivateAttention();
    }

    function setPrivateMatchesMode(mode = 'offers') {
      privateMatchesMode = mode;
      const offers = document.getElementById('private-match-offers-tab');
      const demands = document.getElementById('private-match-demands-tab');
      if (offers) offers.className = `px-3 py-2 rounded-lg ${mode === 'offers' ? 'bg-white text-navy shadow-sm' : 'text-slate-500'}`;
      if (demands) demands.className = `px-3 py-2 rounded-lg ${mode === 'demands' ? 'bg-white text-navy shadow-sm' : 'text-slate-500'}`;
      renderPrivateMatches();
    }

    function privateKpiCard(label, value, accent, panel, subtitle = '') {
      return `<article class="private-kpi-card"><button type="button" onclick="switchPrivateDashboardPanel('${panel}')"><span class="block text-[10px] font-black uppercase tracking-wider text-slate-500">${escapeHTML(label)}</span><strong class="block text-2xl font-black ${accent} mt-1">${escapeHTML(String(value))}</strong>${subtitle ? `<span class="block text-[10px] font-semibold text-slate-500 mt-1 leading-relaxed">${escapeHTML(subtitle)}</span>` : ''}</button></article>`;
    }

    function privateEstimateLabel(value, hasData = true) {
      return hasData && Number(value) > 0 ? `${formatCurrency(value)} estimados` : 'Sin estimación disponible';
    }

    function linkedPropertyValue(item = {}) {
      const property = privatePropertyById(item.propertyId);
      return Number(item.value || item.price || property?.price) || 0;
    }

    function renderPrivateKPIs() {
      const container = document.getElementById('private-dashboard-kpis'); if (!container) return;
      const state = getPrivateDashboardState();
      const operations = state.operations || [];
      const activeOperationRows = operations.filter(item => !['Completada','Cancelada'].includes(item.status));
      const completedOperationRows = operations.filter(item => item.status === 'Completada');
      const canceledOperationRows = operations.filter(item => item.status === 'Cancelada');
      const activeOps = activeOperationRows.length;
      const completedOps = completedOperationRows.length + closedOperations.length;
      const canceledOps = canceledOperationRows.length;
      const pendingTasks = (state.tasks || []).filter(item => item.status !== 'done').length;
      const unread = (state.notifications || []).filter(item => !item.read).length;
      const salesMatches = getSalesMatchRecords();
      const matches = salesMatches.length;
      const myProperties = privateProperties();
      const myNeeds = privateNeeds();
      const captureValue = myProperties.reduce((sum,item)=>sum+(Number(item.price)||0),0);
      const requestValue = (state.requestsReceived || []).reduce((sum,item)=>sum+linkedPropertyValue(item),0);
      const matchValue = salesMatches.reduce((sum,item)=>sum+(Number(item.estimatedValue)||0),0);
      const demandValue = myNeeds.reduce((sum,item)=>sum+(Number(item.budget)||0),0);
      const favoriteValue = getFavoriteIds('capture').reduce((sum,id)=>sum+(Number(privatePropertyById(id)?.price)||0),0) + getFavoriteIds('demand').reduce((sum,id)=>sum+(Number(privateNeedById(id)?.budget)||0),0) + getFavoriteIds('match').reduce((sum,id)=>sum+(Number(salesMatches.find(item=>item.id===id)?.estimatedValue)||0),0);
      const activeValue = activeOperationRows.reduce((sum,item)=>sum+linkedPropertyValue(item),0);
      const completedValue = completedOperationRows.reduce((sum,item)=>sum+linkedPropertyValue(item),0) + closedOperations.reduce((sum,item)=>sum+(Number(item.price)||0),0);
      const canceledValue = canceledOperationRows.reduce((sum,item)=>sum+linkedPropertyValue(item),0);
      let cards = [];
      if (privateDashboardFocus !== 'demands') cards.push(privateKpiCard('Mis captaciones publicadas', myProperties.length, 'text-blue', 'offers', privateEstimateLabel(captureValue, myProperties.length > 0)), privateKpiCard('Solicitudes recibidas', (state.requestsReceived || []).length, 'text-amber', 'requests', privateEstimateLabel(requestValue, requestValue > 0)), privateKpiCard('Coincidencias detectadas', matches, 'text-green', 'overview', privateEstimateLabel(matchValue, matches > 0)));
      if (privateDashboardFocus !== 'offers') cards.push(privateKpiCard('Mis demandas activas', myNeeds.length, 'text-navy', 'demands', privateEstimateLabel(demandValue, myNeeds.length > 0)), privateKpiCard('Favoritos', getFavoriteIds('capture').length + getFavoriteIds('demand').length + getFavoriteIds('match').length, 'text-amber', 'favorites', privateEstimateLabel(favoriteValue, favoriteValue > 0)));
      cards.push(privateKpiCard('Operaciones en curso', activeOps, 'text-blue', 'operations', privateEstimateLabel(activeValue, activeValue > 0)), privateKpiCard('Operaciones cerradas', completedOps, 'text-green', 'operations', privateEstimateLabel(completedValue, completedOps > 0)), privateKpiCard('Operaciones canceladas', canceledOps, 'text-red-600', 'operations', privateEstimateLabel(canceledValue, canceledValue > 0)), privateKpiCard('Tareas pendientes', pendingTasks, 'text-amber', 'tasks'), privateKpiCard('Avisos sin leer', unread, 'text-red-600', 'notifications'), privateKpiCard('Clientes asignados', (state.clients || []).length, 'text-navy', 'overview'), privateKpiCard('Leads activos', (state.leads || []).filter(item => item.status !== 'Convertido').length, 'text-blue', 'overview'));
      container.innerHTML = cards.join('');
      const sidebarRequests = document.getElementById('private-sidebar-requests'); if (sidebarRequests) sidebarRequests.textContent = String((state.requestsReceived || []).filter(item => item.status.includes('Pendiente')).length);
      const sidebarTasks = document.getElementById('private-sidebar-tasks'); if (sidebarTasks) sidebarTasks.textContent = String(pendingTasks);
      const sidebarNotifications = document.getElementById('private-sidebar-notifications'); if (sidebarNotifications) sidebarNotifications.textContent = String(unread);
    }

    function renderPrivateAttention() {
      const container = document.getElementById('private-attention-list'); if (!container) return;
      const state = getPrivateDashboardState();
      const items = [];
      (state.requestsReceived || []).filter(item => item.status.includes('Pendiente')).slice(0, 2).forEach(item => items.push({ priority:'high', title:'Solicitud pendiente de disponibilidad', detail:`${item.agency} espera confirmación y condiciones de colaboración.`, action:'Revisar solicitud', panel:'requests' }));
      (state.tasks || []).filter(item => item.status !== 'done').slice(0, 3).forEach(item => items.push({ priority:item.priority, title:item.title, detail:item.detail, action:'Abrir tarea', panel:item.target || 'tasks' }));
      if (!items.length) container.innerHTML = `<div class="p-5 text-xs text-slate-500">No tienes acciones urgentes. Tu bandeja está al día.</div>`;
      else container.innerHTML = items.slice(0, 5).map(item => `<article class="px-5 py-4 ${privatePriorityClasses(item.priority)}"><div class="flex items-start justify-between gap-4"><div><div class="flex items-center gap-2"><span class="text-[9px] font-black uppercase tracking-wider ${item.priority === 'high' ? 'text-red-600' : item.priority === 'medium' ? 'text-amber' : 'text-blue'}">Prioridad ${privatePriorityLabel(item.priority)}</span></div><strong class="block text-sm text-navy mt-1">${escapeHTML(item.title)}</strong><p class="text-[11px] text-slate-500 mt-1 leading-relaxed">${escapeHTML(item.detail)}</p></div><button onclick="switchPrivateDashboardPanel('${item.panel}')" class="shrink-0 text-[11px] font-bold text-blue">${escapeHTML(item.action)} →</button></div></article>`).join('');
    }

    function renderPrivateMatches() {
      const container = document.getElementById('private-matches-list'); if (!container) return;
      if (privateMatchesMode === 'offers') {
        const cards = privateProperties().slice(0, 8).map(property => ({ property, matches:getCompatibleNeedsForProperty(property, 5) })).filter(item => item.matches.length).slice(0, 4);
        container.innerHTML = cards.length ? cards.map(({property,matches}) => `<article class="private-mini-card"><div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-black text-blue">${escapeHTML(property.reference || property.id)}</span><strong class="block text-sm text-navy mt-1">${escapeHTML(property.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">${formatPropertyFeatures(property,true)} · C.P. ${escapeHTML(property.postalCode || 'N/D')}</span></div><span class="private-status-pill bg-green-light text-green">${matches[0].score}%</span></div><p class="text-[11px] text-slate-500 mt-3">${matches.length} demanda${matches.length===1?'':'s'} compatible${matches.length===1?'':'s'} detectada${matches.length===1?'':'s'}.</p><div class="flex flex-wrap gap-2 mt-3"><button onclick="openMapPropertyCard('${property.id}')" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-bold">Abrir captación</button><button onclick="switchPrivateDashboardPanel('demands')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-navy">Ver demandas</button></div></article>`).join('') : `<p class="text-xs text-slate-500">No se han detectado coincidencias todavía.</p>`;
      } else {
        const cards = privateNeeds().slice(0, 8).map(need => ({ need, matches:getCompatiblePropertiesForNeed(need, 5) })).filter(item => item.matches.length).slice(0, 4);
        container.innerHTML = cards.length ? cards.map(({need,matches}) => `<article class="private-mini-card"><div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-black text-green">Intención de búsqueda</span><strong class="block text-sm text-navy mt-1">${escapeHTML(need.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">Hasta ${formatCurrency(need.budget)} · C.P. ${escapeHTML(need.postalCode || 'N/D')}</span></div><span class="private-status-pill bg-green-light text-green">${matches[0].score}%</span></div><p class="text-[11px] text-slate-500 mt-3">${matches.length} captación${matches.length===1?'':'es'} compatible${matches.length===1?'':'s'} detectada${matches.length===1?'':'s'}.</p><div class="flex flex-wrap gap-2 mt-3"><button onclick="openMapNeedCard('${need.id}')" class="px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Abrir demanda</button><button onclick="switchPrivateDashboardPanel('offers')" class="px-3 py-2 rounded-lg border border-slate-200 text-[10px] font-bold text-navy">Ver propiedades</button></div></article>`).join('') : `<p class="text-xs text-slate-500">No se han detectado coincidencias todavía.</p>`;
      }
    }

    function renderPrivateOverviewOperations() {
      const tbody = document.getElementById('private-overview-operations'); if (!tbody) return;
      const state = getPrivateDashboardState();
      tbody.innerHTML = (state.operations || []).slice(0, 4).map(operation => `<tr class="border-b border-slate-100"><td class="px-4 py-3"><strong class="block text-xs text-navy">${escapeHTML(operation.id)}</strong><span class="text-[10px] text-slate-500">${escapeHTML(operation.collaborator)}</span></td><td class="px-4 py-3"><span class="private-status-pill ${privateStatusClasses(operation.status)}">${escapeHTML(operation.status)}</span></td><td class="px-4 py-3 text-[11px]">${escapeHTML(operation.nextAction)}</td><td class="px-4 py-3"><button onclick="openPrivateOperationModal('${operation.id}')" class="text-[11px] font-bold text-blue">Abrir →</button></td></tr>`).join('');
    }

    function renderPrivateOverviewTasks() {
      const container = document.getElementById('private-overview-tasks'); if (!container) return;
      const state = getPrivateDashboardState();
      const tasks = (state.tasks || []).filter(item => item.status !== 'done').slice(0, 4);
      container.innerHTML = tasks.length ? tasks.map(item => `<div class="flex items-start gap-3"><button type="button" onclick="completePrivateTask('${item.id}')" class="mt-0.5 w-5 h-5 shrink-0 rounded-md border border-slate-300 bg-white text-[10px]">✓</button><div><strong class="block text-xs text-navy">${escapeHTML(item.title)}</strong><span class="block text-[10px] text-slate-500 mt-1">${escapeHTML(item.due)} · ${privatePriorityLabel(item.priority)}</span></div></div>`).join('') : `<p class="text-xs text-slate-500">No tienes tareas pendientes.</p>`;
    }

    function renderPrivateActivity() {
      const overview = document.getElementById('private-overview-activity'); if (!overview) return;
      const state = getPrivateDashboardState();
      overview.innerHTML = (state.activities || []).slice(0, 5).map(item => `<div class="flex items-start gap-3"><span class="w-8 h-8 rounded-lg bg-blue-light text-blue flex items-center justify-center text-xs">${item.icon}</span><div><strong class="block text-xs text-navy">${escapeHTML(item.title)}</strong><span class="block text-[10px] text-slate-500 mt-1">${escapeHTML(item.detail)}</span><span class="block text-[9px] text-slate-400 mt-1">${formatRelativeTime(item.createdAt)}</span></div></div>`).join('');
    }

    function privateFavoriteCard(type, id, compact = false) {
      if (type === 'capture') {
        const property = privatePropertyById(id); if (!property) return '';
        const image = resolveMarketplaceImage(property.image, property.type);
        return `<article class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"><div class="relative ${compact ? 'h-24' : 'h-36'}"><img src="${image}" data-virtual-type="${escapeHTML(property.type)}" onerror="this.onerror=null;this.src=window.getVirtualMarketplaceImage(this.dataset.virtualType);" class="absolute inset-0 w-full h-full object-cover" alt="${escapeHTML(property.title)}" loading="lazy" /></div><div class="p-4"><span class="text-[10px] font-black text-blue">Captación · ${escapeHTML(property.reference || property.id)}</span><strong class="block text-sm text-navy mt-1 line-clamp-2">${escapeHTML(property.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">${escapeHTML(property.province || property.location)} · ${formatCurrency(property.price)}</span><div class="flex flex-wrap gap-2 mt-3"><button onclick="openMapPropertyCard('${property.id}')" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-bold">Abrir ficha</button><button onclick="toggleFavorite('capture','${property.id}')" class="px-3 py-2 rounded-lg border border-red-200 text-red-600 text-[10px] font-bold">Eliminar</button></div></div></article>`;
      }
      if (type === 'demand') {
        const need = privateNeedById(id); if (!need) return '';
        return `<article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4"><span class="text-[10px] font-black text-green">Demanda activa</span><strong class="block text-sm text-navy mt-1">${escapeHTML(need.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">${escapeHTML([need.province,need.municipality].filter(Boolean).join(' · '))} · Hasta ${formatCurrency(need.budget)}</span><div class="flex flex-wrap gap-2 mt-3"><button onclick="openMapNeedCard('${need.id}')" class="px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Abrir demanda</button><button onclick="toggleFavorite('demand','${need.id}')" class="px-3 py-2 rounded-lg border border-red-200 text-red-600 text-[10px] font-bold">Eliminar</button></div></article>`;
      }
      const match = getSalesMatchRecords().find(item => item.id === id); if (!match) return '';
      return `<article class="rounded-2xl border border-slate-200 bg-white shadow-sm p-4"><span class="text-[10px] font-black text-amber">Coincidencia de venta · ${match.score}%</span><strong class="block text-sm text-navy mt-1">${escapeHTML(match.property.title)}</strong><span class="block text-[11px] text-slate-500 mt-1">Demanda: ${escapeHTML(match.need.title)} · ${formatCurrency(match.estimatedValue)}</span><div class="flex flex-wrap gap-2 mt-3"><button onclick="openSalesMatchDetails('${match.id}')" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-bold">Ver detalles</button><button onclick="toggleFavorite('match','${match.id}')" class="px-3 py-2 rounded-lg border border-red-200 text-red-600 text-[10px] font-bold">Eliminar</button></div></article>`;
    }

    function renderPrivateFavorites() {
      const items = [
        ...getFavoriteIds('capture').map(id => ({type:'capture',id})),
        ...getFavoriteIds('demand').map(id => ({type:'demand',id})),
        ...getFavoriteIds('match').map(id => ({type:'match',id}))
      ].filter(item => privateFavoriteCard(item.type,item.id));
      const empty = `<p class="text-xs text-slate-500">Todavía no has guardado operaciones favoritas.</p>`;
      const overview = document.getElementById('private-overview-favorites'); if (overview) overview.innerHTML = items.slice(0,3).map(item => privateFavoriteCard(item.type,item.id,true)).join('') || empty;
      const grid = document.getElementById('private-favorites-grid'); if (grid) grid.innerHTML = items.map(item => privateFavoriteCard(item.type,item.id)).join('') || empty;
    }

    function removePrivateFavorite(propertyId) { toggleFavorite('capture', propertyId); }

    function renderPrivateOffers() {
      const tbody = document.getElementById('private-offers-table'); if (!tbody) return;
      const search = normalizeMatchText(document.getElementById('private-offers-search')?.value || '');
      const list = privateProperties().filter(property => !search || normalizeMatchText(`${property.reference} ${property.title} ${property.province} ${property.municipality} ${property.postalCode}`).includes(search));
      const summary = document.getElementById('private-offers-summary'); if (summary) summary.innerHTML = [ ['Publicadas',list.length,'text-blue'], ['Con solicitudes',(getPrivateDashboardState().requestsReceived||[]).length,'text-amber'], ['Coincidencias',list.reduce((sum,item)=>sum+getCompatibleNeedsForProperty(item,10).length,0),'text-green'], ['Cerradas',closedOperations.length,'text-navy'] ].map(([label,value,color])=>privateKpiCard(label,value,color,'offers')).join('');
      tbody.innerHTML = list.slice(0, 80).map(property => { const matches=getCompatibleNeedsForProperty(property,10); return `<tr class="border-b border-slate-100"><td class="px-4 py-3"><strong class="text-blue">${escapeHTML(property.reference || property.id)}</strong></td><td class="px-4 py-3"><strong class="block text-xs text-navy">${escapeHTML(property.title)}</strong><span class="text-[10px] text-slate-500">${escapeHTML(property.province || property.location)} · C.P. ${escapeHTML(property.postalCode || 'N/D')}</span></td><td class="px-4 py-3 font-bold text-navy">${formatCurrency(property.price)}</td><td class="px-4 py-3"><span class="private-status-pill ${Number(property.score)>=85?'bg-green-light text-green':'bg-blue-light text-blue'}">★ ${escapeHTML(property.score || 80)}/100</span></td><td class="px-4 py-3"><span class="private-status-pill bg-blue-light text-blue">${matches.length}</span></td><td class="px-4 py-3"><span class="private-status-pill bg-green-light text-green">Publicada</span></td><td class="px-4 py-3"><button onclick="openMapPropertyCard('${property.id}')" class="text-[11px] font-bold text-blue">Abrir →</button></td></tr>`; }).join('') || `<tr><td colspan="7" class="p-5 text-xs text-slate-500">No hay captaciones con esos criterios.</td></tr>`;
    }

    function renderPrivateDemands() {
      const tbody = document.getElementById('private-demands-table'); if (!tbody) return;
      const search = normalizeMatchText(document.getElementById('private-demands-search')?.value || '');
      const list = privateNeeds().filter(need => !search || normalizeMatchText(`${need.id} ${need.title} ${need.province} ${need.municipality} ${need.postalCode}`).includes(search));
      const summary = document.getElementById('private-demands-summary'); if (summary) summary.innerHTML = [ ['Activas',list.length,'text-navy'], ['Con coincidencias',list.filter(item=>getCompatiblePropertiesForNeed(item,10).length).length,'text-green'], ['Sin resultados',list.filter(item=>!getCompatiblePropertiesForNeed(item,10).length).length,'text-amber'], ['Solicitudes enviadas',(getPrivateDashboardState().requestsSent||[]).length,'text-blue'] ].map(([label,value,color])=>privateKpiCard(label,value,color,'demands')).join('');
      tbody.innerHTML = list.slice(0, 80).map(need => { const matches=getCompatiblePropertiesForNeed(need,10); return `<tr class="border-b border-slate-100"><td class="px-4 py-3"><strong class="text-green">${escapeHTML(need.id)}</strong></td><td class="px-4 py-3"><strong class="block text-xs text-navy">${escapeHTML(need.title)}</strong><span class="text-[10px] text-slate-500">${escapeHTML(need.province || '')} · C.P. ${escapeHTML(need.postalCode || 'N/D')} · ${formatPropertyFeatures(need,true)}</span></td><td class="px-4 py-3 font-bold text-navy">Hasta ${formatCurrency(need.budget)}</td><td class="px-4 py-3"><span class="private-status-pill ${matches.length?'bg-green-light text-green':'bg-amber-light text-amber'}">${matches.length}</span></td><td class="px-4 py-3"><span class="private-status-pill bg-green-light text-green">Activa</span></td><td class="px-4 py-3"><button onclick="openHomeNeedMatches('${need.id}')" class="text-[11px] font-bold text-blue">Abrir →</button></td></tr>`; }).join('') || `<tr><td colspan="6" class="p-5 text-xs text-slate-500">No hay demandas con esos criterios.</td></tr>`;
    }

    function requestCard(item, received = false) {
      const property = privatePropertyById(item.propertyId);
      return `<article class="private-mini-card"><div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-black text-blue">${escapeHTML(property?.reference || item.propertyId)}</span><strong class="block text-sm text-navy mt-1">${escapeHTML(property?.title || 'Captación')}</strong><span class="block text-[10px] text-slate-500 mt-1">${escapeHTML(item.agency)} · ${formatRelativeTime(item.createdAt)}</span></div><span class="private-status-pill ${privateStatusClasses(item.status)}">${escapeHTML(item.status)}</span></div><p class="text-[11px] text-slate-500 mt-3">${escapeHTML(item.note)}</p><div class="flex flex-wrap gap-2 mt-3">${received && item.status.includes('Pendiente') ? `<button onclick="confirmPrivateRequest('${item.id}')" class="px-3 py-2 rounded-lg bg-green text-white text-[10px] font-bold">Confirmar disponibilidad</button>` : ''}<button onclick="openMapPropertyCard('${item.propertyId}')" class="px-3 py-2 rounded-lg border border-slate-200 text-navy text-[10px] font-bold">Abrir captación</button></div></article>`;
    }
    function renderPrivateRequests() { const state=getPrivateDashboardState(); const received=document.getElementById('private-requests-received'); const sent=document.getElementById('private-requests-sent'); if(received) received.innerHTML=(state.requestsReceived||[]).map(item=>requestCard(item,true)).join('')||`<p class="text-xs text-slate-500">No hay solicitudes recibidas.</p>`; if(sent) sent.innerHTML=(state.requestsSent||[]).map(item=>requestCard(item,false)).join('')||`<p class="text-xs text-slate-500">No hay solicitudes enviadas.</p>`; }
    function confirmPrivateRequest(id) { const state=getPrivateDashboardState(); const item=(state.requestsReceived||[]).find(row=>row.id===id); if(!item)return; item.status='Disponible · Acuerdo de Confidencialidad (NDA) pendiente'; state.activities.unshift({id:`ACT-${Date.now()}`,icon:'✓',title:'Disponibilidad confirmada',detail:'Se ha activado el flujo protegido del Acuerdo de Confidencialidad (NDA).',createdAt:Date.now()}); persistPrivateDashboardState(state); addPrivateNotification({category:'Operaciones',title:'Acuerdo de Confidencialidad (NDA) pendiente tras confirmar disponibilidad',detail:'La operación asociada a la solicitud confirmada requiere preparar el acuerdo de confidencialidad.',target:'operations',dueAt:Date.now()+3600000*4,dedupeKey:`notif-nda-${id}`}); addPrivateTask({title:'Preparar Acuerdo de Confidencialidad (NDA) de la solicitud confirmada',detail:'Agenda la firma y valida las siguientes tareas del expediente protegido.',priority:'high',due:'Hoy',dueAt:Date.now()+3600000*8,target:'operations',dedupeKey:`task-nda-${id}`}); renderDashboard(); showToast('Disponibilidad confirmada. El siguiente paso es gestionar el Acuerdo de Confidencialidad (NDA).', 'success'); }

    function renderPrivateOperations() {
      const tbody=document.getElementById('private-operations-table'); if(!tbody)return; const state=getPrivateDashboardState(); const filter=document.getElementById('private-operation-status-filter')?.value||''; const list=(state.operations||[]).filter(item=>!filter||item.status===filter);
      tbody.innerHTML=list.map(operation=>{const property=privatePropertyById(operation.propertyId);const need=privateNeedById(operation.needId);return `<tr class="border-b border-slate-100"><td class="px-4 py-3"><strong class="text-blue">${escapeHTML(operation.id)}</strong></td><td class="px-4 py-3"><strong class="block text-xs text-navy">${escapeHTML(property?.title||'Captación')}</strong><span class="text-[10px] text-slate-500">${escapeHTML(need?.title||'Demanda vinculada')}</span></td><td class="px-4 py-3">${escapeHTML(operation.collaborator)}</td><td class="px-4 py-3"><span class="private-status-pill ${privateStatusClasses(operation.status)}">${escapeHTML(operation.status)}</span></td><td class="px-4 py-3">${formatRelativeTime(operation.updatedAt)}</td><td class="px-4 py-3">${escapeHTML(operation.nextAction)}</td><td class="px-4 py-3"><button onclick="openPrivateOperationModal('${operation.id}')" class="text-[11px] font-bold text-blue">Expediente →</button></td></tr>`}).join('')||`<tr><td colspan="7" class="p-5 text-xs text-slate-500">No existen operaciones con ese estado.</td></tr>`;
    }
    function openPrivateOperationModal(operationId){const state=getPrivateDashboardState();const operation=(state.operations||[]).find(item=>item.id===operationId);if(!operation)return;const property=privatePropertyById(operation.propertyId);const need=privateNeedById(operation.needId);const modal=document.getElementById('private-operation-modal');const title=document.getElementById('private-operation-modal-title');const body=document.getElementById('private-operation-modal-body');if(title)title.textContent=`${operation.id} · ${operation.status}`;if(body)body.innerHTML=`<div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><div class="private-mini-card"><span class="text-[10px] text-slate-500">Propiedad</span><strong class="block text-sm text-navy mt-1">${escapeHTML(property?.title||'Captación')}</strong></div><div class="private-mini-card"><span class="text-[10px] text-slate-500">Demanda</span><strong class="block text-sm text-navy mt-1">${escapeHTML(need?.title||'Demanda vinculada')}</strong></div><div class="private-mini-card"><span class="text-[10px] text-slate-500">Colaborador</span><strong class="block text-sm text-navy mt-1">${escapeHTML(operation.collaborator)}</strong></div><div class="private-mini-card"><span class="text-[10px] text-slate-500">Próxima acción</span><strong class="block text-sm text-navy mt-1">${escapeHTML(operation.nextAction)}</strong></div></div><div class="mt-5"><h4 class="text-sm font-black text-navy">Línea temporal</h4><div class="mt-3 space-y-3"><div class="private-mini-card"><strong class="block text-xs text-navy">Operación creada</strong><span class="text-[10px] text-slate-500">${privateSafeDate(operation.createdAt)}</span></div><div class="private-mini-card"><strong class="block text-xs text-navy">Última actualización</strong><span class="text-[10px] text-slate-500">${privateSafeDate(operation.updatedAt)} · ${escapeHTML(operation.status)}</span></div></div></div>`;modal?.classList.remove('hidden');}
    function closePrivateOperationModal(){document.getElementById('private-operation-modal')?.classList.add('hidden');}

    function renderPrivateTasks(){const container=document.getElementById('private-tasks-list');if(!container)return;const state=getPrivateDashboardState();container.innerHTML=(state.tasks||[]).map(item=>`<article class="private-section-card p-4 ${item.status==='done'?'opacity-60':''}"><div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3"><div class="flex items-start gap-3"><button onclick="completePrivateTask('${item.id}')" class="mt-0.5 w-6 h-6 rounded-lg border border-slate-300 bg-white text-[11px]">${item.status==='done'?'✓':''}</button><div><div class="flex flex-wrap items-center gap-2"><strong class="text-sm text-navy">${escapeHTML(item.title)}</strong><span class="private-status-pill ${item.priority==='high'?'bg-red-50 text-red-600':item.priority==='medium'?'bg-amber-light text-amber':'bg-blue-light text-blue'}">${privatePriorityLabel(item.priority)}</span></div><p class="text-[11px] text-slate-500 mt-1">${escapeHTML(item.detail)}</p><span class="block text-[10px] text-slate-400 mt-2">${escapeHTML(item.due)}</span></div></div><button onclick="switchPrivateDashboardPanel('${item.target||'overview'}')" class="text-[11px] font-bold text-blue">Abrir contexto →</button></div></article>`).join('');}
    function completePrivateTask(id){const state=getPrivateDashboardState();const item=(state.tasks||[]).find(row=>row.id===id);if(!item)return;item.status=item.status==='done'?'pending':'done';persistPrivateDashboardState(state);renderDashboard();showToast(item.status==='done'?'Tarea completada.':'Tarea reactivada.','success');}

    function openPrivateNotificationContext(id){const state=getPrivateDashboardState();const item=(state.notifications||[]).find(row=>row.id===id);if(!item)return;if(item.propertyId){openMapPropertyCard(item.propertyId);return}switchPrivateDashboardPanel(item.target||'overview')}
    function renderPrivateNotifications(){const container=document.getElementById('private-notifications-list');if(!container)return;const state=getPrivateDashboardState();container.innerHTML=(state.notifications||[]).map(item=>`<article class="private-section-card p-4 ${item.read?'opacity-70':''}"><div class="flex items-start justify-between gap-4"><div><span class="text-[10px] font-black uppercase tracking-wider text-blue">${escapeHTML(item.category)}</span><strong class="block text-sm text-navy mt-1">${escapeHTML(item.title)}</strong><p class="text-[11px] text-slate-500 mt-1">${escapeHTML(item.detail)}</p><span class="block text-[10px] text-slate-400 mt-2">${formatRelativeTime(item.createdAt)}</span></div><div class="flex flex-col gap-2 items-end">${!item.read?`<button onclick="markPrivateNotificationRead('${item.id}')" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-bold">Marcar leída</button>`:''}<button onclick="openPrivateNotificationContext('${item.id}')" class="text-[10px] font-bold text-blue">${item.propertyId?'Ver captación':'Abrir'} →</button></div></div></article>`).join('');}
    function markPrivateNotificationRead(id){const state=getPrivateDashboardState();const item=(state.notifications||[]).find(row=>row.id===id);if(item)item.read=true;persistPrivateDashboardState(state);renderDashboard();}
    function markAllPrivateNotificationsRead(){const state=getPrivateDashboardState();(state.notifications||[]).forEach(item=>item.read=true);persistPrivateDashboardState(state);renderDashboard();showToast('Notificaciones marcadas como leídas.','success');}

    const FISCAL_PROFILE_FIELDS = { legalName:'fiscal-legal-name', tradeName:'fiscal-trade-name', profileType:'fiscal-profile-type', taxId:'fiscal-tax-id', billingEmail:'fiscal-billing-email', phone:'fiscal-phone', address:'fiscal-address', postalCode:'fiscal-postal-code', ccaa:'fiscal-ccaa', municipality:'fiscal-municipality', province:'fiscal-province', country:'fiscal-country', activity:'fiscal-activity', website:'fiscal-website', notes:'fiscal-notes' };

    function renderPrivateFiscalProfile() {
      const profile = getPrivateDashboardState().fiscalProfile || {};
      Object.entries(FISCAL_PROFILE_FIELDS).forEach(([key,id]) => { const element=document.getElementById(id); if(element && !['ccaa','province','municipality'].includes(key)) element.value=profile[key] || ''; });
      TerritorySelector.instances['fiscal-profile']?.setValues({ccaa:profile.ccaa||'',province:profile.province||'',municipality:profile.municipality||'',postalCode:profile.postalCode||''});
      const status=document.getElementById('fiscal-profile-status');
      if(status){const completed=Object.keys(FISCAL_PROFILE_FIELDS).filter(key=>String(profile[key]||'').trim()).length;status.textContent=completed ? `${completed} de ${Object.keys(FISCAL_PROFILE_FIELDS).length} campos completados. Los restantes aparecen como “Pendiente de completar”.` : 'Pendiente de completar';}
    }

    function savePrivateFiscalProfile(event) {
      event.preventDefault();
      if (!requireRegisteredAction('guardar el perfil profesional')) return;
      const state=getPrivateDashboardState(); const profile={};
      Object.entries(FISCAL_PROFILE_FIELDS).forEach(([key,id])=>{profile[key]=cleanText(document.getElementById(id)?.value||'');});
      const fiscalTerritory=TerritorySelector.instances['fiscal-profile']?.getValue()||{};
      profile.ccaa=fiscalTerritory.ccaa||profile.ccaa||''; profile.province=fiscalTerritory.province||profile.province||''; profile.municipality=fiscalTerritory.municipality||profile.municipality||''; profile.postalCode=fiscalTerritory.postalCode||profile.postalCode||''; profile.territory=fiscalTerritory;
      profile.updatedAt=Date.now(); state.fiscalProfile=profile; persistPrivateDashboardState(state);
      const email=getDemoSession?.()?.email||'';
      persistWpRecord('user_preferences',profile,{recordKey:`fiscal-profile-${email||'guest'}`,userEmail:email,title:'Perfil profesional y fiscal',status:'active'});
      renderPrivateFiscalProfile(); addPrivateActivity('✓','Perfil profesional actualizado','Se han guardado los datos profesionales y fiscales privados.');
      showToast('Perfil profesional guardado correctamente.','success');
    }

    function getCurrentPlanType() { return marketplaceAccessState?.plan_type || CAPTACION_MAILCHIMP?.accessState?.plan_type || getDemoSession?.()?.planType || 'basic'; }

    function applyDashboardPlanAccess() {
      const plan = getCurrentPlanType();
      const premium = plan === 'premium';
      const badge = document.getElementById('private-plan-access-badge');
      if (badge) badge.textContent = `${marketplacePlanLabel(plan)} · ${Number(marketplaceAccessState?.remaining_marketplace_accesses || 0)} accesos`;
      document.getElementById('private-tasks-premium-content')?.classList.toggle('hidden', !premium);
      document.getElementById('private-tasks-premium-lock')?.classList.toggle('hidden', premium);
      document.getElementById('private-overview-calendar-section')?.classList.toggle('hidden', !premium);
    }

    function getNewTaskModal() {
      let modal = document.getElementById('new-private-task-modal');
      if (modal) return modal;
      modal = document.createElement('div');
      modal.id = 'new-private-task-modal';
      modal.className = 'fixed inset-0 z-[140] hidden flex items-center justify-center p-4 bg-navy-dark/70 backdrop-blur-sm';
      modal.innerHTML = `<div class="relative w-full max-w-xl max-h-[92vh] overflow-y-auto rounded-3xl bg-white border border-slate-200 shadow-2xl p-6 sm:p-8"><button type="button" onclick="closeNewTaskModal()" class="absolute top-3 right-4 text-slate-400 text-xl font-black">x</button><span class="inline-flex px-3 py-1 rounded-full bg-blue-light text-blue text-[10px] font-black uppercase">Calendario Premium</span><h3 class="text-xl font-black text-navy mt-4">Añadir nueva tarea</h3><form onsubmit="submitNewPrivateTask(event)" class="mt-5 space-y-4"><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Título de la tarea *</span><input id="new-task-title" required minlength="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label><div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label><span class="block text-xs font-bold text-slate-500 mb-1">Fecha *</span><input id="new-task-date" type="date" required class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label><label><span class="block text-xs font-bold text-slate-500 mb-1">Hora opcional</span><input id="new-task-time" type="time" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm" /></label></div><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Descripción opcional</span><textarea id="new-task-description" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm"></textarea></label><label class="block"><span class="block text-xs font-bold text-slate-500 mb-1">Relacionar con</span><select id="new-task-related" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm bg-white"><option value="">Sin relación</option></select></label><div class="grid grid-cols-1 sm:grid-cols-2 gap-3"><label><span class="block text-xs font-bold text-slate-500 mb-1">Recordatorio</span><select id="new-task-reminder" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm bg-white"><option value="none">Sin recordatorio</option><option value="15m">15 minutos antes</option><option value="1h">1 hora antes</option><option value="1d">1 día antes</option></select></label><label><span class="block text-xs font-bold text-slate-500 mb-1">Canal</span><select id="new-task-channel" class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm bg-white"><option value="panel">Panel</option><option value="email">Email</option><option value="whatsapp_todo">WhatsApp futuro / TODO</option></select></label></div><p id="new-task-error" class="hidden rounded-xl bg-red-50 px-3 py-2 text-xs text-red-700"></p><button class="w-full py-3 rounded-xl bg-blue text-white text-xs font-black">Guardar tarea</button></form></div>`;
      document.body.appendChild(modal);
      return modal;
    }

    function openNewTaskModal() {
      if (getCurrentPlanType() !== 'premium') { showToast('El calendario avanzado está disponible en Premium.', 'info'); return; }
      const modal = getNewTaskModal();
      const related = document.getElementById('new-task-related');
      const options = [...properties.map(item=>({id:item.id,label:`Captación: ${item.title}`})),...needs.map(item=>({id:item.id,label:`Demanda: ${item.title}`})),...(getPrivateDashboardState().operations||[]).map(item=>({id:item.id,label:`Operación: ${item.title||item.id}`}))];
      related.innerHTML = '<option value="">Sin relación</option>' + options.map(item=>`<option value="${escapeHTML(item.id)}">${escapeHTML(item.label)}</option>`).join('');
      document.getElementById('new-task-date').value = new Date().toISOString().slice(0,10);
      modal.classList.remove('hidden');
    }

    function closeNewTaskModal() { getNewTaskModal().classList.add('hidden'); }

    async function submitNewPrivateTask(event) {
      event.preventDefault();
      const payload = {title:cleanText(document.getElementById('new-task-title').value),date:document.getElementById('new-task-date').value,time:document.getElementById('new-task-time').value,description:cleanText(document.getElementById('new-task-description').value),related_id:document.getElementById('new-task-related').value,reminder:document.getElementById('new-task-reminder').value,channel:document.getElementById('new-task-channel').value};
      const errorBox = document.getElementById('new-task-error');
      try {
        const response = await fetch(CAPTACION_MAILCHIMP.tasksEndpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':CAPTACION_MAILCHIMP.nonce},body:JSON.stringify(payload)});
        const data = await response.json();
        if(!response.ok||!data?.ok) throw new Error(data?.message||'No se pudo guardar la tarea.');
        addPrivateTask({title:data.task.title,detail:data.task.description||'Tarea de agenda',due:new Date(data.task.dueAt).toLocaleString('es-ES'),dueAt:data.task.dueAt,target:'tasks',priority:'medium',dedupeKey:data.task.id});
        event.target.reset(); closeNewTaskModal(); renderDashboard(); showToast(data.message,'success');
      } catch(error) { errorBox.textContent=error.message; errorBox.classList.remove('hidden'); }
    }

    function linkExternalCalendar() {
      if (getCurrentPlanType() !== 'premium') { showToast('La vinculación de calendario está disponible en Premium.', 'info'); return; }
      // TODO/FIXME PREPRODUCCION: configurar OAuth de Google Calendar en servidor. ICS funciona sin compartir credenciales.
      showToast('Google Calendar OAuth está pendiente de configuración. Puedes usar Exportar ICS de forma compatible.', 'info');
    }

    async function loadWordPressTasks() {
      if (!CAPTACION_MAILCHIMP?.loggedIn || getCurrentPlanType() !== 'premium' || !CAPTACION_MAILCHIMP?.tasksEndpoint) return;
      try {
        const response=await fetch(CAPTACION_MAILCHIMP.tasksEndpoint,{credentials:'same-origin',headers:{'X-WP-Nonce':CAPTACION_MAILCHIMP.nonce}}); const data=await response.json(); if(!response.ok||!data?.ok)return;
        const state=getPrivateDashboardState(); const existing=new Set((state.tasks||[]).map(item=>item.dedupeKey||item.id));
        (data.tasks||[]).forEach(row=>{const task=row.payload||{};if(existing.has(task.id))return;state.tasks.push({id:task.id,title:task.title,detail:task.description||'',dueAt:Number(task.dueAt)||Date.now(),due:new Date(Number(task.dueAt)||Date.now()).toLocaleString('es-ES'),priority:'medium',status:task.status||'pending',target:'tasks',dedupeKey:task.id});}); persistPrivateDashboardState(state); renderDashboard();
      } catch(error) {}
    }

    function getProfessionalDisplayName() {
      const session = getDemoSession?.() || {};
      const wpUser = CAPTACION_MAILCHIMP?.currentUser || {};
      const email = String(session.email || wpUser.email || '').trim();
      const displayName = String(session.name || wpUser.displayName || wpUser.name || '').trim();
      const fullName = [wpUser.firstName, wpUser.lastName].map(value => String(value || '').trim()).filter(Boolean).join(' ');
      const username = String(wpUser.username || '').trim();
      return (displayName && displayName.toLowerCase() !== email.toLowerCase()) ? displayName : (fullName || username || email || 'Agente profesional');
    }

    function syncPrivateProfile(){const session=getDemoSession?.()||{};const name=getProfessionalDisplayName();const agency=session.agency||'Captacion.app';['private-dashboard-agent-name','private-profile-name'].forEach(id=>{const el=document.getElementById(id);if(el)el.textContent=name;});['private-dashboard-agent-agency','private-profile-agency'].forEach(id=>{const el=document.getElementById(id);if(el)el.textContent=agency;});renderPrivateFiscalProfile();}

    function renderExecutiveDashboard() {
      const area = document.getElementById('page-area-privada');
      if (area && privateDashboardPanel === 'overview') area.classList.add('executive-mode');
      const state = getPrivateDashboardState();
      const operations = state.operations || [];
      const activeOperations = operations.filter(item => !['Completada','Cancelada'].includes(item.status));
      const completedOperations = operations.filter(item => item.status === 'Completada').length + closedOperations.length;
      const salesMatches = getSalesMatchRecords();
      const captureValue = properties.reduce((sum,item)=>sum+(Number(item.price)||0),0);
      const demandValue = needs.reduce((sum,item)=>sum+(Number(item.budget)||0),0);
      const matchValue = salesMatches.reduce((sum,item)=>sum+(Number(item.estimatedValue)||0),0);
      const activeValue = activeOperations.reduce((sum,item)=>sum+linkedPropertyValue(item),0);
      const totalPipeline = captureValue + demandValue + matchValue + activeValue;
      const favoriteCount = getFavoriteIds('capture').length + getFavoriteIds('demand').length + getFavoriteIds('match').length;
      const values = {
        'exec-kpi-offers':properties.length, 'exec-kpi-demands':needs.length, 'exec-kpi-matches':salesMatches.length, 'exec-kpi-operations':activeOperations.length,
        'exec-kpi-offers-value':`${formatCurrency(captureValue)} estimados`, 'exec-kpi-demands-value':`${formatCurrency(demandValue)} estimados`, 'exec-kpi-matches-value':`${formatCurrency(matchValue)} estimados`, 'exec-kpi-operations-value':`${formatCurrency(activeValue)} estimados`,
        'exec-pipeline-value':formatCurrency(totalPipeline), 'exec-total-opportunities':properties.length+needs.length+(state.requestsReceived||[]).length+salesMatches.length,
        'exec-legend-offers':`(${properties.length})`, 'exec-legend-demands':`(${needs.length})`, 'exec-legend-requests':`(${(state.requestsReceived||[]).length})`, 'exec-legend-matches':`(${salesMatches.length})`,
        'exec-funnel-offers':properties.length, 'exec-funnel-requests':(state.requestsReceived||[]).length, 'exec-funnel-matches':salesMatches.length, 'exec-funnel-operations':activeOperations.length, 'exec-funnel-closed':completedOperations,
        'exec-requests-count':(state.requestsReceived||[]).length, 'exec-unread-count':(state.notifications||[]).filter(item=>!item.read).length, 'exec-favorites-count':favoriteCount, 'exec-clients-count':(state.clients||[]).length, 'exec-leads-count':(state.leads||[]).filter(item=>item.status!=='Convertido').length, 'exec-tasks-count':(state.tasks||[]).filter(item=>item.status!=='done').length
      };
      Object.entries(values).forEach(([id,value])=>{const element=document.getElementById(id);if(element)element.textContent=String(value);});
      const requestsBox = document.getElementById('exec-latest-requests');
      const matchesBox = document.getElementById('exec-latest-matches');
      const tasksBox = document.getElementById('exec-pending-tasks');
      const requestRows = (state.requestsReceived||[]).slice(0,3).map(item=>{const property=privatePropertyById(item.propertyId);const name=item.agency||'Grupo inversor';return{initials:name.split(/\s+/).slice(0,2).map(part=>part[0]||'').join('').toUpperCase(),name,detail:item.note||property?.title||'Solicitud de información',time:formatRelativeTime(item.createdAt),status:item.status||'Nueva'};});
      if(requestsBox)requestsBox.innerHTML=requestRows.map((item,index)=>`<button type="button" onclick="openExecutiveDestination('requests')" class="exec-row exec-clickable" aria-label="Abrir solicitud de ${escapeHTML(item.name)}, ${escapeHTML(item.time)}"><span class="exec-avatar ${index===1?'green':''}">${escapeHTML(item.initials)}</span><span class="exec-row-copy"><strong>${escapeHTML(item.name)}</strong><span>${escapeHTML(item.detail)}</span></span><span class="exec-row-meta">${escapeHTML(item.time)}<br><i class="exec-pill">${escapeHTML(item.status)}</i></span></button>`).join('')||`<button type="button" onclick="openExecutiveDestination('requests')" class="exec-row exec-clickable" aria-label="Abrir solicitudes"><span class="exec-row-copy"><strong>No hay solicitudes recientes</strong><span>Accede a la bandeja para revisar su estado.</span></span></button>`;
      const matchRows=salesMatches.slice(0,3).map(item=>({title:item.property?.title||'Coincidencia inmobiliaria',location:[item.property?.province,item.property?.municipality].filter(Boolean).join(', ')||'España',time:formatRelativeTime(item.date),property:item.property}));
      if(matchesBox)matchesBox.innerHTML=matchRows.map(item=>{const image=resolveMarketplaceImage(item.property?.image,item.property?.type||'Activo inmobiliario');return`<button type="button" onclick="openMapPropertyCard('${escapeHTML(String(item.property?.id||''))}')" class="exec-row exec-clickable" aria-label="Abrir coincidencia ${escapeHTML(item.title)}"><img class="exec-thumb" src="${escapeHTML(image)}" alt="${escapeHTML(item.title)}" loading="lazy"><span class="exec-row-copy"><strong>${escapeHTML(item.title)}</strong><span>${escapeHTML(item.location)}</span></span><span class="exec-row-meta"><i class="exec-pill green">Nueva</i><br>${escapeHTML(item.time)}</span></button>`;}).join('')||`<button type="button" onclick="openExecutiveDestination('matches')" class="exec-row exec-clickable" aria-label="Abrir coincidencias"><span class="exec-row-copy"><strong>No hay coincidencias recientes</strong><span>Consulta el motor de coincidencias.</span></span></button>`;
      const taskRows=(state.tasks||[]).filter(item=>item.status!=='done').slice(0,4);
      if(tasksBox)tasksBox.innerHTML=taskRows.map((item,index)=>`<button type="button" onclick="openExecutiveDestination('tasks')" class="exec-row exec-clickable" aria-label="Abrir tarea ${escapeHTML(item.title)}"><span class="exec-task-check">✓</span><span class="exec-row-copy"><strong>${escapeHTML(item.title)}</strong></span><span class="exec-row-meta" style="${index===0?'color:#f05a78':''}">${escapeHTML(item.due||'Pendiente')}</span></button>`).join('')||`<button type="button" onclick="openExecutiveDestination('tasks')" class="exec-row exec-clickable" aria-label="Abrir tareas"><span class="exec-row-copy"><strong>No hay tareas pendientes</strong><span>Tu agenda está al día.</span></span></button>`;
    }

    function loadExecutivePdfLibrary() {
      if(window.html2pdf)return Promise.resolve(window.html2pdf);
      return new Promise((resolve,reject)=>{const existing=document.getElementById('captacion-html2pdf');if(existing){existing.addEventListener('load',()=>resolve(window.html2pdf),{once:true});existing.addEventListener('error',()=>reject(new Error('No se pudo cargar el generador PDF.')),{once:true});return;}const script=document.createElement('script');script.id='captacion-html2pdf';script.src='https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';script.async=true;script.onload=()=>resolve(window.html2pdf);script.onerror=()=>reject(new Error('No se pudo cargar el generador PDF.'));document.head.appendChild(script);});
    }

    async function exportExecutiveDashboard() {
      const dashboard=document.querySelector('#private-panel-overview .exec-dashboard');const button=document.getElementById('exec-export-button');if(!dashboard||!button)return;
      const today=new Date();const isoDate=today.toISOString().slice(0,10);const meta=document.createElement('div');meta.className='exec-pdf-meta';meta.innerHTML=`<span>Generado: ${today.toLocaleString('es-ES')}</span><span>Profesional: ${escapeHTML(getProfessionalDisplayName())}</span>`;dashboard.querySelector('.exec-head')?.insertAdjacentElement('afterend',meta);dashboard.classList.add('exec-exporting');button.disabled=true;button.textContent='Generando PDF…';
      try{const html2pdf=await loadExecutivePdfLibrary();await html2pdf().set({margin:[8,8,8,8],filename:`resumen-ejecutivo-captacion-app-${isoDate}.pdf`,image:{type:'jpeg',quality:.96},html2canvas:{scale:2,useCORS:true,backgroundColor:getCurrentTheme()==='dark'?'#08172b':'#f5f8fc',scrollY:0},jsPDF:{unit:'mm',format:'a4',orientation:'portrait'},pagebreak:{mode:['css','legacy'],avoid:['.exec-card','.exec-kpi','.exec-row']}}).from(dashboard).save();showToast('Resumen ejecutivo exportado en PDF.','success');}
      catch(error){showToast(error.message||'No se pudo generar el PDF.','info');}
      finally{meta.remove();dashboard.classList.remove('exec-exporting');button.disabled=false;button.innerHTML='<span aria-hidden="true">⇩</span> Exportar PDF';}
    }

    async function purchaseAccessPack() {
      try {
        const response=await fetch(CAPTACION_MAILCHIMP.accessPurchaseEndpoint,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-WP-Nonce':CAPTACION_MAILCHIMP.nonce},body:'{}'});
        const data=await response.json(); if(!response.ok||!data?.ok)throw new Error(data?.message||'No se pudo iniciar la compra.');
        if(data.checkoutConfigured&&data.checkoutUrl){window.open(data.checkoutUrl,'_blank','noopener,noreferrer');showToast('Checkout abierto. El saldo se actualizará cuando Stripe confirme el pago.','info');}
        else showToast(data.message||'Configura el Payment Link de Stripe para activar los packs.','info');
      } catch(error){showToast(error.message||'No se pudo iniciar la compra.','info');}
    }

    function renderAccessDashboard() {
      const summary=document.getElementById('private-access-summary'); const activity=document.getElementById('private-month-activity'); const history=document.getElementById('private-access-history');
      if(!summary||!activity||!history)return;
      const state=getPrivateDashboardState(); const available=Number(marketplaceAccessState?.remaining_marketplace_accesses||0); const consumed=Number(marketplaceAccessState?.monthly_consumed_accesses||0); const percentage=Number(marketplaceAccessState?.usage_percentage||0);
      const pack=marketplaceAccessState?.plan_type==='premium'?30:15; const canPack=['professional_plus','premium'].includes(marketplaceAccessState?.plan_type);
      summary.innerHTML=`<div class="grid grid-cols-3 gap-3 text-center"><div><strong class="block text-2xl text-blue">${available}</strong><span class="text-[11px] text-slate-500">Disponibles</span></div><div><strong class="block text-2xl text-navy">${consumed}</strong><span class="text-[11px] text-slate-500">Consumidos</span></div><div><strong class="block text-2xl text-navy">${percentage}%</strong><span class="text-[11px] text-slate-500">Utilizado</span></div></div><div class="mt-4 h-2.5 rounded-full bg-slate-200 overflow-hidden"><div class="h-full rounded-full ${percentage>=90?'bg-amber':'bg-blue'}" style="width:${Math.min(100,percentage)}%"></div></div>${canPack?`<button type="button" onclick="purchaseAccessPack()" class="mt-4 text-xs font-black text-blue">Añadir ${pack} accesos por 5 €</button>`:''}`;
      activity.innerHTML=[['Oportunidades',consumed],['Coincidencias',getSalesMatchRecords().length],['Contactos',(state.requestsSent||[]).length]].map(([label,value])=>`<div class="rounded-xl bg-slate-50 border border-slate-200 p-3 text-center"><strong class="block text-xl text-navy">${value}</strong><span class="text-[10px] text-slate-500">${label}</span></div>`).join('');
      history.innerHTML=marketplaceAccessHistory.length?`<table class="w-full min-w-[620px] text-left text-xs"><thead class="bg-slate-50 text-slate-500"><tr><th class="p-3">Fecha</th><th class="p-3">Oportunidad consultada</th><th class="p-3">Acceso</th><th class="p-3">Saldo restante</th></tr></thead><tbody>${marketplaceAccessHistory.map(row=>{const property=privatePropertyById(row.opportunity_id);return`<tr class="border-t border-slate-200"><td class="p-3">${escapeHTML(new Date(String(row.created_at).replace(' ','T')).toLocaleString('es-ES'))}</td><td class="p-3 font-bold text-navy">${escapeHTML(property?.title||row.opportunity_id)}</td><td class="p-3">1 consumido</td><td class="p-3">${Number(row.balance_remaining||0)}</td></tr>`}).join('')}</tbody></table>`:'<p class="p-5 text-sm text-slate-500">Todavía no has desbloqueado oportunidades.</p>';
      let alert=''; if(percentage>=100)alert='Has consumido todos los accesos incluidos en tu plan.';else if(percentage>=90)alert='Te quedan pocos accesos disponibles. Considera ampliar tu capacidad.';else if(percentage>=75)alert='Has utilizado gran parte de tus accesos mensuales.';
      if(alert&&sessionStorage.getItem('captacion_access_alert')!==String(percentage)){sessionStorage.setItem('captacion_access_alert',String(percentage));showToast(alert,'info');}
    }

    function renderDashboard() {
      syncPrivateProfile();
      renderExecutiveDashboard();
      renderAccessDashboard();
      renderPrivateKPIs(); renderPrivateAttention(); renderPrivateMatches(); renderPrivateOverviewOperations(); renderPrivateOverviewTasks(); renderPrivateActivity(); renderPrivateFavorites(); renderPrivateOffers(); renderPrivateDemands(); renderPrivateRequests(); renderPrivateOperations(); renderPrivateTasks(); renderPrivateNotifications();
      renderPrivateAgendaCalendar('private-overview-calendar', 'private-overview-calendar-events', 5);
      renderPrivateAgendaCalendar('private-tasks-calendar', 'private-tasks-calendar-events', 10);
      applyDashboardPlanAccess();
    }

    function applyInternalPilotMessaging() {}


    // --- INICIALIZADOR DE LA PLATAFORMA ---
    function showEmailVerificationResult() {
      const url = new URL(window.location.href);
      const result = url.searchParams.get('email_verification');
      if (!result) return;
      showToast(
        result === 'success'
          ? 'Correo confirmado. Ya puedes iniciar sesion y acceder a tu cuenta.'
          : 'El enlace de verificacion no es valido o ha caducado. Solicita un nuevo correo.',
        result === 'success' ? 'success' : 'error'
      );
      url.searchParams.delete('email_verification');
      window.history.replaceState({}, '', `${url.pathname}${url.search}${url.hash || '#/inicio'}`);
    }

    function initApp() {
      applyTheme(getCurrentTheme(), false);
      const storedSession = getDemoSession();
      if (storedSession && !storedSession.emailVerified) localStorage.removeItem('captacion_demo_session_v4');
      ensureWordPressSession();
      startRegistrationPromptCycle();
      window.addEventListener('hashchange', handleRoute);
      if (!window.location.hash) {
        try {
          window.location.hash = '#/inicio';
        } catch (e) {}
      }
      handleRoute();
      repairMojibakeInDOM();
      initGeoSelectors();
      initTerritorySelectors();
      updatePropertyFormDynamics('need');
      updatePropertyFormDynamics('offer');
      filterNeeds();
      renderMarketplace();
      renderDashboard();
      renderHome();
      applyInternalPilotMessaging();
      calculateSplit();
      initResourcesToolbox();
      showEmailVerificationResult();
      activateProfessionalMembershipFromReturn();
      loadPrivateXmlUrl();
      renderAIConnections();
      fetchMarketplaceAccessState().then(() => { applyDashboardPlanAccess(); loadWordPressTasks(); }).catch(() => applyDashboardPlanAccess());
      loadWordPressRealEstateRecords();
      document.getElementById('menu-btn')?.addEventListener('click', toggleMenu);
      removeLegacyCookiePreferences();
      window.addEventListener('storage', () => {
        try { properties = (JSON.parse(localStorage.getItem('captacion_properties_v3')) || []).map(normalizePropertyRecord); } catch (e) {}
        try { needs = (JSON.parse(localStorage.getItem('captacion_needs_v3')) || []).map(normalizeNeedRecord); } catch (e) {}
        try { closedOperations = JSON.parse(localStorage.getItem('captacion_closed_operations_v4')) || []; } catch (e) {}
        renderMarketplace();
        renderDashboard();
        filterNeeds();
        renderHome();
        applyInternalPilotMessaging();
      });
    }

    // Lanzar inicialización controlada
    if (document.readyState === 'loading') {
      window.addEventListener('DOMContentLoaded', initApp);
    } else {
      initApp();
    }

    /* =============================================
       DATA & PRIVACY MANAGEMENT (XML import/export, batches, deletion)
       ============================================= */

    function chooseFeedXmlFile() {
      document.getElementById('private-feed-xml-file')?.click();
    }

    function handleFeedXmlFileSelected() {
      const fileInput = document.getElementById('private-feed-xml-file');
      const nameInput = document.getElementById('private-feed-xml-file-name');
      if (nameInput) nameInput.value = fileInput?.files?.[0]?.name || '';
    }

    async function importFeedXmlFile() {
      await importXmlFileFromInput('private-feed-xml-file', 'private-feed-xml-import-result');
    }

    async function importPrivateUserXml() {
      await importXmlFileFromInput('private-data-xml-file', 'private-xml-import-result');
    }

    async function importXmlFileFromInput(inputId, resultId) {
      const fileInput = document.getElementById(inputId);
      const resultDiv = document.getElementById(resultId);
      const file = fileInput?.files?.[0];
      if (!file) { showToast('Selecciona un archivo XML.', 'error'); return; }
      if (!file.name.endsWith('.xml')) { showToast('El archivo debe tener extensión .xml.', 'error'); return; }
      resultDiv?.classList.remove('hidden');
      if (resultDiv) resultDiv.innerHTML = '<span class="text-blue">Importando...</span>';
      try {
        const text = await file.text();
        const res = await fetch(window.CAPTACION_API.endpoints.uploadXmlFile, {
          method: 'POST',
          headers: { 'X-WP-Nonce': window.CAPTACION_API.nonce, 'Content-Type': 'application/xml', 'X-Filename': encodeURIComponent(file.name) },
          body: text
        });
        const data = await res.json();
        if (data.ok) {
          if (resultDiv) resultDiv.innerHTML = `<span class="text-green">Importación completada: ${data.imported} registros importados, ${data.rejected} rechazados.</span>`;
          showToast(`XML importado: ${data.imported} registros.`, 'success');
          loadImportBatches();
        } else {
          if (resultDiv) resultDiv.innerHTML = `<span class="text-red">Error: ${data.message || 'Error desconocido'}</span>`;
        }
      } catch (e) {
        if (resultDiv) resultDiv.innerHTML = `<span class="text-red">Error de red: ${e.message}</span>`;
      }
    }

    async function loadImportBatches() {
      const listTargets = [document.getElementById('private-import-batches-list'), document.getElementById('private-feed-import-batches-list')].filter(Boolean);
      if (!listTargets.length) return;
      try {
        const res = await fetch(window.CAPTACION_API.endpoints.listXmlFeeds, {
          headers: { 'X-WP-Nonce': window.CAPTACION_API.nonce }
        });
        const data = await res.json();
        if (!data.ok || !data.batches?.length) {
          listTargets.forEach(listDiv => { listDiv.innerHTML = '<p class="text-xs text-slate-400">No tienes XML importados.</p>'; });
          return;
        }
        const html = data.batches.map(b => {
          const date = new Date(b.created_at).toLocaleDateString('es-ES');
          const isPaused = b.status === 'paused';
          const statusBadge = isPaused ? 'bg-slate-100 text-slate-500' : 'bg-green-light text-green';
          const sourceName = b.source_file_name || b.import_batch_id;
          return `<div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 p-4 rounded-xl border border-slate-200 bg-white">
            <div class="min-w-0">
              <div class="flex flex-wrap items-center gap-2">
                <span class="inline-block px-2 py-0.5 rounded-full text-[9px] font-black uppercase ${statusBadge}">${isPaused ? 'Pausado' : 'Activo'}</span>
                <span class="text-[10px] text-slate-400">${date}</span>
              </div>
              <span class="text-xs font-bold text-navy block mt-2 truncate" title="${escapeHTML(sourceName)}">${escapeHTML(sourceName)}</span>
              <span class="text-[10px] text-slate-500">${escapeHTML(b.import_batch_id)} · ${escapeHTML(b.data_origin)} · ${Number(b.records_imported || 0)}/${Number(b.records_total || 0)} registros</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <strong class="block text-lg font-black text-blue">${Number(b.properties_count || 0)}</strong>
                <span class="block text-[9px] uppercase tracking-wider font-black text-slate-400">Propiedades</span>
              </div>
              <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-center">
                <strong class="block text-lg font-black text-navy">${Number(b.needs_count || 0)}</strong>
                <span class="block text-[9px] uppercase tracking-wider font-black text-slate-400">Demandas</span>
              </div>
              ${b.data_origin === 'xml_url' ? `<button onclick="syncImportBatch('${b.import_batch_id}')" class="px-3 py-2 rounded-xl border border-blue/20 bg-blue-light/40 hover:bg-blue-light text-blue text-[10px] font-bold">Actualizar</button>` : ''}
              <button onclick="updateImportBatchStatus('${b.import_batch_id}', '${isPaused ? 'active' : 'paused'}')" class="px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-navy text-[10px] font-bold">${isPaused ? 'Reactivar' : 'Pausar'}</button>
              <button onclick="deleteImportBatch('${b.import_batch_id}')" class="px-3 py-2 rounded-xl border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 text-[10px] font-bold">Eliminar</button>
            </div>
          </div>`;
        }).join('');
        listTargets.forEach(listDiv => { listDiv.innerHTML = html; });
      } catch (e) {
        listTargets.forEach(listDiv => { listDiv.innerHTML = '<p class="text-xs text-red">Error al cargar lotes.</p>'; });
      }
    }

    async function updateImportBatchStatus(batchId, status) {
      try {
        const res = await fetch(window.CAPTACION_API.endpoints.xmlFeed + encodeURIComponent(batchId), {
          method: 'PATCH',
          headers: { 'X-WP-Nonce': window.CAPTACION_API.nonce, 'Content-Type': 'application/json' },
          body: JSON.stringify({ status })
        });
        const data = await res.json();
        if (data.ok) {
          showToast(status === 'paused' ? 'XML pausado.' : 'XML activado.', 'success');
          loadImportBatches();
          loadWordPressRealEstateRecords();
        } else {
          showToast(data.message || 'No se pudo actualizar el XML.', 'error');
        }
      } catch (e) {
        showToast('Error de red: ' + e.message, 'error');
      }
    }

    async function syncImportBatch(batchId) {
      try {
        const res = await fetch(window.CAPTACION_API.endpoints.syncXmlFeed + encodeURIComponent(batchId) + '/sync', {
          method: 'POST',
          headers: { 'X-WP-Nonce': window.CAPTACION_API.nonce, 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.ok) {
          showToast(`XML actualizado: ${data.imported} propiedades.`, 'success');
          loadImportBatches();
          loadWordPressRealEstateRecords();
        } else {
          showToast(data.message || 'No se pudo actualizar el XML.', 'error');
        }
      } catch (e) {
        showToast('Error de red: ' + e.message, 'error');
      }
    }

    async function deleteImportBatch(batchId) {
      if (!confirm('¿Eliminar este lote? Los registros se marcarán como eliminados y no podrán recuperarse.')) return;
      try {
        const res = await fetch(window.CAPTACION_API.endpoints.xmlFeed + encodeURIComponent(batchId), {
          method: 'DELETE',
          headers: { 'X-WP-Nonce': window.CAPTACION_API.nonce, 'Content-Type': 'application/json' },
          body: JSON.stringify({ confirm: 'CONFIRMAR' })
        });
        const data = await res.json();
        if (data.ok) {
          showToast('Lote eliminado correctamente.', 'success');
          loadImportBatches();
          loadWordPressRealEstateRecords();
        } else {
          showToast(data.message || 'Error al eliminar.', 'error');
        }
      } catch (e) {
        showToast('Error de red: ' + e.message, 'error');
      }
    }

    async function exportMyPrivateData() {
      try {
        const res = await fetch(window.CAPTACION_API.endpoints.exportUserXml, {
          headers: { 'X-WP-Nonce': window.CAPTACION_API.nonce }
        });
        const data = await res.json();
        if (data.ok && data.xml) {
          const blob = new Blob([data.xml], { type: 'application/xml' });
          const url = URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = data.filename || 'captacion-app-export.xml';
          document.body.appendChild(a);
          a.click();
          document.body.removeChild(a);
          URL.revokeObjectURL(url);
          showToast('Exportación completada: ' + data.total_records + ' registros.', 'success');
        } else {
          showToast(data.message || 'Error al exportar.', 'error');
        }
      } catch (e) {
        showToast('Error de red: ' + e.message, 'error');
      }
    }

    async function deleteAllMyPrivateData() {
      const input = document.getElementById('private-delete-confirm-input');
      const resultDiv = document.getElementById('private-delete-result');
      if (!input || input.value.trim() !== 'CONFIRMAR') {
        showToast('Escribe CONFIRMAR para eliminar todos tus datos privados.', 'error');
        return;
      }
      if (!confirm('¿Estás SEGURO? Esta acción eliminará TODOS tus datos privados de forma irreversible.')) return;
      resultDiv.classList.remove('hidden');
      resultDiv.innerHTML = '<span class="text-red">Eliminando datos...</span>';
      try {
        const res = await fetch(window.CAPTACION_API.endpoints.deleteMyData, {
          method: 'DELETE',
          headers: { 'X-WP-Nonce': window.CAPTACION_API.nonce, 'Content-Type': 'application/json' },
          body: JSON.stringify({ confirm: 'CONFIRMAR' })
        });
        const data = await res.json();
        if (data.ok) {
          resultDiv.innerHTML = '<span class="text-green">Todos tus datos privados han sido eliminados.</span>';
          loadImportBatches();
          input.value = '';
        } else {
          resultDiv.innerHTML = `<span class="text-red">Error: ${data.message || 'Error desconocido'}</span>`;
        }
      } catch (e) {
        resultDiv.innerHTML = `<span class="text-red">Error de red: ${e.message}</span>`;
      }
    }
  </script>

<script>
(function(){
  const COMM_STORAGE_KEY='captacion_internal_communications_v1';
  const FLOW_STAGES=[
    {id:'match_detected',label:'Coincidencia detectada',button:'Crear solicitud protegida'},
    {id:'request_sent',label:'Solicitud enviada',button:'Confirmar disponibilidad'},
    {id:'availability_confirmed',label:'Disponibilidad confirmada',button:'Preparar Acuerdo de Confidencialidad (NDA)'},
    {id:'nda_pending',label:'Acuerdo de Confidencialidad (NDA) pendiente',button:'Simular firma del Acuerdo de Confidencialidad (NDA)'},
    {id:'nda_signed',label:'Acuerdo de Confidencialidad (NDA) firmado',button:'Preparar pago de acceso'},
    {id:'payment_pending',label:'Pago pendiente',button:'Simular pago confirmado'},
    {id:'payment_confirmed',label:'Pago confirmado',button:'Activar sala privada'},
    {id:'room_active',label:'Sala privada activa',button:'Flujo protegido completado'}
  ];
  let activeThreadId='';
  const safeEsc=(value)=>typeof escapeHTML==='function'?escapeHTML(String(value ?? '')):String(value ?? '').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
  const now=()=>Date.now();
  const uid=(prefix)=>`${prefix}-${Date.now()}-${Math.random().toString(36).slice(2,7)}`;
  function getNeedRef(need){return need?.id||'DEM-N/D'}
  function firstNeed(){return (window.needs||[])[0]||null}
  function secondNeed(){return (window.needs||[])[1]||firstNeed()}
  function firstProp(){return (window.properties||[])[0]||null}
  function secondProp(){return (window.properties||[])[1]||firstProp()}
  function seedComm(){
    return {
      preferences:{inApp:true,email:true,whatsapp:true,frequency:'instant'},
      subscriptions:[],
      events:[],
      threads:[],
      trace:[]
    }
  }
  function getComm(){
    try{const parsed=JSON.parse(localStorage.getItem(COMM_STORAGE_KEY));if(parsed&&Array.isArray(parsed.subscriptions)&&Array.isArray(parsed.threads)&&Array.isArray(parsed.trace))return parsed}catch(e){}
    const data=seedComm();saveComm(data);return data
  }
  function saveComm(data){try{localStorage.setItem(COMM_STORAGE_KEY,JSON.stringify(data))}catch(e){}}
  function addTrace(category,action,entity,detail,result='success'){
    const data=getComm();data.trace.unshift({id:uid('TR'),category,action,entity,detail,createdAt:now(),result});saveComm(data);return data
  }
  function channelBadge(delivery){const ok=delivery.status==='Entregada'||delivery.status==='Enviada';return `<span class="comm-channel-badge ${ok?'comm-channel-ok':'comm-channel-pending'}">${safeEsc(delivery.channel)} · ${safeEsc(delivery.status)}</span>`}
  function updateCommSidebar(){const d=getComm();const active=(d.subscriptions||[]).filter(x=>x.status==='active').length;const threads=(d.threads||[]).length;const a=document.getElementById('private-sidebar-subscriptions');if(a)a.textContent=active;const b=document.getElementById('private-sidebar-messages');if(b)b.textContent=threads}
  function renderCommStats(){const d=getComm();const el=document.getElementById('private-comm-stats');if(!el)return;const delivered=(d.events||[]).reduce((n,e)=>n+(e.deliveries||[]).filter(x=>x.status==='Entregada').length,0);el.innerHTML=[['Suscripciones activas',(d.subscriptions||[]).filter(x=>x.status==='active').length],['Salas protegidas',(d.threads||[]).length],['Avisos entregados',delivered],['Eventos auditados',(d.trace||[]).length]].map(([label,value])=>`<article class="comm-stat-card"><span class="block text-[10px] font-black uppercase tracking-wider text-slate-500">${safeEsc(label)}</span><strong class="block text-2xl font-black text-blue mt-1">${safeEsc(value)}</strong></article>`).join('')}
  function fillDemandSelect(){const el=document.getElementById('comm-demand-select');if(!el)return;const d=getComm();const subscribed=new Set((d.subscriptions||[]).map(x=>x.needId));const list=(window.needs||[]).filter(x=>!subscribed.has(x.id)).slice(0,80);el.innerHTML=list.map(x=>`<option value="${safeEsc(x.id)}">${safeEsc(x.title)} · ${safeEsc(x.postalCode||'S/C.P.')}</option>`).join('')||'<option value="">Todas las demandas visibles ya tienen suscripción</option>'}
  window.saveCommunicationPreferences=function(){const d=getComm();d.preferences={inApp:!!document.getElementById('comm-pref-inapp')?.checked,email:!!document.getElementById('comm-pref-email')?.checked,whatsapp:!!document.getElementById('comm-pref-whatsapp')?.checked,frequency:document.getElementById('comm-pref-frequency')?.value||'instant'};saveComm(d);persistWpRecord('user_preferences',d.preferences,{recordKey:'communication-preferences',title:'Preferencias de comunicación',status:'active'});addTrace('NOTIFICATION','PREFERENCES_UPDATED','PROFILE','El usuario actualizó sus canales operativos.');renderCommunicationModules();if(window.showToast)showToast('Preferencias de alertas actualizadas.','success')}
  window.subscribeSelectedDemand=function(){const id=document.getElementById('comm-demand-select')?.value;if(!id)return;const d=getComm();if(d.subscriptions.some(x=>x.needId===id))return;const pref=d.preferences||{};d.subscriptions.unshift({id:uid('SUB'),needId:id,channels:['platform',pref.email?'email':'',pref.whatsapp?'whatsapp':''].filter(Boolean),frequency:pref.frequency||'instant',threshold:70,status:'active',createdAt:now()});saveComm(d);addTrace('NOTIFICATION','DEMAND_SUBSCRIBED',id,'Se activaron alertas para la demanda.');renderCommunicationModules();if(window.showToast)showToast('Suscripción activada. Recibirás alertas de coincidencias.','success')}
  window.toggleDemandSubscription=function(id){const d=getComm();const s=d.subscriptions.find(x=>x.id===id);if(!s)return;s.status=s.status==='active'?'paused':'active';saveComm(d);addTrace('NOTIFICATION','SUBSCRIPTION_STATUS_CHANGED',id,`Estado de suscripción: ${s.status}`);renderCommunicationModules();if(window.showToast)showToast(s.status==='active'?'Suscripción reactivada.':'Suscripción pausada.','success')}
  window.removeDemandSubscription=function(id){const d=getComm();d.subscriptions=d.subscriptions.filter(x=>x.id!==id);saveComm(d);addTrace('NOTIFICATION','SUBSCRIPTION_REMOVED',id,'Suscripción retirada por el usuario.');renderCommunicationModules();if(window.showToast)showToast('Suscripción eliminada.','success')}
  function renderSubscriptions(){const d=getComm();const p=d.preferences||{};const set=(id,v)=>{const el=document.getElementById(id);if(el)el.checked=!!v};set('comm-pref-inapp',p.inApp);set('comm-pref-email',p.email);set('comm-pref-whatsapp',p.whatsapp);const f=document.getElementById('comm-pref-frequency');if(f)f.value=p.frequency||'instant';fillDemandSelect();const body=document.getElementById('comm-subscriptions-table');if(!body)return;body.innerHTML=(d.subscriptions||[]).map(s=>{const need=(window.needs||[]).find(x=>x.id===s.needId);const matches=need&&typeof getCompatiblePropertiesForNeed==='function'?getCompatiblePropertiesForNeed(need,10).length:0;return `<tr class="border-b border-slate-100"><td class="px-4 py-3"><strong class="block text-xs text-navy">${safeEsc(need?.title||s.needId)}</strong><span class="text-[10px] text-slate-500">${safeEsc(need?.id||s.needId)} · C.P. ${safeEsc(need?.postalCode||'N/D')}</span></td><td class="px-4 py-3"><span class="private-status-pill ${matches?'bg-green-light text-green':'bg-amber-light text-amber'}">${matches}</span></td><td class="px-4 py-3"><div class="flex flex-wrap gap-1">${(s.channels||[]).map(c=>`<span class="comm-channel-badge comm-channel-ok">${safeEsc(c)}</span>`).join('')}</div></td><td class="px-4 py-3">${safeEsc(s.frequency==='instant'?'Inmediata':s.frequency==='daily'?'Diaria':'Semanal')}</td><td class="px-4 py-3"><span class="private-status-pill ${s.status==='active'?'bg-green-light text-green':'bg-amber-light text-amber'}">${safeEsc(s.status==='active'?'Activa':'Pausada')}</span></td><td class="px-4 py-3"><div class="flex gap-2"><button onclick="toggleDemandSubscription('${safeEsc(s.id)}')" class="text-[10px] font-bold text-blue">${s.status==='active'?'Pausar':'Activar'}</button><button onclick="removeDemandSubscription('${safeEsc(s.id)}')" class="text-[10px] font-bold text-red-600">Eliminar</button></div></td></tr>`}).join('')||'<tr><td colspan="6" class="p-5 text-xs text-slate-500">No has configurado suscripciones todavía.</td></tr>'}
  function renderDeliveries(){const el=document.getElementById('comm-deliveries-list');if(!el)return;const d=getComm();el.innerHTML=(d.events||[]).map(e=>`<article class="private-mini-card"><div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3"><div><span class="text-[10px] font-black uppercase tracking-wider text-blue">${safeEsc(e.type)} · ${safeEsc(e.entityRef)}</span><p class="text-xs text-navy font-bold mt-1">${safeEsc(e.detail)}</p><span class="block text-[10px] text-slate-400 mt-1">${typeof formatRelativeTime==='function'?formatRelativeTime(e.createdAt):new Date(e.createdAt).toLocaleString('es-ES')}</span></div><div class="flex flex-wrap gap-1">${(e.deliveries||[]).map(channelBadge).join('')}</div></div></article>`).join('')}
  window.simulateProtectedMatchNotification=function(){const d=getComm();const n=firstNeed(),p=firstProp();const event={id:uid('EVT'),type:'Nueva coincidencia',entityRef:getNeedRef(n),detail:'Una nueva captación compatible requiere revisión dentro de la plataforma.',priority:'high',createdAt:now(),deliveries:[{channel:'Plataforma',status:'Entregada'}]};if(d.preferences.email)event.deliveries.push({channel:'Email',status:'Entregada'});if(d.preferences.whatsapp)event.deliveries.push({channel:'WhatsApp',status:'Entregada'});d.events.unshift(event);const dash=typeof getPrivateDashboardState==='function'?getPrivateDashboardState():null;if(dash){dash.notifications.unshift({id:uid('NOT'),category:'Oportunidades',title:'Nueva coincidencia protegida',detail:'Accede a Captacion.app para revisar la oportunidad sin exponer contactos.',createdAt:now(),read:false,target:'subscriptions'});dash.activities.unshift({id:uid('ACT'),icon:'✦',title:'Aviso multicanal generado',detail:'La plataforma notificó una coincidencia por los canales configurados.',createdAt:now()});persistPrivateDashboardState(dash)}saveComm(d);addTrace('MATCH','MATCH_DETECTED',event.id,'Coincidencia detectada y aviso multicanal generado.');renderDashboard();if(window.showToast)showToast('Coincidencia simulada: alertas operativas enviadas.','success')}
  function stageIndex(stage){const i=FLOW_STAGES.findIndex(x=>x.id===stage);return i<0?0:i}
  function renderThreads(){const el=document.getElementById('comm-threads-list');if(!el)return;const d=getComm();el.innerHTML=(d.threads||[]).map(t=>{const step=FLOW_STAGES[stageIndex(t.stage)];return `<article class="comm-thread-card"><div class="flex items-start justify-between gap-3"><div><span class="text-[10px] font-black uppercase tracking-wider text-green">Sala protegida · ${safeEsc(t.entityRef)}</span><strong class="block text-sm text-navy mt-1">${safeEsc(t.title)}</strong><span class="block text-[10px] text-slate-500 mt-1">Contraparte: identidad protegida · ${safeEsc(step.label)}</span></div><span class="private-status-pill ${t.stage==='room_active'?'bg-green-light text-green':'bg-blue-light text-blue'}">${safeEsc(step.label)}</span></div><p class="text-[11px] text-slate-500 mt-3 leading-relaxed">Mensajes internos asociados al expediente. El contacto directo continúa oculto hasta completar el flujo configurado.</p><div class="flex flex-wrap gap-2 mt-4"><button onclick="openProtectedThread('${safeEsc(t.id)}')" class="px-3 py-2 rounded-lg bg-navy text-white text-[10px] font-bold">Abrir sala</button><button onclick="switchPrivateDashboardPanel('traceability')" class="px-3 py-2 rounded-lg border border-slate-200 text-navy text-[10px] font-bold">Ver trazabilidad</button></div></article>`}).join('')}
  window.openProtectedThread=function(id){activeThreadId=id;const d=getComm();const t=d.threads.find(x=>x.id===id);if(!t)return;const title=document.getElementById('comm-thread-title');const sub=document.getElementById('comm-thread-subtitle');if(title)title.textContent=t.title;if(sub)sub.textContent=`${t.entityRef} · Comunicación interna sin contacto directo`;renderThreadModal();document.getElementById('comm-thread-modal')?.classList.remove('hidden');addTrace('MESSAGE','THREAD_OPENED',id,'El usuario abrió la sala protegida.')}
  window.closeProtectedThread=function(){document.getElementById('comm-thread-modal')?.classList.add('hidden');activeThreadId=''}
  function renderThreadModal(){const d=getComm();const t=d.threads.find(x=>x.id===activeThreadId);if(!t)return;const msg=document.getElementById('comm-thread-messages');if(msg){msg.innerHTML=(t.messages||[]).map(m=>`<div class="comm-message ${m.kind==='system'?'comm-message-system':m.kind==='me'?'comm-message-me':'comm-message-other'}"><strong class="block text-[10px] mb-1 opacity-80">${m.kind==='system'?'Sistema Captacion.app':m.kind==='me'?'Tú':'Profesional verificado'}</strong>${safeEsc(m.body)}<span class="block text-[9px] mt-2 opacity-70">${new Date(m.createdAt).toLocaleString('es-ES')}</span></div>`).join('');msg.scrollTop=msg.scrollHeight}const idx=stageIndex(t.stage);const flow=document.getElementById('comm-thread-flow');if(flow)flow.innerHTML=FLOW_STAGES.map((s,i)=>`<div class="comm-flow-step ${i<idx?'done':i===idx?'current':''}">${safeEsc(s.label)}</div>`).join('');const btn=document.getElementById('comm-thread-progress-btn');if(btn){btn.textContent=FLOW_STAGES[idx].button;btn.disabled=t.stage==='room_active';btn.classList.toggle('opacity-50',btn.disabled)}}
  function containsContact(body){return /([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,})|(https?:\/\/|www\.)|(\+?\d[\d\s().-]{7,}\d)/i.test(body)}
  window.sendProtectedThreadMessage=function(){const input=document.getElementById('comm-thread-input');const body=(input?.value||'').trim();if(!body)return;if(containsContact(body)){addTrace('SECURITY','CONTACT_SHARE_BLOCKED',activeThreadId,'Se bloqueó un intento de compartir teléfono, email o URL.','blocked');if(window.showToast)showToast('Mensaje bloqueado: no compartas teléfonos, emails ni enlaces externos antes del desbloqueo.','error');return}const d=getComm();const t=d.threads.find(x=>x.id===activeThreadId);if(!t)return;t.messages.push({id:uid('MSG'),kind:'me',body,createdAt:now()});t.updatedAt=now();saveComm(d);addTrace('MESSAGE','MESSAGE_SENT',t.id,'Mensaje interno enviado dentro de la sala protegida.');if(input)input.value='';renderThreadModal();renderThreads();if(window.showToast)showToast('Mensaje enviado dentro de la sala protegida.','success')}
  window.advanceProtectedFlow=function(){const d=getComm();const t=d.threads.find(x=>x.id===activeThreadId);if(!t)return;const idx=stageIndex(t.stage);if(idx>=FLOW_STAGES.length-1)return;const next=FLOW_STAGES[idx+1];t.stage=next.id;t.updatedAt=now();t.messages.push({id:uid('MSG'),kind:'system',body:`Flujo actualizado: ${next.label}.`,createdAt:now()});const ev={id:uid('EVT'),type:'Cambio de estado',entityRef:t.entityRef,detail:`La sala protegida avanzó a: ${next.label}.`,priority:'medium',createdAt:now(),deliveries:[{channel:'Plataforma',status:'Entregada'}]};if(d.preferences.email)ev.deliveries.push({channel:'Email',status:'Entregada'});if(d.preferences.whatsapp)ev.deliveries.push({channel:'WhatsApp',status:'Entregada'});d.events.unshift(ev);saveComm(d);addTrace('FLOW','FLOW_STAGE_CHANGED',t.id,`Nuevo estado: ${next.label}.`);renderThreadModal();renderCommunicationModules();if(window.showToast)showToast(`Flujo actualizado: ${next.label}.`,'success')}
  function renderTrace(){const el=document.getElementById('comm-trace-list');if(!el)return;const d=getComm();const filter=document.getElementById('comm-trace-filter')?.value||'';const list=(d.trace||[]).filter(x=>!filter||x.category===filter);el.innerHTML=list.map(x=>`<article class="comm-trace-line"><div class="flex flex-wrap items-center gap-2"><span class="text-[9px] font-black uppercase tracking-wider text-blue">${safeEsc(x.category)}</span><span class="text-[9px] text-slate-400">${new Date(x.createdAt).toLocaleString('es-ES')}</span><span class="private-status-pill ${x.result==='blocked'?'bg-red-50 text-red-600':'bg-green-light text-green'}">${safeEsc(x.result)}</span></div><strong class="block text-xs text-navy mt-1">${safeEsc(x.action)} · ${safeEsc(x.entity)}</strong><p class="text-[11px] text-slate-500 mt-1 leading-relaxed">${safeEsc(x.detail)}</p></article>`).join('')||'<p class="text-xs text-slate-500">No existen eventos para ese filtro.</p>'}
  window.renderCommunicationTrace=renderTrace;
  window.exportCommunicationTrace=function(){const d=getComm();const payload={exportedAt:new Date().toISOString(),notice:'Exportación demostrativa de trazabilidad. En producción debe generarse desde backend con firma y controles de acceso.',trace:d.trace,events:d.events,threads:d.threads.map(t=>({id:t.id,entityRef:t.entityRef,stage:t.stage,updatedAt:t.updatedAt,messageCount:(t.messages||[]).length}))};const blob=new Blob([JSON.stringify(payload,null,2)],{type:'application/json'});const url=URL.createObjectURL(blob);const a=document.createElement('a');a.href=url;a.download=`captacion-app-trazabilidad-${Date.now()}.json`;a.click();URL.revokeObjectURL(url);addTrace('FLOW','TRACE_EXPORTED','AUDIT','El usuario exportó un registro demostrativo de trazabilidad.');renderTrace()}
  function appendCommunicationOverview(){const fav=document.getElementById('private-overview-favorites')?.closest('section');if(!fav||document.getElementById('private-overview-communications'))return;const box=document.createElement('section');box.id='private-overview-communications';box.className='private-section-card overflow-hidden mb-6';box.innerHTML=`<div class="px-5 py-4 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3"><div><h4 class="text-sm font-black text-navy">Centro de comunicación protegida</h4><p class="text-[11px] text-slate-500 mt-1">Suscripciones, avisos multicanal y salas internas con trazabilidad.</p></div><div class="flex gap-2"><button onclick="switchPrivateDashboardPanel('subscriptions')" class="px-3 py-2 rounded-lg bg-blue text-white text-[10px] font-bold">Gestionar alertas</button><button onclick="switchPrivateDashboardPanel('communications')" class="px-3 py-2 rounded-lg border border-slate-200 text-navy text-[10px] font-bold">Abrir salas</button></div></div><div id="private-overview-comm-stats" class="grid grid-cols-2 lg:grid-cols-4 gap-3 p-4"></div>`;fav.parentNode.insertBefore(box,fav);renderOverviewCommStats()}
  function renderOverviewCommStats(){const el=document.getElementById('private-overview-comm-stats');if(!el)return;const d=getComm();el.innerHTML=[['Suscripciones activas',d.subscriptions.filter(x=>x.status==='active').length],['Salas privadas',d.threads.length],['Avisos multicanal',d.events.length],['Eventos auditados',d.trace.length]].map(([a,b])=>`<div class="private-mini-card"><span class="block text-[10px] uppercase tracking-wider text-slate-500 font-black">${safeEsc(a)}</span><strong class="block text-xl text-blue mt-1">${safeEsc(b)}</strong></div>`).join('')}
  window.renderCommunicationModules=function(){updateCommSidebar();renderCommStats();renderSubscriptions();renderDeliveries();renderThreads();renderTrace();renderOverviewCommStats()}
  window.openCommunicationForDemand=function(needId){const d=getComm();const exists=d.subscriptions.find(x=>x.needId===needId);if(!exists){const p=d.preferences||{};d.subscriptions.unshift({id:uid('SUB'),needId,channels:['platform',p.email?'email':'',p.whatsapp?'whatsapp':''].filter(Boolean),frequency:p.frequency||'instant',threshold:70,status:'active',createdAt:now()});saveComm(d);addTrace('NOTIFICATION','DEMAND_SUBSCRIBED',needId,'Suscripción creada desde la tabla de demandas.')}switchPrivateDashboardPanel('subscriptions');renderCommunicationModules()}
  const baseRenderPrivateDemands=window.renderPrivateDemands;
  if(typeof baseRenderPrivateDemands==='function')window.renderPrivateDemands=function(){baseRenderPrivateDemands();document.querySelectorAll('#private-demands-table tr').forEach(row=>{const btn=row.querySelector("button[onclick^=\"openHomeNeedMatches\"]");if(!btn)return;const match=btn.getAttribute('onclick').match(/'([^']+)'/);const id=match?.[1];if(!id||row.querySelector('[data-comm-subscribe]'))return;const b=document.createElement('button');b.dataset.commSubscribe='1';b.className='block mt-1 text-[10px] font-bold text-green';b.textContent='Gestionar alertas';b.onclick=()=>openCommunicationForDemand(id);btn.parentNode.appendChild(b)})}
  const baseDashboard=window.renderDashboard;
  if(typeof baseDashboard==='function')window.renderDashboard=function(){baseDashboard();appendCommunicationOverview();renderCommunicationModules()}
  const baseConfirm=window.confirmPrivateRequest;
  if(typeof baseConfirm==='function')window.confirmPrivateRequest=function(id){baseConfirm(id);addTrace('FLOW','AVAILABILITY_CONFIRMED',id,'Disponibilidad confirmada desde solicitudes. Se mantiene el contacto protegido.');renderCommunicationModules()}
  document.addEventListener('DOMContentLoaded',()=>{appendCommunicationOverview();renderCommunicationModules()});
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
