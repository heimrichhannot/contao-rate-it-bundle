<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Controller\BackendModule;

use Contao\CoreBundle\Exception\ResponseException;
use Contao\Input;
use Contao\System;
use Doctrine\DBAL\Connection;
use HeimrichHannot\RateItBundle\Service\RatingManager;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Backend module "RateIt" – lists rating statistics, allows viewing details,
 * resetting ratings and exporting to CSV.
 *
 * Registered in $GLOBALS['BE_MOD'] with a 'callback', so Contao instantiates it
 * with `new RateItBackendModule($dc)` and calls generate(). Services are pulled
 * from the container.
 *
 * Deviations from the Contao 4 version:
 *  - Export is CSV (the former cgo-it/contao-xls_export-bundle is Contao 4 only).
 *  - The rating distribution is rendered as bars instead of the discontinued
 *    Google "jsapi" charts.
 */
class RateItBackendModule
{
    private Connection $connection;
    private RatingManager $ratingManager;
    private Request $request;
    private int $perPage;

    public function __construct($dc = null)
    {
        $container = System::getContainer();
        $this->connection = $container->get('database_connection');
        $this->ratingManager = $container->get(RatingManager::class);
        $this->request = $container->get('request_stack')->getCurrentRequest();

        System::loadLanguageFile('tl_rateit');
        System::loadLanguageFile('default');

        $container->get('contao.framework')->initialize();
        $perPage = (int) $container->get('contao.framework')->getAdapter(\Contao\Config::class)->get('rating_listsize');
        $this->perPage = $perPage > 0 ? $perPage : 10;

        $GLOBALS['TL_CSS']['contao-rate-it-bundle-backend'] = 'bundles/contaorateit/contao-rate-it-bundle-backend.css|static';
    }

    public function generate(): string
    {
        $act = Input::get('act') ?: Input::post('act');

        return match ($act) {
            'view' => $this->viewRating(),
            'export' => $this->exportRatings(),
            'exportDetails' => $this->exportRatingDetails(),
            'reset_ratings' => $this->resetRatings(),
            default => $this->listRatings(),
        };
    }

    private function listRatings(): string
    {
        $session = $this->request->getSession()->getBag('contao_backend');
        $settings = $session->get('rateit_settings', []);

        if (Input::post('rateit_action')) {
            $settings = [
                'typ' => trim((string) Input::post('rateit_typ')),
                'active' => trim((string) Input::post('rateit_active')),
                'order' => trim((string) Input::post('rateit_order')),
                'page' => (int) Input::post('rateit_page'),
                'find' => trim((string) Input::post('rateit_find')),
            ];
            $session->set('rateit_settings', $settings);
        }

        $typ = $settings['typ'] ?? '';
        $active = $settings['active'] ?? '';
        $order = $settings['order'] ?? 'rating';
        $page = (int) ($settings['page'] ?? 0);
        $find = $settings['find'] ?? '';

        $where = [];
        $params = [];

        if ('' !== $typ) {
            $where[] = 'i.typ = ?';
            $params[] = $typ;
        }
        if ('' !== $active) {
            $where[] = 'i.active = ?';
            $params[] = '0' === $active ? '' : $active;
        }
        if ('' !== $find) {
            $where[] = 'i.title LIKE ?';
            $params[] = '%'.$find.'%';
        }

        $orderBy = match ($order) {
            'title' => 'title',
            'typ' => 'typ',
            'createdat' => 'createdat',
            default => 'rating DESC',
        };

        [$items, $total] = $this->queryItems($where, $params, $orderBy, $page);

        return $this->render('@Contao/backend/rateit_list.html.twig', [
            'items' => $items,
            'filter' => compact('typ', 'active', 'order', 'page', 'find'),
            'typeOptions' => $GLOBALS['TL_LANG']['tl_rateit_type_options'] ?? [],
            'activeOptions' => $GLOBALS['TL_LANG']['tl_rateit_active_options'] ?? [],
            'orderOptions' => $GLOBALS['TL_LANG']['tl_rateit_order_options'] ?? [],
            'pages' => $this->buildPages($total),
            'exportLink' => $this->createUrl(['act' => 'export']),
            'formAction' => $this->createUrl([]),
            'requestToken' => $this->requestToken(),
            'text' => $GLOBALS['TL_LANG']['tl_rateit'] ?? [],
            'msc' => $GLOBALS['TL_LANG']['MSC'] ?? [],
        ]);
    }

