# 💰 Système de Wallet Pro avec Période de Blocage

## 📋 Vue d'ensemble

Le système de Wallet Pro implémente une **période de blocage (holding period)** pour les fonds, similaire aux plateformes professionnelles comme Stripe, PayPal, etc. Cette fonctionnalité garantit la sécurité des transactions en bloquant temporairement les nouveaux gains avant qu'ils ne soient disponibles au retrait.

## 🎯 Fonctionnalités

### 1. Trois Types de Soldes

- **Solde Total** (`balance`) : Montant total dans le wallet (disponible + bloqué)
- **Solde Disponible** (`available_balance`) : Montant immédiatement disponible pour le retrait
- **Solde Bloqué** (`held_balance`) : Montant en période de blocage, sera disponible après le délai

### 2. Période de Blocage Configurable

Par défaut : **7 jours** (configurable)

Les fonds gagnés sont automatiquement bloqués pendant cette période pour permettre :
- La gestion des litiges
- Les remboursements éventuels
- La sécurité des transactions

### 3. Libération Automatique

Une commande artisan libère automatiquement les fonds lorsque la période de blocage est terminée :

```bash
# Libérer les fonds expirés
php artisan wallet:release-holds

# Mode simulation (sans appliquer les changements)
php artisan wallet:release-holds --dry-run

# Forcer la libération (même si pas encore expiré)
php artisan wallet:release-holds --force
```

## ⚙️ Configuration

### Fichier `.env`

```env
# Période de blocage en jours (par défaut: 7)
WALLET_HOLDING_PERIOD_DAYS=7

# Montant minimum de retrait (par défaut: 5)
WALLET_MINIMUM_PAYOUT=5

# Fréquence de libération automatique (daily, hourly, twiceDaily)
WALLET_AUTO_RELEASE_SCHEDULE=daily
```

### Fichier `config/wallet.php`

```php
return [
    'holding_period_days' => env('WALLET_HOLDING_PERIOD_DAYS', 7),
    'minimum_payout_amount' => env('WALLET_MINIMUM_PAYOUT', 5),
    'auto_release_schedule' => env('WALLET_AUTO_RELEASE_SCHEDULE', 'daily'),
];
```

## 🔧 Utilisation dans le Code

### Créditer SANS période de blocage (immédiatement disponible)

```php
$wallet = $user->wallet;

$transaction = $wallet->credit(
    amount: 100.00,
    type: 'bonus',
    description: 'Bonus de bienvenue',
    transactionable: $order,
    metadata: ['reason' => 'welcome_bonus']
);
```

### Créditer AVEC période de blocage (Wallet Pro)

```php
$wallet = $user->wallet;

$result = $wallet->creditWithHold(
    amount: 100.00,
    type: 'commission',
    holdingDays: 7, // null = utilise la config par défaut
    description: 'Commission sur la vente #123',
    transactionable: $order,
    metadata: ['order_id' => $order->id]
);

// $result contient:
// - 'transaction' : La transaction wallet créée
// - 'hold' : Le hold (période de blocage) créé
```

### Retrait (utilise automatiquement le solde disponible)

```php
try {
    $transaction = $wallet->debit(
        amount: 50.00,
        type: 'payout',
        description: 'Retrait vers Mobile Money',
        transactionable: $payout
    );
} catch (\Exception $e) {
    // Erreur: "Solde disponible insuffisant..."
}
```

## 📊 Structure de la Base de Données

### Table `wallets`

| Champ | Type | Description |
|-------|------|-------------|
| `balance` | decimal | Solde total (disponible + bloqué) |
| `available_balance` | decimal | Solde disponible au retrait |
| `held_balance` | decimal | Solde en période de blocage |
| `reserved_balance` | decimal | Solde réservé (retraits en cours) |
| `total_earned` | decimal | Total gagné depuis le début |
| `total_withdrawn` | decimal | Total retiré |

### Table `wallet_holds`

