<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Twig\Runtime;

use HeimrichHannot\RateItBundle\Service\RateItAssetsManager;
use HeimrichHannot\RateItBundle\Service\RatingManager;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

class RateItRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RatingManager $ratingManager,
        private readonly RateItAssetsManager $assetsManager,
        private readonly Environment $twig,
    ) {
    }

    /**
     * Render the rating widget for a given item.
     *
     * @param int|string  $rkey     the rating key (usually the news/page/article id)
     * @param string      $type     one of page|article|ce|module|news|faq|galpic|news4ward
     * @param string|null $template optional template name (defaults to the configured one)
     */
    public function renderRating($rkey, string $type = 'news', ?string $template = null): string
    {
        $this->assetsManager->register();

        $name = $template ?: $this->ratingManager->getTemplate();

        if (!str_contains($name, '@') && !str_ends_with($name, '.html.twig')) {
            $name = '@Contao/'.$name.'.html.twig';
        }

        return $this->twig->render($name, $this->ratingManager->getRatingData($rkey, $type));
    }

    /**
     * Return the raw rating data (for use in custom templates).
     *
     * @param int|string $rkey
     *
     * @return array<string, mixed>
     */
    public function getRatingData($rkey, string $type = 'news'): array
    {
        $this->assetsManager->register();

        return $this->ratingManager->getRatingData($rkey, $type);
    }
}
