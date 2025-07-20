<?php

namespace App\Controller\Api;

use App\Entity\Like;
use App\Entity\Publication;
use App\Repository\LikeRepository;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class LikeApiController extends AbstractController
{
    #[Route('/likes/current', name: 'api_likes_current', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getCurrentUserLikes(Request $request, LikeRepository $likeRepository, PublicationRepository $publicationRepository): JsonResponse
    {
        $user = $this->getUser();
        $publicationId = $request->query->get('publication');
        
        if (!$publicationId) {
            return $this->json(['error' => 'Publication ID required'], 400);
        }
        
        // Je récupère la publication
        $publication = $publicationRepository->find($publicationId);
        if (!$publication) {
            return $this->json(['error' => 'Publication not found'], 404);
        }
        
        // Je récupère le like de l'utilisateur courant pour cette publication
        $like = $likeRepository->findOneBy([
            'user' => $user,
            'publication' => $publication
        ]);
        
        if ($like) {
            return $this->json([
                'hydra:member' => [
                    [
                        '@id' => '/api/likes/' . $like->getId(),
                        'id' => $like->getId(),
                        'user' => '/api/users/' . $like->getUser()->getId(),
                        'publication' => '/api/publications/' . $like->getPublication()->getId()
                    ]
                ]
            ]);
        }
        
        return $this->json(['hydra:member' => []]);
    }

    #[Route('/likes/user/{publicationId}', name: 'api_likes_user_publication', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getUserLikeForPublication(int $publicationId, LikeRepository $likeRepository, PublicationRepository $publicationRepository): JsonResponse
    {
        $user = $this->getUser();
        
        // Je récupère la publication
        $publication = $publicationRepository->find($publicationId);
        if (!$publication) {
            return $this->json(['error' => 'Publication not found'], 404);
        }
        
        // Je récupère le like de l'utilisateur courant pour cette publication
        $like = $likeRepository->findOneBy([
            'user' => $user,
            'publication' => $publication
        ]);
        
        if ($like) {
            return $this->json([
                'hydra:member' => [
                    [
                        '@id' => '/api/likes/' . $like->getId(),
                        'id' => $like->getId(),
                        'user' => '/api/users/' . $like->getUser()->getId(),
                        'publication' => '/api/publications/' . $like->getPublication()->getId()
                    ]
                ]
            ]);
        }
        
        return $this->json(['hydra:member' => []]);
    }
} 