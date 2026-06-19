<?php

/**
 * Plugin Name: Woo Broadcast Mailer
 * Description: Sistema de envío masivo de emails: envía a compradores de productos específicos o programa envíos por rol de usuario. Compatible con HPOS y Action Scheduler.
 * Version: 2.0.2
 * Author: 22MW · The Capricho Studio
 * Author URI: https://22mw.online
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * WC requires at least: 10.0
 * WC tested up to: 10.4.3    
 * Requires Plugins: woocommerce
 * Text Domain: wc-pbm
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer;

defined('ABSPATH') || exit;

/**
 * Declara compatibilidad con características de WooCommerce
 */
function declare_hpos_compatibility()
{
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
}
add_action('before_woocommerce_init', __NAMESPACE__ . '\\declare_hpos_compatibility');

/**
 * Activación del plugin
 */
function activate_plugin()
{
    create_database_tables();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\activate_plugin');

/**
 * Desactivación del plugin
 */
function deactivate_plugin()
{
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, __NAMESPACE__ . '\\deactivate_plugin');

/**
 * Crea las tablas de base de datos necesarias
 *
 * @return void
 */
function create_database_tables()
{
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();

    // Tabla de envíos programados
    $table_scheduled = $wpdb->prefix . 'pbm_scheduled_emails';
    $sql_scheduled = "CREATE TABLE IF NOT EXISTS {$table_scheduled} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_role varchar(50) NOT NULL,
        subject varchar(255) NOT NULL,
        message longtext NOT NULL,
        scheduled_at datetime NOT NULL,
        batch_size int(11) NOT NULL DEFAULT 30,
        emails_per_hour int(11) NOT NULL DEFAULT 200,
        status varchar(20) NOT NULL DEFAULT 'pending',
        created_at datetime NOT NULL,
        PRIMARY KEY (id),
        KEY status (status),
        KEY scheduled_at (scheduled_at)
    ) {$charset_collate};";

    // Tabla de logs de envío
    $table_logs = $wpdb->prefix . 'pbm_scheduled_logs';
    $sql_logs = "CREATE TABLE IF NOT EXISTS {$table_logs} (
        id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        scheduled_id bigint(20) UNSIGNED NOT NULL,
        started_at datetime NOT NULL,
        completed_at datetime DEFAULT NULL,
        total_sent int(11) NOT NULL DEFAULT 0,
        total_failed int(11) NOT NULL DEFAULT 0,
        error_message text,
        PRIMARY KEY (id),
        KEY scheduled_id (scheduled_id)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_scheduled);
    dbDelta($sql_logs);
}

/**
 * Verifica dependencias del plugin
 *
 * @return bool
 */
function check_plugin_dependencies()
{
    if (! class_exists('WooCommerce')) {
        add_action('admin_notices', __NAMESPACE__ . '\\woocommerce_missing_notice');
        return false;
    }

    return true;
}

/**
 * Comprueba si Action Scheduler está disponible.
 *
 * @return bool
 */
function is_action_scheduler_available()
{
    return function_exists('as_schedule_single_action') && function_exists('as_unschedule_action');
}

/**
 * Devuelve el mensaje de estado de Action Scheduler.
 *
 * @return string
 */
function get_action_scheduler_status_message()
{
    if (is_action_scheduler_available()) {
        return __('Action Scheduler está disponible. Los envíos en segundo plano pueden programarse.', 'wc-pbm');
    }

    return __('Action Scheduler no está disponible. No se pueden programar envíos hasta revisar WooCommerce.', 'wc-pbm');
}

/**
 * Devuelve el mensaje de error para bloqueos por Action Scheduler.
 *
 * @return string
 */
function get_action_scheduler_unavailable_message()
{
    return __('Action Scheduler no está disponible. No se pudo programar el envío.', 'wc-pbm');
}

/**
 * Muestra aviso si WooCommerce no está instalado
 *
 * @return void
 */
function woocommerce_missing_notice()
{
?>
    <div class="notice notice-error">
        <p>
            <strong>WOO Broadcast Mailer</strong> requiere que WooCommerce esté instalado y activado.
        </p>
    </div>
<?php
}

