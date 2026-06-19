<?php
/**
 * String repository for Email String Editor.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Handles Email String Editor storage and legacy compatibility.
 */
class String_Repository
{
    const OPTION_NAME = 'pbm_email_string_overrides';
    const LEGACY_OPTION_NAME = 'wc_custom_email_strings';
    const DOMAIN = 'woocommerce';
    const HIDDEN_MARKER = '__pbm_hidden__';

    /**
     * Cached data for current request.
     *
     * @var array|null
     */
    private $data = null;

    /**
     * Get normalized data.
     *
     * @return array
     */
    public function get_data()
    {
        if (null !== $this->data) {
            return $this->data;
        }

        $data = get_option(self::OPTION_NAME, array());
        if (! is_array($data)) {
            $data = array();
        }

        if (empty($data['version'])) {
            $data['version'] = 1;
        }

        if (empty($data['strings']) || ! is_array($data['strings'])) {
            $data['strings'] = array();
        }

        $this->data = $data;
        return $this->data;
    }

    /**
     * Save normalized data.
     *
     * @param array $data Data to save.
     * @return void
     */
    private function save_data($data)
    {
        $data['version'] = 1;
        if (empty($data['strings']) || ! is_array($data['strings'])) {
            $data['strings'] = array();
        }

        $this->data = $data;
        update_option(self::OPTION_NAME, $data, false);
    }

    /**
     * Get custom text for language/original.
     *
     * @param string $language      Language code.
     * @param string $original_text Original text.
     * @return string
     */
    public function get_custom_text($language, $original_text)
    {
        $data = $this->get_data();
        $entry = $data['strings'][$language][self::DOMAIN][$original_text] ?? null;

        if (is_array($entry) && ! empty($entry['custom'])) {
            return (string) $entry['custom'];
        }

        $legacy = $this->get_legacy_custom_text($language, $original_text);
        return $legacy;
    }

    /**
     * Get all custom strings for a language.
     *
     * @param string $language Language code.
     * @return array
     */
    public function get_strings_for_language($language)
    {
        $data = $this->get_data();
        $strings = $data['strings'][$language][self::DOMAIN] ?? array();

        if (! is_array($strings)) {
            $strings = array();
        }

        $legacy = $this->get_legacy_strings_for_language($language);
        foreach ($legacy as $original => $entry) {
            if (! isset($strings[$original])) {
                $strings[$original] = $entry;
            }
        }

        return $strings;
    }

    /**
     * Save a list of overrides for a template.
     *
     * @param string $language    Language code.
     * @param string $template_id Template ID.
     * @param array  $strings     Original => custom.
     * @return void
     */
    public function save_template_strings($language, $template_id, $strings)
    {
        $data = $this->get_data();

        if (empty($data['strings'][$language])) {
            $data['strings'][$language] = array();
        }

        if (empty($data['strings'][$language][self::DOMAIN])) {
            $data['strings'][$language][self::DOMAIN] = array();
        }

        foreach ($strings as $original => $custom) {
            $original = sanitize_text_field((string) $original);
            $custom = sanitize_text_field((string) $custom);

            if ('' === $original) {
                continue;
            }

            if ('' === $custom) {
                unset($data['strings'][$language][self::DOMAIN][$original]);
                continue;
            }

            $data['strings'][$language][self::DOMAIN][$original] = array(
                'custom'     => $custom,
                'template'   => sanitize_text_field($template_id),
                'source'     => self::DOMAIN,
                'context'    => 'email',
                'updated_at' => current_time('mysql', true),
            );
        }

        $this->save_data($data);
    }

    /**
     * Delete one override.
     *
     * @param string $language      Language code.
     * @param string $original_text Original text.
     * @return void
     */
    public function delete_string($language, $original_text)
    {
        $data = $this->get_data();
        unset($data['strings'][$language][self::DOMAIN][$original_text]);
        $this->save_data($data);
    }

    /**
     * Check if legacy data exists.
     *
     * @return bool
     */
    public function has_legacy_data()
    {
        $legacy = get_option(self::LEGACY_OPTION_NAME, array());
        return is_array($legacy) && ! empty($legacy);
    }

    /**
     * Get legacy custom text.
     *
     * @param string $language      Language code.
     * @param string $original_text Original text.
     * @return string
     */
    private function get_legacy_custom_text($language, $original_text)
    {
        $legacy = get_option(self::LEGACY_OPTION_NAME, array());
        if (! is_array($legacy) || empty($legacy[$language][$original_text])) {
            return '';
        }

        $entry = $legacy[$language][$original_text];
        if (is_array($entry)) {
            return isset($entry['custom']) ? (string) $entry['custom'] : '';
        }

        return (string) $entry;
    }

    /**
     * Get legacy strings for one language.
     *
     * @param string $language Language code.
     * @return array
     */
    private function get_legacy_strings_for_language($language)
    {
        $legacy = get_option(self::LEGACY_OPTION_NAME, array());
        if (! is_array($legacy) || empty($legacy[$language]) || ! is_array($legacy[$language])) {
            return array();
        }

        $strings = array();
        foreach ($legacy[$language] as $original => $entry) {
            $custom = is_array($entry) ? (string) ($entry['custom'] ?? '') : (string) $entry;
            $template = is_array($entry) ? (string) ($entry['template'] ?? '') : '';

            if ('' === $custom) {
                continue;
            }

            $strings[(string) $original] = array(
                'custom'   => $custom,
                'template' => $template,
                'source'   => 'legacy',
                'context'  => 'email',
            );
        }

        return $strings;
    }
}
