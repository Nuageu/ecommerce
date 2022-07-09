<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'security_login', priority: 1)]
    public function login(AuthenticationUtils $utils, FormFactoryInterface $factory): Response
    {
        $form = $this->createForm(LoginType::class, ['email' => $utils->getLastUsername()]);

        // $form = $factory->createNamed('', LoginType::class, ['_username' => $utils->getLastUsername()]);
        // dd($utils->getLastAuthenticationError());

        return $this->render('security/login.html.twig', [
            'formView' => $form->createView(),
            'error' => $utils->getLastAuthenticationError()
        ]);
    }

    #[Route('/logout', name: 'security_logout', priority: 1)]
    public function logout()
    {
    }
}
