<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\DataContainer;

use Contao\Backend;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Contao\File;
use Contao\FilesModel;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;

/**
 * Keeps the tl_rateit_items table in sync when rate-able records (pages,
 * articles, news, faqs, content elements, modules) are saved or deleted.
 *
 * Replaces the former DcaHelper and the inline tl_*_rating DCA callback classes.
 */
class RatingItemContainer
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
        private readonly string $projectDir,
    ) {
    }

    #[AsCallback(table: 'tl_page', target: 'config.onsubmit')]
    public function onSubmitPage(DataContainer $dc): void
    {
        $this->insertOrUpdate($dc, 'page', $dc->activeRecord->title ?? '');
    }

    #[AsCallback(table: 'tl_page', target: 'config.ondelete')]
    public function onDeletePage(DataContainer $dc): void
    {
        $this->delete($dc, 'page');
    }

    #[AsCallback(table: 'tl_article', target: 'config.onsubmit')]
    public function onSubmitArticle(DataContainer $dc): void
    {
        $this->insertOrUpdate($dc, 'article', $dc->activeRecord->title ?? '');
    }

    #[AsCallback(table: 'tl_article', target: 'config.ondelete')]
    public function onDeleteArticle(DataContainer $dc): void
    {
        $this->delete($dc, 'article');
    }

    #[AsCallback(table: 'tl_news', target: 'config.onsubmit')]
    public function onSubmitNews(DataContainer $dc): void
    {
        $this->insertOrUpdate($dc, 'news', $dc->activeRecord->headline ?? '');
    }

    #[AsCallback(table: 'tl_news', target: 'config.ondelete')]
    public function onDeleteNews(DataContainer $dc): void
    {
        $this->delete($dc, 'news');
    }

    #[AsCallback(table: 'tl_faq', target: 'config.onsubmit')]
    public function onSubmitFaq(DataContainer $dc): void
    {
        $this->insertOrUpdate($dc, 'faq', $dc->activeRecord->question ?? '');
    }

    #[AsCallback(table: 'tl_faq', target: 'config.ondelete')]
    public function onDeleteFaq(DataContainer $dc): void
    {
        $this->delete($dc, 'faq');
    }

    #[AsCallback(table: 'tl_module', target: 'config.onsubmit')]
    public function onSubmitModule(DataContainer $dc): void
    {
        $this->insertOrUpdate($dc, 'module', $dc->activeRecord->rateit_title ?? '');
    }

    #[AsCallback(table: 'tl_module', target: 'config.ondelete')]
    public function onDeleteModule(DataContainer $dc): void
    {
        $this->delete($dc, 'module');
    }

    #[AsCallback(table: 'tl_content', target: 'config.onsubmit')]
    public function onSubmitContent(DataContainer $dc): void
    {
        if ('gallery' === ($dc->activeRecord->type ?? null)) {
            $this->syncGallery($dc);

            return;
        }

        $this->insertOrUpdate($dc, 'ce', $dc->activeRecord->rateit_title ?? '');
    }

    #[AsCallback(table: 'tl_content', target: 'config.ondelete')]
    public function onDeleteContent(DataContainer $dc): void
    {
        if ('gallery' === ($dc->activeRecord->type ?? null)) {
            $id = (int) $dc->activeRecord->id;
            $this->connection->executeStatement(
                'DELETE FROM tl_rateit_ratings WHERE pid IN (SELECT id FROM tl_rateit_items WHERE rkey LIKE ? AND typ = ?)',
                [$id.'|%', 'galpic']
            );
            $this->connection->executeStatement(
                'DELETE FROM tl_rateit_items WHERE rkey LIKE ? AND typ = ?',
                [$id.'|%', 'galpic']
            );

            return;
        }

        $this->delete($dc, 'ce');
    }

    #[AsCallback(table: 'tl_settings', target: 'fields.rating_template.options')]
    public function getRateItTemplates(): array
    {
        return $this->framework->getAdapter(Backend::class)->getTemplateGroup('rateit_');
    }

    #[AsCallback(table: 'tl_module', target: 'fields.rateit_template.options')]
    public function getTopRatingsTemplates(): array
    {
        return $this->framework->getAdapter(Backend::class)->getTemplateGroup('mod_rateit_top');
    }

    private function insertOrUpdate(DataContainer $dc, string $type, ?string $title): void
    {
        $record = $dc->activeRecord;
        $active = ($record->rateit_active ?? false) || ($record->addRating ?? false);
        $rkey = (string) $record->id;

        if (!$active) {
            $this->connection->update('tl_rateit_items', ['active' => ''], ['rkey' => $rkey, 'typ' => $type]);

            return;
        }

        $existing = $this->connection->fetchAssociative(
            'SELECT id FROM tl_rateit_items WHERE rkey = ? AND typ = ?',
            [$rkey, $type]
        );

        if (!$existing) {
            $this->connection->insert('tl_rateit_items', [
                'rkey' => $rkey,
                'tstamp' => time(),
                'typ' => $type,
                'createdat' => time(),
                'title' => (string) $title,
                'active' => '1',
            ]);

            return;
        }

        $this->connection->update(
            'tl_rateit_items',
            ['active' => '1', 'title' => (string) $title],
            ['rkey' => $rkey, 'typ' => $type]
        );
    }

    private function delete(DataContainer $dc, string $type): void
    {
        $this->connection->delete('tl_rateit_items', ['rkey' => (string) $dc->activeRecord->id, 'typ' => $type]);
    }

    private function syncGallery(DataContainer $dc): void
    {
        $this->framework->initialize();
        $record = $dc->activeRecord;
        $galleryId = (int) $record->id;
        $active = $record->rateit_active ? '1' : '';

        // deactivate previous gallery items first
        $this->connection->executeStatement(
            "UPDATE tl_rateit_items SET active = '' WHERE rkey LIKE ? AND typ = 'galpic'",
            [$galleryId.'|%']
        );

        $files = $this->framework->getAdapter(FilesModel::class)
            ->findMultipleByUuids(StringUtil::deserialize($record->multiSRC, true));

        if (null === $files) {
            return;
        }

        $headline = StringUtil::deserialize($record->headline);
        $title = (\is_array($headline) && !empty($headline['value'])) ? $headline['value'] : (string) $galleryId;

        while ($files->next()) {
            if ('file' === $files->type) {
                $this->upsertGalleryItem($galleryId, $files->id, $files->path, $title, $active);

                continue;
            }

            $subFiles = $this->framework->getAdapter(FilesModel::class)->findByPid($files->uuid);

            if (null === $subFiles) {
                continue;
            }

            while ($subFiles->next()) {
                if ('folder' === $subFiles->type) {
                    continue;
                }

                $this->upsertGalleryItem($galleryId, $subFiles->id, $subFiles->path, $title, $active);
            }
        }
    }

    private function upsertGalleryItem(int $galleryId, $picId, string $path, string $title, string $active): void
    {
        if (!is_file($this->projectDir.'/'.$path) || !(new File($path))->isGdImage) {
            return;
        }

        $rkey = $galleryId.'|'.$picId;
        $ratingTitle = $title.' - '.basename($path);

        $existing = $this->connection->fetchAssociative(
            'SELECT id FROM tl_rateit_items WHERE rkey = ? AND typ = ?',
            [$rkey, 'galpic']
        );

        if (!$existing) {
            $this->connection->insert('tl_rateit_items', [
                'rkey' => $rkey,
                'tstamp' => time(),
                'typ' => 'galpic',
                'createdat' => time(),
                'title' => $ratingTitle,
                'active' => $active,
            ]);

            return;
        }

        $this->connection->update(
            'tl_rateit_items',
            ['active' => $active, 'title' => $ratingTitle],
            ['rkey' => $rkey, 'typ' => 'galpic']
        );
    }
}
