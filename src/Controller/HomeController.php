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
            $entityManager->persist($publication);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        $publications = $publicationRepository->findBy([], ['createdAt' => 'DESC']);

        $commentForms = [];
        foreach ($publications as $publication) {
            $commentaire = new Commentaire();
            $form = $this->createForm(CommentaireType::class, $commentaire, [
                'action' => $this->generateUrl('add_comment', ['id' => $publication->getId()])
            ]);
            $commentForms[$publication->getId()] = $form->createView();
        }

        return $this->render('home/index.html.twig', [
            'publications' => $publications,
            'publicationForm' => $form->createView(),
            'commentForms' => $commentForms,
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
