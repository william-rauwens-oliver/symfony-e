<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Publication;
use App\Repository\PublicationRepository;
use App\Repository\FollowRepository;
use Doctrine\ORM\EntityManagerInterface;

class SuggestionService
{
    private PublicationRepository $publicationRepository;
    private FollowRepository $followRepository;
    private EntityManagerInterface $em;

    public function __construct(
        PublicationRepository $publicationRepository, 
        FollowRepository $followRepository,
        EntityManagerInterface $em
    ) {
        $this->publicationRepository = $publicationRepository;
        $this->followRepository = $followRepository;
        $this->em = $em;
    }

    /**
     * Retourne les publications triées par score de suggestion pour l'utilisateur donné
     * @return array [publication, score]
     */
    public function suggestPublicationsForUser(User $user): array
    {
        $publications = $this->publicationRepository->findAllWithRelations();
        $scored = [];
        
        foreach ($publications as $pub) {
            $score = 0;
            $scoreDetails = [];
            
            // +5 par like reçu
            $likesCount = $pub->getLikes()->count();
            $score += 5 * $likesCount;
            $scoreDetails['likes'] = $likesCount * 5;
            
            // +3 par commentaire reçu
            $commentsCount = $pub->getCommentaires()->count();
            $score += 3 * $commentsCount;
            $scoreDetails['comments'] = $commentsCount * 3;
            
            // +10 si votre publication a été re-postée par un compte que vous suivez
            // (Pour l'instant, on simule avec les likes des utilisateurs que vous suivez)
            $followingUsers = $this->getFollowingUsers($user);
            $likesFromFollowing = 0;
            foreach ($pub->getLikes() as $like) {
                if (in_array($like->getUser(), $followingUsers)) {
                    $likesFromFollowing++;
                }
            }
            $score += 10 * $likesFromFollowing;
            $scoreDetails['following_likes'] = $likesFromFollowing * 10;
            
            // +7 si l'un de vos hashtags est réutilisé dans une publication
            $hashtags = $this->extractHashtags($pub->getContent());
            $userHashtags = $this->getUserHashtags($user);
            $hashtagMatches = 0;
            foreach ($hashtags as $tag) {
                if (in_array(strtolower($tag), array_map('strtolower', $userHashtags))) {
                    $hashtagMatches++;
                }
            }
            $score += 7 * $hashtagMatches;
            $scoreDetails['hashtag_matches'] = $hashtagMatches * 7;
            
            // +15 si l'auteur de la publication a interagi avec votre commentaire récemment
            $recentInteraction = $this->hasRecentInteraction($pub->getUser(), $user);
            if ($recentInteraction) {
                $score += 15;
                $scoreDetails['recent_interaction'] = 15;
            }
            
            $scored[] = [
                'publication' => $pub, 
                'score' => $score,
                'scoreDetails' => $scoreDetails
            ];
        }
        
        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);
        return $scored;
    }

    private function extractHashtags(?string $content): array
    {
        if (!$content) return [];
        preg_match_all('/#(\w+)/u', $content, $matches);
        return $matches[1] ?? [];
    }

    private function getUserHashtags(User $user): array
    {
        $hashtags = [];
        foreach ($user->getPublications() as $pub) {
            $hashtags = array_merge($hashtags, $this->extractHashtags($pub->getContent()));
        }
        return array_unique($hashtags);
    }

    private function getFollowingUsers(User $user): array
    {
        $follows = $this->followRepository->findFollowing($user);
        return array_map(fn($follow) => $follow->getFollowed(), $follows);
    }

    private function hasRecentInteraction(User $author, User $user): bool
    {
        // Vérifier si l'auteur a liké ou commenté une publication de l'utilisateur dans les 7 derniers jours
        $sevenDaysAgo = new \DateTimeImmutable('-7 days');
        
        // Vérifier les likes de l'auteur sur les publications de l'utilisateur
        // (On utilise la date de création de la publication car Like n'a pas de createdAt)
        $qb = $this->em->createQueryBuilder();
        $qb->select('l')
           ->from(\App\Entity\Like::class, 'l')
           ->join('l.publication', 'p')
           ->where('l.user = :author')
           ->andWhere('p.user = :user')
           ->andWhere('p.createdAt >= :date')
           ->setParameter('author', $author)
           ->setParameter('user', $user)
           ->setParameter('date', $sevenDaysAgo);
        
        $recentLikes = $qb->getQuery()->getResult();
        
        // Vérifier les commentaires de l'auteur sur les publications de l'utilisateur
        $qb = $this->em->createQueryBuilder();
        $qb->select('c')
           ->from(\App\Entity\Commentaire::class, 'c')
           ->join('c.publication', 'p')
           ->where('c.user = :author')
           ->andWhere('p.user = :user')
           ->andWhere('c.createdAt >= :date')
           ->setParameter('author', $author)
           ->setParameter('user', $user)
           ->setParameter('date', $sevenDaysAgo);
        
        $recentComments = $qb->getQuery()->getResult();
        
        return !empty($recentLikes) || !empty($recentComments);
    }
} 