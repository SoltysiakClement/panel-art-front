<?php

namespace App\Controller;

use App\Service\MyCacheService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    #[Route('/', name: 'app_dashboard')]
    public function index(MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        return $this->render('dashboard/index.html.twig', [
        ]);
    }
}
