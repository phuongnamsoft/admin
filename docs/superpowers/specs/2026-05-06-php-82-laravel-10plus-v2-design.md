# PHP 8.2 + Laravel 10/11 — Package v2.0.0 Design

## Summary

Raise **minimum PHP to 8.2**, require **Laravel 10 or 11** via Composer, and release **v2.0.0** as a semver **major** so consumers on PHP 7.x / Laravel 8–9 stay on **1.x**. Scope is compatibility and deprecation cleanup, not a feature rewrite.

## Goals

- Install cleanly on **PHP ^8.2** with **Laravel ^10.0 || ^11.0** (recommended cap; avoids silent breakage on unreleased Laravel majors).
- Test suite passes on a matrix of **PHP 8.2 (and optionally 8.3)** × **Laravel 10 and 11**.
- Document breaking changes for **1.x → 2.x** (Composer constraints, any API removals discovered during migration).

## Non-goals

- No repo-wide `strict_types=1` adoption as part of this release unless explicitly added later.
- No large refactors unrelated to PHP/Laravel compatibility.
- No commitment to **Laravel 12+** until constraints are widened and verified in a future release.

## Composer policy

| Constraint | v1 (current) | v2 (this design) |
|------------|--------------|------------------|
| `php` | `>=7.3.0` | `^8.2` |
| `laravel/framework` | `>=8.0` | `^10.0 \|\| ^11.0` |
| Symfony packages used directly | Broad (`dom-crawler` ~3–6) | Narrow to versions compatible with L10/L11 (typically **^6.0 \|\| ^7.0** for `symfony/dom-crawler`; exact set validated during implementation). |

**Rationale for `^10.0 || ^11.0` instead of bare `>=10`:** The project owner asked for “Laravel >= 10”; capping at 11 documents supported majors and prevents Composer from selecting a future **Laravel 12** that may break this package before it is tested. Widening to `^12.0` becomes a deliberate follow-up release.

Other `require` entries (Doctrine DBAL, Elfinder, Google2FA, Bacon QR Code, Intervention Image) are checked for versions that support **PHP 8.2** and **Laravel 10/11**; bumps happen only when resolution or runtime errors require them.

**Dev dependencies:** `laravel/laravel` is aligned to **^10.0 || ^11.0** for the test harness. **`laravel/browser-kit-testing`** is evaluated first; if it cannot satisfy Laravel 10/11, tests migrate to **`Illuminate\Foundation\Testing\TestCase`** and HTTP-style assertions (same behaviors, different base class).

## Code and runtime compatibility

- Fix **PHP 8.2** issues (e.g. deprecated dynamic properties on classes without `#[\AllowDynamicProperties]` or declared properties), **null** / type warnings, and removed/deprecated PHP APIs used in `src/` or published stubs.
- Fix **Laravel 10 vs 11** differences only where this package calls framework APIs (middleware, container, routing, validation, filesystem, events).
- Prefer **minimal, localized** changes; match existing style in touched files.

## Testing and CI

- **Local:** `composer install` (or `update`) and `vendor/bin/phpunit` after constraint changes.
- **CI:** Add or extend a workflow (e.g. GitHub Actions) running PHPUnit on at least **PHP 8.2 × Laravel 10** and **PHP 8.2 × Laravel 11**; add **PHP 8.3** if maintenance cost is acceptable.

## Documentation and release

- **CHANGELOG** (or `docs/en/change-log.md` if that is the public changelog): list PHP/Laravel floors, link or summarize upgrade steps.
- **README**: replace outdated “PHP >= 7 / Laravel >= 5.5” style lines with **PHP ^8.2** and **Laravel ^10 \|\| ^11** for v2.
- Optional **UPGRADING.md** at repo root if consumer steps exceed a short changelog bullet list.
- **Git tag `v2.0.0`** after tests pass and docs are updated (release process may include Packagist auto-update).

## Repository note

There is **no `composer.lock`** in this package today; CI and local dev run `composer update`. Pinning Laravel per CI job may temporarily rewrite `composer.json` in the runner — that must not be committed back.

## Risks

- **BrowserKit** or other dev tools may block resolution; mitigation is test base migration.
- **Transitive** Symfony/HTTP client mismatches if `symfony/*` constraints are too tight or too loose; resolved by following Laravel’s versions and adjusting only this package’s direct requires.

## Approval record

Design approved by product owner: **major v2**, **PHP ^8.2**, **Laravel 10+** with **recommended constraint `^10.0 || ^11.0`**, pragmatic compatibility scope (no mass strict_types), optional PHPStan later.
