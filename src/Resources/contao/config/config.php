<?php

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['generatePage'][]      = ['HeimrichHannot\RateItBundle\RateItPage', 'generatePage'];
$GLOBALS['TL_HOOKS']['parseArticles'][]     = ['HeimrichHannot\RateItBundle\RateItNews', 'parseArticle'];
$GLOBALS['TL_HOOKS']['getContentElement'][] = ['HeimrichHannot\RateItBundle\RateItFaq', 'getContentElementRateIt'];
$GLOBALS['TL_HOOKS']['parseTemplate'][]     = ['HeimrichHannot\RateItBundle\RateItArticle', 'parseTemplateRateIt'];

/**
 * Back end modules
 */
array_insert($GLOBALS['BE_MOD']['content'], -1,
    [
        'rateit' => [
            'callback'   => 'HeimrichHannot\RateItBundle\RateItBackendModule',
            'icon'       => 'bundles/contaorateit/images/icon.gif',
            'stylesheet' => 'bundles/contaorateit/css/backend.css',
            'javascript' => 'bundles/contaorateit/js/RateItBackend.js',
        ]
    ]);

/**
 * frontend moduls
 */
$GLOBALS['FE_MOD']['application']['rateit']             = 'HeimrichHannot\RateItBundle\RateItModule';
$GLOBALS['FE_MOD']['application']['rateit_top_ratings'] = 'HeimrichHannot\RateItBundle\RateItTopRatingsModule';

/**
 * content elements
 */
$GLOBALS['TL_CTE']['includes']['rateit'] = 'HeimrichHannot\RateItBundle\RateItCE';