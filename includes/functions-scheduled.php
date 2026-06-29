<?php

/**
 * Funciones relacionadas con envíos programados
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer;

defined('ABSPATH') || exit;

/**
 * Renderiza la pestaña de envíos programados
 *
 * @return void
 */
function render_scheduled_emails_tab()
{
?>
    <div class="pbm-scheduled-wrapper">
        <div>
            <h2><?php esc_html_e('Envíos Programados', 'wc-pbm'); ?></h2>
            <div id="pbm-scheduled-list">
                <?php render_scheduled_emails_list(); ?>
            </div>
        </div>
    </div>

    <!-- Modal para logs -->
    <div id="pbm-logs-modal" style="display:none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; padding: 30px; border-radius: 10px; max-width: 600px; width: 90%; max-height: 80%; overflow-y: auto;">
            <h2 style="margin-top: 0;"><?php esc_html_e('Logs de Envío', 'wc-pbm'); ?></h2>
            <div id="pbm-logs-content"></div>
            <p style="text-align: right; margin-bottom: 0;">
                <button type="button" id="pbm-close-logs-modal" class="button" style="margin-top: 20px;">
                    <?php esc_html_e('Cerrar', 'wc-pbm'); ?>
                </button>
            </p>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Cancelar envío programado
            $(document).on('click', '.pbm-cancel-scheduled', function(e) {
                e.preventDefault();

                if (!confirm('<?php echo esc_js(__('¿Estás seguro de cancelar este envío?', 'wc-pbm')); ?>')) {
                    return;
                }

                const scheduledId = $(this).data('id');

                $.post(ajaxurl, {
                    action: 'pbm_cancel_scheduled_email',
                    scheduled_id: scheduledId,
                    nonce: '<?php echo wp_create_nonce('pbm_scheduled_action'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Error al cancelar', 'wc-pbm')); ?>');
                    }
                });
            });

            // Ejecutar ahora
            $(document).on('click', '.pbm-run-now', function(e) {
                e.preventDefault();

                if (!confirm('<?php echo esc_js(__('¿Ejecutar este envío inmediatamente?', 'wc-pbm')); ?>')) {
                    return;
                }

                const scheduledId = $(this).data('id');

                $.post(ajaxurl, {
                    action: 'pbm_run_scheduled_now',
                    scheduled_id: scheduledId,
                    nonce: '<?php echo wp_create_nonce('pbm_scheduled_action'); ?>'
                }, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Error al ejecutar', 'wc-pbm')); ?>');
                    }
                });
            });

            // Ver logs
            $(document).on('click', '.pbm-view-logs', function(e) {
                e.preventDefault();

                const scheduledId = $(this).data('id');

                $.post(ajaxurl, {
                    action: 'pbm_get_scheduled_logs',
                    scheduled_id: scheduledId,
                    nonce: '<?php echo wp_create_nonce('pbm_scheduled_action'); ?>'
                }, function(response) {
                    if (response.success) {
                        $('#pbm-logs-content').html(response.data.html);
                        $('#pbm-logs-modal').css('display', 'flex');
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Error al obtener logs', 'wc-pbm')); ?>');
                    }
                });
            });

            // Borrar individual
            $(document).on('click', '.pbm-delete-scheduled', function(e) {
                e.preventDefault();

                if (!confirm('<?php echo esc_js(__('¿Eliminar este envío y sus logs?', 'wc-pbm')); ?>')) {
                    return;
                }

                const $btn = $(this);
                const scheduledId = $btn.data('id');

                $.post(ajaxurl, {
                    action: 'pbm_delete_scheduled_email',
                    scheduled_id: scheduledId,
                    nonce: '<?php echo wp_create_nonce('pbm_scheduled_action'); ?>'
                }, function(response) {
                    if (response.success) {
                        $btn.closest('tr').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Error al borrar', 'wc-pbm')); ?>');
                    }
                });
            });

            // Borrado masivo
            $(document).on('click', '.pbm-bulk-delete', function(e) {
                e.preventDefault();

                const status = $(this).data('status');
                const statusLabel = status === 'completed' ? '<?php echo esc_js(__('completados', 'wc-pbm')); ?>' : '<?php echo esc_js(__('cancelados', 'wc-pbm')); ?>';

                if (!confirm('<?php echo esc_js(__('¿Eliminar todos los envíos', 'wc-pbm')); ?> ' + statusLabel + '?')) {
                    return;
                }

                $.post(ajaxurl, {
                    action: 'pbm_bulk_delete_scheduled',
                    status: status,
                    nonce: '<?php echo wp_create_nonce('pbm_scheduled_action'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Error al borrar', 'wc-pbm')); ?>');
                    }
                });
            });

            // Cerrar modal
            $('#pbm-close-logs-modal, #pbm-logs-modal').on('click', function(e) {
                if (e.target === this) {
                    $('#pbm-logs-modal').hide();
                }
            });
        });
    </script>
