<?php

namespace App\Controller\Api;

use App\Entity\Publication;
use App\Repository\PublicationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api')]
class PublicationApiController extends AbstractController
{
    #[Route('/publications', name: 'api_publications_list', methods: ['GET'])]
    public function list(
        PublicationRepository $publicationRepository,
        SerializerInterface $serializer,
        Request $request
    ): JsonResponse {
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        
        // Récupérer les publications avec pagination
        $publications = $publicationRepository->findAllWithPagination($page, $limit);
        
        // Sérialiser avec des groupes spécifiques pour éviter les relations circulaires
        $data = $serializer->serialize($publications, 'json', [
            'groups' => ['publication:read'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/publications/{id}', name: 'api_publication_show', methods: ['GET'])]
    public function show(
        Publication $publication,
        SerializerInterface $serializer
    ): JsonResponse {
        $data = $serializer->serialize($publication, 'json', [
            'groups' => ['publication:read'],
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);
        
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/publications', name: 'api_publication_create', methods: ['POST'])]
    public function create(
        Request $request, 
        EntityManagerInterface $em, 
        Security $security
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        // Gestion des données (FormData ou JSON)
        $contentType = $request->headers->get('Content-Type');
        $texte = null;
        $image = null;
        $video = null;

        if (strpos($contentType, 'multipart/form-data') !== false) {
            // FormData (frontend React)
            $texte = $request->request->get('texte');
            $imageFile = $request->files->get('image');
            $videoFile = $request->files->get('video');
            
            // Gestion des fichiers uploadés
            if ($imageFile) {
                $filename = uniqid('img_') . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/images', $filename);
                $image = '/uploads/images/' . $filename;
            }
            
            if ($videoFile) {
                $filename = uniqid('vid_') . '.' . $videoFile->guessExtension();
                $videoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/videos', $filename);
                $video = '/uploads/videos/' . $filename;
            }
        } else {
            // JSON
            $data = json_decode($request->getContent(), true);
            $texte = $data['texte'] ?? null;
            $image = $data['image'] ?? null;
            $video = $data['video'] ?? null;
        }
        
        if (!$texte || trim($texte) === '') {
            return $this->json(['error' => 'Le texte est obligatoire'], 400);
        }

        $publication = new Publication();
        $publication->setTexte(trim($texte));
        $publication->setUser($user);
        $publication->setCreatedAt(new \DateTimeImmutable());

        // Gestion image
        if ($image) {
            $publication->setImage($image);
        }

        // Gestion vidéo
        if ($video) {
            $publication->setVideo($video);
        }

        $em->persist($publication);
        $em->flush();

        return $this->json([
            'id' => $publication->getId(),
            'texte' => $publication->getTexte(),
            'image' => $publication->getImage(),
            'video' => $publication->getVideo(),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
            'createdAt' => $publication->getCreatedAt()?->format('c'),
        ], 201);
    }

    #[Route('/publications/{id}', name: 'api_publication_update', methods: ['PUT'])]
    public function update(
        Publication $publication,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user || $publication->getUser() !== $user) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        // Gestion des données (FormData ou JSON)
        $contentType = $request->headers->get('Content-Type');
        $texte = null;
        $image = null;
        $video = null;

        if (strpos($contentType, 'multipart/form-data') !== false) {
            // FormData (frontend React)
            $texte = $request->request->get('texte');
            $imageFile = $request->files->get('image');
            $videoFile = $request->files->get('video');
            
            // Gestion des fichiers uploadés
            if ($imageFile) {
                $filename = uniqid('img_') . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/images', $filename);
                $image = '/uploads/images/' . $filename;
            }
            
            if ($videoFile) {
                $filename = uniqid('vid_') . '.' . $videoFile->guessExtension();
                $videoFile->move($this->getParameter('kernel.project_dir') . '/public/uploads/videos', $filename);
                $video = '/uploads/videos/' . $filename;
            }
        } else {
            // JSON
            $data = json_decode($request->getContent(), true);
            $texte = $data['texte'] ?? null;
            $image = $data['image'] ?? null;
            $video = $data['video'] ?? null;
        }
        
        if (isset($texte)) {
            $publication->setTexte($texte);
        }
        
        if (isset($image)) {
            $publication->setImage($image);
        }
        
        if (isset($video)) {
            $publication->setVideo($video);
        }

        $em->flush();

        return $this->json([
            'id' => $publication->getId(),
            'texte' => $publication->getTexte(),
            'image' => $publication->getImage(),
            'video' => $publication->getVideo(),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
            'createdAt' => $publication->getCreatedAt()?->format('c'),
        ]);
    }

    #[Route('/publications/{id}', name: 'api_publication_delete', methods: ['DELETE'])]
    public function delete(
        Publication $publication,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user || $publication->getUser() !== $user) {
            return $this->json(['error' => 'Non autorisé'], 403);
        }

        $em->remove($publication);
        $em->flush();

        return $this->json(['message' => 'Publication supprimée'], 200);
    }

    #[Route('/publications/{id}/commentaires', name: 'api_publication_commentaires', methods: ['GET'])]
    public function commentaires(
        Publication $publication,
        SerializerInterface $serializer
    ): JsonResponse {
        $commentaires = array_filter(
            $publication->getCommentaires()->toArray(),
            fn($c) => $c instanceof \App\Entity\Commentaire
        );
        try {
            $data = $serializer->serialize($commentaires, 'json', [
                'groups' => ['commentaire:read'],
                'max_depth' => 1,
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);
        } catch (\Throwable $e) {
            file_put_contents('var/comment_serialize_error.log', $e->getMessage() . "\n" . print_r($commentaires, true), FILE_APPEND);
            throw $e;
        }
        return new JsonResponse($data, 200, [], true);
    }

    #[Route('/publications/{id}/commentaires', name: 'api_publication_commentaire_create', methods: ['POST'])]
    public function createCommentaire(
        Publication $publication,
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return $this->json(['error' => 'Non authentifié'], 401);
        }

        $data = json_decode($request->getContent(), true);
        $texte = $data['texte'] ?? null;
        
        if (!$texte || trim($texte) === '') {
            return $this->json(['error' => 'Le texte est obligatoire'], 400);
        }

        $commentaire = new \App\Entity\Commentaire();
        $commentaire->setTexte(trim($texte));
        $commentaire->setUser($user);
        $commentaire->setPublication($publication);
        $commentaire->setCreatedAt(new \DateTimeImmutable());

        $em->persist($commentaire);
        $em->flush();

        return $this->json([
            'id' => $commentaire->getId(),
            'texte' => $commentaire->getTexte(),
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ],
            'createdAt' => $commentaire->getCreatedAt()?->format('c'),
        ], 201);
    }


} 