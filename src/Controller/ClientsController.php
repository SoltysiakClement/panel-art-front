<?php

namespace App\Controller;

use App\Service\MyCacheService;
use App\Form\Clients\ClientsFormType;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/clients')]
class ClientsController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/', name: 'app_clients')]
    public function index(MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $url_artworks = $_ENV['API_URL'] . '/peintures';
        $reponseData = $this->httpClient->request('GET', $url_artworks);
        
        return $this->render('clients/index.html.twig', [
            'user_name' => $user['firstname'].' '.$user['lastname']
        ]);
    }
    
    #[Route('/detail', name: 'app_detail_client')]
    public function detail(MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $url_artworks = $_ENV['API_URL'] . '/peintures';
        $reponseData = $this->httpClient->request('GET', $url_artworks);
        $artworks = $reponseData->toArray();
        
        return $this->render('clients/detail.html.twig', [
            'user_name' => $user['firstname'].' '.$user['lastname'],
            'artworks' => $artworks
        ]);
    }

    #[Route('/add', name: 'app_add_client')]
    public function add(Request $request,MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ClientsFormType::class);
        $form->handleRequest($request);
        
        return $this->render('clients/add.html.twig', [
            'user_name' => $user['firstname'].' '.$user['lastname'],
            'form' => $form->createView()
        ]);
    }
}
