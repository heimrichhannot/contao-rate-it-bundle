<?php

namespace HeimrichHannot\RateItBundle;

use HeimrichHannot\RateItBundle\RateItRating;

class RateItPage extends \Frontend
{

    var $rateItRating;

    /**
     * Initialize the controller
     */
    public function __construct()
    {
        parent::__construct();

        $this->rateItRating = new RateItRating();
        $this->loadDataContainer('settings');
    }

    public function generatePage($objPage, $objLayout, $objPageType)
    {
        if ($objPage->addRating) {
            $actRecord = $this->Database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ='page'")
                ->execute($objPage->id)
                ->fetchAssoc();

            if ($actRecord['active']) {
                $this->rateItRating->rkey = $objPage->id;
                $this->rateItRating->generate();

                $rating = $this->rateItRating->output();
                $rating .= $this->includeJs();
                $rating .= $this->includeCss();

                $objTemplate = $objPageType->Template;
                if ($objTemplate) {
                    if ($objPage->rateit_position == 'after') {
                        $objTemplate->main .= $rating;
                    } else {
                        $objTemplate->main = $rating . $objTemplate->main;
                    }
                }
            }
        }
    }

    private function includeCss()
    {
        $included    = false;
        $strHeadTags = '';
        if (is_array($GLOBALS['TL_CSS'])) {
            foreach ($GLOBALS['TL_CSS'] as $script) {
                if ($this->startsWith($script, 'bundles/contaorateit/css/rateit') === true) {
                    $included = true;
                    break;
                }
            }
        }

        if (!$included) {
            $strHeadTags = '<link rel="stylesheet" href="' . $this->addStaticUrlTo('bundles/contaorateit/css/rateit.min.css') . '">';
            switch ($GLOBALS['TL_CONFIG']['rating_type']) {
                case 'hearts' :
                    $strHeadTags .= '<link rel="stylesheet" href="' . $this->addStaticUrlTo('bundles/contaorateit/css/heart.min.css') . '">';
                    break;
                default:
                    $strHeadTags .= '<link rel="stylesheet" href="' . $this->addStaticUrlTo('bundles/contaorateit/css/star.min.css') . '">';
            }
        }
        return $strHeadTags;
    }

    private function includeJs()
    {
        global $objPage;

        $included    = false;
        $strHeadTags = '';
        if (is_array($GLOBALS['TL_JAVASCRIPT'])) {
            foreach ($GLOBALS['TL_JAVASCRIPT'] as $script) {
                if ($this->startsWith($script, 'bundles/contaorateit/js/rateit') === true) {
                    $included = true;
                    break;
                }
            }
        }

        if (!$included) {
            $strHeadTags = '<script' . (($objPage->outputFormat == 'xhtml') ? ' type="text/javascript"' : '') . ' src="' . $this->addStaticUrlTo('bundles/contaorateit/js/onReadyRateIt.js') . '"></script>' . "\n";
            $strHeadTags .= '<script' . (($objPage->outputFormat == 'xhtml') ? ' type="text/javascript"' : '') . ' src="' . $this->addStaticUrlTo('bundles/contaorateit/js/rateit.js') . '"></script>' . "\n";
        }
        return $strHeadTags;
    }

    function startsWith($haystack, $needle)
    {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }
}

?>
