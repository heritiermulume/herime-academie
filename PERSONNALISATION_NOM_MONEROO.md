# Personnalisation du Nom Affiché lors des Paiements Moneroo

## 🎯 Problème

Actuellement, lors de l'initialisation d'un paiement Mobile Money via Moneroo, le nom qui s'affiche chez l'opérateur est **"DRC, pawaPay"** au lieu de **"Herime Académie"**.

## 📍 Où le Nom Apparaît

Le nom du marchand peut apparaître à plusieurs endroits :

1. **SMS de l'opérateur** - "Paiement de XXX FC à DRC, pawaPay"
2. **Notification push** - Sur le téléphone de l'utilisateur
3. **Historique de transactions** - Dans l'application mobile money
4. **Reçu de paiement** - Fourni par Moneroo après paiement

## 🔍 Sources du Nom Affiché

### 1. Champ `description` de l'API (✅ Déjà Configuré)

**Code actuel** :
```php
// MonerooController.php ligne 333
'description' => config('services.moneroo.company_name', 'Herime Académie') . ' - Paiement commande ' . $order->order_number,
```

**Résultat** : `"Herime Académie - Paiement commande MON-ABC123"`

⚠️ **Note**: Ce champ est utilisé pour les factures et reçus, **mais pas nécessairement** pour le nom affiché chez l'opérateur.

### 2. Nom du Compte Marchand Moneroo (⭐ À Modifier)

Le nom principal qui s'affiche provient du **profil de votre compte marchand Moneroo**.

#### Comment le Modifier

1. **Connectez-vous au Dashboard Moneroo**
   - URL: https://dashboard.moneroo.io
   - Utilisez vos identifiants marchand

2. **Accédez aux Paramètres du Compte**
   - Menu > Paramètres
   - Ou Menu > Profil
   - Ou Menu > Informations de l'Entreprise

3. **Cherchez les Champs Suivants**
   - **Business Name** / **Nom de l'Entreprise**
   - **Merchant Name** / **Nom du Marchand**
   - **Display Name** / **Nom d'Affichage**
   - **Public Name** / **Nom Public**

4. **Modifiez Tous les Champs Pertinents**
   ```
   Ancien: "DRC, pawaPay" ou "PawaPay"
   Nouveau: "Herime Académie"
   ```

5. **Sauvegardez les Modifications**

6. **Testez un Paiement**
   - Créez une commande de test
   - Vérifiez le SMS/notification reçu
   - Le nom devrait maintenant être "Herime Académie"

### 3. Configuration Auprès des Opérateurs (Si Problème Persiste)

Si le changement dans le dashboard ne fonctionne pas immédiatement, c'est que Moneroo doit mettre à jour votre nom auprès des opérateurs (Orange Money, M-Pesa, Airtel Money, etc.).

#### Contactez le Support Moneroo

**Email** : support@moneroo.io (vérifier sur leur site)

**Message Type** :
```
Objet: Changement du Nom d'Affichage pour le Compte Marchand

Bonjour,

Je suis [Votre Nom], représentant de Herime Académie.

Notre compte marchand Moneroo affiche actuellement "DRC, pawaPay" 
lors des transactions Mobile Money auprès des opérateurs.

Nous souhaitons changer ce nom pour "Herime Académie".

Détails du compte:
- Email du compte: [votre email marchand]
- ID Marchand: [si vous l'avez]
- Nom actuel: "DRC, pawaPay"
- Nom souhaité: "Herime Académie"

Pouvez-vous nous aider à effectuer ce changement auprès des 
opérateurs Mobile Money ?

Merci,
[Votre Nom]
Herime Académie
```

## 📝 Configuration dans le Code

### Fichier `.env`

Assurez-vous que cette variable est bien définie :

```env
MONEROO_COMPANY_NAME="Herime Académie"
```

### Fichier `config/services.php`

La configuration est déjà correcte :

