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

class RateItArticle extends RateItFrontend {

	/**
	 * Initialize the controller
	 */
	public function __construct() {
		parent::__construct();
	}

	public function parseTemplateRateIt($objTemplate) {
		if ($objTemplate->type == 'article') {
			$objTemplate = $this->doArticle($objTemplate);
		} else if ($objTemplate->type == 'articleList') {
			$objTemplate = $this->doArticleList($objTemplate);
		} else if ($objTemplate->type == 'gallery') {
			$objTemplate = $this->doGallery($objTemplate);
		}
		return $objTemplate;
	}

	private function doArticle($objTemplate) {
		$arrArticle = $this->Database->prepare('SELECT * FROM tl_article WHERE ID=?')
		->limit(1)
		->execute($objTemplate->id)
		->fetchAssoc();

		if ($arrArticle['addRating']) {
			if ($objTemplate->multiMode && $objTemplate->showTeaser) {
				$objTemplate->setName('mod_'.$arrArticle['rateit_template'].'_teaser');
			} else {
				$objTemplate->setName($arrArticle['rateit_template']);
			}

			$ratingId = $arrArticle['id'];
			$rating = $this->loadRating($ratingId, 'article');
			$stars = !$rating ? 0 : $this->percentToStars($rating['rating']);
			$percent = round($rating['rating'], 0)."%";

			$objTemplate->descriptionId = 'rateItRating-'.$ratingId.'-description';
			$objTemplate->description = $this->getStarMessage($rating);
			$objTemplate->rateItID = 'rateItRating-'.$ratingId.'-article-'.$stars.'_'.$this->intStars;
			$objTemplate->rateit_class = 'rateItRating';
			$objTemplate->itemreviewed = $rating['title'];
			$objTemplate->actRating = $this->percentToStars($rating['rating']);
			$objTemplate->maxRating = $this->intStars;
			$objTemplate->votes = $rating[totalRatings];

			if ($this->strTextPosition == "before") {
				$objTemplate->showBefore = true;
			}
			else if ($this->strTextPosition == "after") {
				$objTemplate->showAfter = true;
			}

			if ($arrArticle['rateit_position'] == 'before') {
				$objTemplate->rateit_rating_before = true;
			} else if ($arrArticle['rateit_position'] == 'after') {
				$objTemplate->rateit_rating_after = true;
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

		return $objTemplate;
	}

	private function doArticleList($objTemplate) {
		if ($objTemplate->rateit_active) {
			$bolTemplateFixed = false;
			$arrArticles = array();
			foreach ($objTemplate->articles as $article) {
				$arrArticle = $this->Database->prepare('SELECT * FROM tl_article WHERE ID=?')
				->limit(1)
				->execute($article['articleId'])
				->fetchAssoc();
					
				if ($arrArticle['addRating']) {
					if (!$bolTemplateFixed) {
						$objTemplate->setName($objTemplate->getName().'_rateit');
						$bolTemplateFixed = true;
					}

					$ratingId = $arrArticle['id'];
					$rating = $this->loadRating($ratingId, 'article');
					$stars = !$rating ? 0 : $this->percentToStars($rating['rating']);
					$percent = round($rating['rating'], 0)."%";
						
					$article['descriptionId'] = 'rateItRating-'.$ratingId.'-description';
					$article['description'] = $this->getStarMessage($rating);
					$article['rateItID'] = 'rateItRating-'.$ratingId.'-article-'.$stars.'_'.$this->intStars;
					$article['rateit_class'] = 'rateItRating';
					$article['itemreviewed'] = $rating['title'];
					$article['actRating'] = $this->percentToStars($rating['rating']);
					$article['maxRating'] = $this->intStars;
					$article['votes'] = $rating[totalRatings];
						
					if ($this->strTextPosition == "before") {
						$article['showBefore'] = true;
					}
					else if ($this->strTextPosition == "after") {
						$article['showAfter'] = true;
					}
						
					if ($arrArticle['rateit_position'] == 'before') {
						$article['rateit_rating_before'] = true;
					} else if ($arrArticle['rateit_position'] == 'after') {
						$article['rateit_rating_after'] = true;
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
					
				$arrArticles[] = $article;
			}
			$objTemplate->articles = $arrArticles;
		}
		return $objTemplate;
	}

	private function doGallery($objTemplate) {
		$arrGallery = $this->Database->prepare('SELECT * FROM tl_content WHERE ID=?')
						   ->limit(1)
						   ->execute($objTemplate->id)
						   ->fetchAssoc();
			
		if ($arrGallery['rateit_active']) {
			$arrRating = array();
			
			if (version_compare(VERSION, '3.2', '>=')) {
				$objFiles = \FilesModel::findMultipleByUuids(deserialize($arrGallery['multiSRC']));
			} else {
				$objFiles = \FilesModel::findMultipleByIds(deserialize($arrGallery['multiSRC']));
			}

			if ($objFiles !== null) {
				// Get all images
				while ($objFiles->next()) {
					// Continue if the files has been processed or does not exist 
					if (isset($arrRating[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path)) {
						continue;
					}
				
					// Single files
					if ($objFiles->type == 'file') {
						$objFile = new \File($objFiles->path, true);
				
						if (!$objFile->isGdImage) {
							continue;
						}
				
					   $this->addRatingForImage($arrRating, $arrGallery['id'], $objFiles->id, $objFile->path);
					}
					// Folders
					else {
						if (version_compare(VERSION, '3.2', '>=')) {
							$objSubfiles = \FilesModel::findByPid($objFiles->uuid);
						} else {
							$objSubfiles = \FilesModel::findByPid($objFiles->id);
						}
				
						if ($objSubfiles === null) {
							continue;
						}
				
						while ($objSubfiles->next()) {
							// Skip subfolders
							if ($objSubfiles->type == 'folder') {
								continue;
							}
				
							$objFile = new \File($objSubfiles->path, true);
				
							if (!$objFile->isGdImage) {
								continue;
							}
				
						   $this->addRatingForImage($arrRating, $arrGallery['id'], $objSubfiles->id, $objSubfiles->path);
						}
					}
				}
			}

			$objTemplate->arrRating = $arrRating;

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

		return $objTemplate;
	}
	
	private function addRatingForImage(&$arrRating, $galleryId, $picId, $picPath) {
		$ratingId = $galleryId.'|'.$picId;
		$rating = $this->loadRating($ratingId, 'galpic');
		$stars = !$rating ? 0 : $this->percentToStars($rating['rating']);
		$percent = round($rating['rating'], 0)."%";
			
		$arrRating[$picPath] = array();
		$arrRating[$picPath]['descriptionId'] = 'rateItRating-'.$ratingId.'-description';
		$arrRating[$picPath]['description'] = $this->getStarMessage($rating);
		$arrRating[$picPath]['rateItID'] = 'rateItRating-'.$ratingId.'-galpic-'.$stars.'_'.$this->intStars;
		$arrRating[$picPath]['rateit_class'] = 'rateItRating';
		$arrRating[$picPath]['itemreviewed'] = $rating['title'];
		$arrRating[$picPath]['actRating'] = $this->percentToStars($rating['rating']);
		$arrRating[$picPath]['maxRating'] = $this->intStars;
		$arrRating[$picPath]['votes'] = $rating[totalRatings];
		
		if ($this->strTextPosition == "before") {
			$arrRating[$picPath]['showBefore'] = true;
		}
		else if ($this->strTextPosition == "after") {
			$arrRating[$picPath]['showAfter'] = true;
		}
	}
}
?>