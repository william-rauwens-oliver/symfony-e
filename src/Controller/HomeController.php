<?php

namespace App\Controller;

use App\Repository\PublicationRepository;
use App\Repository\UserRepository;
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
    // J'ai désactivé cette route car elle est gérée par React
    // #[Route('/', name: 'app_home')]
    // public function index(
    //     PublicationRepository $publicationRepository,
    //     Request $request,
    //     EntityManagerInterface $entityManager
    // ): Response
    // {
    //     $publication = new Publication();
    //     $form = $this->createForm(PublicationType::class, $publication);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $publication->setUser($this->getUser());
    //         $entityManager->persist($publication);
    //         $entityManager->flush();
    //         $this->addFlash('success', 'Publication créée avec succès !');
    //         return $this->redirectToRoute('app_home');
    //     }

    //     $publications = $publicationRepository->findBy([], ['createdAt' => 'DESC']);

    //     return $this->render('home/index.html.twig', [
    //         'publications' => $publications,
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/search', name: 'app_search')]
    public function search(Request $request, PublicationRepository $publicationRepository, UserRepository $userRepository): Response
    {
        $query = $request->query->get('q', '');
        $results = [
            'publications' => [],
            'users' => [],
            'hashtags' => [],
        ];
        if ($query) {
            $results['publications'] = $publicationRepository->searchPublications($query);
            $results['users'] = $userRepository->findBy(['username' => $query]);
            // TODO: Ajouter la recherche de hashtags si besoin
        }
        return $this->render('home/search.html.twig', [
            'query' => $query,
            'results' => $results,
        ]);
    }

    #[Route('/hashtag/{tag}', name: 'app_hashtag')]
    public function hashtag(string $tag, PublicationRepository $publicationRepository): Response
    {
        $publications = $publicationRepository->findByHashtag($tag);

        return $this->render('home/hashtag.html.twig', [
            'publications' => $publications,
            'tag' => $tag,
        ]);
    }

    #[Route('/suggested', name: 'app_suggested')]
    public function suggested(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirect('/login');
        }
        // Le service de suggestion manque, je retourne une liste vide ou un message explicite
        $suggestions = [];
        $message = 'Le service de suggestion est temporairement indisponible.';
        return $this->render('home/suggested.html.twig', [
            'suggestions' => $suggestions,
            'message' => $message,
        ]);
    }

    #[Route('/trending', name: 'app_trending')]
    public function trending(PublicationRepository $publicationRepository): Response
    {
        $publications = $publicationRepository->findTrendingPublications();

        return $this->render('home/trending.html.twig', [
            'publications' => $publications,
        ]);
    }
}
