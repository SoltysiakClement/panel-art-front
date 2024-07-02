<?php

namespace App\Controller;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(): Response
    {
        $user = $this->cache->get('user', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->getUser();
        });
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        return $this->render('dashboard/index.html.twig', [
        ]);
    }
}
