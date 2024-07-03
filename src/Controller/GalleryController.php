<?php

namespace App\Controller;

use App\Service\MyCacheService;
use App\Form\Artworks\ArtworksFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class GalleryController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }
    
    #[Route('/gallery', name: 'app_gallery')]
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
            'user_name' => $user['firstname'].' '.$user['lastname']
        ]);
    }

    #[Route('/gallery/add', name: 'app_gallery_add')]
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
            $url = $_ENV['API_URL'] . '/peintures';

            $response = $this->httpClient->request('POST', $url, [
                'json' => $artworkData
            ]);
            dd($response);

            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 201) {
                $this->addFlash('success', 'Artwork added successfully!');
                return $this->redirectToRoute('app_gallery');
            } else {
                // Handle non-successful responses
                $this->addFlash('error', 'Failed to add artwork due to API error.');
            }

        }

        // Rediriger vers la page de formulaire en cas d'erreur ou afficher à nouveau le formulaire
        return $this->render('gallery/add.html.twig', [
            'user_name' => $user['firstname'] . ' ' . $user['lastname'],
            'form' => $form->createView()
        ]);
    }

}
