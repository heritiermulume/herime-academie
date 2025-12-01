# 🎯 Accès à l'interface Manager

## ✅ L'API fonctionne !

Vous avez vu le message de bienvenue, ce qui confirme que Evolution API est opérationnel.

## 📱 Accéder à l'interface Manager

**Ouvrez dans votre navigateur** :
```
http://localhost:8080/manager
```

## 🔑 Identifiants de connexion

Quand l'interface vous demande de vous connecter, utilisez :

### URL du serveur
```
http://localhost:8080
```

### Clé API globale
```
e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2
```

## 📝 Étapes suivantes

1. **Ouvrez** : http://localhost:8080/manager
2. **Entrez les identifiants** ci-dessus
3. **Connectez-vous**
4. **Trouvez l'instance "default"** dans la liste
5. **Cliquez sur "Connect"** ou l'icône de connexion
6. **Scannez le QR code** avec WhatsApp
7. **Attendez la confirmation** de connexion

## 🔄 Alternative si l'interface ne fonctionne pas

Si vous ne pouvez pas vous connecter à l'interface manager, vous pouvez obtenir le QR code directement :

**Ouvrez dans votre navigateur** :
```
http://localhost:8080/instance/connect/default
```

Cette URL devrait afficher le QR code directement (si l'instance est correctement configurée).

## ✅ Vérification après connexion

Une fois le QR code scanné et l'instance connectée :

```bash
php artisan whatsapp:test
```

Le statut devrait passer de `close` à `open`.

