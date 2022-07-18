<?php

namespace App\Cart;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;


class CartService
{
    protected $request;
    protected $productRepository;

    public function __construct(RequestStack $request, ProductRepository $productRepository)
    {
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    public function empty()
    {
        $this->request->getSession()->set('cart', []);
    }
    public function add($id)
    {
        $cart = $this->request->getSession()->get('cart', []);


        //3. voir si le produit ($id) existe déjà dans le tableau
        //4.si c'est le cas , simplement augmenter la quantité
        //5. Sinon ajouter le produit avec la quantité 1

        if (array_key_exists($id, $cart)) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        //6. Enregistrer le tableau mis a jour dans la sessions
        $this->request->getSession()->set('cart', $cart);
    }

    public function remove(int $id)
    {
        $cart = $this->request->getSession()->get('cart', []);

        unset($cart[$id]);

        $this->request->getSession()->set('cart', $cart);
    }

    public function decrement(int $id)
    {
        $cart = $this->request->getSession()->get('cart', []);

        if (!array_key_exists($id, $cart)) {
            return;
        }

        // soit le produit est à 1 en quantity alors il faut simplement le supprimer

        if ($cart[$id] === 1) {
            $this->remove($id);
            return;
        }


        //soit le produit est a plus de 1 alors il faut décrémenter

        $cart[$id]--;

        $this->request->getSession()->set('cart', $cart);
    }

    public function getTotal(): int
    {
        $total = 0;

        foreach ($this->request->getSession()->get('cart', []) as $id => $qty) {
            $product = $this->productRepository->find($id);

            if (!$product) {
                continue;
            }

            $total += $product->getPrice() * $qty;
        }
        return $total;
    }


    public function getDetailedCartItem(): array
    {
        $detailedCart = [];
        // [12 => ['product' => .... ,'quantity' => qty]]
        foreach ($this->request->getSession()->get('cart', []) as $id => $qty) {
            $product = $this->productRepository->find($id);

            if (!$product) {
                continue;
            }

            $detailedCart[] = new CartItem($product, $qty);
        }

        return $detailedCart;
    }
}
