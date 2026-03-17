<?php

/**
 * Uninstall handler for Woo Broadcast Mailer.
 *
 * @package WC_Product_Broadcast_Mailer
 */

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Whether to delete plugin data on uninstall.
 *
 * Set PBM_DELETE_DATA_ON_UNINSTALL to true in wp-config.php,
 * or set the option "pbm_delete_data_on_uninstall" to truthy.
 *
 * @return bool
 */
function pbm_should_delete_data()
{
    if (defined('PBM_DELETE_DATA_ON_UNINSTALL') && PBM_DELETE_DATA_ON_UNINSTALL) {
        return true;
    }

    return (bool) get_option('pbm_delete_data_on_uninstall', false);
}

if (! pbm_should_delete_data()) {
    return;
}

global $wpdb;
$table_emails = $wpdb->prefix . 'pbm_scheduled_emails';
$table_logs = $wpdb->prefix . 'pbm_scheduled_logs';

$wpdb->query("DROP TABLE IF EXISTS {$table_logs}");
$wpdb->query("DROP TABLE IF EXISTS {$table_emails}");
delete_option('pbm_delete_data_on_uninstall');
