<?php

/**
 * Extend tl_page
 */

$GLOBALS['TL_DCA']['tl_page']['config']['onsubmit_callback'][] = ['tl_page_rateit', 'insert'];
$GLOBALS['TL_DCA']['tl_page']['config']['ondelete_callback'][] = ['tl_page_rateit', 'delete'];

/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'addRating';
foreach ($GLOBALS['TL_DCA']['tl_page']['palettes'] as $keyPalette => $valuePalette) {
    // Skip if we have a array or the palettes for subselections
    if (is_array($valuePalette) || $keyPalette == "__selector__" || $keyPalette == "root" || $keyPalette == "forward" || $keyPalette == "redirect") {
        continue;
    }

    $valuePalette .= ';{rateit_legend:hide},addRating';

    // Write new entry back in the palette
    $GLOBALS['TL_DCA']['tl_page']['palettes'][$keyPalette] = $valuePalette;
}

/**
 * Add subpalettes to tl_page
 */
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['addRating'] = 'rateit_position';

// Fields
$GLOBALS['TL_DCA']['tl_page']['fields']['addRating'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['addRating'],
        'exclude'   => true,
        'inputType' => 'checkbox',
        'sql'       => "char(1) NOT NULL default ''",
        'eval'      => ['tl_class' => 'w50 m12', 'submitOnChange' => true]
    ];

$GLOBALS['TL_DCA']['tl_page']['fields']['rateit_position'] =
    [
        'label'     => &$GLOBALS['TL_LANG']['tl_page']['rateit_position'],
        'default'   => 'before',
        'exclude'   => true,
        'inputType' => 'select',
        'options'   => ['after', 'before'],
        'reference' => &$GLOBALS['TL_LANG']['tl_page'],
        'sql'       => "varchar(6) NOT NULL default ''",
        'eval'      => ['mandatory' => true, 'tl_class' => 'w50']
    ];

class tl_page_rateit extends \HeimrichHannot\RateItBundle\DcaHelper
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
        return $this->insertOrUpdateRatingKey($dc, 'page', $dc->activeRecord->title);
    }

    public function delete(\DC_Table $dc)
    {
        return $this->deleteRatingKey($dc, 'page');
    }
}