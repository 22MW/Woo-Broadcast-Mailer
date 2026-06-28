<?php

/**
 * Funciones relacionadas con el envío de emails
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer;

defined('ABSPATH') || exit;

/**
 * Procesa un lote de emails en segundo plano (Action Scheduler callback)
 *
 * @param array  $batch        Array de destinatarios.
 * @param string $subject      Asunto del email.
 * @param string $message      Cuerpo del email (puede contener placeholders).
 * @param int    $scheduled_id ID del envío programado (opcional, 0 para envíos manuales).
 * @return void
 */
function process_email_batch($batch, $subject, $message, $scheduled_id = 0, $plain_body = false)
{
    $sent = 0;
    $failed = 0;

    foreach ($batch as $recipient) {
        if (send_single_email($recipient, $subject, $message, $plain_body)) {
            $sent++;
        } else {
            $failed++;
        }
    }

    // Crear log solo si es envío programado
    if ($scheduled_id > 0) {
        create_batch_log($scheduled_id, $sent, $failed);
        maybe_complete_scheduled_email($scheduled_id);
    }
}

/**
 * Envía un único email a un destinatario
 *
 * @param array  $recipient Array con 'email' y 'name'.
 * @param string $subject   Asunto.
 * @param string $message   Mensaje (puede contener {customer_name}).
 * @return bool True si se envió correctamente.
 */
function send_single_email($recipient, $subject, $message, $plain_body = false)
{
    $to = sanitize_email($recipient['email'] ?? '');
    if (! is_email($to)) {
        return false;
    }

    $placeholders = get_broadcast_email_placeholders($recipient, $to);
    $body = strtr($message, $placeholders);
    $body = prepare_broadcast_email_body($body, $plain_body);
    $body = normalize_broadcast_email_spacing($body);
    $headers = array('Content-Type: text/html; charset=UTF-8');

    if ($plain_body) {
        add_filter('haet_mail_use_template', '__return_false', 9999);
    }

    $sent = wp_mail($to, $subject, $body, $headers);

    if ($plain_body) {
        remove_filter('haet_mail_use_template', '__return_false', 9999);
    }

    return $sent;
}

/**
 * Obtiene placeholders personalizados para el email.
 *
 * @param array  $recipient Datos del destinatario.
 * @param string $email     Email destino.
 * @return array
 */
function get_broadcast_email_placeholders($recipient, $email)
{
    $first_name = '';
    $last_name = '';
    $display_name = trim((string) ($recipient['name'] ?? ''));
    $user = get_user_by('email', $email);

    if ($user instanceof \WP_User) {
        $first_name = (string) get_user_meta($user->ID, 'first_name', true);
        $last_name = (string) get_user_meta($user->ID, 'last_name', true);
        $display_name = '' !== trim($display_name) ? $display_name : (string) $user->display_name;
    }

    $customer_name = trim($display_name);
    if ('' === $customer_name) {
        $customer_name = trim($first_name . ' ' . $last_name);
    }

    return array(
        '{customer_name}' => esc_html($customer_name),
        '{first_name}'    => esc_html($first_name),
        '{last_name}'     => esc_html($last_name),
        '{email}'         => esc_html($email),
        '{current_date}'  => esc_html(wp_date(get_option('date_format'))),
    );
}

/**
 * Prepara el cuerpo del email antes del envío.
 *
 * @param string $body       Cuerpo del mensaje.
 * @param bool   $plain_body Si debe omitir plantilla global.
 * @return string
 */
function prepare_broadcast_email_body($body, $plain_body)
{
    if ($plain_body || ! broadcast_email_body_has_block_html($body)) {
        $body = nl2br(wp_kses_post($body));
    } else {
        $body = wp_kses_post($body);
    }

    $body = preserve_broadcast_manual_spacing($body);

    return normalize_broadcast_email_breaks($body);
}

/**
 * Comprueba si el cuerpo ya contiene HTML estructurado.
 *
 * @param string $body Cuerpo del mensaje.
 * @return bool
 */
function broadcast_email_body_has_block_html($body)
{
    return (bool) preg_match('/<(p|div|table|ol|ul|li|h[1-6]|pre|blockquote)\b/i', (string) $body);
}

/**
 * Limpia saltos convertidos en br entre bloques sin tocar saltos reales dentro del texto.
 *
 * @param string $body HTML del mensaje.
 * @return string
 */
