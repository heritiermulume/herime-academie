# Configuration MOKO Afrika

## Variables d'environnement requises

Ajoutez ces variables à votre fichier `.env` :

```env
# MOKO Afrika Configuration
# Documentation: https://moko-africa-documentation.vercel.app

# MOKO API Configuration
MOKO_API_URL=https://paydrc.gofreshbakery.net/api/v5
MOKO_TOKEN_URL=https://paydrc.gofreshbakery.net/api/v5/token

# MOKO Credentials (Remplacez par vos vraies credentials)
MOKO_MERCHANT_ID=your_merchant_id_here
MOKO_MERCHANT_SECRET=your_merchant_secret_here

# MOKO Settings
MOKO_DEFAULT_CURRENCY=CDF
MOKO_CALLBACK_URL=${APP_URL}/moko/callback
MOKO_SUCCESS_URL=${APP_URL}/moko/success
MOKO_FAILURE_URL=${APP_URL}/moko/failure
```

## Configuration

1. **Obtenez vos credentials MOKO** :
   - Contactez MOKO Afrika pour obtenir votre `MERCHANT_ID` et `MERCHANT_SECRET`
   - Documentation : https://moko-africa-documentation.vercel.app

2. **Configurez les URLs de callback** :
   - Assurez-vous que votre application est accessible publiquement
   - Les URLs de callback doivent être accessibles depuis l'extérieur

3. **Testez l'intégration** :
   - Utilisez les numéros de test fournis par MOKO Afrika
   - Vérifiez que les callbacks fonctionnent correctement

## Méthodes de paiement supportées

- **Airtel Money** (`airtel`)
- **Orange Money** (`orange`) 
- **M-Pesa Vodacom** (`mpesa`)
- **Afrimoney Africell** (`africell`)

## Fonctionnalités

- ✅ Paiement Mobile Money instantané
- ✅ Callbacks automatiques pour les notifications
- ✅ Gestion des statuts de transaction
- ✅ Interface utilisateur intuitive
- ✅ Logs détaillés pour le debugging
- ✅ Support multi-devises (CDF, USD)

## Routes disponibles

- `GET /moko/payment` - Page de paiement MOKO
- `POST /moko/initiate` - Initier un paiement
- `GET /moko/status/{reference}` - Vérifier le statut
- `POST /moko/callback` - Callback MOKO (webhook)
- `GET /moko/success` - Page de succès
- `GET /moko/failure` - Page d'échec

## Modèles de données

### MokoTransaction
- Stockage des transactions MOKO
- Suivi des statuts
- Gestion des callbacks
- Relations avec les commandes

## Support

Pour toute question concernant l'intégration MOKO Afrika :
- Documentation : https://moko-africa-documentation.vercel.app
- Support : info@mokoafrika.com
