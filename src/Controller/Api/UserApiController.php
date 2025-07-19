<?php

namespace App\Controller\Api;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[Route('/api/users')]
class UserApiController extends AbstractController
{
    #[Route('/me', name: 'api_users_me', methods: ['GET'])]
    public function me(TokenStorageInterface $tokenStorage): JsonResponse
    {
        $token = $tokenStorage->getToken();
        if (!$token || !$token->getUser() instanceof User) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $user = $token->getUser();
        
        return new JsonResponse([
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ]);
    }
} 