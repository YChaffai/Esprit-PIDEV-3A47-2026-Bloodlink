<?php

namespace App\Controller\Front;

use App\Entity\User;
use App\Form\Front\ClientProfileType;
use App\Form\Front\UserProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/front/profile')]
#[IsGranted('ROLE_USER')]
class ProfileController extends AbstractController
{
    #[Route('', name: 'front_profile_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        $client = $user->getClient();

        if ($client) {
            // Client specific profile with medical info
            $form = $this->createForm(ClientProfileType::class, $client);
            
            // Manually set User fields in the form since they are unmapped in ClientProfileType
            $form->get('nom')->setData($user->getNom());
            $form->get('prenom')->setData($user->getPrenom());
            $form->get('email')->setData($user->getEmail());

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Update User fields
                $user->setNom($form->get('nom')->getData());
                $user->setPrenom($form->get('prenom')->getData());
                $user->setEmail($form->get('email')->getData());

                // Handle Password
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                }

                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
                return $this->redirectToRoute('front_profile_edit');
            }
        } else {
            // General profile for Admin, Doctor, Agent (CNTS)
            $form = $this->createForm(UserProfileType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Handle Password
                $plainPassword = $form->get('plainPassword')->getData();
                if ($plainPassword) {
                    $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
                }

                $entityManager->flush();

                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
                return $this->redirectToRoute('front_profile_edit');
            }
        }

        return $this->render('front/profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
            'is_client' => $client !== null,
        ]);
    }
}
