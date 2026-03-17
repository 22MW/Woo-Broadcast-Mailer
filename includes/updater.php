<?php
/**
 * GitHub Releases updater
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer;

defined('ABSPATH') || exit;

/**
 * Registra los hooks del updater
 *
 * @return void
 */
function register_updater_hooks()
{
    if (! is_admin()) {
        return;
    }

    add_filter('site_transient_update_plugins', __NAMESPACE__ . '\\filter_plugin_updates');
    add_filter('plugins_api', __NAMESPACE__ . '\\filter_plugin_info', 10, 3);
    add_filter('upgrader_source_selection', __NAMESPACE__ . '\\fix_source_dir', 10, 4);
}

/**
 * URL de la API de GitHub para releases
 *
 * @return string
 */
function get_github_api_url()
{
    return 'https://api.github.com/repos/22MW/Woo-Broadcast-Mailer/releases/latest';
}

/**
 * Obtiene la información de la última release (cacheada)
 *
 * @return array|null
 */
function get_latest_release()
{
    $cache_key = 'pbm_github_release_latest';
    $cached = get_transient($cache_key);

    if (is_array($cached)) {
        return $cached;
    }

    $response = wp_remote_get(get_github_api_url(), array(
        'timeout' => 10,
        'headers' => array(
            'Accept' => 'application/vnd.github+json',
            'User-Agent' => 'Woo-Broadcast-Mailer',
        ),
    ));

    if (is_wp_error($response)) {
        return null;
    }

    $code = wp_remote_retrieve_response_code($response);
    if ($code !== 200) {
        return null;
    }

    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (! is_array($data)) {
        return null;
    }

    set_transient($cache_key, $data, HOUR_IN_SECONDS);
    return $data;
}

/**
 * Extrae la versión remota desde el tag
 *
 * @param array $release Release data.
 * @return string
 */
function get_remote_version($release)
{
    $tag = isset($release['tag_name']) ? $release['tag_name'] : '';
    return ltrim((string) $tag, 'v');
}

/**
 * Obtiene el paquete ZIP de la release
 *
 * @param array $release Release data.
 * @return string
 */
function get_package_url($release)
{
    $tag = isset($release['tag_name']) ? (string) $release['tag_name'] : '';
    if ($tag === '') {
        return '';
    }

    return 'https://github.com/22MW/Woo-Broadcast-Mailer/archive/refs/tags/' . $tag . '.zip';
}

/**
 * Devuelve el slug del plugin
 *
 * @return string
 */
function get_plugin_slug()
{
    return plugin_basename(dirname(__DIR__) . '/woo-broadcast-mailer.php');
}

/**
 * Devuelve el directorio del plugin
 *
 * @return string
 */
function get_plugin_dir_name()
{
    return 'woo-broadcast-mailer';
}

/**
 * Filtra actualizaciones de plugins
 *
 * @param object $transient Transient data.
 * @return object
 */
function filter_plugin_updates($transient)
{
    if (! isset($transient->checked) || ! is_array($transient->checked)) {
        return $transient;
    }

    $release = get_latest_release();
    if (! $release) {
        return $transient;
    }

    $remote_version = get_remote_version($release);
    $slug = get_plugin_slug();

    if (empty($remote_version) || empty($transient->checked[$slug])) {
        return $transient;
    }

    $local_version = $transient->checked[$slug];
    if (version_compare($remote_version, $local_version, '<=')) {
        return $transient;
    }

    $transient->response[$slug] = (object) array(
        'slug' => 'woo-broadcast-mailer',
        'plugin' => $slug,
        'new_version' => $remote_version,
        'url' => 'https://github.com/22MW/Woo-Broadcast-Mailer',
        'package' => get_package_url($release),
    );

    return $transient;
}

/**
 * Filtra la info del plugin en la pantalla de actualizaciones
 *
 * @param false|object|array $result API result.
 * @param string            $action Action name.
 * @param object            $args   Arguments.
 * @return object|false
 */
function filter_plugin_info($result, $action, $args)
{
    if ($action !== 'plugin_information') {
        return $result;
    }

    if (empty($args->slug) || $args->slug !== 'woo-broadcast-mailer') {
        return $result;
    }

    $release = get_latest_release();
    if (! $release) {
        return $result;
    }

    $info = new \stdClass();
    $info->name = 'Woo Broadcast Mailer';
    $info->slug = 'woo-broadcast-mailer';
    $info->version = get_remote_version($release);
    $info->author = '22MW';
    $info->homepage = 'https://github.com/22MW/Woo-Broadcast-Mailer';
    $info->download_link = get_package_url($release);
    $info->sections = array(
        'description' => 'Sistema de envío masivo de emails para WooCommerce.',
    );

    return $info;
}

/**
 * Corrige el nombre de la carpeta del plugin al actualizar
 *
 * @param string $source        Ruta temporal del paquete.
 * @param string $remote_source Ruta remota.
 * @param object $upgrader      Upgrader instance.
 * @param array  $hook_extra    Hook extra args.
 * @return string
 */
function fix_source_dir($source, $remote_source, $upgrader, $hook_extra)
{
    if (empty($hook_extra['plugin']) || $hook_extra['plugin'] !== get_plugin_slug()) {
        return $source;
    }

    $expected = get_plugin_dir_name();
    if (basename($source) === $expected) {
        return $source;
    }

    $corrected = trailingslashit(dirname($source)) . $expected;
    if (@rename($source, $corrected)) {
        return $corrected;
    }

    return $source;
}
