<?php
/**
 * Template scanner for Email String Editor.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Scans allowed WooCommerce email template locations.
 */
class Template_Scanner
{
    /**
     * Return allowed email templates indexed by ID.
     *
     * @return array
     */
    public function get_templates()
    {
        $templates = array();

        foreach ($this->get_scan_sources() as $source) {
            if (empty($source['path']) || ! is_dir($source['path'])) {
                continue;
            }

            $files = glob(trailingslashit($source['path']) . '*.php');
            if (! is_array($files)) {
                continue;
            }

            foreach ($files as $file) {
                $real_path = realpath($file);
                if (! $real_path || ! $this->is_allowed_path($real_path, $source['path'])) {
                    continue;
                }

                $relative = basename($real_path);
                $id = sanitize_key($source['id']) . ':' . $relative;
                $templates[$id] = array(
                    'id'            => $id,
                    'label'         => $this->get_template_label($relative),
                    'file'          => $real_path,
                    'relative_path' => $relative,
                    'source'        => $source['id'],
                    'source_label'  => $source['label'],
                );
            }
        }

        uasort($templates, static function ($a, $b) {
            return strcasecmp($a['source_label'] . $a['label'], $b['source_label'] . $b['label']);
        });

        return $templates;
    }

    /**
     * Get one template by ID.
     *
     * @param string $template_id Template ID.
     * @return array|null
     */
    public function get_template($template_id)
    {
        $templates = $this->get_templates();
        return $templates[$template_id] ?? null;
    }

    /**
     * Extract translatable strings from template.
     *
     * @param string $template_id Template ID.
     * @return array
     */
    public function extract_strings($template_id)
    {
        $template = $this->get_template($template_id);
        if (! $template || empty($template['file']) || ! is_readable($template['file'])) {
            return array();
        }

        $content = file_get_contents($template['file']);
        if (! is_string($content)) {
            return array();
        }

        $patterns = array(
            '__'         => "/__\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
            '_e'        => "/_e\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
            'esc_html__' => "/esc_html__\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
            'esc_html_e' => "/esc_html_e\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
            'esc_attr__' => "/esc_attr__\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
            'esc_attr_e' => "/esc_attr_e\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
        );

        $strings = array();
        foreach ($patterns as $function => $pattern) {
            if (! preg_match_all($pattern, $content, $matches)) {
                continue;
            }

            foreach ($matches[1] as $match) {
                $text = stripslashes((string) $match);
                if ('' === $text || isset($strings[$text])) {
                    continue;
                }

                $strings[$text] = array(
                    'text'     => $text,
                    'function' => $function,
                );
            }
        }

        return array_values($strings);
    }

    /**
     * Return configured scan sources.
     *
     * @return array
     */
    private function get_scan_sources()
    {
        $sources = array(
            array(
                'id'    => 'woocommerce',
                'label' => __('WooCommerce core', 'wc-pbm'),
                'path'  => WP_PLUGIN_DIR . '/woocommerce/templates/emails/',
            ),
            array(
                'id'    => 'theme-child',
                'label' => __('Tema hijo', 'wc-pbm'),
                'path'  => get_stylesheet_directory() . '/woocommerce/emails/',
            ),
            array(
                'id'    => 'theme-parent',
                'label' => __('Tema padre', 'wc-pbm'),
                'path'  => get_template_directory() . '/woocommerce/emails/',
            ),
            array(
                'id'    => 'woocommerce-subscriptions',
                'label' => __('WooCommerce Subscriptions', 'wc-pbm'),
                'path'  => WP_PLUGIN_DIR . '/woocommerce-subscriptions/templates/emails/',
            ),
            array(
                'id'    => 'woocommerce-bookings',
                'label' => __('WooCommerce Bookings', 'wc-pbm'),
                'path'  => WP_PLUGIN_DIR . '/woocommerce-bookings/templates/emails/',
            ),
            array(
                'id'    => 'woocommerce-memberships',
                'label' => __('WooCommerce Memberships', 'wc-pbm'),
                'path'  => WP_PLUGIN_DIR . '/woocommerce-memberships/templates/emails/',
            ),
        );

        return $sources;
    }

    /**
     * Check that file is under allowed base path.
     *
     * @param string $file_path File path.
     * @param string $base_path Base path.
     * @return bool
     */
    private function is_allowed_path($file_path, $base_path)
    {
        $base_real = realpath($base_path);
        return $base_real && 0 === strpos($file_path, trailingslashit($base_real));
    }

    /**
     * Get human label for template file.
     *
     * @param string $relative_path Relative file path.
     * @return string
     */
    private function get_template_label($relative_path)
    {
        if (function_exists('WC') && WC()->mailer()) {
            foreach ((array) WC()->mailer()->get_emails() as $email) {
                $html_match = ! empty($email->template_html) && basename($email->template_html) === $relative_path;
                $plain_match = ! empty($email->template_plain) && basename($email->template_plain) === $relative_path;

                if ($html_match || $plain_match) {
                    return $email->get_title() . ' · ' . $relative_path;
                }
            }
        }

        return ucwords(str_replace(array('-', '.php'), array(' ', ''), $relative_path)) . ' · ' . $relative_path;
    }
}