<?php
}

/**
 * Renderiza el selector de roles de WordPress
 *
 * @return void
 */
function render_role_selector()
{
    $roles = wp_roles()->get_names();

    echo '<select id="pbm_user_role" name="pbm_user_role">';
    echo '<option value="">' . esc_html__('Selecciona un rol...', 'wc-pbm') . '</option>';

    foreach ($roles as $role_slug => $role_name) {
        printf(
            '<option value="%s">%s</option>',
            esc_attr($role_slug),
            esc_html($role_name)
        );
    }

    echo '</select>';
}

/**
 * Renderiza la lista de envíos programados
 *
 * @return void
 */
function render_scheduled_emails_list()
{
    $scheduled_emails = get_all_scheduled_emails();

    if (empty($scheduled_emails)) {
        echo '<p>' . esc_html__('No hay envíos registrados.', 'wc-pbm') . '</p>';
        return;
    }

?>
    <div style="margin-bottom: 15px;">
        <button class="button button-secondary pbm-bulk-delete" data-status="completed">
            <?php esc_html_e('Borrar todos los completados', 'wc-pbm'); ?>
        </button>
        <button class="button button-secondary pbm-bulk-delete" data-status="cancelled">
            <?php esc_html_e('Borrar todos los cancelados', 'wc-pbm'); ?>
        </button>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th style="width: 150px;"><?php esc_html_e('Tipo', 'wc-pbm'); ?></th>
                <th style="width: 170px;"><?php esc_html_e('Fecha', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Audiencia', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Asunto', 'wc-pbm'); ?></th>
                <th style="width: 120px;"><?php esc_html_e('Estado', 'wc-pbm'); ?></th>
                <th style="width: 170px;"><?php esc_html_e('Config. Envío', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Acciones', 'wc-pbm'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($scheduled_emails as $email) : ?>
                <?php
                $delivery_meta = get_delivery_meta($email->id);
                $type_label = get_delivery_type_label($delivery_meta);
                $audience_label = get_delivery_audience_label($email, $delivery_meta);
                ?>
                <tr>
                    <td><?php echo esc_html($type_label); ?></td>
                    <td><?php echo esc_html(get_date_from_gmt($email->scheduled_at, 'd/m/Y H:i')); ?></td>
                    <td><?php echo esc_html($audience_label); ?></td>
                    <td><?php echo esc_html($email->subject); ?></td>
                    <td>
                        <?php
                        $status_labels = array(
                            'pending'   => __('Pendiente', 'wc-pbm'),
                            'running'   => __('En ejecución', 'wc-pbm'),
                            'completed' => __('Completado', 'wc-pbm'),
                            'cancelled' => __('Cancelado', 'wc-pbm'),
                        );
                        echo '<strong>' . esc_html($status_labels[$email->status] ?? $email->status) . '</strong>';
                        ?>
                    </td>
                    <td>
                        <?php
                        printf(
                            esc_html__('%d por lote / %d por hora', 'wc-pbm'),
                            (int) $email->batch_size,
                            (int) $email->emails_per_hour
                        );
                        ?>
                    </td>
                    <td>
                        <?php if ($email->status === 'pending') : ?>
                            <a href="#" class="button pbm-run-now" data-id="<?php echo esc_attr($email->id); ?>">
                                <?php esc_html_e('Ejecutar Ahora', 'wc-pbm'); ?>
                            </a>
                            <a href="#" class="button pbm-cancel-scheduled" data-id="<?php echo esc_attr($email->id); ?>">
                                <?php esc_html_e('Cancelar', 'wc-pbm'); ?>
                            </a>
                        <?php endif; ?>
                        <a href="#" class="button pbm-view-logs" data-id="<?php echo esc_attr($email->id); ?>">
                            <?php esc_html_e('Ver Logs', 'wc-pbm'); ?>
                        </a>
                        <?php if (in_array($email->status, array('completed', 'cancelled'), true)) : ?>
                            <a href="#" class="button button-link-delete pbm-delete-scheduled" data-id="<?php echo esc_attr($email->id); ?>">
                                <?php esc_html_e('Borrar', 'wc-pbm'); ?>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php
}

/**
 * Obtiene metadatos de entrega guardados.
 *
 * @param int $delivery_id ID del envío.
 * @return array
 */
function get_delivery_meta($delivery_id)
{
    $meta = get_option('pbm_delivery_meta_' . absint($delivery_id), array());
    return is_array($meta) ? $meta : array();
}

/**
 * Etiqueta tipo de envío.
 *
 * @param array $delivery_meta Metadatos.
 * @return string
 */
function get_delivery_type_label($delivery_meta)
{
    $type = $delivery_meta['type'] ?? '';
    if ('instant' === $type) {
        return __('Instantáneo', 'wc-pbm');
    }

    if ('scheduled' === $type) {
        return __('Programado', 'wc-pbm');
    }

    return __('Programado', 'wc-pbm');
}

/**
 * Etiqueta audiencia.
 *
 * @param object $email Fila de envío.
 * @param array  $delivery_meta Metadatos.
 * @return string
 */
function get_delivery_audience_label($email, $delivery_meta)
{
    if (! empty($delivery_meta['audience_snapshot']['summary'])) {
        return (string) $delivery_meta['audience_snapshot']['summary'];
    }

    if (! empty($delivery_meta['global']['is_global']) && ! empty($delivery_meta['global']['sources']) && is_array($delivery_meta['global']['sources'])) {
        $sources = $delivery_meta['global']['sources'];
        $parts = array();

        if (! empty($sources['product'])) {
            $parts[] = sprintf(__('Productos: %d', 'wc-pbm'), (int) $sources['product']);
        }
        if (! empty($sources['role'])) {
            $parts[] = sprintf(__('Roles: %d', 'wc-pbm'), (int) $sources['role']);
        }
        if (! empty($sources['mailmint'])) {
            $parts[] = sprintf(__('Listas: %d', 'wc-pbm'), (int) $sources['mailmint']);
        }
        if (! empty($sources['manual'])) {
            $parts[] = sprintf(__('Manuales: %d', 'wc-pbm'), (int) $sources['manual']);
        }

        $summary = empty($parts) ? __('Global', 'wc-pbm') : implode(' | ', $parts);
        return __('Global: ', 'wc-pbm') . $summary;
    }

    if (! empty($delivery_meta['audience'])) {
        return (string) $delivery_meta['audience'];
    }

    return (string) $email->user_role;
}

/**
 * Construye un snapshot descriptivo de audiencia.
 *
 * @param array  $audience_items   Items seleccionados.
 * @param array  $manual_emails    Emails manuales.
 * @param array  $excluded_emails  Emails excluidos.
 * @param array  $recipients       Destinatarios resueltos.
 * @param string $audience_mode    Modo de audiencia.
 * @return array
 */
function build_delivery_audience_snapshot($audience_items, $manual_emails, $excluded_emails, $recipients, $audience_mode)
{
    $items = build_delivery_audience_snapshot_items($audience_items);
    $manual_count = count(normalize_email_list($manual_emails));
    $excluded_count = count(normalize_email_list($excluded_emails));
    $final_count = is_array($recipients) ? count($recipients) : 0;

    return array(
        'mode'                  => 'dynamic' === $audience_mode ? 'dynamic' : 'fixed',
        'created_at'            => current_time('mysql', true),
        'preview_count'         => $final_count,
        'final_count'           => $final_count,
        'items'                 => $items,
        'manual_count'          => $manual_count,
        'excluded_count'        => $excluded_count,
        'excluded_emails_count' => $excluded_count,
        'summary'               => build_delivery_audience_snapshot_summary($items, $manual_count, $excluded_count, $final_count),
    );
}

/**
 * Construye items descriptivos de audiencia.
 *
 * @param array $audience_items Items seleccionados.
 * @return array
 */
function build_delivery_audience_snapshot_items($audience_items)
{
    $items = array();
    $sources = get_recipient_sources();

    foreach ((array) $audience_items as $item) {
        if (! is_array($item)) {
            continue;
        }

        $source = sanitize_text_field($item['source'] ?? '');
        $selector_value = sanitize_text_field($item['selectorValue'] ?? ($item['selector_value'] ?? ''));
        if ('' === $source || '' === $selector_value) {
            continue;
        }

        $status = isset($sources[$source]) ? 'available' : 'unknown';
        if (isset($sources[$source]['enabled']) && ! $sources[$source]['enabled']) {
            $status = 'missing';
        }

        $items[] = array(
            'source'         => $source,
            'selector_value' => $selector_value,
            'source_label'   => get_delivery_audience_source_label($source, $sources),
            'selector_label' => get_delivery_audience_selector_label($source, $selector_value),
            'count'          => get_delivery_audience_item_count($source, $selector_value),
            'status'         => $status,
        );
    }

    return $items;
}

/**
 * Obtiene etiqueta de fuente para snapshot.
 *
 * @param string $source  Fuente.
 * @param array  $sources Fuentes registradas.
 * @return string
 */
function get_delivery_audience_source_label($source, $sources)
{
    if (! empty($sources[$source]['label'])) {
        return (string) $sources[$source]['label'];
    }

    return (string) $source;
}

/**
 * Obtiene etiqueta de selector para snapshot.
 *
 * @param string $source         Fuente.
 * @param string $selector_value Valor.
 * @return string
 */
function get_delivery_audience_selector_label($source, $selector_value)
{
    if ('product' === $source) {
        $product = wc_get_product(absint($selector_value));
        return $product ? $product->get_name() . ' (#' . $product->get_id() . ')' : sprintf(__('Producto #%d', 'wc-pbm'), absint($selector_value));
    }

    if ('role' === $source) {
        $roles = wp_roles()->get_names();
        return (string) ($roles[$selector_value] ?? $selector_value);
    }

    if ('mailmint' === $source) {
        foreach (get_mailmint_lists_for_selector() as $list) {
            if ((string) ($list['id'] ?? '') === (string) $selector_value) {
                return (string) $list['title'] . ' (#' . absint($list['id']) . ')';
            }
        }
        return sprintf(__('Lista Mail Mint #%d', 'wc-pbm'), absint($selector_value));
    }

    if ('broadcast_list' === $source) {
        $lists = get_broadcast_lists();
        if (! empty($lists[$selector_value]['name'])) {
            return (string) $lists[$selector_value]['name'];
        }
    }

    return (string) $selector_value;
}

/**
 * Cuenta destinatarios de un item de audiencia.
 *
 * @param string $source         Fuente.
 * @param string $selector_value Valor.
 * @return int
 */
function get_delivery_audience_item_count($source, $selector_value)
{
    $args = array(
        'product_id'        => 'product' === $source ? absint($selector_value) : 0,
        'role'              => 'role' === $source ? $selector_value : '',
        'mailmint_list_id'  => 'mailmint' === $source ? absint($selector_value) : 0,
        'broadcast_list_id' => 'broadcast_list' === $source ? $selector_value : '',
    );

    $recipients = get_recipients_by_source($source, $args);
    return is_array($recipients) ? count($recipients) : 0;
}

/**
 * Construye resumen legible del snapshot.
 *
 * @param array $items          Items.
 * @param int   $manual_count   Manuales.
 * @param int   $excluded_count Excluidos.
 * @param int   $final_count    Total final.
 * @return string
 */
function build_delivery_audience_snapshot_summary($items, $manual_count, $excluded_count, $final_count)
{
    $parts = array();
    foreach ((array) $items as $item) {
        $parts[] = sprintf(
            '%1$s: %2$s (%3$d)',
            (string) ($item['source_label'] ?? ''),
            (string) ($item['selector_label'] ?? ''),
            (int) ($item['count'] ?? 0)
        );
    }

    if ($manual_count > 0) {
        $parts[] = sprintf(__('Manuales: %d', 'wc-pbm'), $manual_count);
    }

    $summary = empty($parts) ? __('Audiencia no definida', 'wc-pbm') : implode(' | ', $parts);
    if ($excluded_count > 0) {
        $summary .= ' | ' . sprintf(__('Excluidos: %d', 'wc-pbm'), $excluded_count);
    }

    return sprintf(
        /* translators: %1$s: resumen de fuentes, %2$d: total final */
        __('%1$s | Total: %2$d', 'wc-pbm'),
        $summary,
        $final_count
    );
}

/**
 * Actualiza el snapshot final de una audiencia dinámica.
 *
 * @param int   $delivery_id   ID del envío.
 * @param array $delivery_meta Metadatos.
 * @param array $recipients    Destinatarios finales.
 * @return void
 */
function update_dynamic_delivery_audience_snapshot($delivery_id, $delivery_meta, $recipients)
{
    $config = is_array($delivery_meta['audience_config'] ?? null) ? $delivery_meta['audience_config'] : array();
    $final_snapshot = build_delivery_audience_snapshot(
        is_array($config['audience_items'] ?? null) ? $config['audience_items'] : array(),
        is_array($config['manual_emails'] ?? null) ? $config['manual_emails'] : array(),
        is_array($config['excluded_emails'] ?? null) ? $config['excluded_emails'] : array(),
        $recipients,
        'dynamic'
    );

    $snapshot = is_array($delivery_meta['audience_snapshot'] ?? null) ? $delivery_meta['audience_snapshot'] : $final_snapshot;
    $snapshot['final_count'] = count((array) $recipients);
    $snapshot['final_items'] = $final_snapshot['items'];
    $snapshot['final_summary'] = $final_snapshot['summary'];
    $snapshot['final_updated_at'] = current_time('mysql', true);
    $snapshot['summary'] = $final_snapshot['summary'];

    $delivery_meta['audience_snapshot'] = $snapshot;
    update_option('pbm_delivery_meta_' . absint($delivery_id), $delivery_meta, false);
}

/**
 * Obtiene todos los envíos programados
 *
 * @return array
 */
function get_all_scheduled_emails()
{
    global $wpdb;

    $table = $wpdb->prefix . 'pbm_scheduled_emails';

    return $wpdb->get_results(
        "SELECT * FROM {$table} ORDER BY scheduled_at DESC"
    );
}

/**
 * Ejecuta un envío programado (callback de Action Scheduler)
 *
 * @param int $scheduled_id ID del envío programado.
 * @return void
 */
function execute_scheduled_email($scheduled_id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'pbm_scheduled_emails';
    $scheduled = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE id = %d",
        $scheduled_id
    ));

    if (! $scheduled || $scheduled->status !== 'pending') {
        return;
    }

    // Actualizar estado a running
    update_scheduled_email_status($scheduled_id, 'running');

    try {
        $delivery_meta = get_delivery_meta($scheduled_id);
        $plain_body = ! empty($delivery_meta['plain_body']);

        if ('dynamic' === ($delivery_meta['audience_mode'] ?? 'fixed')) {
            $users = resolve_dynamic_scheduled_recipients($delivery_meta);
            save_scheduled_recipients_snapshot($scheduled_id, $users);
            update_dynamic_delivery_audience_snapshot($scheduled_id, $delivery_meta, $users);
        } else {
            $users = get_scheduled_recipients_snapshot($scheduled_id);
        }

        if (empty($users)) {
            // Compatibilidad: envíos antiguos por rol.
            $users = get_users_by_role($scheduled->user_role);
        }

        if (empty($users)) {
            throw new \Exception(__('No se encontraron usuarios con ese rol', 'wc-pbm'));
        }

        // Programar lotes usando el sistema existente
        $scheduled_count = schedule_email_batches(
            $users,
            $scheduled->subject,
            $scheduled->message,
            (int) $scheduled->batch_size,
            (int) $scheduled->emails_per_hour,
            $scheduled_id,
            $plain_body
        );

        if ($scheduled_count < 1) {
            throw new \Exception(__('No se pudieron programar lotes de envío', 'wc-pbm'));
        }
    } catch (\Exception $e) {
        // Crear log de error
        global $wpdb;
        $table = $wpdb->prefix . 'pbm_scheduled_logs';
        $wpdb->insert(
            $table,
            array(
                'scheduled_id'  => $scheduled_id,
                'started_at'    => current_time('mysql', true),
                'completed_at'  => current_time('mysql', true),
                'total_sent'    => 0,
                'total_failed'  => 0,
                'error_message' => $e->getMessage(),
            ),
            array('%d', '%s', '%s', '%d', '%d', '%s')
        );

        update_scheduled_email_status($scheduled_id, 'cancelled');
    }
}

