<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

// Callbacks are registered via HeimrichHannot\RateItBundle\DataContainer\RatingItemContainer (#[AsCallback]).

$GLOBALS['TL_DCA']['tl_faq']['palettes']['__selector__'][] = 'addRating';
$GLOBALS['TL_DCA']['tl_faq']['palettes']['default'] .= ';{rating_legend:hide},addRating';

$GLOBALS['TL_DCA']['tl_faq']['subpalettes']['addRating'] = 'rateit_position';

$GLOBALS['TL_DCA']['tl_faq']['fields']['addRating'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_faq']['addRating'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
    'eval' => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
];

$GLOBALS['TL_DCA']['tl_faq']['fields']['rateit_position'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_faq']['rateit_position'],
    'default' => 'before',
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['after', 'before'],
    'reference' => &$GLOBALS['TL_LANG']['tl_faq'],
    'sql' => "varchar(6) NOT NULL default ''",
    'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
];
