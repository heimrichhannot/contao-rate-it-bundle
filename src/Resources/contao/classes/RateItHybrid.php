<?php

namespace HeimrichHannot\RateItBundle;

/**
 * Class RateItHybrid
 */
abstract class RateItHybrid extends RateItFrontend
{
    //protected $intStars = 5;

    /**
     * Initialize the controller
     */
    public function __construct($objElement)
    {
        parent::__construct($objElement);
    }

    /**
     * Display a wildcard in the back end
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### Rate IT ###';
            $objTemplate->title    = $this->rateit_title;
            $objTemplate->id       = $this->id;
            $objTemplate->link     = $this->name;
            $objTemplate->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        $this->strTemplate = $GLOBALS['TL_CONFIG']['rating_template'];

        $this->strType         = $GLOBALS['TL_CONFIG']['rating_type'];
        $this->strTextPosition = $GLOBALS['TL_CONFIG']['rating_textposition'];

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaorateit/js/onReadyRateIt.js|static';
        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/contaorateit/js/rateit.js|static';
        $GLOBALS['TL_CSS'][]        = 'bundles/contaorateit/css/rateit.min.css||static';
        switch ($this->strType) {
            case 'hearts' :
                $GLOBALS['TL_CSS'][] = 'bundles/contaorateit/css/heart.min.css||static';
                break;
            default:
                $GLOBALS['TL_CSS'][] = 'bundles/contaorateit/css/star.min.css||static';
        }

        return parent::generate();
    }

    /**
     * Generate the module/content element
     */
    protected function compile()
    {
        $this->Template = new \FrontendTemplate($this->strTemplate);

        $this->Template->setData($this->arrData);

        $rating   = $this->loadRating($this->getParent()->id, $this->getType());
        $ratingId = $this->getParent()->id;
        $stars    = !$rating ? 0 : $this->percentToStars($rating['rating']);
        $percent  = round($rating['rating'], 0) . "%";

        $this->Template->descriptionId = 'rateItRating-' . $ratingId . '-description';
        $this->Template->description   = $this->getStarMessage($rating);
        $this->Template->id            = 'rateItRating-' . $ratingId . '-' . $this->getType() . '-' . $stars . '_' . $this->intStars;
        $this->Template->rateit_class  = 'rateItRating';
        $this->Template->itemreviewed  = $rating['title'];
        $this->Template->actRating     = $this->percentToStars($rating['rating']);
        $this->Template->maxRating     = $this->intStars;
        $this->Template->votes         = $rating[totalRatings];

        if ($this->strTextPosition == "before") {
            $this->Template->showBefore = true;
        } else {
            if ($this->strTextPosition == "after") {
                $this->Template->showAfter = true;
            }
        }

        return parent::compile();
    }
}

?>