<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Driver : mock | flexpay | shwary
    |--------------------------------------------------------------------------
    | mock   : paiement marqué payé immédiatement (développement / démo).
    | flexpay: agrégateur Mobile Money RDC (Airtel, Orange, Afrimoney, M-Pesa).
    |          Inscription & clés : https://flexpay.cd
    */
    'driver' => env('MOBILE_MONEY_DRIVER', 'mock'),

    'flexpay' => [
        'api_key' => env('FLEXPAY_API_KEY'),
        'merchant' => env('FLEXPAY_MERCHANT_ID'),
        'currency' => env('FLEXPAY_CURRENCY', 'CDF'),
        'pay_url' => env('FLEXPAY_PAY_URL', 'https://backend.flexpay.cd/api/rest/v1/paymentService'),
        /*
         * URL appelée par Flexpay une fois le client confirmé sur téléphone (USSD).
         * À configurer dans le tableau Flexpay : doit pointer vers votre domaine public
         * (ex: https://votredomaine.cd/webhooks/flexpay).
         */
        'callback_url' => env('FLEXPAY_CALLBACK_URL', env('APP_URL').'/webhooks/flexpay'),
        /*
         * Optionnel : si défini, le webhook doit envoyer le header X-Webhook-Secret avec cette valeur.
         * (À configurer côté Flexpay si leur interface le permet, sinon laissez vide en local.)
         */
        'webhook_secret' => env('FLEXPAY_WEBHOOK_SECRET'),
    ],

    'shwary' => [
        'merchant_id' => env('SHWARY_MERCHANT_ID'),
        'merchant_key' => env('SHWARY_MERCHANT_KEY'),
        'country' => env('SHWARY_COUNTRY', 'DRC'),
        'currency' => env('SHWARY_CURRENCY', 'CDF'),
        'sandbox' => env('SHWARY_SANDBOX', true),
        'min_amount_cdf' => (int) env('SHWARY_MIN_AMOUNT_CDF', 2900),
        'base_url' => env('SHWARY_BASE_URL'),
        'payment_url' => env('SHWARY_PAYMENT_URL'),
        'transaction_url' => env('SHWARY_TRANSACTION_URL'),
        'callback_url' => env('SHWARY_CALLBACK_URL', env('APP_URL').'/webhooks/shwary'),
        'webhook_secret' => env('SHWARY_WEBHOOK_SECRET'),
    ],
];
