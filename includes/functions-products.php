<?php

/**
 * Funciones relacionadas con productos y destinatarios
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer;

defined('ABSPATH') || exit;

/**
 * Renderiza el selector de productos
 *
 * @return void
 */
function render_product_selector()
{
    $products = get_products_for_selector();

    echo '<select id="pbm_product_id" name="pbm_product_id" required>';
    echo '<option value="">' . esc_html__('Selecciona un producto...', 'wc-pbm') . '</option>';

    foreach ($products as $product) {
        $lang_label = get_wpml_language_label($product->get_id());
        $label = $product->get_name() . ' (#' . $product->get_id() . ')' . $lang_label;
        printf(
            '<option value="%d">%s</option>',
            esc_attr($product->get_id()),
            esc_html($label)
        );
    }

    echo '</select>';
}

/**
 * Obtiene compradores únicos de un producto (incluyendo variaciones y suscripciones)
 *
 * @param int $product_id ID del producto.
 * @return array Array asociativo con email como clave y datos del cliente como valor.
 */
function get_product_purchasers($product_id)
{
    $purchasers = array();

    // 1. Obtener pedidos del producto y sus variaciones
    $order_recipients = get_recipients_from_orders($product_id);
    $purchasers = array_merge($purchasers, $order_recipients);

    // 2. Obtener suscriptores activos (si WooCommerce Subscriptions está activo)
    if (class_exists('WC_Subscriptions_Product')) {
        $subscription_recipients = get_recipients_from_subscriptions($product_id);
        $purchasers = array_merge($purchasers, $subscription_recipients);
    }

    return $purchasers;
}

/**
 * Obtiene destinatarios desde pedidos completados/procesados
 * Método compatible con HPOS: itera sobre pedidos y verifica items
 *
 * @param int $product_id ID del producto.
 * @return array Array asociativo [email => datos].
 */
function get_recipients_from_orders($product_id)
{
    $recipients = array();
    $product    = wc_get_product($product_id);

    if (! $product) {
        return $recipients;
    }

    $product_ids = get_product_and_variation_ids($product);
    $target_lang = get_wpml_language_code($product_id);

    if (is_hpos_enabled()) {
        return get_recipients_from_order_lookup($product_ids, $target_lang);
    }

    // Obtener pedidos por páginas para evitar consumo excesivo de memoria
    $page = 1;
    $limit = 200;
    do {
        $orders = wc_get_orders(array(
            'limit'   => $limit,
            'page'    => $page,
            'status'  => array('completed', 'processing', 'on-hold'),
            'orderby' => 'date',
            'order'   => 'DESC',
            'suppress_filters' => true,
        ));

        foreach ($orders as $order) {
            if (order_contains_product($order, $product_ids) && order_matches_language($order, $target_lang)) {
                $email = extract_email_from_order($order);

                if ($email && ! isset($recipients[$email])) {
                    $recipients[$email] = array(
                        'name'  => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
                        'email' => $email,
                    );
                }
            }
        }

        $page++;
    } while (count($orders) === $limit);

    return $recipients;
}

/**
 * Comprueba si HPOS está activo
 *
 * @return bool
 */
function is_hpos_enabled()
{
    if (class_exists('\\Automattic\\WooCommerce\\Utilities\\OrderUtil')) {
        return (bool) \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
    }

    return false;
}

/**
 * Obtiene destinatarios desde la tabla de lookup (HPOS)
 *
 * @param array $product_ids IDs del producto y variaciones.
 * @return array
 */
function get_recipients_from_order_lookup($product_ids, $target_lang = '')
{
    $recipients = array();
    $order_ids = get_order_ids_from_lookup($product_ids);
    if (empty($order_ids)) {
        return $recipients;
    }

    $orders = wc_get_orders(array(
        'include' => $order_ids,
        'limit'   => -1,
        'orderby' => 'date',
        'order'   => 'DESC',
        'suppress_filters' => true,
    ));

    foreach ($orders as $order) {
        if (! order_matches_language($order, $target_lang)) {
            continue;
        }
        $email = extract_email_from_order($order);
        if ($email && ! isset($recipients[$email])) {
            $recipients[$email] = array(
                'name'  => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
                'email' => $email,
            );
        }
    }

    return $recipients;
}

/**
 * Obtiene IDs de pedidos desde la tabla de lookup
 *
 * @param array $product_ids IDs del producto y variaciones.
 * @return array
 */
function get_order_ids_from_lookup($product_ids)
{
    global $wpdb;

    $ids = array_values(array_filter(array_map('absint', (array) $product_ids)));
    if (empty($ids)) {
        return array();
    }

    $table = $wpdb->prefix . 'wc_order_product_lookup';
    $id_placeholders = implode(',', array_fill(0, count($ids), '%d'));
    $status = array('wc-completed', 'wc-processing', 'wc-on-hold');
    $status_placeholders = implode(',', array_fill(0, count($status), '%s'));

    $sql = "SELECT DISTINCT order_id
            FROM {$table}
            WHERE (product_id IN ({$id_placeholders}) OR variation_id IN ({$id_placeholders}))
              AND order_status IN ({$status_placeholders})";

    $query_args = array_merge($ids, $ids, $status);

    return array_map('absint', $wpdb->get_col($wpdb->prepare($sql, ...$query_args)));
}

