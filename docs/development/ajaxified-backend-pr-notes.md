# PR Body — Ajaxified Backend for com_content

## Title

Ajaxified Backend (com_content): async admin list/actions, autosave policy + recover flow, custom-field filtering, reusable async hooks, and accessibility hardening

## Base and Branch

- Base: `5.4-dev`
- Head: `feature/ajaxified-backend-livewire-like`

## Why

Administrator UX for `com_content` currently depends on full page submits for most list and edit interactions. This PR adds progressive asynchronous behavior with strict fallback compatibility, while preserving server-side ACL/CSRF enforcement and existing synchronous flows.

## What this PR delivers

### 1) Async admin contract foundations

- Feature-flag plumbing for async backend behavior in `com_content`.
- Reusable JSON envelope trait for async responses.
- Shared client helper to execute async requests with graceful fallback.

### 2) Async list interactions in Articles manager

- Async state actions (`publish`, `unpublish`, `archive`, `trash`, `featured`, `unfeatured`).
- Async workflow transition execution.
- Async search/filter/order/pagination behavior with list fragment refresh.

### 3) Autosave in Article edit

- Autosave scheduler with dirty-state tracking.
- Async autosave endpoint contract and status payload.
- Versioning-safe autosave policy:
	- skip unchanged payload,
	- minimum interval throttle based on configured autosave interval.
- Recover last autosaved snapshot and undo recover (MVP, session-local).

### 4) Custom-field filtering in Articles list

- New custom-field filter controls in search tools.
- Query integration for selected custom field + optional value match.

### 5) Shared hooks + accessibility hardening

- Core reusable async fragment refresh helper extraction.
- Focus restoration after async refresh.
- Aria-live announcement for async messages.

### 6) Documentation and reviewer support

- Execution tracker with all units marked complete.
- Contract aligned with final implemented scope.
- QA and reviewer guide.
- PR handoff note (this file).

## Detailed commit-by-commit mapping

1. `b3ecfa80b9` docs(async-admin): add step-by-step ajaxified backend implementation plan
	 - Added planning tracker:
		 - `docs/development/ajaxified-backend-plan.md`

2. `c809fb6e50` feat(async-admin): add com_content async flag and js option exposure
	 - Added config and script option exposure:
		 - `administrator/components/com_content/config.xml`
		 - `administrator/components/com_content/src/View/Articles/HtmlView.php`
		 - `administrator/language/en-GB/com_content.ini`
		 - `tests/System/integration/administrator/components/com_content/Articles.cy.js`

3. `3034f123a8` docs(async-admin): define async contract and implementation guardrails
	 - Added contract and guardrails docs:
		 - `docs/development/ajaxified-backend-contract.md`

4. `b359c8aec9` feat(async-admin): add reusable async response envelope trait
	 - Added reusable server trait and unit tests:
		 - `libraries/src/MVC/Controller/AsyncAdminResponseTrait.php`
		 - `tests/Unit/Libraries/Cms/MVC/Controller/AsyncAdminResponseTraitTest.php`

5. `c7adaae075` feat(async-admin): add client async helper with graceful fallback
	 - Added shared client helper:
		 - `build/media_source/system/js/core.es6.js`

6. `a65a268c66` feat(async-admin): add async article list state actions pilot
	 - Added list action async flow and controller response handling:
		 - `administrator/components/com_content/src/Controller/ArticlesController.php`
		 - `administrator/components/com_content/tmpl/articles/default.php`
		 - `build/media_source/com_content/js/articles-list.es6.js`
		 - `build/media_source/system/js/core.es6.js`
		 - `tests/System/integration/administrator/components/com_content/Articles.cy.js`

7. `ec98ce9f42` feat(async-admin): support async workflow transitions in article list
	 - Extended async handling for transitions:
		 - `administrator/components/com_content/src/Controller/ArticlesController.php`
		 - `build/media_source/com_content/js/articles-list.es6.js`
		 - `tests/System/integration/administrator/components/com_content/Articles.cy.js`

8. `650a41245a` feat(async-admin): add async filter and pagination handling for articles list
	 - Added async search tools/pagination interception:
		 - `build/media_source/com_content/js/articles-list.es6.js`
		 - `tests/System/integration/administrator/components/com_content/Articles.cy.js`

9. `14aecd049d` feat(autosave): add scheduler and dirty tracking for article edit
	 - Added autosave foundations:
		 - `administrator/components/com_content/config.xml`
		 - `administrator/components/com_content/src/View/Article/HtmlView.php`
		 - `administrator/components/com_content/tmpl/article/edit.php`
		 - `administrator/language/en-GB/com_content.ini`
		 - `build/media_source/com_content/js/form-edit.es6.js`
		 - `tests/System/integration/administrator/components/com_content/Article.cy.js`

10. `d73bbeb1c0` feat(autosave): add async autosave endpoint contract and status payload
		- Added controller endpoint contract and client status handling:
			- `administrator/components/com_content/src/Controller/ArticleController.php`
			- `administrator/language/en-GB/com_content.ini`
			- `build/media_source/com_content/js/form-edit.es6.js`
			- `tests/System/integration/administrator/components/com_content/Article.cy.js`

