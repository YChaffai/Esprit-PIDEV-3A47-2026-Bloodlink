<?php

namespace App\Controller\Front;

use App\Entity\Client;
use App\Entity\User;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AuthController extends AbstractController
{
    #[Route('/login', name: 'front_login')]
    public function login(AuthenticationUtils $authUtils): Response
    {
        return $this->render('front/auth/login.html.twig', [
            'last_username' => $authUtils->getLastUsername(),
            'error' => $authUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony handles this via security.yaml
    }

    #[Route('/register', name: 'front_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        ClientRepository $clientRepo
    ): Response {
        if ($request->isMethod('POST')) {
            $nom = trim((string) $request->request->get('nom'));
            $prenom = trim((string) $request->request->get('prenom'));
            $email = trim((string) $request->request->get('email'));
            $plain = (string) $request->request->get('password');
            $typeSang = trim((string) $request->request->get('typeSang'));

            if (!$nom || !$prenom || !$email || !$plain || !$typeSang) {
                $this->addFlash('danger', 'Please fill all fields.');
                return $this->redirectToRoute('front_register');
            }

            $user = new User();
            $user->setNom($nom)
                ->setPrenom($prenom)
                ->setEmail($email)
                ->setRole('ROLE_USER')
                ->setPassword($hasher->hashPassword($user, $plain));

            $em->persist($user);
            $em->flush();

            // Create client profile linked to this user
            $client = new Client();
            $client->setUser($user);
            $client->setTypeSang($typeSang);
            $client->setDernierDon(new \DateTime('today'));

            $em->persist($client);
            $em->flush();

            $this->addFlash('success', 'Account created. You can login now.');
            return $this->redirectToRoute('front_login');
        }

        return $this->render('front/auth/register.html.twig');
    }
}
