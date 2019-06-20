<?php

namespace HeimrichHannot\RateItBundle\Item;

use HeimrichHannot\RateItBundle\RateItFrontend;

trait NewsRateItItemTrait
{
    public function getStars()
    {
        return intval($GLOBALS['TL_CONFIG']['rating_count']) ?: 5;
    }

    public function getTextPosition()
    {
        return $GLOBALS['TL_CONFIG']['rating_textposition'] ?: 'after';
    }

    public function getRatingId()
    {
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaorateit/js/onReadyRateIt.js|static';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaorateit/js/rateit.js|static';
        $GLOBALS['TL_CSS'][]        = 'bundles/contaorateit/css/rateit.min.css||static';

        switch ($GLOBALS['TL_CONFIG']['rating_type']) {
            case 'hearts' :
                $GLOBALS['TL_CSS'][] = 'bundles/contaorateit/css/heart.min.css||static';
                break;
            default:
                $GLOBALS['TL_CSS'][] = 'bundles/contaorateit/css/star.min.css||static';
        }

        $ratingId = $this->id;

        $frontend = new RateItFrontend();
        $rating   = $frontend->loadRating($ratingId, 'news');
        $stars    = !$rating ? 0 : $frontend->percentToStars($rating['rating']);

        return 'rateItRating-' . $ratingId . '-news-' . $stars . '_' . $this->getStars();
    }

    public function getDescriptionId()
    {
        return 'rateItRating-' . $this->id . '-description';
    }

    public function getDescription()
    {
        $ratingId = $this->id;

        $frontend = new RateItFrontend();
        $rating   = $frontend->loadRating($ratingId, 'news');

        return $frontend->getStarMessage($rating);
    }

    public function getRateItClass()
    {
        return 'rateItRating';
    }

    public function getItemReviewed()
    {
        $ratingId = $this->id;

        $frontend = new RateItFrontend();
        $rating   = $frontend->loadRating($ratingId, 'news');

        return $rating['title'];
    }

    public function getActRating()
    {
        $ratingId = $this->id;

        $frontend = new RateItFrontend();
        $rating   = $frontend->loadRating($ratingId, 'news');

        return $frontend->percentToStars($rating['rating']);
    }

    public function getMaxRating()
    {
        return $this->getStars();
    }

    public function getVotes()
    {
        $ratingId = $this->id;

        $frontend = new RateItFrontend();
        $rating   = $frontend->loadRating($ratingId, 'news');

        return $rating['totalRating'];
    }

    public function getShowBefore()
    {
        return $this->getTextPosition() === 'before';
    }

    public function getShowAfter()
    {
        return $this->getTextPosition() === 'after';
    }

    public function getRatingBefore()
    {
        return $this->rateit_position === 'before';
    }

    public function getRatingAfter()
    {
        return $this->rateit_position === 'after';
    }
}
