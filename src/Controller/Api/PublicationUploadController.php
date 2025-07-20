<?php

namespace App\Controller\Api;

use App\Entity\Publication;
use App\Entity\PublicationInput;
use App\DataPersister\PublicationDataPersister;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[Route('/api')]
class PublicationUploadController extends AbstractController
{
    public function __construct(
        private PublicationDataPersister $dataPersister
    ) {}

    #[Route('/publications/upload', name: 'api_publication_upload', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function uploadPublication(Request $request): JsonResponse
    {
        try {
            // Log pour debug
            file_put_contents(__DIR__ . '/../../../logfile', "[PublicationUploadController] Début upload\n", FILE_APPEND);
            
            // Récupérer les données du formulaire
            $texte = $request->request->get('texte');
            $imageFile = $request->files->get('image');
            $videoFile = $request->files->get('video');
            
            file_put_contents(__DIR__ . '/../../../logfile', "[PublicationUploadController] Texte: " . ($texte ?? 'null') . "\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/../../../logfile', "[PublicationUploadController] Image: " . ($imageFile ? $imageFile->getClientOriginalName() : 'null') . "\n", FILE_APPEND);
            file_put_contents(__DIR__ . '/../../../logfile', "[PublicationUploadController] Vidéo: " . ($videoFile ? $videoFile->getClientOriginalName() : 'null') . "\n", FILE_APPEND);
            
            // Validation
            if (empty($texte)) {
                return new JsonResponse(['error' => 'Le texte ne doit pas être vide'], 400);
            }
            
            // Créer l'objet PublicationInput
            $publicationInput = new PublicationInput();
            $publicationInput->texte = $texte;
            $publicationInput->image = $imageFile;
            $publicationInput->video = $videoFile;
            
            // Utiliser le DataPersister pour traiter la publication
            $publication = $this->dataPersister->process($publicationInput, new \ApiPlatform\Metadata\Post());
            
            file_put_contents(__DIR__ . '/../../../logfile', "[PublicationUploadController] Publication créée avec ID: " . $publication->getId() . "\n", FILE_APPEND);
            
            return new JsonResponse([
                'id' => $publication->getId(),
                'texte' => $publication->getTexte(),
                'image' => $publication->getImage(),
                'video' => $publication->getVideo(),
                'createdAt' => $publication->getCreatedAt()->format('c'),
                'user' => [
                    'id' => $publication->getUser()->getId(),
                    'username' => $publication->getUser()->getUsername()
                ]
            ], 201);
            
        } catch (\Exception $e) {
            file_put_contents(__DIR__ . '/../../../logfile', "[PublicationUploadController] ERREUR: " . $e->getMessage() . "\n", FILE_APPEND);
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
} 