/**
 * Inicializa el plugin
 */
function init()
{
    if (! check_plugin_dependencies()) {
        return;
    }

    load_textdomain();

    // Cargar funciones adicionales
    require_once plugin_dir_path(__FILE__) . 'includes/functions-products.php';
    require_once plugin_dir_path(__FILE__) . 'includes/functions-email.php';
    require_once plugin_dir_path(__FILE__) . 'includes/functions-scheduled.php';
    require_once plugin_dir_path(__FILE__) . 'includes/ajax-handlers.php';
    require_once plugin_dir_path(__FILE__) . 'includes/updater.php';
    require_once plugin_dir_path(__FILE__) . 'includes/email-string-editor.php';

    Email_String_Editor\bootstrap();

    add_action('admin_menu', __NAMESPACE__ . '\\register_admin_menu');
    add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets');
    add_action('admin_post_pbm_save_settings', __NAMESPACE__ . '\\handle_settings_save');
    add_action('admin_notices', __NAMESPACE__ . '\\maybe_show_settings_notice');
    add_action('admin_init', __NAMESPACE__ . '\\register_updater_hooks');
    add_action('wp_ajax_pbm_preview_recipients', __NAMESPACE__ . '\\ajax_preview_recipients');
    add_action('wp_ajax_pbm_count_recipients', __NAMESPACE__ . '\\ajax_count_recipients');
    add_action('wp_ajax_pbm_resolve_audience_item', __NAMESPACE__ . '\\ajax_resolve_audience_item');
    add_action('wp_ajax_pbm_search_selectors', __NAMESPACE__ . '\\ajax_search_selectors');
    add_action('wp_ajax_pbm_send_broadcast', __NAMESPACE__ . '\\ajax_send_broadcast');
    add_action('pbm_process_email_batch', __NAMESPACE__ . '\\process_email_batch', 10, 4);

    // Registrar hooks AJAX para envíos programados
    add_action('wp_ajax_pbm_create_scheduled_email', __NAMESPACE__ . '\\ajax_create_scheduled_email');
    add_action('wp_ajax_pbm_cancel_scheduled_email', __NAMESPACE__ . '\\ajax_cancel_scheduled_email');
    add_action('wp_ajax_pbm_run_scheduled_now', __NAMESPACE__ . '\\ajax_run_scheduled_now');
    add_action('wp_ajax_pbm_get_scheduled_logs', __NAMESPACE__ . '\\ajax_get_scheduled_logs');
    add_action('wp_ajax_pbm_list_scheduled_emails', __NAMESPACE__ . '\\ajax_list_scheduled_emails');
    add_action('wp_ajax_pbm_preview_role_recipients', __NAMESPACE__ . '\\ajax_preview_role_recipients');
    add_action('wp_ajax_pbm_delete_scheduled_email', __NAMESPACE__ . '\\ajax_delete_scheduled_email');
    add_action('wp_ajax_pbm_bulk_delete_scheduled', __NAMESPACE__ . '\\ajax_bulk_delete_scheduled');
    add_action('wp_ajax_pbm_bulk_delete_scheduled_ids', __NAMESPACE__ . '\\ajax_bulk_delete_scheduled_ids');

    // Registrar callbacks de Action Scheduler
    add_action('pbm_execute_scheduled_email', __NAMESPACE__ . '\\execute_scheduled_email', 10, 1);
}
add_action('plugins_loaded', __NAMESPACE__ . '\\init');

/**
 * Carga el textdomain del plugin
 *
 * @return void
 */
function load_textdomain()
{
    load_plugin_textdomain('wc-pbm', false, dirname(plugin_basename(__FILE__)) . '/languages');
}

/**
 * Registra el submenú en WooCommerce
 *
 * @return void
 */
function register_admin_menu()
{
    add_submenu_page(
        'woocommerce',
        __('Broadcast Mailer', 'wc-pbm'),
        __('Broadcast Mailer', 'wc-pbm'),
        'manage_woocommerce',
        'product-broadcast-mailer',
        __NAMESPACE__ . '\\render_admin_page'
    );
}

