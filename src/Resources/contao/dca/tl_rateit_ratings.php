<?php

/**
 * Table tl_rateit_items
 */
$GLOBALS['TL_DCA']['tl_rateit_ratings'] = [
    'config' => [
        'dataContainer' => 'Table',
        'ptable'        => 'tl_rateit_items',
        'switchToEdit'  => false,
        'sql'           => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ]
        ]
    ],

    'fields' => [
        'id'         => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'pid'        => [
            'foreignKey' => 'tl_rateit_items.id',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'lazy']
        ],
        'ip_address' => [
            'sql' => "varchar(50) NULL"
        ],
        'memberid'   => [
            'sql' => "int(10) unsigned NULL"
        ],
        'rating'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'createdat'  => [
            'sql' => "int(10) NOT NULL default '0'"
        ]
    ]
];
