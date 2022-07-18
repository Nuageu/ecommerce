const stripe = Stripe(stripePublicKey);

function initialize() {
    document.querySelector("#payment-form").addEventListener("submit", handleSubmit);
    elements = stripe.elements({ clientSecret });

    const paymentElement = elements.create("payment");
    paymentElement.mount("#payment-element");
}

async function handleSubmit(e) {
    e.preventDefault();

    const { error } = await stripe.confirmPayment({
        elements,
        confirmParams: {
        // Make sure to change this to your payment completion page
        return_url: redirectAfterSuccessUrl,
        },
    });

    // This point will only be reached if there is an immediate error when
    // confirming the payment. Otherwise, your customer will be redirected to
    // your `return_url`. For some payment methods like iDEAL, your customer will
    // be redirected to an intermediate site first to authorize the payment, then
    // redirected to the `return_url`.
    if (error.type === "card_error" || error.type === "validation_error") {
        console.log(result);
    } else {
        // the payment succeeded!
        window.location.href = redirectAfterSuccessUrl ;
    }
}

// Fetches the payment intent status after payment submission
async function checkStatus() {
    const clientSecret = new URLSearchParams(window.location.search).get(
        "payment_intent_client_secret"
    );

    if (!clientSecret) {
        return;
    }

    const { paymentIntent } = await stripe.retrievePaymentIntent(clientSecret);

    switch (paymentIntent.status) {
        case "succeeded":
        console.log("Payment succeeded!");
        break;
        case "processing":
        console.log("Your payment is processing.");
        break;
        case "requires_payment_method":
        console.log("Your payment was not successful, please try again.");
        break;
        default:
        console.log("Something went wrong.");
        break;
    }
}

initialize();
checkStatus();