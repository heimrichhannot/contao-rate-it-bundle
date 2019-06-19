<?php

namespace HeimrichHannot\RateItBundle;

class RateItNews extends RateItFrontend
{

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function parseArticle($objTemplate, $objArticle, $caller)
    {
        if (strpos(get_class($caller), "ModuleNews") !== false &&
            $objArticle['addRating']) {
            $ratingId = $objTemplate->id;
            $rating   = $this->loadRating($ratingId, 'news');
            $stars    = !$rating ? 0 : $this->percentToStars($rating['rating']);
            $percent  = round($rating['rating'], 0) . "%";

            $objTemplate->descriptionId = 'rateItRating-' . $ratingId . '-description';
            $objTemplate->description   = $this->getStarMessage($rating);
            $objTemplate->ratingId      = 'rateItRating-' . $ratingId . '-news-' . $stars . '_' . $this->intStars;
            $objTemplate->rateit_class  = 'rateItRating';
            $objTemplate->itemreviewed  = $rating['title'];
            $objTemplate->actRating     = $this->percentToStars($rating['rating']);
            $objTemplate->maxRating     = $this->intStars;
            $objTemplate->votes         = $rating[totalRatings];

            if ($this->strTextPosition == "before") {
                $objTemplate->showBefore = true;
            } else {
                if ($this->strTextPosition == "after") {
                    $objTemplate->showAfter = true;
                }
            }

            if ($objArticle['rateit_position'] == 'before') {
                $objTemplate->rateit_rating_before = true;
            } else {
                if ($objArticle['rateit_position'] == 'after') {
                    $objTemplate->rateit_rating_after = true;
                }
            }

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
        }
    }
}

?>