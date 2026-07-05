# Code Review Guidelines & Checklist

This document details the code review process and quality standards required for the Universe Tracker repository. Any pull request (PR) or code contribution must adhere to this checklist before approval and merge.

---

## ⚠️ Mandatory Pre-requisite

> [!IMPORTANT]
> **ALL TESTS MUST PASS BEFORE APPROVING ANY CHANGES.**
> No PR should be merged with failing, skipped, or flaky tests, or if there is any degradation in code coverage.

Before approving a PR, you must run the project's integrated verification suite:
```bash
composer test
```
This script executes:
1.  **Pint Linting:** Ensuring all PHP files adhere to the project's style standards.
2.  **PHPStan Static Analysis:** Verifying type safety and preventing logic bugs.
3.  **Pest PHP Test Suite:** Executing all unit and feature tests.

Additionally, for front-end changes, make sure the front-end checks pass:
```bash
# Verify ESLint & Prettier
npm run lint:check
npm run format:check

# Verify TypeScript type checks
npm run types:check
```

---

## 1. Architectural & Logic Review

### 1.1 Separation of Concerns
*   **Controllers:** Should remain thin. Check if business logic has been extracted into dedicated Action classes, Services, or Domain Models.
*   **Routing:** Verify that routes use modern Fluent binding syntax in [routes/web.php](file:///c:/Users/jared/Herd/universe-tracker/routes/web.php).
*   **Middleware:** Verify that new middleware or routing rules are correctly registered in [bootstrap/app.php](file:///c:/Users/jared/Herd/universe-tracker/bootstrap/app.php).

### 1.2 Data Access & Eloquent Best Practices
*   **N+1 Queries:** Check all database queries. Ensure relationships are eager-loaded using `with()` or `load()` when accessing related model attributes in loops or serializations.
*   **Database Transactions:** Ensure multi-model operations (e.g. creating a model and its associated relations) are wrapped in `DB::transaction()`.
*   **Model Attributes & Casts:** Check new Eloquent models for proper usage of PHP Attributes (`#[Fillable]`, `#[Hidden]`) and make sure value casts are defined in the `casts(): array` method instead of the legacy `$casts` property.

---

## 2. Security Review

*   **Mass Assignment Protection:** Ensure no models are populated directly from raw request arrays (`$request->all()`). All writes should use sanitized data (`$request->validated()`).
*   **Authorization:** Ensure every controller action and API endpoint is protected by an authorization check, using Laravel **Policies** or **Gates** (`$this->authorize()` or `Gate::authorize()`).
*   **SQL Injection:** Validate that raw queries or raw constraints (`whereRaw`, `selectRaw`, etc.) are not concatenating user input directly. Always use query bindings.
*   **Authentication & Session Guarding:** If endpoints deal with sensitive data or user settings, ensure they are guarded by the appropriate auth/Fortify middleware.

---

## 3. Frontend & Inertia.js Review

*   **Inertia Responses:** Ensure controllers return `Inertia::render('PageName', [...])` with lean payloads. Avoid sending unnecessary model attributes to the client.
*   **TypeScript Safety:** Check that typescript files (`.ts`, `.vue`) avoid using the `any` type. Interfaces, type declarations, or Generics should be explicitly defined.
*   **CSS and Styling:** Ensure Tailwind CSS v4 directives and classes are used correctly. Check that styling changes look consistent in both **Light** and **Dark** modes (using Tailwind's `dark:` modifier).
*   **Aesthetics:** Confirm that the user interface feels polished, handles loading states gracefully, and incorporates necessary micro-animations or transitions.

---

## 4. Database Migrations

*   **Rollback Safety:** Verify that the migration's `down()` method successfully reverses the exact schema changes made in the `up()` method.
*   **Performance:** Check that indexes are added for foreign keys and commonly searched columns.

---

## 5. Reviewer Checklist Summary

When reviewing a PR, check off the following items:

- [ ] All automated tests pass locally (`composer test` and front-end lints).
- [ ] No regression in test coverage.
- [ ] Security checks verified (mass assignment, SQL injection, authorization).
- [ ] No N+1 queries introduced.
- [ ] All new logic is covered by unit or feature tests in Pest PHP.
- [ ] UI visual check (Light/Dark mode, responsive layout).
- [ ] Code style matches Pint guidelines.
