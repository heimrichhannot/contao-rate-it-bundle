<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

// Callbacks (incl. the gallery handling) are registered via
// HeimrichHannot\RateItBundle\DataContainer\RatingItemContainer (#[AsCallback]).

$GLOBALS['TL_DCA']['tl_content']['palettes']['rateit'] = '{type_legend},type,rateit_title;{rateit_legend},rateit_active;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

if (isset($GLOBALS['TL_DCA']['tl_content']['palettes']['gallery'])) {
    $GLOBALS['TL_DCA']['tl_content']['palettes']['gallery'] .= ';{rateit_legend},rateit_active';
}

$GLOBALS['TL_DCA']['tl_content']['fields']['rateit_title'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['rateit_title'],
    'exclude' => true,
    'inputType' => 'text',
    'sql' => "varchar(255) NOT NULL default ''",
    'eval' => ['mandatory' => true, 'maxlength' => 255],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['rateit_active'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['rateit_active'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
    'eval' => ['tl_class' => 'w50'],
];
