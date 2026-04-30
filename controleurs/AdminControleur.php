<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../configuration/securite.php';
require_once __DIR__ . '/../modeles/Utilisateur.php';
require_once __DIR__ . '/../modeles/Fiche.php';
require_once __DIR__ . '/../modeles/Journal.php';
require_once __DIR__ . '/../modeles/Notification.php';
require_once __DIR__ . '/../modeles/ApiJeton.php';

class AdminControleur
{
    public function tableauBord(): void
    {
        exigerConnexion(['administrateur']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifierCsrfOuEchouer();
        }

        $message = '';
        $typeMessage = 'success';
        $utilisateurEdition = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = trim($_POST['action'] ?? '');

            if ($action === 'creer_utilisateur') {
                $nom = trim($_POST['nom'] ?? '');
                $prenom = trim($_POST['prenom'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $motDePasse = trim($_POST['mot_de_passe'] ?? '');
                $role = trim($_POST['role'] ?? 'visiteur');
                $estApprouve = isset($_POST['est_approuve']) ? 1 : 0;
                if ($nom === '' || $prenom === '' || $email === '' || $motDePasse === '' || $role === '') {
                    $message = 'Tous les champs de création sont obligatoires.';
                    $typeMessage = 'danger';
                } elseif (Utilisateur::emailExiste($email)) {
                    $message = 'Cet e-mail existe déjà.';
                    $typeMessage = 'danger';
                } elseif (Utilisateur::creer($nom, $prenom, $email, $motDePasse, $role, $estApprouve)) {
                    $message = 'Utilisateur créé avec succès.';
                    Journal::enregistrerDepuisSession('CREATION_UTILISATEUR', 'utilisateur', null, 'Création du compte ' . $email);
                    Notification::ajouter(null, 'Compte utilisateur créé', 'Un compte ' . $role . ' a été créé pour ' . $email . '.', 'info');
                } else {
                    $message = 'Création impossible.';
                    $typeMessage = 'danger';
                }
            }

            if ($action === 'modifier_utilisateur') {
                $idUtilisateur = (int) ($_POST['id_utilisateur'] ?? 0);
                $nom = trim($_POST['nom'] ?? '');
                $prenom = trim($_POST['prenom'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = trim($_POST['role'] ?? 'visiteur');
                $estApprouve = isset($_POST['est_approuve']) ? 1 : 0;
                $nouveauMotDePasse = trim($_POST['mot_de_passe'] ?? '');
                if ($idUtilisateur > 0 && Utilisateur::modifierParAdmin($idUtilisateur, $nom, $prenom, $email, $role, $estApprouve, $nouveauMotDePasse !== '' ? $nouveauMotDePasse : null)) {
                    $message = 'Utilisateur modifié avec succès.';
                    Journal::enregistrerDepuisSession('MODIFICATION_UTILISATEUR', 'utilisateur', $idUtilisateur, 'Modification du compte ' . $email);
                } else {
                    $message = 'Modification impossible.';
                    $typeMessage = 'danger';
                }
            }

            if ($action === 'supprimer_utilisateur') {
                $idUtilisateur = (int) ($_POST['id_utilisateur'] ?? 0);
                if ($idUtilisateur > 0 && $idUtilisateur !== (int) ($_SESSION['utilisateur']['id'] ?? 0) && Utilisateur::supprimer($idUtilisateur)) {
                    $message = 'Utilisateur supprimé avec succès.';
                    Journal::enregistrerDepuisSession('SUPPRESSION_UTILISATEUR', 'utilisateur', $idUtilisateur, 'Suppression d\'un compte utilisateur');
                } else {
                    $message = 'Suppression impossible.';
                    $typeMessage = 'danger';
                }
            }
        }

        if (isset($_GET['edit']) && ctype_digit((string) $_GET['edit'])) {
            $utilisateurEdition = Utilisateur::trouverParId((int) $_GET['edit']);
        }

        $utilisateurs = Utilisateur::tous();
        $statsUtilisateurs = Utilisateur::statistiques();
        $statsFiches = Fiche::statistiquesGlobales();
        $statsMensuelles = Fiche::statistiquesMensuelles(trim($_GET['mois_stats'] ?? ''));

        require __DIR__ . '/../vues/admin/tableau_bord_admin.php';
    }

    public function fiches(): void
    {
        exigerConnexion(['administrateur']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifierCsrfOuEchouer();
        }

        $statut = trim($_GET['statut'] ?? '');
        $mois = trim($_GET['mois'] ?? '');
        $recherche = trim($_GET['recherche'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $parPageParam = (int) ($_GET['par_page'] ?? 10);
        $parPage = in_array($parPageParam, [10,20,50], true) ? $parPageParam : 10;

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $resultat = Fiche::toutesAvecFiltresPagines($statut !== '' ? $statut : null, $mois !== '' ? $mois : null, $recherche !== '' ? $recherche : null, 1, 10000);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="fiches_admin.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Numero','Visiteur','Email','Mois','Date creation','Montant TTC','Statut'], ';');
            foreach ($resultat['donnees'] as $f) {
                fputcsv($out, [$f['numero_fiche'], trim(($f['prenom'] ?? '') . ' ' . ($f['nom'] ?? '')), $f['email'] ?? '', $f['mois'], $f['date_creation'], $f['montant_total'], $f['statut']], ';');
            }
            fclose($out);
            exit;
        }

        $resultat = Fiche::toutesAvecFiltresPagines($statut !== '' ? $statut : null, $mois !== '' ? $mois : null, $recherche !== '' ? $recherche : null, $page, $parPage);
        $pagination = pagination_infos($resultat['total'], $page, $parPage);
        $fiches = $resultat['donnees'];
        $statsGlobales = Fiche::statistiquesGlobales();
        $statsMensuelles = Fiche::statistiquesMensuelles($mois !== '' ? $mois : null);
        require __DIR__ . '/../vues/admin/fiches.php';
    }

    public function jetons(): void
    {
        exigerConnexion(['administrateur']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifierCsrfOuEchouer();
            if (isset($_POST['revoquer_jeton'])) {
                $idJeton = (int) ($_POST['id_jeton'] ?? 0);
                if ($idJeton > 0 && ApiJeton::revoquer($idJeton)) {
                    Journal::enregistrerDepuisSession('REVOCATION_JETON_API', 'api_jeton', $idJeton, 'Révocation manuelle d\'un jeton API');
                    Notification::ajouter(null, 'Jeton API révoqué', 'Un jeton API a été révoqué par un administrateur.', 'warning');
                }
            }
            if (isset($_POST['purger_expire'])) {
                $nombreSupprimes = ApiJeton::purgerExpires();
                if ($nombreSupprimes > 0) {
                    Journal::enregistrerDepuisSession('PURGE_JETONS_API', 'api_jeton', null, $nombreSupprimes . ' jeton(s) API expiré(s) supprimé(s)');
                    Notification::ajouter(null, 'Jetons API purgés', $nombreSupprimes . ' jeton(s) expiré(s) ont été supprimé(s).', 'info');
                }
            }
            header('Location: ' . asset_url('admin_jetons.php'));
            exit;
        }
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $parPageParam = (int) ($_GET['par_page'] ?? 20);
        $parPage = in_array($parPageParam, [10,20,50], true) ? $parPageParam : 20;
        $jetons = ApiJeton::lister($page, $parPage);
        $pagination = pagination_infos(ApiJeton::compter(), $page, $parPage);
        $statsJetons = ApiJeton::statistiques();
        require __DIR__ . '/../vues/admin/jetons.php';
    }

    public function notifications(): void
    {
        exigerConnexion(['visiteur','comptable','administrateur']);
        $utilisateur = $_SESSION['utilisateur'];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifierCsrfOuEchouer();
            if (isset($_POST['marquer_lue'])) {
                Notification::marquerCommeLue((int) ($_POST['id_notification'] ?? 0), (int) $utilisateur['id']);
            }
            if (isset($_POST['tout_marquer_lu'])) {
                Notification::toutMarquerCommeLu((int) $utilisateur['id'], (string) $utilisateur['role']);
            }
            header('Location: ' . asset_url('notifications.php'));
            exit;
        }
        $notifications = Notification::pourUtilisateur((int) $utilisateur['id'], (string) $utilisateur['role'], 100);
        require __DIR__ . '/../vues/admin/notifications.php';
    }
}
