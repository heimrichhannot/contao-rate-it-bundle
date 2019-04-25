<?php
/**
 * Contao Open Source CMS
 * Copyright (C) 2005-2011 Leo Feyer
 *
 * Formerly known as TYPOlight Open Source CMS.
 *
 * This program is free software: you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation, either
 * version 3 of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this program. If not, please visit the Free
 * Software Foundation website at <http://www.gnu.org/licenses/>.
 *
 * PHP version 5
 * @copyright  cgo IT, 2013
 * @author     Carsten Götzinger (info@cgo-it.de)
 * @package    rateit
 * @license    GNU/LGPL
 * @filesource
 */

namespace cgoIT\rateit;

class RateItPage extends \Frontend {
	
	/**
	 * Initialize the controller
	 */
	public function __construct() {
		parent::__construct();

		$this->loadDataContainer('settings');
	}
	
	public function generatePage($objPage, $objLayout, $objPageType) {
		if ($objPage->addRating) {
			$actRecord = $this->Database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ='page'")
										->execute($objPage->id)
										->fetchAssoc();
			
			if ($actRecord['active']) {
				$this->import('rateit\\RateItRating', 'RateItRating');
				$this->RateItRating->rkey = $objPage->id;
				$this->RateItRating->generate();
				
				$rating = $this->RateItRating->output();
				$rating .= $this->includeJs();
				$rating .= $this->includeCss();
				
				$objTemplate = $objPageType->Template;
				if ($objTemplate) {
					if ($objPage->rateit_position == 'after') {
						$objTemplate->main .= $rating;
					} else {
						$objTemplate->main = $rating.$objTemplate->main;
					}
				}
			}
		}
	}

	private function includeCss() {
		$included = false;
		$strHeadTags = '';
		if (is_array($GLOBALS['TL_CSS'])) {
			foreach ($GLOBALS['TL_CSS'] as $script) {
				if ($this->startsWith($script, 'system/modules/rateit/public/css/rateit') === true) {
					$included = true;
					break;
				}
			}
		}

	    if (!$included) {
	    	$strHeadTags = '<link rel="stylesheet" href="'.$this->addStaticUrlTo('system/modules/rateit/public/css/rateit.min.css').'">';
	    	switch ($GLOBALS['TL_CONFIG']['rating_type']) {
	    		case 'hearts' :
	    			$strHeadTags .= '<link rel="stylesheet" href="'.$this->addStaticUrlTo('system/modules/rateit/public/css/heart.min.css').'">';
	    			break;
	    		default:
	    			$strHeadTags .= '<link rel="stylesheet" href="'.$this->addStaticUrlTo('system/modules/rateit/public/css/star.min.css').'">';
	    	}
	    }
		return $strHeadTags;
	}
	
	private function includeJs() {
		$included = false;
		$strHeadTags = '';
		if (is_array($GLOBALS['TL_JAVASCRIPT'])) {
			foreach ($GLOBALS['TL_JAVASCRIPT'] as $script) {
				if ($this->startsWith($script, 'system/modules/rateit/public/js/rateit') === true) {
					$included = true;
					break;
				}
			}
		}

	    if (!$included) {
	   		$strHeadTags = '<script' . (($objPage->outputFormat == 'xhtml') ? ' type="text/javascript"' : '') . ' src="' . $this->addStaticUrlTo('system/modules/rateit/public/js/onReadyRateIt.js') . '"></script>' . "\n";
	   		$strHeadTags .= '<script' . (($objPage->outputFormat == 'xhtml') ? ' type="text/javascript"' : '') . ' src="' . $this->addStaticUrlTo('system/modules/rateit/public/js/rateit.min.js') . '"></script>' . "\n";
	    }
	   	return $strHeadTags;
	}
	
	function startsWith($haystack, $needle) {
	    // search backwards starting from haystack length characters from the end
	    return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}
}
?>