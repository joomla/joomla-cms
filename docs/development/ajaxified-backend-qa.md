# Ajaxified Backend — Usage and QA Notes

## Scope

This document covers the incremental async backend pilot delivered for `com_content` in administrator pages:

- Async list interactions (state actions, transitions, filters, pagination).
- Autosave with dirty-state scheduling and async response contract.
- Autosave anti-spam policy (unchanged payload skip + interval throttling).
- Custom-field filter controls and query integration.

## Feature Flags

All flags are currently provided through `com_content` options.

- `async_admin_enabled`
  - Enables async list behavior for supported `com_content` list actions.
  - Disabled path keeps classic full-submit behavior.
- `autosave_enabled`
  - Enables periodic async autosave on article edit form.
- `autosave_interval`
  - Autosave interval in seconds.
  - Also used as minimum autosave write interval for anti-spam throttling.

## Async Contracts

### Server Envelope

Async endpoints return data in the normalized envelope shape:

- `success` (boolean)
- `messages` (object keyed by message type)
- `redirect` (string|null)
- `fragments` (object)
- `meta` (object)

### Autosave Meta

Autosave responses include `meta.autosave` and `meta.autosaveAt`.

Skip scenarios include:

- `meta.skipped = true`
- `meta.reason = "unchanged" | "throttled"`
- `meta.retryAfter` for throttled responses

## Recover/Undo Snapshot

The article edit autosave UI provides explicit controls:

- Recover last autosave snapshot.
- Undo recover.

The control is local to the active edit session and designed as an MVP safety net.

## Reusable Core Hooks

Shared helpers in system core script:

- `Joomla.asyncAdminRequest(...)`
- `Joomla.refreshAdminFragment(...)`
- `Joomla.announceAsyncMessages(...)`

These are intended as extension points for additional administrator components.

## Accessibility Behavior

After async fragment updates:

- Messages are announced via aria-live region.
- Focus attempts to return to the previously active element by id.
- If not possible, focus moves to the refreshed container.

## Recommended QA Flow

1. Enable/disable flags and verify both async and fallback paths.
2. Validate list actions:
   - Publish/unpublish/archive/trash/feature.
   - Workflow transitions.
   - Search/filter/pagination.
3. Validate autosave lifecycle:
   - Dirty detection and periodic save.
   - Skip on unchanged and skip on throttling.
   - Recover and undo snapshot.
4. Validate custom-field filtering:
   - Field control visibility.
   - Query filtering by selected field/value.
5. Validate accessibility:
   - Message announcement.
   - Focus persistence after async refresh.

## Targeted Commands

- `npm run lint:js`
- `npm run lint:testjs`
- `php -l administrator/components/com_content/src/Controller/ArticleController.php`
- `php -l administrator/components/com_content/src/Model/ArticlesModel.php`

Primary system specs:

- `tests/System/integration/administrator/components/com_content/Articles.cy.js`
- `tests/System/integration/administrator/components/com_content/Article.cy.js`

## Known Local Workspace Noise

The following local modifications are unrelated to this feature stream and can remain out-of-scope for commits:

- `package-lock.json`
- `plugins/editors/tinymce/tinymce.xml`
