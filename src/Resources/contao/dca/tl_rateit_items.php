<?php

/**
 * Table tl_rateit_items
 */
$GLOBALS['TL_DCA']['tl_rateit_items'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ctable'        => ['tl_rateit_ratings'],
        'switchToEdit'  => false,
        'sql'           => [
            'keys' => [
                'id' => 'primary'
            ]
        ]
    ],

    'fields' => [
        'id'        => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'    => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'title'     => [
            'sql' => "varchar(513) NOT NULL default ''"
        ],
        'rkey'      => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'typ'       => [
            'sql' => "varchar(32) NOT NULL default ''"
        ],
        'createdat' => [
            'sql' => "int(10) NOT NULL default '0'"
        ],
        'active'    => [
            'sql' => "char(1) NOT NULL default ''"
        ]
    ]
];