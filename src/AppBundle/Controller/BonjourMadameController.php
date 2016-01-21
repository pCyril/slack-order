<?php

namespace AppBundle\Controller;

use AppBundle\Service\BonjourMadameService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BonjourMadameController extends Controller
{

    /**
     * @Route("/bonjour", name="bonjour")
     */
    public function lastBonjourMadameAction(Request $request)
    {
        if ($request->get('token') !== $this->getParameter('bonjour_madame_token')) {
            throw new \InvalidArgumentException('Bad token');
        }
        /** @var BonjourMadameService $bonjourMadameService */
        $bonjourMadameService = $this->get('bonjour_madame_service');
        $image = $bonjourMadameService->getLastBonjourMadameImage();

        return new JsonResponse([
            'text' => 'Vicieux !!!',
            'attachments' => [
                [
                    'fallback' => 'Fail ?',
                    'text' => 'Dis bonjour à la dame',
                    'color' => '#E71840',
                    'image_url' => $image,
                ],
            ],
        ]);
    }
}
