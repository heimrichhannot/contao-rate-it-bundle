<?php 

namespace HeimrichHannot\RateItBundle;

/**
 * Class DcaHelper
 */
class DcaHelper extends \Backend
{

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}
   
   /**
	 * Return all navigation templates as array
	 * @param DataContainer
	 * @return array
	 */
	public function getRateItTemplates(\DataContainer $dc)
	{
		$intPid = $dc->activeRecord->pid;

		if ($this->Input->get('act') == 'overrideAll')
		{
			$intPid = $this->Input->get('id');
		}

		return $this->getTemplateGroup('rateit_', $intPid);
	}

	/**
	 * Anlegen eines Datensatzes in der Tabelle tl_rateit_items, falls dieser noch nicht exisitiert.
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function insertOrUpdateRatingKey(\DC_Table $dc, $type, $ratingTitle) {
		if ($dc->activeRecord->rateit_active || $dc->activeRecord->addRating) {
			$actRecord = $this->Database->prepare("SELECT * FROM tl_rateit_items WHERE rkey=? and typ=?")
							->execute($dc->activeRecord->id, $type)
							->fetchAssoc();
			if (!is_array($actRecord)) {
				$arrSet = [
                    'rkey'      => $dc->activeRecord->id,
                    'tstamp'    => time(),
                    'typ'       => $type,
                    'createdat' => time(),
                    'title'     => $ratingTitle,
                    'active'    => '1'
                ];
				$insertRecord = $this->Database->prepare("INSERT INTO tl_rateit_items %s")
											   ->set($arrSet)
											   ->execute()
											   ->insertId;
			} else {
				$this->Database->prepare("UPDATE tl_rateit_items SET active='1', title=? WHERE rkey=? and typ=?")
							   ->execute($ratingTitle, $dc->activeRecord->id, $type)
							   ->updatedId;
			}
		} else {
			$this->Database->prepare("UPDATE tl_rateit_items SET active='' WHERE rkey=? and typ=?")
						   ->execute($dc->activeRecord->id, $type)
						   ->updatedId;
				
		}
		return true;
	}

	/**
	 * Löschen eines Datensatzes aus der Tabelle tl_rateit_items.
	 * @param mixed
	 * @param object
	 * @return string
	 */
	public function deleteRatingKey(\DC_Table $dc, $type)
	{
		$this->Database->prepare("DELETE FROM tl_rateit_items WHERE rkey=? and typ=?")
		               ->execute($dc->activeRecord->id, $type);
		return true;
	}
}
?>