<?php

/**
 * Handlers AJAX para el plugin
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer;

defined('ABSPATH') || exit;

/**
 * AJAX: Vista previa de destinatarios (dry-run)
 *
 * @return void
 */
function ajax_preview_recipients()
{
    check_ajax_referer('pbm_broadcast_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    try {
        $source = sanitize_text_field($_POST['source'] ?? 'product');
        $available_sources = get_recipient_sources();
        $product_id = absint($_POST['product_id'] ?? 0);
        $role = sanitize_text_field($_POST['role'] ?? '');
        $mailmint_list_id = absint($_POST['mailmint_list_id'] ?? 0);

        if (! isset($available_sources[$source])) {
            wp_send_json_error(array('message' => __('Fuente inválida', 'wc-pbm')));
        }

        if (isset($available_sources[$source]['enabled']) && ! $available_sources[$source]['enabled']) {
            wp_send_json_error(array('message' => __('La fuente seleccionada no está disponible', 'wc-pbm')));
        }

        if ('product' === $source && ! $product_id) {
            wp_send_json_error(array('message' => __('ID de producto inválido', 'wc-pbm')));
        }

        if ('role' === $source && ! $role) {
            wp_send_json_error(array('message' => __('Rol inválido', 'wc-pbm')));
        }

        if ('mailmint' === $source && ! $mailmint_list_id) {
            wp_send_json_error(array('message' => __('Lista de Mail Mint inválida', 'wc-pbm')));
        }

        $recipients = get_recipients_by_source($source, array(
            'product_id'      => $product_id,
            'role'            => $role,
            'mailmint_list_id' => $mailmint_list_id,
        ));

        // Extraer solo los emails para la lista separada por comas
        $emails = array_keys($recipients);

        $orders_count = 0;
        $subscriptions_count = 0;
        if ('product' === $source && $product_id) {
            $orders_count = get_orders_count_for_product($product_id);
            $subscriptions_count = get_subscriptions_count_for_product($product_id);
        }

        wp_send_json_success(array(
            'total'                => count($recipients),
            'orders_count'         => $orders_count,
            'subscriptions_count'  => $subscriptions_count,
            'emails'               => $emails,
        ));
    } catch (\Throwable $e) {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PBM preview error: ' . $e->getMessage());
        }
        wp_send_json_error(array('message' => __('Error interno. Revisa el log.', 'wc-pbm')));
    }
}

/**
 * AJAX: Conteo de destinatarios por fuente/selector (sin devolver emails)
 *
 * @return void
 */
