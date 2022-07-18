<?php

namespace App\Controller\Purchase;

use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchasePaymentController extends AbstractController
{

    #[Route('/purchase/pay/{id}', name: 'purchase_payment_form')]
    #[isGranted("ROLE_USER")]
    public function showCardForm($id, PurchaseRepository $purchaseRepository)
    {
        $purchase = $purchaseRepository->find($id);

        if (
            !$purchase ||
            ($purchase && $purchase->getUser() !== $this->getUser()) ||
            ($purchase && $purchase->getStatus() === Purchas e::STATUS_PAID)
        ) {
            return $this->redirectToRoute('cart_show');
        }

        \Stripe\Stripe::setApiKey('sk_test_51LMvh7B7x3vTgZYbUodNGB6ncMNcshgkpWE6NLbjIGPSK02B716TEPiRBb9OWPdHK4MGIsP9xmM4o3OFZBDClrbp00rwkoFSyM');

        $intent = \Stripe\PaymentIntent::create([
            'amount' => $purchase->getTotal(),
            'currency' => 'eur'
        ]);



        return $this->render('purchase/payment.html.twig', [
            'clientSecret' => $intent->client_secret,
            'purchase' => $purchase
        ]);
    }
}
