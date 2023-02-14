<?php

/*
 * Copyright (c) 2023 Heimrich & Hannot GmbH
 *
 * @license LGPL-3.0-or-later
 */

namespace HeimrichHannot\RateItBundle\Controller;

use HeimrichHannot\RateItBundle\RateIt;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AjaxRateItController extends AbstractController
{
    /**
     * Handles rating requests.
     *
     * @Route("/rateit", name="ajax_rateit", defaults={"_scope" = "frontend", "_token_check" = false})
     */
    public function ajaxAction()
    {
        $this->container->get('contao.framework')->initialize();

        $controller = new RateIt();

        $response = $controller->doVote();
        $response->send();

        return new Response(null);
    }
}
