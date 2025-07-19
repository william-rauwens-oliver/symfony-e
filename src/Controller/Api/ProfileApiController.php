<?php

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/profile')]
class ProfileApiController extends AbstractController
{
    #[Route('/update', name: 'api_profile_update', methods: ['PUT'])]
    public function updateProfile(
        Request $request,
        TokenStorageInterface $tokenStorage,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): JsonResponse {
        $token = $tokenStorage->getToken();
        if (!$token || !$token->getUser() instanceof User) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $user = $token->getUser();
        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse(['error' => 'Données invalides'], 400);
        }

        // Mettre à jour l'email si fourni
        if (isset($data['email']) && $data['email'] !== null) {
            $user->setEmail($data['email']);
        }

        // Mettre à jour le username si fourni
        if (isset($data['username']) && $data['username'] !== null) {
            $user->setUsername($data['username']);
        }

        // Mettre à jour le mot de passe si fourni et non vide
        if (isset($data['password']) && $data['password'] !== null && $data['password'] !== '') {
            $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
            $user->setPassword($hashedPassword);
        }

        // Valider l'entité
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return new JsonResponse(['error' => 'Validation échouée', 'details' => $errorMessages], 400);
        }

        try {
            $entityManager->persist($user);
            $entityManager->flush();

            return new JsonResponse([
                'message' => 'Profil mis à jour avec succès',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'username' => $user->getUsername()
                ]
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()], 500);
        }
    }
} 