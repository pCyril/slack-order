<?php

namespace AppBundle\Controller;

use AppBundle\Service\BagelCommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class BagelController extends Controller
{
    /**
     * @Route("/bagel", name="bagel")
     */
    public function bagelAction(Request $request)
    {
        if ($request->get('token') !== $this->getParameter('bagel_command_token')) {
            throw new \InvalidArgumentException('Bad token');
        }

        /** @var BagelCommandService $bagelCommandService */
        $bagelCommandService = $this->get('bonjour_madame_sevice');

        $text = $request->get('text');

        $text = !empty($text) ? $text : BagelCommandService::BAGEL_COMMAND_HELP;

        $textExploded = explode(' ', $text);

        switch($textExploded[0]) {
            case BagelCommandService::BAGEL_COMMAND_HELP:
            default:
                $data = $bagelCommandService->help();
                break;
            case BagelCommandService::BAGEL_COMMAND_CANCEL:
                $data = $bagelCommandService->cancelOrder($request->get('user_name'));
                break;
            case BagelCommandService::BAGEL_COMMAND_LIST:
                $data = $bagelCommandService->orderList();
                break;
            case BagelCommandService::BAGEL_COMMAND_ORDER:
                unset($textExploded[0]);
                $data = $bagelCommandService->addOrder($request->get('user_name'), implode(" ", $textExploded));
                break;
        }

        return new JsonResponse($data);
    }

}
