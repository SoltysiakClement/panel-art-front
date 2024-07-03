<?php

namespace App\Controller;

use App\Service\MyCacheService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\Security\UserRegistrationFormType;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, MyCacheService $cacheService): Response
    {
        $user = $cacheService->getCacheData('user');
        if($user){    
            return $this->redirectToRoute('app_dashboard');
        }

        $log = [];
        $form = $this->createForm(UserRegistrationFormType::class, $log);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $email = $formData['email'];
            $password = $formData['password'];
            $url = $_ENV['API_URL'] . '/clients/login/' . $email . '/' . $password;

            try {
                $reponseData = $this->httpClient->request('GET', $url);
                $data = $reponseData->toArray();
                $cacheService->setCacheData('user', $data);    
                
                return $this->redirectToRoute('app_dashboard');
                
            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de se connecter à l\'API.');
                dd('Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('security/login.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(MyCacheService $cacheService): Response
    {
        $cacheService->deleteCacheData('user');
        return $this->redirectToRoute('app_login');
    }
}
