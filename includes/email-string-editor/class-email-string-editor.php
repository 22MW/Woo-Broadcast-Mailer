<?php
/**
 * Email String Editor coordinator.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Coordinates admin and email hooks for the Email String Editor module.
 */
class Email_String_Editor
{
    /**
     * Admin page instance.
     *
     * @var Admin_Page
     */
    private $admin_page;

    /**
     * Gettext filter instance.
     *
     * @var Gettext_Filter
     */
    private $gettext_filter;

    /**
     * AJAX controller instance.
     *
     * @var Ajax_Controller
     */
    private $ajax_controller;

    /**
     * Constructor.
     *
     * @param Admin_Page      $admin_page      Admin page instance.
     * @param Ajax_Controller $ajax_controller AJAX controller instance.
     * @param Gettext_Filter  $gettext_filter  Gettext filter instance.
     */
    public function __construct(Admin_Page $admin_page, Ajax_Controller $ajax_controller, Gettext_Filter $gettext_filter)
    {
        $this->admin_page = $admin_page;
        $this->ajax_controller = $ajax_controller;
        $this->gettext_filter = $gettext_filter;
    }

    /**
     * Register module hooks.
     *
     * @return void
     */
    public function register_hooks()
    {
        add_action('admin_menu', array($this->admin_page, 'register_menu'));
        add_action('admin_post_pbm_save_email_strings', array($this->admin_page, 'save_strings'));
        add_action('admin_post_pbm_update_email_string', array($this->admin_page, 'update_string'));
        add_action('admin_post_pbm_delete_email_string', array($this->admin_page, 'delete_string'));

        add_action('wp_ajax_pbm_email_editor_bootstrap', array($this->ajax_controller, 'bootstrap'));
        add_action('wp_ajax_pbm_email_editor_search_strings', array($this->ajax_controller, 'search_strings'));
        add_action('wp_ajax_pbm_email_editor_save_strings', array($this->ajax_controller, 'save_strings'));
        add_action('wp_ajax_pbm_email_editor_list_changes', array($this->ajax_controller, 'list_changes'));
        add_action('wp_ajax_pbm_email_editor_update_string', array($this->ajax_controller, 'update_string'));
        add_action('wp_ajax_pbm_email_editor_delete_string', array($this->ajax_controller, 'delete_string'));

        add_action('woocommerce_email_header', array($this->gettext_filter, 'start_email_context'), 0, 2);
        add_action('woocommerce_email_footer', array($this->gettext_filter, 'end_email_context'), PHP_INT_MAX, 0);
        add_filter('gettext', array($this->gettext_filter, 'filter_gettext'), 20, 3);
        add_filter('gettext_with_context', array($this->gettext_filter, 'filter_gettext_with_context'), 20, 4);
    }
}
