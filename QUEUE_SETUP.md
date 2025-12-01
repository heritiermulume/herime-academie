# Configuration de la Queue pour l'Envoi Combiné

## Problème
Les messages combinés (Email + WhatsApp) ne sont pas envoyés car les jobs sont mis en queue mais ne sont pas exécutés sans worker actif.

## Solutions

### Option 1 : Utiliser la queue en mode "sync" (Recommandé pour développement)

Modifiez votre fichier `.env` :
```env
QUEUE_CONNECTION=sync
```

Avec cette configuration, les jobs s'exécutent immédiatement de manière synchrone après la réponse HTTP, sans nécessiter de worker.

**Avantages :**
- Pas besoin de lancer un worker
- Envoi immédiat garanti
- Simple à configurer

**Inconvénients :**
- Peut ralentir la réponse HTTP si beaucoup d'utilisateurs
- Pas vraiment asynchrone

### Option 2 : Utiliser la queue en mode "database" avec worker (Recommandé pour production)

1. **Configurer la queue dans `.env` :**
```env
QUEUE_CONNECTION=database
```

2. **S'assurer que la table `jobs` existe :**
```bash
php artisan queue:table
php artisan migrate
```

3. **Lancer le worker de queue :**
```bash
# En développement (une fois)
php artisan queue:work

# En production (en arrière-plan)
php artisan queue:work --daemon

# Ou avec supervisor/systemd pour redémarrage automatique
```

**Avantages :**
- Vraiment asynchrone
- Ne bloque pas l'interface
- Meilleur pour la production

**Inconvénients :**
- Nécessite un worker actif
- Plus complexe à configurer

### Option 3 : Traitement des jobs en queue

Si vous avez des jobs en attente dans la queue, vous pouvez les traiter :

```bash
# Traiter tous les jobs en queue
php artisan queue:work

# Traiter une seule fois
php artisan queue:work --once

# Traiter avec timeout
php artisan queue:work --timeout=60
```

## Vérification

Pour vérifier l'état de la queue :
```bash
# Compter les jobs en attente
php artisan tinker
>>> DB::table('jobs')->count()

# Voir les jobs échoués
>>> DB::table('failed_jobs')->count()
```

## Recommandation

Pour un environnement de développement/test, utilisez `QUEUE_CONNECTION=sync` dans votre `.env`.

Pour la production, utilisez `QUEUE_CONNECTION=database` avec un worker actif.

