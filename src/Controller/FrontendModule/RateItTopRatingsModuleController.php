<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Controller\FrontendModule;

use Contao\ArticleModel;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Routing\ContentUrlGenerator;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Contao\NewsModel;
use Contao\PageModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use HeimrichHannot\RateItBundle\Service\RateItAssetsManager;
use HeimrichHannot\RateItBundle\Service\RatingManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

/**
 * Lists the best / most rated items (former FE_MOD "rateit_top_ratings").
 */
#[AsFrontendModule(type: 'rateit_top_ratings', category: 'application', template: 'mod_rateit_top_ratings')]
class RateItTopRatingsModuleController extends AbstractFrontendModuleController
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly RatingManager $ratingManager,
        private readonly RateItAssetsManager $assetsManager,
        private readonly ContentUrlGenerator $urlGenerator,
        private readonly Environment $twig,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $this->assetsManager->register();

        $types = StringUtil::deserialize($model->rateit_types, true);
        $topType = 'most' === $model->rateit_toptype ? 'most' : 'best';
        $count = max(1, (int) $model->rateit_count);

        $ratings = [];

        if (!empty($types)) {
            $placeholders = implode(',', array_fill(0, \count($types), '?'));

            $sql = "SELECT
                        i.id AS item_id,
                        i.rkey AS rkey,
                        i.title AS title,
                        i.typ AS typ,
                        i.createdat AS createdat,
                        i.active AS active,
                        IFNULL(AVG(r.rating), 0) AS best,
                        COUNT(r.rating) AS most
                    FROM tl_rateit_items i
                    LEFT OUTER JOIN tl_rateit_ratings r ON (i.id = r.pid)
                    WHERE typ IN ($placeholders)
                    GROUP BY rkey, title, item_id, typ, createdat, active
                    ORDER BY $topType DESC
                    LIMIT $count";

            foreach ($this->connection->fetchAllAssociative($sql, $types) as $row) {
                $stars = $this->ratingManager->percentToStars((float) $row['best']);
                $ratings[] = [
                    'title' => $row['title'],
                    'typ' => $row['typ'],
                    'rateItID' => 'rateItRating-'.$row['rkey'].'-'.$row['typ'].'-'.$stars.'_'.$this->ratingManager->getStars(),
                    'descriptionId' => 'rateItRating-'.$row['rkey'].'-description',
                    'rateit_class' => 'rateItRating',
                    'skin' => $this->ratingManager->getSkinClass(),
                    'url' => $this->getUrl($row['typ'], (string) $row['rkey']),
                    'description' => $this->ratingManager->getStarMessage([
                        'totalRatings' => (int) $row['most'],
                        'rating' => (float) $row['best'],
                        'title' => $row['title'],
                    ]),
                    'rating' => (float) $row['best'],
                    'count' => (int) $row['most'],
                    'rel' => 'not-rateable',
                ];
            }
        }

        $headline = StringUtil::deserialize($model->headline);
        $cssID = StringUtil::deserialize($model->cssID, true);

        return new Response($this->twig->render('@Contao/mod_rateit_top_ratings.html.twig', [
            'arrRatings' => $ratings,
            'headlineText' => \is_array($headline) ? ($headline['value'] ?? '') : (string) $headline,
            'headlineTag' => \is_array($headline) ? ($headline['unit'] ?? 'h2') : 'h2',
            'elementId' => $cssID[0] ?? '',
            'elementClass' => trim('mod_rateit_top_ratings ce_rateit_top_ratings '.($cssID[1] ?? '')),
        ]));
    }

    private function getUrl(string $type, string $rkey): ?string
    {
        $this->framework->initialize();

        try {
            if ('page' === $type) {
                $page = $this->framework->getAdapter(PageModel::class)->findById($rkey);

                return $page ? $this->urlGenerator->generate($page, [], UrlGeneratorInterface::ABSOLUTE_URL) : null;
            }

            if ('news' === $type) {
                $news = $this->framework->getAdapter(NewsModel::class)->findById($rkey);

                return $news ? $this->urlGenerator->generate($news, [], UrlGeneratorInterface::ABSOLUTE_URL) : null;
            }

            if ('article' === $type) {
                $article = $this->framework->getAdapter(ArticleModel::class)->findById($rkey);

                if (null !== $article && null !== ($page = $this->framework->getAdapter(PageModel::class)->findById($article->pid))) {
                    return $this->urlGenerator->generate($page, [], UrlGeneratorInterface::ABSOLUTE_URL).'#'.$article->alias;
                }
            }
        } catch (\Throwable) {
            return null;
        }

        return null;
    }
}
