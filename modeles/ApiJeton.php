<?php
require_once __DIR__ . '/../configuration/base.php';

class ApiJeton
{
    public static function lister(int $page = 1, int $parPage = 20): array
    {
        $offset = max(0, ($page - 1) * $parPage);
        $sql = 'SELECT aj.*, u.nom, u.prenom, u.email, u.role,
                       CASE WHEN aj.date_expiration < NOW() THEN 1 ELSE 0 END AS est_expire
                FROM api_jetons aj
                INNER JOIN utilisateurs u ON u.id = aj.id_utilisateur
                ORDER BY aj.date_creation DESC, aj.id DESC
                LIMIT :limite OFFSET :decalage';
        $stmt = Base::connexion()->prepare($sql);
        $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
        $stmt->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function compter(): int
    {
        return (int) Base::connexion()->query('SELECT COUNT(*) FROM api_jetons')->fetchColumn();
    }

    public static function statistiques(): array
    {
        $sql = 'SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN date_expiration >= NOW() THEN 1 ELSE 0 END) AS actifs,
                    SUM(CASE WHEN date_expiration < NOW() THEN 1 ELSE 0 END) AS expires
                FROM api_jetons';
        $stmt = Base::connexion()->query($sql);
        $stats = $stmt->fetch() ?: [];
        return [
            'total' => (int) ($stats['total'] ?? 0),
            'actifs' => (int) ($stats['actifs'] ?? 0),
            'expires' => (int) ($stats['expires'] ?? 0),
        ];
    }

    public static function revoquer(int $id): bool
    {
        $stmt = Base::connexion()->prepare('DELETE FROM api_jetons WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }

    public static function purgerExpires(): int
    {
        $stmt = Base::connexion()->prepare('DELETE FROM api_jetons WHERE date_expiration < NOW()');
        $stmt->execute();
        return $stmt->rowCount();
    }
}
