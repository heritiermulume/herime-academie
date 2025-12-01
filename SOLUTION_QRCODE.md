# 🔧 Solution pour le QR Code qui ne se charge pas

## 🐛 Problème identifié

L'erreur `Cannot read properties of undefined (reading 'state')` indique un bug dans Evolution API où `authState` n'est pas initialisé avant d'être utilisé.

## ✅ Correction appliquée

J'ai modifié le fichier `whatsapp.baileys.service.ts` pour s'assurer que `authState` est initialisé avant utilisation.

## 🔄 Redémarrage

Evolution API a été redémarré avec la correction.

## 📱 Tester maintenant

1. **Rafraîchissez la page** dans votre navigateur : http://localhost:8080/manager
2. **Cliquez sur "Connect"** pour l'instance "default"
3. Le QR code devrait maintenant apparaître

## 🔍 Si le problème persiste

### Option 1: Vérifier les logs
```bash
tail -50 /tmp/evolution-api.log
```

### Option 2: Supprimer et recréer l'instance
```bash
# Supprimer
curl -X DELETE http://localhost:8080/instance/delete/default \
  -H "apikey: e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2"

# Recréer
curl -X POST http://localhost:8080/instance/create \
  -H "apikey: e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2" \
  -H "Content-Type: application/json" \
  -d '{"instanceName":"default","integration":"WHATSAPP-BAILEYS"}'
```

### Option 3: Utiliser une version différente d'Evolution API

Si le problème persiste, vous pouvez essayer une version stable différente ou utiliser une autre solution WhatsApp API.

## 📝 Note

La correction a été appliquée directement dans le code source. Si vous mettez à jour Evolution API, vous devrez peut-être réappliquer cette correction.

