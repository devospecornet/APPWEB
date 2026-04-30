# API GSB Future

## Authentification
- `POST /api/connexion.php`
- `POST /api/deconnexion.php`
- Authentification par jeton Bearer via `Authorization: Bearer <token>`

## Réponse JSON homogène
Format recommandé :

```json
{
  "succes": true,
  "message": "...",
  "donnees": {}
}
```

En cas d'erreur :

```json
{
  "succes": false,
  "message": "Description de l'erreur"
}
```

## Fiches
- `GET /api/fiches.php`
- `GET /api/fiches.php?id=1`
- `POST /api/fiches.php`
- `PUT /api/fiches.php`
- `DELETE /api/fiches.php?id=1`

## Hors forfaits
- `GET /api/hors_forfaits.php?id_fiche=1`
- `POST /api/hors_forfaits.php`
- `PUT /api/hors_forfaits.php`
- `DELETE /api/hors_forfaits.php?id=1`

## Codes HTTP
- `200` succès
- `201` création
- `400` requête invalide
- `401` non authentifié
- `403` accès refusé
- `404` ressource introuvable
- `405` méthode non autorisée
- `500` erreur serveur
