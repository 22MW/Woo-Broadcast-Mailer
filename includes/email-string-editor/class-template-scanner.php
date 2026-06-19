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

        $templates = array_merge($templates, $this->get_dynamic_strings());

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

        if (! $template) {
            return array();
        }

        $strings = array();

        if (! empty($template['file']) && is_readable($template['file'])) {
            $content = file_get_contents($template['file']);
            if (! is_string($content)) {
                $content = '';
            }

            $patterns = array(
                '__'         => "/__\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
                '_e'        => "/_e\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
                'esc_html__' => "/esc_html__\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
                'esc_html_e' => "/esc_html_e\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
                'esc_attr__' => "/esc_attr__\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
                'esc_attr_e' => "/esc_attr_e\s*\(\s*['\"](.+?)['\"]\s*,\s*['\"][a-zA-Z0-9_-]+['\"]\s*\)/",
            );

            foreach ($patterns as $function => $pattern) {
                if (! preg_match_all($pattern, $content, $matches)) {
                    continue;
                }

                foreach ($matches[1] as $match) {
                    $text = stripslashes((string) $match);
                    $this->add_string($strings, $text, $function);
                }
            }

            $this->add_email_object_strings($strings, $template);

            $this->add_html_text_strings($strings, $content);
        }

        if ('dynamic' === $template['source']) {
            $this->add_dynamic_strings($strings, $template['id']);
        }

        return array_values($strings);
    }

    /**
     * Add known dynamic strings from the WC_Email object linked to a template.
     *
     * @param array $strings  Current strings indexed by original text.
     * @param array $template Template data.
     * @return void
     */
    private function add_email_object_strings(&$strings, $template)
    {
        $relative_path = (string) ($template['relative_path'] ?? '');
        $email = $this->get_email_for_template($relative_path);

        if (! $email) {
            return;
        }

        if (method_exists($email, 'get_title')) {
            $this->add_string($strings, (string) $email->get_title(), 'email_title');
        }

        $this->add_string($strings, $this->get_email_setting($email, 'heading', 'get_default_heading'), 'email_heading');
        $this->add_string($strings, $this->get_email_setting($email, 'subject', 'get_default_subject'), 'email_subject');
    }

    /**
     * Get an email setting without requiring an order/object context.
     *
     * @param object $email          WooCommerce email object.
     * @param string $option         Option key.
     * @param string $default_method Default method name.
     * @return string
     */
    private function get_email_setting($email, $option, $default_method)
    {
        try {
            $default = method_exists($email, $default_method) ? (string) $email->{$default_method}() : '';

            if (method_exists($email, 'get_option')) {
                return (string) $email->get_option($option, $default);
            }

            return $default;
        } catch (\Throwable $error) {
            return '';
        }
    }

    /**
     * Extract readable text from HTML nodes, excluding PHP blocks and known dynamic fragments.
     *
     * @param array  $strings  Current strings.
     * @param string $content  Template HTML/PHP content.
     * @return void
     */
    private function add_html_text_strings(&$strings, $content)
    {
        if (empty($content)) {
            return;
        }

        $content = $this->strip_php_blocks($content);
        $content = $this->strip_dynamic_fragments($content);

        if (empty($content)) {
            return;
        }

        if (preg_match_all('~<p[^>]*>(.*?)</p>~is', $content, $matches)) {
            foreach ($matches[1] as $fragment) {
                $text = $this->html_to_plain($fragment);
                $this->add_string($strings, $text, 'html_text');
            }
        }

        if (preg_match_all('~<(?:td|th|span|div)[^>]*>(.*?)</(?:td|th|span|div)>~is', $content, $matches)) {
            foreach ($matches[1] as $fragment) {
                $text = $this->html_to_plain($fragment);
                $this->add_string($strings, $text, 'html_text');
            }
        }
    }

    /**
     * Remove PHP blocks.
     *
     * @param string $content Content.
     * @return string
     */
    private function strip_php_blocks($content)
    {
        return (string) preg_replace('~<\?(?:php)?[\s\S]*?\?>~i', '', $content);
    }

    /**
     * Remove known dynamic fragments that should not be extracted as static strings.
     *
     * @param string $content Content.
     * @return string
     */
    private function strip_dynamic_fragments($content)
    {
        $content = (string) preg_replace('~\{order_number\}~i', '', $content);
        $content = (string) preg_replace('~\{site_title\}~i', '', $content);
        $content = (string) preg_replace('~\#\{?\d+\}?~', '', $content);

        return $content;
    }

    /**
     * Convert HTML fragment to readable plain text.
     *
     * @param string $fragment HTML.
     * @return string
     */
    private function html_to_plain($fragment)
    {
        $text = (string) $fragment;
        $text = (string) preg_replace('~<br\s*/?>~i', "\n", $text);
        $text = (string) preg_replace('~<[^>]+>~', '', $text);
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("~[\r\n]+~", ' ', $text);
        $text = trim((string) preg_replace('~\s+~', ' ', $text));

        return $text;
    }

    /**
     * Add a string if it is not empty or duplicated.
     *
     * @param array  $strings  Current strings indexed by original text.
     * @param string $text     Original text.
     * @param string $function Origin/function label.
     * @return void
     */
    private function add_string(&$strings, $text, $function)
    {
        $text = trim((string) $text);

        if ('' === $text || isset($strings[$text])) {
            return;
        }

        $strings[$text] = array(
            'text'     => $text,
            'function' => $function,
        );
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
                'id'    => 'woocommerce-blocks',
                'label' => __('WooCommerce bloques email', 'wc-pbm'),
                'path'  => WP_PLUGIN_DIR . '/woocommerce/templates/emails/block/',
            ),
            array(
                'id'    => 'woocommerce-includes',
                'label' => __('WooCommerce clases email', 'wc-pbm'),
                'path'  => WP_PLUGIN_DIR . '/woocommerce/includes/emails/',
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
     * Get WooCommerce email object linked to a template basename.
     *
     * @param string $relative_path Template relative path.
     * @return object|null
     */
    private function get_email_for_template($relative_path)
    {
        if (! function_exists('WC') || ! WC()->mailer()) {
            return null;
        }

        foreach ((array) WC()->mailer()->get_emails() as $email) {
            $html_match = ! empty($email->template_html) && basename($email->template_html) === $relative_path;
            $plain_match = ! empty($email->template_plain) && basename($email->template_plain) === $relative_path;

            if ($html_match || $plain_match) {
                return $email;
            }
        }

        return null;
    }

    /**
     * Get human label for template file.
     *
     * @param string $relative_path Relative file path.
     * @return string
     */
    private function get_template_label($relative_path)
    {
        $email = $this->get_email_for_template($relative_path);

        if ($email && method_exists($email, 'get_title')) {
            return $email->get_title() . ' · ' . $relative_path;
        }

        return ucwords(str_replace(array('-', '.php'), array(' ', ''), $relative_path)) . ' · ' . $relative_path;
    }

    /**
     * Return dynamic email strings from WooCommerce options/filters.
     *
     * @return array
     */
    private function get_dynamic_strings()
    {
        $strings = array();
        $this->add_dynamic_string($strings, 'dynamic:woocommerce_email_footer_text', 'Textos dinámicos WooCommerce', 'Texto del pie de página', 'woocommerce_email_footer_text');

        return $strings;
    }

    /**
     * Add one dynamic string entry.
     *
     * @param array  $strings         Current templates.
     * @param string $id              Template ID.
     * @param string $source_label    Source label.
     * @param string $label           Human label.
     * @param string $option_key      Option key.
     * @return void
     */
    private function add_dynamic_string(&$strings, $id, $source_label, $label, $option_key)
    {
        $strings[$id] = array(
            'id'            => $id,
            'label'         => $label,
            'file'          => '',
            'relative_path' => $option_key,
            'source'        => 'dynamic',
            'source_label'  => $source_label,
            'option_key'    => $option_key,
        );
    }

    /**
     * Add dynamic strings for a virtual template to extraction result.
     *
     * @param array  $strings      Current strings.
     * @param array  $template     Template data.
     * @return void
     */
    private function add_dynamic_strings(&$strings, $template_id)
    {
        $template = $this->get_template($template_id);

        if (! $template || empty($template['option_key'])) {
            return;
        }

        $option_key = (string) $template['option_key'];

        if ('woocommerce_email_footer_text' === $option_key) {
            $value = (string) get_option('woocommerce_email_footer_text', '');
            if ('' !== $value) {
                $this->add_string($strings, $value, 'woocommerce_email_footer_text');
            }
        }

        if ('woocommerce_email_from_name' === $option_key) {
            $value = (string) get_option('woocommerce_email_from_name', '');
            if ('' !== $value) {
                $this->add_string($strings, $value, 'woocommerce_email_from_name');
            }
        }

        if ('woocommerce_email_from_address' === $option_key) {
            $value = (string) get_option('woocommerce_email_from_address', '');
            if ('' !== $value) {
                $this->add_string($strings, $value, 'woocommerce_email_from_address');
            }
        }
    }
}
