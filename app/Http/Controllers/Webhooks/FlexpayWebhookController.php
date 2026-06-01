<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Contravention;
use App\Services\MobileMoney\MobileMoneyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FlexpayWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        $secret = config('mobile_money.flexpay.webhook_secret');
        if ($secret !== null && $secret !== '' && $request->header('X-Webhook-Secret') !== $secret) {
            abort(401, 'Invalid webhook secret');
        }

        $raw = $request->getContent();
        $data = MobileMoneyService::parseWebhookPayload($request->all(), $raw);

        $code = $data['code'] ?? '';
        $reference = $data['reference'] ?? null;
        $isSuccessful = $code === '0' || ($data['isSuccessFull'] ?? false) === true;

        if (!$reference || !is_string($reference)) {
            Log::warning('Flexpay webhook sans référence', ['data' => $data]);

            return response()->json(['ok' => false, 'error' => 'missing reference'], 422);
        }

        $contravention = Contravention::where('reference_paiement', $reference)->first();

        if (!$contravention) {
            Log::warning('Flexpay webhook référence inconnue', ['reference' => $reference]);

            return response()->json(['ok' => true, 'ignored' => true]);
        }

        if ($isSuccessful) {
            $contravention->statut = 'payee';
            $contravention->est_payee = true;
            $contravention->paiement_fournisseur = 'flexpay';
            $contravention->paiement_statut = 'completed';
            if (!empty($data['orderNumber'])) {
                $contravention->paiement_transaction_id = (string) $data['orderNumber'];
            }
            $contravention->save();
        } else {
            $contravention->paiement_statut = 'failed';
            $contravention->save();
        }

        return response()->json(['ok' => true]);
    }
}
