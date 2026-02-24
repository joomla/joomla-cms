# Ajaxified Backend Contract (Pilot)

## Scope

This document defines the technical contract for asynchronous administrator actions in the Ajaxified Backend project.

Initial pilot scope:

- Component: `com_content`
- View: Articles list (`view=articles`)
- Actions: list state changes, filtering/search, ordering, and pagination (incremental rollout)

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
- Focus must move predictably after list fragment replacement.
- Keyboard-only workflows must remain equivalent to full-page mode.

---

## Rollout Strategy

1. Pilot on `com_content` list interactions.
2. Verify parity through targeted Cypress tests.
3. Extract reusable infrastructure only after pilot stability.
4. Expand to more components incrementally.
