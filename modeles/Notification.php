<?php
require_once __DIR__ . '/../configuration/base.php';

class Notification
{
    public static function ajouter(?int $idUtilisateur, string $titre, string $message, string $niveau = 'info'): bool
    {
        $stmt = Base::connexion()->prepare(
            'INSERT INTO notifications (id_utilisateur, titre, message, niveau) VALUES (:id_utilisateur, :titre, :message, :niveau)'
        );
        return $stmt->execute([
            'id_utilisateur' => $idUtilisateur,
            'titre' => $titre,
            'message' => $message,
            'niveau' => $niveau,
        ]);
    }

    public static function pourUtilisateur(int $idUtilisateur, string $role, int $limit = 50): array
    {
        $sql = 'SELECT * FROM notifications WHERE id_utilisateur = :id_utilisateur';
        $params = ['id_utilisateur' => $idUtilisateur];
        if (in_array($role, ['administrateur', 'comptable'], true)) {
            $sql .= ' OR id_utilisateur IS NULL';
        }
        $sql .= ' ORDER BY date_creation DESC, id DESC LIMIT ' . max(1, (int) $limit);
        $stmt = Base::connexion()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function marquerCommeLue(int $id, int $idUtilisateur): bool
    {
        $stmt = Base::connexion()->prepare('UPDATE notifications SET est_lue = 1 WHERE id = :id AND (id_utilisateur = :id_utilisateur OR id_utilisateur IS NULL)');
        $stmt->execute(['id' => $id, 'id_utilisateur' => $idUtilisateur]);
        return $stmt->rowCount() > 0;
    }

    public static function toutMarquerCommeLu(int $idUtilisateur, string $role): bool
    {
        $sql = 'UPDATE notifications SET est_lue = 1 WHERE id_utilisateur = :id_utilisateur';
        if (in_array($role, ['administrateur', 'comptable'], true)) {
            $sql .= ' OR id_utilisateur IS NULL';
        }
        $stmt = Base::connexion()->prepare($sql);
        $stmt->execute(['id_utilisateur' => $idUtilisateur]);
        return $stmt->rowCount() >= 0;
    }
}