function normalize_broadcast_email_breaks($body)
{
    $block_tags = 'p|div|table|thead|tbody|tr|td|th|ol|ul|li|h[1-6]|pre|blockquote';

    $body = preg_replace('/<br\s*\/?>\s*(<(' . $block_tags . ')\b)/i', '$1', $body);
    $body = preg_replace('/(<\/(' . $block_tags . ')>)\s*<br\s*\/?>/i', '$1', $body);
    $body = preg_replace('/(<(ol|ul)\b[^>]*>)\s*<br\s*\/?>/i', '$1', $body);
    $body = preg_replace('/(<\/li>)\s*<br\s*\/?>\s*(<li\b)/i', '$1$2', $body);

    return $body;
}

/**
 * Convierte espacios manuales del editor en espaciadores email-safe.
 *
 * @param string $body HTML del mensaje.
 * @return string
 */
function preserve_broadcast_manual_spacing($body)
{
    $spacer = '<div style="height:16px;line-height:16px;font-size:16px">&nbsp;</div>';

    $body = preg_replace('/(?:\s*<br\s*\/?>\s*){2,}/i', $spacer, $body);
    $body = preg_replace('/(<\/(p|h[1-6]|li|ol|ul|div|blockquote)>)\s*(?:\r?\n\s*){2,}(<(p|h[1-6]|ol|ul|div|blockquote)\b)/i', '$1' . $spacer . '$3', $body);

    return $body;
}

/**
 * Añade estilos inline mínimos para conservar espaciado en clientes de email.
 *
 * @param string $body HTML del mensaje.
 * @return string
 */
function normalize_broadcast_email_spacing($body)
{
    if ('' === trim((string) $body)) {
        return $body;
    }

    $styles = array(
        'p'   => 'margin:0 0 5px;line-height:1.6;',
        'h1'  => 'margin:32px 0 16px;line-height:1.2;',
        'h2'  => 'margin:30px 0 15px;line-height:1.25;',
        'h3'  => 'margin:28px 0 14px;line-height:1.3;',
        'h4'  => 'margin:24px 0 12px;line-height:1.35;',
        'ol'  => 'margin:0 0 22px 24px;padding-left:20px;',
        'ul'  => 'margin:0 0 22px 24px;padding-left:20px;',
        'li'  => 'margin:0 0 8px;line-height:1.5;',
        'pre' => 'margin:24px 0 0;white-space:pre-wrap;line-height:1.5;',
    );

    foreach ($styles as $tag => $style) {
        $body = add_inline_style_to_email_tag($body, $tag, $style);
    }

    return $body;
}

/**
 * Añade estilo inline sin borrar estilos existentes.
 *
 * @param string $html  HTML.
 * @param string $tag   Etiqueta.
 * @param string $style Estilo a añadir.
 * @return string
 */
function add_inline_style_to_email_tag($html, $tag, $style)
{
    return preg_replace_callback(
        '/<' . preg_quote($tag, '/') . '\b([^>]*)>/i',
        function ($matches) use ($tag, $style) {
            $attributes = $matches[1];

            if (preg_match('/\sstyle=("|\')(.*?)\1/i', $attributes, $style_match)) {
                $merged = rtrim($style_match[2]);
                $merged = '' === $merged || str_ends_with($merged, ';') ? $merged . $style : $merged . ';' . $style;
                $attributes = preg_replace('/\sstyle=("|\')(.*?)\1/i', ' style="' . esc_attr($merged) . '"', $attributes, 1);

                return '<' . $tag . $attributes . '>';
            }

            return '<' . $tag . $attributes . ' style="' . esc_attr($style) . '">';
        },
        $html
    );
}

/**
 * Programa lotes de emails usando el sistema existente
 *
 * @param array  $recipients       Array de destinatarios.
 * @param string $subject          Asunto.
 * @param string $message          Mensaje.
 * @param int    $batch_size       Tamaño de lote.
 * @param int    $emails_per_hour  Emails por hora.
 * @param int    $scheduled_id     ID del envío programado.
 * @return int Número de lotes programados.
 */
function schedule_email_batches($recipients, $subject, $message, $batch_size, $emails_per_hour, $scheduled_id, $plain_body = false)
{
    if (! is_action_scheduler_available()) {
        return 0;
    }

    $batches = array_chunk($recipients, $batch_size);
    $scheduled_count = 0;
    $interval_seconds = ceil(($batch_size / $emails_per_hour) * 3600);

    foreach ($batches as $batch) {
        $run_at = time() + ($scheduled_count * $interval_seconds);

        // Reutilizar el hook existente pbm_process_email_batch
        as_schedule_single_action(
            $run_at,
            'pbm_process_email_batch',
            array($batch, $subject, $message, $scheduled_id, $plain_body),
            'product-broadcast-mailer'
        );
        $scheduled_count++;
    }

    return $scheduled_count;
}
