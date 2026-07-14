<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Controller;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendUser;
use Contao\System;
use Doctrine\DBAL\Connection;
use HeimrichHannot\RateItBundle\Service\RatingManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles a single rating (vote) submitted via AJAX by the frontend widget.
 */
#[Route(
    '/rateit',
    name: 'rateit_ajax',
    defaults: ['_scope' => 'frontend', '_token_check' => false],
    methods: ['POST']
)]
class AjaxRateItController
{
    private const ALLOWED_TYPES = ['page', 'article', 'ce', 'module', 'news', 'faq', 'galpic', 'news4ward'];

    public function __construct(
        private readonly Connection $connection,
        private readonly RatingManager $ratingManager,
        private readonly ContaoFramework $framework,
        private readonly Security $security,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $this->framework->initialize();
        System::loadLanguageFile('default');

        $rkey = (string) $request->request->get('id');
        $percent = $request->request->get('vote');
        $type = (string) $request->request->get('type');

        // validate the rating key (numeric, or pipe-separated numerics for gallery pictures)
        if (str_contains($rkey, '|')) {
            foreach (explode('|', $rkey) as $part) {
                if (!is_numeric($part)) {
                    return $this->error('invalid_rating');
                }
            }
        } elseif (!is_numeric($rkey)) {
            return $this->error('invalid_rating');
        }

        if (!is_numeric($percent) || (float) $percent > 100) {
            return $this->error('invalid_rating');
        }
        $rating = (float) $percent;

        if (!\in_array($type, self::ALLOWED_TYPES, true)) {
            return $this->error('invalid_type');
        }

        $config = $this->framework->getAdapter(Config::class);
        $allowDuplicates = (bool) $config->get('rating_allow_duplicate_ratings');
        $allowDuplicatesForMembers = (bool) $config->get('rating_allow_duplicate_ratings_for_members');

        $ip = (string) $request->getClientIp();

        $userId = null;
        $user = $this->security->getUser();
        if ($user instanceof FrontendUser) {
            $userId = (int) $user->id;
        }

        $itemId = $this->connection->fetchOne(
            'SELECT id FROM tl_rateit_items WHERE rkey = ? AND typ = ?',
            [$rkey, $type]
        );

        if (false === $itemId) {
            return $this->error('invalid_rating');
        }

        $countUser = null;
        if (null !== $userId) {
            $countUser = (int) $this->connection->fetchOne(
                'SELECT COUNT(*) FROM tl_rateit_ratings WHERE pid = ? AND memberid = ?',
                [$itemId, $userId]
            );
        }

        $countIp = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM tl_rateit_ratings WHERE pid = ? AND ip_address = ?',
            [$itemId, $ip]
        );

        if ((!$allowDuplicatesForMembers && (null !== $countUser && 0 === $countUser))
            || ($allowDuplicatesForMembers && null !== $userId)) {
            $this->insertRating($itemId, $ip, $userId, $rating);
        } elseif (null === $countUser && ((!$allowDuplicates && 0 === $countIp) || $allowDuplicates)) {
            $this->insertRating($itemId, $ip, $userId, $rating);
        } else {
            return $this->error('duplicate_vote');
        }

        return new JsonResponse($this->ratingManager->getStarMessage($this->ratingManager->loadRating($rkey, $type)));
    }

    private function insertRating(int $itemId, string $ip, ?int $userId, float $rating): void
    {
        $this->connection->insert('tl_rateit_ratings', [
            'pid' => $itemId,
            'tstamp' => time(),
            'ip_address' => $ip,
            'memberid' => $userId,
            'rating' => $rating,
            'createdat' => time(),
        ]);
    }

    private function error(string $key): JsonResponse
    {
        // the language strings already carry the "ERROR:" prefix the widget looks for
        return new JsonResponse($GLOBALS['TL_LANG']['rateit']['error'][$key] ?? 'ERROR: '.$key);
    }
}
