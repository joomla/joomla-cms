# Ajaxified Backend — Implementation Plan

## Goal

Implement an incremental, production-safe "Ajaxified Backend" for Joomla administrator UX with:

1. List actions without full page reload (publish/unpublish/trash/feature/filter/order/pagination/transition).
2. Automatic saving while editing (configurable interval, enable/disable).
3. Versioning-safe autosave policy (avoid version spam).
4. Extended filtering by custom fields.

This plan uses small, test-backed commits. Every step must be complete and green before moving to the next.

---

## Branch and Commit Policy

- Base branch: `upstream/6.1-dev`
- Working branch: `feature/ajaxified-backend-livewire-like`
- Commit style: one small functional unit per commit.
- Rule: no mixed commits (feature + refactor + unrelated formatting).
- Rule: every commit includes tests or explicit proof of no regression.
- Rule: keep non-Ajax fallback fully working at all times.

Recommended commit prefixes:

- `feat(async-admin): ...`
- `feat(autosave): ...`
- `feat(filters): ...`
- `test(com_content): ...`
- `refactor(async-admin): ...`

---

## Definition of Done (per unit)

A unit is done only if all are true:

- Code implemented within scoped files only.
- Existing behavior remains functional without Ajax mode.
- New behavior covered by targeted tests.
- JS lint and touched tests lint pass.
- Manual smoke test for affected admin flow passes.
- Commit message clearly describes what and why.

---

## Delivery Phases (commit-by-commit)

### Phase 0 — Safety and Feature Flag

#### Unit 0.1: Add feature flag plumbing
- Add global config/option for `async_admin_enabled` (default: disabled).
- Wire option exposure to JS options storage.
- Add language strings for admin setting labels and help text.

Tests:
- Unit test for config default and option read path.
- Manual: verify disabled by default.

Commit:
- `feat(async-admin): add feature flag plumbing and defaults`

#### Unit 0.2: Add architecture notes and guardrails
- Add internal docs describing request/response contract and fallback rules.
- Define strict constraints (token handling, permission checks, redirects in payload).

Tests:
- N/A (docs-only commit).

Commit:
- `docs(async-admin): define request contract and fallback constraints`

---

### Phase 1 — Core Async Contract

#### Unit 1.1: Server response envelope for async admin actions
- Introduce reusable helper/trait for JSON responses: `success`, `messages`, `redirect`, `fragments`, `meta`.
- Keep current redirect responses untouched for non-Ajax requests.

Tests:
- PHP unit tests for envelope format and headers.

Commit:
- `feat(async-admin): add reusable async response envelope`

#### Unit 1.2: Client request helper and fallback behavior
- Add JS utility using `Joomla.request` for admin async actions.
- Standardize token/error handling and automatic fallback to full submit on failure.

Tests:
- JS tests if available for helper behavior.
- Manual: force XHR failure -> fallback submit works.

Commit:
- `feat(async-admin): add client helper with graceful fallback`

---

### Phase 2 — Pilot List View (com_content Articles)

#### Unit 2.1: Async publish/unpublish/trash/feature actions
- Add async endpoints/paths for list state actions in `com_content`.
- Return partial list HTML fragment + messages.
- Preserve permissions and token checks.

Tests:
- Extend system tests for articles list action flows.

Commit:
- `feat(async-admin): async article state actions in list view`

#### Unit 2.2: Async workflow transition in list rows
- Async handling for `runTransition` actions in articles list.
- Row/list refresh without full reload.

Tests:
- Add Cypress scenario for transition execution and UI update.

Commit:
- `feat(async-admin): async workflow transitions in article list`

#### Unit 2.3: Async filters/search/order/pagination
- Intercept search tools and ordering submits for articles list.
- Replace list container with server-rendered fragment.

Tests:
- Cypress tests for search, status filter, ordering, and page navigation.

Commit:
- `feat(async-admin): async searchtools interactions for article list`

---

### Phase 3 — Autosave (Pilot: com_content Article Edit)

#### Unit 3.1: Autosave scheduler and dirty-state tracking
- Add form-level autosave timer (configurable interval).
- Track dirty state and skip requests when unchanged.
- Pause autosave during explicit save/apply operations.

