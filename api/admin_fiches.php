<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../modeles/Fiche.php';
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

if ($methode === 'GET') {
    ReponseJson::envoyer([
        'succes' => true,
        'fiches' => Fiche::toutesNonValideesAdmin()
    ]);
}

if ($methode === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    if (!is_array($body)) {
        ReponseJson::envoyer([
            'succes' => false,
            'message' => 'Corps JSON invalide.'
        ], 400);
    }

    $idFiche = (int) ($body['id_fiche'] ?? 0);
    $action = trim($body['action'] ?? '');
    $commentaire = trim($body['commentaire'] ?? '');

    if ($idFiche <= 0 || !in_array($action, ['valider', 'refuser', 'supprimer'], true)) {
        ReponseJson::envoyer([
            'succes' => false,
            'message' => 'Paramètres invalides.'
        ], 400);
    }

    if ($action === 'valider') {
        $ok = Fiche::validerParAdmin($idFiche, $commentaire);

        ReponseJson::envoyer([
            'succes' => $ok,
            'message' => $ok ? 'Fiche validée par l\'administrateur.' : 'Validation impossible.'
        ], $ok ? 200 : 400);
    }

    if ($action === 'refuser') {
        if ($commentaire === '') {
            ReponseJson::envoyer([
                'succes' => false,
                'message' => 'Le commentaire est obligatoire pour refuser.'
            ], 400);
        }

        $ok = Fiche::refuserParAdmin($idFiche, $commentaire);

        ReponseJson::envoyer([
            'succes' => $ok,
            'message' => $ok ? 'Fiche refusée par l\'administrateur.' : 'Refus impossible.'
        ], $ok ? 200 : 400);
    }

    $ok = Fiche::supprimerParAdmin($idFiche);

    ReponseJson::envoyer([
        'succes' => $ok,
        'message' => $ok ? 'Fiche supprimée par l\'administrateur.' : 'Suppression impossible.'
    ], $ok ? 200 : 400);
}

ReponseJson::envoyer([
    'succes' => false,
    'message' => 'Méthode non autorisée.'
], 405);
