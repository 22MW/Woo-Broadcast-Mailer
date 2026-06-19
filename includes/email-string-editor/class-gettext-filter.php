<?php
/**
 * Gettext filter for WooCommerce email string overrides.
 *
 * @package WC_Product_Broadcast_Mailer
 */

namespace WC_Product_Broadcast_Mailer\Email_String_Editor;

defined('ABSPATH') || exit;

/**
 * Applies saved string overrides only while WooCommerce renders emails.
 */
class Gettext_Filter
{
    /**
     * Repository.
     *
     * @var String_Repository
     */
    private $repository;

    /**
     * Language resolver.
     *
     * @var Language_Resolver
     */
    private $language_resolver;

    /**
     * Whether WooCommerce email rendering is active.
     *
     * @var bool
     */
    private $email_context = false;

    /**
     * Current email language.
     *
     * @var string
     */
    private $email_language = '';

    /**
     * Constructor.
     *
     * @param String_Repository $repository        Repository.
     * @param Language_Resolver $language_resolver Language resolver.
     */
    public function __construct(String_Repository $repository, Language_Resolver $language_resolver)
    {
        $this->repository = $repository;
        $this->language_resolver = $language_resolver;
    }

    /**
     * Mark WooCommerce email rendering as active.
     *
     * @param string $email_heading Email heading.
     * @param mixed  $email         WooCommerce email object.
     * @return void
     */
    public function start_email_context($email_heading = '', $email = null)
    {
        $this->email_context = true;
        $this->email_language = $this->resolve_email_language($email);
    }

    /**
     * Mark WooCommerce email rendering as finished.
     *
     * @return void
     */
    public function end_email_context()
    {
        $this->email_context = false;
        $this->email_language = '';
    }

    /**
     * Filter translated WooCommerce strings.
     *
     * @param string $translation Translated text.
     * @param string $text        Original text.
     * @param string $domain      Text domain.
     * @return string
     */
    public function filter_gettext($translation, $text, $domain)
    {
        return $this->maybe_override_text($translation, $text, $domain);
    }

    /**
     * Filter translated WooCommerce strings with context.
     *
     * @param string $translation Translated text.
     * @param string $text        Original text.
     * @param string $context     Translation context.
     * @param string $domain      Text domain.
     * @return string
     */
    public function filter_gettext_with_context($translation, $text, $context, $domain)
    {
        return $this->maybe_override_text($translation, $text, $domain);
    }

    /**
     * Filter dynamic WooCommerce email headings and subjects.
     *
     * @param string $text   Heading or subject text.
     * @param mixed  $object Related WooCommerce object.
     * @param mixed  $email  WooCommerce email object.
     * @return string
     */
    public function filter_email_heading_or_subject($text, $object = null, $email = null)
    {
        if ('' === $text) {
            return $text;
        }

        $language = $this->resolve_email_language($email);
        $custom = $this->repository->get_custom_text($language, $text);

        if (String_Repository::HIDDEN_MARKER === $custom) {
            return '';
        }

        return '' !== $custom ? $custom : $text;
    }

    /**
     * Filter dynamic WooCommerce email text options.
     *
     * @param string $text  Option value.
     * @param mixed  $email WooCommerce email object.
     * @return string
     */
    public function filter_dynamic_email_text($text, $email = null)
    {
        if ('' === $text) {
            return $text;
        }

        $language = $this->resolve_email_language($email);
        $custom = $this->repository->get_custom_text($language, $text);

        if (String_Repository::HIDDEN_MARKER === $custom) {
            return '';
        }

        return '' !== $custom ? $custom : $text;
    }

    /**
     * Filter WooCommerce additional email content.
     *
     * @param string $content Additional content.
     * @param mixed  $object  Related object.
     * @param mixed  $email   WooCommerce email object.
     * @return string
     */
    public function filter_email_additional_content($content, $object = null, $email = null)
    {
        if ('' === $content) {
            return $content;
        }

        $language = $this->resolve_email_language($email);
        $custom = $this->repository->get_custom_text($language, $content);

        if (String_Repository::HIDDEN_MARKER === $custom) {
            return '';
        }

        if ('' !== $custom) {
            return $custom;
        }

        return $this->maybe_override_store_email_content($language, $content);
    }

