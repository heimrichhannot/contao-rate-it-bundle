<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

use HeimrichHannot\RateItBundle\Controller\BackendModule\RateItBackendModule;

/*
 * Backend module: rating statistics.
 *
 * Frontend modules, the content element, hooks and DCA callbacks are registered
 * via PHP attributes (AsFrontendModule / AsContentElement / AsHook / AsCallback).
 */
$GLOBALS['BE_MOD']['content']['rateit'] = [
    'tables' => [],
    'callback' => RateItBackendModule::class,
];
