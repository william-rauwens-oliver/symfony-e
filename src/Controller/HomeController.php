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
use App\Service\SuggestionService;

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

            $this->addFlash('success', 'Votre publication a été publiée avec succès !');
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
                
                $this->addFlash('success', 'Votre commentaire a été ajouté avec succès !');
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

    #[Route('/suggested', name: 'app_suggested')]
    public function suggested(
        SuggestionService $suggestionService
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }
        $suggestions = $suggestionService->suggestPublicationsForUser($user);
        return $this->render('home/suggested.html.twig', [
            'suggestions' => $suggestions,
        ]);
    }

    #[Route('/hashtag/{tag}', name: 'app_hashtag')]
    public function hashtag(string $tag, PublicationRepository $publicationRepository): Response
    {
        $publications = $publicationRepository->findByHashtag($tag);
        return $this->render('home/hashtag.html.twig', [
            'tag' => $tag,
            'publications' => $publications,
        ]);
    }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q', '');
        $users = $hashtags = $publications = [];
        if ($query) {
            // Recherche utilisateurs
            $users = $em->getRepository(\App\Entity\User::class)->createQueryBuilder('u')
                ->where('LOWER(u.username) LIKE :q OR LOWER(u.email) LIKE :q')
                ->setParameter('q', '%' . strtolower($query) . '%')
                ->setMaxResults(10)
                ->getQuery()->getResult();
            // Recherche hashtags (distincts)
            $publicationsWithTag = $em->getRepository(\App\Entity\Publication::class)->createQueryBuilder('p')
                ->where('LOWER(p.content) LIKE :tag')
                ->setParameter('tag', '%#' . strtolower($query) . '%')
                ->getQuery()->getResult();
            $hashtags = [];
            foreach ($publicationsWithTag as $pub) {
                foreach ($pub->getHashtags() as $tag) {
                    if (stripos($tag, $query) !== false && !in_array(strtolower($tag), $hashtags)) {
                        $hashtags[] = strtolower($tag);
                    }
                }
            }
            // Recherche publications
            $publications = $em->getRepository(\App\Entity\Publication::class)->createQueryBuilder('p')
                ->where('LOWER(p.content) LIKE :q')
                ->setParameter('q', '%' . strtolower($query) . '%')
                ->orderBy('p.createdAt', 'DESC')
                ->setMaxResults(20)
                ->getQuery()->getResult();
        }
        return $this->render('home/search.html.twig', [
            'query' => $query,
            'users' => $users,
            'hashtags' => $hashtags,
            'publications' => $publications,
        ]);
    }
}
