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

        /*if ($request->get('token') !== $config['order']['token']) {
            throw new \InvalidArgumentException('Bad token');
        }*/

        $orderCommandService = new OrderCommandService(
            $app['doctrine.manager'], $app['mailer'], $app['twig'], $app['translator'], $config['order']);

        $text = $request->get('text');
        $text = !empty($text) ? $text : $app['translator']->trans('command.options.help');
        $textExploded = explode(' ', $text);
        switch($textExploded[0]) {
            case $app['translator']->trans('command.options.help'):
            default:
                $data = $orderCommandService->menu();
                break;
            case $app['translator']->trans('command.options.cancel'):
                $data = $orderCommandService->cancelOrder($request->get('user_name'));
                break;
            case $app['translator']->trans('command.options.list'):
                $data = $orderCommandService->orderList();
                break;
            case $app['translator']->trans('command.options.place'):
                unset($textExploded[0]);
                $data = $orderCommandService->addOrder($request->get('user_name'), implode(" ", $textExploded));
                break;
            case $app['translator']->trans('command.options.send'):
                unset($textExploded[0]);
                $data = $orderCommandService->send($request->get('user_name'), $textExploded);
                break;
            case $app['translator']->trans('command.options.history'):
                $data = $orderCommandService->historyList($request->get('user_name'));
                break;
            case $app['translator']->trans('command.options.menu'):
                $data = $orderCommandService->menu();
                break;
        }

        return new JsonResponse($data);
    }
}
