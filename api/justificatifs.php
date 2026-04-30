<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../modeles/Fiche.php';
require_once __DIR__ . '/../modeles/Justificatif.php';
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
    $fiche = $trouverFicheAccessible($idFiche);
    if (!$fiche) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Fiche introuvable.'], 404);
    }

    ReponseJson::envoyer([
        'succes' => true,
        'justificatifs' => Justificatif::tousParFiche($idFiche)
    ]);
}

if ($roleUtilisateur !== 'visiteur') {
    ReponseJson::envoyer(['succes' => false, 'message' => 'Action réservée au visiteur.'], 403);
}

if ($methode === 'POST') {
    $idFiche = (int) ($_POST['id_fiche'] ?? 0);
    $fiche = Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur);
    if (!$fiche) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Fiche introuvable.'], 404);
    }

    if (!isset($_FILES['justificatif']) || !is_array($_FILES['justificatif'])) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Fichier justificatif manquant.'], 400);
    }

    $fichier = $_FILES['justificatif'];
    if (($fichier['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        ReponseJson::envoyer(['succes' => false, 'message' => "Échec de l'upload du justificatif."], 400);
    }

    $nomReel = (string) ($fichier['name'] ?? '');
    $extension = strtolower(pathinfo($nomReel, PATHINFO_EXTENSION));
    $extensionsAutorisees = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $extensionsAutorisees, true)) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Extension de fichier non autorisée.'], 400);
    }

    if (!is_dir(STOCKAGE_PATH)) {
        @mkdir(STOCKAGE_PATH, 0775, true);
    }

    $nomServeur = uniqid('justif_', true) . '.' . $extension;
    $cheminDestination = rtrim(STOCKAGE_PATH, "/\\") . DIRECTORY_SEPARATOR . $nomServeur;

    if (!move_uploaded_file((string) $fichier['tmp_name'], $cheminDestination)) {
        ReponseJson::envoyer(['succes' => false, 'message' => "Impossible d'enregistrer le justificatif sur le serveur."], 500);
    }

    $ttc5 = (float) ($_POST['montant_ttc_5'] ?? 0);
    $ttc10 = (float) ($_POST['montant_ttc_10'] ?? 0);
    $ttc20 = (float) ($_POST['montant_ttc_20'] ?? 0);

    $ok = Justificatif::ajouter($idFiche, $nomReel, $nomServeur, $extension, $ttc5, $ttc10, $ttc20);
    if (!$ok) {
        @unlink($cheminDestination);
        ReponseJson::envoyer(['succes' => false, 'message' => 'Enregistrement en base impossible.'], 500);
    }

    ReponseJson::envoyer(['succes' => true, 'message' => 'Justificatif ajouté avec succès.']);
}

if ($methode === 'DELETE') {
    $id = (int) ($_GET['id'] ?? $body['id'] ?? 0);
    $justificatif = Justificatif::trouverParId($id);
    if (!$justificatif) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Justificatif introuvable.'], 404);
    }

    $fiche = Fiche::trouverParIdEtUtilisateur((int) $justificatif['id_fiche'], $idUtilisateur);
    if (!$fiche) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Accès refusé.'], 403);
    }

    $ok = Justificatif::supprimer($id, (int) $justificatif['id_fiche']);
    if ($ok && !empty($justificatif['nom_serveur'])) {
        $chemin = rtrim(STOCKAGE_PATH, "/\\") . DIRECTORY_SEPARATOR . $justificatif['nom_serveur'];
        if (is_file($chemin)) {
            @unlink($chemin);
        }
    }

    ReponseJson::envoyer([
        'succes' => $ok,
        'message' => $ok ? 'Justificatif supprimé.' : 'Suppression impossible.'
    ], $ok ? 200 : 400);
}

ReponseJson::envoyer(['succes' => false, 'message' => 'Méthode non autorisée.'], 405);
