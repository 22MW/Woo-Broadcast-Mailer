<?php

/**
 * Plugin Name: Woo Broadcast Mailer
 * Description: Sistema de envío masivo de emails: envía a compradores de productos específicos o programa envíos por rol de usuario. Compatible con HPOS y Action Scheduler.
 * Version: 1.1.0
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
            <a class="pbm-brand-link" href="https://22mw.online/" target="_blank" rel="noopener noreferrer">
                <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/img/22mw.svg'); ?>" alt="22MW">
            </a>
        </h1>
        <div id="pbm-admin-app"></div>
        <form id="pbm-broadcast-form" method="post">
            <?php wp_nonce_field('pbm_broadcast_action', 'pbm_nonce'); ?>
            <input type="hidden" id="pbm_audience_items" name="pbm_audience_items" value="">
            <input type="hidden" id="pbm_manual_emails" name="pbm_manual_emails" value="">

            <table class="form-table">
                <tr id="pbm-source-row-legacy">
                    <th scope="row">
                        <label for="pbm_recipient_source"><?php esc_html_e('Fuente', 'wc-pbm'); ?></label>
                    </th>
                    <td>
                        <select id="pbm_recipient_source" name="pbm_recipient_source">
                            <?php foreach ($recipient_sources as $source_key => $source_data) : ?>
                                <option value="<?php echo esc_attr($source_key); ?>" <?php disabled(isset($source_data['enabled']) && ! $source_data['enabled']); ?>>
                                    <?php echo esc_html($source_data['label'] ?? $source_key); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($recipient_sources['mailmint']) && empty($recipient_sources['mailmint']['enabled'])) : ?>
                            <p class="description pbm-warning-text">
                                <?php esc_html_e('Mail Mint no está activo o no tiene tablas creadas. Esta fuente queda deshabilitada.', 'wc-pbm'); ?>
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr id="pbm-selector-row-legacy">
                    <th scope="row"><?php esc_html_e('Selector', 'wc-pbm'); ?></th>
                    <td>
                        <div id="pbm-source-product" class="pbm-selector-legacy-block">
                            <label for="pbm_product_id"><?php esc_html_e('Producto', 'wc-pbm'); ?></label>
                            <?php render_product_selector(); ?>
                            <p class="description">
                                <?php esc_html_e('Selecciona el producto. Se incluirán automáticamente todas las variaciones y suscripciones relacionadas.', 'wc-pbm'); ?>
                            </p>
                        </div>

                        <div id="pbm-source-role" class="pbm-selector-legacy-block" style="display:none;">
                            <label for="pbm_user_role"><?php esc_html_e('Rol de Usuario', 'wc-pbm'); ?></label>
                            <?php render_role_selector(); ?>
                            <p class="description">
                                <?php esc_html_e('Selecciona el rol de WordPress para obtener destinatarios.', 'wc-pbm'); ?>
                            </p>
                        </div>

                        <div id="pbm-source-mailmint" class="pbm-selector-legacy-block" style="display:none;">
                            <label for="pbm_mailmint_list"><?php esc_html_e('Lista Mail Mint', 'wc-pbm'); ?></label>
                            <?php render_mailmint_list_selector(); ?>
                            <p class="description">
                                <?php esc_html_e('Selecciona la lista de Mail Mint para obtener destinatarios suscritos.', 'wc-pbm'); ?>
                            </p>
                        </div>

                        <p class="pbm-preview-button-wrap">
                            <button type="button" id="pbm-preview-btn" class="button">
                                <?php esc_html_e('Vista Previa de Destinatarios', 'wc-pbm'); ?>
                            </button>
                        </p>

                        <div id="pbm-preview-results" class="pbm-preview-results" style="display:none;">
                            <h4 class="pbm-preview-title"><?php esc_html_e('Resumen de Destinatarios', 'wc-pbm'); ?></h4>
                            <div id="pbm-preview-content"></div>
                            <div id="pbm-emails-list" class="pbm-emails-list">
                                <strong><?php esc_html_e('Emails:', 'wc-pbm'); ?></strong><br>
                                <span id="pbm-emails-content" class="pbm-emails-content"></span>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr id="pbm-subject-row-legacy">
                    <th scope="row">
                        <label for="pbm_subject"><?php esc_html_e('Asunto', 'wc-pbm'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="pbm_subject" name="pbm_subject" class="regular-text" required>
                    </td>
                </tr>

                <tr id="pbm-message-row-legacy">
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

                <tr id="pbm-batch-row-legacy">
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

                <tr id="pbm-hour-row-legacy">
                    <th scope="row">
                        <label for="pbm_emails_per_hour"><?php esc_html_e('Emails por hora', 'wc-pbm'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="pbm_emails_per_hour" name="pbm_emails_per_hour" value="200" min="10" max="1000" class="small-text">
                        <p class="description">
                            <?php esc_html_e('Límite de emails a enviar por hora (ej: 200). El sistema calculará automáticamente el intervalo entre lotes.', 'wc-pbm'); ?>
                        </p>
                        <p id="pbm-interval-preview" class="pbm-interval-preview" style="display: none;">
                            <strong><?php esc_html_e('Intervalo calculado:', 'wc-pbm'); ?></strong> <span id="pbm-interval-value"></span>
                        </p>
                    </td>
                </tr>

                <tr id="pbm-schedule-row-legacy">
                    <th scope="row">
                        <label for="pbm_schedule_enabled"><?php esc_html_e('Programación', 'wc-pbm'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" id="pbm_schedule_enabled" name="pbm_schedule_enabled" value="1">
                            <?php esc_html_e('Programar envío', 'wc-pbm'); ?>
                        </label>
                    </td>
                </tr>

                <tr id="pbm_schedule_datetime_row" style="display:none;">
                    <th scope="row">
                        <label for="pbm_scheduled_datetime"><?php esc_html_e('Fecha y Hora de Envío', 'wc-pbm'); ?></label>
                    </th>
                    <td>
                        <input type="datetime-local" id="pbm_scheduled_datetime" name="pbm_scheduled_datetime">
                        <p class="description">
                            <?php esc_html_e('Si activas programación, el envío se ejecutará a esta hora.', 'wc-pbm'); ?>
                        </p>
                    </td>
                </tr>
            </table>

            <p class="submit pbm-submit-wrap" id="pbm-submit-wrap-legacy">
                <button type="submit" id="pbm-send-btn" class="button" disabled>
                    <?php esc_html_e('Enviar Emails', 'wc-pbm'); ?>
                </button>
            </p>
        </form>

        <script type="text/javascript">
            jQuery(document).ready(function($) {
                let recipientsData = null;

                function updateIntervalPreview() {
                    const batchSize = parseInt($('#pbm_batch_size').val(), 10) || 30;
                    const perHour = parseInt($('#pbm_emails_per_hour').val(), 10) || 200;
                    const intervalMinutes = Math.ceil((batchSize / perHour) * 60);

                    if (perHour > 0 && batchSize > 0) {
                        $('#pbm-interval-value').text(intervalMinutes + ' <?php echo esc_js(__('minutos entre lotes', 'wc-pbm')); ?>');
                        $('#pbm-interval-preview').slideDown();
                    }
                }

                function toggleScheduleFields() {
                    const isChecked = $('#pbm_schedule_enabled').is(':checked');
                    $('#pbm_schedule_datetime_row').toggle(isChecked);
                }

                function toggleSourceFields() {
                    const source = $('#pbm_recipient_source').val();
                    const mailmintEnabled = <?php echo (isset($recipient_sources['mailmint']) && ! empty($recipient_sources['mailmint']['enabled'])) ? 'true' : 'false'; ?>;

                    if (source === 'mailmint' && !mailmintEnabled) {
                        alert('<?php echo esc_js(__('Mail Mint no está disponible en este sitio.', 'wc-pbm')); ?>');
                        $('#pbm_recipient_source').val('product');
                    }

                    const normalizedSource = $('#pbm_recipient_source').val();

                    $('#pbm_product_id').prop('required', false).prop('disabled', true);
                    $('#pbm_user_role').prop('required', false).prop('disabled', true);
                    $('#pbm_mailmint_list').prop('required', false).prop('disabled', true);

                    $('#pbm-source-product').hide();
                    $('#pbm-source-role').hide();
                    $('#pbm-source-mailmint').hide();
                    $('#pbm-preview-results').hide();
                    $('#pbm-send-btn').prop('disabled', true);
                    recipientsData = null;

                    if (normalizedSource === 'product') {
                        $('#pbm-source-product').show();
                        $('#pbm_product_id').prop('required', true).prop('disabled', false);
                    } else if (normalizedSource === 'role') {
                        $('#pbm-source-role').show();
                        $('#pbm_user_role').prop('required', true).prop('disabled', false);
                    } else if (normalizedSource === 'mailmint') {
                        $('#pbm-source-mailmint').show();
                        $('#pbm_mailmint_list').prop('required', true).prop('disabled', false);
                    }
                }

                $('#pbm_batch_size, #pbm_emails_per_hour').on('input', updateIntervalPreview);
                $('#pbm_schedule_enabled').on('change', toggleScheduleFields);
                $('#pbm_recipient_source').on('change', toggleSourceFields);
                updateIntervalPreview();
                toggleScheduleFields();
                toggleSourceFields();

                $(document).on('click', '#pbm-preview-btn', function(e) {
                    e.preventDefault();

                    const source = $('#pbm_recipient_source').val();
                    const productId = $('#pbm_product_id').val();
                    const role = $('#pbm_user_role').val();
                    const mailmintList = $('#pbm_mailmint_list').val();
                    const audienceItems = $('#pbm_audience_items').val();
                    const manualEmails = $('#pbm_manual_emails').val();
                    const hasGlobalAudience = (audienceItems && audienceItems !== '[]') || (manualEmails && manualEmails !== '[]');

                    if (!hasGlobalAudience && source === 'product' && !productId) {
                        alert('<?php echo esc_js(__('Por favor selecciona un producto', 'wc-pbm')); ?>');
                        return;
                    }

                    if (!hasGlobalAudience && source === 'role' && !role) {
                        alert('<?php echo esc_js(__('Por favor selecciona un rol', 'wc-pbm')); ?>');
                        return;
                    }

                    if (!hasGlobalAudience && source === 'mailmint' && !mailmintList) {
                        alert('<?php echo esc_js(__('Por favor selecciona una lista de Mail Mint', 'wc-pbm')); ?>');
                        return;
                    }

                    $(this).prop('disabled', true).text('<?php echo esc_js(__('Cargando...', 'wc-pbm')); ?>');

                    $.post(ajaxurl, {
                        action: 'pbm_preview_recipients',
                        source: source,
                        product_id: productId,
                        role: role,
                        mailmint_list_id: mailmintList,
                        audience_items: audienceItems,
                        manual_emails: manualEmails,
                        nonce: $('#pbm_nonce').val()
                    }, function(response) {
                        if (response.success) {
                            recipientsData = response.data;

                            let previewHtml = '<p style="margin: 0 0 5px;"><strong><?php echo esc_js(__('Total de destinatarios únicos:', 'wc-pbm')); ?></strong> ' + response.data.total + '</p>';

                            if (response.data.is_global_audience) {
                                previewHtml += '<p style="margin: 0;"><strong><?php echo esc_js(__('Fuente:', 'wc-pbm')); ?></strong> <?php echo esc_js(__('Lista global combinada', 'wc-pbm')); ?></p>';
                            } else if (source === 'product') {
                                previewHtml += '<p style="margin: 0 0 5px;"><strong><?php echo esc_js(__('Pedidos encontrados:', 'wc-pbm')); ?></strong> ' + response.data.orders_count + '</p>';
                                previewHtml += '<p style="margin: 0;"><strong><?php echo esc_js(__('Suscripciones activas:', 'wc-pbm')); ?></strong> ' + response.data.subscriptions_count + '</p>';
                            } else if (source === 'role') {
                                previewHtml += '<p style="margin: 0;"><strong><?php echo esc_js(__('Fuente:', 'wc-pbm')); ?></strong> <?php echo esc_js(__('Usuarios por rol', 'wc-pbm')); ?></p>';
                            } else if (source === 'mailmint') {
                                previewHtml += '<p style="margin: 0;"><strong><?php echo esc_js(__('Fuente:', 'wc-pbm')); ?></strong> <?php echo esc_js(__('Lista Mail Mint (suscritos)', 'wc-pbm')); ?></p>';
                            }

                            $('#pbm-preview-content').html(previewHtml);

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
                    }).fail(function() {
                        alert('<?php echo esc_js(__('No se pudo completar la vista previa. Revisa consola/red.', 'wc-pbm')); ?>');
                    }).always(function() {
                        $('#pbm-preview-btn').prop('disabled', false).text('<?php echo esc_js(__('Vista Previa de Destinatarios', 'wc-pbm')); ?>');
                    });
                });

                $('#pbm-broadcast-form').on('submit', function(e) {
                    e.preventDefault();

                    if (!recipientsData || recipientsData.total === 0) {
                        alert('<?php echo esc_js(__('Primero debes hacer una vista previa', 'wc-pbm')); ?>');
                        return;
                    }

                    if (!confirm('<?php echo esc_js(__('¿Estás seguro de enviar este broadcast a', 'wc-pbm')); ?> ' + recipientsData.total + ' <?php echo esc_js(__('destinatarios?', 'wc-pbm')); ?>')) {
                        return;
                    }

                    const isScheduled = $('#pbm_schedule_enabled').is(':checked');
                    const scheduledDatetime = $('#pbm_scheduled_datetime').val();

                    if (isScheduled && !scheduledDatetime) {
                        alert('<?php echo esc_js(__('Debes indicar fecha y hora para programar', 'wc-pbm')); ?>');
                        return;
                    }

                    if (typeof window.tinyMCE !== 'undefined' && window.tinyMCE.get('pbm_message')) {
                        window.tinyMCE.get('pbm_message').save();
                    }

                    const messageContent = $('#pbm_message').val();
                    if (!messageContent || !messageContent.trim()) {
                        alert('<?php echo esc_js(__('El mensaje no puede estar vacío', 'wc-pbm')); ?>');
                        return;
                    }

                    $('#pbm-send-btn').prop('disabled', true).text('<?php echo esc_js(__('Programando envíos...', 'wc-pbm')); ?>');

                    $.post(ajaxurl, {
                        action: 'pbm_send_broadcast',
                        source: $('#pbm_recipient_source').val(),
                        product_id: $('#pbm_product_id').val(),
                        role: $('#pbm_user_role').val(),
                        mailmint_list_id: $('#pbm_mailmint_list').val(),
                        audience_items: $('#pbm_audience_items').val(),
                        manual_emails: $('#pbm_manual_emails').val(),
                        subject: $('#pbm_subject').val(),
                        message: messageContent,
                        batch_size: $('#pbm_batch_size').val(),
                        emails_per_hour: $('#pbm_emails_per_hour').val(),
                        schedule_enabled: isScheduled ? '1' : '0',
                        scheduled_datetime: scheduledDatetime,
                        nonce: $('#pbm_nonce').val()
                    }, function(response) {
                        if (response.success) {
                            alert(response.data.message);
                            location.reload();
                        } else {
                            alert(response.data.message || '<?php echo esc_js(__('Error al programar el envío', 'wc-pbm')); ?>');
                        }
                    }).fail(function() {
                        alert('<?php echo esc_js(__('No se pudo completar el envío. Revisa consola/red.', 'wc-pbm')); ?>');
                    }).always(function() {
                        $('#pbm-send-btn').prop('disabled', false).text('<?php echo esc_js(__('Enviar Emails', 'wc-pbm')); ?>');
                    });
                });
            });
        </script>

        <div class="pbm-manage-wrap pbm-legacy-scheduled">
            <details>
                <summary class="pbm-manage-summary">
                    <?php esc_html_e('Ver envíos programados y logs', 'wc-pbm'); ?>
                </summary>
                <div class="pbm-manage-content">
                    <?php render_scheduled_emails_tab(); ?>
                </div>
            </details>
        </div>

        <!-- Mensaje siempre visible -->
        <div class="pbm-info-box pbm-info-box-compact">
            <p>
                <strong><?php esc_html_e('Importante:', 'wc-pbm'); ?></strong>
                <?php esc_html_e('Los correos se enviarán en segundo plano usando Action Scheduler.', 'wc-pbm'); ?>
                <a target="_blank" href="<?php echo esc_url($scheduled_actions_url); ?>">
                    <?php esc_html_e('Monitoriza el progreso aquí', 'wc-pbm'); ?>
                </a>.
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
