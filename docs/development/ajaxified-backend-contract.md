# Ajaxified Backend Contract

## Scope

This document defines the technical contract for asynchronous administrator actions in the Ajaxified Backend project.

Completed scope:

- Component: `com_content`
- Views:
  - Articles list (`view=articles`)
  - Article edit (`view=article`)
- Capabilities:
  - Async list state changes, transitions, search/filter/order/pagination.
  - Async autosave scheduler and status contract.
  - Autosave anti-spam policy (unchanged payload skip + minimum interval throttle).
  - Recover/undo last autosave snapshot (session-local MVP).
  - Custom-field filters (UI + list query integration).
  - Reusable core async refresh helper with focus and aria-live handling.

The contract is designed for progressive enhancement and strict backward compatibility.

---

## Backward Compatibility Rules

1. Existing non-Ajax behavior must remain fully functional.
2. Async behavior must be gated by configuration/feature flag.
3. Any async failure must gracefully fall back to standard full-page flow.
4. No permission checks may move from server to client.

---

## Request Detection

An admin action is considered async only when all are true:

- Feature flag is enabled for the scope.
- Request indicates XHR (`X-Requested-With`) or explicit async hint.
- CSRF token validation succeeds.

If any condition is not met, server handles request as standard synchronous flow.

---

## Response Envelope

Async responses must return JSON in a consistent envelope:

```json
{
  "success": true,
  "messages": {
    "message": ["..."],
    "warning": [],
    "error": []
  },
  "redirect": null,
  "fragments": {
    "list": "<html>..."
  },
  "meta": {
    "component": "com_content",
    "view": "articles"
  }
}
```

Notes:

- `redirect` may be populated when server requires navigation.
- `fragments` keys are optional and action-dependent.
- `messages` should mirror Joomla message queues/categories.

Autosave responses may include policy metadata:

```json
{
  "meta": {
    "autosave": true,
    "autosaveAt": "2026-02-24 00:00:00",
    "skipped": true,
    "reason": "unchanged",
    "retryAfter": 10
  }
}
```

`reason` values are currently `unchanged` and `throttled`.

---

## Error Handling Policy

- Validation/permission/token failures return `success=false` with `error` messages.
- Client must render messages and either:
  - apply returned redirect, or
  - fallback to full submit when response is unusable.

No silent failure is allowed.

---

## Security Requirements

- CSRF checks remain mandatory for state-changing requests.
- ACL checks remain server-side only.
- Response fragments must be rendered using existing Joomla escaping rules.
- No additional trust on client-provided IDs/tasks.

---

## Accessibility Requirements

- Async message rendering must keep Joomla alert semantics.
- Focus should be restored to the prior active control by id when possible.
- If focus restoration target does not exist after refresh, focus moves to refreshed container.
- Async messages should be announced via aria-live region.
- Keyboard-only workflows must remain equivalent to full-page mode.

---

## Reusable Core Hooks

Shared hooks used by `com_content` and available for extension:

- `Joomla.asyncAdminRequest(...)`
- `Joomla.refreshAdminFragment(...)`
- `Joomla.announceAsyncMessages(...)`

---

## PR Readiness Checklist

- All units in [docs/development/ajaxified-backend-plan.md](docs/development/ajaxified-backend-plan.md) marked complete.
- Targeted lint passes:
  - `npm run lint:js`
  - `npm run lint:testjs`
- PHP syntax checks pass for touched controllers/models.
- New/updated system tests cover:
  - async list actions and fragment refresh path,
  - autosave contract + skip/recover behavior,
  - custom-field filter controls and query filtering,
  - accessibility behavior after async refresh.

---

## Rollout Strategy

1. Keep feature flags default-safe for production environments.
2. Verify parity through targeted Cypress specs before merge.
3. Land as incremental commits (already preserved on branch history).
4. Expand to additional components in follow-up PRs.