11. `8e8131d7a1` feat(autosave): integrate versioning-safe autosave policy
		- Added skip/throttle anti-spam policy:
			- `administrator/components/com_content/src/Controller/ArticleController.php`
			- `administrator/language/en-GB/com_content.ini`
			- `build/media_source/com_content/js/form-edit.es6.js`
			- `tests/System/integration/administrator/components/com_content/Article.cy.js`

12. `8a72c93dbe` feat(autosave): add recover and undo snapshot flow
		- Added recover/undo controls and behavior:
			- `build/media_source/com_content/js/form-edit.es6.js`
			- `administrator/language/en-GB/com_content.ini`
			- `tests/System/integration/administrator/components/com_content/Article.cy.js`

13. `ea0999ce03` feat(filters): add custom field controls to article list filters
		- Added filter form controls and state wiring:
			- `administrator/components/com_content/forms/filter_articles.xml`
			- `administrator/components/com_content/src/Model/ArticlesModel.php`
			- `administrator/language/en-GB/com_content.ini`
			- `tests/System/integration/administrator/components/com_content/Articles.cy.js`

14. `d79315877d` feat(filters): apply custom field filters in article list query
		- Added SQL join/conditions for selected custom field/value:
			- `administrator/components/com_content/src/Model/ArticlesModel.php`
			- `tests/System/integration/administrator/components/com_content/Articles.cy.js`

15. `945da08bb0` refactor(async-admin): extract reusable admin async refresh hooks
		- Extracted reusable fragment refresh hook into core and refactored list consumer:
			- `build/media_source/system/js/core.es6.js`
			- `build/media_source/com_content/js/articles-list.es6.js`
			- `tests/System/integration/administrator/components/com_content/Articles.cy.js`

16. `3c3128449c` feat(async-admin): add a11y focus and live message refresh handling
		- Added focus restoration and aria-live announcements:
			- `build/media_source/system/js/core.es6.js`
			- `tests/System/integration/administrator/components/com_content/Articles.cy.js`

17. `42590e894f` docs(async-admin): add usage, QA, and reviewer guide
		- Added operational and validation guide:
			- `docs/development/ajaxified-backend-qa.md`

18. `e2f83e5cac` docs(async-admin): align contract with completed feature scope
		- Updated contract to final behavior and PR checklist:
			- `docs/development/ajaxified-backend-contract.md`

19. `6d3b376ec0` docs(async-admin): add pull request handoff notes
		- Added PR support doc:
			- `docs/development/ajaxified-backend-pr-notes.md`

## Behavioral guarantees preserved

- Async behavior is feature-flag-gated.
- Non-async paths remain available and functional.
- CSRF and ACL checks remain server-side.
- Client fallback path is retained for unusable async responses.

## Validation executed

- `npm run lint:js`
	- Passes with pre-existing unrelated warnings in baseline files.
- `npm run lint:testjs`
	- Passes.
- `php -l administrator/components/com_content/src/Controller/ArticleController.php`
	- Passes.
- `php -l administrator/components/com_content/src/Model/ArticlesModel.php`
	- Passes.

## Test coverage touched

- `tests/System/integration/administrator/components/com_content/Articles.cy.js`
	- Async helper usage and fallback behavior.
	- Async list submit/transition/action flow assertions.
	- Reusable fragment helper usage.
	- Custom-field filter controls and query behavior.
	- Accessibility behavior (focus + live announcement).

- `tests/System/integration/administrator/components/com_content/Article.cy.js`
	- Autosave option exposure and status node.
	- Async autosave contract usage.
	- Autosave skip status behavior.
	- Recover/undo snapshot flow.

- `tests/Unit/Libraries/Cms/MVC/Controller/AsyncAdminResponseTraitTest.php`
	- Envelope trait behavior and payload shape checks.

## Reviewer QA checklist (manual)

1. Flags OFF:
	 - Confirm classic full submit behavior on list and edit screens.
2. Flags ON:
	 - Confirm list async actions, transitions, search filters, pagination.
3. Autosave:
	 - Confirm pending/saving/saved statuses.
	 - Confirm skip-on-unchanged and skip-on-throttle behavior.
	 - Confirm recover and undo recover controls.
4. Custom fields:
	 - Confirm filter controls appear when fields exist.
	 - Confirm list query narrows by selected field/value.
5. Accessibility:
	 - Confirm focus restoration after async refresh.
	 - Confirm async messages are announced via aria-live.

## Risk and rollout notes

- Scope intentionally limited to `com_content` pilot for functional safety.
- Shared helper extraction is additive and consumed by pilot implementation.
- Follow-up work for additional components should reuse the same contract/hooks.

## Copy-ready short PR description (optional)

This PR completes the Ajaxified Backend pilot for `com_content` with incremental, test-backed commits. It introduces async list interactions, autosave with anti-spam policy and recover/undo support, custom-field filtering (controls + query integration), reusable async refresh hooks, and accessibility hardening (focus + aria-live). All units in the implementation tracker are complete and documented, with lint/syntax validation and updated QA/reviewer guidance.
