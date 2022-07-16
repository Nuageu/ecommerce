<?php


namespace App\Controller\Purchase;

use App\Entity\User;
use Twig\Environment;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class PurchasesListController extends AbstractController
{
    // protected $security;
    // protected $router;
    // protected $twig;

    // public function __construct(Security $security, RouterInterface $router, Environment $twig)
    // {
    //     $this->security = $security;
    //     $this->router = $router;
    //     $this->twig = $twig;
    // }

    #[Route('/purchases', name: 'purchase_index', priority: 1)]
    #[isGranted("ROLE_USER", message: "Vous connecté pour accéder à vos commandes")]

    public function index()
    {
        //1. Nous devons nous assurer que la personne est connectée (sinon redirection page d'acceuil)
        // -> Security
        /** @var User */
        $user = $this->getUser();

        // if (!$user) {
        //     throw new AccessDeniedException("Vous devez être connecté pour accéder a vos commandes");
        //     //Redirection -> RedirectResponse
        //     //Générer une URL en fonction d'une nom d'une route -> UrlGeneratorInterface
        // }
        //2.Nous Voulons savoir QUI est connecté
        //-> Security


        //3. Nous voulons passer l'utilisateur connecté a twig afin d'afficher ses commandes
        //Environment twig / response
        // dd($user->getPurchases()->getValues());

        return $this->render('purchase/index.html.twig', [
            'purchases' => $user->getPurchases()
        ]);
        // $html = $this->twig->render('purchase/index.html.twig', [
        //     'purchases' => $user->getPurchases()
        // ]);

        // return new Response($html);
    }
}
