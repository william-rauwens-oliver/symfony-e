<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Publication;
use App\Form\PublicationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        PublicationRepository $publicationRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $publication = new Publication();
        $form = $this->createForm(PublicationType::class, $publication);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $publication->setUser($this->getUser());
            $publication->setCreatedAt(new \DateTimeImmutable());

            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $imageDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
                if (!is_dir($imageDirectory)) {
                    mkdir($imageDirectory, 0777, true);
                }
                $newFilename = uniqid().'.'.$imageFile->guessExtension();
                $imageFile->move($imageDirectory, $newFilename);
                $publication->setImage($newFilename);
            }

            // Gestion de l'upload de vidéo
            $videoFile = $form->get('video')->getData();
            if ($videoFile) {
                $videoDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/videos';
                if (!is_dir($videoDirectory)) {
                    mkdir($videoDirectory, 0777, true);
                }
                $newFilename = uniqid().'.'.$videoFile->guessExtension();
                $videoFile->move($videoDirectory, $newFilename);
                $publication->setVideo($newFilename);
            }

            $entityManager->persist($publication);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        // Gestion de l'ajout de commentaires
        if ($request->isMethod('POST') && $request->request->has('publication_id') && $request->request->has('comment_content')) {
            $publicationId = $request->request->get('publication_id');
            $commentContent = $request->request->get('comment_content');
            
            $publication = $publicationRepository->find($publicationId);
            if ($publication && $this->getUser() && !empty(trim($commentContent))) {
                $commentaire = new Commentaire();
                $commentaire->setUser($this->getUser());
                $commentaire->setPublication($publication);
                $commentaire->setContent(trim($commentContent));
                $commentaire->setCreatedAt(new \DateTimeImmutable());
                
                $entityManager->persist($commentaire);
                $entityManager->flush();
                
                return $this->redirectToRoute('app_home');
            }
        }

        $publications = $publicationRepository->findAllWithRelations();

        // Suppression de la génération de commentForms
        // foreach ($publications as $publication) {
        //     $commentaire = new Commentaire();
        //     $form = $this->createForm(CommentaireType::class, $commentaire, [
        //         'action' => $this->generateUrl('add_comment', ['id' => $publication->getId()])
        //     ]);
        //     $commentForms[$publication->getId()] = $form->createView();
        // }

        return $this->render('home/index.html.twig', [
            'publications' => $publications,
            'publicationForm' => $form->createView(),
            // 'commentForms' => $commentForms, // supprimé
        ]);
    }

    #[Route('/comment/{id}', name: 'add_comment', methods: ['POST'])]
    public function addComment(
        Request $request,
        EntityManagerInterface $entityManager,
        PublicationRepository $publicationRepository,
        int $id
    ): Response {
        $publication = $publicationRepository->find($id);
        if (!$publication) {
            throw $this->createNotFoundException('Publication non trouvée');
        }

        $commentaire = new Commentaire();
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commentaire->setUser($this->getUser());
            $commentaire->setPublication($publication);
            $commentaire->setCreatedAt(new \DateTimeImmutable());
            $entityManager->persist($commentaire);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home');
    }
}
