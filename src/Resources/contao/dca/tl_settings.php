<?php

/**
 * palettes
 */
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] .= ';{rateit_legend:hide},rating_type,rating_count,rating_textposition,rating_listsize,rating_allow_duplicate_ratings,rating_allow_duplicate_ratings_for_members,rating_template,rating_description';

/**
 * fields
 */
$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_type'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_type'],
        'default'   => 'hearts',
        'exclude'   => true,
        'inputType' => 'select',
        'options'   => ['hearts', 'stars'],
        'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50']
    ];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_count'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_count'],
        'default'   => '5',
        'exclude'   => true,
        'inputType' => 'select',
        'options'   => ['1', '5', '10'],
        'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50']
    ];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_textposition'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_textposition'],
        'default'   => 'after',
        'exclude'   => true,
        'inputType' => 'select',
        'options'   => ['after', 'before'],
        'reference' => &$GLOBALS['TL_LANG']['tl_settings'],
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50']
    ];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_listsize'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_listsize'],
        'exclude'   => true,
        'default'   => 10,
        'inputType' => 'text',
        'eval'      => ['mandatory' => false, 'maxlength' => 4, 'tl_class' => 'w50']
    ];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_allow_duplicate_ratings'] =
    [
        'exclude'   => true,
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['allow_duplicate_ratings'],
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 m12']
    ];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_allow_duplicate_ratings_for_members'] =
    [
        'exclude'   => true,
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['allow_duplicate_ratings_for_members'],
        'inputType' => 'checkbox',
        'eval'      => ['tl_class' => 'w50 m12']
    ];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_template'] =
    [
        'label'            => &$GLOBALS['TL_LANG']['tl_settings']['rating_template'],
        'default'          => 'rateit_default',
        'exclude'          => true,
        'inputType'        => 'select',
        'options_callback' => ['tl_settings_rateit', 'getRateItTemplates'],
        'eval'             => ['mandatory' => true, 'tl_class' => 'w50']
    ];

$GLOBALS['TL_DCA']['tl_settings']['fields']['rating_description'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_settings']['rating_description'],
        'exclude'   => true,
        'default'   => '%current%/%max% %type% (%count% [Stimme|Stimmen])',
        'inputType' => 'text',
        'eval'      => ['mandatory' => true, 'allowHtml' => true, 'tl_class' => 'w50']
    ];