<?php

namespace App\Services\MobileMoney;

/**
 * Résultat d’une demande de paiement Mobile Money.
 */
final class PaymentInitResult
{
    public function __construct(
        public readonly bool $success,
        public readonly bool $completedImmediately,
        public readonly string $message,
        public readonly ?string $reference = null,
        public readonly ?string $orderNumber = null,
    ) {
    }
}