/**
 * Verifica si un pedido contiene un producto específico
 *
 * @param \WC_Order $order       Objeto pedido.
 * @param array     $product_ids Array de IDs de producto a buscar.
 * @return bool True si contiene el producto.
 */
function order_contains_product($order, $product_ids)
{
    foreach ($order->get_items() as $item) {
        $item_product_id = $item->get_product_id();
        $item_variation_id = $item->get_variation_id();

        if (in_array($item_product_id, $product_ids, true)) {
            return true;
        }

        if ($item_variation_id && in_array($item_variation_id, $product_ids, true)) {
            return true;
        }
    }

    return false;
}

/**
 * Obtiene destinatarios desde suscripciones activas
 *
 * @param int $product_id ID del producto.
 * @return array Array asociativo [email => datos].
 */
function get_recipients_from_subscriptions($product_id)
{
    $recipients = array();

    if (! function_exists('wcs_get_subscriptions')) {
        return $recipients;
    }

    $product     = wc_get_product($product_id);
    $product_ids = get_product_and_variation_ids($product);

    // Obtener todas las suscripciones activas
    $subscriptions = wcs_get_subscriptions(array(
        'subscriptions_per_page' => -1,
        'subscription_status'    => array('active'),
    ));

    foreach ($subscriptions as $subscription) {
        if (order_contains_product($subscription, $product_ids)) {
            $email = extract_email_from_order($subscription);

            if ($email && ! isset($recipients[$email])) {
                $recipients[$email] = array(
                    'name'  => trim($subscription->get_billing_first_name() . ' ' . $subscription->get_billing_last_name()),
                    'email' => $email,
                );
            }
        }
    }

    return $recipients;
}

/**
 * Obtiene IDs del producto padre y todas sus variaciones
 *
 * @param \WC_Product $product Objeto producto.
 * @return array Array de IDs.
 */
function get_product_and_variation_ids($product)
{
    $ids = array($product->get_id());

    if ($product->is_type('variable')) {
        $ids = array_merge($ids, $product->get_children());
    }

    return $ids;
}

/**
 * Extrae el email de facturación de un pedido u orden
 *
 * @param \WC_Order|\WC_Subscription $order Objeto pedido o suscripción.
 * @return string Email o cadena vacía.
 */
function extract_email_from_order($order)
{
    $email = $order->get_billing_email();
    return is_email($email) ? sanitize_email($email) : '';
}

/**
 * Obtiene el conteo de pedidos para un producto
 *
 * @param int $product_id ID del producto.
 * @return int Número de pedidos.
 */
function get_orders_count_for_product($product_id)
{
    $product = wc_get_product($product_id);

    if (! $product) {
        return 0;
    }

    $product_ids = get_product_and_variation_ids($product);
    $target_lang = get_wpml_language_code($product_id);
    if (is_hpos_enabled()) {
        $ids = get_order_ids_from_lookup($product_ids);
        if (empty($ids)) {
            return 0;
        }
        $count = 0;
        $orders = wc_get_orders(array(
            'include' => $ids,
            'limit'   => -1,
            'suppress_filters' => true,
        ));
        foreach ($orders as $order) {
            if (order_matches_language($order, $target_lang)) {
                $count++;
            }
        }
        return $count;
    }

    $count = 0;

    $page = 1;
    $limit = 200;
    do {
        $orders = wc_get_orders(array(
            'limit'   => $limit,
            'page'    => $page,
            'status'  => array('completed', 'processing', 'on-hold'),
            'return'  => 'objects',
            'suppress_filters' => true,
        ));

        foreach ($orders as $order) {
            if (order_contains_product($order, $product_ids) && order_matches_language($order, $target_lang)) {
                $count++;
            }
        }

        $page++;
    } while (count($orders) === $limit);

    return $count;
}

/**
 * Obtiene el conteo de suscripciones activas para un producto
 *
 * @param int $product_id ID del producto.
 * @return int Número de suscripciones activas.
 */
function get_subscriptions_count_for_product($product_id)
{
    if (! function_exists('wcs_get_subscriptions')) {
        return 0;
    }

    $product     = wc_get_product($product_id);
    $product_ids = get_product_and_variation_ids($product);
    $count       = 0;

    $subscriptions = wcs_get_subscriptions(array(
        'subscriptions_per_page' => -1,
        'subscription_status'    => array('active'),
    ));

    foreach ($subscriptions as $subscription) {
        if (order_contains_product($subscription, $product_ids)) {
            $count++;
        }
    }

    return $count;
}

/**
 * Obtiene usuarios activos por rol de WordPress
 *
 * @param string $role Slug del rol.
 * @return array Array de usuarios con formato [email => [name, email]].
 */
