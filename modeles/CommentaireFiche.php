<?php
require_once __DIR__ . '/../configuration/base.php';

class CommentaireFiche
{
    public static function ajouter(int $idFiche, ?int $idUtilisateur, string $auteurRole, string $contenu, string $typeCommentaire = 'commentaire'): bool
    {
        $contenu = trim($contenu);
        if ($idFiche <= 0 || $contenu === '') {
            return false;
        }

        $stmt = Base::connexion()->prepare(
            'INSERT INTO fiche_commentaires (id_fiche, id_utilisateur, auteur_role, type_commentaire, contenu)
             VALUES (:id_fiche, :id_utilisateur, :auteur_role, :type_commentaire, :contenu)'
        );

        return $stmt->execute([
            'id_fiche' => $idFiche,
            'id_utilisateur' => $idUtilisateur,
            'auteur_role' => $auteurRole,
            'type_commentaire' => $typeCommentaire,
            'contenu' => $contenu,
        ]);
    }

    public static function toutesParFiche(int $idFiche): array
    {
        $stmt = Base::connexion()->prepare(
            'SELECT fc.*, u.nom, u.prenom, u.email
             FROM fiche_commentaires fc
             LEFT JOIN utilisateurs u ON u.id = fc.id_utilisateur
             WHERE fc.id_fiche = :id_fiche
             ORDER BY fc.date_creation ASC, fc.id ASC'
        );
        $stmt->execute(['id_fiche' => $idFiche]);
        return $stmt->fetchAll();
    }
}