function ajax_count_recipients()
{
    check_ajax_referer('pbm_broadcast_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $source = sanitize_text_field($_POST['source'] ?? 'product');
    $available_sources = get_recipient_sources();
    $product_id = absint($_POST['product_id'] ?? 0);
    $role = sanitize_text_field($_POST['role'] ?? '');
    $mailmint_list_id = absint($_POST['mailmint_list_id'] ?? 0);

    if (! isset($available_sources[$source])) {
        wp_send_json_error(array('message' => __('Fuente inválida', 'wc-pbm')));
    }

    if (isset($available_sources[$source]['enabled']) && ! $available_sources[$source]['enabled']) {
        wp_send_json_error(array('message' => __('La fuente seleccionada no está disponible', 'wc-pbm')));
    }

    if ('product' === $source && ! $product_id) {
        wp_send_json_success(array('total' => 0));
    }

    if ('role' === $source && ! $role) {
        wp_send_json_success(array('total' => 0));
    }

    if ('mailmint' === $source && ! $mailmint_list_id) {
        wp_send_json_success(array('total' => 0));
    }

    $recipients = get_recipients_by_source($source, array(
        'product_id'       => $product_id,
        'role'             => $role,
        'mailmint_list_id' => $mailmint_list_id,
    ));

    wp_send_json_success(array(
        'total' => count($recipients),
    ));
}

/**
 * AJAX: Resuelve emails reales de un item de audiencia.
 *
 * @return void
 */
function ajax_resolve_audience_item()
{
    check_ajax_referer('pbm_broadcast_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $source = sanitize_text_field($_POST['source'] ?? 'product');
    $available_sources = get_recipient_sources();
    $selector_value = sanitize_text_field($_POST['selector_value'] ?? '');

    if (! isset($available_sources[$source])) {
        wp_send_json_error(array('message' => __('Fuente inválida', 'wc-pbm')));
    }

    if (isset($available_sources[$source]['enabled']) && ! $available_sources[$source]['enabled']) {
        wp_send_json_error(array('message' => __('La fuente seleccionada no está disponible', 'wc-pbm')));
    }

    $args = array(
        'product_id'       => 0,
        'role'             => '',
        'mailmint_list_id' => 0,
    );

    if ('product' === $source) {
        $args['product_id'] = absint($selector_value);
        if (! $args['product_id']) {
            wp_send_json_success(array('total' => 0, 'emails' => array()));
        }
    } elseif ('role' === $source) {
        $args['role'] = $selector_value;
        if (! $args['role']) {
            wp_send_json_success(array('total' => 0, 'emails' => array()));
        }
    } elseif ('mailmint' === $source) {
        $args['mailmint_list_id'] = absint($selector_value);
        if (! $args['mailmint_list_id']) {
            wp_send_json_success(array('total' => 0, 'emails' => array()));
        }
    }

    $recipients = get_recipients_by_source($source, $args);
    $emails = array_map('strtolower', array_keys($recipients));
    $emails = array_values(array_unique(array_filter($emails, 'is_email')));

    wp_send_json_success(array(
        'total'  => count($emails),
        'emails' => $emails,
    ));
}

/**
 * AJAX: Enviar broadcast (programa las acciones)
 *
 * @return void
 */
function ajax_send_broadcast()
{
    check_ajax_referer('pbm_broadcast_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $source = sanitize_text_field($_POST['source'] ?? 'product');
    $available_sources = get_recipient_sources();
    $product_id = absint($_POST['product_id'] ?? 0);
    $role = sanitize_text_field($_POST['role'] ?? '');
    $mailmint_list_id = absint($_POST['mailmint_list_id'] ?? 0);
    $subject    = sanitize_text_field($_POST['subject'] ?? '');
    $message    = wp_kses_post($_POST['message'] ?? '');
    $batch_size = absint($_POST['batch_size'] ?? 30);
    $emails_per_hour = absint($_POST['emails_per_hour'] ?? 200);
    $schedule_enabled = ! empty($_POST['schedule_enabled']) && '1' === sanitize_text_field(wp_unslash($_POST['schedule_enabled']));
    $scheduled_datetime = sanitize_text_field($_POST['scheduled_datetime'] ?? '');

    if (! $subject || ! $message) {
        wp_send_json_error(array('message' => __('Faltan datos requeridos', 'wc-pbm')));
    }

    if (! isset($available_sources[$source])) {
        wp_send_json_error(array('message' => __('Fuente inválida', 'wc-pbm')));
    }

    if (isset($available_sources[$source]['enabled']) && ! $available_sources[$source]['enabled']) {
        wp_send_json_error(array('message' => __('La fuente seleccionada no está disponible', 'wc-pbm')));
    }

    if ($batch_size < 1 || $emails_per_hour < 1) {
        wp_send_json_error(array('message' => __('Parámetros de envío inválidos', 'wc-pbm')));
    }

    if ('product' === $source && ! $product_id) {
        wp_send_json_error(array('message' => __('ID de producto inválido', 'wc-pbm')));
    }

    if ('role' === $source && ! $role) {
        wp_send_json_error(array('message' => __('Rol inválido', 'wc-pbm')));
    }

    if ('mailmint' === $source && ! $mailmint_list_id) {
        wp_send_json_error(array('message' => __('Lista de Mail Mint inválida', 'wc-pbm')));
    }

    $recipients = get_recipients_by_source($source, array(
        'product_id'       => $product_id,
        'role'             => $role,
        'mailmint_list_id' => $mailmint_list_id,
    ));
    $audience_label = get_source_audience_label($source, $product_id, $role, $mailmint_list_id);

    if (empty($recipients)) {
        wp_send_json_error(array('message' => __('No se encontraron destinatarios', 'wc-pbm')));
    }

    if ($schedule_enabled) {
        if (! $scheduled_datetime) {
            wp_send_json_error(array('message' => __('La fecha de programación es obligatoria', 'wc-pbm')));
        }

        $local_datetime = str_replace('T', ' ', $scheduled_datetime) . ':00';
        $gmt_datetime = get_gmt_from_date($local_datetime);
        $scheduled_timestamp = strtotime($gmt_datetime . ' UTC');

        if ($scheduled_timestamp <= time()) {
            wp_send_json_error(array('message' => __('La fecha debe ser futura', 'wc-pbm')));
        }

        global $wpdb;
        $table = $wpdb->prefix . 'pbm_scheduled_emails';

        $inserted = $wpdb->insert(
            $table,
            array(
                'user_role'       => 'scheduled',
                'subject'         => $subject,
                'message'         => $message,
                'scheduled_at'    => $gmt_datetime,
                'batch_size'      => $batch_size,
                'emails_per_hour' => $emails_per_hour,
                'status'          => 'pending',
                'created_at'      => current_time('mysql', true),
            ),
            array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
        );

        if (! $inserted) {
            wp_send_json_error(array('message' => __('Error al guardar en base de datos', 'wc-pbm')));
        }

        $scheduled_id = (int) $wpdb->insert_id;
        add_option('pbm_scheduled_recipients_' . $scheduled_id, $recipients, '', false);
        add_option('pbm_delivery_meta_' . $scheduled_id, array(
            'type'     => 'scheduled',
            'source'   => $source,
            'audience' => $audience_label,
        ), '', false);

        if (function_exists('as_schedule_single_action')) {
            as_schedule_single_action(
                $scheduled_timestamp,
                'pbm_execute_scheduled_email',
                array($scheduled_id),
                'product-broadcast-mailer'
            );
        }

        wp_send_json_success(array(
            'message' => __('Envío programado correctamente', 'wc-pbm'),
        ));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'pbm_scheduled_emails';
    $inserted = $wpdb->insert(
        $table,
        array(
            'user_role'       => 'instant',
            'subject'         => $subject,
            'message'         => $message,
            'scheduled_at'    => current_time('mysql', true),
            'batch_size'      => $batch_size,
            'emails_per_hour' => $emails_per_hour,
            'status'          => 'running',
            'created_at'      => current_time('mysql', true),
        ),
        array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
    );

    if (! $inserted) {
        wp_send_json_error(array('message' => __('Error al guardar en base de datos', 'wc-pbm')));
    }

    $delivery_id = (int) $wpdb->insert_id;
    add_option('pbm_delivery_meta_' . $delivery_id, array(
        'type'     => 'instant',
        'source'   => $source,
        'audience' => $audience_label,
    ), '', false);

    $scheduled_count = schedule_email_batches($recipients, $subject, $message, $batch_size, $emails_per_hour, $delivery_id);
    update_scheduled_email_status($delivery_id, 'completed');

    wp_send_json_success(array(
        'message' => sprintf(
            /* translators: %1$d: número de lotes, %2$d: número total de destinatarios */
            __('Se programaron %1$d lotes para %2$d destinatarios. Revisa WooCommerce > Estado > Acciones Programadas.', 'wc-pbm'),
            $scheduled_count,
            count($recipients)
        ),
    ));
}

/**
 * Devuelve una etiqueta legible de audiencia según fuente.
 *
 * @param string $source Fuente seleccionada.
 * @param int    $product_id ID de producto.
 * @param string $role Rol WP.
 * @param int    $mailmint_list_id ID de lista Mail Mint.
 * @return string
 */
function get_source_audience_label($source, $product_id, $role, $mailmint_list_id)
{
    if ('product' === $source && $product_id > 0) {
        return sprintf(__('Producto #%d', 'wc-pbm'), $product_id);
    }

    if ('role' === $source && $role) {
        return sprintf(__('Rol: %s', 'wc-pbm'), $role);
    }

    if ('mailmint' === $source && $mailmint_list_id > 0) {
        return sprintf(__('Lista Mail Mint #%d', 'wc-pbm'), $mailmint_list_id);
    }

    return __('Audiencia no definida', 'wc-pbm');
}

/**
 * AJAX: Vista previa de destinatarios por rol
 *
 * @return void
 */
function ajax_preview_role_recipients()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $role = sanitize_text_field($_POST['role'] ?? '');

    if (! $role) {
        wp_send_json_error(array('message' => __('Rol inválido', 'wc-pbm')));
    }

    $users = get_users_by_role($role);

    $formatted_emails = array();
    foreach ($users as $email => $data) {
        $formatted_emails[] = $data['name'] . ' (' . $email . ')';
    }

    wp_send_json_success(array(
        'total' => count($users),
        'emails' => $formatted_emails,
    ));
}

/**
 * AJAX: Crear envío programado
 *
 * @return void
 */
function ajax_create_scheduled_email()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $role               = sanitize_text_field($_POST['role'] ?? '');
    $subject            = sanitize_text_field($_POST['subject'] ?? '');
    $message            = wp_kses_post($_POST['message'] ?? '');
    $scheduled_datetime = sanitize_text_field($_POST['scheduled_datetime'] ?? '');
    $batch_size         = absint($_POST['batch_size'] ?? 30);
    $emails_per_hour    = absint($_POST['emails_per_hour'] ?? 200);

    if (! $role || ! $subject || ! $message || ! $scheduled_datetime) {
        wp_send_json_error(array('message' => __('Faltan datos requeridos', 'wc-pbm')));
    }

    // Convertir datetime-local a timestamp considerando zona horaria de WordPress
    $local_datetime = str_replace('T', ' ', $scheduled_datetime) . ':00';
    $gmt_datetime = get_gmt_from_date($local_datetime);
    $scheduled_timestamp = strtotime($gmt_datetime . ' UTC');

    if ($scheduled_timestamp <= time()) {
        wp_send_json_error(array('message' => __('La fecha debe ser futura', 'wc-pbm')));
    }

    global $wpdb;
    $table = $wpdb->prefix . 'pbm_scheduled_emails';

    $inserted = $wpdb->insert(
        $table,
        array(
            'user_role'        => $role,
            'subject'          => $subject,
            'message'          => $message,
            'scheduled_at'     => $gmt_datetime,
            'batch_size'       => $batch_size,
            'emails_per_hour'  => $emails_per_hour,
            'status'           => 'pending',
            'created_at'       => current_time('mysql', true),
        ),
        array('%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s')
    );

    if (! $inserted) {
        wp_send_json_error(array('message' => __('Error al guardar en base de datos', 'wc-pbm')));
    }

    $scheduled_id = $wpdb->insert_id;

    // Programar acción en Action Scheduler
    if (function_exists('as_schedule_single_action')) {
        as_schedule_single_action(
            $scheduled_timestamp,
            'pbm_execute_scheduled_email',
            array($scheduled_id),
            'product-broadcast-mailer'
        );
    }

    wp_send_json_success(array(
        'message' => __('Envío programado correctamente', 'wc-pbm'),
    ));
}

/**
 * AJAX: Cancelar envío programado
 *
 * @return void
 */
function ajax_cancel_scheduled_email()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $scheduled_id = absint($_POST['scheduled_id'] ?? 0);

    if (! $scheduled_id) {
        wp_send_json_error(array('message' => __('ID inválido', 'wc-pbm')));
    }

    update_scheduled_email_status($scheduled_id, 'cancelled');

    // Cancelar acción en Action Scheduler
    if (function_exists('as_unschedule_action')) {
        as_unschedule_action('pbm_execute_scheduled_email', array($scheduled_id), 'product-broadcast-mailer');
    }

    wp_send_json_success(array(
        'message' => __('Envío cancelado', 'wc-pbm'),
    ));
}

/**
 * AJAX: Ejecutar envío programado inmediatamente
 *
 * @return void
 */
function ajax_run_scheduled_now()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $scheduled_id = absint($_POST['scheduled_id'] ?? 0);

    if (! $scheduled_id) {
        wp_send_json_error(array('message' => __('ID inválido', 'wc-pbm')));
    }

    // Cancelar la acción programada original
    if (function_exists('as_unschedule_action')) {
        as_unschedule_action('pbm_execute_scheduled_email', array($scheduled_id), 'product-broadcast-mailer');
    }

    // Programar para ejecución inmediata
    if (function_exists('as_schedule_single_action')) {
        as_schedule_single_action(
            time(),
            'pbm_execute_scheduled_email',
            array($scheduled_id),
            'product-broadcast-mailer'
        );
    }

    wp_send_json_success(array(
        'message' => __('Envío programado para ejecución inmediata', 'wc-pbm'),
    ));
}

