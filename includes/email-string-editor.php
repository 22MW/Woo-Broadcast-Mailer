<?php
/**
 * Email String Editor module loader.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

require_once __DIR__ . '/email-string-editor/class-template-scanner.php';
require_once __DIR__ . '/email-string-editor/class-string-repository.php';
require_once __DIR__ . '/email-string-editor/class-language-resolver.php';
require_once __DIR__ . '/email-string-editor/class-admin-page.php';
require_once __DIR__ . '/email-string-editor/class-email-string-editor.php';

/**
 * Bootstrap the Email String Editor module.
 *
 * @return void
 */
function bootstrap()
{
    $scanner = new Template_Scanner();
    $repository = new String_Repository();
    $language_resolver = new Language_Resolver();
    $admin_page = new Admin_Page($scanner, $repository, $language_resolver);

    $module = new Email_String_Editor($admin_page);
    $module->register_hooks();
}
