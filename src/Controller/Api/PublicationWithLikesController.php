<?php

namespace App\Controller\Api;

use App\Entity\Publication;
use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class PublicationWithLikesController extends AbstractController
{
    #[Route('/publications-with-likes', name: 'api_publications_with_likes', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function getPublicationsWithLikes(Request $request, PublicationRepository $publicationRepository): JsonResponse
    {
        $user = $this->getUser();
        $publications = $publicationRepository->findAll();
        
        $data = [];
        foreach ($publications as $publication) {
            $publicationData = [
                'id' => $publication->getId(),
                'texte' => $publication->getTexte(),
                'image' => $publication->getImage(),
                'video' => $publication->getVideo(),
                'createdAt' => $publication->getCreatedAt()->format('c'),
                'likeCount' => $publication->getLikeCount(),
                'likedByCurrentUser' => $publication->isLikedByUser($user),
                'user' => [
                    'id' => $publication->getUser()->getId(),
                    'username' => $publication->getUser()->getUsername(),
                ]
            ];
            $data[] = $publicationData;
        }
        
        return $this->json(['hydra:member' => $data]);
    }
} 