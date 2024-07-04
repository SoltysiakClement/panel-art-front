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

        $urlClients = $_ENV['API_URL'] . '/clients';

        try {
            $reponseDataClients = $this->httpClient->request('GET', $urlClients);
            $dataClients = $reponseDataClients->toArray();
        } catch (\Exception $e) {
            $dataClients = [];
        }

        $nbClients = count($dataClients);

        $url_artworks = $_ENV['API_URL'] . '/peintures';
        $reponseData = $this->httpClient->request('GET', $url_artworks);
        
        return $this->render('clients/index.html.twig', [
            'user_name' => $user['firstname'].' '.$user['lastname'],
            'nbClients' => $nbClients,
            'clients' => $dataClients,
        ]);
    }
    
    #[Route('/detail/{email}', name: 'app_detail_client')]
    public function detail(string $email ,MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $url_artworks = $_ENV['API_URL'] . '/peintures';
        $reponseData = $this->httpClient->request('GET', $url_artworks);
        $artworks = $reponseData->toArray();


        $urlClient = $_ENV['API_URL'] . '/clients/' . $email;
        $urlPeinture = $_ENV['API_URL'] . '/peintures';
        $urlVentes = $_ENV['API_URL'] . '/ventes';

        try {
            $reponseDataClients = $this->httpClient->request('GET', $urlClient);
            $dataClient = $reponseDataClients->toArray();
        } catch (\Exception $e) {
            $dataClient = [];
        }

        try {
            $reponseDataVentes = $this->httpClient->request('GET', $urlVentes);
            $dataVentes = $reponseDataVentes->toArray();
        } catch (\Exception $e) {
            $dataVentes = [];
        }

        $peintures = [];
        $chiffreAffaire = 0;

        foreach ($dataVentes as $vente) {
            try {
                if ($vente['idClient'] == $dataClient["id"]){
                    $response = $this->httpClient->request('GET', $urlPeinture . '/' . $vente['idPeinture']);
                    $data = $response->toArray();
                    $peintures[] = $data;
                    if (isset($data['prize'])) {
                        $prizePeinture = $data['prize'];
                        $chiffreAffaire += $vente['amount'] * $prizePeinture;
                    } else {
                        throw new \Exception("Le prix de la peinture avec l'ID " . $vente['idPeinture'] . " n'a pas été trouvé.");
                    }
                }
            } catch (\Exception $e) {
            }
        }

        $nbPeintures = count($peintures);
        
        return $this->render('clients/detail.html.twig', [
            'user_name' => $user['firstname'].' '.$user['lastname'],
            'artworks' => $artworks,
            'client' => $dataClient,
            'peintures' => $peintures,
            'nbPeintures' => $nbPeintures,
            'chiffreAffaire' => $chiffreAffaire,
        ]);
    }

    #[Route('/add', name: 'app_add_client')]
    public function add(Request $request, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ClientsFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $clientData = $form->getData();

            $urlAPI = 'http://127.0.0.1:3000/api/clients/register';
            $data = [
                'email' => $clientData['email'],
                'password' => $clientData['password'],
                'firstname' => $clientData['firstname'],
                'lastname' => $clientData['lastname'],
                'adresse' => $clientData['address'],
                'complement' => $clientData['complement'],
                'town' => $clientData['town'],
                'postalCode' => $clientData['postalCode'],
                'phone' => $clientData['phone'],
            ];

            try {
                $response = $this->httpClient->request('POST', $urlAPI, [
                    'json' => $data,
                ]);

                if ($response->getStatusCode() === 200) {
                    return $this->redirectToRoute('app_dashboard');
                } else {
                    $error = 'An error occurred while registering the client.';
                    if ($response->getStatusCode() === 409) {
                        $error = 'Email already exists.';
                    }
                    $this->addFlash('error', $error);
                    return $this->redirectToRoute('app_dashboard');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'An error occurred: ' . $e->getMessage());
            }
        }

        return $this->render('clients/add.html.twig', [
            'user_name' => $user['firstname'] . ' ' . $user['lastname'],
            'form' => $form->createView(),
        ]);
    }


}
