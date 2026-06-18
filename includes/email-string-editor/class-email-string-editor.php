<?php
/**
 * Email String Editor coordinator.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Coordinates admin hooks for the Email String Editor module.
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
     * Constructor.
     *
     * @param Admin_Page $admin_page Admin page instance.
     */
    public function __construct(Admin_Page $admin_page)
    {
        $this->admin_page = $admin_page;
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
    }
}
