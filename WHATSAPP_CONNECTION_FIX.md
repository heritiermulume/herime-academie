# 🔧 Correction des identifiants Evolution API

## ✅ Informations de connexion CORRECTES

### 📡 URL du serveur
```
http://localhost:8080
```

### 🔐 Clé API globale
```
e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2
```

## 🔍 Vérification

La clé API fonctionne correctement (testée avec curl). Si l'interface manager dit "Identifiants invalides", essayez:

### Solution 1: Redémarrer Evolution API

```bash
# Arrêter
kill $(cat /tmp/evolution-api.pid)

# Redémarrer
cd evolution-api
npm start
```

Puis réessayez de vous connecter.

### Solution 2: Vérifier l'URL

Assurez-vous d'utiliser exactement:
- **URL**: `http://localhost:8080` (sans slash à la fin)
- **Clé API**: Copiez-collez exactement la clé ci-dessus

### Solution 3: Utiliser l'API directement (sans interface)

Si l'interface manager ne fonctionne pas, vous pouvez créer et connecter l'instance via l'API:

```bash
# L'instance est déjà créée, récupérez le QR code:
curl http://localhost:8080/instance/connect/default \
  -H "apikey: e20d827cf706399860c46f6b9f11e55ac4cbb77d0cbe5548648937727a4e55d2"
```

Ou ouvrez directement dans le navigateur:
```
http://localhost:8080/instance/connect/default
```

## 📝 Note

L'instance "default" existe déjà. Il vous suffit de la connecter à WhatsApp en scannant le QR code.

Une fois connectée, testez avec:
```bash
php artisan whatsapp:test
```