/**
 * Recalcula destinatarios de una audiencia dinámica programada.
 *
 * @param array $delivery_meta Metadatos del envío.
 * @return array
 */
function resolve_dynamic_scheduled_recipients($delivery_meta)
{
    $config = is_array($delivery_meta['audience_config'] ?? null) ? $delivery_meta['audience_config'] : array();
    $audience_items = is_array($config['audience_items'] ?? null) ? $config['audience_items'] : array();
    $manual_emails = is_array($config['manual_emails'] ?? null) ? $config['manual_emails'] : array();
    $excluded_emails = is_array($config['excluded_emails'] ?? null) ? $config['excluded_emails'] : array();

    $recipients = resolve_global_recipients($audience_items, $manual_emails);
    return exclude_recipients_from_list($recipients, $excluded_emails);
}

/**
 * Guarda el snapshot final de destinatarios de un envío programado.
 *
 * @param int   $scheduled_id ID del envío programado.
 * @param array $recipients   Destinatarios finales.
 * @return void
 */
function save_scheduled_recipients_snapshot($scheduled_id, $recipients)
{
    $option_key = 'pbm_scheduled_recipients_' . absint($scheduled_id);

    if (false === get_option($option_key, false)) {
        add_option($option_key, $recipients, '', false);
        return;
    }

    update_option($option_key, $recipients, false);
}

