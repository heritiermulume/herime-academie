# 🔄 Reconnecter l'instance WhatsApp

## ✅ L'instance a été déconnectée et recréée

L'instance "default" a été supprimée et recréée pour permettre la connexion.

## 📱 Obtenir le QR code

### Option 1: Via l'interface Manager

1. **Rafraîchissez la page** http://localhost:8080/manager
2. **Trouvez l'instance "default"**
3. **Cliquez sur "Connect"** ou l'icône de connexion
4. Le QR code devrait apparaître

### Option 2: URL directe

Ouvrez dans votre navigateur :
```
http://localhost:8080/instance/connect/default
```

### Option 3: Via l'API (si les autres ne fonctionnent pas)

Si l'endpoint `/connect` ne fonctionne pas, vous pouvez essayer de redémarrer l'instance :

```bash
# Redémarrer l'instance
curl -X POST http://localhost:8080/instance/restart/default \
  -H "apikey: e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2"
```

Puis réessayez d'obtenir le QR code.

## 🔍 Vérification de l'état

Vérifiez l'état actuel de l'instance :

```bash
php artisan whatsapp:test
```

Ou via curl :
```bash
curl http://localhost:8080/instance/fetchInstances \
  -H "apikey: e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2"
```

## 💡 Note

Si l'interface manager dit que l'instance est "connectée" mais que le statut réel est "close", c'est probablement un problème d'affichage de l'interface. L'instance a été recréée et devrait maintenant permettre la connexion.

