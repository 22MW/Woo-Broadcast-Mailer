<?php
/**
 * AJAX controller for Email String Editor.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Handles Email String Editor AJAX requests.
 */
class Ajax_Controller
{
    /** @var Template_Scanner */
    private $scanner;

    /** @var String_Repository */
    private $repository;

    /** @var Language_Resolver */
    private $language_resolver;

    /**
     * Constructor.
     *
     * @param Template_Scanner  $scanner           Template scanner.
     * @param String_Repository $repository        String repository.
     * @param Language_Resolver $language_resolver Language resolver.
     */
    public function __construct(Template_Scanner $scanner, String_Repository $repository, Language_Resolver $language_resolver)
    {
        $this->scanner = $scanner;
        $this->repository = $repository;
        $this->language_resolver = $language_resolver;
    }

    /**
     * Return initial editor data.
     *
     * @return void
     */
    public function bootstrap()
    {
        $this->verify_request();

        wp_send_json_success(array(
            'templates'     => array_values(array_map(array($this, 'format_template'), $this->scanner->get_templates())),
            'languages'     => $this->format_languages($this->language_resolver->get_available_languages()),
            'hasLegacyData' => $this->repository->has_legacy_data(),
        ));
    }

    /**
     * Search editable strings.
     *
     * @return void
     */
    public function search_strings()
    {
        $this->verify_request();

        $template_id = isset($_POST['template']) ? sanitize_text_field(wp_unslash($_POST['template'])) : '';
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $templates = $this->scanner->get_templates();
        $selected_templates = array();

        if ('' !== $template_id) {
            $template = $this->scanner->get_template($template_id);
            if (! $template) {
                wp_send_json_error(array('message' => __('Plantilla no válida.', 'wc-pbm')), 400);
            }
            $selected_templates[$template_id] = $template;
        } elseif ('' !== $search) {
            $selected_templates = $templates;
        } else {
            wp_send_json_success(array('items' => array()));
        }

        $items = array();
        foreach ($selected_templates as $id => $template) {
            foreach ($this->scanner->extract_strings($id) as $string) {
                $original = (string) ($string['text'] ?? '');
                if ('' === $original || ('' !== $search && ! $this->string_matches_search($original, $search))) {
                    continue;
                }

                $items[] = $this->format_search_item($template, $original, (string) ($string['function'] ?? ''));
            }
        }

        wp_send_json_success(array('items' => $items));
    }

    /**
     * Save a batch of custom strings.
     *
     * @return void
     */
    public function save_strings()
    {
        $this->verify_request();

        $items = isset($_POST['items']) ? json_decode(wp_unslash((string) $_POST['items']), true) : array();
        if (! is_array($items)) {
            wp_send_json_error(array('message' => __('Datos no válidos.', 'wc-pbm')), 400);
        }

        $languages = $this->language_resolver->get_available_languages();
        $grouped = array();

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $template_id = isset($item['template']) ? sanitize_text_field((string) $item['template']) : '';
            $original = isset($item['original']) ? sanitize_text_field((string) $item['original']) : '';
            $custom = isset($item['custom']) && is_array($item['custom']) ? $item['custom'] : array();

            if ('' === $original || ! $this->scanner->get_template($template_id)) {
                continue;
            }

            foreach ($custom as $language => $value) {
                $language = sanitize_text_field((string) $language);
                if (! isset($languages[$language])) {
                    continue;
                }
                $grouped[$language][$template_id][$original] = sanitize_text_field((string) $value);
            }
        }

        foreach ($grouped as $language => $templates_data) {
            foreach ($templates_data as $template_id => $strings) {
                $this->repository->save_template_strings($language, $template_id, $strings);
            }
        }

