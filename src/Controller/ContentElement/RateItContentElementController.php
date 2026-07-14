<?php

/*
 * Copyright (c) 2024 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use HeimrichHannot\RateItBundle\Twig\Runtime\RateItRuntime;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Renders a standalone rating widget content element (former TL_CTE "rateit").
 */
#[AsContentElement(type: 'rateit', category: 'includes')]
class RateItContentElementController extends AbstractContentElementController
{
    public function __construct(private readonly RateItRuntime $rateItRuntime)
    {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        return new Response($this->rateItRuntime->renderRating($model->id, 'ce'));
    }
}
