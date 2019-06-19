<?php

namespace HeimrichHannot\RateItBundle;

/**
 * Class RateItCE
 */
class RateItCE extends RateItHybrid
{

	/**
	 * Initialize the controller
	 */
	public function __construct($objElement) {
		parent::__construct($objElement);
	}

	protected function getType() {
	   return 'ce';
	}
}

?>