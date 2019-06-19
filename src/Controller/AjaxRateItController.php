<?php

namespace HeimrichHannot\RateItBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use HeimrichHannot\RateItBundle\RateIt;

class AjaxRateItController extends Controller
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