    /**
     * Apply overrides saved against WooCommerce store-email placeholders.
     *
     * @param string $language Language code.
     * @param string $content  Rendered content.
     * @return string
     */
    private function maybe_override_store_email_content($language, $content)
    {
        if (false === strpos($content, 'Thanks again! If you need any help with your order, please contact us at ')) {
            return $content;
        }

        $store_email = $this->extract_store_email_from_content($content);
        $originals = array(
            'Thanks again! If you need any help with your order, please contact us at {store_email}.',
            'Thanks again! If you need any help with your order, please contact us at %s.',
        );

        foreach ($originals as $original) {
            $custom = $this->repository->get_custom_text($language, $original);

            if (String_Repository::HIDDEN_MARKER === $custom) {
                return '';
            }

            if ('' === $custom) {
                continue;
            }

            if (false !== strpos($custom, '{store_email}')) {
                return str_replace('{store_email}', $store_email, $custom);
            }

            if (false !== strpos($custom, '%s')) {
                return sprintf($custom, $store_email);
            }

            return $custom;
        }

        return $content;
    }

    /**
     * Extract store email from rendered additional content.
     *
     * @param string $content Rendered content.
     * @return string
     */
    private function extract_store_email_from_content($content)
    {
        if (preg_match('/at\s+([^\s<>]+@[^\s<>\.]+(?:\.[^\s<>\.]+)+)\.?$/', $content, $matches)) {
            return sanitize_email((string) $matches[1]);
        }

        return sanitize_email((string) get_option('woocommerce_email_from_address', get_option('admin_email')));
    }

    /**
     * Apply override if the current gettext call is safe.
     *
     * @param string $translation Translated text.
     * @param string $text        Original text.
     * @param string $domain      Text domain.
     * @return string
     */
    private function maybe_override_text($translation, $text, $domain)
    {
        if (! $this->email_context || String_Repository::DOMAIN !== $domain || '' === $text) {
            return $translation;
        }

        $language = $this->email_language ?: $this->language_resolver->get_current_language();
        $custom = $this->repository->get_custom_text($language, $text);

        if (String_Repository::HIDDEN_MARKER === $custom) {
            return '';
        }

        return '' !== $custom ? $custom : $translation;
    }

    /**
     * Resolve language for current WooCommerce email.
     *
     * @param mixed $email WooCommerce email object.
     * @return string
     */
    private function resolve_email_language($email)
    {
        $candidates = array();
        $order = $this->get_email_order($email);

        if ($order) {
            $order_language = (string) $order->get_meta('wpml_language', true);
            if ('' !== $order_language) {
                $candidates[] = $order_language;
            }
        }

        if (function_exists('determine_locale')) {
            $candidates[] = determine_locale();
        }

        $candidates[] = get_locale();

        return $this->match_available_language($candidates);
    }

    /**
     * Get order object from WooCommerce email object.
     *
     * @param mixed $email WooCommerce email object.
     * @return \WC_Order|null
     */
    private function get_email_order($email)
    {
        if (! is_object($email) || empty($email->object)) {
            return null;
        }

        return $email->object instanceof \WC_Order ? $email->object : null;
    }

    /**
     * Match candidate language codes against available editor languages.
     *
     * @param array $candidates Candidate language codes.
     * @return string
     */
    private function match_available_language($candidates)
    {
        $available = array_keys($this->language_resolver->get_available_languages());
        $available_map = array_fill_keys($available, true);

        foreach ($candidates as $candidate) {
            $candidate = sanitize_text_field((string) $candidate);
            if ('' === $candidate) {
                continue;
            }

            if (isset($available_map[$candidate])) {
                return $candidate;
            }

            $matched = $this->match_language_prefix($candidate, $available);
            if ('' !== $matched) {
                return $matched;
            }
        }

        return $this->language_resolver->get_current_language();
    }

    /**
     * Match short language code with locale variants.
     *
     * @param string $candidate Candidate language code.
     * @param array  $available Available language codes.
     * @return string
     */
    private function match_language_prefix($candidate, $available)
    {
        $prefix = strtolower(str_replace('-', '_', $candidate));
        $prefix = explode('_', $prefix)[0] ?? '';

        if ('' === $prefix) {
            return '';
        }

        foreach ($available as $language) {
            $language_prefix = strtolower(str_replace('-', '_', $language));
            if (0 === strpos($language_prefix, $prefix . '_') || $language_prefix === $prefix) {
                return $language;
            }
        }

        return '';
    }
}
