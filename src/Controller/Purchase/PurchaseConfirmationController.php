<?php

namespace App\Controller\Purchase;

use DateTimeImmutable;
use App\Entity\Purchase;
use App\Cart\CartService;
use App\Entity\PurchaseItem;
use App\Form\CartConfirmationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PurchaseConfirmationController extends AbstractController
{
    protected $formFactory;
    protected $router;
    protected $security;
    protected $cartService;
    protected $em;

    public function __construct(FormFactoryInterface $formFactory, RouterInterface $router, Security $security, CartService $cartService, EntityManagerInterface $em)
    {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->security = $security;
        $this->cartService = $cartService;
        $this->em = $em;
    }

    #[Route('/purchase/confirm', name: 'purchase_confirm')]
    public function confirm(Request $request)
    {
        //1. lire les données du formulaire formfactoryinterface / request

        $form = $this->formFactory->create(CartConfirmationType::class);

        $form->handleRequest($request);

        //2. si le formulaire n'a pas été soumis : redirect

        if (!$form->isSubmitted()) {

            //message flash puis redirections (FlashBagInterface)
            $this->addFlash('warning', 'vous devez remplir le formulaire de confirmation');

            return new RedirectResponse($this->router->generate('cart_show'));
        }

        //3. pas connecté redirect Security

        $user = $this->security->getUser();

        if (!$user) {
            throw new AccessDeniedException("Vous devez être connecté pour confirmer une commande");
        }

        //4. Pas de produit dans le panier redirect CartService

        $cartItems = $this->cartService->getDetailedCartItem();

        if (count($cartItems) === 0) {
            $this->addFlash('warning', 'Vous ne pouvez pas confirmer une commande avec un panier vide');
            return new RedirectResponse($this->router->generate('cart_show'));
        }

        //5. nous allons créér une purchase
        /** @var Purchase */
        $purchase = $form->getData();
        // dd($purchase);
        //6. nous allons la lier avec l'utilisateur actuellement connecté (sécurity)
        $purchase->setUser($user)
            ->setPurchasedAt(new DateTimeImmutable);



        // $purchase = new Purchase;

        // $purchase->setAddress($data['adress'])
        //     ->setFullName($data['fullname'])
        //     ->setPostalCode($data['postalCode']);

        //7.Nous allons la lier avec les produits qui sont dans le panier (cartservice)

        $total = 0;

        foreach ($this->cartService->getDetailedCartItem() as $cartItem) {
            $purchaseItem = new PurchaseItem;
            $purchaseItem->setPurchase($purchase)
                ->setProduct($cartItem->product)
                ->setProductName($cartItem->product->getName())
                ->setQuantity($cartItem->qty)
                ->setProductPrice($cartItem->product->getPrice())
                ->setTotal($cartItem->getTotal());

            $total += $cartItem->getTotal();

            $this->em->persist($purchaseItem);
        }

        $purchase->setTotal($total);

        $this->em->persist($purchase);
        //8. nous allons enregistrer la commande (entitymanagerinterface)

        $this->em->flush();

        $this->addFlash('success', "la commande a bien été enregistrée");

        return new RedirectResponse($this->router->generate('purchase_index'));
    }
}
