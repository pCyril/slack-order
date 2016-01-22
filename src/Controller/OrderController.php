<?php

namespace SlackOrder\Controller;

use SlackOrder\Application;
use SlackOrder\Service\OrderCommandService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OrderController
{
    public function orderAction(Application $app, Request $request)
    {
        $config = $app['config'];

        if ($request->get('token') !== $config['order']['token']) {
            throw new \InvalidArgumentException('Bad token');
        }

        $orderCommandService = new OrderCommandService(
            $app['doctrine.manager'], $app['mailer'], $app['twig'], $config['order']);

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
