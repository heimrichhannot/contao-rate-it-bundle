<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\EventListener\Contao;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Template;
use Doctrine\DBAL\Connection;
use HeimrichHannot\RateItBundle\Service\RateItAssetsManager;
use HeimrichHannot\RateItBundle\Service\RatingManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Adds rating data to article, article list and gallery templates
 * (former RateItArticle::parseTemplateRateIt).
 */
class ParseTemplateListener
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly RatingManager $ratingManager,
        private readonly RateItAssetsManager $assetsManager,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    #[AsHook('parseTemplate')]
    public function __invoke(Template $template): void
    {
        match ($template->type ?? null) {
            'article' => $this->doArticle($template),
            'articleList' => $this->doArticleList($template),
            'gallery' => $this->doGallery($template),
            default => null,
        };
    }

    private function doArticle(Template $template): void
    {
        $article = $this->connection->fetchAssociative('SELECT * FROM tl_article WHERE id = ?', [$template->id]);

        if (!$article || empty($article['addRating'])) {
            return;
        }

        $data = $this->ratingManager->getRatingData($article['id'], 'article');

        $template->descriptionId = $data['descriptionId'];
        $template->description = $data['description'];
        $template->rateItID = $data['ratingId'];
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

    private function doArticleList(Template $template): void
    {
        if (empty($template->rateit_active)) {
            return;
        }

        $templateFixed = false;
        $articles = [];

        foreach ((array) $template->articles as $article) {
            $row = $this->connection->fetchAssociative(
                'SELECT * FROM tl_article WHERE id = ?',
                [$article['articleId'] ?? 0]
            );

            if ($row && !empty($row['addRating'])) {
                if (!$templateFixed) {
                    $template->setName($template->getName().'_rateit');
                    $templateFixed = true;
                }

                $data = $this->ratingManager->getRatingData($row['id'], 'article');

                $article['descriptionId'] = $data['descriptionId'];
                $article['description'] = $data['description'];
                $article['rateItID'] = $data['ratingId'];
                $article['rateit_class'] = $data['rateit_class'];
                $article['rateit_skin'] = $data['skin'];
                $article['itemreviewed'] = $data['itemreviewed'];
                $article['actRating'] = $data['actRating'];
                $article['maxRating'] = $data['maxRating'];
                $article['votes'] = $data['votes'];
                $article['showBefore'] = $data['showBefore'];
                $article['showAfter'] = $data['showAfter'];
                $article['rateit_rating_before'] = 'before' === ($row['rateit_position'] ?? '');
                $article['rateit_rating_after'] = 'after' === ($row['rateit_position'] ?? '');

                $this->assetsManager->register();
            }

            $articles[] = $article;
        }

        $template->articles = $articles;
    }

    private function doGallery(Template $template): void
    {
        $gallery = $this->connection->fetchAssociative('SELECT * FROM tl_content WHERE id = ?', [$template->id]);

        if (!$gallery || empty($gallery['rateit_active'])) {
            return;
        }

        $this->framework->initialize();
        $filesModel = $this->framework->getAdapter(FilesModel::class);

        $ratings = [];
        $files = $filesModel->findMultipleByUuids(StringUtil::deserialize($gallery['multiSRC'], true));

        if (null !== $files) {
            while ($files->next()) {
                if (isset($ratings[$files->path]) || !is_file($this->projectDir.'/'.$files->path)) {
                    continue;
                }

                if ('file' === $files->type) {
                    $this->addRatingForImage($ratings, (string) $gallery['id'], $files->id, $files->path);

                    continue;
                }

                $subFiles = $filesModel->findByPid($files->uuid);

                if (null === $subFiles) {
                    continue;
                }

                while ($subFiles->next()) {
                    if ('folder' === $subFiles->type) {
                        continue;
                    }

                    $this->addRatingForImage($ratings, (string) $gallery['id'], $subFiles->id, $subFiles->path);
                }
            }
        }

        $template->arrRating = $ratings;
        $this->assetsManager->register();
    }

    /**
     * @param array<string, mixed> $ratings
     */
    private function addRatingForImage(array &$ratings, string $galleryId, $picId, string $picPath): void
    {
        if (!(new File($picPath))->isGdImage) {
            return;
        }

        $data = $this->ratingManager->getRatingData($galleryId.'|'.$picId, 'galpic');

        $ratings[$picPath] = [
            'descriptionId' => $data['descriptionId'],
            'description' => $data['description'],
            'rateItID' => $data['ratingId'],
            'rateit_class' => $data['rateit_class'],
            'rateit_skin' => $data['skin'],
            'itemreviewed' => $data['itemreviewed'],
            'actRating' => $data['actRating'],
            'maxRating' => $data['maxRating'],
            'votes' => $data['votes'],
            'showBefore' => $data['showBefore'],
            'showAfter' => $data['showAfter'],
        ];
    }
}
