<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; margin: 20px; color: #000; }
        .header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 20px; margin-bottom: 20px; }
        .footer { margin-top: 40px; font-size: 12px; text-align: center; border-top: 1px solid #ccc; padding-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        .amount { font-size: 24px; font-weight: bold; text-align: right; }
        .qr { text-align: center; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ROADSHIELD - REÇU OFFICIEL</h1>
        <p>Lubumbashi, RDC - Haut-Katanga</p>
        <p>PROVINCIAUX DES TRANSPORTS</p>
    </div>

    <p><strong>Référence :</strong> {{ $contravention->code_unique }}</p>
    <p><strong>Date :</strong> {{ $contravention->created_at->format('d/m/Y H:i') }}</p>
    <p><strong>Lieu :</strong> {{ $contravention->lieu }}</p>
    <p><strong>Propriétaire du véhicule :</strong> {{ $contravention->vehicule->proprietaire->nom ?? 'Non identifié' }} {{ $contravention->vehicule->proprietaire->prenom ?? '' }}</p>

    <table>
        <tr>
            <th>Plaque :</th>
            <td>{{ $contravention->vehicule->plaque_immatriculation }}</td>
        </tr>
        <tr>
            <th>Marque/Modèle :</th>
            <td>{{ $contravention->vehicule->marque }} {{ $contravention->vehicule->modele }}</td>
        </tr>
        <tr>
            <th>Propriétaire :</th>
            <td>{{ $contravention->vehicule->proprietaire->nom ?? 'Inconnu' }} {{ $contravention->vehicule->proprietaire->prenom ?? '' }}</td>
        </tr>
        <tr>
            <th>Motif :</th>
            <td>
                @if($contravention->type === 'Défaut de documents' && !empty($contravention->documents_manquants))
                    Défaut de : {{ implode(', ', array_column($contravention->documents_manquants, 'nom')) }}
                @else
                    {{ $contravention->type }}
                @endif
            </td>
        </tr>
        @if($contravention->type === 'Défaut de documents' && !empty($contravention->documents_manquants))
        <tr>
            <th>Détails des documents manquants :</th>
            <td>
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($contravention->documents_manquants as $document)
                        <li style="margin: 5px 0;">
                            <strong>{{ $document['nom'] }}</strong>
                            @if(isset($document['date_expiration']))
                                <span style="color: #d32f2f;">(expiré le {{ $document['date_expiration'] }})</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </td>
        </tr>
        @endif
    </table>

    <div style="text-align: right; margin: 30px 0;">
        <div class="amount">{{ number_format($contravention->montant, 0, ',', '.') }} FC</div>
        <p>TOTAL À PAYER</p>
    </div>

    <div class="qr">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ url('/verifier/' . $contravention->code_unique) }}" alt="QR Vérification">
        <p>Scannez pour vérifier l'authenticité</p>
    </div>

    <div class="footer">
        <p>Agent : {{ Auth::user()->name }}</p>
        <p>Régularisez sous 48h - RoadShield Système Officiel</p>
    </div>
</body>
</html>
