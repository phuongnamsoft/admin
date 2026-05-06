# PHP 8.2 + Laravel 10/11 — v2.0.0 Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use `subagent-driven-development` (recommended) or `executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship **v2.0.0** with `php:^8.2`, `laravel/framework:^10.0||^11.0`, updated dev stack and tests, CI matrix, and docs so the package installs and runs on supported PHP/Laravel combinations.

**Architecture:** Tighten Composer platform and framework constraints first, resolve dependencies with `composer update`, then fix test/runtime breakages with minimal source edits. Keep `Laravel\BrowserKitTesting\TestCase` by upgrading to **`laravel/browser-kit-testing:^7`**, which supports Laravel 10/11, to avoid rewriting all `visit()` / `see()` tests.

**Tech stack:** PHP 8.2+, Laravel 10/11, PHPUnit 10+, Composer 2, optional GitHub Actions.

---

## File map (what changes)

| Area | Files |
|------|--------|
| Constraints | `composer.json` |
| Lockfile | `composer.lock` (regenerated) |
| PHPUnit | `phpunit.xml.dist` (PHPUnit 10+ schema if required) |
| Tests base | `tests/TestCase.php` (imports / parent only if needed) |
| Package source | `src/**/*.php` — only files that fail tests or emit PHP 8.2 / Laravel deprecations |
| Docs | `README.md`, `CHANGELOG.md`, optional `UPGRADING.md`, `docs/en/change-log.md` if it mirrors releases |
| CI | Create `.github/workflows/tests.yml` (or extend existing) |

---

### Task 1: Composer — platform and framework

**Files:**
- Modify: `composer.json`

- [ ] **Step 1: Replace `require` and `require-dev` constraint blocks**

Edit `composer.json` so the relevant sections match (keep `name`, `authors`, `autoload`, `extra`, `scripts` as they are; merge these keys):

```json
    "require": {
        "php": "^8.2",
        "symfony/dom-crawler": "^6.0|^7.0",
        "laravel/framework": "^10.0|^11.0",
        "doctrine/dbal": "^3.0|^4.0",
        "barryvdh/laravel-elfinder": "^0.5.3",
        "pragmarx/google2fa": "^8.0",
        "bacon/bacon-qr-code": "^3.0",
        "intervention/image": "^3.11"
    },
    "require-dev": {
        "laravel/laravel": "^10.0|^11.0",
        "laravel/browser-kit-testing": "^7.0",
        "phpunit/phpunit": "^10.5",
        "spatie/phpunit-watcher": "^1.22"
    },
```

Update the `suggest` line for Intervention Image to refer to v3 (text only), e.g. `"intervention/image": "Required for image upload/manipulation (~3.x)."`

- [ ] **Step 2: Refresh lockfile**

Run:

```bash
cd "d:/templates/projects/packages/admin"
composer update --with-all-dependencies
```

Expected: completes without unresolvable conflicts. If `barryvdh/laravel-elfinder` or another package blocks resolution, run `composer why-not laravel/framework 11.0` (or the blocked version) and bump only that direct dependency to the smallest version that satisfies PHP 8.2 + Laravel 10/11.

- [ ] **Step 3: Commit**

```bash
git add composer.json composer.lock
git commit -m "chore!: require PHP ^8.2 and Laravel ^10|^11 for v2"
```

---

### Task 2: PHPUnit configuration (PHPUnit 10)

**Files:**
- Modify: `phpunit.xml.dist`

- [ ] **Step 1: Align `phpunit.xml.dist` with PHPUnit 10**

Replace the file content with:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true"
         failOnDeprecation="true"
         failOnNotice="true"
         failOnWarning="true"
         cacheDirectory=".phpunit.cache"
>
    <testsuites>
        <testsuite name="all">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

- [ ] **Step 2: Add cache dir to `.gitignore` if missing**

Append a line:

```
.phpunit.cache/
```

to `.gitignore` when that directory is not already ignored.

- [ ] **Step 3: Commit**

```bash
git add phpunit.xml.dist .gitignore
git commit -m "chore(test): migrate phpunit.xml.dist for PHPUnit 10"
```

---

### Task 3: Test harness — BrowserKit 7 + Laravel skeleton

**Files:**
- Modify: `tests/TestCase.php`

- [ ] **Step 1: Confirm parent class import**

Ensure the file still begins with:

```php
<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
```

No logic change is required unless `createApplication()` paths break: `bootstrap/app.php` must exist under `vendor/laravel/laravel` after `composer update` (Laravel 10/11 skeleton). If the bootstrap API differs, update only the `require` line for `bootstrap/app.php` to match the installed skeleton (Laravel 11 uses the same file path).

- [ ] **Step 2: Run one smoke test**

```bash
./vendor/bin/phpunit tests/LaravelTest.php --colors=always
```

Expected: tests run; either PASS or a concrete error message about bootstrap/config (then fix in the same task).

- [ ] **Step 3: Commit** (only if you changed `TestCase.php`)

```bash
git add tests/TestCase.php
git commit -m "chore(test): align TestCase with Laravel 10/11 skeleton"
```

---

### Task 4: Full PHPUnit run and fix package source

**Files:**
- Modify: only failing files under `src/` (and `src/helpers.php` if referenced)

- [ ] **Step 1: Run full suite**

```bash
./vendor/bin/phpunit --colors=always
```

Expected: either all green or a list of failures.

- [ ] **Step 2: Fix failures in priority order**

