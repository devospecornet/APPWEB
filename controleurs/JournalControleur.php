<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../configuration/securite.php';
require_once __DIR__ . '/../modeles/Journal.php';

class JournalControleur
{
    public function index(): void
    {
        exigerConnexion(['comptable', 'administrateur']);
        $role = $_SESSION['utilisateur']['role'] ?? 'comptable';
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $parPageParam = (int) ($_GET['par_page'] ?? 20);
        $parPage = in_array($parPageParam, [10,20,50], true) ? $parPageParam : 20;
        $filtres = [
            'action' => trim($_GET['action'] ?? ''),
            'role_utilisateur' => trim($_GET['role_utilisateur'] ?? ''),
            'utilisateur' => trim($_GET['utilisateur'] ?? ''),
            'date_debut' => trim($_GET['date_debut'] ?? ''),
            'date_fin' => trim($_GET['date_fin'] ?? ''),
        ];

        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            $resultat = Journal::pagines($role, $filtres, 1, 10000);
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="journal_actions.csv"');
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date','Utilisateur','Role','Action','Objet','Details','Niveau'], ';');
            foreach ($resultat['donnees'] as $log) {
                fputcsv($out, [$log['date_action'], trim(($log['prenom'] ?? '') . ' ' . ($log['nom'] ?? '')), $log['role_utilisateur'], $log['action'], $log['type_objet'], $log['details'], $log['niveau']], ';');
            }
            fclose($out);
            exit;
        }

        $resultat = Journal::pagines($role, $filtres, $page, $parPage);
        $logs = $resultat['donnees'];
        $pagination = pagination_infos($resultat['total'], $page, $parPage);
        require __DIR__ . '/../vues/journal/index.php';
    }
}
