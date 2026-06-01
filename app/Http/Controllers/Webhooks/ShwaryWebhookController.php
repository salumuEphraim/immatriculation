<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Contravention;
use App\Services\MobileMoney\MobileMoneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShwaryWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('mobile_money.shwary.webhook_secret');
        if ($secret !== null && $secret !== '' && $request->header('X-Webhook-Secret') !== $secret) {
            abort(401, 'Invalid webhook secret');
        }

        $data = MobileMoneyService::parseWebhookPayload($request->all(), $request->getContent());

        $transactionId = $data['id']
            ?? $data['transaction_id']
            ?? $data['transactionId']
            ?? data_get($data, 'data.id')
            ?? data_get($data, 'data.transaction_id');
        $reference = $data['reference']
            ?? $data['external_reference']
            ?? data_get($data, 'metadata.reference')
            ?? data_get($data, 'metadata.external_reference');
        $status = strtolower((string) ($data['status'] ?? data_get($data, 'data.status') ?? ''));

        if (!$reference && !$transactionId) {
            Log::warning('Shwary webhook sans reference ni transaction', ['data' => $data]);

            return response()->json(['ok' => false, 'error' => 'missing reference'], 422);
        }

        $contravention = Contravention::query()
            ->when($reference, fn ($query) => $query->orWhere('reference_paiement', $reference))
            ->when($transactionId, fn ($query) => $query->orWhere('paiement_transaction_id', $transactionId))
            ->first();

        if (!$contravention) {
            Log::warning('Shwary webhook transaction inconnue', [
                'reference' => $reference,
                'transaction_id' => $transactionId,
            ]);

            return response()->json(['ok' => true, 'ignored' => true]);
        }

        if (in_array($status, ['completed', 'success', 'successful', 'paid'], true)) {
            $contravention->statut = 'payee';
            $contravention->est_payee = true;
            $contravention->paiement_fournisseur = 'shwary';
            $contravention->paiement_statut = 'completed';
        } elseif (in_array($status, ['failed', 'cancelled', 'canceled', 'expired'], true)) {
            $contravention->paiement_statut = 'failed';
        } else {
            $contravention->paiement_statut = $status ?: 'initiated';
        }

        if ($transactionId) {
            $contravention->paiement_transaction_id = (string) $transactionId;
        }

        $contravention->save();

        return response()->json(['ok' => true]);
    }
}