/**
 * Carga assets (Select2) en la página de admin
 *
 * @param string $hook Hook de la página actual.
 * @return void
 */
function enqueue_admin_assets($hook)
{
    if ('woocommerce_page_product-broadcast-mailer' !== $hook) {
        return;
    }

    wp_enqueue_style('select2', WC()->plugin_url() . '/assets/css/select2.css', array(), '4.0.3');
    wp_enqueue_style(
        'pbm-admin',
        plugin_dir_url(__FILE__) . 'assets/css/admin.css',
        array('select2'),
        get_plugin_version()
    );
    wp_enqueue_script('select2', WC()->plugin_url() . '/assets/js/select2/select2.min.js', array('jquery'), '4.0.3', true);

    wp_add_inline_script('select2', '
        jQuery(document).ready(function($) {
            $("#pbm_product_id").select2({
                width: "100%",
                placeholder: "' . esc_js(__('Selecciona un producto...', 'wc-pbm')) . '"
            });
        });
    ');

    $asset_file = plugin_dir_path(__FILE__) . 'build/index.asset.php';
    if (file_exists($asset_file)) {
        $asset_data = require $asset_file;
        wp_enqueue_script(
            'pbm-admin-react',
            plugin_dir_url(__FILE__) . 'build/index.js',
            $asset_data['dependencies'] ?? array('wp-element', 'wp-components', 'wp-i18n'),
            $asset_data['version'] ?? get_plugin_version(),
            true
        );
        wp_localize_script(
            'pbm-admin-react',
            'pbmAdminApp',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('pbm_broadcast_action'),
                'scheduledNonce' => wp_create_nonce('pbm_scheduled_action'),
            )
        );

        $style_file = plugin_dir_path(__FILE__) . 'build/index.css';
        if (file_exists($style_file)) {
            wp_enqueue_style(
                'pbm-admin-react',
                plugin_dir_url(__FILE__) . 'build/index.css',
                array(),
                $asset_data['version'] ?? get_plugin_version()
            );
        }
    }
}

/**
 * Renderiza la página de administración
 *
 * @return void
 */
