<?php 

namespace HeimrichHannot\RateItBundle;

class RateItRating extends RateItFrontend { 
	
	/**
	 * RatingKey
	 * @var int
	 */
	public $rkey = 0;
	
	public $ratingType = 'page';
	
	/**
	 * Initialize the controller
	 */
	public function __construct($objElement= []) {
		parent::__construct($objElement);
	}

	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate() {
		parent::generate();
	}
	
	/**
	 * Compile
	 */
	protected function compile()
	{
		$this->loadLanguageFile('default');

		$this->Template = new \FrontendTemplate($this->strTemplate);
		$this->Template->setData($this->arrData);

		$rating = $this->loadRating($this->rkey, $this->ratingType);
		$ratingId = $this->rkey;
		$stars = !$rating ? 0 : $this->percentToStars($rating['rating']);
		$percent = round($rating['rating'], 0)."%";
		
		$this->Template->descriptionId = 'rateItRating-'.$ratingId.'-description';
		$this->Template->description = $this->getStarMessage($rating);
		$this->Template->id = 'rateItRating-'.$ratingId.'-'.$this->ratingType.'-'.$stars.'_'.$this->intStars;
		$this->Template->class = 'rateItRating';
		$this->Template->itemreviewed = $rating['title'];
		$this->Template->actRating = $this->percentToStars($rating['rating']);
		$this->Template->maxRating = $this->intStars;
		$this->Template->votes = $rating[totalRatings];
		
		if ($this->strTextPosition == "before") {
			$this->Template->showBefore = true;
		}
		else if ($this->strTextPosition == "after") {
			$this->Template->showAfter = true;
		}
		
		return $this->Template->parse();
	}
	
	public function output() {
	   return $this->compile();
	}
}

?>
