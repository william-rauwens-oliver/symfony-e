<?php

namespace App\Controller;

use App\Entity\Like;
use App\Repository\PublicationRepository;
use App\Repository\LikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class LikeController extends AbstractController
{
    #[Route('/like/{id}', name: 'like_publication')]
    public function like(
        int $id,
        PublicationRepository $publicationRepository,
        LikeRepository $likeRepository,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $publication = $publicationRepository->find($id);
        $user = $this->getUser();

        if (!$publication || !$user) {
            return $this->redirectToRoute('app_home');
        }

        // Vérifie si l'utilisateur a déjà liké cette publication
        $existingLike = $likeRepository->findOneBy([
            'user' => $user,
            'publication' => $publication,
        ]);

        if ($existingLike) {
            // Si déjà liké, on retire le like (toggle)
            $entityManager->remove($existingLike);
            $entityManager->flush();
            $this->addFlash('info', 'Like retiré.');
        } else {
            // Sinon, on ajoute un like
            $like = new Like();
            $like->setUser($user);
            $like->setPublication($publication);
            $entityManager->persist($like);
            $entityManager->flush();
            $this->addFlash('success', 'Publication likée !');
        }

        return $this->redirectToRoute('app_home');
    }
}
