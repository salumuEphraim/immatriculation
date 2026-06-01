<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; line-height: 1.6; color: #333; }
        .container { padding: 20px; border: 1px solid #eee; border-radius: 5px; }
        .header { background: #dc3545; color: white; padding: 10px; text-align: center; border-radius: 5px 5px 0 0; }
        .footer { font-size: 12px; color: #777; margin-top: 20px; border-top: 1px solid #eee; padding-top: 10px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>ROADSHIELD - Avis d'infraction</h2>
        </div>
        <p>Bonjour {{ $contravention->vehicule->proprietaire->nom ?? 'Monsieur/Madame' }},</p>
        <p>Une infraction a ete constatee pour votre vehicule immatricule <strong>{{ $contravention->vehicule->plaque }}</strong> a Lubumbashi.</p>
        
        <ul>
            <li><strong>Motif :</strong> {{ $contravention->type }}</li>
            <li><strong>Lieu :</strong> {{ $contravention->lieu }}</li>
            <li><strong>Montant :</strong> {{ number_format($contravention->montant, 0, ',', '.') }} FC</li>
        </ul>

        <p>Veuillez trouver en piece jointe le recu officiel au format PDF. Vous disposez de 48 heures pour regulariser votre situation aupres des services competents.</p>
        
        <div class="footer">
            <p>Ceci est un message automatique de la plateforme RoadShield RDC. Merci de ne pas y repondre.</p>
        </div>
    </div>
</body>
</html>
