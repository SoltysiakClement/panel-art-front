<?php

namespace App\Controller;

use App\Service\MyCacheService;
use App\Form\Artworks\ArtworksFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/gallery')]
class GalleryController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    #[Route('/', name: 'app_gallery')]
    public function index(MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $url = $_ENV['API_URL'] . '/peintures';
        $reponseData = $this->httpClient->request('GET', $url);
        $artworks = $reponseData->toArray();

        return $this->render('gallery/index.html.twig', [
            'artworks' => $artworks,
            'user_initials' => $cacheService->getInitials($user['firstname'], $user['lastname']),
            'user_name' => $user['firstname'].' '.$user['lastname']
        ]);
    }

    #[Route('/add', name: 'app_gallery_add')]
    public function addArtwork(Request $request, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ArtworksFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $artworkData = $form->getData();
            $urlPeinture = $_ENV['API_URL'] . '/peintures';

            $data = [
                'title' => $artworkData['title'],
                'height' => $artworkData['height'],
                'width' => $artworkData['width'],
                'description' => $artworkData['description'],
                'quantity' => $artworkData['quantity'],
                'createdAt' => $artworkData['createdAt']->format('Y-m-d\TH:i:s\Z'),
                'method' => $artworkData['method'],
                'prize' => $artworkData['prize'],
            ];

            try {
                $response = $this->httpClient->request('POST', $urlPeinture, [
                    'json' => $data,
                ]);


                return $this->redirectToRoute('app_gallery');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            }

        }

        // Rediriger vers la page de formulaire en cas d'erreur ou afficher à nouveau le formulaire
        return $this->render('gallery/add.html.twig', [
            'user_name' => $user['firstname'] . ' ' . $user['lastname'],
            'user_initials' => $cacheService->getInitials($user['firstname'], $user['lastname']),
            'form' => $form->createView()
        ]);
    }

}
