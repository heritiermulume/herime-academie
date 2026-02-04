# Meta Pixel & Conversions API – Déduplication (event_id)

## Recommandation Meta

Meta recommande d’**améliorer le taux d’événements Pixel couverts par l’API Conversions** pour :

- Améliorer la précision des rapports  
- Réduire le **coût par résultat** (les annonceurs avec ~75 % de couverture en bénéficient par rapport au Pixel seul)

Pour cela, il faut **améliorer les clés de déduplication** entre les événements Pixel (navigateur) et Conversions API (serveur).

## Ce qui est déjà en place dans le projet

1. **Même `event_id` pour Pixel et CAPI**  
   Pour chaque événement (PageView, événements personnalisés) :
   - Le **Pixel** reçoit `eventID` (4ᵉ argument de `fbq('track', ...)`).
   - La **CAPI** reçoit le même identifiant dans `event_id`.
   - Génération côté client : `crypto.randomUUID()` ou `ev_<timestamp>_<random>`.

2. **Méthode recommandée Meta : Event ID + Event Name**  
   La déduplication repose sur :
   - `event_id` (CAPI) = `eventID` (Pixel)
   - `event_name` (CAPI) = nom de l’événement Pixel  
   Fenêtre de déduplication : 48 h.

3. **Données utilisateur pour le matching**  
   La CAPI envoie aussi : `fbp`, `fbc`, IP, User-Agent, et si connecté : email/telephone hashés (SHA-256), pour améliorer le matching côté Meta.

4. **Robustesse de la couverture CAPI**  
   En cas d’échec du premier envoi CAPI (réseau, timeout), une **seule relance** est faite après ~600 ms pour PageView et pour les événements déclenchés (clics, soumissions, etc.), afin d’augmenter le taux d’événements effectivement couverts par la CAPI.

## Configuration à vérifier (Events Manager / Admin)

- **Pixel Meta** : activé et correctement configuré (IDs dans Réglages > Meta Pixel & Events).
- **Conversions API** : activée dans Réglages (case « Activer Meta Conversions API (CAPI) »).
- **Token CAPI** : Access Token Graph API renseigné (récupéré dans Events Manager / Paramètres de la source > Conversions API). À garder **privé** (jamais exposé au front).

Avec Pixel + CAPI activés et le même `event_id` envoyé des deux côtés, vous êtes aligné avec la recommandation Meta pour la déduplication et une meilleure couverture CAPI.

## Références Meta

- [Deduplicate Pixel and Server Events](https://developers.facebook.com/docs/marketing-api/conversions-api/deduplicate-pixel-and-server-events/)
- [Server Event Parameters (event_id)](https://developers.facebook.com/docs/marketing-api/conversions-api/parameters/server-event/)
- [End-to-End Implementation](https://developers.facebook.com/docs/marketing-api/conversions-api/guides/end-to-end-implementation)
