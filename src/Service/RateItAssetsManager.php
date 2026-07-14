<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Service;

use HeimrichHannot\EncoreContracts\PageAssetsTrait;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

/**
 * Registers the RateIt frontend assets on the current page.
 *
 * Uses the Encore bundle entrypoint when available, otherwise falls back to the
 * built files published from the bundle's public folder. This centralises the
 * former scattered `$GLOBALS['TL_JAVASCRIPT'] / $GLOBALS['TL_CSS']` handling.
 */
class RateItAssetsManager implements ServiceSubscriberInterface
{
    use PageAssetsTrait;

    public function register(): void
    {
        $this->addPageEntrypoint('contao-rate-it-bundle', [
            'TL_CSS' => [
                'contao-rate-it-bundle' => 'bundles/contaorateit/contao-rate-it-bundle.css|static',
            ],
            'TL_JAVASCRIPT' => [
                'contao-rate-it-bundle' => 'bundles/contaorateit/contao-rate-it-bundle.js|static',
            ],
        ]);
    }
}
