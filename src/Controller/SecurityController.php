<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Security\UserRegistrationFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, UserRepository $userRepository)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/register', name: 'app_register')]
    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserRegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Validate the password format
            if (!$this->isPasswordValid($user->getPassword())) {
                $this->addFlash('danger', "Le mot de passe doit comporter au moins 6 caractères");
                return $this->redirectToRoute('app_register');
            }

            // Encode the plain password
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, $user->getPassword())
            );

            // Validate the email format
            if (!$this->isEmailValid($user->getEmail())) {
                $this->addFlash('danger', "Le format de l'email n'est pas valide");
                return $this->redirectToRoute('app_register');
            }

            // Validated that the email is not already used
            $existingUser = $this->userRepository->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser !== null) {
                $this->addFlash('danger', "L'email est déjà utilisé");
                return $this->redirectToRoute('app_register');
            }


            // Save the user to database
            $this->em->persist($user);
            $this->em->flush();

            // Redirect or do something else
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private function isEmailValid(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        return true;
    }

    private function isPasswordValid(string $password): bool
    {
        return strlen($password) >= 6;
    }
}
