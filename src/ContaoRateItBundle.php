<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle;

use HeimrichHannot\RateItBundle\DependencyInjection\ContaoRateItExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoRateItBundle extends Bundle
{
    public function getContainerExtension(): ?ExtensionInterface
    {
        return new ContaoRateItExtension();
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
