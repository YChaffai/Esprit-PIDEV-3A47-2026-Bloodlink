<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class Konnect
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $konnectApiKey,
        private readonly string $konnectApiUrl,
        private readonly string $receiverWalletId,
        private readonly string $publicBaseUrl,
    ) {}

    /**
     * @return array{payUrl?:string, raw?:array}
     */
    public function initPayment(array $payload): array
    {
        $response = $this->httpClient->request('POST', $this->konnectApiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'x-api-key' => $this->konnectApiKey,
            ],
            'json' => $payload,
        ]);

        // don't throw on non-200 automatically
        $data = $response->toArray(false);

        return [
            'payUrl' => $data['payUrl'] ?? null,
            'raw' => $data,
        ];
    }

    public function buildPayload(array $input): array
    {
        // amount must be integer (Konnect expects int)
        $amount = (int) $input['amount'];

        // example success/fail URLs as Symfony routes (recommended)
        $successUrl = rtrim($this->publicBaseUrl, '/')
            . '/payment/success?formation=' . urlencode((string)$input['id_formation'])
            . '&prix=' . urlencode((string)$input['prix'])
            . '&user=' . urlencode((string)$input['id_user']);

        $failUrl = rtrim($this->publicBaseUrl, '/') . '/payment/fail';

        return [
            'receiverWalletId' => $this->receiverWalletId,
            'token' => 'TND',
            'amount' => $amount,
            'type' => 'immediate',
            'description' => 'Payment for Order',
            'acceptedPaymentMethods' => ['wallet', 'bank_card', 'e-DINAR', 'flouci'],
            'lifespan' => 15,
            'checkoutForm' => true,
            'addPaymentFeesToAmount' => true,
            'firstName' => (string) $input['firstName'],
            'lastName' => (string) $input['lastName'],
            'phoneNumber' => (string) $input['phoneNumber'],
            'email' => (string) $input['email'],
            'orderId' => $input['orderId'] ?? uniqid('order_', true),

            // IMPORTANT: set real webhook in prod
            'webhook' => $input['webhook'] ?? null,
            'silentWebhook' => true,

            'successUrl' => $successUrl,
            'failUrl' => $failUrl,
            'theme' => 'light',
        ];
    }
}