<?php

namespace App\Modules\Order\Domain\Contracts;

use App\Models\Order;
use App\Models\User;

interface PaymentServiceInterface
{
    /**
     * Initialize a hosted checkout form.
     * Returns ['success'=>bool, 'checkout_form_url'=>string, 'token'=>string]
     * or ['success'=>false, 'message'=>string] on failure.
     */
    public function initializeCheckout(Order $order, User $customer, string $callbackUrl): array;

    /**
     * Retrieve and verify a completed checkout form by token.
     * Returns ['success'=>bool, 'payment_id'=>?string, 'conversation_id'=>?string,
     *          'fraud_status'=>?int, 'error_code'=>?string, 'error_message'=>?string]
     */
    public function retrieveCheckoutForm(string $token): array;
}
