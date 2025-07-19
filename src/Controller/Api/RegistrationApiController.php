<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RegistrationApiController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['username'], $data['email'], $data['password'])) {
            return $this->json(['error' => 'Invalid data'], 400);
        }

        // Vérification unicité email/username
        $existing = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            return $this->json(['error' => 'Email déjà utilisé'], 409);
        }
        $existing = $em->getRepository(User::class)->findOneBy(['username' => $data['username']]);
        if ($existing) {
            return $this->json(['error' => 'Nom d\'utilisateur déjà utilisé'], 409);
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));

        $em->persist($user);
        $em->flush();

        return $this->json(['success' => true, 'user' => [
            'id' => $user->getId(),
            'username' => $user->getUsername(),
            'email' => $user->getEmail(),
        ]]);
    }
} 