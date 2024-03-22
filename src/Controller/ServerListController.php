<?php

namespace App\Controller;

use App\Service\ServerListService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ServerListController extends AbstractController
{

    public function __construct(private readonly ServerListService $serverListService, private readonly LoggerInterface $logger)
    {
    }
    #[Route('/', name: 'app_server_list_index')]
    public function index(): Response
    {
        return $this->render('server-list/index.html.twig');
    }

    #[Route('/server-list', name: 'app_server_list')]
    public function serverList(Request $request): Response
    {
        $queryParams = $request->query->all();

        $serverList = $this->serverListService->getServerList($queryParams);

        return $this->render('server-list/server.html.twig', [
            'serverList' => $serverList
        ]);
    }

    #[Route('/server-list/filters', name: 'app_server_list_filters')]
    public function serverFilters(): Response
    {
        $serverFilters = $this->serverListService->getServerFilters();

        return $this->render('server-list/filters.html.twig', [
            'serverFilters' => $serverFilters
        ]);
    }

}
