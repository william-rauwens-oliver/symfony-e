<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Component\Dotenv\Dotenv;

class JwtAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // Log tous les headers reçus pour debug
        file_put_contents(__DIR__ . '/../../jwt_debug.log', '[supports] Headers: ' . json_encode($request->headers->all()) . "\n", FILE_APPEND);
        file_put_contents(__DIR__ . '/../../jwt_debug.log', '[supports] Method: ' . $request->getMethod() . ', Path: ' . $request->getPathInfo() . "\n", FILE_APPEND);
        // Vérifier si la requête contient un token Bearer
        $authHeader = $request->headers->get('Authorization');
        $supports = $authHeader && str_starts_with($authHeader, 'Bearer ');
        file_put_contents(__DIR__ . '/../../jwt_debug.log', '[supports] Path: ' . $request->getPathInfo() . ', supports: ' . ($supports ? 'true' : 'false') . "\n", FILE_APPEND);
        return $supports;
    }

    public function authenticate(Request $request): Passport
    {
        file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] Path: ' . $request->getPathInfo() . "\n", FILE_APPEND);
        
        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] No Bearer token found\n', FILE_APPEND);
            throw new CustomUserMessageAuthenticationException('Token manquant');
        }

        $token = substr($authHeader, 7); // Enlever "Bearer "
        file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] Token found: ' . substr($token, 0, 20) . "...\n", FILE_APPEND);

        try {
            // Décoder le JWT
            $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? null;
            if (!$jwtSecret) {
                // Ajout : charger le .env si la variable n'est pas trouvée
                if (file_exists(__DIR__ . '/../../.env')) {
                    $dotenv = new \Symfony\Component\Dotenv\Dotenv();
                    $dotenv->load(__DIR__ . '/../../.env');
                    $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? null;
                }
            }
            file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] JWT_SECRET used: ' . ($jwtSecret ? substr($jwtSecret, 0, 8) : 'AUCUN') . "\n", FILE_APPEND);
            if (!$jwtSecret) {
                file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] JWT_SECRET non défini dans l\'environnement !\n', FILE_APPEND);
                throw new \Exception('JWT_SECRET non défini');
            }
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key($jwtSecret, 'HS256'));
            file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] JWT decoded successfully: ' . json_encode($decoded) . "\n", FILE_APPEND);
            
            // Récupérer l'utilisateur par ID
            $user = $this->entityManager->getRepository(\App\Entity\User::class)->find($decoded->user_id);
            
            if (!$user) {
                file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] User not found for ID: ' . $decoded->user_id . "\n", FILE_APPEND);
                throw new CustomUserMessageAuthenticationException('Utilisateur non trouvé');
            }

            file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] User found: ' . $user->getEmail() . "\n", FILE_APPEND);

            return new \Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport(
                new \Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge($user->getEmail(), function($userIdentifier) {
                    return $this->entityManager->getRepository(\App\Entity\User::class)->findOneBy(['email' => $userIdentifier]);
                })
            );
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../../jwt_debug.log', '[authenticate] Error decoding JWT: ' . $e->getMessage() . ', token reçu: ' . substr($token, 0, 20) . "...\n", FILE_APPEND);
            throw new CustomUserMessageAuthenticationException('Token invalide');
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        file_put_contents(__DIR__ . '/../../jwt_debug.log', '[onAuthenticationSuccess] User: ' . $token->getUserIdentifier() . "\n", FILE_APPEND);
        // Laisser passer la requête
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        file_put_contents(__DIR__ . '/../../jwt_debug.log', '[onAuthenticationFailure] ' . $exception->getMessage() . "\n", FILE_APPEND);
        return new JsonResponse([
            'error' => 'Non authentifié'
        ], 401);
    }
} 