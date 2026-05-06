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
        $users = get_scheduled_recipients_snapshot($scheduled_id);

        if (empty($users)) {
            // Compatibilidad: envíos antiguos por rol.
            $users = get_users_by_role($scheduled->user_role);
        }

        if (empty($users)) {
            throw new \Exception(__('No se encontraron usuarios con ese rol', 'wc-pbm'));
        }

        // Programar lotes usando el sistema existente
        schedule_email_batches(
            $users,
            $scheduled->subject,
            $scheduled->message,
            (int) $scheduled->batch_size,
            (int) $scheduled->emails_per_hour,
            $scheduled_id
        );

        // Actualizar estado a completed
        update_scheduled_email_status($scheduled_id, 'completed');
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

        update_scheduled_email_status($scheduled_id, 'completed');
    }
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
        delete_option($option_key);
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

    $wpdb->insert(
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
 * Elimina un envío programado y todos sus logs
 *
 * @param int $scheduled_id ID del envío.
 * @return bool True si se eliminó correctamente.
 */
function delete_scheduled_email_with_logs($scheduled_id)
{
    global $wpdb;

    $table_logs = $wpdb->prefix . 'pbm_scheduled_logs';
    $table_emails = $wpdb->prefix . 'pbm_scheduled_emails';

    // Borrar logs primero
    $wpdb->delete($table_logs, array('scheduled_id' => $scheduled_id), array('%d'));

    // Borrar email programado
    $deleted = (bool) $wpdb->delete($table_emails, array('id' => $scheduled_id), array('%d'));

    if ($deleted) {
        delete_option('pbm_delivery_meta_' . absint($scheduled_id));
        delete_option('pbm_scheduled_recipients_' . absint($scheduled_id));
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
    }

    return $deleted;
}
