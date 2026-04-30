<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../configuration/securite.php';
require_once __DIR__ . '/../modeles/Fiche.php';
require_once __DIR__ . '/../modeles/Journal.php';
require_once __DIR__ . '/../modeles/CommentaireFiche.php';
require_once __DIR__ . '/../modeles/Notification.php';

class ComptableControleur
{
    public function liste(): void
    {
        exigerConnexion(['comptable', 'administrateur']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifierCsrfOuEchouer();
            $idFiche = (int) ($_POST['id_fiche'] ?? 0);
            $commentaire = trim($_POST['commentaire_comptable'] ?? '');
            $idUtilisateur = (int) ($_SESSION['utilisateur']['id'] ?? 0);
            $role = (string) ($_SESSION['utilisateur']['role'] ?? 'comptable');

            if (isset($_POST['valider_fiche']) && $idFiche > 0) {
                if (Fiche::valider($idFiche, $commentaire)) {
                    if ($commentaire !== '') {
                        CommentaireFiche::ajouter($idFiche, $idUtilisateur, $role, $commentaire, 'validation');
                    }
                    Journal::enregistrerDepuisSession('VALIDATION_FICHE', 'fiche_frais', $idFiche, 'Fiche validée par le comptable');
                    $fiche = Fiche::trouverParId($idFiche);
                    if ($fiche) {
                        Notification::ajouter((int) $fiche['id_utilisateur'], 'Fiche validée', 'Votre fiche ' . $fiche['numero_fiche'] . ' a été validée.', 'info');
                        Notification::ajouter(null, 'Validation comptable', 'La fiche ' . $fiche['numero_fiche'] . ' a été validée.', 'info');
                    }
                }
            }

            if (isset($_POST['refuser_fiche']) && $idFiche > 0 && $commentaire !== '') {
                if (Fiche::refuser($idFiche, $commentaire)) {
                    CommentaireFiche::ajouter($idFiche, $idUtilisateur, $role, $commentaire, 'refus');
                    Journal::enregistrerDepuisSession('REFUS_FICHE', 'fiche_frais', $idFiche, 'Fiche refusée : ' . $commentaire, 'warning');
                    $fiche = Fiche::trouverParId($idFiche);
                    if ($fiche) {
                        Notification::ajouter((int) $fiche['id_utilisateur'], 'Fiche refusée', 'Votre fiche ' . $fiche['numero_fiche'] . ' a été refusée. Consultez le commentaire du comptable.', 'warning');
                        Notification::ajouter(null, 'Refus comptable', 'La fiche ' . $fiche['numero_fiche'] . ' a été refusée.', 'warning');
                    }
                }
            }

            if (isset($_POST['supprimer_fiche_comptable']) && $idFiche > 0) {
                if (Fiche::supprimerParAdminOuComptable($idFiche)) {
                    Journal::enregistrerDepuisSession('SUPPRESSION_FICHE', 'fiche_frais', $idFiche, 'Fiche transmise supprimée par le comptable', 'warning');
                }
            }

            header('Location: ' . asset_url('comptable.php'));
            exit;
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $parPageParam = (int) ($_GET['par_page'] ?? 10);
        $parPage = in_array($parPageParam, [10,20,50], true) ? $parPageParam : 10;
        $resultat = Fiche::toutesAvecFiltresPagines('transmise', trim($_GET['mois'] ?? ''), trim($_GET['recherche'] ?? ''), $page, $parPage);
        $fiches = $resultat['donnees'];
        $pagination = pagination_infos($resultat['total'], $page, $parPage);

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="fiches_comptable.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Numero','Visiteur','Email','Mois','Montant TTC','Statut'], ';');
            foreach (Fiche::toutesAvecFiltresPagines('transmise', trim($_GET['mois'] ?? ''), trim($_GET['recherche'] ?? ''), 1, 10000)['donnees'] as $f) {
                fputcsv($out, [$f['numero_fiche'], trim($f['prenom'] . ' ' . $f['nom']), $f['email'], $f['mois'], $f['montant_total'], $f['statut']], ';');
            }
            fclose($out);
            exit;
        }

        require __DIR__ . '/../vues/comptable/validation.php';
    }
}