function render_admin_page()
{
    if (! current_user_can('manage_woocommerce')) {
        return;
    }
    $recipient_sources = get_recipient_sources();

    // URL a las Acciones Programadas
    $scheduled_actions_url = admin_url('admin.php?page=wc-status&tab=action-scheduler&s=pbm_process_email_batch');

?>
    <div class="wrap pbm-admin">
        <h1>
            <span class="pbm-title-wrap">
                <?php esc_html_e(' Broadcast Mailer', 'wc-pbm'); ?>
                <small class="pbm-version">
                    <?php echo esc_html(sprintf(__('(v%s)', 'wc-pbm'), get_plugin_version())); ?>
                </small>
            </span>
            <span class="pbm-header-actions">
                <a class="pbm-email-editor-link" href="<?php echo esc_url(admin_url('admin.php?page=pbm-email-string-editor')); ?>">
                    <?php esc_html_e('Editor de emails', 'wc-pbm'); ?>
                </a>
                <a class="pbm-brand-link" href="https://22mw.online/" target="_blank" rel="noopener noreferrer">
                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/img/22mw.svg'); ?>" alt="22MW">
                </a>
            </span>
        </h1>
        <div id="pbm-admin-app"></div>
        <form id="pbm-broadcast-form" method="post">
            <?php wp_nonce_field('pbm_broadcast_action', 'pbm_nonce'); ?>
            <input type="hidden" id="pbm_audience_items" name="pbm_audience_items" value="">
            <input type="hidden" id="pbm_manual_emails" name="pbm_manual_emails" value="">
            <div class="pbm-legacy-data" aria-hidden="true">
                <select id="pbm_recipient_source" name="pbm_recipient_source">
                    <?php foreach ($recipient_sources as $source_key => $source_data) : ?>
                        <option value="<?php echo esc_attr($source_key); ?>" <?php disabled(isset($source_data['enabled']) && ! $source_data['enabled']); ?>>
                            <?php echo esc_html($source_data['label'] ?? $source_key); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php render_product_selector(); ?>
                <?php render_role_selector(); ?>
                <?php render_mailmint_list_selector(); ?>
            </div>

            <div id="pbm-message-row-legacy">
                <?php
                wp_editor('', 'pbm_message', array(
                    'textarea_rows' => 15,
                    'media_buttons' => false,
                    'teeny'         => false,
                    'quicktags'     => true,
                ));
                ?>
                <p class="description">
                    <?php esc_html_e('Usa {customer_name} para el nombre del cliente.', 'wc-pbm'); ?>
                </p>
            </div>
        </form>

        <!-- Mensaje siempre visible -->
        <div class="pbm-info-box pbm-info-box-compact">
            <p>
                <strong><?php esc_html_e('Importante:', 'wc-pbm'); ?></strong>
                <?php esc_html_e('Los correos se enviarán en segundo plano usando Action Scheduler.', 'wc-pbm'); ?>
                <a target="_blank" href="<?php echo esc_url($scheduled_actions_url); ?>">
                    <?php esc_html_e('Monitoriza el progreso aquí', 'wc-pbm'); ?>
                </a>.
            </p>
            <p>
                <strong><?php esc_html_e('Estado Action Scheduler:', 'wc-pbm'); ?></strong>
                <?php echo esc_html(get_action_scheduler_status_message()); ?>
            </p>
        </div>

        <div class="pbm-info-box">
            <h2><?php esc_html_e('Ajustes', 'wc-pbm'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('pbm_save_settings', 'pbm_settings_nonce'); ?>
                <input type="hidden" name="action" value="pbm_save_settings">
                <label>
                    <input type="checkbox" name="pbm_delete_data_on_uninstall" value="1" <?php checked((bool) get_option('pbm_delete_data_on_uninstall', false)); ?>>
                    <?php esc_html_e('Borrar datos al desinstalar', 'wc-pbm'); ?>
                </label>
                <p class="description">
                    <?php esc_html_e('Si está activo, se eliminarán las tablas y la opción del plugin al desinstalar.', 'wc-pbm'); ?>
                </p>
                <p>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e('Guardar ajustes', 'wc-pbm'); ?>
                    </button>
                </p>
            </form>
        </div>

    </div><!-- .wrap -->
<?php
}

/**
 * Obtiene la versión del plugin desde el header
 *
 * @return string
 */
function get_plugin_version()
{
    $data = get_file_data(__FILE__, array('Version' => 'Version'));
    return isset($data['Version']) ? (string) $data['Version'] : '';
}

/**
 * Guarda ajustes del plugin
 *
 * @return void
 */
function handle_settings_save()
{
    if (! current_user_can('manage_woocommerce')) {
        wp_die(__('Permisos insuficientes', 'wc-pbm'));
    }

    check_admin_referer('pbm_save_settings', 'pbm_settings_nonce');

    $value = isset($_POST['pbm_delete_data_on_uninstall']) ? 1 : 0;
    update_option('pbm_delete_data_on_uninstall', $value);

    $url = add_query_arg(
        array(
            'page' => 'product-broadcast-mailer',
            'pbm_settings_saved' => '1',
        ),
        admin_url('admin.php')
    );
    wp_safe_redirect($url);
    exit;
}

/**
 * Muestra aviso de ajustes guardados
 *
 * @return void
 */
function maybe_show_settings_notice()
{
    if (! isset($_GET['page']) || $_GET['page'] !== 'product-broadcast-mailer') {
        return;
    }

    if (empty($_GET['pbm_settings_saved'])) {
        return;
    }

    if (! current_user_can('manage_woocommerce')) {
        return;
    }

?>
    <div class="notice notice-success is-dismissible">
        <p><?php esc_html_e('Ajustes guardados.', 'wc-pbm'); ?></p>
    </div>
<?php
}
