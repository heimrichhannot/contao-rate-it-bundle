# Changelog

All notable changes to this project will be documented in this file.

## [5.0.0]
- Changed: migrated the bundle to Contao 5.3 / Symfony 6.4+ and PHP 8.1+.
- Added: `rateit_rating()` and `rateit_rating_data()` Twig functions, replacing the deprecated reader/list "item" classes (`src/Item/*`).
- Changed: frontend JS/CSS moved to `assets/` and bundled with Symfony Encore (`webpack.config.js`) instead of being injected via `$GLOBALS['TL_JAVASCRIPT']`/`TL_CSS`.
- Changed: legacy `\Hybrid` frontend modules / content element rewritten as fragment controllers (`#[AsFrontendModule]` / `#[AsContentElement]`).
- Changed: `$GLOBALS['TL_HOOKS']` registrations rewritten as `#[AsHook]` event listeners; DCA callbacks/`DcaHelper` rewritten as an `#[AsCallback]` service.
- Changed: bundle directory layout modernised (`contao/`, `config/`, `assets/`, `public/`).
- Changed: backend rating export is now CSV (dropped the Contao-4-only `cgo-it/contao-xls_export-bundle`); the rating distribution is rendered as bars instead of the discontinued Google "jsapi" charts.

## [4.0.10] - 2023-06-05
- Fixed: warnings
- 
## [4.0.9] - 2023-04-27
- Fixed: warnings

## [4.0.8] - 2023-04-27
- Fixed: warnings

## [4.0.7] - 2023-02-21
- Fixed: exceptions

## [4.0.6] - 2023-02-21
- Fixed: exception on cache warmup

## [4.0.5] - 2023-02-21
- Fixed: array index issues with php 8

## [4.0.4] - 2023-02-15
- Changed: adjusted license to LGPL-3.0-or-later and added license file
- Fixed: compatibility with symfony 5
- Fixed: missing return types in Plugin class