<?php

/**
 * Plugin Name: Woo Broadcast Mailer
 * Description: Sistema de envío masivo de emails: envía a compradores de productos específicos o programa envíos por rol de usuario. Compatible con HPOS y Action Scheduler.
 * Version: 1.0.7
 * Author: 22MW ( Veri*Fac*WOO TEAM )
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

    add_action('admin_menu', __NAMESPACE__ . '\\register_admin_menu');
    add_action('admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_admin_assets');
    add_action('admin_post_pbm_save_settings', __NAMESPACE__ . '\\handle_settings_save');
    add_action('admin_notices', __NAMESPACE__ . '\\maybe_show_settings_notice');
    add_action('admin_init', __NAMESPACE__ . '\\register_updater_hooks');
    add_action('wp_ajax_pbm_preview_recipients', __NAMESPACE__ . '\\ajax_preview_recipients');
    add_action('wp_ajax_pbm_send_broadcast', __NAMESPACE__ . '\\ajax_send_broadcast');
    add_action('pbm_process_email_batch', __NAMESPACE__ . '\\process_email_batch', 10, 4);

    // Registrar hooks AJAX para envíos programados
    add_action('wp_ajax_pbm_create_scheduled_email', __NAMESPACE__ . '\\ajax_create_scheduled_email');
    add_action('wp_ajax_pbm_cancel_scheduled_email', __NAMESPACE__ . '\\ajax_cancel_scheduled_email');
    add_action('wp_ajax_pbm_run_scheduled_now', __NAMESPACE__ . '\\ajax_run_scheduled_now');
    add_action('wp_ajax_pbm_get_scheduled_logs', __NAMESPACE__ . '\\ajax_get_scheduled_logs');
    add_action('wp_ajax_pbm_preview_role_recipients', __NAMESPACE__ . '\\ajax_preview_role_recipients');
    add_action('wp_ajax_pbm_delete_scheduled_email', __NAMESPACE__ . '\\ajax_delete_scheduled_email');
    add_action('wp_ajax_pbm_bulk_delete_scheduled', __NAMESPACE__ . '\\ajax_bulk_delete_scheduled');

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
    wp_enqueue_script('select2', WC()->plugin_url() . '/assets/js/select2/select2.min.js', array('jquery'), '4.0.3', true);

    wp_add_inline_script('select2', '
        jQuery(document).ready(function($) {
            $("#pbm_product_id").select2({
                width: "100%",
                placeholder: "' . esc_js(__('Selecciona un producto...', 'wc-pbm')) . '"
            });
        });
    ');
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

    // Sistema de pestañas
    $current_tab = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'broadcast';

    // URL a las Acciones Programadas
    $scheduled_actions_url = admin_url('admin.php?page=wc-status&tab=action-scheduler&s=pbm_process_email_batch');

?>
    <div class="wrap" style=" background: #ffffff; padding: 20px; ">
        <h1>
            <?php esc_html_e(' Broadcast Mailer', 'wc-pbm'); ?>
            <small style="color:#666; font-weight: normal;">
                <?php echo esc_html(sprintf(__('(v%s)', 'wc-pbm'), get_plugin_version())); ?>
            </small>
        </h1>

        <!-- Sistema de pestañas -->
        <h2 class="nav-tab-wrapper" style="margin-bottom: 20px;">
            <a href="<?php echo esc_url(admin_url('admin.php?page=product-broadcast-mailer&tab=broadcast')); ?>"
                class="nav-tab <?php echo $current_tab === 'broadcast' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Envío por Producto', 'wc-pbm'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=product-broadcast-mailer&tab=scheduled')); ?>"
                class="nav-tab <?php echo $current_tab === 'scheduled' ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e('Envíos Programados', 'wc-pbm'); ?>
            </a>
        </h2>

        <?php if ($current_tab === 'broadcast') : ?>

            <form id="pbm-broadcast-form" method="post" style="max-width: 800px;">
                <?php wp_nonce_field('pbm_broadcast_action', 'pbm_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="pbm_product_id"><?php esc_html_e('Producto', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <?php render_product_selector(); ?>
                            <p class="description">
                                <?php esc_html_e('Selecciona el producto. Se incluirán automáticamente todas las variaciones y suscripciones relacionadas.', 'wc-pbm'); ?>
                            </p>
                            <p style="margin-top: 10px;">
                                <button type="button" id="pbm-preview-btn" class="button">
                                    <?php esc_html_e('Vista Previa de Destinatarios', 'wc-pbm'); ?>
                                </button>
                            </p>

                            <!-- Resumen de destinatarios (inline después del selector) -->
                            <div id="pbm-preview-results" style="display:none; margin-top: 15px;padding: 15px;background: rgb(240 240 240);border-radius: 10px;">
                                <h4 style="margin-top: 0; margin-bottom: 10px;"><?php esc_html_e('Resumen de Destinatarios', 'wc-pbm'); ?></h4>
                                <div id="pbm-preview-content"></div>
                                <div id="pbm-emails-list" style="margin-top: 12px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 3px; max-height: 150px; overflow-y: auto; font-size: 12px; line-height: 1.6;">
                                    <strong><?php esc_html_e('Emails:', 'wc-pbm'); ?></strong><br>
                                    <span id="pbm-emails-content" style="color: #555;"></span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_subject"><?php esc_html_e('Asunto', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="pbm_subject" name="pbm_subject" class="regular-text" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_message"><?php esc_html_e('Mensaje', 'wc-pbm'); ?></label>
                        </th>
                        <td>
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
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_batch_size"><?php esc_html_e('Tamaño de lote', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="pbm_batch_size" name="pbm_batch_size" value="30" min="10" max="100" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Correos por lote (recomendado: 20-50)', 'wc-pbm'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_emails_per_hour"><?php esc_html_e('Emails por hora', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="pbm_emails_per_hour" name="pbm_emails_per_hour" value="200" min="10" max="1000" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Límite de emails a enviar por hora (ej: 200). El sistema calculará automáticamente el intervalo entre lotes.', 'wc-pbm'); ?>
                            </p>
                            <p id="pbm-interval-preview" style="margin-top: 8px; padding: 8px; background: #f0f0f0; border-radius: 5px; display: none;">
                                <strong><?php esc_html_e('Intervalo calculado:', 'wc-pbm'); ?></strong> <span id="pbm-interval-value"></span>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit" style="text-align:right">
                    <button type="submit" id="pbm-send-btn" class="button" style="border: none;padding: 10px 30px; background: #1a1a1a;color: #ffffff;" disabled>
                        <?php esc_html_e('Enviar Emails', 'wc-pbm'); ?>
                    </button>
                </p>
            </form>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    let recipientsData = null;

                    // Calcular y mostrar intervalo en tiempo real
                    function updateIntervalPreview() {
                        const batchSize = parseInt($('#pbm_batch_size').val()) || 30;
                        const perHour = parseInt($('#pbm_emails_per_hour').val()) || 200;
                        const intervalMinutes = Math.ceil((batchSize / perHour) * 60);

                        if (perHour > 0 && batchSize > 0) {
                            $('#pbm-interval-value').text(intervalMinutes + ' <?php echo esc_js(__('minutos entre lotes', 'wc-pbm')); ?>');
                            $('#pbm-interval-preview').slideDown();
                        }
                    }

                    $('#pbm_batch_size, #pbm_emails_per_hour').on('input', updateIntervalPreview);
                    updateIntervalPreview();

                    // Vista previa
                    $('#pbm-preview-btn').on('click', function(e) {
                        e.preventDefault();

                        const productId = $('#pbm_product_id').val();
                        if (!productId) {
                            alert('<?php echo esc_js(__('Por favor selecciona un producto', 'wc-pbm')); ?>');
                            return;
                        }

                        $(this).prop('disabled', true).text('<?php echo esc_js(__('Cargando...', 'wc-pbm')); ?>');

                        $.post(ajaxurl, {
                            action: 'pbm_preview_recipients',
                            product_id: productId,
                            nonce: $('#pbm_nonce').val()
                        }, function(response) {
                            if (response.success) {
                                recipientsData = response.data;

                                // Mostrar estadísticas
                                $('#pbm-preview-content').html(
                                    '<p style="margin: 0 0 5px;"><strong><?php echo esc_js(__('Total de destinatarios únicos:', 'wc-pbm')); ?></strong> ' + response.data.total + '</p>' +
                                    '<p style="margin: 0 0 5px;"><strong><?php echo esc_js(__('Pedidos encontrados:', 'wc-pbm')); ?></strong> ' + response.data.orders_count + '</p>' +
                                    '<p style="margin: 0;"><strong><?php echo esc_js(__('Suscripciones activas:', 'wc-pbm')); ?></strong> ' + response.data.subscriptions_count + '</p>'
                                );

                                // Mostrar lista de emails separados por coma
                                if (response.data.emails && response.data.emails.length > 0) {
                                    $('#pbm-emails-content').text(response.data.emails.join(', '));
                                } else {
                                    $('#pbm-emails-content').text('<?php echo esc_js(__('No se encontraron emails', 'wc-pbm')); ?>');
                                }

                                $('#pbm-preview-results').slideDown();
                                $('#pbm-send-btn').prop('disabled', false);
                            } else {
                                alert(response.data.message || '<?php echo esc_js(__('Error al obtener destinatarios', 'wc-pbm')); ?>');
                            }
                        }).always(function() {
                            $('#pbm-preview-btn').prop('disabled', false).text('<?php echo esc_js(__('Vista Previa de Destinatarios', 'wc-pbm')); ?>');
                        });
                    });

                    // Enviar broadcast
                    $('#pbm-broadcast-form').on('submit', function(e) {
                        e.preventDefault();

                        if (!recipientsData || recipientsData.total === 0) {
                            alert('<?php echo esc_js(__('Primero debes hacer una vista previa', 'wc-pbm')); ?>');
                            return;
                        }

                        if (!confirm('<?php echo esc_js(__('¿Estás seguro de enviar este broadcast a', 'wc-pbm')); ?> ' + recipientsData.total + ' <?php echo esc_js(__('destinatarios?', 'wc-pbm')); ?>')) {
                            return;
                        }

                        $('#pbm-send-btn').prop('disabled', true).text('<?php echo esc_js(__('Programando envíos...', 'wc-pbm')); ?>');

                        $.post(ajaxurl, {
                            action: 'pbm_send_broadcast',
                            product_id: $('#pbm_product_id').val(),
                            subject: $('#pbm_subject').val(),
                            message: $('#pbm_message').val(),
                            batch_size: $('#pbm_batch_size').val(),
                            emails_per_hour: $('#pbm_emails_per_hour').val(),
                            nonce: $('#pbm_nonce').val()
                        }, function(response) {
                            if (response.success) {
                                alert(response.data.message);
                                location.reload();
                            } else {
                                alert(response.data.message || '<?php echo esc_js(__('Error al programar el envío', 'wc-pbm')); ?>');
                                $('#pbm-send-btn').prop('disabled', false).text('<?php echo esc_js(__('Enviar Emails', 'wc-pbm')); ?>');
                            }
                        });
                    });
                });
            </script>

        <?php endif; // end broadcast tab 
        ?>

        <?php if ($current_tab === 'scheduled') : ?>
            <?php render_scheduled_emails_tab(); ?>
        <?php endif; ?>

        <!-- Mensaje siempre visible -->
        <div style="background: #f1f1f1; border-radius: 10px; padding: 10px 30px; margin-top: 20px;">
            <p>
                <strong><?php esc_html_e('Importante:', 'wc-pbm'); ?></strong>
                <?php esc_html_e('Los correos se enviarán en segundo plano usando Action Scheduler.', 'wc-pbm'); ?>
                <a target="_blank" href="<?php echo esc_url($scheduled_actions_url); ?>">
                    <?php esc_html_e('Monitoriza el progreso aquí', 'wc-pbm'); ?>
                </a>.
            </p>
        </div>

        <div style="background: #f1f1f1; border-radius: 10px; padding: 20px 30px; margin-top: 20px;">
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
