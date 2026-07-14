# RateIt bundle

Star/heart rating extension for the Contao Open Source CMS. This bundle is a
fork of [cgoIT/contao-rate-it-bundle](https://github.com/cgoIT/contao-rate-it-bundle),
migrated to **Contao 5** and Symfony 6.4/7.

## Installation

```bash
composer require heimrichhannot/contao-rate-it-bundle
```

The frontend assets (rating widget JS + the star/heart skins) are built with
[Symfony Encore](https://symfony.com/doc/current/frontend.html):

```bash
yarn install
yarn build-production   # or: yarn build-dev
```

This produces the webpack entries in `public/`
(`contao-rate-it-bundle.js` / `.css` and `contao-rate-it-bundle-backend.css`).
When the [contao-encore-bundle](https://github.com/heimrichhannot/contao-encore-bundle)
is installed the entrypoints are registered automatically; otherwise the built
files are loaded from `bundles/contaorateit/` as a fallback.

## Rating configuration

The rating type (hearts/stars), scale, text position, default template and the
description pattern are configured under *System settings* in the Contao backend.

## Usage

### Twig function (recommended)

The former list/reader "item" classes (`src/Item/*`) have been **replaced by a
Twig function**. Render a rating widget for any rate-able entity directly in a
Twig template:

```twig
{# render the widget for a news item #}
{{ rateit_rating(news.id, 'news') }}

{# use a custom template (defaults to the one configured in the settings) #}
{{ rateit_rating(news.id, 'news', 'rateit_microdata') }}

{# or fetch the raw data for a fully custom markup #}
{% set rating = rateit_rating_data(news.id, 'news') %}
```

Valid types: `page`, `article`, `ce`, `module`, `news`, `faq`, `galpic`,
`news4ward`.

### Classic integrations

The classic, flag-driven integrations continue to work and are implemented as
Contao hook listeners / fragment controllers:

* **Pages** – enable *"Add rating"* in the page settings (`generatePage` hook).
* **Articles / article lists / galleries** – enable *"Add rating"* (`parseTemplate` hook).
* **News** – enable *"Add rating"* on a news item (`parseArticles` hook,
  `news_*_rateit` templates).
* **FAQ** – enable *"Add rating"* on a FAQ (`getContentElement` hook).
* **Rating content element / frontend module** – add the *"RateIt"* content
  element or frontend module.
* **Top ratings** – the *"RateIt best/most ratings"* frontend module.

### Backend module

A *RateIt* backend module (under *Content*) lists the rating statistics, allows
resetting ratings and exporting them (CSV).

## Migration notes (Contao 4 → 5)

* `src/Item/*` (reader/list item classes) → the `rateit_rating()` /
  `rateit_rating_data()` Twig functions.
* JS/CSS previously injected via `$GLOBALS['TL_JAVASCRIPT']`/`TL_CSS` → moved to
  `assets/` and bundled with Encore (`webpack.config.js`).
* Legacy `\Hybrid` frontend modules / content element → fragment controllers
  (`#[AsFrontendModule]` / `#[AsContentElement]`).
* `$GLOBALS['TL_HOOKS']` registrations → `#[AsHook]` event listeners.
* DCA callback classes / `DcaHelper` → `#[AsCallback]` service
  (`RatingItemContainer`).
* `.html5` widget templates rendered by the bundle → Twig
  (`rateit_default`, `rateit_microdata`, `mod_rateit_top_ratings`, backend views).
  The news/article/gallery override templates remain in the classic `.html5`
  format because they extend the corresponding core Contao templates.
* Excel export (Contao-4-only `cgo-it/contao-xls_export-bundle`) → CSV export.
* The backend rating distribution is rendered as bars instead of the
  discontinued Google "jsapi" charts.