/**
 * Obtiene destinatarios snapshot para un envío programado.
 *
 * @param int $scheduled_id ID del envío programado.
 * @return array
 */
function get_scheduled_recipients_snapshot($scheduled_id)
{
    $option_key = 'pbm_scheduled_recipients_' . absint($scheduled_id);
    $recipients = get_option($option_key, array());

    if (is_array($recipients) && ! empty($recipients)) {
        return $recipients;
    }

    return array();
}

/**
 * Actualiza el estado de un envío programado
 *
 * @param int    $scheduled_id ID del envío.
 * @param string $status       Nuevo estado.
 * @return void
 */
function update_scheduled_email_status($scheduled_id, $status)
{
    global $wpdb;

    $table = $wpdb->prefix . 'pbm_scheduled_emails';

    $wpdb->update(
        $table,
        array('status' => $status),
        array('id' => $scheduled_id),
        array('%s'),
        array('%d')
    );
}

/**
 * Obtiene el total esperado de mensajes para un envío.
 *
 * @param int $scheduled_id ID del envío.
 * @return int Total esperado.
 */
function get_expected_messages_count($scheduled_id)
{
    $recipients = get_option('pbm_scheduled_recipients_' . absint($scheduled_id), array());
    if (is_array($recipients) && ! empty($recipients)) {
        return count($recipients);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'pbm_scheduled_emails';
    $scheduled = $wpdb->get_row($wpdb->prepare(
        "SELECT user_role FROM {$table} WHERE id = %d",
        absint($scheduled_id)
    ));

    if (! $scheduled || empty($scheduled->user_role)) {
        return 0;
    }

    $users = get_users_by_role($scheduled->user_role);
    return is_array($users) ? count($users) : 0;
}

/**
 * Actualiza a completado cuando los logs cubren todos los mensajes esperados.
 *
 * @param int $scheduled_id ID del envío.
 * @return void
 */
function maybe_complete_scheduled_email($scheduled_id)
{
    $expected = get_expected_messages_count($scheduled_id);
    if ($expected < 1) {
        return;
    }

    $logs = get_scheduled_logs($scheduled_id);
    $processed = 0;
    foreach ((array) $logs as $log) {
        $processed += (int) ($log->total_sent ?? 0);
        $processed += (int) ($log->total_failed ?? 0);
    }

    if ($processed >= $expected) {
        update_scheduled_email_status($scheduled_id, 'completed');
        delete_option('pbm_scheduled_recipients_' . absint($scheduled_id));
    }
}

/**
 * Crea un log individual para un lote ejecutado
 *
 * @param int $scheduled_id ID del envío programado.
 * @param int $sent         Emails enviados correctamente.
 * @param int $failed       Emails fallidos.
 * @return void
 */
function create_batch_log($scheduled_id, $sent, $failed)
{
    global $wpdb;

    $table = $wpdb->prefix . 'pbm_scheduled_logs';

    $inserted = $wpdb->insert(
        $table,
        array(
            'scheduled_id'  => $scheduled_id,
            'started_at'    => current_time('mysql', true),
            'completed_at'  => current_time('mysql', true),
            'total_sent'    => $sent,
            'total_failed'  => $failed,
        ),
        array('%d', '%s', '%s', '%d', '%d')
    );

    if (! $inserted) {
        update_scheduled_email_status($scheduled_id, 'failed');
    }
}

/**
 * Obtiene los logs de un envío programado
 *
 * @param int $scheduled_id ID del envío.
 * @return array Array de logs.
 */
function get_scheduled_logs($scheduled_id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'pbm_scheduled_logs';

    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table} WHERE scheduled_id = %d ORDER BY started_at DESC",
        $scheduled_id
    ));
}

