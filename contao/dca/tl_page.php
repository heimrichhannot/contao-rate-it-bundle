<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

// Callbacks are registered via HeimrichHannot\RateItBundle\DataContainer\RatingItemContainer (#[AsCallback]).

$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'addRating';

foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $keyPalette => $valuePalette) {
    if (\is_array($valuePalette) || \in_array($keyPalette, ['__selector__', 'root', 'forward', 'redirect'], true)) {
        continue;
    }

    $GLOBALS['TL_DCA']['tl_page']['palettes'][$keyPalette] = $valuePalette.';{rateit_legend:hide},addRating';
}

$GLOBALS['TL_DCA']['tl_page']['subpalettes']['addRating'] = 'rateit_position';

$GLOBALS['TL_DCA']['tl_page']['fields']['addRating'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['addRating'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
    'eval' => ['tl_class' => 'w50 m12', 'submitOnChange' => true],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['rateit_position'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_page']['rateit_position'],
    'default' => 'before',
    'exclude' => true,
    'inputType' => 'select',
    'options' => ['after', 'before'],
    'reference' => &$GLOBALS['TL_LANG']['tl_page'],
    'sql' => "varchar(6) NOT NULL default ''",
    'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
];
