<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'cart_add', requirements: ["id" => "\d+"])]
    public function add($id, Request $request, ProductRepository $productRepository, CartService $cartService, SessionInterface $session): Response
    {

        //0. Securisation : est ce que le produit exsite

        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas !");
        }

        //1. Retrouver le panier dans la sessions (tableau)
        //2. Si le tableau n'existe pas encore, alors prendre un tableau vide
        // [ 12 => 3 , 29 => 2]  [key => qty, etc]


        // $cart = $session->get('cart', []);


        // //3. voir si le produit ($id) existe déjà dans le tableau
        // //4.si c'est le cas , simplement augmenter la quantité
        // //5. Sinon ajouter le produit avec la quantité 1

        // if (array_key_exists($id, $cart)) {
        //     $cart[$id]++;
        // } else {
        //     $cart[$id] = 1;
        // }

        // //6. Enregistrer le tableau mis a jour dans la sessions
        // $session->set('cart', $cart);
        // $request->getSession()->remove('cart');

        // dd($session->get('cart'));
        // dd($session);

        // /** @var FlashBag */
        // $flashbag = $session->getBag('flashes');
        // $flashbag->add('success', "Le produit a bien été ajouté au panier");
        // $flashbag->add('info', "une petite information");
        // $flashbag->add('warning', "Attention");
        // $flashbag->add('success', "Un autre succès");
        // // $flashbag->add('success', "Tout s'est bien passé");
        // $flashbag->add('warning', "attentionT");

        $cartService->add($id);

        $this->addFlash('success', "Le produit a bien été ajouté au panier");

        // dd($flashbag);

        return $this->redirectToRoute('product_show', [
            'category_slug' => $product->getCategory()->getSlug(),
            'slug' => $product->getSlug()
        ]);
    }


    #[Route('/cart', name: 'cart_show')]
    public function show(CartService $cartService)
    {
        $detailedCart = $cartService->getDetailedCartItem();

        $total = $cartService->getTotal();

        // dd($detailedCart);
        // dd($session->get('cart'));
        return $this->render('cart/index.html.twig', [
            'items' => $detailedCart,
            'total' => $total
        ]);
    }

    #[Route('/cart/delete/{id}', name: 'cart_delete', requirements: ["id" => "\d+"])]
    public function delete($id, ProductRepository $productRepository, CartService $cartService)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas et ne peut pas être supprimé !");
        }

        $cartService->remove($id);

        $this->addFlash("success", "Le produit a bien été supprimé du panier");

        return $this->redirectToRoute('cart_show');
    }

    #[Route('/cart/decrement/{id}', name: 'cart_decrement', requirements: ["id" => "\d+"])]
    public function decrement($id, CartService $cartService, ProductRepository $productRepository)
    {
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas et ne peut pas être décrémenté !");
        }

        $cartService->decrement($id);

        $this->addFlash("success", "le produit a bien été décrémenté");

        return $this->redirectToRoute('cart_show');
    }
}