        wp_send_json_success(array('message' => __('Cambios guardados.', 'wc-pbm')));
    }

    /**
     * List saved changes.
     *
     * @return void
     */
    public function list_changes()
    {
        $this->verify_request();

        $changes = array();
        foreach ($this->language_resolver->get_available_languages() as $language => $label) {
            foreach ($this->repository->get_strings_for_language($language) as $original => $entry) {
                $entry = is_array($entry) ? $entry : array('custom' => (string) $entry);
                $changes[] = array(
                    'language'      => $language,
                    'languageLabel' => $label,
                    'original'      => (string) $original,
                    'custom'        => (string) ($entry['custom'] ?? ''),
                    'template'      => (string) ($entry['template'] ?? ''),
                    'source'        => (string) ($entry['source'] ?? 'own'),
                );
            }
        }

        wp_send_json_success(array('changes' => $changes));
    }

    /**
     * Update one saved string.
     *
     * @return void
     */
    public function update_string()
    {
        $this->verify_request();

        $language = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : '';
        $template_id = isset($_POST['template']) ? sanitize_text_field(wp_unslash($_POST['template'])) : '';
        $original = isset($_POST['original']) ? sanitize_text_field(wp_unslash($_POST['original'])) : '';
        $custom = isset($_POST['custom']) ? sanitize_text_field(wp_unslash($_POST['custom'])) : '';

        if (! $this->is_valid_language($language) || '' === $original) {
            wp_send_json_error(array('message' => __('Datos no válidos.', 'wc-pbm')), 400);
        }

        if ('' !== $template_id && ! $this->scanner->get_template($template_id)) {
            wp_send_json_error(array('message' => __('Plantilla no válida.', 'wc-pbm')), 400);
        }

        $this->repository->save_template_strings($language, $template_id, array($original => $custom));
        wp_send_json_success(array('message' => __('Cambio guardado.', 'wc-pbm')));
    }

    /**
     * Delete one own override.
     *
     * @return void
     */
    public function delete_string()
    {
        $this->verify_request();

        $language = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : '';
        $original = isset($_POST['original']) ? sanitize_text_field(wp_unslash($_POST['original'])) : '';

        if (! $this->is_valid_language($language) || '' === $original) {
            wp_send_json_error(array('message' => __('Datos no válidos.', 'wc-pbm')), 400);
        }

        $this->repository->delete_string($language, $original);
        wp_send_json_success(array('message' => __('Personalización eliminada.', 'wc-pbm')));
    }

    /**
     * Validate nonce and capability.
     *
     * @return void
     */
    private function verify_request()
    {
        check_ajax_referer('pbm_email_editor_action', 'nonce');

        if (! current_user_can('manage_woocommerce')) {
            wp_send_json_error(array('message' => __('Permisos insuficientes.', 'wc-pbm')), 403);
        }
    }

    /**
     * Format template data without absolute paths.
     *
     * @param array $template Template data.
     * @return array
     */
    private function format_template($template)
    {
        return array(
            'id'           => (string) ($template['id'] ?? ''),
            'label'        => (string) ($template['label'] ?? ''),
            'sourceLabel'  => (string) ($template['source_label'] ?? ''),
            'relativePath' => (string) ($template['relative_path'] ?? ''),
        );
    }

    /**
     * Format language map for React.
     *
     * @param array $languages Languages.
     * @return array
     */
    private function format_languages($languages)
    {
        $items = array();
        foreach ($languages as $code => $label) {
            $items[] = array(
                'code'  => (string) $code,
                'label' => (string) $label,
            );
        }
        return $items;
    }

    /**
     * Format one search result.
     *
     * @param array  $template Template data.
     * @param string $original Original string.
     * @param string $function Function name.
     * @return array
     */
    private function format_search_item($template, $original, $function)
    {
        $translations = array();
        $custom = array();

        foreach ($this->language_resolver->get_available_languages() as $language => $label) {
            $translations[$language] = $this->get_translation_for_language($original, $language);
            $custom[$language] = $this->repository->get_custom_text($language, $original);
        }

        return array(
            'templateId'    => (string) ($template['id'] ?? ''),
            'templateLabel' => (string) ($template['label'] ?? ''),
            'sourceLabel'   => (string) ($template['source_label'] ?? ''),
            'relativePath'  => (string) ($template['relative_path'] ?? ''),
            'original'      => $original,
            'function'      => $function,
            'translations'  => $translations,
            'custom'        => $custom,
        );
    }

    /**
     * Check if original, translations or custom texts match search.
     *
     * @param string $original Original string.
     * @param string $search   Search term.
     * @return bool
     */
    private function string_matches_search($original, $search)
    {
        if (false !== stripos($original, $search)) {
            return true;
        }

        foreach ($this->language_resolver->get_available_languages() as $language => $label) {
            if (false !== stripos($this->get_translation_for_language($original, $language), $search)) {
                return true;
            }

            if (false !== stripos($this->repository->get_custom_text($language, $original), $search)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return translation for a language.
     *
     * @param string $original Original text.
     * @param string $language Language code.
     * @return string
     */
    private function get_translation_for_language($original, $language)
    {
        if (function_exists('switch_to_locale') && function_exists('restore_previous_locale')) {
            switch_to_locale($language);
            $translation = translate($original, String_Repository::DOMAIN);
            restore_previous_locale();
            return $translation;
        }

        return translate($original, String_Repository::DOMAIN);
    }

    /**
     * Check valid language.
     *
     * @param string $language Language code.
     * @return bool
     */
    private function is_valid_language($language)
    {
        $languages = $this->language_resolver->get_available_languages();
        return isset($languages[$language]);
    }
}
