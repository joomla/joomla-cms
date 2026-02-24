# PR Notes — Ajaxified Backend for com_content

## Title

Ajaxified Backend (com_content): async list/actions, autosave policy + recover, custom-field filtering, and shared async admin hooks

## Branch

`feature/ajaxified-backend-livewire-like`

## Summary

This PR delivers the planned Ajaxified Backend feature stream in incremental commits with fallback-safe behavior.

Delivered capabilities:

- Async list actions for `com_content` articles (state actions, transitions, filters/search/order/pagination).
- Async request/response contract and reusable server response envelope.
- Autosave scheduler with dirty-state tracking.
- Autosave endpoint contract with status metadata.
- Versioning-safe autosave policy (`unchanged` skip + interval throttling).
- Recover/undo last autosave snapshot (MVP).
- Custom-field list filter controls and query integration.
- Shared admin async refresh helper extraction.
- Accessibility hardening for async refresh (focus + aria-live announcements).
- Final usage/QA/contract docs.

## Commit Stream (high level)

- `feat(async-admin): ...`
- `feat(autosave): ...`
- `feat(filters): ...`
- `refactor(async-admin): ...`
- `docs(async-admin): ...`

Recent tail includes:

- `d79315877d` — apply custom field filters in query
- `945da08bb0` — extract reusable admin async refresh hooks
- `3c3128449c` — add a11y focus and live-message refresh handling
- `42590e894f` — usage, QA, reviewer guide
- `e2f83e5cac` — align contract with final scope

## Validation Performed

- `npm run lint:js` (passes; existing unrelated warnings only)
- `npm run lint:testjs` (passes)
- `php -l administrator/components/com_content/src/Controller/ArticleController.php` (passes)
- `php -l administrator/components/com_content/src/Model/ArticlesModel.php` (passes)

## Docs Updated

- `docs/development/ajaxified-backend-plan.md`
- `docs/development/ajaxified-backend-contract.md`
- `docs/development/ajaxified-backend-qa.md`

## Reviewer Checklist

- Verify feature-flag OFF path preserves synchronous behavior.
- Verify feature-flag ON path for async list interactions.
- Verify autosave status, skip policy, and recover/undo behavior.
- Verify custom-field filter controls and result filtering.
- Verify focus behavior and aria-live announcements after async refresh.

## Notes

Unrelated local changes not part of this PR:

- `package-lock.json`
- `plugins/editors/tinymce/tinymce.xml`
