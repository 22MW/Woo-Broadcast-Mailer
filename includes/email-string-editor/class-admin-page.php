<?php
/**
 * Admin page for Email String Editor.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Renders and processes the Email String Editor admin page.
 */
class Admin_Page
{
    const MENU_SLUG = 'pbm-email-string-editor';

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
     * Register WooCommerce submenu.
     *
     * @return void
     */
    public function register_menu()
    {
        add_submenu_page(
            'woocommerce',
            __('Editor de emails WooCommerce', 'wc-pbm'),
            __('Editor de emails', 'wc-pbm'),
            'manage_woocommerce',
            self::MENU_SLUG,
            array($this, 'render')
        );
    }

    /**
     * Render admin page.
     *
     * @return void
     */
    public function render()
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Permisos insuficientes.', 'wc-pbm'));
        }

        $this->enqueue_assets();
        ?>
        <div class="wrap pbm-email-string-editor">
            <h1><?php esc_html_e('Editor de emails WooCommerce', 'wc-pbm'); ?></h1>
            <p><?php esc_html_e('Busca strings en plantillas de emails WooCommerce y ajusta sus textos por idioma.', 'wc-pbm'); ?></p>
            <div id="pbm-email-string-editor-app"></div>
        </div>
        <?php
    }

    /**
     * Enqueue React assets for the Email String Editor page.
     *
     * @return void
     */
    private function enqueue_assets()
    {
        $plugin_file = dirname(__DIR__, 2) . '/woo-broadcast-mailer.php';
        $asset_file = dirname(__DIR__, 2) . '/build/index.asset.php';
        $asset_data = file_exists($asset_file) ? require $asset_file : array();
        $dependencies = $asset_data['dependencies'] ?? array('wp-element', 'wp-components', 'wp-i18n');
        $version = $asset_data['version'] ?? '2.0.1.6';

        wp_enqueue_script(
            'pbm-admin-react',
            plugin_dir_url($plugin_file) . 'build/index.js',
            $dependencies,
            $version,
            true
        );

        wp_localize_script(
            'pbm-admin-react',
            'pbmEmailEditor',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('pbm_email_editor_action'),
            )
        );

        $style_file = dirname(__DIR__, 2) . '/build/index.css';
        if (file_exists($style_file)) {
            wp_enqueue_style(
                'pbm-admin-react',
                plugin_dir_url($plugin_file) . 'build/index.css',
                array(),
                $version
            );
        }
    }

    /**
     * Save strings from admin-post.
     *
     * @return void
     */
    public function save_strings()
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Permisos insuficientes.', 'wc-pbm'));
        }

        check_admin_referer('pbm_save_email_strings', 'pbm_email_strings_nonce');

        $templates = isset($_POST['templates']) && is_array($_POST['templates']) ? wp_unslash($_POST['templates']) : array();
        $originals = isset($_POST['original_strings']) && is_array($_POST['original_strings']) ? wp_unslash($_POST['original_strings']) : array();
        $customs = isset($_POST['custom_strings']) && is_array($_POST['custom_strings']) ? wp_unslash($_POST['custom_strings']) : array();
        $allowed_languages = $this->language_resolver->get_available_languages();
        $grouped = array();

        foreach ($originals as $index => $original) {
            $template_id = isset($templates[$index]) ? sanitize_text_field((string) $templates[$index]) : '';
            if (! $this->scanner->get_template($template_id)) {
                continue;
            }

            foreach ($allowed_languages as $language => $label) {
                $custom = isset($customs[$language][$index]) ? (string) $customs[$language][$index] : '';
                $grouped[$language][$template_id][(string) $original] = $custom;
            }
        }

        foreach ($grouped as $language => $templates_data) {
            foreach ($templates_data as $template_id => $strings) {
                $this->repository->save_template_strings($language, $template_id, $strings);
            }
        }

        wp_safe_redirect($this->get_page_url(array(
            'tab'     => 'editor',
            'updated' => '1',
        )));
        exit;
    }

    /**
     * Update one saved string from admin-post.
     *
     * @return void
     */
    public function update_string()
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Permisos insuficientes.', 'wc-pbm'));
        }

        check_admin_referer('pbm_update_email_string', 'pbm_update_email_string_nonce');

        $language = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : '';
        $template_id = isset($_POST['template']) ? sanitize_text_field(wp_unslash($_POST['template'])) : '';
        $original = isset($_POST['original_text']) ? sanitize_text_field(wp_unslash($_POST['original_text'])) : '';
        $custom = isset($_POST['custom_text']) ? sanitize_text_field(wp_unslash($_POST['custom_text'])) : '';

        if ($this->is_valid_language($language) && '' !== $original) {
            $this->repository->save_template_strings($language, $template_id, array($original => $custom));
        }

        wp_safe_redirect($this->get_page_url(array(
            'tab'     => 'changes',
            'updated' => '1',
        )));
        exit;
    }

    /**
     * Delete string from admin-post.
     *
     * @return void
     */
    public function delete_string()
    {
        if (! current_user_can('manage_woocommerce')) {
            wp_die(esc_html__('Permisos insuficientes.', 'wc-pbm'));
        }

        check_admin_referer('pbm_delete_email_string', 'pbm_delete_email_string_nonce');

        $language = isset($_POST['language']) ? sanitize_text_field(wp_unslash($_POST['language'])) : '';
        $original = isset($_POST['original_text']) ? sanitize_text_field(wp_unslash($_POST['original_text'])) : '';

        if ($this->is_valid_language($language) && '' !== $original) {
            $this->repository->delete_string($language, $original);
        }

        wp_safe_redirect($this->get_page_url(array(
            'tab'     => 'changes',
            'deleted' => '1',
        )));
        exit;
    }

    /**
     * Build editor results.
     *
     * @param string $template_id Template ID.
     * @param string $search      Search term.
     * @param array  $templates   Templates.
     * @return array
     */
    private function get_editor_results($template_id, $search, $templates)
    {
        $results = array();
        $selected_templates = array();

        if ($template_id && isset($templates[$template_id])) {
            $selected_templates[$template_id] = $templates[$template_id];
        } elseif ('' !== $search) {
            $selected_templates = $templates;
        } else {
            return array();
        }

        foreach ($selected_templates as $id => $template) {
            foreach ($this->scanner->extract_strings($id) as $string) {
                if ('' !== $search && ! $this->string_matches_search((string) $string['text'], $search)) {
                    continue;
                }

                $results[] = array(
                    'template' => $template,
                    'text'     => (string) $string['text'],
                    'function' => (string) $string['function'],
                );
            }
        }

        return $results;
    }

    /**
     * Check if original, translations or saved custom texts match search.
     *
     * @param string $original Original text.
     * @param string $search   Search term.
     * @return bool
     */
    private function string_matches_search($original, $search)
    {
        if (false !== stripos($original, $search)) {
            return true;
        }

        foreach ($this->language_resolver->get_available_languages() as $language => $label) {
            $translation = $this->get_translation_for_language($original, $language);
            $custom = $this->repository->get_custom_text($language, $original);

            if (false !== stripos($translation, $search) || false !== stripos($custom, $search)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render notices.
     *
     * @return void
     */
    private function render_notices()
    {
        if (! empty($_GET['updated'])) {
            echo '<div class="notice notice-success"><p>' . esc_html__('Cambios guardados.', 'wc-pbm') . '</p></div>';
        }
        if (! empty($_GET['deleted'])) {
            echo '<div class="notice notice-success"><p>' . esc_html__('Personalización eliminada.', 'wc-pbm') . '</p></div>';
        }
        if (! empty($_GET['pbm_error'])) {
            echo '<div class="notice notice-error"><p>' . esc_html__('No se pudo procesar la solicitud.', 'wc-pbm') . '</p></div>';
        }
    }

    /**
     * Render editor filters.
     *
     * @param array  $templates   Templates.
     * @param string $template_id Template ID.
     * @param string $search      Search term.
     * @return void
     */
    private function render_editor_filters($templates, $template_id, $search)
    {
        ?>
        <form method="get" action="<?php echo esc_url(admin_url('admin.php')); ?>">
            <input type="hidden" name="page" value="<?php echo esc_attr(self::MENU_SLUG); ?>">
            <input type="hidden" name="tab" value="editor">
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="pbm-email-string-template"><?php esc_html_e('Email / plantilla', 'wc-pbm'); ?></label></th>
                    <td>
                        <select id="pbm-email-string-template" name="template">
                            <option value=""><?php esc_html_e('Todas las plantillas al buscar', 'wc-pbm'); ?></option>
                            <?php foreach ($templates as $template) : ?>
                                <option value="<?php echo esc_attr($template['id']); ?>" <?php selected($template_id, $template['id']); ?>>
                                    <?php echo esc_html($template['source_label'] . ' · ' . $template['label']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pbm-email-string-search"><?php esc_html_e('Buscar en todos los strings', 'wc-pbm'); ?></label></th>
                    <td>
                        <input id="pbm-email-string-search" type="search" name="s" value="<?php echo esc_attr($search); ?>" class="regular-text">
                        <p class="description"><?php esc_html_e('Si no eliges plantilla, la búsqueda recorre todas las plantillas permitidas y compara original, traducciones y personalizaciones guardadas.', 'wc-pbm'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(__('Buscar / cargar strings', 'wc-pbm'), 'secondary'); ?>
        </form>
        <?php
    }

    /**
     * Render editor.
     *
     * @param array  $results   Results.
     * @param array  $languages Languages.
     * @param string $search    Search term.
     * @return void
     */
    private function render_editor($results, $languages, $search)
    {
        if (empty($results)) {
            echo '<p>' . esc_html__('Selecciona una plantilla o busca una palabra para ver strings editables.', 'wc-pbm') . '</p>';
            return;
        }
        ?>
        <?php if ('' !== $search) : ?>
            <h2><?php echo esc_html(sprintf(__('Resultados para: %s', 'wc-pbm'), $search)); ?></h2>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php wp_nonce_field('pbm_save_email_strings', 'pbm_email_strings_nonce'); ?>
            <input type="hidden" name="action" value="pbm_save_email_strings">
            <table class="widefat striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('String / email', 'wc-pbm'); ?></th>
                        <th><?php esc_html_e('Idiomas', 'wc-pbm'); ?></th>
                        <th><?php esc_html_e('Función', 'wc-pbm'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $index => $result) : ?>
                        <?php $original = (string) $result['text']; ?>
                        <tr>
                            <td>
                                <p><strong><?php echo esc_html($result['template']['label']); ?></strong></p>
                                <p><?php echo esc_html($result['template']['source_label'] . ' · ' . $result['template']['relative_path']); ?></p>
                                <code><?php echo esc_html($original); ?></code>
                                <input type="hidden" name="templates[<?php echo esc_attr($index); ?>]" value="<?php echo esc_attr($result['template']['id']); ?>">
                                <input type="hidden" name="original_strings[<?php echo esc_attr($index); ?>]" value="<?php echo esc_attr($original); ?>">
                            </td>
                            <td>
                                <?php foreach ($languages as $language => $label) : ?>
                                    <?php
                                    $custom = $this->repository->get_custom_text($language, $original);
                                    $translation = $this->get_translation_for_language($original, $language);
                                    ?>
                                    <p>
                                        <label>
                                            <strong><?php echo esc_html($label . ' (' . $language . ')'); ?></strong><br>
                                            <span class="description"><?php echo esc_html(sprintf(__('Traducción actual: %s', 'wc-pbm'), $translation)); ?></span><br>
                                            <input type="text" class="large-text" name="custom_strings[<?php echo esc_attr($language); ?>][<?php echo esc_attr($index); ?>]" value="<?php echo esc_attr($custom); ?>" placeholder="<?php echo esc_attr($translation); ?>">
                                        </label>
                                    </p>
                                <?php endforeach; ?>
                            </td>
                            <td><code><?php echo esc_html($result['function']); ?></code></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php submit_button(__('Guardar personalizaciones', 'wc-pbm')); ?>
        </form>
        <?php
    }

    /**
     * Render saved changes.
     *
     * @param array $languages Languages.
     * @return void
     */
    private function render_changes($languages)
    {
        $has_changes = false;
        ?>
        <table class="widefat striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Idioma', 'wc-pbm'); ?></th>
                    <th><?php esc_html_e('Original', 'wc-pbm'); ?></th>
                    <th><?php esc_html_e('Personalizado', 'wc-pbm'); ?></th>
                    <th><?php esc_html_e('Plantilla', 'wc-pbm'); ?></th>
                    <th><?php esc_html_e('Acciones', 'wc-pbm'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($languages as $language => $label) : ?>
                    <?php foreach ($this->repository->get_strings_for_language($language) as $original => $entry) : ?>
                        <?php
                        $has_changes = true;
                        $custom = is_array($entry) ? (string) ($entry['custom'] ?? '') : (string) $entry;
                        $template = is_array($entry) ? (string) ($entry['template'] ?? '') : '';
                        ?>
                        <tr>
                            <td><?php echo esc_html($label . ' (' . $language . ')'); ?></td>
                            <td><code><?php echo esc_html($original); ?></code></td>
                            <td>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <?php wp_nonce_field('pbm_update_email_string', 'pbm_update_email_string_nonce'); ?>
                                    <input type="hidden" name="action" value="pbm_update_email_string">
                                    <input type="hidden" name="language" value="<?php echo esc_attr($language); ?>">
                                    <input type="hidden" name="template" value="<?php echo esc_attr($template); ?>">
                                    <input type="hidden" name="original_text" value="<?php echo esc_attr($original); ?>">
                                    <input type="text" class="regular-text" name="custom_text" value="<?php echo esc_attr($custom); ?>">
                                    <?php submit_button(__('Guardar', 'wc-pbm'), 'secondary small', '', false); ?>
                                </form>
                            </td>
                            <td><?php echo esc_html($template ?: '-'); ?></td>
                            <td>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                                    <?php wp_nonce_field('pbm_delete_email_string', 'pbm_delete_email_string_nonce'); ?>
                                    <input type="hidden" name="action" value="pbm_delete_email_string">
                                    <input type="hidden" name="language" value="<?php echo esc_attr($language); ?>">
                                    <input type="hidden" name="original_text" value="<?php echo esc_attr($original); ?>">
                                    <?php submit_button(__('Borrar', 'wc-pbm'), 'delete small', '', false); ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php if (! $has_changes) : ?>
            <p><?php esc_html_e('No hay cambios guardados todavía.', 'wc-pbm'); ?></p>
        <?php endif; ?>
        <?php
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
            $translation = translate($original, 'woocommerce');
            restore_previous_locale();
            return $translation;
        }

        return translate($original, 'woocommerce');
    }

    /**
     * Get module page URL.
     *
     * @param array $args Extra args.
     * @return string
     */
    private function get_page_url($args = array())
    {
        return add_query_arg(array_merge(array('page' => self::MENU_SLUG), $args), admin_url('admin.php'));
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