/**
 * Añade un evento básico por destinatario.
 *
 * @param int    $delivery_id  ID del envío.
 * @param string $email        Email destinatario.
 * @param string $status       Estado del evento.
 * @param string $error        Error básico.
 * @param int    $batch_index  Índice de lote.
 * @return void
 */
function add_delivery_event($delivery_id, $email, $status, $error = '', $batch_index = 0)
{
    $delivery_id = absint($delivery_id);
    $email = sanitize_email($email);
    $status = 'failed' === $status ? 'failed' : 'sent';

    if ($delivery_id < 1 || ! is_email($email)) {
        return;
    }

    $option_key = 'pbm_delivery_events_' . $delivery_id;
    $events = get_option($option_key, array());
    $events = is_array($events) ? $events : array();
    $events[] = array(
        'email'       => $email,
        'status'      => $status,
        'timestamp'   => current_time('mysql', true),
        'error'       => sanitize_text_field($error),
        'batch_index' => absint($batch_index),
    );

    if (false === get_option($option_key, false)) {
        add_option($option_key, $events, '', false);
        return;
    }

    update_option($option_key, $events, false);
}

/**
 * Obtiene eventos por destinatario.
 *
 * @param int $delivery_id ID del envío.
 * @return array
 */
function get_delivery_events($delivery_id)
{
    $events = get_option('pbm_delivery_events_' . absint($delivery_id), array());
    return is_array($events) ? $events : array();
}

