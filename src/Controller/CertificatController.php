<?php

namespace App\Controller;

use App\Service\MyCacheService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/certificat')]
class CertificatController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/generate/{idPeinture}', name: 'app_generate_certificat')]
    public function index(int $idPeinture, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if(!$user){
            return $this->redirectToRoute('app_login');
        }

        $urlCertificats = $_ENV['API_URL'] . '/generate-certificate/' . $idPeinture . '/' . $user['id'];

        return new RedirectResponse($urlCertificats);
    }
}