function get_users_by_role($role)
{
    $users_query = new \WP_User_Query(array(
        'role'   => $role,
        'fields' => array('ID', 'user_email', 'display_name'),
    ));

    $users = $users_query->get_results();
    $recipients = array();

    foreach ($users as $user) {
        if (is_email($user->user_email)) {
            $recipients[$user->user_email] = array(
                'email' => $user->user_email,
                'name'  => $user->display_name,
            );
        }
    }

    return $recipients;
}

/**
 * Obtiene productos en todos los idiomas si WPML está activo
 *
 * @return array
 */
function get_products_for_selector()
{
    $args = get_products_query_args();
    $languages = get_wpml_active_languages();
    if (empty($languages)) {
        return wc_get_products($args);
    }

    $current = get_wpml_current_language();
    if ($current && $current !== 'all') {
        return get_products_for_language($args, $current);
    }

    return get_products_all_languages_grouped($args, $languages);
}

/**
 * Obtiene args base para productos
 *
 * @return array
 */
function get_products_query_args()
{
    return array(
        'limit'   => -1,
        'status'  => 'publish',
        'orderby' => 'name',
        'order'   => 'ASC',
        'return'  => 'objects',
    );
}

/**
 * Obtiene productos para un idioma
 *
 * @param array  $args Args base.
 * @param string $code Código de idioma.
 * @return array
 */
function get_products_for_language($args, $code)
{
    $lang_args = $args;
    $lang_args['lang'] = $code;
    $lang_args['suppress_filters'] = false;
    return wc_get_products($lang_args);
}

/**
 * Obtiene productos agrupados por idioma (WPML)
 *
 * @param array $args Args base.
 * @param array $languages Idiomas activos.
 * @return array
 */
function get_products_all_languages_grouped($args, $languages)
{
    $all = array();
    foreach ($languages as $code) {
        $all = array_merge($all, get_products_for_language($args, $code));
    }

    return group_products_by_trid($all);
}

/**
 * Agrupa productos por TRID y mantiene uno por grupo
 *
 * @param array $products Productos.
 * @return array
 */
function group_products_by_trid($products)
{
    $default = get_wpml_default_language();
    $grouped = array();
    $fallback = array();

    foreach ($products as $product) {
        $id = $product->get_id();
        $trid = get_wpml_trid($id);
        if (! $trid) {
            $fallback[$id] = $product;
            continue;
        }
        if (! isset($grouped[$trid]) || is_default_language_product($id, $default)) {
            $grouped[$trid] = $product;
        }
    }

    return array_values($grouped + $fallback);
}

/**
 * Obtiene idiomas activos desde WPML
 *
 * @return array
 */
function get_wpml_active_languages()
{
    $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    if (empty($languages) || ! is_array($languages)) {
        return array();
    }

    $codes = array();
    foreach ($languages as $lang) {
        if (! empty($lang['code'])) {
            $codes[] = $lang['code'];
        }
    }

    return array_values(array_unique($codes));
}

/**
 * Obtiene el idioma actual de WPML
 *
 * @return string
 */
function get_wpml_current_language()
{
    $code = apply_filters('wpml_current_language', null);
    return is_string($code) ? $code : '';
}

/**
 * Obtiene el idioma por defecto de WPML
 *
 * @return string
 */
function get_wpml_default_language()
{
    $code = apply_filters('wpml_default_language', null);
    return is_string($code) ? $code : '';
}

/**
 * Devuelve etiqueta de idioma para WPML
 *
 * @param int $post_id ID del post.
 * @return string
 */
function get_wpml_language_label($post_id)
{
    $code = get_wpml_language_code($post_id);
    if ($code === '') {
        return '';
    }

    return ' [' . strtoupper($code) . ']';
}

/**
 * Obtiene el código de idioma de un post
 *
 * @param int $post_id ID del post.
 * @return string
 */
function get_wpml_language_code($post_id)
{
    $details = apply_filters('wpml_post_language_details', null, $post_id);
    if (empty($details['language_code'])) {
        return '';
    }

    return (string) $details['language_code'];
}

/**
 * Verifica si un pedido coincide con un idioma objetivo (WPML)
 *
 * @param \WC_Order $order Objeto pedido.
 * @param string    $target_lang Código de idioma.
 * @return bool
 */
function order_matches_language($order, $target_lang)
{
    if ($target_lang === '') {
        return true;
    }

    $order_lang = (string) $order->get_meta('wpml_language', true);
    if ($order_lang === '') {
        return true;
    }

    return $order_lang === $target_lang;
}

/**
 * Obtiene TRID de WPML
 *
 * @param int $post_id ID del post.
 * @return int
 */
function get_wpml_trid($post_id)
{
    $trid = apply_filters('wpml_element_trid', null, $post_id, 'post_product');
    return $trid ? (int) $trid : 0;
}

/**
 * Verifica si el producto es del idioma por defecto
 *
 * @param int    $post_id ID del post.
 * @param string $default Código por defecto.
 * @return bool
 */
function is_default_language_product($post_id, $default)
{
    if ($default === '') {
        return false;
    }

    return get_wpml_language_code($post_id) === $default;
}
