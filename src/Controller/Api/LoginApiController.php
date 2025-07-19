<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LoginApiController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['email'], $data['password'])) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        $user = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], 404);
        }
        if (!$passwordHasher->isPasswordValid($user, $data['password'])) {
            return $this->json(['error' => 'Mot de passe incorrect'], 401);
        }

        // Générer un JWT
        $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? 'default_jwt_secret';
        $payload = [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'exp' => (new \DateTime('+7 days'))->getTimestamp(),
        ];
        $jwt = JWT::encode($payload, $jwtSecret, 'HS256');

        // Debug: Log les informations de connexion
        error_log("DEBUG API LOGIN - User authenticated: " . $user->getEmail());
        error_log("DEBUG API LOGIN - JWT generated: " . substr($jwt, 0, 20) . "...");

        return $this->json([
            'success' => true,
            'token' => $jwt,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ]
        ]);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function me(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            error_log("DEBUG API ME - No Authorization header");
            return $this->json(['error' => 'Token manquant'], 401);
        }

        $token = substr($authHeader, 7);
        $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? 'default_jwt_secret';

        try {
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
            $user = $em->getRepository(User::class)->find($decoded->user_id);
            
            if (!$user) {
                error_log("DEBUG API ME - User not found for ID: " . $decoded->user_id);
                return $this->json(['error' => 'Utilisateur non trouvé'], 404);
            }

            error_log("DEBUG API ME - User found: " . $user->getEmail());

            return $this->json([
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ]);
        } catch (\Exception $e) {
            error_log("DEBUG API ME - Token decode error: " . $e->getMessage());
            return $this->json(['error' => 'Token invalide'], 401);
        }
    }

    #[Route('/api/logout', name: 'api_logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // Pour JWT, la déconnexion se fait côté client en supprimant le token
        return $this->json(['success' => true, 'message' => 'Déconnexion réussie']);
    }
} 