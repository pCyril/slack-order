<?php

namespace AppBundle\Controller;

use AppBundle\Service\OrderCommandService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller
{
    /**
     * @Route("/order", name="order")
     */
    public function orderAction(Request $request)
    {
            if ($request->get('token') !== $this->getParameter('order_command_token')) {
            throw new \InvalidArgumentException('Bad token');
        }

        /** @var OrderCommandService $orderCommandService */
        $orderCommandService = $this->get('order_command_service');

        $text = $request->get('text');

        $text = !empty($text) ? $text : OrderCommandService::ORDER_COMMAND_HELP;

        $textExploded = explode(' ', $text);

        switch($textExploded[0]) {
            case OrderCommandService::ORDER_COMMAND_HELP:
            default:
                $data = $orderCommandService->help();
                break;
            case OrderCommandService::ORDER_COMMAND_CANCEL:
                $data = $orderCommandService->cancelOrder($request->get('user_name'));
                break;
            case OrderCommandService::ORDER_COMMAND_LIST:
                $data = $orderCommandService->orderList();
                break;
            case OrderCommandService::ORDER_COMMAND_ORDER:
                unset($textExploded[0]);
                $data = $orderCommandService->addOrder($request->get('user_name'), implode(" ", $textExploded));
                break;
            case OrderCommandService::ORDER_COMMAND_SEND:
                unset($textExploded[0]);
                $data = $orderCommandService->send($request->get('user_name'), $textExploded);
                break;
        }

        return new JsonResponse($data);
    }

}
