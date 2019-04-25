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

class RateItFaq extends RateItFrontend { 
	
	/**
	 * Initialize the controller
	 */
	public function __construct() {
		parent::__construct();
	}
	
	public function getContentElementRateIt($objRow, $strBuffer) {
		if ($objRow->type == 'module') {
			$objModule = $this->Database->prepare("SELECT * FROM tl_module WHERE id=? AND type IN ('faqpage', 'faqreader')")
										->limit(1)
										->execute($objRow->module);
			
			if ($objModule->numRows == 1) {
				$this->faq_categories = deserialize($objModule->faq_categories);
				
				if ($objModule->type == 'faqreader') {
					$strBuffer = $this->generateForFaqReader($objModule, $strBuffer);
				} else {
					$strBuffer = $this->generateForFaqPage($objModule, $strBuffer);
				}
				
				$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/rateit/public/js/onReadyRateIt.js|static';
				$GLOBALS['TL_JAVASCRIPT'][] = 'system/modules/rateit/public/js/rateit.min.js|static';
				$GLOBALS['TL_CSS'][] = 'system/modules/rateit/public/css/rateit.min.css||static';
				switch ($GLOBALS['TL_CONFIG']['rating_type']) {
					case 'hearts' :
						$GLOBALS['TL_CSS'][] = 'system/modules/rateit/public/css/heart.min.css||static';
						break;
					default:
						$GLOBALS['TL_CSS'][] = 'system/modules/rateit/public/css/star.min.css||static';
				}
			}
		}
		return $strBuffer;
	}
	
	private function generateForFaqPage($objModule, $strBuffer) {
		$objFaq = $this->Database
		               ->execute("SELECT *, author AS authorId, (SELECT headline FROM tl_faq_category WHERE tl_faq_category.id=tl_faq.pid) AS category, (SELECT name FROM tl_user WHERE tl_user.id=tl_faq.author) AS author FROM tl_faq WHERE pid IN(" . implode(',', array_map('intval', $this->faq_categories)) . ")" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""));
		
		if ($objFaq->numRows < 1) {
			return $strBuffer;
		}
		
		$htmlBuffer = new \simple_html_dom();
		$htmlBuffer->load($strBuffer);
		
		$arrFaqs = $objFaq->fetchAllAssoc();
		foreach ($arrFaqs as $arrFaq) {
			$rating = $this->generateSingle($arrFaq, $strBuffer);
			
			$h3 = $htmlBuffer->find('#'.$arrFaq['alias']);
			if (is_array($h3) && count($h3) == 1) { 
				$section = $h3[0]->parent();
				
				if ($arrFaq['rateit_position'] == 'before') {
					$section->innertext = $rating.$section->innertext;
				} else if ($arrFaq['rateit_position'] == 'after') {
					$section->innertext = $section->innertext.$rating;
				}
			}
		}
		
		$strBuffer = $htmlBuffer->save();
		
		// Aufräumen
		$htmlBuffer->clear();
		unset($htmlBuffer);
		
		return $strBuffer;
	}
	
	private function generateForFaqReader($objModule, $strBuffer) {
		// Set the item from the auto_item parameter
		if ($GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
			$this->Input->setGet('items', $this->Input->get('auto_item'));
		}
		
		// Do not index or cache the page if no FAQ has been specified
		if (!$this->Input->get('items')) {
			return $strBuffer;
		}
		
		$objFaq = $this->Database->prepare("SELECT *, author AS authorId, (SELECT title FROM tl_faq_category WHERE tl_faq_category.id=tl_faq.pid) AS category, (SELECT name FROM tl_user WHERE tl_user.id=tl_faq.author) AS author FROM tl_faq WHERE pid IN(" . implode(',', array_map('intval', $this->faq_categories)) . ") AND (id=? OR alias=?)" . (!BE_USER_LOGGED_IN ? " AND published=1" : ""))
								 ->limit(1)
								 ->execute((is_numeric($this->Input->get('items')) ? $this->Input->get('items') : 0), $this->Input->get('items'));
		
		if ($objFaq->numRows == 1) {
			$arrFaq = $objFaq->fetchAssoc();
			
			$rating = $this->generateSingle($arrFaq, $strBuffer);
		}
	
		if ($arrFaq['rateit_position'] == 'before') {
			$strBuffer = $rating.$strBuffer;
		} else if ($arrFaq['rateit_position'] == 'after') {
			$strBuffer = $strBuffer.$rating;
		}

		return $strBuffer;
	}
	
	private function generateSingle($arrFaq, $strBuffer) {
		$rating = '';
		
		if ($arrFaq['addRating']) {
			$actRecord = $this->Database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ='faq'")
			->execute($arrFaq['id'])
			->fetchAssoc();
				
			if ($actRecord['active']) {
				$this->import('rateit\\RateItRating', 'RateItRating');
				$this->RateItRating->rkey = $arrFaq['id'];
				$this->RateItRating->ratingType = 'faq';
				$this->RateItRating->generate();
		
				$rating = $this->RateItRating->output();
			}
		}
		
		return $rating;
	}
}
?>