```php
'moneroo' => [
    // ...
    'company_name' => env('MONEROO_COMPANY_NAME', 'Herime Académie'),
    // ...
],
```

### Fichier `MonerooController.php`

Le champ description utilise déjà le nom configuré :

```php
$payload = [
    'amount' => $amountInSmallestUnit,
    'currency' => $paymentCurrency,
    'description' => config('services.moneroo.company_name', 'Herime Académie') 
                   . ' - Paiement commande ' . $order->order_number,
    'customer' => [
        'email' => $user->email,
        'first_name' => $this->extractFirstName($user->name),
        'last_name' => $this->extractLastName($user->name),
    ],
    // ...
];
```

## 🧪 Comment Tester

### Test 1: Vérifier la Configuration Locale

```bash
# Dans le terminal Laravel
php artisan tinker

# Exécuter cette commande
config('services.moneroo.company_name')

# Résultat attendu: "Herime Académie"
```

### Test 2: Paiement Réel

1. Créer une commande de test (petit montant)
2. Initier le paiement
3. Vérifier le SMS/notification reçu sur le téléphone
4. Le nom devrait être "Herime Académie"

### Test 3: Vérifier les Logs

```php
// Dans MonerooController.php, ligne 358, on logue le payload
\Log::info('Moneroo: Envoi de la requête d\'initialisation', [
    'payload' => $payload,
]);

// Vérifier storage/logs/laravel.log
// Chercher "description" dans le payload
// Devrait être: "Herime Académie - Paiement commande XXX"
```

## 🔒 Considérations de Sécurité

### Caractères Autorisés

Le nom doit être :
- **Longueur** : 3-50 caractères
- **Caractères** : Lettres, chiffres, espaces, tirets
- **Éviter** : Emojis, caractères spéciaux

**Bon** : `"Herime Académie"`  
**Mauvais** : `"Herime 🎓 Académie!!!"` (emojis et symboles)

### Conformité

Assurez-vous que le nom :
- ✅ Correspond à votre raison sociale officielle
- ✅ Est reconnaissable par vos clients
- ✅ Est professionnel et clair

## 📊 Délais de Mise à Jour

| Niveau | Délai |
|--------|-------|
| Champ `description` API | Immédiat |
| Dashboard Moneroo | Immédiat à 24h |
| Chez les opérateurs | 1-5 jours ouvrables |

⚠️ **Important** : La mise à jour du nom chez les opérateurs peut prendre quelques jours car Moneroo doit coordonner avec chaque opérateur (Orange, Airtel, etc.).

## ✅ Checklist

- [ ] Vérifier `MONEROO_COMPANY_NAME` dans `.env`
- [ ] Vérifier que le code utilise `config('services.moneroo.company_name')`
- [ ] Se connecter au Dashboard Moneroo
- [ ] Modifier le nom du compte marchand
- [ ] Tester avec `php artisan tinker`
- [ ] Faire un paiement de test
- [ ] Vérifier le SMS/notification reçu
- [ ] Si problème persiste, contacter le support Moneroo
- [ ] Documenter le changement

## 🆘 Support

### Support Moneroo

- 🌐 **Site** : https://moneroo.io
- 📧 **Email** : support@moneroo.io (à vérifier)
- 💬 **Chat** : Disponible dans le dashboard
- 📚 **Documentation** : https://docs.moneroo.io

### Support Herime Académie

- 📧 **Email** : support@herime-academie.com
- 📱 **WhatsApp** : [Votre numéro]

## 📚 Références

- [Documentation Moneroo - Initialiser un paiement](https://docs.moneroo.io/fr/payments/initialiser-un-paiement)
- [Documentation Moneroo - Intégration Standard](https://docs.moneroo.io/fr/payments/integration-standard)
- Code source : `app/Http/Controllers/MonerooController.php` ligne 333
- Configuration : `config/services.php` ligne 46

---

**Date de création** : {{ date('d/m/Y') }}  
**Dernière mise à jour** : {{ date('d/m/Y') }}  
**Version** : 1.0

