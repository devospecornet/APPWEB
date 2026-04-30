<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../modeles/Utilisateur.php';
require_once __DIR__ . '/utils/AuthMiddleware.php';
require_once __DIR__ . '/utils/ReponseJson.php';

$utilisateurApi = AuthMiddleware::utilisateurConnecteApi();

if (($utilisateurApi['role'] ?? '') !== 'administrateur') {
    ReponseJson::envoyer([
        'succes' => false,
        'message' => 'Accès réservé à l\'administrateur.'
    ], 403);
}

$methode = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$idAdmin = (int) ($utilisateurApi['id_utilisateur'] ?? 0);

if ($methode === 'GET') {
    ReponseJson::envoyer([
        'succes' => true,
        'utilisateurs' => Utilisateur::tous()
    ]);
}

if ($methode === 'POST') {
    $nom = trim($body['nom'] ?? '');
    $prenom = trim($body['prenom'] ?? '');
    $email = trim($body['email'] ?? '');
    $motDePasse = trim($body['mot_de_passe'] ?? '');
    $role = trim($body['role'] ?? '');

    if ($nom === '' || $prenom === '' || $email === '' || $motDePasse === '' || $role === '') {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Tous les champs sont obligatoires.'], 400);
    }

    if (!in_array($role, ['visiteur', 'comptable', 'administrateur'], true)) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Rôle invalide.'], 400);
    }

    if (Utilisateur::emailExiste($email)) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Cet email existe déjà.'], 409);
    }

    $ok = Utilisateur::creer($nom, $prenom, $email, $motDePasse, $role, 1);
    ReponseJson::envoyer([
        'succes' => $ok,
        'message' => $ok ? 'Utilisateur créé avec succès.' : 'Création impossible.'
    ], $ok ? 201 : 500);
}

if ($methode === 'PUT') {
    $idUtilisateur = (int) ($body['id_utilisateur'] ?? $body['id'] ?? 0);
    $nom = trim($body['nom'] ?? '');
    $prenom = trim($body['prenom'] ?? '');
    $email = trim($body['email'] ?? '');
    $role = trim($body['role'] ?? '');
    $estApprouve = isset($body['est_approuve']) ? (int) $body['est_approuve'] : 1;
    $motDePasse = isset($body['mot_de_passe']) ? trim((string) $body['mot_de_passe']) : null;

    if ($idUtilisateur <= 0 || $nom === '' || $prenom === '' || $email === '' || $role === '') {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Paramètres invalides.'], 400);
    }

    if (!in_array($role, ['visiteur', 'comptable', 'administrateur'], true)) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Rôle invalide.'], 400);
    }

    if (Utilisateur::emailExiste($email, $idUtilisateur)) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Cet email existe déjà.'], 409);
    }

    $ok = Utilisateur::modifierParAdmin($idUtilisateur, $nom, $prenom, $email, $role, $estApprouve, $motDePasse);
    ReponseJson::envoyer([
        'succes' => $ok,
        'message' => $ok ? 'Utilisateur mis à jour.' : 'Mise à jour impossible.'
    ], $ok ? 200 : 400);
}

if ($methode === 'DELETE') {
    $idUtilisateur = (int) ($_GET['id'] ?? $body['id_utilisateur'] ?? $body['id'] ?? 0);

    if ($idUtilisateur <= 0) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Identifiant utilisateur invalide.'], 400);
    }

    if ($idUtilisateur === $idAdmin) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Tu ne peux pas supprimer ton propre compte.'], 400);
    }

    $ok = Utilisateur::supprimer($idUtilisateur);
    ReponseJson::envoyer([
        'succes' => $ok,
        'message' => $ok ? 'Utilisateur supprimé avec succès.' : 'Suppression impossible.'
    ], $ok ? 200 : 400);
}

ReponseJson::envoyer([
    'succes' => false,
    'message' => 'Méthode non autorisée.'
], 405);
