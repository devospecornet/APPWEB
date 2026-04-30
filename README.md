# GSB Future

Version améliorée de l'application de notes de frais pour un contexte BTS SIO.

## Points livrés
- base de données `gsbfutur`
- suppression du dossier `noyau`
- helpers déplacés dans `includes/`
- connexion PDO sécurisée
- TVA calculée sur le total TTC saisi, forfait + hors forfait
- page administrateur dédiée pour toutes les fiches
- journalisation accessible au comptable et à l'administrateur
- affichage des dates en `JJ/MM/AAAA`
- endpoints REST conservés pour la suite Android

## Import SQL
Importer `sql/gsbfutur.sql` dans phpMyAdmin ou exécuter le script de mise à jour fourni.

## Comptes de démonstration
Mot de passe pour les trois comptes : `PREHELNEO18AI`
- admin@gsb.local
- comptable@gsb.local
- visiteur@gsb.local

## Connexion base locale
Par défaut :
- base : `gsbfutur`
- utilisateur : `root`
- mot de passe MySQL : selon ta configuration locale

Tu peux aussi surcharger avec des variables d'environnement :
- `GSB_DB_HOST`
- `GSB_DB_PORT`
- `GSB_DB_NAME`
- `GSB_DB_USER`
- `GSB_DB_PASS`