/**
 * Comprueba si un envío puede borrarse sin afectar colas activas.
 *
 * @param int $scheduled_id ID del envío.
 * @return bool True si el envío existe y está en estado borrable.
 */
function can_delete_scheduled_email($scheduled_id)
{
    global $wpdb;

    $table_emails = $wpdb->prefix . 'pbm_scheduled_emails';
    $status = $wpdb->get_var($wpdb->prepare(
        "SELECT status FROM {$table_emails} WHERE id = %d",
        absint($scheduled_id)
    ));

    if (in_array($status, array('completed', 'cancelled', 'failed'), true)) {
        return true;
    }

    return 'running' === $status && ! has_pending_broadcast_actions($scheduled_id);
}

/**
 * Comprueba si quedan acciones pendientes para un envío.
 *
 * @param int $scheduled_id ID del envío.
 * @return bool
 */
function has_pending_broadcast_actions($scheduled_id)
{
    if (! function_exists('as_has_scheduled_action')) {
        return false;
    }

    $scheduled_id = absint($scheduled_id);
    if (as_has_scheduled_action('pbm_execute_scheduled_email', array($scheduled_id), 'product-broadcast-mailer')) {
        return true;
    }

    $recipients = get_option('pbm_scheduled_recipients_' . $scheduled_id, array());
    if (! is_array($recipients) || empty($recipients)) {
        return false;
    }

    $delivery_meta = get_delivery_meta($scheduled_id);
    $plain_body = ! empty($delivery_meta['plain_body']);

    return as_has_scheduled_action(
        'pbm_process_email_batch',
        array($recipients, get_scheduled_email_subject($scheduled_id), get_scheduled_email_message($scheduled_id), $scheduled_id, $plain_body),
        'product-broadcast-mailer'
    );
}

