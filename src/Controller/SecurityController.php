<?php

namespace App\Controller;

use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Form\Security\UserRegistrationFormType;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    private $httpClient;
    private $cache;

    public function __construct(HttpClientInterface $httpClient, CacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request): Response
    {
        $user = $this->cache->get('user', function (ItemInterface $item) {
            $item->expiresAfter(3600);
            return $this->getUser();
        });
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

            try {
                // Utilisation du cache pour stocker/récupérer les données de l'utilisateur
                $data = $this->cache->get('user', function (ItemInterface $item) use ($email, $password) {
                    $item->expiresAfter(3600); // Défini la durée de vie du cache (ici, une heure)
                    $url = $_ENV['API_URL'] . '/clients/login/' . $email . '/' . $password;
                    $response = $this->httpClient->request('GET', $url);
                    return $response->toArray();  // Retourne les données après les avoir converties en tableau
                });


            } catch (\Exception $e) {
                $this->addFlash('error', 'Impossible de se connecter à l\'API.');
                dd('Erreur : ' . $e->getMessage());
            }
        }

        return $this->render('security/login.html.twig', [
            'form' => $form,
        ]);
    }
    

    // #[Route(path: '/register', name: 'app_register')]
    // public function register(Request $request): Response
    // {

    // }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
