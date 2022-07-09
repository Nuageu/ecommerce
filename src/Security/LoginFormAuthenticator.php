<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\CustomCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginFormAuthenticator extends AbstractAuthenticator
{

    protected $generator;
    protected $encoder;
    protected $userRepository;

    public function __construct(UrlGeneratorInterface $generator, UserPasswordHasherInterface $encoder, UserRepository $userRepository)
    {
        $this->generator = $generator;
        $this->encoder = $encoder;
        $this->userRepository = $userRepository;
    }
    public function supports(Request $request): ?bool
    {
        // TODO: Implement supports() method.s
        return $request->attributes->get('_route') === 'security_login'
            && $request->isMethod('POST');
    }

    public function authenticate(Request $request): Passport
    {
        // dd($request);
        $credentials = $request->get('login');

        $email = $credentials['email'];
        // dd($credentials);
        // dd($request);
        return new Passport(
            new UserBadge($email, function ($email) {
                $user = $this->userRepository->findOneBy(['email' => $email]);

                if (!$email) {
                    throw new AuthenticationException("mail pas bon");
                }
                return $user;
            }),
            new PasswordCredentials($credentials['password'])
        );
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        try {
            return $userProvider->loadUserByIdentifier($credentials['email']);
        } catch (AuthenticationException $e) {
            // throw new UserNotFoundException("Cette adresse email n'est pas connue");
            throw new UserNotFoundException("Cette adresse email n'est pas connue");
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->encoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new RedirectResponse('/');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // dd($exception);
        $login = $request->get('login');
        $request->getSession()->set(Security::LAST_USERNAME, $login['email']);
        return $request->attributes->set(Security::AUTHENTICATION_ERROR, $exception);
    }

    //    public function start(Request $request, AuthenticationException $authException = null): Response
    //    {
    //        /*
    //         * If you would like this class to control what happens when an anonymous user accesses a
    //         * protected page (e.g. redirect to /login), uncomment this method and make this class
    //         * implement Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface.
    //         *
    //         * For more details, see https://symfony.com/doc/current/security/experimental_authenticators.html#configuring-the-authentication-entry-point
    //         */
    //    }
}
