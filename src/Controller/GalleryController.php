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

        $urlVentes = $_ENV['API_URL'] . '/ventes';
        $ventes = $this->httpClient->request('GET', $urlVentes)->toArray();

        $mesVentes = [];

        foreach ($ventes as $vente){
            if ($vente['idClient'] == $user['id']){
                $mesVentes[] = $vente;
            }
        }


        return $this->render('gallery/index.html.twig', [
            'user'=> $user,
            'artworks' => $artworks,
            'user_initials' => $cacheService->getInitials($user['firstname'], $user['lastname']),
            'user_name' => $user['firstname'].' '.$user['lastname'],
            'mesVentes' => $mesVentes,
        ]);
    }

    #[Route('/add', name: 'app_gallery_add')]
    public function addArtwork(Request $request, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!in_array('ROLE_PEINTRE', $user['roles'])) {
            return $this->redirectToRoute('app_dashboard');
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
            'user'=> $user,
            'user_name' => $user['firstname'] . ' ' . $user['lastname'],
            'user_initials' => $cacheService->getInitials($user['firstname'], $user['lastname']),
            'form' => $form->createView()
        ]);
    }

    #[Route('/edit/{id}', name: 'app_gallery_edit')]
    public function editArtwork(int $id, Request $request, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!in_array('ROLE_PEINTRE', $user['roles'])) {
            return $this->redirectToRoute('app_dashboard');
        }

        $urlPeinture = $_ENV['API_URL'] . '/peintures/' . $id;

        // Récupérer l'œuvre existante
        try {
            $response = $this->httpClient->request('GET', $urlPeinture);
            $artworkData = $response->toArray();

            if (isset($artworkData['createdAt'])) {
                $artworkData['createdAt'] = new \DateTime($artworkData['createdAt']);
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while fetching the artwork: ' . $e->getMessage());
            return $this->redirectToRoute('app_gallery');
        }

        $form = $this->createForm(ArtworksFormType::class, $artworkData);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $artworkData = $form->getData();

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
                $response = $this->httpClient->request('PUT', $urlPeinture, [
                    'json' => $data,
                ]);

                return $this->redirectToRoute('app_gallery');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            }
        }

        // Rediriger vers la page de formulaire en cas d'erreur ou afficher à nouveau le formulaire
        return $this->render('gallery/edit.html.twig', [
            'user' => $user,
            'user_name' => $user['firstname'] . ' ' . $user['lastname'],
            'user_initials' => $cacheService->getInitials($user['firstname'], $user['lastname']),
            'form' => $form->createView()
        ]);
    }

    #[Route('/delete/{id}', name: 'app_gallery_delete')]
    public function delete(int $id, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        if (!in_array('ROLE_PEINTRE', $user['roles'])) {
            return $this->redirectToRoute('app_dashboard');
        }

        $url = $_ENV['API_URL'].'/peintures/' . $id;

        try {
            $response = $this->httpClient->request('DELETE', $url);
            $statusCode = $response->getStatusCode();

            if ($statusCode === 204) {
                $this->addFlash('success', 'Sale deleted successfully.');
            } else {
                $this->addFlash('error', 'Failed to delete sale.');
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_gallery');
    }

    #[Route('/buy/{idPeinture}', name: 'app_gallery_buy')]
    public function buy(int $idPeinture, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $url = $_ENV['API_URL'].'/ventes';

        $data = [
            'idClient' => $user['id'],
            'idPeinture' => $idPeinture,
            'amount' => 1,
            'status' => 'disponible',
        ];

        try {
            $response = $this->httpClient->request('POST', $url, [
                'json' => $data,
            ]);

            return $this->redirectToRoute('app_detail_client', ['email' => $user['email']]);
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
        }
        return $this->redirectToRoute('app_detail_client', ['email' => $user['email']]);
    }


}