Tests:
- Cypress: auto-save triggers after edits and interval.

Commit:
- `feat(autosave): add scheduler and dirty tracking for article edit`

#### Unit 3.2: Draft-safe save endpoint contract
- Add autosave request mode that does not break normal save flow.
- Return autosave timestamp + state for UI indicator.

Tests:
- PHP/unit integration around autosave mode handling.
- Cypress: verify autosave status indicator updates.

Commit:
- `feat(autosave): add autosave endpoint contract and status payload`

#### Unit 3.3: Versioning integration policy
- Implement anti-spam policy:
  - No new version when data hash is unchanged.
  - Minimum version interval window.
  - Respect explicit manual save/apply for normal version creation.

Tests:
- Extend content history tests: repeated autosave should not create excessive versions.

Commit:
- `feat(autosave): integrate versioning-safe autosave policy`

#### Unit 3.4: Undo/recover last autosave snapshot (MVP)
- Add simple recover action in edit UI to restore last autosaved snapshot.
- Ensure action is explicit and reversible.

Tests:
- Cypress: edit -> autosave -> change again -> recover snapshot.

Commit:
- `feat(autosave): add recover last autosave snapshot`

---

### Phase 4 — Custom Fields Filter Extension

#### Unit 4.1: Filter form support for custom fields (articles list)
- Add field-driven filter controls to search tools form.
- Include selected custom field filters in list state.

Tests:
- Cypress using test custom fields.

Commit:
- `feat(filters): add custom field controls to article list filters`

#### Unit 4.2: Query-layer integration for custom field filters
- Extend `ArticlesModel` list query with joins/conditions for selected field filters.
- Ensure performant SQL and no regressions in existing filters.

Tests:
- Integration/system tests with multiple field types.

Commit:
- `feat(filters): apply custom field filters in article list query`

---

### Phase 5 — Generalization and Hardening

#### Unit 5.1: Extract reusable core hooks from com_content pilot
- Move reusable async action logic to shared admin JS/core layer.
- Keep component-specific adapters thin.

Tests:
- Existing com_content tests must still pass.

Commit:
- `refactor(async-admin): extract reusable admin async hooks`

#### Unit 5.2: Accessibility and UX hardening
- Focus management after fragment updates.
- ARIA live announcements for success/error states.
- Keyboard behavior parity with non-Ajax flow.

Tests:
- Cypress checks for focus placement and message visibility.

Commit:
- `feat(async-admin): add a11y and focus management for async updates`

#### Unit 5.3: Final docs and contributor testing notes
- Document feature flags, known limitations, and test commands.
- Add QA checklist for maintainers/reviewers.

Tests:
- N/A (docs-only commit).

Commit:
- `docs(async-admin): add usage, QA, and reviewer guide`

---

## Test Execution Strategy

For each unit, run the smallest relevant scope first:

1. JS lint (if JS changed): `npm run lint:js`
2. System test lint (if Cypress changed): `npm run lint:testjs`
3. Targeted Cypress spec(s):
   - `tests/System/integration/administrator/components/com_content/Articles.cy.js`
   - `tests/System/integration/administrator/components/com_content/Article.cy.js`
   - `tests/System/integration/administrator/components/com_contenthistory/Content.cy.js`
4. Broader suite only when phase completes.

---

## Working Loop (repeat for every unit)

1. Implement minimal scope.
2. Run targeted tests.
3. Fix only issues related to current unit.
4. Re-run tests.
5. Commit.
6. Record completed unit in this file.

---

## Current Status Tracker

- [x] Branch created from `upstream/6.1-dev`
- [x] Unit 0.1 complete
- [x] Unit 0.2 complete
- [x] Unit 1.1 complete
- [x] Unit 1.2 complete
- [x] Unit 2.1 complete
- [x] Unit 2.2 complete
- [x] Unit 2.3 complete
- [x] Unit 3.1 complete
- [x] Unit 3.2 complete
- [x] Unit 3.3 complete
- [x] Unit 3.4 complete
- [x] Unit 4.1 complete
- [x] Unit 4.2 complete
- [x] Unit 5.1 complete
- [x] Unit 5.2 complete
- [x] Unit 5.3 complete
