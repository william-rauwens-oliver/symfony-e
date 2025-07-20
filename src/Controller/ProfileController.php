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
use App\Repository\FollowRepository;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    public function profile(Request $request): Response
    {
        // Je log les infos de session pour débugger
        error_log("DEBUG PROFILE - Session ID: " . $request->getSession()->getId());
        error_log("DEBUG PROFILE - User: " . ($this->getUser() ? $this->getUser()->getEmail() : 'NULL'));
        
        $user = $this->getUser();
        if (!$user) {
            error_log("DEBUG PROFILE - Access denied: No user found");
            throw $this->createAccessDeniedException();
        }
        
        error_log("DEBUG PROFILE - Access granted for user: " . $user->getEmail());
        
        // Je récupère les publications et retweets de l'utilisateur
        $publications = $user->getPublications();
        $reposts = $user->getReposts();
        
        // Je combine les publications et retweets, triés par date
        $allContent = [];
        
        // J'ajoute les publications
        foreach ($publications as $publication) {
            $allContent[] = [
                'type' => 'publication',
                'content' => $publication,
                'date' => $publication->getCreatedAt()
            ];
        }
        
        // J'ajoute les retweets (seulement ceux avec des publications valides)
        foreach ($reposts as $repost) {
            // Je vérifie que la publication du repost existe encore
            if ($repost->getPublication() && $repost->getPublication()->getId()) {
                $allContent[] = [
                    'type' => 'repost',
                    'content' => $repost,
                    'date' => $repost->getCreatedAt()
                ];
            }
        }
        
        // Je trie par date décroissante
        usort($allContent, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });
        
        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'allContent' => $allContent,
        ]);
    }

    #[Route('/profile/{id}', name: 'app_profile_show')]
    public function show(User $user, FollowRepository $followRepository): Response
    {
        $publications = $user->getPublications();
        $reposts = $user->getReposts();
        $allContent = [];
        foreach ($publications as $publication) {
            $allContent[] = [
                'type' => 'publication',
                'content' => $publication,
                'date' => $publication->getCreatedAt()
            ];
        }
        foreach ($reposts as $repost) {
            // Je vérifie que la publication du repost existe encore
            if ($repost->getPublication() && $repost->getPublication()->getId()) {
                $allContent[] = [
                    'type' => 'repost',
                    'content' => $repost,
                    'date' => $repost->getCreatedAt()
                ];
            }
        }
        usort($allContent, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });
        $followers = $followRepository->findFollowers($user);
        $followings = $followRepository->findFollowing($user);
        return $this->render('profile/profile.html.twig', [
            'user' => $user,
            'allContent' => $allContent,
            'followers_count' => count($followers),
            'followings_count' => count($followings),
            'followers' => array_map(fn($f) => $f->getFollower(), $followers),
            'followings' => array_map(fn($f) => $f->getFollowed(), $followings),
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
            // Je supprime les likes de l'utilisateur
            foreach ($user->getLikes() as $like) {
                $em->remove($like);
            }
            // Je supprime les commentaires de l'utilisateur
            foreach ($user->getCommentaires() as $commentaire) {
                $em->remove($commentaire);
            }
            // Je supprime toute logique liée aux publications de l'utilisateur
            $em->remove($user);
            $em->flush();
            $this->container->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
            $this->addFlash('success', 'Votre compte a été supprimé avec succès. Nous espérons vous revoir bientôt !');
            return $this->redirectToRoute('react_home');
        }
        return $this->render('profile/delete.html.twig');
    }
} 