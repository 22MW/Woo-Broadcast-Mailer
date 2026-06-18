<?php
/**
 * Language resolver for Email String Editor.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Resolves available and selected languages.
 */
class Language_Resolver
{
    /**
     * Return current locale.
     *
     * @return string
     */
    public function get_current_language()
    {
        return get_locale();
    }

    /**
     * Return available languages with labels.
     *
     * @return array<string,string>
     */
    public function get_available_languages()
    {
        $languages = array();
        $current = $this->get_current_language();
        $labels = array(
            'es_ES' => __('Español', 'wc-pbm'),
            'ca'    => __('Català', 'wc-pbm'),
            'ca_ES' => __('Català', 'wc-pbm'),
            'en_US' => __('English', 'wc-pbm'),
            'fr_FR' => __('Français', 'wc-pbm'),
            'de_DE' => __('Deutsch', 'wc-pbm'),
            'it_IT' => __('Italiano', 'wc-pbm'),
            'pt_PT' => __('Português', 'wc-pbm'),
        );

        $languages[$current] = $labels[$current] ?? $current;

        foreach ((array) get_available_languages() as $language) {
            if (! isset($languages[$language])) {
                $languages[$language] = $labels[$language] ?? $language;
            }
        }

        return $languages;
    }

    /**
     * Resolve selected language from request.
     *
     * @return string
     */
    public function get_selected_language()
    {
        $requested = isset($_REQUEST['language']) ? sanitize_text_field(wp_unslash($_REQUEST['language'])) : '';
        $languages = $this->get_available_languages();

        if ($requested && isset($languages[$requested])) {
            return $requested;
        }

        return $this->get_current_language();
    }
}
