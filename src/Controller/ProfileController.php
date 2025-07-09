<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        return $this->render('profile/profile.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/edit', name: 'app_profile_edit')]
    public function edit(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }
            $em->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/profile/delete', name: 'app_profile_delete', methods: ['GET', 'POST'])]
    public function delete(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException();
        }
        if ($request->isMethod('POST')) {
            // Supprimer les likes de l'utilisateur
            foreach ($user->getLikes() as $like) {
                $em->remove($like);
            }
            // Supprimer les commentaires de l'utilisateur
            foreach ($user->getCommentaires() as $commentaire) {
                $em->remove($commentaire);
            }
            // Supprimer les publications de l'utilisateur
            foreach ($user->getPublications() as $publication) {
                $em->remove($publication);
            }
            $em->remove($user);
            $em->flush();
            $this->container->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
            $this->addFlash('success', 'Votre compte a été supprimé avec succès. Nous espérons vous revoir bientôt !');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('profile/delete.html.twig');
    }
} 