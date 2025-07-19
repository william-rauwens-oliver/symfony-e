<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\Follow;
use App\Entity\Publication;
use App\Entity\Repost;
use App\Entity\Commentaire;
use App\Entity\CommentLike;
use Doctrine\ORM\EntityManagerInterface;

class SuggestionApiController extends AbstractController
{
    public function __construct(
        private TokenStorageInterface $tokenStorage
    ) {}

    #[Route('/api/trending', name: 'api_trending', methods: ['GET'])]
    public function trending(): JsonResponse
    {
        $user = $this->tokenStorage->getToken()?->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        // Supprimer l'injection du SuggestionService et tout le code qui l'utilise
        
        return $this->json([
            'trending' => array_map(function($item) {
                return [
                    'publication' => [
                        'id' => $item['publication']->getId(),
                        'content' => $item['publication']->getContent(),
                        'createdAt' => $item['publication']->getCreatedAt()->format('c'),
                        'author' => [
                            'id' => $item['publication']->getUser()->getId(),
                            'username' => $item['publication']->getUser()->getUsername(),
                            'email' => $item['publication']->getUser()->getEmail(),
                        ],
                        'likes' => $item['publication']->getLikes()->count(),
                        'comments' => $item['publication']->getCommentaires()->count(),
                    ],
                    'score' => $item['score'],
                    'scoreDetails' => $item['scoreDetails']
                ];
            }, $trending)
        ]);
    }

    #[Route('/api/suggestions', name: 'api_suggestions', methods: ['GET'])]
    public function suggestions(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        // Récupérer les comptes suivis
        $following = $em->getRepository(Follow::class)->findFollowing($user);
        $followingIds = array_map(fn($f) => $f->getFollowed()->getId(), $following);

        // Récupérer les hashtags utilisés par l'utilisateur
        $userPublications = $em->getRepository(Publication::class)->findBy(['user' => $user]);
        $userHashtags = [];
        foreach ($userPublications as $pub) {
            if ($pub->getTexte()) {
                preg_match_all('/#(\w+)/u', $pub->getTexte(), $matches);
                foreach ($matches[1] as $tag) {
                    $userHashtags[] = mb_strtolower($tag);
                }
            }
        }
        $userHashtags = array_unique($userHashtags);

        // Récupérer les publications récentes (hors celles de l'utilisateur)
        $publications = $em->getRepository(Publication::class)->createQueryBuilder('p')
            ->where('p.user != :me')
            ->setParameter('me', $user)
            ->orderBy('p.createdAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        $suggestions = [];
        foreach ($publications as $pub) {
            $score = 0;
            $scoreDetails = [];
            $pubId = $pub->getId();
            $pubUser = $pub->getUser();

            // +5 par like reçu
            $likeCount = $pub->getLikeCount();
            $score += 5 * $likeCount;
            $scoreDetails['likes'] = 5 * $likeCount;

            // +3 par commentaire reçu
            $commentCount = $pub->getCommentaires()->count();
            $score += 3 * $commentCount;
            $scoreDetails['comments'] = 3 * $commentCount;

            // +10 si reposté par un compte suivi
            $reposts = $em->getRepository(Repost::class)->findBy(['publication' => $pub]);
            $repostByFollowed = 0;
            foreach ($reposts as $repost) {
                if (in_array($repost->getUser()->getId(), $followingIds)) {
                    $repostByFollowed++;
                }
            }
            if ($repostByFollowed > 0) {
                $score += 10 * $repostByFollowed;
                $scoreDetails['following_reposts'] = 10 * $repostByFollowed;
            }

            // +7 si un hashtag de l'utilisateur est réutilisé
            $hashtags = [];
            if ($pub->getTexte()) {
                preg_match_all('/#(\w+)/u', $pub->getTexte(), $matches);
                foreach ($matches[1] as $tag) {
                    $hashtags[] = mb_strtolower($tag);
                }
            }
            $commonTags = array_intersect($userHashtags, $hashtags);
            if (count($commonTags) > 0) {
                $score += 7 * count($commonTags);
                $scoreDetails['hashtag_matches'] = 7 * count($commonTags);
            }

            // +15 si l’auteur a interagi avec un commentaire de l'utilisateur dans les 7 derniers jours
            $recentInteraction = 0;
            $since = (new \DateTimeImmutable('-7 days'));
            // Like ou commentaire de l'auteur sur un commentaire de l'utilisateur
            $userComments = $em->getRepository(Commentaire::class)->findBy(['user' => $user, 'publication' => $pub]);
            foreach ($userComments as $comment) {
                // Like de l'auteur
                $commentLikes = $em->getRepository(CommentLike::class)->findBy(['commentaire' => $comment]);
                foreach ($commentLikes as $cl) {
                    if ($cl->getUser()->getId() === $pubUser->getId() && $cl->getId() && $cl->getId() > 0 && $cl->getCommentaire()->getCreatedAt() >= $since) {
                        $recentInteraction++;
                    }
                }
                // Réponse de l'auteur
                $replies = $em->getRepository(Commentaire::class)->findBy(['parent' => $comment]);
                foreach ($replies as $reply) {
                    if ($reply->getUser()->getId() === $pubUser->getId() && $reply->getCreatedAt() >= $since) {
                        $recentInteraction++;
                    }
                }
            }
            if ($recentInteraction > 0) {
                $score += 15 * $recentInteraction;
                $scoreDetails['recent_interaction'] = 15 * $recentInteraction;
            }

            $suggestions[] = [
                'publication' => [
                    'id' => $pub->getId(),
                    'content' => $pub->getTexte(),
                    'createdAt' => $pub->getCreatedAt()?->format('c'),
                    'author' => [
                        'id' => $pub->getUser()?->getId(),
                        'username' => $pub->getUser()?->getUsername(),
                        'email' => $pub->getUser()?->getEmail(),
                    ],
                    'likes' => $likeCount,
                    'comments' => $commentCount,
                ],
                'score' => $score,
                'scoreDetails' => $scoreDetails
            ];
        }

        // Trier par score décroissant
        usort($suggestions, fn($a, $b) => $b['score'] <=> $a['score']);

        return $this->json([
            'suggestions' => $suggestions
        ]);
    }

    #[Route('/api/me/cleanup', name: 'api_me_cleanup', methods: ['POST'])]
    public function cleanup(EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }
        // Supprimer les commentaires
        $commentaires = $em->getRepository(Commentaire::class)->findBy(['user' => $user]);
        foreach ($commentaires as $commentaire) {
            $em->remove($commentaire);
        }
        // Supprimer les publications
        $publications = $em->getRepository(Publication::class)->findBy(['user' => $user]);
        foreach ($publications as $publication) {
            $em->remove($publication);
        }
        $em->flush();
        return $this->json(['success' => true]);
    }
} 