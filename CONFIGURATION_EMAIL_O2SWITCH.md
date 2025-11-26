# Configuration Email SMTP O2Switch

## Configuration du fichier .env

Ajoutez ou modifiez les lignes suivantes dans votre fichier `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.o2switch.net
MAIL_PORT=587
MAIL_USERNAME=academie@herime.com
MAIL_PASSWORD=RYT&91{(*F!i
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=academie@herime.com
MAIL_FROM_NAME="Herime Academie"
```

## Paramètres configurés

- **Hébergeur** : O2Switch
- **Serveur SMTP** : mail.o2switch.net
- **Port** : 587 (TLS) - Alternative : 465 (SSL)
- **Chiffrement** : TLS - Alternative : SSL (si vous utilisez le port 465)
- **Email** : academie@herime.com
- **Nom d'expéditeur** : Herime Academie

## Configuration alternative (port 465 avec SSL)

Si le port 587 ne fonctionne pas, vous pouvez essayer la configuration SSL :

```env
MAIL_MAILER=smtp
MAIL_HOST=mail.o2switch.net
MAIL_PORT=465
MAIL_USERNAME=academie@herime.com
MAIL_PASSWORD=RYT&91{(*F!i
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=academie@herime.com
MAIL_FROM_NAME="Herime Academie"
```

## Après configuration

1. Vérifiez que votre fichier `.env` contient bien tous les paramètres ci-dessus
2. Vide le cache de configuration Laravel :
   ```bash
   php artisan config:clear
   ```
3. Testez l'envoi d'un email

## Test de configuration

Pour tester la configuration, vous pouvez utiliser une commande artisan ou simplement effectuer une action qui déclenche un email (inscription à un cours, paiement, etc.).

## Note importante

Le mot de passe contient des caractères spéciaux. Assurez-vous qu'il est bien entre guillemets dans le fichier `.env` si nécessaire.



