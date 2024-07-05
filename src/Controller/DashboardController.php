<?php

namespace App\Controller;

use App\Service\MyCacheService;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DashboardController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/', name: 'app_dashboard')]
    public function index(MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $urlClients = $_ENV['API_URL'] . '/clients';
        $urlVentes = $_ENV['API_URL'] . '/ventes';
        $urlPeinture = $_ENV['API_URL'] . '/peintures';
        $urlCertificats = $_ENV['API_URL'] . '/certificats';

        try {
            $reponseDataClients = $this->httpClient->request('GET', $urlClients);
            $dataClients = $reponseDataClients->toArray();
        } catch (\Exception $e) {
            $dataClients = [];
        }

        try {
            $reponseDataVentes = $this->httpClient->request('GET', $urlVentes);
            $dataVentes = $reponseDataVentes->toArray();
        } catch (\Exception $e) {
            $dataVentes = [];
        }

        try {
            $reponseDataCertificats = $this->httpClient->request('GET', $urlCertificats);
            $dataCertificats = $reponseDataCertificats->toArray();
        } catch (\Exception $e) {
            $dataCertificats = [];
        }

        $chiffreAffaire = 0;
        $nbVentes = count($dataVentes);
        $nbClients = count($dataClients);
        $nbCertificats = count($dataCertificats);

        foreach ($dataVentes as $vente) {
            try {
                $response = $this->httpClient->request('GET', $urlPeinture . '/' . $vente['idPeinture']);
                $data = $response->toArray();

                if (isset($data['prize'])) {
                    $prizePeinture = $data['prize'];
                    $chiffreAffaire += $vente['amount'] * $prizePeinture;
                } else {
                    throw new \Exception("Le prix de la peinture avec l'ID " . $vente['idPeinture'] . " n'a pas été trouvé.");
                }
            } catch (\Exception $e) {
                // Gérer l'erreur en continuant avec un prix de 0 pour cette peinture
                $prizePeinture = 0;
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'user'=> $user,
            'user_initials' => $cacheService->getInitials($user['firstname'], $user['lastname']),
            'user_name' => $user['firstname'] . ' ' . $user['lastname'],
            'chiffreAffaire' => $chiffreAffaire,
            'nbVentes' => $nbVentes,
            'nbClients' => $nbClients,
            'nbCertificats' => $nbCertificats,
        ]);
    }

}
