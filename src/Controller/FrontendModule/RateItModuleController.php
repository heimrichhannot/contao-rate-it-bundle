<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use HeimrichHannot\RateItBundle\Twig\Runtime\RateItRuntime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders a standalone rating widget frontend module (former FE_MOD "rateit").
 */
#[AsFrontendModule(type: 'rateit', category: 'application')]
class RateItModuleController extends AbstractFrontendModuleController
{
    public function __construct(private readonly RateItRuntime $rateItRuntime)
    {
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        return new Response($this->rateItRuntime->renderRating($model->id, 'module'));
    }
}
