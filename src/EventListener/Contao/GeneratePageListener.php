<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\LayoutModel;
use Contao\PageModel;
use Contao\PageRegular;
use Doctrine\DBAL\Connection;
use HeimrichHannot\RateItBundle\Twig\Runtime\RateItRuntime;

/**
 * Injects a page rating into the page template (former RateItPage::generatePage).
 */
class GeneratePageListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly RateItRuntime $rateItRuntime,
    ) {
    }

    #[AsHook('generatePage')]
    public function __invoke(PageModel $page, LayoutModel $layout, PageRegular $pageRegular): void
    {
        if (!$page->addRating) {
            return;
        }

        $active = $this->connection->fetchOne(
            "SELECT active FROM tl_rateit_items WHERE rkey = ? AND typ = 'page'",
            [(string) $page->id]
        );

        if ('1' !== (string) $active) {
            return;
        }

        // renderRating() also registers the required frontend assets
        $rating = $this->rateItRuntime->renderRating($page->id, 'page');

        $template = $pageRegular->Template ?? null;

        if (null === $template || !isset($template->main)) {
            return;
        }

        if ('after' === $page->rateit_position) {
            $template->main .= $rating;
        } else {
            $template->main = $rating.$template->main;
        }
    }
}
