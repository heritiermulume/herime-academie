# Configuration du Cron pour O2Switch

## 📋 Qu'est-ce qu'un cron ?

Un cron est une tâche qui s'exécute automatiquement à intervalles réguliers sur votre serveur.

Pour Laravel, il faut configurer UN SEUL cron qui appelle `schedule:run` chaque minute.

## 🎯 Pourquoi c'est important ?

Laravel utilise ce système pour :
- Envoyer des emails en différé
- Nettoyer le cache automatiquement
- Générer des rapports
- Synchroniser des données
- Toutes tâches planifiées que vous définissez

## ⚙️ Configuration sur O2Switch

### Méthode 1 : Via l'interface O2Switch

1. **Connectez-vous à votre espace client O2Switch**
2. **Allez dans "Crontab"** ou **"Tâches planifiées"**
3. **Cliquez sur "Ajouter une tâche"**
4. **Configurez comme suit :**

```
Minute:   *
Heure:    *
Jour:     *
Mois:     *
Jour Sem: *

Commande: cd /home/muhe3594/herime-academie && php artisan schedule:run >> /dev/null 2>&1
```

5. **Enregistrez**

### Méthode 2 : Via SSH

```bash
# Se connecter en SSH
ssh votre-compte@persil.o2switch.net

# Éditer le crontab
crontab -e

# Ajouter cette ligne (remplacer le chemin par le vôtre)
* * * * * cd /home/muhe3594/herime-academie && php artisan schedule:run >> /dev/null 2>&1

# Sauvegarder (Ctrl+O, Entrée, Ctrl+X dans nano)
```

### Méthode 3 : Tester d'abord

Avant de configurer le cron, testez que la commande fonctionne :

```bash
cd ~/herime-academie
php artisan schedule:run
```

Si vous voyez "Running scheduled tasks..." ou rien d'erreur, c'est bon !

## 🔍 Vérifier que le cron fonctionne

### 1. Voir les tâches planifiées définies

```bash
php artisan schedule:list
```

Cela vous montrera toutes les tâches qui seront exécutées.

### 2. Voir les logs du cron

```bash
# Au lieu de /dev/null, vous pouvez rediriger vers un fichier
# Dans le crontab, changez pour :
* * * * * cd /home/muhe3594/herime-academie && php artisan schedule:run >> storage/logs/cron.log 2>&1

# Puis voir les logs
tail -f storage/logs/cron.log
```

### 3. Tester manuellement

```bash
cd ~/herime-academie
php artisan schedule:run -v
```

Le flag `-v` (verbose) vous montrera ce qui s'exécute.

## 📝 Exemple de tâches planifiées dans votre projet

Dans `app/Console/Kernel.php`, vous pouvez définir des tâches comme :

```php
protected function schedule(Schedule $schedule)
{
    // Nettoyer le cache chaque jour à 2h du matin
    $schedule->command('cache:clear')->dailyAt('02:00');
    
    // Envoyer des emails de rappel chaque lundi
    $schedule->call(function () {
        // Votre code ici
    })->weeklyOn(1, '8:00');
    
    // Backup de la base de données chaque nuit
    $schedule->command('backup:run')->daily();
}
```

## ⚠️ Important

- **Le cron doit s'exécuter CHAQUE MINUTE** (c'est Laravel qui gère les intervalles)
- **Une seule ligne de cron suffit**
- **Le cron lui-même doit pointer vers le bon dossier**
- **Utilisez le chemin complet du projet**

## 🔧 Dépannage

### Le cron ne s'exécute pas

1. Vérifiez les permissions du fichier :
```bash
chmod 755 ~/herime-academie
```

2. Vérifiez que PHP est dans le PATH :
```bash
which php
```

Si `which php` ne retourne rien, utilisez le chemin complet :
```bash
* * * * * cd /home/muhe3594/herime-academie && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

3. Vérifiez les logs du serveur :
```bash
tail -f /var/log/cron.log
```

### Tester la configuration

```bash
# Se connecter en SSH
ssh votre-compte@persil.o2switch.net

# Voir le crontab actuel
crontab -l

# Devrait afficher quelque chose comme :
* * * * * cd /home/muhe3594/herime-academie && php artisan schedule:run >> /dev/null 2>&1
```

## ✅ Checklist

- [ ] Le cron est configuré dans O2Switch
- [ ] La commande utilise le bon chemin vers le projet
- [ ] PHP est accessible (ou chemin complet utilisé)
- [ ] Les permissions sont correctes
- [ ] Le test manuel (`php artisan schedule:run`) fonctionne
- [ ] Vous pouvez voir les tâches avec `php artisan schedule:list`

---

**Note** : Sur O2Switch, utilisez de préférence l'interface pour configurer le crontab, c'est plus simple et plus sûr.