/**
 * Obtiene el asunto de un envío.
 *
 * @param int $scheduled_id ID del envío.
 * @return string
 */
function get_scheduled_email_subject($scheduled_id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'pbm_scheduled_emails';

    return (string) $wpdb->get_var($wpdb->prepare(
        "SELECT subject FROM {$table} WHERE id = %d",
        absint($scheduled_id)
    ));
}

/**
 * Obtiene el mensaje de un envío.
 *
 * @param int $scheduled_id ID del envío.
 * @return string
 */
function get_scheduled_email_message($scheduled_id)
{
    global $wpdb;

    $table = $wpdb->prefix . 'pbm_scheduled_emails';

    return (string) $wpdb->get_var($wpdb->prepare(
        "SELECT message FROM {$table} WHERE id = %d",
        absint($scheduled_id)
    ));
}

/**
 * Elimina un envío programado y todos sus logs
 *
 * @param int $scheduled_id ID del envío.
 * @return bool True si se eliminó correctamente.
 */
function delete_scheduled_email_with_logs($scheduled_id)
{
    global $wpdb;

    $scheduled_id = absint($scheduled_id);
    if (! can_delete_scheduled_email($scheduled_id)) {
        return false;
    }

    $table_logs = $wpdb->prefix . 'pbm_scheduled_logs';
    $table_emails = $wpdb->prefix . 'pbm_scheduled_emails';

    // Borrar logs primero
    $wpdb->delete($table_logs, array('scheduled_id' => $scheduled_id), array('%d'));

    // Borrar email programado
    $deleted = (bool) $wpdb->delete($table_emails, array('id' => $scheduled_id), array('%d'));

    if ($deleted) {
        delete_option('pbm_delivery_meta_' . $scheduled_id);
        delete_option('pbm_scheduled_recipients_' . $scheduled_id);
        delete_option('pbm_delivery_events_' . $scheduled_id);
    }

    return $deleted;
}

/**
 * Elimina todos los envíos programados por estado
 *
 * @param string $status Estado (completed o cancelled).
 * @return int Número de registros eliminados.
 */
function bulk_delete_scheduled_by_status($status)
{
    global $wpdb;

    $table_emails = $wpdb->prefix . 'pbm_scheduled_emails';
    $table_logs = $wpdb->prefix . 'pbm_scheduled_logs';

    // Obtener IDs
    $ids = $wpdb->get_col($wpdb->prepare(
        "SELECT id FROM {$table_emails} WHERE status = %s",
        $status
    ));

    if (empty($ids)) {
        return 0;
    }

    $ids_placeholder = implode(',', array_fill(0, count($ids), '%d'));

    // Borrar logs
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table_logs} WHERE scheduled_id IN ({$ids_placeholder})",
        ...$ids
    ));

    // Borrar emails
    $deleted = $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table_emails} WHERE status = %s",
        $status
    ));

    foreach ($ids as $id) {
        delete_option('pbm_delivery_meta_' . absint($id));
        delete_option('pbm_scheduled_recipients_' . absint($id));
        delete_option('pbm_delivery_events_' . absint($id));
    }

    return $deleted;
}
