<?php

namespace App\Controller\Api;

use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api')]
class SearchApiController extends AbstractController
{
    #[Route('/search', name: 'api_search', methods: ['GET'])]
    public function search(
        Request $request,
        PublicationRepository $publicationRepository,
        UserRepository $userRepository,
        SerializerInterface $serializer
    ): JsonResponse {
        $query = $request->query->get('q', '');
        $query = trim($query);
        $publications = [];
        $users = [];
        $hashtags = [];
        $error = null;
        try {
            if ($query === '#') {
                // Afficher tous les hashtags
                $hashtags = $publicationRepository->findAllHashtags(50);
            } elseif ($query !== '' && $query[0] === '#') {
                // Recherche hashtag
                $hashtag = mb_substr($query, 1);
                if ($hashtag === '') {
                    $hashtags = $publicationRepository->findAllHashtags(50);
                } else {
                    $publications = $publicationRepository->findByHashtag($hashtag);
                    $hashtags = count($publications) > 0 ? [$hashtag] : [];
                }
            } elseif ($query !== '') {
                // Recherche utilisateur
                $users = $userRepository->searchUsers($query);
                $hashtags = [];
            } else {
                $hashtags = [];
            }
            $data = [
                'publications' => json_decode($serializer->serialize($publications, 'json', ['groups' => ['publication:read']])),
                'users' => json_decode($serializer->serialize($users, 'json', ['groups' => ['user:read']])),
                'hashtags' => $hashtags,
                'total' => count($publications) + count($users),
                'query' => $query,
            ];
        } catch (\Throwable $e) {
            $data = [
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
                'publications' => [],
                'users' => [],
                'hashtags' => [],
                'total' => 0,
                'query' => $query,
            ];
        }
        return $this->json($data);
    }
} 