<?php

namespace App\Services\MobileMoney;

use App\Models\Contravention;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MobileMoneyService
{
    public function initiate(Contravention $contravention, string $telephone, string $operateur): PaymentInitResult
    {
        $driver = config('mobile_money.driver', 'mock');

        return match ($driver) {
            'flexpay' => $this->initiateFlexpay($contravention, $telephone),
            'shwary' => $this->initiateShwary($contravention, $telephone),
            default => $this->initiateMock($contravention, $telephone, $operateur),
        };
    }

    private function initiateMock(Contravention $contravention, string $telephone, string $operateur): PaymentInitResult
    {
        $reference = 'MM-MOCK-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));

        $contravention->paiement_fournisseur = 'mock';
        $contravention->paiement_statut = 'completed';
        $contravention->paiement_transaction_id = null;
        $contravention->reference_paiement = $reference;
        $contravention->statut = 'payee';
        $contravention->est_payee = true;
        $contravention->save();

        Log::info('MobileMoney mock payment', [
            'contravention_id' => $contravention->id,
            'telephone' => $telephone,
            'operateur' => $operateur,
            'reference' => $reference,
        ]);

        return new PaymentInitResult(
            success: true,
            completedImmediately: true,
            message: 'Paiement simulé enregistré avec succès.',
            reference: $reference,
        );
    }

    private function initiateFlexpay(Contravention $contravention, string $telephone): PaymentInitResult
    {
        $apiKey = config('mobile_money.flexpay.api_key');
        $merchant = config('mobile_money.flexpay.merchant');
        $url = config('mobile_money.flexpay.pay_url');
        $currency = config('mobile_money.flexpay.currency', 'CDF');
        $callbackUrl = config('mobile_money.flexpay.callback_url');

        if (empty($apiKey) || empty($merchant)) {
            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: 'Flexpay n\'est pas configuré (FLEXPAY_API_KEY / FLEXPAY_MERCHANT_ID).',
            );
        }

        $reference = 'RSH-' . $contravention->id . '-' . strtoupper(bin2hex(random_bytes(4)));
        $phone = $this->normalizeCongolesePhone($telephone);
        $amount = (string) (int) round((float) $contravention->montant);

        try {
            $response = Http::timeout(45)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'merchant' => $merchant,
                    'type' => '1',
                    'phone' => $phone,
                    'reference' => $reference,
                    'amount' => $amount,
                    'currency' => $currency,
                    'callbackUrl' => $callbackUrl,
                ]);
        } catch (\Throwable $e) {
            Log::error('Flexpay HTTP error', [
                'e' => $e->getMessage(),
                'contravention_id' => $contravention->id,
            ]);

            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: 'Impossible de joindre Flexpay. Réessayez plus tard.',
            );
        }

        $body = $response->json();

        if (!is_array($body)) {
            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: 'Réponse Flexpay invalide.',
            );
        }

        if (($body['code'] ?? null) === '0') {
            $contravention->paiement_fournisseur = 'flexpay';
            $contravention->paiement_statut = 'initiated';
            $contravention->paiement_transaction_id = $body['orderNumber'] ?? null;
            $contravention->reference_paiement = $reference;
            $contravention->save();

            return new PaymentInitResult(
                success: true,
                completedImmediately: false,
                message: 'Demande envoyée. Validez le paiement sur votre téléphone.',
                reference: $reference,
                orderNumber: $body['orderNumber'] ?? null,
            );
        }

        $message = $body['message'] ?? 'Paiement refusé par Flexpay.';

        return new PaymentInitResult(
            success: false,
            completedImmediately: false,
            message: is_string($message) ? $message : 'Paiement refusé par Flexpay.',
        );
    }

    private function initiateShwary(Contravention $contravention, string $telephone): PaymentInitResult
    {
        $merchantId = config('mobile_money.shwary.merchant_id');
        $merchantKey = config('mobile_money.shwary.merchant_key');
        $callbackUrl = config('mobile_money.shwary.callback_url');
        $country = config('mobile_money.shwary.country', 'DRC');
        $currency = config('mobile_money.shwary.currency', 'CDF');
        $paymentUrl = $this->shwaryPaymentUrl();
        $amount = (int) round((float) $contravention->montant);
        $phone = $this->normalizeE164CongolesePhone($telephone);
        $minAmount = (int) config('mobile_money.shwary.min_amount_cdf', 2900);

        if (empty($merchantId) || empty($merchantKey) || empty($paymentUrl)) {
            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: 'Shwary n\'est pas configure (SHWARY_MERCHANT_ID / SHWARY_MERCHANT_KEY / SHWARY_PAYMENT_URL).',
            );
        }

        if ($country === 'DRC' && $currency === 'CDF' && $amount < $minAmount) {
            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: "Le montant minimum Shwary pour la RDC est de {$minAmount} CDF.",
            );
        }

        if (!preg_match('/^\+243\d{9}$/', $phone)) {
            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: 'Le numero doit etre au format congolais +243XXXXXXXXX.',
            );
        }

        $reference = 'RSH-' . $contravention->id . '-' . strtoupper(bin2hex(random_bytes(4)));

        try {
            $response = Http::timeout(45)
                ->retry(2, 300)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $merchantKey,
                    'X-Merchant-Id' => $merchantId,
                    'X-Merchant-Key' => $merchantKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($paymentUrl, [
                    'merchant_id' => $merchantId,
                    'country' => $country,
                    'currency' => $currency,
                    'amount' => $amount,
                    'phone_number' => $phone,
                    'callback_url' => $callbackUrl,
                    'reference' => $reference,
                    'external_reference' => $reference,
                    'metadata' => [
                        'contravention_id' => $contravention->id,
                        'code_unique' => $contravention->code_unique,
                    ],
                ]);
        } catch (\Throwable $e) {
            Log::error('Shwary HTTP error', [
                'error' => $e->getMessage(),
                'contravention_id' => $contravention->id,
            ]);

            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: 'Impossible de joindre Shwary. Reessayez plus tard.',
            );
        }

        $body = $response->json();

        if (!$response->successful()) {
            Log::warning('Shwary payment refused', [
                'status' => $response->status(),
                'body' => $body,
                'contravention_id' => $contravention->id,
            ]);

            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: $this->paymentErrorMessage($response->status(), is_array($body) ? $body : []),
            );
        }

        if (!is_array($body)) {
            return new PaymentInitResult(
                success: false,
                completedImmediately: false,
                message: 'Reponse Shwary invalide.',
            );
        }

        $transactionId = $body['id']
            ?? $body['transaction_id']
            ?? $body['transactionId']
            ?? data_get($body, 'data.id')
            ?? data_get($body, 'data.transaction_id');
        $status = strtolower((string) ($body['status'] ?? data_get($body, 'data.status') ?? 'initiated'));
        $isCompleted = in_array($status, ['completed', 'success', 'successful', 'paid'], true);

        $contravention->paiement_fournisseur = 'shwary';
        $contravention->paiement_statut = $isCompleted ? 'completed' : 'initiated';
        $contravention->paiement_transaction_id = $transactionId ? (string) $transactionId : null;
        $contravention->reference_paiement = $reference;
        if ($isCompleted) {
            $contravention->statut = 'payee';
            $contravention->est_payee = true;
        }
        $contravention->save();

        return new PaymentInitResult(
            success: true,
            completedImmediately: $isCompleted,
            message: $isCompleted
                ? 'Paiement Shwary confirme avec succes.'
                : 'Demande envoyee via Shwary. Confirmez le paiement sur votre telephone.',
            reference: $reference,
            orderNumber: $transactionId ? (string) $transactionId : null,
        );
    }

    public function normalizeCongolesePhone(string $raw): string
    {
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        if (str_starts_with($digits, '243')) {
            return $digits;
        }

        if (str_starts_with($digits, '0')) {
            return '243' . substr($digits, 1);
        }

        if (strlen($digits) === 9) {
            return '243' . $digits;
        }

        return $digits;
    }

    public function normalizeE164CongolesePhone(string $raw): string
    {
        $normalized = $this->normalizeCongolesePhone($raw);

        return str_starts_with($normalized, '243') ? '+' . $normalized : $normalized;
    }

    private function shwaryPaymentUrl(): ?string
    {
        $paymentUrl = config('mobile_money.shwary.payment_url');

        if ($paymentUrl) {
            return $paymentUrl;
        }

        $baseUrl = rtrim((string) config('mobile_money.shwary.base_url'), '/');

        return $baseUrl !== '' ? $baseUrl . '/payments/initiate' : null;
    }

    private function paymentErrorMessage(int $status, array $body): string
    {
        $message = $body['message'] ?? $body['error'] ?? data_get($body, 'detail');

        if (is_string($message) && $message !== '') {
            return $message;
        }

        return match ($status) {
            400, 422 => 'Donnees de paiement invalides.',
            401, 403 => 'Identifiants Shwary invalides.',
            402 => 'Solde marchand insuffisant.',
            429 => 'Trop de requetes vers Shwary. Reessayez dans quelques instants.',
            default => 'Paiement refuse par Shwary.',
        };
    }

    public static function parseWebhookPayload(?array $parsed, string $rawBody): array
    {
        if (is_array($parsed) && isset($parsed['code'])) {
            return $parsed;
        }

        $decoded = json_decode($rawBody, true);

        return is_array($decoded) ? $decoded : [];
    }
}
