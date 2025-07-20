<?php

namespace App\Controller\Api;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\SecurityBundle\Security;

#[Route('/api')]
class CommentaireApiController extends AbstractController
{
    #[Route('/commentaires-with-likes', name: 'api_commentaires_with_likes', methods: ['GET'])]
    public function getCommentairesWithLikes(
        Request $request,
        CommentaireRepository $commentaireRepository,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        Security $security
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $publicationId = $request->query->get('publication');
        $parentId = $request->query->get('parent');

        if (!$publicationId) {
            return $this->json(['error' => 'ID de publication requis'], 400);
        }

        // Je récupère les commentaires
        $criteria = ['publication' => $publicationId];
        if ($parentId) {
            $criteria['parent'] = $parentId;
        } else {
            $criteria['parent'] = null; // Je récupère seulement les commentaires parents
        }

        $commentaires = $commentaireRepository->findBy($criteria, ['createdAt' => 'ASC']);

        // Je récupère les likes de l'utilisateur pour ces commentaires
        $commentIds = array_map(fn($c) => $c->getId(), $commentaires);
        $userLikes = [];
        
        if (!empty($commentIds)) {
            $userLikes = $em->getRepository('App\Entity\CommentLike')->findBy([
                'commentaire' => $commentIds,
                'user' => $user
            ]);
        }

        $userLikedCommentIds = array_map(fn($like) => $like->getCommentaire()->getId(), $userLikes);

        // Je sérialise les commentaires avec les informations de like
        $data = [];
        foreach ($commentaires as $commentaire) {
            $commentArray = [
                'id' => $commentaire->getId(),
                'content' => $commentaire->getContent(),
                'createdAt' => $commentaire->getCreatedAt()->format('c'),
                'user' => [
                    'id' => $commentaire->getUser()->getId(),
                    'username' => $commentaire->getUser()->getUsername()
                ],
                'publication' => '/api/publications/' . $commentaire->getPublication()->getId(),
                'parent' => $commentaire->getParent() ? '/api/commentaires/' . $commentaire->getParent()->getId() : null,
                'likeCount' => $commentaire->getLikeCount(),
                'likedByCurrentUser' => in_array($commentaire->getId(), $userLikedCommentIds)
            ];
            
            $data[] = $commentArray;
        }

        return $this->json($data);
    }

    #[Route('/commentaires/{id}/like-status', name: 'api_commentaire_like_status', methods: ['GET'])]
    public function getCommentaireLikeStatus(
        Commentaire $commentaire,
        EntityManagerInterface $em,
        Security $security
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $like = $em->getRepository('App\Entity\CommentLike')->findOneBy([
            'commentaire' => $commentaire,
            'user' => $user
        ]);

        return $this->json([
            'commentId' => $commentaire->getId(),
            'likeCount' => $commentaire->getLikeCount(),
            'likedByCurrentUser' => $like !== null,
            'userLikeId' => $like ? $like->getId() : null
        ]);
    }
} 