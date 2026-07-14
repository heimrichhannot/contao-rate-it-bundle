<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Callbacks are registered via HeimrichHannot\RateItBundle\DataContainer\RatingItemContainer (#[AsCallback]).

$GLOBALS['TL_DCA']['tl_news']['palettes']['__selector__'][] = 'addRating';

PaletteManipulator::create()
    ->addLegend('rateit_legend', 'image_legend')
    ->addField('addRating', 'rateit_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news');

$GLOBALS['TL_DCA']['tl_news']['subpalettes']['addRating'] = 'rateit_position';

$GLOBALS['TL_DCA']['tl_news']['fields']['addRating'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['addRating'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
    'eval' => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
];

$GLOBALS['TL_DCA']['tl_news']['fields']['rateit_position'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_news']['rateit_position'],
    'default' => 'before',
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['after', 'before'],
    'reference' => &$GLOBALS['TL_LANG']['tl_news'],
    'sql' => "varchar(6) NOT NULL default ''",
    'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
];
