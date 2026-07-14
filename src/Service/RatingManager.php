<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Service;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\System;
use Doctrine\DBAL\Connection;

/**
 * Central data layer for the RateIt bundle.
 *
 * Replaces the former \Hybrid based "RateItFrontend" class. It reads the rating
 * configuration from the Contao settings (tl_settings / localconfig) and
 * provides the aggregated rating data consumed by the Twig functions,
 * fragment controllers and hook listeners.
 */
class RatingManager
{
    public function __construct(
        private readonly Connection $connection,
        private readonly ContaoFramework $framework,
    ) {
    }

    /**
     * Number of stars/hearts the rating scale uses.
     */
    public function getStars(): int
    {
        $stars = (int) $this->getConfig('rating_count');

        return $stars > 0 ? $stars : 5;
    }

    /**
     * Rating type: "hearts" or "stars".
     */
    public function getType(): string
    {
        return $this->getConfig('rating_type') ?: 'hearts';
    }

    /**
     * CSS modifier class matching the configured rating type.
     */
    public function getSkinClass(): string
    {
        return 'hearts' === $this->getType() ? 'rateit-hearts' : 'rateit-stars';
    }

    /**
     * Text position: "before" or "after".
     */
    public function getTextPosition(): string
    {
        return $this->getConfig('rating_textposition') ?: 'after';
    }

    /**
     * Default frontend template name (without extension).
     */
    public function getTemplate(): string
    {
        return $this->getConfig('rating_template') ?: 'rateit_default';
    }

    /**
     * Load the aggregated rating for a given key and type.
     *
     * @param int|string $rkey
     *
     * @return array<string, mixed>|null
     */
    public function loadRating($rkey, string $type): ?array
    {
        $sql = 'SELECT
                    i.rkey AS rkey,
                    i.title AS title,
                    IFNULL(AVG(r.rating), 0) AS rating,
                    COUNT(r.rating) AS totalRatings
                FROM tl_rateit_items i
                LEFT OUTER JOIN tl_rateit_ratings r ON (i.id = r.pid)
                WHERE i.rkey = ? AND typ = ? AND active = ?
                GROUP BY i.rkey, i.title';

        $result = $this->connection->fetchAssociative($sql, [(string) $rkey, $type, '1']);

        return false === $result ? null : $result;
    }

    /**
     * Convert a percentage value (0-100) to the configured star scale.
     */
    public function percentToStars(float $percent): float
    {
        $modifier = 100 / $this->getStars();

        return round($percent / $modifier, 1);
    }

    /**
     * Build the human readable rating description (e.g. "3/5 stars (12 votes)").
     *
     * @param array<string, mixed>|null $rating
     */
    public function getStarMessage(?array $rating): string
    {
        $this->loadLanguageFile('default');

        $stars = $this->percentToStars((float) ($rating['rating'] ?? 0));
        $pattern = (string) ($this->getConfig('rating_description') ?: '%current%/%max% %type% (%count% [Stimme|Stimmen])');

        $labels = [];
        $hasLabels = (bool) preg_match('/^.*\[(.+)\|(.+)\].*$/i', $pattern, $labels);

        // singular label only when there is exactly one rating
        $isPlural = !$rating || 1 !== (int) ($rating['totalRatings'] ?? 0);
        $label = $hasLabels ? ($isPlural ? $labels[2] : $labels[1]) : '';

        $count = $rating ? (int) ($rating['totalRatings'] ?? 0) : 0;
        $typeLabel = 'hearts' === $this->getType()
            ? ($GLOBALS['TL_LANG']['rateit']['hearts'] ?? '')
            : ($GLOBALS['TL_LANG']['rateit']['stars'] ?? '');

        $description = str_replace(
            ['%current%', '%max%', '%type%', '%count%'],
            [str_replace('.', ',', (string) $stars), (string) $this->getStars(), $typeLabel, (string) $count],
            $pattern
        );

        return preg_replace('/^(.*)(\[.*\])(.*)$/i', '\\1'.$label.'\\3', $description) ?? $description;
    }

    /**
     * Aggregate all values a rating widget template needs.
     *
     * @param int|string $rkey
     *
     * @return array<string, mixed>
     */
    public function getRatingData($rkey, string $type): array
    {
        $rating = $this->loadRating($rkey, $type);
        $stars = $rating ? $this->percentToStars((float) $rating['rating']) : 0;
        $max = $this->getStars();
        $position = $this->getTextPosition();

        return [
            'rkey' => $rkey,
            'type' => $type,
            'ratingId' => 'rateItRating-'.$rkey.'-'.$type.'-'.$stars.'_'.$max,
            'descriptionId' => 'rateItRating-'.$rkey.'-description',
            'description' => $this->getStarMessage($rating),
            'rateit_class' => 'rateItRating',
            'skin' => $this->getSkinClass(),
            'itemreviewed' => $rating['title'] ?? '',
            'actRating' => $stars,
            'maxRating' => $max,
            'votes' => $rating ? (int) ($rating['totalRatings'] ?? 0) : 0,
            'showBefore' => 'before' === $position,
            'showAfter' => 'after' === $position,
        ];
    }

    private function getConfig(string $key)
    {
        $this->framework->initialize();

        return $this->framework->getAdapter(Config::class)->get($key);
    }

    private function loadLanguageFile(string $name): void
    {
        $this->framework->initialize();
        $this->framework->getAdapter(System::class)->loadLanguageFile($name);
    }
}
