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
    $products = wc_get_products(array(
        'limit'   => -1,
        'status'  => 'publish',
        'orderby' => 'name',
        'order'   => 'ASC',
        'return'  => 'objects',
    ));

    echo '<select id="pbm_product_id" name="pbm_product_id" required>';
    echo '<option value="">' . esc_html__('Selecciona un producto...', 'wc-pbm') . '</option>';

    foreach ($products as $product) {
        printf(
            '<option value="%d">%s (#%d)</option>',
            esc_attr($product->get_id()),
            esc_html($product->get_name()),
            esc_html($product->get_id())
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

    // Obtener TODOS los pedidos con estados válidos
    $orders = wc_get_orders(array(
        'limit'   => -1,
        'status'  => array('completed', 'processing', 'on-hold'),
        'orderby' => 'date',
        'order'   => 'DESC',
    ));

    // Iterar y filtrar por producto
    foreach ($orders as $order) {
        if (order_contains_product($order, $product_ids)) {
            $email = extract_email_from_order($order);

            if ($email && ! isset($recipients[$email])) {
                $recipients[$email] = array(
                    'name'  => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
                    'email' => $email,
                );
            }
        }
    }

    return $recipients;
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
    $count = 0;

    $orders = wc_get_orders(array(
        'limit'   => -1,
        'status'  => array('completed', 'processing', 'on-hold'),
        'return'  => 'objects',
    ));

    foreach ($orders as $order) {
        if (order_contains_product($order, $product_ids)) {
            $count++;
        }
    }

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
