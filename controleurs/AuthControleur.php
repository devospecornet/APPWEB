<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../configuration/securite.php';
require_once __DIR__ . '/../modeles/Utilisateur.php';
require_once __DIR__ . '/../modeles/Journal.php';
require_once __DIR__ . '/../modeles/Notification.php';

class AuthControleur
{
    public function connexion(): void
    {
        demarrerSessionSiNecessaire();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifierCsrfOuEchouer();
            $email = trim($_POST['email'] ?? '');
            $motDePasse = $_POST['mot_de_passe'] ?? '';

            if (!limiterTentativesConnexion($email)) {
                $message = 'Trop de tentatives. Réessaie dans 15 minutes.';
                require __DIR__ . '/../vues/auth/connexion.php';
                return;
            }

            $utilisateur = Utilisateur::verifierConnexion($email, $motDePasse);

            if ($utilisateur) {
                session_regenerate_id(true);
                reinitialiserTentativesConnexion($email);
                $_SESSION['utilisateur'] = [
                    'id' => (int) $utilisateur['id'],
                    'nom' => $utilisateur['nom'],
                    'prenom' => $utilisateur['prenom'],
                    'email' => $utilisateur['email'],
                    'role' => $utilisateur['role']
                ];
                $_SESSION['LAST_ACTIVITY'] = time();

                Journal::enregistrerDepuisSession(
                    'CONNEXION',
                    'utilisateur',
                    (int) $utilisateur['id'],
                    'Connexion réussie pour ' . $utilisateur['email']
                );

                Notification::ajouter((int) $utilisateur['id'], 'Connexion enregistrée', 'Votre connexion a bien été prise en compte.', 'info');
                header('Location: ' . asset_url('tableau_bord.php'));
                exit;
            }

            $message = 'Identifiants invalides.';
            require __DIR__ . '/../vues/auth/connexion.php';
            return;
        }

        $message = $_SESSION['message_connexion'] ?? '';
        unset($_SESSION['message_connexion']);

        require __DIR__ . '/../vues/auth/connexion.php';
    }

    public function deconnexion(): void
    {
        demarrerSessionSiNecessaire();
        if (isset($_SESSION['utilisateur'])) {
            Journal::enregistrerDepuisSession(
                'DECONNEXION',
                'utilisateur',
                (int) ($_SESSION['utilisateur']['id'] ?? 0),
                'Déconnexion utilisateur'
            );
        }
        session_unset();
        session_destroy();
        header('Location: ' . asset_url('index.php'));
        exit;
    }
}