| Champ | Type | Description |
|-------|------|-------------|
| `wallet_id` | bigint | ID du wallet |
| `wallet_transaction_id` | bigint | ID de la transaction source |
| `amount` | decimal | Montant bloqué |
| `currency` | string | Devise |
| `reason` | string | Raison du blocage |
| `held_at` | timestamp | Date de début du blocage |
| `held_until` | timestamp | Date de libération prévue |
| `released_at` | timestamp | Date réelle de libération |
| `status` | string | held, released, cancelled |

## 🚀 Automatisation

### Scheduler Laravel

Dans `app/Console/Kernel.php`, ajouter :

```php
protected function schedule(Schedule $schedule)
{
    // Libérer les fonds bloqués expirés (quotidiennement)
    $schedule->command('wallet:release-holds')
        ->daily()
        ->at('02:00')
        ->timezone('Africa/Kinshasa');
}
```

### Cron Job

Sur le serveur, ajouter cette ligne au crontab :

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 🎨 Interface Utilisateur

### Dashboard Wallet

- ✅ Affiche clairement les 3 soldes (disponible, bloqué, total)
- ✅ Liste des fonds en période de blocage avec date de libération
- ✅ Bannière explicative sur le système de blocage
- ✅ Temps restant avant libération pour chaque hold

### Formulaire de Retrait

- ✅ Affiche le solde **disponible** (pas le total)
- ✅ Informe sur le montant bloqué s'il y en a
- ✅ Validation stricte du solde disponible
- ✅ Messages d'erreur explicites

## 📈 Cas d'Usage

### 1. Commission d'Ambassadeur (avec blocage)

```php
$ambassador = Auth::user();
$wallet = $ambassador->wallet;

// Créditer avec période de blocage de 7 jours
$result = $wallet->creditWithHold(
    amount: 50.00,
    type: 'commission',
    description: 'Commission sur la commande #' . $order->id
);

// Le solde total augmente de 50
// Le solde bloqué augmente de 50
// Le solde disponible reste inchangé
// Dans 7 jours, les 50 seront transférés vers le solde disponible
```

### 2. Bonus Immédiat (sans blocage)

```php
$wallet = $user->wallet;

// Créditer immédiatement disponible
$transaction = $wallet->credit(
    amount: 25.00,
    type: 'bonus',
    description: 'Bonus de parrainage'
);

// Le solde total augmente de 25
// Le solde disponible augmente de 25
// Peut être retiré immédiatement
```

### 3. Retrait

```php
$wallet = $user->wallet;

// Vérifier le solde disponible
if ($wallet->hasBalance(100.00)) {
    $transaction = $wallet->debit(
        amount: 100.00,
        type: 'payout',
        description: 'Retrait vers MTN Mobile Money'
    );
    
    // Le solde disponible diminue de 100
    // Le solde total diminue de 100
    // Le solde bloqué reste inchangé
}
```

## 🛡️ Sécurité

### Protection contre les doubles retraits

- ✅ Vérification stricte du solde **disponible** (pas total)
- ✅ Transaction atomique avec `DB::beginTransaction()`
- ✅ Rollback automatique en cas d'erreur

### Traçabilité

- ✅ Chaque hold est tracé dans `wallet_holds`
- ✅ Chaque libération crée une transaction de type 'release'
- ✅ Logs détaillés de toutes les opérations

## 📞 Support

Pour toute question ou problème :
- Email : academie@herime.com
- Documentation Moneroo : https://docs.moneroo.io

## 🔄 Maintenance

### Vérifier l'état des holds

```php
// Obtenir tous les holds actifs
$activeHolds = WalletHold::active()->get();

// Obtenir les holds libérables
$releasableHolds = WalletHold::releasable()->get();

// Pour un wallet spécifique
$wallet = Wallet::find($walletId);
$holds = $wallet->activeHolds;
```

### Libérer manuellement un hold

```php
$hold = WalletHold::find($holdId);

if ($hold->isReleasable()) {
    $hold->release();
}
```

### Annuler un hold

```php
$hold = WalletHold::find($holdId);
$hold->cancel('Raison de l\'annulation');
```

---

**Date de création** : 17 Décembre 2025  
**Version** : 1.0

