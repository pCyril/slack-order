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

    /**
     * @Route("/bonjour", name="bonjour")
     */
    public function lastBonjourMadameAction(Request $request)
    {
        /*if ($request->get('token') !== 'wReTCvIBqSJoD3mKzXWWWHsx') {
            throw new \InvalidArgumentException('Bad token');
        }*/
        /** @var BonjourMadameService $bonjourMadameService */
        $bonjourMadameService = $this->get('bonjour_madame_sevice');
        $image = $bonjourMadameService->getLastBonjourMadameImage();

        return new JsonResponse([
            'text' => 'Vicieux !!!',
            'attachments' => [
                [
                    'fallback' => 'Tiens mais chut ;)',
                    'text' => 'Tiens mais chut ;)',
                    'color' => '#E71840',
                    'image_url' => $image,
                ],
            ],
        ]);
    }
}
