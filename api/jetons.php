<?php
require_once __DIR__ . '/../modeles/ApiJeton.php';
require_once __DIR__ . '/utils/AuthMiddleware.php';
require_once __DIR__ . '/utils/ReponseJson.php';

$utilisateurApi = AuthMiddleware::utilisateurConnecteApi();
if (($utilisateurApi['role'] ?? '') !== 'administrateur') {
    ReponseJson::envoyer(['succes' => false, 'message' => "Accès réservé à l'administrateur."], 403);
}

$methode = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];

if ($methode === 'GET') {
    ReponseJson::envoyer([
        'succes' => true,
        'jetons' => ApiJeton::lister(1, 100),
        'statistiques' => ApiJeton::statistiques()
    ]);
}

if ($methode === 'DELETE') {
    $id = (int) ($_GET['id'] ?? $body['id'] ?? 0);
    if ($id <= 0) {
        ReponseJson::envoyer(['succes' => false, 'message' => 'Identifiant de jeton invalide.'], 400);
    }
    $ok = ApiJeton::revoquer($id);
    ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Jeton révoqué.' : 'Révocation impossible.'], $ok ? 200 : 400);
}

if ($methode === 'POST' && (($body['action'] ?? '') === 'purger')) {
    $nb = ApiJeton::purgerExpires();
    ReponseJson::envoyer(['succes' => true, 'message' => 'Jetons expirés purgés.', 'nombre' => $nb]);
}

ReponseJson::envoyer(['succes' => false, 'message' => 'Méthode non autorisée.'], 405);
