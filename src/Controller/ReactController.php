<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReactController extends AbstractController
{
    #[Route('/', name: 'react_home')]
    public function home(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/login', name: 'react_login')]
    public function login(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/register', name: 'react_register')]
    public function register(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/profile/{id}', name: 'react_profile')]
    public function profile(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/profile/username/{username}', name: 'react_profile_username')]
    public function profileUsername(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/profile/edit', name: 'react_profile_edit')]
    public function profileEdit(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/profile/delete', name: 'react_profile_delete')]
    public function profileDelete(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/suggested', name: 'react_suggested')]
    public function suggested(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/search', name: 'react_search')]
    public function search(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/hashtag/{tag}', name: 'react_hashtag')]
    public function hashtag(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/likes', name: 'react_likes')]
    public function likes(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/legal', name: 'react_legal')]
    public function legal(): Response
    {
        return $this->render('react_app.html.twig');
    }

    #[Route('/privacy', name: 'react_privacy')]
    public function privacy(): Response
    {
        return $this->render('react_app.html.twig');
    }
} 