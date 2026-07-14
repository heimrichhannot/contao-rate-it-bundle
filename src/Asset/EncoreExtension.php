<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Asset;

use HeimrichHannot\EncoreContracts\EncoreEntry;
use HeimrichHannot\EncoreContracts\EncoreExtensionInterface;
use HeimrichHannot\RateItBundle\ContaoRateItBundle;

class EncoreExtension implements EncoreExtensionInterface
{
    public function getBundle(): string
    {
        return ContaoRateItBundle::class;
    }

    public function getEntries(): array
    {
        return [
            EncoreEntry::create('contao-rate-it-bundle', 'assets/js/main.js')
                ->addJsEntryToRemoveFromGlobals('contao-rate-it-bundle'),
            EncoreEntry::create('contao-rate-it-bundle-backend', 'assets/js/backend.entry.js'),
        ];
    }
}
