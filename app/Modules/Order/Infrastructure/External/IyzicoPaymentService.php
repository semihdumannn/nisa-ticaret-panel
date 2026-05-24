<?php

namespace App\Modules\Order\Infrastructure\External;

use App\Models\Order;
use App\Models\User;
use Iyzipay\Model\Address as IyziAddress;
use Iyzipay\Model\BasketItem;
use Iyzipay\Model\BasketItemType;
use Iyzipay\Model\Buyer;
use Iyzipay\Model\PaymentGroup;
use Iyzipay\Model\CheckoutFormInitialize;
use Iyzipay\Model\RetrieveCheckoutForm;
use Iyzipay\Request\CreateCheckoutFormInitializeRequest;
use Iyzipay\Request\RetrieveCheckoutFormRequest;
use Iyzipay\Options;
use Illuminate\Support\Facades\Log;

class IyzicoPaymentService
{
    private Options $options;

    public function __construct()
    {
        $this->options = new Options();
        $this->options->setApiKey(config('services.iyzico.api_key'));
        $this->options->setSecretKey(config('services.iyzico.secret_key'));
        $this->options->setBaseUrl(config('services.iyzico.base_url', 'https://sandbox-api.iyzipay.com'));
    }

    /**
     * Initialize iyzico Checkout Form for an order.
     * Returns the checkout form page URL to redirect the user to.
     */
    public function initializeCheckout(Order $order, User $customer, string $callbackUrl): array
    {
        $request = new CreateCheckoutFormInitializeRequest();
        $request->setLocale('tr');
        $request->setConversationId((string) $order->id);
        $request->setPrice(number_format((float) $order->subtotal - (float) $order->discount_amount, 2, '.', ''));
        $request->setPaidPrice(number_format((float) $order->total, 2, '.', ''));
        $request->setCurrency('TRY');
        $request->setBasketId((string) $order->id);
        $request->setPaymentGroup(PaymentGroup::PRODUCT);
        $request->setCallbackUrl($callbackUrl);

        // Buyer
        $buyer = new Buyer();
        $buyer->setId((string) $customer->id);
        $buyer->setName($customer->name ?? 'Unknown');
        $buyer->setSurname('—');
        $buyer->setGsmNumber($customer->phone ?? '+905000000000');
        $buyer->setEmail($customer->email ?? 'noemail@nisa.com');
        $buyer->setIdentityNumber('11111111111'); // TR national ID (sandbox)
        $buyer->setRegistrationAddress($order->address->full_address ?? 'N/A');
        $buyer->setCity($order->address->city ?? 'Istanbul');
        $buyer->setCountry('Turkey');
        $request->setBuyer($buyer);

        // Shipping & billing address
        $addr = new IyziAddress();
        $addr->setContactName($customer->name ?? 'Customer');
        $addr->setCity($order->address->city ?? 'Istanbul');
        $addr->setCountry('Turkey');
        $addr->setAddress($order->address->full_address ?? 'N/A');
        $request->setShippingAddress($addr);
        $request->setBillingAddress($addr);

        // Basket items
        $items = [];
        foreach ($order->items as $item) {
            $bi = new BasketItem();
            $bi->setId((string) $item->id);
            $bi->setName($item->product->name ?? 'Product');
            $bi->setCategory1('Beverage');
            $bi->setItemType(BasketItemType::PHYSICAL);
            $bi->setPrice(number_format((float) $item->total_price, 2, '.', ''));
            $items[] = $bi;
        }
        $request->setBasketItems($items);

        try {
            $form = CheckoutFormInitialize::create($request, $this->options);

            if ($form->getStatus() !== 'success') {
                Log::error('iyzico init failed', [
                    'order_id'       => $order->id,
                    'error_code'     => $form->getErrorCode(),
                    'error_message'  => $form->getErrorMessage(),
                ]);

                return [
                    'success' => false,
                    'message' => $form->getErrorMessage() ?? 'Payment initialization failed.',
                ];
            }

            return [
                'success'          => true,
                'checkout_form_url' => $form->getPaymentPageUrl(),
                'token'            => $form->getToken(),
            ];
        } catch (\Throwable $e) {
            Log::error('iyzico exception', ['error' => $e->getMessage(), 'order_id' => $order->id]);

            return [
                'success' => false,
                'message' => 'Payment service unavailable.',
            ];
        }
    }

    /**
     * Retrieve a completed checkout form by token and verify payment.
     */
    public function retrieveCheckoutForm(string $token): array
    {
        $request = new RetrieveCheckoutFormRequest();
        $request->setLocale('tr');
        $request->setToken($token);

        try {
            $form = RetrieveCheckoutForm::create($request, $this->options);

            $success = $form->getStatus() === 'success'
                && $form->getPaymentStatus() === 'SUCCESS';

            return [
                'success'          => $success,
                'payment_id'       => $form->getPaymentId(),
                'conversation_id'  => $form->getConversationId(),
                'fraud_status'     => $form->getFraudStatus(),
                'error_code'       => $form->getErrorCode(),
                'error_message'    => $form->getErrorMessage(),
            ];
        } catch (\Throwable $e) {
            Log::error('iyzico retrieve exception', ['error' => $e->getMessage()]);

            return [
                'success'       => false,
                'error_message' => 'Payment verification failed.',
            ];
        }
    }
}