    private function viewRating(): string
    {
        $rkey = (string) Input::get('rkey');
        $typ = (string) Input::get('typ');

        $item = $this->connection->fetchAssociative(
            'SELECT i.id AS item_id, i.rkey, i.title, i.typ, i.createdat, i.active,
                    IFNULL(AVG(r.rating), 0) AS rating, COUNT(r.rating) AS totalRatings
             FROM tl_rateit_items i
             LEFT OUTER JOIN tl_rateit_ratings r ON (i.id = r.pid)
             WHERE i.rkey = ? AND i.typ = ?
             GROUP BY i.id, i.rkey, i.title, i.typ, i.createdat, i.active',
            [$rkey, $typ]
        );

        if (!$item) {
            throw new ResponseException(new RedirectResponse($this->createUrl([])));
        }

        $item['stars'] = $this->ratingManager->getStars();
        $item['percent'] = (float) $item['rating'];
        $item['rating'] = $this->ratingManager->percentToStars((float) $item['rating']);

        $ratings = $this->connection->fetchAllAssociative(
            'SELECT r.id, r.ip_address AS ip, r.memberid, r.rating, r.createdat,
                    CONCAT(COALESCE(m.firstname, \'\'), \' \', COALESCE(m.lastname, \'\')) AS member
             FROM tl_rateit_ratings r
             LEFT JOIN tl_member m ON m.id = r.memberid
             WHERE r.pid = ?
             ORDER BY r.createdat DESC',
            [$item['item_id']]
        );

        foreach ($ratings as &$rating) {
            $rating['percent'] = (float) $rating['rating'];
            $rating['rating'] = $this->ratingManager->percentToStars((float) $rating['rating']);
        }
        unset($rating);

        return $this->render('@Contao/backend/rateit_view.html.twig', [
            'item' => $item,
            'ratings' => $ratings,
            'distribution' => $this->getDistribution((int) $item['item_id']),
            'typeOptions' => $GLOBALS['TL_LANG']['tl_rateit_type_options'] ?? [],
            'activeOptions' => $GLOBALS['TL_LANG']['tl_rateit_active_options'] ?? [],
            'backLink' => $this->createUrl([]),
            'exportLink' => $this->createUrl(['act' => 'exportDetails', 'rkey' => $rkey, 'typ' => $typ]),
            'text' => $GLOBALS['TL_LANG']['tl_rateit'] ?? [],
            'msc' => $GLOBALS['TL_LANG']['MSC'] ?? [],
        ]);
    }

    private function resetRatings(): string
    {
        $ids = Input::post('selectedids');

        if (\is_array($ids)) {
            foreach ($ids as $id) {
                [$rkey, $typ] = array_pad(explode('__', (string) $id), 2, '');
                $itemId = $this->connection->fetchOne(
                    'SELECT id FROM tl_rateit_items WHERE rkey = ? AND typ = ?',
                    [$rkey, $typ]
                );
                if (false !== $itemId) {
                    $this->connection->executeStatement('DELETE FROM tl_rateit_ratings WHERE pid = ?', [$itemId]);
                }
            }
        }

        throw new ResponseException(new RedirectResponse($this->createUrl([])));
    }

    private function exportRatings(): string
    {
        [$items] = $this->queryItems([], [], 'rating DESC', -1);

        $rows = [];
        $rows[] = array_values($GLOBALS['TL_LANG']['tl_rateit']['xls_headers'] ?? ['rkey', 'title', 'typ', 'createdat', 'active', 'rating', 'stars', 'percent', 'totalRatings']);

        foreach ($items as $item) {
            $rows[] = [
                $item['rkey'],
                $item['title'],
                $GLOBALS['TL_LANG']['tl_rateit_type_options'][$item['typ']] ?? $item['typ'],
                $item['createdat'] ? date('Y-m-d H:i', (int) $item['createdat']) : '',
                '1' === (string) $item['active'] ? 'yes' : 'no',
                $item['rating'],
                $item['stars'],
                $item['percent'],
                $item['totalRatings'],
            ];
        }

        throw new ResponseException($this->csvResponse('rateit-ratings-'.date('Ymd-His').'.csv', $rows));
    }

    private function exportRatingDetails(): string
    {
        $rkey = (string) Input::get('rkey');
        $typ = (string) Input::get('typ');

        $itemId = $this->connection->fetchOne(
            'SELECT id FROM tl_rateit_items WHERE rkey = ? AND typ = ?',
            [$rkey, $typ]
        );

        $rows = [];
        $rows[] = array_values($GLOBALS['TL_LANG']['tl_rateit']['xls_headers_detail'] ?? ['ip', 'member', 'rating', 'createdat']);

        if (false !== $itemId) {
            $ratings = $this->connection->fetchAllAssociative(
                'SELECT r.ip_address AS ip, r.rating, r.createdat,
                        CONCAT(COALESCE(m.firstname, \'\'), \' \', COALESCE(m.lastname, \'\')) AS member
                 FROM tl_rateit_ratings r
                 LEFT JOIN tl_member m ON m.id = r.memberid
                 WHERE r.pid = ? ORDER BY r.createdat DESC',
                [$itemId]
            );

            foreach ($ratings as $rating) {
                $rows[] = [
                    $rating['ip'],
                    trim((string) $rating['member']),
                    $this->ratingManager->percentToStars((float) $rating['rating']),
                    $rating['createdat'] ? date('Y-m-d H:i', (int) $rating['createdat']) : '',
                ];
            }
        }

        throw new ResponseException($this->csvResponse('rateit-rating-'.$rkey.'-'.date('Ymd-His').'.csv', $rows));
    }

    /**
     * @return array{0: array<int, array<string, mixed>>, 1: int}
     */
    private function queryItems(array $where, array $params, string $orderBy, int $page): array
    {
        $whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $total = (int) $this->connection->fetchOne("SELECT COUNT(*) FROM tl_rateit_items i $whereSql", $params);

        $limit = '';
        if ($page >= 0) {
            $limit = 'LIMIT '.($page * $this->perPage).', '.$this->perPage;
        }

        $sql = "SELECT i.id AS item_id, i.rkey, i.title, i.typ, i.createdat, i.active,
                       IFNULL(AVG(r.rating), 0) AS rating, COUNT(r.rating) AS totalRatings
                FROM tl_rateit_items i
                LEFT OUTER JOIN tl_rateit_ratings r ON (i.id = r.pid)
                $whereSql
                GROUP BY i.id, i.rkey, i.title, i.typ, i.createdat, i.active
                ORDER BY $orderBy
                $limit";

        $items = $this->connection->fetchAllAssociative($sql, $params);

        foreach ($items as &$item) {
            $item['active'] = '1' === (string) $item['active'] ? '1' : '0';
            $item['percent'] = (float) $item['rating'];
            $item['rating'] = $this->ratingManager->percentToStars((float) $item['rating']);
            $item['stars'] = $this->ratingManager->getStars();
            $item['viewLink'] = $this->createUrl(['act' => 'view', 'rkey' => $item['rkey'], 'typ' => $item['typ']]);
        }
        unset($item);

        return [$items, $total];
    }

    /**
     * Rating distribution (grouped by star value) for the bar chart.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getDistribution(int $itemId): array
    {
        $rows = $this->connection->fetchAllAssociative(
            'SELECT rating, COUNT(*) AS count FROM tl_rateit_ratings WHERE pid = ? GROUP BY rating ORDER BY rating',
            [$itemId]
        );

        $max = 0;
        foreach ($rows as $row) {
            $max = max($max, (int) $row['count']);
        }

        $distribution = [];
        foreach ($rows as $row) {
            $distribution[] = [
                'stars' => $this->ratingManager->percentToStars((float) $row['rating']),
                'count' => (int) $row['count'],
                'width' => $max > 0 ? round((int) $row['count'] / $max * 100) : 0,
            ];
        }

        return $distribution;
    }

    private function buildPages(int $total): array
    {
        $pages = [];
        $first = 1;
        while ($total > 0) {
            $cnt = min($total, $this->perPage);
            $pages[] = $first.' - '.($first + $cnt - 1);
            $first += $cnt;
            $total -= $cnt;
        }

        return $pages;
    }

    private function createUrl(array $params): string
    {
        $url = 'contao?do='.Input::get('do');
        foreach ($params as $key => $value) {
            if ('' !== $value && null !== $value) {
                $url .= '&amp;'.$key.'='.rawurlencode((string) $value);
            }
        }

        return $url;
    }

    private function requestToken(): string
    {
        return System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
    }

    private function render(string $template, array $context): string
    {
        return System::getContainer()->get('twig')->render($template, $context);
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function csvResponse(string $filename, array $rows): Response
    {
        $response = new StreamedResponse(static function () use ($rows): void {
            $handle = fopen('php://output', 'wb');
            foreach ($rows as $row) {
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $response;
    }
}
