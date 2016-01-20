<?php

namespace AppBundle\Controller;

use AppBundle\Service\BonjourMadameService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @template
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * @Route("/excuses", name="excuse")
     */
    public function excuseAction(Request $request)
    {

    }
}
