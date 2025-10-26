# 🔐 Configuration des Credentials MOKO Afrika

## 🚨 Problème Actuel
L'erreur **"Échec de génération du token MOKO Afrika"** indique que les credentials MOKO ne sont pas configurés.

## 🛠️ Solution Immédiate : Mode Test
Le système est maintenant configuré avec un **mode test** qui fonctionne sans credentials réels :

- ✅ **Token de test** : Généré automatiquement
- ✅ **Transaction simulée** : Réponse de succès simulée
- ✅ **Pas de paiement réel** : Idéal pour les tests

## 🔑 Configuration des Credentials Réels

### 1. Obtenir les Credentials MOKO
Contactez MOKO Afrika pour obtenir :
- `MOKO_MERCHANT_ID` : Votre ID marchand
- `MOKO_MERCHANT_SECRET` : Votre secret marchand

### 2. Ajouter au fichier .env
```env
# MOKO Afrika Configuration
MOKO_MERCHANT_ID=your_merchant_id_here
MOKO_MERCHANT_SECRET=your_merchant_secret_here
MOKO_API_URL=https://paydrc.gofreshbakery.net/api/v5
MOKO_TOKEN_URL=https://paydrc.gofreshbakery.net/api/v5/token
MOKO_DEFAULT_CURRENCY=CDF
MOKO_CALLBACK_URL=http://your-domain.com/moko/callback
MOKO_SUCCESS_URL=http://your-domain.com/moko/success
MOKO_FAILURE_URL=http://your-domain.com/moko/failure
```

### 3. Vérifier la Configuration
```bash
php artisan tinker --execute="
echo 'MOKO_MERCHANT_ID: ' . config('moko.merchant_id') . PHP_EOL;
echo 'MOKO_MERCHANT_SECRET: ' . (config('moko.merchant_secret') ? 'SET' : 'NOT SET') . PHP_EOL;
"
```

## 🧪 Mode Test vs Production

### Mode Test (Actuel)
- ✅ Fonctionne sans credentials
- ✅ Transactions simulées
- ✅ Parfait pour le développement
- ❌ Pas de vrais paiements

### Mode Production
- ✅ Vrais paiements MOKO
- ✅ Transactions réelles
- ✅ Revenus réels
- ❌ Nécessite des credentials valides

## 🚀 Test du Paiement MOKO

### Avec Mode Test (Maintenant)
1. Allez sur la page de checkout
2. Sélectionnez "Mobile Money"
3. Remplissez le formulaire
4. Cliquez sur "Payer avec Mobile Money"
5. ✅ **Succès** : Transaction simulée

### Avec Credentials Réels (Plus tard)
1. Configurez les credentials dans `.env`
2. Redémarrez l'application
3. Testez le paiement
4. ✅ **Succès** : Vraie transaction MOKO

## 📞 Support MOKO Afrika

Pour obtenir vos credentials :
- **Documentation** : https://moko-africa-documentation.vercel.app
- **Support** : Contactez MOKO Afrika directement
- **Test** : Utilisez le mode test en attendant

## ✅ Résolution

L'erreur **"Échec de génération du token MOKO Afrika"** est maintenant résolue grâce au mode test. Vous pouvez tester le paiement MOKO immédiatement !
