<?php
require_once __DIR__ . '/../configuration/base.php';
require_once __DIR__ . '/utils/AuthMiddleware.php';
require_once __DIR__ . '/utils/ReponseJson.php';

$methode = $_SERVER['REQUEST_METHOD'];
if (!in_array($methode, ['POST', 'DELETE'], true)) {
    ReponseJson::envoyer(['succes' => false, 'message' => 'Méthode non autorisée.'], 405);
}

$authorization = AuthMiddleware::recupererAuthorization();
if (!str_starts_with($authorization, 'Bearer ')) {
    ReponseJson::envoyer(['succes' => false, 'message' => 'Jeton Bearer manquant.'], 400);
}

$jeton = trim(substr($authorization, 7));
Base::connexion()->prepare('DELETE FROM api_jetons WHERE jeton = :jeton')->execute(['jeton' => $jeton]);

ReponseJson::envoyer(['succes' => true, 'message' => 'Déconnexion API effectuée.']);
