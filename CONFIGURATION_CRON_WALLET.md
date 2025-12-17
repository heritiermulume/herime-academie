# ⏰ Configuration du Cron pour la Libération Automatique des Fonds

## 📋 Vue d'ensemble

Le système de libération automatique des fonds nécessite que le **Laravel Scheduler** soit configuré correctement sur votre serveur. Cette documentation explique comment le configurer et vérifier qu'il fonctionne.

## 🔧 Configuration du Cron Job

### Sur le Serveur de Production

1. **Ouvrir le crontab**

```bash
crontab -e
```

2. **Ajouter cette ligne** (une seule fois)

```bash
* * * * * cd /chemin/vers/herime-academie && php artisan schedule:run >> /dev/null 2>&1
```

**Remplacez** `/chemin/vers/herime-academie` par le chemin absolu vers votre projet.

**Exemple réel** :
```bash
* * * * * cd /var/www/herime-academie && php artisan schedule:run >> /dev/null 2>&1
```

3. **Sauvegarder et quitter**

- Sous `nano` : `Ctrl+X`, puis `Y`, puis `Entrée`
- Sous `vim` : `:wq`

### ⚠️ Important

- Cette ligne doit être ajoutée **une seule fois**
- Elle s'exécute **toutes les minutes**
- Laravel Scheduler décide ensuite quelles tâches exécuter selon leur planification

## 🕐 Planning d'Exécution

Le système de libération automatique s'exécute :

- **Quand** : Tous les jours à **2h00 du matin** (heure de Kinshasa)
- **Condition** : Seulement si **"Libération automatique"** est activée dans les paramètres
- **Action** : Libère tous les fonds dont la période de blocage est terminée

## ✅ Vérifier que le Cron Fonctionne

### Méthode 1 : Depuis l'Interface Admin

1. Allez dans **Admin → Paramètres**
2. Faites défiler jusqu'à la section **"Tester le système de libération"**
3. Cliquez sur **"Tester maintenant"**

**Résultat attendu** :
- ✅ `Aucun fonds à libérer pour le moment` → Tout fonctionne, pas de fonds à libérer
- ✅ `X fond(s) sont prêts à être libérés` → Tout fonctionne, des fonds seront libérés à 2h

### Méthode 2 : Via la Ligne de Commande

```bash
# Se connecter au serveur en SSH
ssh user@votre-serveur.com

# Aller dans le répertoire du projet
cd /var/www/herime-academie

# Tester en mode simulation (ne libère rien)
php artisan wallet:release-holds --dry-run
```

**Résultat attendu** :
```
🔓 Démarrage de la libération des fonds bloqués...

📊 2 hold(s) à traiter

[====================] 100%

═══════════════════════════════════════
           RÉSUMÉ DE L'OPÉRATION       
═══════════════════════════════════════

Settings créés     | 2
Settings existants | 0
Total              | 2

✅ Libération terminée avec succès !
```

### Méthode 3 : Vérifier les Logs Laravel

```bash
# Voir les derniers logs
tail -f storage/logs/laravel.log

# Rechercher les logs de libération
grep "Hold libéré" storage/logs/laravel.log
```

**Résultat attendu** :
```
[2025-12-17 02:00:15] production.INFO: Hold libéré automatiquement {"hold_id":5,"wallet_id":12,"amount":50.00,"currency":"USD"}
```

## 🧪 Forcer une Libération Manuelle

Si vous voulez libérer les fonds **immédiatement** sans attendre 2h du matin :

```bash
# Libération réelle (libère les fonds maintenant)
php artisan wallet:release-holds

# Forcer la libération de TOUS les fonds (même ceux pas encore expirés)
php artisan wallet:release-holds --force
```

⚠️ **Attention** : `--force` libère même les fonds dont la période n'est pas terminée. À utiliser avec précaution.

## 🔍 Vérifier que le Cron est Actif

### Vérifier le crontab

```bash
crontab -l
```

**Résultat attendu** :
```
* * * * * cd /var/www/herime-academie && php artisan schedule:run >> /dev/null 2>&1
```

### Vérifier que le cron s'exécute

```bash
# Voir les dernières exécutions du cron
grep CRON /var/log/syslog | tail -20
```

**Résultat attendu** (toutes les minutes) :
```
Dec 17 14:23:01 server CRON[12345]: (www-data) CMD (cd /var/www/herime-academie && php artisan schedule:run >> /dev/null 2>&1)
Dec 17 14:24:01 server CRON[12346]: (www-data) CMD (cd /var/www/herime-academie && php artisan schedule:run >> /dev/null 2>&1)
```

## 🛠️ Dépannage

### Problème 1 : Le cron ne s'exécute pas

**Symptôme** : Aucune trace dans les logs

**Solution** :
1. Vérifier que le cron est bien ajouté : `crontab -l`
2. Vérifier que le chemin est correct
3. Vérifier les permissions : `ls -la /var/www/herime-academie`
4. Tester manuellement : `cd /var/www/herime-academie && php artisan schedule:run`

### Problème 2 : Erreur de permissions

**Symptôme** : `Permission denied` dans les logs

**Solution** :
```bash
# Donner les bonnes permissions
sudo chown -R www-data:www-data /var/www/herime-academie
sudo chmod -R 755 /var/www/herime-academie
sudo chmod -R 775 /var/www/herime-academie/storage
sudo chmod -R 775 /var/www/herime-academie/bootstrap/cache
```

### Problème 3 : PHP introuvable

**Symptôme** : `php: command not found`

**Solution** :
```bash
# Trouver le chemin de PHP
which php
# Résultat : /usr/bin/php8.2 (par exemple)

# Utiliser le chemin complet dans le crontab
* * * * * cd /var/www/herime-academie && /usr/bin/php8.2 artisan schedule:run >> /dev/null 2>&1
```

### Problème 4 : La libération ne s'active pas

**Symptôme** : Le cron fonctionne mais rien ne se passe à 2h

**Solution** :
1. Vérifier que l'option est activée : **Admin → Paramètres → Libération automatique**
2. Vérifier qu'il y a des fonds à libérer : `php artisan wallet:release-holds --dry-run`
3. Vérifier l'heure du serveur : `date` (doit être Africa/Kinshasa)

## 📊 Monitoring

### Créer un Webhook de Notification (Optionnel)

Pour être notifié quand des fonds sont libérés, vous pouvez ajouter dans `app/Console/Commands/ReleaseWalletHolds.php` :

```php
// Après la libération réussie, envoyer une notification
if ($successCount > 0) {
    \Notification::send(
        User::role('admin')->get(),
        new \App\Notifications\FundsReleasedNotification($successCount, $totalAmount)
    );
}
```

### Logs Détaillés

Les logs de libération sont dans :
- `storage/logs/laravel.log` (logs généraux)
- Rechercher : `"Hold libéré"` ou `"wallet:release-holds"`

## 🚀 En Développement Local

Pour tester en local sans attendre le cron :

```bash
# Terminal 1 : Démarrer le serveur Laravel
php artisan serve

# Terminal 2 : Simuler le cron (exécute toutes les minutes)
php artisan schedule:work

# Terminal 3 : Tester manuellement
php artisan wallet:release-holds --dry-run
```

## 📞 Support

Si le cron ne fonctionne toujours pas après avoir suivi ces étapes :
- Email : academie@herime.com
- Logs à partager : `storage/logs/laravel.log`

---

**Date de création** : 17 Décembre 2025  
**Version** : 1.0

