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
function process_email_batch($batch, $subject, $message, $scheduled_id = 0)
{
    $sent = 0;
    $failed = 0;

    foreach ($batch as $recipient) {
        if (send_single_email($recipient, $subject, $message)) {
            $sent++;
        } else {
            $failed++;
        }
    }

    // Crear log solo si es envío programado
    if ($scheduled_id > 0) {
        create_batch_log($scheduled_id, $sent, $failed);
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
function send_single_email($recipient, $subject, $message)
{
    $to      = $recipient['email'];
    $name    = trim($recipient['name']);
    $body    = str_replace('{customer_name}', $name, $message);
    $body    = nl2br($body);
    $headers = array('Content-Type: text/html; charset=UTF-8');
    return wp_mail($to, $subject, $body, $headers);
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
function schedule_email_batches($recipients, $subject, $message, $batch_size, $emails_per_hour, $scheduled_id)
{
    $batches = array_chunk($recipients, $batch_size);
    $scheduled_count = 0;
    $interval_seconds = ceil(($batch_size / $emails_per_hour) * 3600);

    foreach ($batches as $batch) {
        if (function_exists('as_schedule_single_action')) {
            $run_at = time() + ($scheduled_count * $interval_seconds);

            // Reutilizar el hook existente pbm_process_email_batch
            as_schedule_single_action(
                $run_at,
                'pbm_process_email_batch',
                array($batch, $subject, $message, $scheduled_id),
                'product-broadcast-mailer'
            );
            $scheduled_count++;
        }
    }

    return $scheduled_count;
}