1. **Fatal errors / type errors** in `src/` (constructor signatures, return types, removed Laravel APIs).
2. **PHP 8.2 deprecations** (e.g. dynamic properties): prefer real declared properties; use `#[\AllowDynamicProperties]` only on legacy value objects where declaration is impractical.
3. **Test-only failures** (assertions, HTML changes from Laravel): update test expectations only when the framework output legitimately changed (e.g. dashboard copy).

- [ ] **Step 3: Re-run until green**

```bash
./vendor/bin/phpunit --colors=always
```

Expected: `OK (N tests, … assertions)`.

- [ ] **Step 4: Commit**

```bash
git add src/ tests/
git commit -m "fix: PHP 8.2 and Laravel 10/11 compatibility for v2"
```

---

### Task 5: CI — GitHub Actions matrix

**Files:**
- Create: `.github/workflows/tests.yml`

- [ ] **Step 1: Add workflow file**

Create `.github/workflows/tests.yml`:

```yaml
name: tests

on:
  push:
    branches: [master, main]
  pull_request:

jobs:
  phpunit:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ['8.2', '8.3']
        laravel: ['10', '11']
    name: PHP ${{ matrix.php }} Laravel ${{ matrix.laravel }}
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          extensions: dom, curl, libxml, mbstring, zip, pdo, mysql, fileinfo
          coverage: none

      - name: Pin Laravel ${{ matrix.laravel }}.x and install
        run: |
          composer require "laravel/framework:^${{ matrix.laravel }}.0" --no-update --no-interaction
          composer update --prefer-dist --no-interaction --no-progress

      - name: Run PHPUnit
        env:
          DB_CONNECTION: mysql
          MYSQL_HOST: 127.0.0.1
          MYSQL_PORT: 3306
          MYSQL_DATABASE: laravel_admin_test
          MYSQL_USER: root
          MYSQL_PASSWORD: password
        run: vendor/bin/phpunit --colors=always

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: laravel_admin_test
        ports:
          - 3306:3306
        options: --health-cmd="mysqladmin ping" --health-interval=10s --health-timeout=5s --health-retries=5
```

This repository has **no committed `composer.lock`**, so each CI job may rewrite `composer.json`’s `laravel/framework` line for that run only (ephemeral checkout). If you later add a lockfile, switch to **one Laravel version per branch** or use `composer install` plus a second workflow that bumps the lock for the other major.

- [ ] **Step 2: Push and verify** (or `act` locally if installed)

- [ ] **Step 3: Commit**

```bash
git add .github/workflows/tests.yml
git commit -m "ci: run PHPUnit on PHP 8.2+ with MySQL service"
```

---

### Task 6: Documentation and changelog

**Files:**
- Modify: `README.md`, `CHANGELOG.md`, optionally `UPGRADING.md`, `docs/en/change-log.md`

- [ ] **Step 1: Update README Requirements**

Replace outdated PHP/Laravel lines (e.g. `PHP >= 7.0.0`, `Laravel >= 5.5.0`) with:

```markdown
## Requirements

- PHP ^8.2
- Laravel ^10.0 or ^11.0
- Fileinfo PHP extension
```

- [ ] **Step 2: Write CHANGELOG entry for v2.0.0**

In `CHANGELOG.md`, append:

```markdown
## 2.0.0 - unreleased

### Breaking

- Minimum PHP is now **8.2** (`^8.2`).
- Minimum Laravel is now **10.x**; supported through **11.x** (`^10.0 || ^11.0` in Composer). Laravel 8–9 and PHP 7.x are no longer supported; use **1.x** for older stacks.

### Migration

- Bump your app to PHP 8.2+ and Laravel 10 or 11, then `composer require phuongnamsoft/admin:^2.0`.
- Review any overrides of package classes for signature changes after upgrading.
```

- [ ] **Step 3: Optional `UPGRADING.md`**

Create `UPGRADING.md` only if you discover consumer-facing API removals beyond Composer (list each symbol removed and replacement).

- [ ] **Step 4: Commit**

```bash
git add README.md CHANGELOG.md UPGRADING.md docs/en/change-log.md
git commit -m "docs: document v2 PHP/Laravel requirements and changelog"
```

---

### Task 7: Release tag (human or CI)

**Files:** none (git tag)

- [ ] **Step 1: Confirm clean tree and tests**

```bash
git status
./vendor/bin/phpunit --colors=always
```

Expected: clean working tree (except intentional), PHPUnit green.

- [ ] **Step 2: Tag v2.0.0**

```bash
git tag -a v2.0.0 -m "Release v2.0.0: PHP ^8.2, Laravel ^10|^11"
git push origin v2.0.0
```

---

## Plan self-review

| Spec item | Task covering it |
|-----------|------------------|
| PHP ^8.2 | Task 1 |
| Laravel ^10 \|\| ^11 | Task 1 |
| Major semver v2 | Tasks 1, 6, 7 |
| Minimal refactors | Task 4 wording |
| BrowserKit path | Task 1 + 3 |
| CI matrix | Task 5 |
| README / changelog | Task 6 |

Placeholder scan: none intentional; Task 5 notes a fallback if matrix `composer require` is brittle — implementer picks single-job CI first if needed, then expands.

---

## Execution handoff

Plan complete and saved to `docs/superpowers/plans/2026-05-06-php-82-laravel-10plus-v2.md`.

**1. Subagent-driven (recommended)** — one subagent per task, quick review between tasks.  
**2. Inline execution** — run tasks in order in this session with commits after each task.

Say which you prefer (or start Task 1 inline without replying).
