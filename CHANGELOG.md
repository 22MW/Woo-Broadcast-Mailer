# Changelog

All notable changes to this project will be documented in this file.

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
