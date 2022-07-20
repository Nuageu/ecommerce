<?php

namespace App\Controller\Purchase;

use App\Cart\CartService;
use App\Entity\Purchase;
use App\Event\PurchaseSuccessEvent;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class PurchasePaymentSuccessController extends AbstractController
{

    #[Route('/purchase/terminate/{id}', name: 'purchase_payment_success')]
    #[isGranted("ROLE_USER")]
    public function success(
        $id,
        PurchaseRepository $purchaseRepository,
        EntityManagerInterface $em,
        CartService $cartService,
        EventDispatcherInterface $eventDispatcher
    ) {
        //1. recuperer la commande

        $purchase = $purchaseRepository->find($id);

        if (
            !$purchase ||
            ($purchase && $purchase->getUser() !== $this->getUser()) ||
            ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)
        ) {
            $this->addFlash('warning', " la commande n'existe pas !");
            return $this->redirectToRoute("purchase_index");
        }
        //2. je la fais passer au status Payée PAID

        $purchase->setStatus(Purchase::STATUS_PAID);

        $em->flush();
        //3. je vide le panier

        $cartService->empty();
        //4. je redirige avec un flash vers la liste des commandes

        //lancer un événement qui permette de réagir a la prise d'une commande
        $purchaseEvent = new PurchaseSuccessEvent($purchase);

        $eventDispatcher->dispatch($purchaseEvent, 'purchase.success');

        $this->addFlash('success', " la commande a été payée et confirmée !");

        return $this->redirectToRoute('purchase_index');
    }
}
