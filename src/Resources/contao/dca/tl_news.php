<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_news']['config']['onsubmit_callback'][] = ['tl_news_rating', 'insert'];
$GLOBALS['TL_DCA']['tl_news']['config']['ondelete_callback'][] = ['tl_news_rating', 'delete'];

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_news']['palettes']['__selector__'][] = 'addRating';

PaletteManipulator::create()
    ->addLegend('rateit_legend', 'image_legend')
    ->addField('addRating', 'rateit_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news');

/*
 * Add subpalettes to tl_article
 */
$GLOBALS['TL_DCA']['tl_news']['subpalettes']['addRating'] = 'rateit_position';

// Fields
$GLOBALS['TL_DCA']['tl_news']['fields']['addRating'] =
    [
        'label' => &$GLOBALS['TL_LANG']['tl_news']['addRating'],
        'exclude' => true,
        'inputType' => 'checkbox',
        'sql' => "char(1) NOT NULL default ''",
        'eval' => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
    ];

$GLOBALS['TL_DCA']['tl_news']['fields']['rateit_position'] =
    [
        'label' => &$GLOBALS['TL_LANG']['tl_news']['rateit_position'],
        'default' => 'before',
        'exclude' => true,
        'inputType' => 'select',
        'options' => ['after', 'before'],
        'reference' => &$GLOBALS['TL_LANG']['tl_news'],
        'sql' => "varchar(6) NOT NULL default ''",
        'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
    ];

class tl_news extends \HeimrichHannot\RateItBundle\DcaHelper
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function insert(DC_Table $dc)
    {
        return $this->insertOrUpdateRatingKey($dc, 'news', $dc->activeRecord->headline);
    }

    public function delete(DC_Table $dc)
    {
        return $this->deleteRatingKey($dc, 'news');
    }
}
