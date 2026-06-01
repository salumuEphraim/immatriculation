<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Reçu de Contravention - {{ $infraction->code_unique }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .ticket { width: 100%; border: 1px solid #000; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 22px; color: #dc3545; }
        .section-title { font-weight: bold; text-transform: uppercase; background: #eee; padding: 5px; margin-top: 15px; }
        .row { margin: 10px 0; }
        .label { color: #666; width: 150px; display: inline-block; }
        .value { font-weight: bold; }
        .total-box { margin-top: 20px; padding: 15px; border: 2px solid #333; text-align: center; font-size: 18px; }
        .qr-code { text-align: center; margin-top: 30px; }
        .footer { text-align: center; font-size: 10px; margin-top: 30px; color: #777; }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="header">
            <h1>ROADSHIELD RDC</h1>
            <p>Province du Haut-Katanga - Ville de Lubumbashi</p>
            <p><strong>REÇU OFFICIEL DE CONTRAVENTION</strong></p>
        </div>

        <div class="row">
            <span class="label">Référence :</span>
            <span class="value">{{ $infraction->code_unique }}</span>
        </div>
        <div class="row">
            <span class="label">Date :</span>
            <span class="value">{{ $infraction->created_at->format('d/m/Y H:i') }}</span>
        </div>
        <div class="row">
            <span class="label">Lieu :</span>
            <span class="value">{{ $infraction->lieu }}</span>
        </div>

        <div class="section-title">Informations Véhicule</div>
        <div class="row">
            <span class="label">Plaque d'immatriculation :</span>
            <span class="value">{{ $infraction->vehicule->plaque }}</span>
        </div>
        <div class="row">
            <span class="label">Marque / Modèle :</span>
            <span class="value">{{ $infraction->vehicule->marque }} {{ $infraction->vehicule->modele }}</span>
        </div>
        <div class="row">
            <span class="label">Propriétaire :</span>
            <span class="value">{{ $infraction->vehicule->proprietaire->nom ?? 'Inconnu' }}</span>
        </div>

        <div class="section-title">Détails de l'Amende</div>
        <div class="row">
            <span class="label">Nature de l'infraction :</span>
            <span class="value">{{ $infraction->type }}</span>
        </div>

        <div class="total-box">
            TOTAL À PAYER : <strong>{{ number_format($infraction->montant, 0, ',', '.') }} FC</strong>
        </div>

        <div class="qr-code">
            {{-- Utilisation de l'API externe pour le QR Code --}}
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ url('/verifier/' . $infraction->code_unique) }}" width="120" height="120">
            <p style="font-size: 9px;">Scannez pour vérifier l'authenticité sur roadshield.cd</p>
        </div>

        <div class="footer">
            Agent verbalisateur : {{ $infraction->agent->name ?? 'Système' }}<br>
            Ceci est un document officiel généré électroniquement. Merci de régulariser sous 48h.
        </div>
    </div>
</body>
</html>