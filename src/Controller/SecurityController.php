<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class SecurityController extends AbstractController
{
    // Routes désactivées car gérées par React
    // #[Route(path: '/login', name: 'app_login')]
    // public function login(AuthenticationUtils $authenticationUtils): Response
    // {
    //     // if ($this->getUser()) {
    //     //     return $this->redirectToRoute('target_path');
    //     // }

    //     // get the login error if there is one
    //     $error = $authenticationUtils->getLastAuthenticationError();
    //     // last username entered by the user
    //     $lastUsername = $authenticationUtils->getLastUsername();

    //     return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    // }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        // This method can be blank - it will be intercepted by the logout key on your firewall.
        // If you want to add custom logic before logout, you can add it here.
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function apiLogin(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user || !$passwordHasher->isPasswordValid($user, $password)) {
            return $this->json(['error' => 'Identifiants invalides'], 401);
        }

        // Générer un token JWT simple
        $payload = [
            'user_id' => $user->getId(),
            'email' => $user->getEmail(),
            'username' => $user->getUsername(),
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24) // 24 heures
        ];

        $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? 'default_jwt_secret';
        $jwt = JWT::encode($payload, $jwtSecret, 'HS256');

        return $this->json([
            'token' => $jwt,
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ]
        ]);
    }

    #[Route('/api/me', name: 'api_me', methods: ['GET'])]
    public function apiMe(Request $request, EntityManagerInterface $entityManager): Response
    {
        $authHeader = $request->headers->get('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->json(['error' => 'Token manquant'], 401);
        }

        $token = substr($authHeader, 7); // Enlever "Bearer "

        try {
            // Décoder le JWT
            $jwtSecret = $_ENV['JWT_SECRET'] ?? $_SERVER['JWT_SECRET'] ?? 'default_jwt_secret';
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));
            
            // Récupérer l'utilisateur
            $user = $entityManager->getRepository(User::class)->find($decoded->user_id);
            
            if (!$user) {
                return $this->json(['error' => 'Utilisateur non trouvé'], 401);
            }

            return $this->json([
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ]);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Token invalide'], 401);
        }
    }

    #[Route('/auth/status', name: 'auth_status')]
    public function authStatus(Request $request): Response
    {
        $user = $this->getUser();
        $session = $request->getSession();
        
        // Debug: Log les informations de session
        error_log("DEBUG AUTH STATUS - Session ID: " . $session->getId());
        error_log("DEBUG AUTH STATUS - User: " . ($user ? $user->getEmail() : 'NULL'));
        error_log("DEBUG AUTH STATUS - Session data: " . json_encode($session->all()));
        
        // Vérifier le token de sécurité
        $tokenStorage = $this->container->get('security.token_storage');
        $token = $tokenStorage->getToken();
        error_log("DEBUG AUTH STATUS - Security token: " . ($token ? 'EXISTS' : 'NULL'));
        if ($token) {
            error_log("DEBUG AUTH STATUS - Token user: " . $token->getUserIdentifier());
        }
        
        $status = [
            'authenticated' => $user !== null,
            'session_id' => $session->getId(),
            'user' => $user ? [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'username' => $user->getUsername(),
                'roles' => $user->getRoles(),
            ] : null,
            'session_data' => $session->all(),
        ];
        
        return $this->json($status);
    }
}
