<?php
require_once __DIR__ . '/../configuration/base.php';

class Utilisateur
{
    public static function trouverParEmail(string $email): ?array
    {
        $stmt = Base::connexion()->prepare(
            "SELECT * FROM utilisateurs WHERE email = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);

        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        return $utilisateur ?: null;
    }

    public static function trouverParId(int $id): ?array
    {
        $stmt = Base::connexion()->prepare(
            "SELECT * FROM utilisateurs WHERE id = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);

        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        return $utilisateur ?: null;
    }

    public static function tous(): array
    {
        $stmt = Base::connexion()->query(
            "SELECT id, nom, prenom, email, role, est_approuve, date_creation
             FROM utilisateurs
             ORDER BY id DESC"
        );

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function emailExiste(string $email, int $exclureId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = :email";
        $params = ['email' => $email];

        if ($exclureId > 0) {
            $sql .= " AND id <> :id";
            $params['id'] = $exclureId;
        }

        $stmt = Base::connexion()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function creer(
        string $nom,
        string $prenom,
        string $email,
        string $motDePasse,
        string $role,
        int $estApprouve = 1
    ): bool {
        $stmt = Base::connexion()->prepare(
            "INSERT INTO utilisateurs (nom, prenom, email, mdp, role, est_approuve)
             VALUES (:nom, :prenom, :email, :mdp, :role, :est_approuve)"
        );

        return $stmt->execute([
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'mdp' => password_hash($motDePasse, PASSWORD_DEFAULT),
            'role' => $role,
            'est_approuve' => $estApprouve
        ]);
    }

    public static function modifierParAdmin(
        int $id,
        string $nom,
        string $prenom,
        string $email,
        string $role,
        int $estApprouve,
        ?string $nouveauMotDePasse = null
    ): bool {
        $params = [
            'id' => $id,
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => $email,
            'role' => $role,
            'est_approuve' => $estApprouve
        ];

        $sql = "UPDATE utilisateurs
                SET nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    role = :role,
                    est_approuve = :est_approuve";

        if ($nouveauMotDePasse !== null && trim($nouveauMotDePasse) !== '') {
            $sql .= ", mdp = :mdp";
            $params['mdp'] = password_hash($nouveauMotDePasse, PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = :id";

        $stmt = Base::connexion()->prepare($sql);

        return $stmt->execute($params);
    }

    public static function supprimer(int $id): bool
    {
        $stmt = Base::connexion()->prepare(
            "DELETE FROM utilisateurs WHERE id = :id"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public static function reinitialiserMotDePasse(int $id, string $nouveauMotDePasse): bool
    {
        $stmt = Base::connexion()->prepare(
            "UPDATE utilisateurs
             SET mdp = :mdp
             WHERE id = :id"
        );

        $stmt->execute([
            'id' => $id,
            'mdp' => password_hash($nouveauMotDePasse, PASSWORD_DEFAULT)
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function approuver(int $id, int $etat): bool
    {
        $stmt = Base::connexion()->prepare(
            "UPDATE utilisateurs
             SET est_approuve = :etat
             WHERE id = :id"
        );
        $stmt->execute([
            'id' => $id,
            'etat' => $etat
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function verifierConnexion(string $email, string $motDePasse): ?array
    {
        $utilisateur = self::trouverParEmail($email);

        if (!$utilisateur) {
            return null;
        }

        if ((int) $utilisateur['est_approuve'] !== 1) {
            return null;
        }

        if (!password_verify($motDePasse, $utilisateur['mdp'])) {
            return null;
        }

        return $utilisateur;
    }

    public static function statistiques(): array
    {
        $pdo = Base::connexion();

        return [
            'total' => (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
            'visiteurs' => (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'visiteur'")->fetchColumn(),
            'comptables' => (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'comptable'")->fetchColumn(),
            'administrateurs' => (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE role = 'administrateur'")->fetchColumn(),
            'approuves' => (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE est_approuve = 1")->fetchColumn(),
            'bloques' => (int) $pdo->query("SELECT COUNT(*) FROM utilisateurs WHERE est_approuve = 0")->fetchColumn(),
        ];
    }
}
