<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Routing\RouterInterface;

class AppCustomAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('_username', '');
        $password = $request->request->get('_password', '');
        $csrfToken = $request->request->get('_csrf_token', '');

        // Debug: Log les données reçues
        error_log("DEBUG AUTH - Email: " . $email);
        error_log("DEBUG AUTH - Password: " . ($password ? 'SET' : 'NOT SET'));
        error_log("DEBUG AUTH - CSRF Token: " . $csrfToken);
        error_log("DEBUG AUTH - Session ID: " . $request->getSession()->getId());

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        try {
            $passport = new Passport(
                new UserBadge($email),
                new PasswordCredentials($password),
                [
                    new CsrfTokenBadge('form', $csrfToken),
                    new RememberMeBadge(),
                ]
            );
            error_log("DEBUG AUTH - Passport créé avec succès");
            return $passport;
        } catch (\Exception $e) {
            error_log("DEBUG AUTH - Erreur lors de la création du passport: " . $e->getMessage());
            throw $e;
        }
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Debug: Log le succès de l'authentification
        error_log("DEBUG AUTH SUCCESS - User: " . $token->getUserIdentifier());
        error_log("DEBUG AUTH SUCCESS - Session ID: " . $request->getSession()->getId());
        
        // S'assurer que la session est sauvegardée
        $request->getSession()->save();
        
        // Redirige vers la page d'accueil
        return new RedirectResponse($this->urlGenerator->generate('react_home'));
    }

    protected function getLoginUrl(Request $request): string
    {
        return '/login';
    }
}
