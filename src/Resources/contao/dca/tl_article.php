<?php

/**
 * Extend tl_article
 */

$GLOBALS['TL_DCA']['tl_article']['config']['onsubmit_callback'][] = ['tl_article_rating', 'insert'];
$GLOBALS['TL_DCA']['tl_article']['config']['ondelete_callback'][] = ['tl_article_rating', 'delete'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_article']['palettes']['__selector__'][] = 'addRating';
$GLOBALS['TL_DCA']['tl_article']['palettes']['default']        = $GLOBALS['TL_DCA']['tl_article']['palettes']['default'] . ';{rateit_legend:hide},addRating';

/**
 * Add subpalettes to tl_article
 */
$GLOBALS['TL_DCA']['tl_article']['subpalettes']['addRating'] = 'rateit_position,rateit_template';

// Fields
$GLOBALS['TL_DCA']['tl_article']['fields']['addRating'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_article']['addRating'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
        'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true]
    ];

$GLOBALS['TL_DCA']['tl_article']['fields']['rateit_position'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_article']['rateit_position'],
        'default'   => 'before',
        'exclude'   => true,
        'inputType' => 'select',
        'options'   => ['after', 'before'],
        'reference' => &$GLOBALS['TL_LANG']['tl_article'],
        'sql'       => "varchar(6) NOT NULL default ''",
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50']
    ];

class tl_article_rating extends \HeimrichHannot\RateItBundle\DcaHelper
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
        return $this->insertOrUpdateRatingKey($dc, 'article', $dc->activeRecord->title);
    }

    public function delete(\DC_Table $dc)
    {
        return $this->deleteRatingKey($dc, 'article');
    }
}