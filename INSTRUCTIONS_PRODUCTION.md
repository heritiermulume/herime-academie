# Instructions pour résoudre le conflit en PRODUCTION

## 🔴 Actions à faire SUR LE SERVEUR DE PRODUCTION

### 1. Connectez-vous au serveur (SSH)
```bash
ssh votre-utilisateur@votre-serveur
```

### 2. Allez dans le répertoire du projet
```bash
cd /chemin/vers/herime-academie
```

### 3. Vérifiez l'état actuel
```bash
git status
```

### 4. Résolvez le conflit (copiez-collez ces 3 commandes)
```bash
git checkout --theirs storage/app/private/.gitignore
git add storage/app/private/.gitignore
git commit -m "Résolution conflit .gitignore"
```

### 5. Terminez la mise à jour
```bash
git pull origin main
```

## ✅ Vérification

Vérifiez que tout est OK :
```bash
git status
cat storage/app/private/.gitignore
```

Le fichier devrait contenir :
```
*
!.gitignore
```

## ⚠️ Si vous obtenez d'autres erreurs

Si `git pull` vous demande encore quelque chose :
- Suivez les instructions affichées
- Ou contactez-moi avec le message d'erreur exact

## 📝 Note importante

Si vous êtes **bloqué au milieu d'un merge**, toutes ces commandes doivent être exécutées dans l'ordre.
Si vous n'avez pas encore fait `git pull`, commencez directement par `git pull origin main` et suivez les instructions.

