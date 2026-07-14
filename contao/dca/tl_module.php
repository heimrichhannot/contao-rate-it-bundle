<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

// Callbacks (incl. the rateit_template options) are registered via
// HeimrichHannot\RateItBundle\DataContainer\RatingItemContainer (#[AsCallback]).

$GLOBALS['TL_DCA']['tl_module']['palettes']['rateit'] = '{title_legend},name,rateit_title,type;{rateit_legend},rateit_active;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['rateit_top_ratings'] = '{title_legend},name,headline,type;{rateit_legend},rateit_types,rateit_toptype,rateit_count,rateit_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';

PaletteManipulator::create()
    ->addLegend('rateit_legend', 'reference_legend')
    ->addField('rateit_active', 'rateit_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('articlelist', 'tl_module');

$GLOBALS['TL_DCA']['tl_module']['fields']['rateit_title'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['rateit_title'],
    'exclude' => true,
    'inputType' => 'text',
    'sql' => "varchar(255) NOT NULL default ''",
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['rateit_active'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['rateit_active'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'sql' => "char(1) NOT NULL default ''",
    'eval' => ['tl_class' => 'w50 m12'],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['rateit_types'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['rateit_types'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options' => ['page', 'article', 'ce', 'module', 'news', 'faq', 'galpic', 'news4ward'],
    'eval' => ['multiple' => true, 'mandatory' => true],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['rateit_types'],
    'sql' => "varchar(255) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['rateit_toptype'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['rateit_toptype'],
    'exclude' => true,
    'default' => 'best',
    'inputType' => 'select',
    'options' => ['best', 'most'],
    'eval' => ['mandatory' => true, 'tl_class' => 'w50'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['rateit_toptype'],
    'sql' => "varchar(10) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['rateit_count'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['rateit_count'],
    'default' => '10',
    'exclude' => true,
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 3, 'rgxp' => 'digit', 'tl_class' => 'w50'],
    'sql' => "varchar(3) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['rateit_template'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['rateit_template'],
    'default' => 'mod_rateit_top_ratings',
    'exclude' => true,
    'inputType' => 'select',
    'eval' => ['mandatory' => true, 'tl_class' => 'w50', 'includeBlankOption' => true],
    'sql' => "varchar(255) NOT NULL default ''",
];
