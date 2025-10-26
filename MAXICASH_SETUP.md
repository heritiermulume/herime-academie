# Configuration MaxiCash

Ce document explique comment configurer le système de paiement MaxiCash pour Herime Académie.

## Étape 1 : Enregistrement MaxiCash

1. Téléchargez l'application MaxiCash Mobile depuis :
   - [iOS](https://itunes.apple.com/us/app/maxicash-app/id1061618380?mt=8)
   - [Android](https://play.google.com/store/apps/details?id=com.pluritone.maxicash)

2. Créez un compte MaxiCash

3. Contactez l'équipe MaxiCash pour obtenir un compte marchand :
   - Email : info@maxicashapp.com
   - Vous recevrez un formulaire à remplir pour fournir les détails de votre entreprise
   - Une fois approuvé, vous recevrez vos identifiants marchand (MerchantID et MerchantPassword)

## Étape 2 : Configuration de l'environnement

Ajoutez les variables suivantes dans votre fichier `.env` :

```env
# MaxiCash Configuration
MAXICASH_MERCHANT_ID=votre_merchant_id
MAXICASH_MERCHANT_PASSWORD=votre_merchant_password
MAXICASH_SANDBOX=true
MAXICASH_API_URL=https://api-testbed.maxicashapp.com
MAXICASH_GATEWAY_URL=https://api-testbed.maxicashapp.com/PayEntryPost
```

### Mode Sandbox (Test)

Pour les tests, utilisez :
```env
MAXICASH_SANDBOX=true
MAXICASH_API_URL=https://api-testbed.maxicashapp.com
MAXICASH_GATEWAY_URL=https://api-testbed.maxicashapp.com/PayEntryPost
```

### Mode Production

Pour la production, utilisez :
```env
MAXICASH_SANDBOX=false
MAXICASH_API_URL=https://api.maxicashapp.com
MAXICASH_GATEWAY_URL=https://api.maxicashapp.com/PayEntryPost
```

## Étape 3 : Types de monnaie

MaxiCash supporte deux types de monnaie :

- **maxiDollar** : Équivaut à 1 USD (par défaut, utilisé pour les marchands internationaux)
- **maxiRand** : Équivaut à 1 ZAR (utilisé pour les marchands en Afrique du Sud)

Le système est actuellement configuré pour utiliser **maxiDollar** comme devise par défaut.

## Étape 4 : URL de retour

Les URLs de retour sont automatiquement configurées dans le contrôleur :

- **accepturl** : Page de succès du paiement
- **cancelurl** : Page d'annulation du paiement
- **declineurl** : Page d'échec du paiement
- **notifyurl** : Webhook pour les notifications MaxiCash

Ces URLs sont accessibles via les routes Laravel définies dans `routes/web.php`.

## Étape 5 : Test du système

### Sur l'application MaxiCash

1. Ajoutez des fonds de test à votre portefeuille MaxiCash
2. Utilisez l'application pour tester un paiement

### Sur le site

1. Ajoutez des cours au panier
2. Aller à la page de checkout
3. Sélectionnez "MaxiCash" comme méthode de paiement
4. Entrez votre numéro de téléphone MaxiCash
5. Cliquez sur "Procéder au paiement"
6. Vous serez redirigé vers le portail MaxiCash
7. Confirmez le paiement depuis l'application mobile MaxiCash

## Documentation de l'API MaxiCash

- Documentation officielle : https://developer.maxicashapp.com
- Support : info@maxicashapp.com

## Notes importantes

1. **Montants** : Les montants doivent être envoyés en **cents** (cents). Par exemple, pour payer 10 USD, envoyez 1000.

2. **Référence** : Chaque transaction doit avoir une référence unique générée automatiquement.

3. **Notifications** : Les notifications sont envoyées via webhook à l'URL configurée.

4. **Sécurité** : Les identifiants MerchantID et MerchantPassword doivent être gardés secrets et ne jamais être exposés côté client.

## Résolution de problèmes

### Le paiement ne se lance pas

- Vérifiez que les identifiants MaxiCash sont corrects dans le `.env`
- Vérifiez que vous utilisez le bon environnement (sandbox/production)
- Consultez les logs Laravel : `storage/logs/laravel.log`

### Erreur de redirection

- Vérifiez que les URLs de retour sont accessibles publiquement
- Vérifiez que HTTPS est activé (nécessaire pour la production)

### Transaction échouée

- Vérifiez que l'utilisateur a suffisamment de fonds dans son portefeuille MaxiCash
- Vérifiez que le numéro de téléphone est correct et enregistré sur MaxiCash
- Consultez les logs pour plus de détails

## Support

Pour toute question ou problème :
- Email Herime : support@herime-academie.com
- Email MaxiCash : info@maxicashapp.com

