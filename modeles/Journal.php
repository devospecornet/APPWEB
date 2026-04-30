<?php
require_once __DIR__ . '/../configuration/base.php';

class Journal
{
    public static function ajouter(
        ?int $idUtilisateur,
        string $roleUtilisateur,
        string $action,
        string $typeObjet,
        ?int $idObjet,
        string $details,
        string $niveau = 'info'
    ): bool {
        $stmt = Base::connexion()->prepare(
            'INSERT INTO journal_actions
             (id_utilisateur, role_utilisateur, action, type_objet, id_objet, details, niveau)
             VALUES
             (:id_utilisateur, :role_utilisateur, :action, :type_objet, :id_objet, :details, :niveau)'
        );

        return $stmt->execute([
            'id_utilisateur' => $idUtilisateur,
            'role_utilisateur' => $roleUtilisateur,
            'action' => $action,
            'type_objet' => $typeObjet,
            'id_objet' => $idObjet,
            'details' => $details,
            'niveau' => $niveau,
        ]);
    }

    public static function enregistrerDepuisSession(string $action, string $typeObjet, ?int $idObjet, string $details, string $niveau = 'info'): bool
    {
        $utilisateur = $_SESSION['utilisateur'] ?? null;

        return self::ajouter(
            $utilisateur['id'] ?? null,
            $utilisateur['role'] ?? 'invite',
            $action,
            $typeObjet,
            $idObjet,
            $details,
            $niveau
        );
    }

    private static function baseFiltre(string $roleConnecte = 'administrateur', array $filtres = []): array
    {
        $sql = ' FROM journal_actions j LEFT JOIN utilisateurs u ON u.id = j.id_utilisateur WHERE 1=1';
        $params = [];

        if ($roleConnecte === 'comptable') {
            $sql .= " AND j.action IN (
                'CONNEXION','DECONNEXION','CREATION_FICHE','MODIFICATION_FICHE','TRANSMISSION_FICHE',
                'VALIDATION_FICHE','REFUS_FICHE','SUPPRESSION_FICHE','AJOUT_HORS_FORFAIT','SUPPRESSION_HORS_FORFAIT',
                'CREATION_UTILISATEUR','MODIFICATION_UTILISATEUR','SUPPRESSION_UTILISATEUR'
            )";
        }

        $action = trim((string) ($filtres['action'] ?? ''));
        if ($action !== '') {
            $sql .= ' AND j.action = :action';
            $params['action'] = $action;
        }

        $roleFiltre = trim((string) ($filtres['role_utilisateur'] ?? ''));
        if ($roleFiltre !== '') {
            $sql .= ' AND j.role_utilisateur = :role_utilisateur';
            $params['role_utilisateur'] = $roleFiltre;
        }

        $utilisateur = trim((string) ($filtres['utilisateur'] ?? ''));
        if ($utilisateur !== '') {
            $sql .= ' AND (u.nom LIKE :utilisateur OR u.prenom LIKE :utilisateur OR u.email LIKE :utilisateur)';
            $params['utilisateur'] = '%' . $utilisateur . '%';
        }

        $dateDebut = trim((string) ($filtres['date_debut'] ?? ''));
        if ($dateDebut !== '') {
            $sql .= ' AND DATE(j.date_action) >= :date_debut';
            $params['date_debut'] = $dateDebut;
        }

        $dateFin = trim((string) ($filtres['date_fin'] ?? ''));
        if ($dateFin !== '') {
            $sql .= ' AND DATE(j.date_action) <= :date_fin';
            $params['date_fin'] = $dateFin;
        }

        return [$sql, $params];
    }

    public static function pagines(string $roleConnecte = 'administrateur', array $filtres = [], int $page = 1, int $parPage = 20): array
    {
        [$base, $params] = self::baseFiltre($roleConnecte, $filtres);
        $stmtCount = Base::connexion()->prepare('SELECT COUNT(*)' . $base);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();
        $offset = max(0, ($page - 1) * $parPage);
        $sql = 'SELECT j.*, u.nom, u.prenom, u.email' . $base . ' ORDER BY j.date_action DESC, j.id DESC LIMIT :limite OFFSET :decalage';
        $stmt = Base::connexion()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
        $stmt->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['donnees' => $stmt->fetchAll(), 'total' => $total];
    }
}
