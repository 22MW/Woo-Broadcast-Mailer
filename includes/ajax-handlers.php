<?php

/**
 * Handlers AJAX para el plugin
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer;

defined('ABSPATH') || exit;

/**
 * Decodifica un array JSON desde POST.
 *
 * @param string $key Clave POST.
 * @return array
 */
function get_json_array_from_post($key)
{
    $raw = wp_unslash($_POST[$key] ?? '');
    if (! is_string($raw) || '' === trim($raw)) {
        return array();
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : array();
}

/**
 * Normaliza lista de emails.
 *
 * @param array $emails Lista de emails.
 * @return array
 */
function normalize_email_list($emails)
{
    $result = array();
    foreach ((array) $emails as $email) {
        $normalized = strtolower(trim((string) $email));
        if ($normalized && is_email($normalized)) {
            $result[$normalized] = $normalized;
        }
    }

    return array_values($result);
}

/**
 * Resuelve destinatarios globales desde items + manuales.
 *
 * @param array $audience_items Items de audiencia.
 * @param array $manual_emails Emails manuales.
 * @return array
 */
function resolve_global_recipients($audience_items, $manual_emails)
{
    $recipients = array();
    $sources = get_recipient_sources();

    foreach ((array) $audience_items as $item) {
        if (! is_array($item)) {
            continue;
        }

        $source = sanitize_text_field($item['source'] ?? '');
        $selector_value = sanitize_text_field($item['selectorValue'] ?? '');

        if (! $source || ! isset($sources[$source])) {
            continue;
        }

        if (isset($sources[$source]['enabled']) && ! $sources[$source]['enabled']) {
            continue;
        }

        $args = array(
            'product_id'       => 0,
            'role'             => '',
            'mailmint_list_id' => 0,
        );

        if ('product' === $source) {
            $args['product_id'] = absint($selector_value);
            if (! $args['product_id']) {
                continue;
            }
        } elseif ('role' === $source) {
            $args['role'] = $selector_value;
            if (! $args['role']) {
                continue;
            }
        } elseif ('mailmint' === $source) {
            $args['mailmint_list_id'] = absint($selector_value);
            if (! $args['mailmint_list_id']) {
                continue;
            }
        } else {
            continue;
        }

        $source_recipients = get_recipients_by_source($source, $args);
        foreach ($source_recipients as $email => $data) {
            if (! isset($recipients[$email])) {
                $recipients[$email] = $data;
            }
        }
    }

    $manual = normalize_email_list($manual_emails);
    foreach ($manual as $email) {
        if (! isset($recipients[$email])) {
            $recipients[$email] = array(
                'name'  => '',
                'email' => $email,
            );
        }
    }

    return $recipients;
}

/**
 * Construye metadatos resumidos de audiencia global.
 *
 * @param array $audience_items Items seleccionados.
 * @param array $manual_emails Emails manuales.
 * @return array
 */
function build_global_audience_meta($audience_items, $manual_emails)
{
    $counts = array(
        'product' => 0,
        'role'    => 0,
        'mailmint' => 0,
        'manual'  => 0,
    );

    foreach ((array) $audience_items as $item) {
        if (! is_array($item)) {
            continue;
        }
        $source = sanitize_text_field($item['source'] ?? '');
        if (isset($counts[$source])) {
            $counts[$source]++;
        }
    }

    $counts['manual'] = count(normalize_email_list($manual_emails));

    return array(
        'is_global' => true,
        'sources'   => $counts,
    );
}

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

        $audience_items = get_json_array_from_post('audience_items');
        $manual_emails = get_json_array_from_post('manual_emails');
        $is_global_audience = ! empty($audience_items) || ! empty($manual_emails);

        if ($is_global_audience) {
            $recipients = resolve_global_recipients($audience_items, $manual_emails);
        } else {
            $recipients = get_recipients_by_source($source, array(
                'product_id'       => $product_id,
                'role'             => $role,
                'mailmint_list_id' => $mailmint_list_id,
            ));
        }

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
            'is_global_audience'   => $is_global_audience,
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
 * AJAX: Busca selectores por fuente (top 3 o búsqueda 3+).
 *
 * @return void
 */
function ajax_search_selectors()
{
    check_ajax_referer('pbm_broadcast_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $source = sanitize_text_field($_POST['source'] ?? 'product');
    $query = sanitize_text_field($_POST['q'] ?? '');
    $sources = get_recipient_sources();

    if (! isset($sources[$source])) {
        wp_send_json_error(array('message' => __('Fuente inválida', 'wc-pbm')));
    }

    if (isset($sources[$source]['enabled']) && ! $sources[$source]['enabled']) {
        wp_send_json_success(array('items' => array()));
    }

    $items = array();
    if ('product' === $source) {
        $items = search_product_selectors($query);
    } elseif ('role' === $source) {
        $items = search_role_selectors($query);
    } elseif ('mailmint' === $source) {
        $items = search_mailmint_selectors($query);
    }

    wp_send_json_success(array('items' => $items));
}

/**
 * Busca productos para selector.
 *
 * @param string $query Texto de búsqueda.
 * @return array
 */
function search_product_selectors($query)
{
    $args = get_products_query_args();
    $args['limit'] = '' === $query ? 10 : 8;

    if ('' !== $query) {
        $args['s'] = $query;
    }

    $products = wc_get_products($args);
    $result = array();
    foreach ((array) $products as $product) {
        $result[] = array(
            'value' => (string) $product->get_id(),
            'label' => $product->get_name() . ' (#' . $product->get_id() . ')',
        );
    }

    return $result;
}

/**
 * Busca roles para selector.
 *
 * @param string $query Texto de búsqueda.
 * @return array
 */
function search_role_selectors($query)
{
    $roles = wp_roles()->get_names();
    $items = array();

    foreach ($roles as $slug => $name) {
        $haystack = strtolower($slug . ' ' . $name);
        if ('' !== $query && false === strpos($haystack, strtolower($query))) {
            continue;
        }
        $items[] = array(
            'value' => (string) $slug,
            'label' => (string) $name,
        );
    }

    return array_slice($items, 0, '' === $query ? 10 : 8);
}

/**
 * Busca listas Mail Mint para selector.
 *
 * @param string $query Texto de búsqueda.
 * @return array
 */
function search_mailmint_selectors($query)
{
    $lists = get_mailmint_lists_for_selector();
    $items = array();

    foreach ($lists as $list) {
        $label = (string) $list['title'] . ' (#' . (int) $list['id'] . ')';
        if ('' !== $query && false === strpos(strtolower($label), strtolower($query))) {
            continue;
        }
        $items[] = array(
            'value' => (string) absint($list['id']),
            'label' => $label,
        );
    }

    return array_slice($items, 0, '' === $query ? 10 : 8);
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

    $audience_items = get_json_array_from_post('audience_items');
    $manual_emails = get_json_array_from_post('manual_emails');
    $is_global_audience = ! empty($audience_items) || ! empty($manual_emails);

    if ($is_global_audience) {
        $recipients = resolve_global_recipients($audience_items, $manual_emails);
        $audience_label = __('Lista global combinada', 'wc-pbm');
        $global_meta = build_global_audience_meta($audience_items, $manual_emails);
    } else {
        $recipients = get_recipients_by_source($source, array(
            'product_id'       => $product_id,
            'role'             => $role,
            'mailmint_list_id' => $mailmint_list_id,
        ));
        $audience_label = get_source_audience_label($source, $product_id, $role, $mailmint_list_id);
        $global_meta = array();
    }

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
            'global'   => $global_meta,
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
        'global'   => $global_meta,
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

/**
 * AJAX: Listado paginado/ordenado de envíos para panel React.
 *
 * @return void
 */
function ajax_list_scheduled_emails()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    global $wpdb;

    $page = max(1, absint($_POST['page'] ?? 1));
    $per_page = max(1, min(50, absint($_POST['per_page'] ?? 10)));
    $offset = ($page - 1) * $per_page;

    $allowed_sort = array('scheduled_at', 'status', 'subject');
    $sort_by = sanitize_text_field($_POST['sort_by'] ?? 'scheduled_at');
    if (! in_array($sort_by, $allowed_sort, true)) {
        $sort_by = 'scheduled_at';
    }

    $sort_dir = strtoupper(sanitize_text_field($_POST['sort_dir'] ?? 'DESC'));
    if (! in_array($sort_dir, array('ASC', 'DESC'), true)) {
        $sort_dir = 'DESC';
    }

    $table = $wpdb->prefix . 'pbm_scheduled_emails';
    $total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

    $rows = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$table} ORDER BY {$sort_by} {$sort_dir} LIMIT %d OFFSET %d",
            $per_page,
            $offset
        )
    );

    $status_labels = array(
        'pending'   => __('Pendiente', 'wc-pbm'),
        'running'   => __('En ejecución', 'wc-pbm'),
        'completed' => __('Completado', 'wc-pbm'),
        'cancelled' => __('Cancelado', 'wc-pbm'),
    );

    $items = array();
    foreach ((array) $rows as $row) {
        $meta = get_delivery_meta($row->id);
        $items[] = array(
            'id'             => (int) $row->id,
            'type'           => get_delivery_type_label($meta),
            'date'           => get_date_from_gmt($row->scheduled_at, 'd/m/Y H:i'),
            'audience'       => get_delivery_audience_label($row, $meta),
            'subject'        => (string) $row->subject,
            'status'         => (string) $row->status,
            'status_label'   => (string) ($status_labels[$row->status] ?? $row->status),
            'config'         => sprintf(
                esc_html__('%d por lote / %d por hora', 'wc-pbm'),
                (int) $row->batch_size,
                (int) $row->emails_per_hour
            ),
            'can_delete'     => in_array($row->status, array('completed', 'cancelled'), true),
        );
    }

    wp_send_json_success(array(
        'items'      => $items,
        'page'       => $page,
        'per_page'   => $per_page,
        'total'      => $total,
        'total_pages' => (int) ceil($total / $per_page),
    ));
}

/**
 * AJAX: Borrado masivo por IDs seleccionados.
 *
 * @return void
 */
function ajax_bulk_delete_scheduled_ids()
{
    check_ajax_referer('pbm_scheduled_action', 'nonce');

    if (! current_user_can('manage_woocommerce')) {
        wp_send_json_error(array('message' => __('Permisos insuficientes', 'wc-pbm')));
    }

    $ids = $_POST['ids'] ?? array();
    if (! is_array($ids)) {
        wp_send_json_error(array('message' => __('IDs inválidos', 'wc-pbm')));
    }

    $ids = array_values(array_filter(array_map('absint', $ids)));
    if (empty($ids)) {
        wp_send_json_error(array('message' => __('No hay elementos seleccionados', 'wc-pbm')));
    }

    $deleted = 0;
    foreach ($ids as $id) {
        if (delete_scheduled_email_with_logs($id)) {
            $deleted++;
        }
    }

    wp_send_json_success(array(
        'deleted' => $deleted,
        'message' => sprintf(__('%d envíos eliminados', 'wc-pbm'), $deleted),
    ));
}
