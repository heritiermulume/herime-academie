# Configuration du fichier .env sur O2Switch

Après avoir exécuté les migrations et seeders, vous devez vérifier votre fichier `.env` pour éviter les erreurs SMTP.

## Configuration des emails

Ouvrez le fichier `.env` dans votre compte O2Switch et assurez-vous que cette ligne existe :

```env
MAIL_MAILER=log
```

Si vous voyez des lignes comme :
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.o2switch.net
```

Remplacez-les par :
```env
MAIL_MAILER=log
```

## Pourquoi "log" ?

Le driver `log` enregistre les emails dans les fichiers de log au lieu d'essayer de les envoyer par SMTP. C'est parfait pour le développement et les tests.

## Commandes à exécuter après modification

Une fois que vous avez modifié le `.env`, exécutez ces commandes :

```bash
cd ~/herime-academie
php artisan config:clear
php artisan config:cache
php artisan cache:clear
```

## Pour vérifier la configuration

Exécutez cette commande pour voir la configuration actuelle :

```bash
php artisan tinker
```

Puis dans tinker :

```php
config('mail.default')
config('mail.mailers')
```

Vous devriez voir `log` comme valeur par défaut.

## Configuration SMTP complète (optionnel)

Si vous voulez configurer SMTP plus tard avec O2Switch, voici les paramètres :

```env
MAIL_MAILER=smtp
MAIL_HOST=ssl0.ovh.net
MAIL_PORT=587
MAIL_USERNAME=votre-email@votre-domaine.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@academie.herime.com
MAIL_FROM_NAME="Herime Académie"
```

Mais pour l'instant, utilisez `log` pour éviter les erreurs.
