# Résolution du conflit Git en production

## Problème
Le fichier `storage/app/private/.gitignore` a des modifications locales qui entrent en conflit avec les modifications distantes.

## Solution rapide (recommandée)

### Option 1 : Accepter la version distante (si les modifications locales ne sont pas importantes)

```bash
# Sur le serveur de production, exécutez :
git checkout --theirs storage/app/private/.gitignore
git add storage/app/private/.gitignore
git commit -m "Résolution du conflit: acceptation de la version distante pour .gitignore"
```

### Option 2 : Voir les différences d'abord

```bash
# Voir les différences entre local et distant
git diff HEAD storage/app/private/.gitignore
git diff origin/main storage/app/private/.gitignore

# Si la version distante est correcte, accepter celle-ci :
git checkout --theirs storage/app/private/.gitignore
git add storage/app/private/.gitignore
git commit -m "Résolution du conflit .gitignore"
```

### Option 3 : Stash les modifications locales (si vous voulez les conserver)

```bash
# Sauvegarder les modifications locales
git stash

# Faire le pull
git pull origin main

# Appliquer à nouveau les modifications si nécessaire
git stash pop
```

## Le fichier .gitignore devrait contenir :

```
*
!.gitignore
```

Cela permet d'ignorer tous les fichiers dans `storage/app/private/` sauf le fichier `.gitignore` lui-même.

## Après résolution du conflit

Une fois le conflit résolu, vous pouvez continuer avec :

```bash
git pull origin main
```

Ou si vous avez déjà fait le pull :

```bash
git merge origin/main
```

