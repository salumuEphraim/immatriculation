# Paiement Mobile Money (Flexpay – RDC)

L’application peut fonctionner en **mode démo** (`MOBILE_MONEY_DRIVER=mock`) ou en **mode réel** avec **Flexpay**, agrégateur courant en République Démocratique du Congo (Airtel Money, Orange Money, Afrimoney, M-Pesa).

## 1. Obtenir un compte marchand

1. Créer un compte / demander l’API sur **[flexpay.cd](https://flexpay.cd)**.
2. Récupérer **l’API Key** et l’identifiant **merchant** fournis par Flexpay.

## 2. Variables d’environnement (`.env`)

```env
MOBILE_MONEY_DRIVER=flexpay
FLEXPAY_API_KEY=votre_cle_api
FLEXPAY_MERCHANT_ID=votre_merchant
FLEXPAY_CURRENCY=CDF
FLEXPAY_CALLBACK_URL=https://votre-domaine.cd/webhooks/flexpay
```

- **`FLEXPAY_CALLBACK_URL`** : doit être une URL **HTTPS** accessible depuis Internet (Flexpay appelle cette URL après la validation client sur le téléphone).
- En local, utilisez un tunnel (**ngrok**, **expose**, etc.) pour tester le webhook.

### Sécuriser le webhook (recommandé)

Définissez un secret et configurez votre proxy / Flexpay pour envoyer le header `X-Webhook-Secret` avec la même valeur :

```env
FLEXPAY_WEBHOOK_SECRET=une_chaine_longue_secrete
```

Si `FLEXPAY_WEBHOOK_SECRET` est vide, le webhook reste ouvert (acceptable uniquement en développement).

## 3. Endpoint webhook dans l’application

- **URL** : `POST /webhooks/flexpay`
- **CSRF** : exclu dans `VerifyCsrfToken` (requis pour les callbacks externes).
- **Comportement** : si le corps indique un paiement réussi (`code === "0"`), l’infraction correspondant à la **référence** envoyée à Flexpay est marquée **payée**.

Les références générées sont du type `RSH-{id_infraction}-{suffixe}`.

## 4. Numéro de téléphone

Flexpay attend en général un numéro au format **`243XXXXXXXXX`**. Le service normalise automatiquement les saisies du type `0…`, `+243…`, etc.

## 5. Fichiers utiles

| Fichier | Rôle |
|--------|------|
| `config/mobile_money.php` | Driver, URLs, devise |
| `app/Services/MobileMoney/MobileMoneyService.php` | Appel API + mode mock |
| `app/Http/Controllers/Webhooks/FlexpayWebhookController.php` | Réception du callback |

En cas de changement d’API côté Flexpay, adaptez l’URL `FLEXPAY_PAY_URL` dans `config/mobile_money.php` (valeur par défaut : `https://backend.flexpay.cd/api/rest/v1/paymentService`).
