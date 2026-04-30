<?php
require_once __DIR__ . '/../modeles/Fiche.php';
require_once __DIR__ . '/../modeles/LigneFrais.php';
require_once __DIR__ . '/utils/AuthMiddleware.php';
require_once __DIR__ . '/utils/ReponseJson.php';

$utilisateurApi = AuthMiddleware::utilisateurConnecteApi();
$idUtilisateur = (int) $utilisateurApi['id_utilisateur'];
$roleUtilisateur = (string) ($utilisateurApi['role'] ?? '');
$methode = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];

$trouverFicheAccessible = static function (int $idFiche) use ($idUtilisateur, $roleUtilisateur) {
    return in_array($roleUtilisateur, ['comptable', 'administrateur'], true)
        ? Fiche::trouverParId($idFiche)
        : Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur);
};

if ($methode === 'GET') {
    $idFiche = (int) ($_GET['id_fiche'] ?? 0);
    if ($idFiche <= 0) ReponseJson::envoyer(['succes' => false, 'message' => 'Identifiant de fiche invalide.'], 400);
    $fiche = $trouverFicheAccessible($idFiche);
    if (!$fiche) ReponseJson::envoyer(['succes' => false, 'message' => 'Fiche introuvable.'], 404);
    ReponseJson::envoyer(['succes' => true, 'hors_forfaits' => Fiche::horsForfaits($idFiche)]);
}

if ($roleUtilisateur !== 'visiteur') {
    ReponseJson::envoyer(['succes' => false, 'message' => 'Action réservée au visiteur.'], 403);
}

if ($methode === 'POST') {
    $idFiche = (int) ($body['id_fiche'] ?? 0);
    $fiche = Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur);
    if (!$fiche) ReponseJson::envoyer(['succes' => false, 'message' => 'Fiche introuvable.'], 404);
    $ok = Fiche::ajouterHorsForfait(
        $idFiche,
        trim((string) ($body['type_consommation'] ?? '')),
        trim((string) ($body['date'] ?? '')),
        trim((string) ($body['libelle'] ?? '')),
        (float) ($body['montant'] ?? 0),
        trim((string) ($body['commentaire'] ?? '')),
        (float) ($body['taux_tva'] ?? 0)
    );
    ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Hors forfait ajouté avec succès.' : 'Ajout impossible.'], $ok ? 201 : 400);
}

if ($methode === 'PUT') {
    $id = (int) ($body['id'] ?? 0);
    $ligne = LigneFrais::trouverHorsForfaitParId($id);
    if (!$ligne) ReponseJson::envoyer(['succes' => false, 'message' => 'Ligne introuvable.'], 404);
    $fiche = Fiche::trouverParIdEtUtilisateur((int) $ligne['id_fiche'], $idUtilisateur);
    if (!$fiche) ReponseJson::envoyer(['succes' => false, 'message' => 'Accès refusé.'], 403);
    $ok = LigneFrais::modifierHorsForfait($id, trim((string)($body['type_consommation'] ?? '')), trim((string)($body['date'] ?? '')), trim((string)($body['libelle'] ?? '')), (float)($body['montant'] ?? 0), trim((string)($body['commentaire'] ?? '')), (float)($body['taux_tva'] ?? 0));
    ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Ligne mise à jour.' : 'Mise à jour impossible.'], $ok ? 200 : 400);
}

if ($methode === 'DELETE') {
    $id = (int) ($_GET['id'] ?? $body['id'] ?? 0);
    $ligne = LigneFrais::trouverHorsForfaitParId($id);
    if (!$ligne) ReponseJson::envoyer(['succes' => false, 'message' => 'Ligne introuvable.'], 404);
    $fiche = Fiche::trouverParIdEtUtilisateur((int) $ligne['id_fiche'], $idUtilisateur);
    if (!$fiche) ReponseJson::envoyer(['succes' => false, 'message' => 'Accès refusé.'], 403);
    $ok = LigneFrais::supprimerHorsForfait($id);
    ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Ligne supprimée.' : 'Suppression impossible.'], $ok ? 200 : 400);
}

ReponseJson::envoyer(['succes' => false, 'message' => 'Méthode non autorisée.'], 405);