/**
 * AJAX: Obtener logs de un envío
 *
 * @return void
 */
function ajax_get_scheduled_logs()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $scheduled_id = absint($_POST['scheduled_id'] ?? 0);

    if (! $scheduled_id) {
        wp_send_json_error(array('message' => __('ID inválido', 'wc-pbm')));
    }

    $logs = get_scheduled_logs($scheduled_id);

    if (empty($logs)) {
        wp_send_json_success(array(
            'html' => '<p>' . esc_html__('No hay logs disponibles.', 'wc-pbm') . '</p>',
        ));
    }

    ob_start();
?>
    <table class="widefat" style="width: 100%;">
        <thead>
            <tr>
                <th><?php esc_html_e('Inicio', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Fin', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Enviados', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Fallidos', 'wc-pbm'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log) : ?>
                <tr>
                    <td><?php echo esc_html(get_date_from_gmt($log->started_at, 'd/m/Y H:i:s')); ?></td>
                    <td><?php echo $log->completed_at ? esc_html(get_date_from_gmt($log->completed_at, 'd/m/Y H:i:s')) : '-'; ?></td>
                    <td><?php echo esc_html($log->total_sent); ?></td>
                    <td><?php echo esc_html($log->total_failed); ?></td>
                </tr>
                <?php if ($log->error_message) : ?>
                    <tr>
                        <td colspan="4">
                            <strong><?php esc_html_e('Error:', 'wc-pbm'); ?></strong>
                            <?php echo esc_html($log->error_message); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
    $html = ob_get_clean();

    wp_send_json_success(array('html' => $html));
}

/**
 * AJAX: Borrar un envío programado y sus logs
 *
 * @return void
 */
function ajax_delete_scheduled_email()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $scheduled_id = absint($_POST['scheduled_id'] ?? 0);

    if (!$scheduled_id) {
        wp_send_json_error(array('message' => __('ID inválido', 'wc-pbm')));
    }

    if (delete_scheduled_email_with_logs($scheduled_id)) {
        wp_send_json_success(array('message' => __('Envío eliminado correctamente', 'wc-pbm')));
    } else {
        wp_send_json_error(array('message' => __('Error al eliminar', 'wc-pbm')));
    }
}

/**
 * AJAX: Borrado masivo de envíos por estado
 *
 * @return void
 */
function ajax_bulk_delete_scheduled()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $status = sanitize_text_field($_POST['status'] ?? '');

    if (!in_array($status, array('completed', 'cancelled'), true)) {
        wp_send_json_error(array('message' => __('Estado inválido', 'wc-pbm')));
    }

    $deleted = bulk_delete_scheduled_by_status($status);

    wp_send_json_success(array(
        'message' => sprintf(__('%d envíos eliminados', 'wc-pbm'), $deleted),
        'deleted' => $deleted
    ));
}
