# Changelog

All notable changes to this project will be documented in this file.

## 2.0.1.8
- Adjusted broadcast admin layout for audience source, manual emails, global audience list, preview result, and header shortcut.
- Added direct header link to Email String Editor.
- Updated React build assets.

## 2.0.1.7
- Fixed stale preview detection so scheduling and scheduled datetime changes do not invalidate recipient preview.
- Kept stale preview protection for audience, manual emails, batch size, and emails per hour.
- Added internal 22MW-BACK admin visual plan.

## 2.0.1.6
- Migrated Email String Editor admin UI to React.
- Added AJAX controller for templates, search, save, update, delete, and saved changes.
- Kept existing storage and E4 email override behavior unchanged.
- Updated admin build assets.

## 2.0.1.5
- Added safe Email String Editor override application for WooCommerce emails.
- Limited gettext overrides to WooCommerce email rendering context and `woocommerce` text domain.
- Resolved email language from order `wpml_language` with locale fallback.
- Confirmed E4 QA as OK.

## 2.0.1.4
- Added HPOS fallback for product recipients when WooCommerce order lookup returns no results.
- Product recipient counts now scan order line items if the HPOS lookup path is empty.
- Preserved existing email deduplication and WPML language filtering.

## 2.0.1.3
- Added Email String Editor admin module under WooCommerce.
- Added global template string search across allowed WooCommerce email templates.
- Added multi-language editing from the same screen.
- Added multi-language search across original strings, WooCommerce translations, and saved customizations.
- Added direct editing from the saved changes screen.
- Stored overrides in `pbm_email_string_overrides` with read compatibility for `wc_custom_email_strings`.
- Kept real email override application disabled pending safe WooCommerce email context hook.

## 2.0.1.2
- Completed Plan A hardening before QA/release.
- Kept scheduled deliveries in `running` until batch logs cover expected recipients.
- Preserved scheduled recipient snapshots until completion or deletion.
- Added stale-preview protection in React admin and rebuilt assets.
- Escaped `{customer_name}` in HTML emails and validated recipient email before send.
- Restricted scheduled deletion by ID to completed or cancelled records.
- Excluded `_dev/` from release ZIP workflow.

## 2.0.1.1
- Added Action Scheduler availability checks and admin status notice.
- Blocked send/schedule actions when Action Scheduler is unavailable.
- Added internal `_dev/` Plan A memory and Email String Editor planning notes.

## 2.0.0
- Major admin migration to React for core broadcast workflow.
- Removed legacy selector/preview/send admin block (legacy markup and inline JS cleanup).
- Added global combined audience workflow across sources (product, role, Mail Mint, manual emails).
- Added multi-select dependent selector with AJAX search (3+ chars) and global audience builder.
- Added global audience summary with gross, unique, and duplicate counts.
- React preview flow for unique recipients via unified AJAX endpoint.
- React send flow for instant and scheduled deliveries with client-side validation.
- Kept classic `wp_editor` integrated inside React panel for stable message editing.
- Backend validation hardening for global audiences:
- When `audience_items`/`manual_emails` exists, skip legacy required selector checks.
- Keep legacy selector checks only for non-global audience requests.
- Scheduled/logs management upgraded in React cards:
- Sorting by date/status/subject.
- Page selection + bulk delete by selected IDs.
- Pagination set to 12 cards per page.
- Colored status badges for `pending`, `running`, `completed`, `cancelled`.
- Added total messages per card with historical fallback:
- Prefer stored scheduled recipients.
- Fallback to aggregated sent+failed logs for old records.
- UI tokens and style alignment with 22MW visual system:
- Unified button treatment (solid backgrounds, no borders, full radius).
- Added 22MW brand logo in header linking to `https://22mw.online/`.

## 1.1.0
- Unified broadcast flow for instant and scheduled deliveries in a single form.
- Added recipient source selector architecture: Woo product, WordPress role, and Mail Mint list.
- Added dependent selector behavior by source with recipient preview before send.
- Refactored backend to a single send endpoint that:
- If scheduling is disabled, queues immediate batches.
- If scheduling is enabled, stores schedule metadata and queues execution for selected date/time.
- Implemented extensible recipient source layer:
- `get_recipients_from_product()`
- `get_recipients_from_role()`
- `get_recipients_from_mailmint_list()`
- Integrated Mail Mint (phase 1):
- List loading from `mint_contact_groups` (`type=lists`).
- Subscribed contacts retrieval from relationship tables.
- Preview support for Mail Mint audience.
- Added role-based audience preview and normalized source-specific preview summaries.
- Added compatibility checks for Mail Mint availability, with disabled source and admin warning when unavailable.
- Preserved and reinforced nonce, capability, sanitization, and guarded execution flows.
- Kept lower delivery-management block with scheduled records and logs:
- Pending/completed/cancelled visibility.
- Execute now, cancel, delete, and bulk delete actions.
- Improved admin UI consistency and styling polish (without changing send logic):
- Custom admin stylesheet enqueue for plugin screen.
- Unified visual treatment for form blocks, preview panel, table, modal, and buttons.

## 1.0.9
- Language filtering for recipients using WPML order language.

## 1.0.8.4
- Filter recipients by WPML order language.

## 1.0.8.3
- Revert preview without AJAX.

## 1.0.8.2
- Preview recipients without AJAX to avoid server limits.

## 1.0.8.1
- WPML translated IDs and language label in selector.

## 1.0.8
- Test automatic update flow.

## 1.0.7
- Test release for automatic asset upload.

## 1.0.6
- Use release asset zip when available to preserve folder name.

## 1.0.5
- Use GitHub tag ZIP for updates to avoid API download issues.

## 1.0.4
- Show plugin version in admin title.

## 1.0.3
- GitHub Releases updater (automatic update checks).

## 1.0.2
- Optional uninstall cleanup (controlled by config/option).

## 1.0.1
- Public release preparation: requirements aligned and textdomain loading added.
- Email line breaks preserved with HTML `<br>` conversion on send.
