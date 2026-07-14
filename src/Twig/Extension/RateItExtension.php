<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Twig\Extension;

use HeimrichHannot\RateItBundle\Twig\Runtime\RateItRuntime;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides the RateIt Twig functions.
 *
 * These replace the former "list/reader item" classes (DefaultReaderItem /
 * DefaultListItem / NewsRateItItemTrait): instead of a rendering item object,
 * templates now call a Twig function.
 *
 *   {{ rateit_rating(news.id, 'news') }}            renders the rating widget
 *   {% set data = rateit_rating_data(news.id) %}    returns the raw values
 */
class RateItExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('rateit_rating', [RateItRuntime::class, 'renderRating'], ['is_safe' => ['html']]),
            new TwigFunction('rateit_rating_data', [RateItRuntime::class, 'getRatingData']),
        ];
    }
}
