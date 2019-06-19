<?php

$GLOBALS['TL_DCA']['tl_faq']['config']['onsubmit_callback'][] = ['tl_faq_rating', 'insert'];
$GLOBALS['TL_DCA']['tl_faq']['config']['ondelete_callback'][] = ['tl_faq_rating', 'delete'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_faq']['palettes']['__selector__'][] = 'addRating';
$GLOBALS['TL_DCA']['tl_faq']['palettes']['default']        = $GLOBALS['TL_DCA']['tl_faq']['palettes']['default'] . ';{rating_legend:hide},addRating';

/**
 * Add subpalettes to tl_article
 */
$GLOBALS['TL_DCA']['tl_faq']['subpalettes']['addRating'] = 'rateit_position';

// Fields
$GLOBALS['TL_DCA']['tl_faq']['fields']['addRating'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_faq']['addRating'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
        'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true]
    ];

$GLOBALS['TL_DCA']['tl_faq']['fields']['rateit_position'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_faq']['rateit_position'],
        'default'   => 'before',
        'exclude'   => true,
        'inputType' => 'select',
        'options'   => ['after', 'before'],
        'reference' => &$GLOBALS['TL_LANG']['tl_faq'],
        'sql'       => "varchar(6) NOT NULL default ''",
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50']
    ];

class tl_faq_rating extends \HeimrichHannot\RateItBundle\DcaHelper
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function insert(\DC_Table $dc)
    {
        return $this->insertOrUpdateRatingKey($dc, 'faq', $dc->activeRecord->question);
    }

    public function delete(\DC_Table $dc)
    {
        return $this->deleteRatingKey($dc, 'faq');
    }
}