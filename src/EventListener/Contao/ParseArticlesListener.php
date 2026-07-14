<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Template;
use HeimrichHannot\RateItBundle\Service\RateItAssetsManager;
use HeimrichHannot\RateItBundle\Service\RatingManager;

/**
 * Adds the rating data to a news item template (former RateItNews::parseArticle).
 *
 * The dedicated list/reader "Item" classes have been replaced by the
 * {{ rateit_rating() }} Twig function; this hook keeps the classic Contao news
 * integration (news_*_rateit templates driven by the "addRating" flag) working.
 */
class ParseArticlesListener
{
    public function __construct(
        private readonly RatingManager $ratingManager,
        private readonly RateItAssetsManager $assetsManager,
    ) {
    }

    #[AsHook('parseArticles')]
    public function __invoke(Template $template, array $article, object $module): void
    {
        if (!str_contains($module::class, 'ModuleNews') || empty($article['addRating'])) {
            return;
        }

        $data = $this->ratingManager->getRatingData($template->id, 'news');

        $template->descriptionId = $data['descriptionId'];
        $template->description = $data['description'];
        $template->ratingId = $data['ratingId'];
        $template->rateit_class = $data['rateit_class'];
        $template->rateit_skin = $data['skin'];
        $template->itemreviewed = $data['itemreviewed'];
        $template->actRating = $data['actRating'];
        $template->maxRating = $data['maxRating'];
        $template->votes = $data['votes'];
        $template->showBefore = $data['showBefore'];
        $template->showAfter = $data['showAfter'];

        $template->rateit_rating_before = 'before' === ($article['rateit_position'] ?? '');
        $template->rateit_rating_after = 'after' === ($article['rateit_position'] ?? '');

        $this->assetsManager->register();
    }
}
