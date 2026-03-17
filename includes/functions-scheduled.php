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
        <!-- Formulario de creación -->
        <div style="max-width: 800px; margin-bottom: 30px;">
            <h2><?php esc_html_e('Programar Nuevo Envío', 'wc-pbm'); ?></h2>

            <form id="pbm-scheduled-form" method="post">
                <?php wp_nonce_field('pbm_scheduled_action', 'pbm_scheduled_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="pbm_user_role"><?php esc_html_e('Rol de Usuario', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <?php render_role_selector(); ?>
                            <p class="description">
                                <?php esc_html_e('Los usuarios se obtendrán en el momento del envío, no ahora.', 'wc-pbm'); ?>
                            </p>
                            <p style="margin-top: 10px;">
                                <button type="button" id="pbm-preview-role-btn" class="button">
                                    <?php esc_html_e('Vista Previa de Destinatarios', 'wc-pbm'); ?>
                                </button>
                            </p>

                            <div id="pbm-role-preview-results" style="display:none; margin-top: 15px; padding: 15px; background: rgb(240 240 240); border-radius: 10px;">
                                <h4 style="margin-top: 0; margin-bottom: 10px;"><?php esc_html_e('Resumen de Destinatarios', 'wc-pbm'); ?></h4>
                                <div id="pbm-role-preview-content"></div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_scheduled_subject"><?php esc_html_e('Asunto', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="pbm_scheduled_subject" name="pbm_scheduled_subject" class="regular-text" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_scheduled_message"><?php esc_html_e('Mensaje', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <?php
                            wp_editor('', 'pbm_scheduled_message', array(
                                'textarea_rows' => 15,
                                'media_buttons' => false,
                                'teeny'         => false,
                                'quicktags'     => true,
                            ));
                            ?>
                            <p class="description">
                                <?php esc_html_e('Usa {customer_name} para el nombre del usuario.', 'wc-pbm'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_scheduled_datetime"><?php esc_html_e('Fecha y Hora de Envío', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <input type="datetime-local" id="pbm_scheduled_datetime" name="pbm_scheduled_datetime" required>
                            <p class="description">
                                <?php esc_html_e('El envío se ejecutará exactamente a esta hora.', 'wc-pbm'); ?>
                            </p>
                            <p style="margin-top: 8px; padding: 8px; background: rgb(240, 240, 240); border-radius: 5px;">
                                <strong><?php esc_html_e('Fecha y hora actual del servidor:', 'wc-pbm'); ?></strong>
                                <?php echo esc_html(current_time('d/m/Y H:i:s')); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_scheduled_batch_size"><?php esc_html_e('Tamaño de lote', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="pbm_scheduled_batch_size" name="pbm_scheduled_batch_size" value="30" min="10" max="100" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Correos por lote (recomendado: 20-50)', 'wc-pbm'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="pbm_scheduled_emails_per_hour"><?php esc_html_e('Emails por hora', 'wc-pbm'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="pbm_scheduled_emails_per_hour" name="pbm_scheduled_emails_per_hour" value="200" min="10" max="1000" class="small-text">
                            <p class="description">
                                <?php esc_html_e('Límite de emails a enviar por hora.', 'wc-pbm'); ?>
                            </p>
                            <p id="pbm-scheduled-interval-preview" style="margin-top: 8px; padding: 8px; background: #f0f0f0; border-radius: 5px; display: none;">
                                <strong><?php esc_html_e('Intervalo calculado:', 'wc-pbm'); ?></strong> <span id="pbm-scheduled-interval-value"></span>
                            </p>
                        </td>
                    </tr>
                </table>

                <p class="submit" style="text-align:right">
                    <button type="submit" id="pbm-create-scheduled-btn" class="button" style="border: none; padding: 10px 30px; background: #1a1a1a; color: #ffffff;">
                        <?php esc_html_e('Programar Envío', 'wc-pbm'); ?>
                    </button>
                </p>
            </form>
        </div>

        <!-- Lista de envíos programados -->
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
            // Calcular intervalo en tiempo real
            function updateScheduledIntervalPreview() {
                const batchSize = parseInt($('#pbm_scheduled_batch_size').val()) || 30;
                const perHour = parseInt($('#pbm_scheduled_emails_per_hour').val()) || 200;
                const intervalMinutes = Math.ceil((batchSize / perHour) * 60);

                if (perHour > 0 && batchSize > 0) {
                    $('#pbm-scheduled-interval-value').text(intervalMinutes + ' <?php echo esc_js(__('minutos entre lotes', 'wc-pbm')); ?>');
                    $('#pbm-scheduled-interval-preview').slideDown();
                }
            }

            $('#pbm_scheduled_batch_size, #pbm_scheduled_emails_per_hour').on('input', updateScheduledIntervalPreview);
            updateScheduledIntervalPreview();

            // Vista previa de destinatarios por rol
            $('#pbm-preview-role-btn').on('click', function(e) {
                e.preventDefault();

                const role = $('#pbm_user_role').val();
                if (!role) {
                    alert('<?php echo esc_js(__('Por favor selecciona un rol', 'wc-pbm')); ?>');
                    return;
                }

                $(this).prop('disabled', true).text('<?php echo esc_js(__('Cargando...', 'wc-pbm')); ?>');

                $.post(ajaxurl, {
                    action: 'pbm_preview_role_recipients',
                    role: role,
                    nonce: $('#pbm_scheduled_nonce').val()
                }, function(response) {
                    if (response.success) {
                        let html = '<p style="margin: 0 0 10px;"><strong><?php echo esc_js(__('Total de usuarios activos:', 'wc-pbm')); ?></strong> ' + response.data.total + '</p>';

                        if (response.data.emails && response.data.emails.length > 0) {
                            html += '<div style="margin-top: 12px; padding: 10px; background: #fff; border: 1px solid #ddd; border-radius: 3px; max-height: 150px; overflow-y: auto; font-size: 12px; line-height: 1.6;">';
                            html += '<strong><?php echo esc_js(__('Usuarios:', 'wc-pbm')); ?></strong><br>';
                            html += '<span style="color: #555;">' + response.data.emails.join(', ') + '</span>';
                            html += '</div>';
                        }

                        $('#pbm-role-preview-content').html(html);
                        $('#pbm-role-preview-results').slideDown();
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Error al obtener destinatarios', 'wc-pbm')); ?>');
                    }
                }).always(function() {
                    $('#pbm-preview-role-btn').prop('disabled', false).text('<?php echo esc_js(__('Vista Previa de Destinatarios', 'wc-pbm')); ?>');
                });
            });

            // Crear envío programado
            $('#pbm-scheduled-form').on('submit', function(e) {
                e.preventDefault();

                const role = $('#pbm_user_role').val();
                const subject = $('#pbm_scheduled_subject').val();
                const message = $('#pbm_scheduled_message').val();
                const datetime = $('#pbm_scheduled_datetime').val();

                if (!role || !subject || !message || !datetime) {
                    alert('<?php echo esc_js(__('Por favor completa todos los campos', 'wc-pbm')); ?>');
                    return;
                }

                if (!confirm('<?php echo esc_js(__('¿Confirmas la programación de este envío?', 'wc-pbm')); ?>')) {
                    return;
                }

                $('#pbm-create-scheduled-btn').prop('disabled', true).text('<?php echo esc_js(__('Programando...', 'wc-pbm')); ?>');

                $.post(ajaxurl, {
                    action: 'pbm_create_scheduled_email',
                    role: role,
                    subject: subject,
                    message: message,
                    scheduled_datetime: datetime,
                    batch_size: $('#pbm_scheduled_batch_size').val(),
                    emails_per_hour: $('#pbm_scheduled_emails_per_hour').val(),
                    nonce: $('#pbm_scheduled_nonce').val()
                }, function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Error al programar el envío', 'wc-pbm')); ?>');
                        $('#pbm-create-scheduled-btn').prop('disabled', false).text('<?php echo esc_js(__('Programar Envío', 'wc-pbm')); ?>');
                    }
                });
            });

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

    echo '<select id="pbm_user_role" name="pbm_user_role" required>';
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
        echo '<p>' . esc_html__('No hay envíos programados.', 'wc-pbm') . '</p>';
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
                <th><?php esc_html_e('Fecha Programada', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Rol', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Asunto', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Estado', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Config. Envío', 'wc-pbm'); ?></th>
                <th><?php esc_html_e('Acciones', 'wc-pbm'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($scheduled_emails as $email) : ?>
                <tr>
                    <td><?php echo esc_html(get_date_from_gmt($email->scheduled_at, 'd/m/Y H:i')); ?></td>
                    <td><?php echo esc_html($email->user_role); ?></td>
                    <td><?php echo esc_html($email->subject); ?></td>
                    <td>
                        <?php
                        $status_labels = array(
                            'pending'   => __('Pendiente', 'wc-pbm'),
                            'running'   => __('En ejecución', 'wc-pbm'),
                            'completed' => __('Completado', 'wc-pbm'),
                            'cancelled' => __('Cancelado', 'wc-pbm'),
                        );
                        echo esc_html($status_labels[$email->status] ?? $email->status);
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
        // Obtener usuarios activos del rol EN ESTE MOMENTO
        $users = get_users_by_role($scheduled->user_role);

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
    return (bool) $wpdb->delete($table_emails, array('id' => $scheduled_id), array('%d'));
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
    return $wpdb->query($wpdb->prepare(
        "DELETE FROM {$table_emails} WHERE status = %s",
        $status
    ));
}
