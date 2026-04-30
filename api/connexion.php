<?php
require_once __DIR__ . '/../configuration/base.php';
require_once __DIR__ . '/../modeles/Utilisateur.php';
require_once __DIR__ . '/utils/ReponseJson.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];

$email = trim($body['email'] ?? '');
$motDePasse = $body['mot_de_passe'] ?? '';

if ($email === '' || $motDePasse === '') {
    ReponseJson::envoyer(['succes' => false, 'message' => 'Email et mot de passe obligatoires.'], 400);
}

$utilisateur = Utilisateur::verifierConnexion($email, $motDePasse);

if (!$utilisateur) {
    ReponseJson::envoyer(['succes' => false, 'message' => 'Identifiants invalides.'], 401);
}

Base::connexion()->prepare('DELETE FROM api_jetons WHERE id_utilisateur = :id_utilisateur')
    ->execute(['id_utilisateur' => $utilisateur['id']]);

$jeton = bin2hex(random_bytes(32));
$expiration = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');

Base::connexion()->prepare(
    'INSERT INTO api_jetons (id_utilisateur, jeton, date_expiration)
     VALUES (:id_utilisateur, :jeton, :date_expiration)'
)->execute([
    'id_utilisateur' => $utilisateur['id'],
    'jeton' => $jeton,
    'date_expiration' => $expiration
]);

ReponseJson::envoyer([
    'succes' => true,
    'message' => 'Connexion API réussie.',
    'data' => [
        'jeton' => $jeton,
        'utilisateur' => [
            'id' => (int) $utilisateur['id'],
            'nom' => $utilisateur['nom'],
            'prenom' => $utilisateur['prenom'],
            'email' => $utilisateur['email'],
            'role' => $utilisateur['role']
        ]
    ]
]);
