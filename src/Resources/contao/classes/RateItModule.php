<?php

namespace HeimrichHannot\RateItBundle;

/**
 * Class RateItModule
 */
class RateItModule extends RateItHybrid
{

	/**
	 * Initialize the controller
	 */
	public function __construct($objElement) {
		parent::__construct($objElement);
	}

	protected function getType() {
	   return 'module';
	}
}

?>