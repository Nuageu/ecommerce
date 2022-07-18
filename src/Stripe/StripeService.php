<?php

namespace App\Stripe;

use App\Entity\Purchase;

class StripeService
{

    protected $secretKey;
    protected $publicKey;

    public function __construct(string $publicKey, string $secretKey)
    {
        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function getPaymentIntent(Purchase $purchase)
    {
        \Stripe\Stripe::setApiKey('sk_test_51LMvh7B7x3vTgZYbUodNGB6ncMNcshgkpWE6NLbjIGPSK02B716TEPiRBb9OWPdHK4MGIsP9xmM4o3OFZBDClrbp00rwkoFSyM');

        return \Stripe\PaymentIntent::create([
            'amount' => $purchase->getTotal(),
            'currency' => 'eur'
        ]);
    }
}
