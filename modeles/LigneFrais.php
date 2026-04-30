<?php
require_once __DIR__ . '/../configuration/base.php';
require_once __DIR__ . '/Fiche.php';

class LigneFrais
{
    public static function toutesParFiche(int $idFiche): array
    {
        $stmt = Base::connexion()->prepare(
            'SELECT * FROM hors_forfaits WHERE id_fiche = :id_fiche ORDER BY date_ajout DESC, id DESC'
        );
        $stmt->execute(['id_fiche' => $idFiche]);
        return $stmt->fetchAll();
    }

    public static function trouverParId(int $id, int $idFiche): ?array
    {
        $stmt = Base::connexion()->prepare(
            'SELECT * FROM hors_forfaits WHERE id = :id AND id_fiche = :id_fiche LIMIT 1'
        );
        $stmt->execute([
            'id' => $id,
            'id_fiche' => $idFiche,
        ]);

        $ligne = $stmt->fetch();
        return $ligne ?: null;
    }

    public static function tauxTvaAutorise(float $taux): bool
    {
        return in_array((int) round($taux), [5, 10, 20], true);
    }

    public static function decomposerMontant(float $montantTtc, float $tauxTva): array
    {
        $montantTtc = round(max(0, $montantTtc), 2);
        $montantHt = round($montantTtc / (1 + ($tauxTva / 100)), 2);
        $montantTva = round($montantTtc - $montantHt, 2);

        return [
            'montant_ttc' => $montantTtc,
            'montant_ht' => $montantHt,
            'montant_tva' => $montantTva,
        ];
    }

    public static function ajouter(int $idFiche, string $libelle, float $montantTtc, float $tauxTva): bool
    {
        $montants = self::decomposerMontant($montantTtc, $tauxTva);

        $stmt = Base::connexion()->prepare(
            'INSERT INTO hors_forfaits
             (id_fiche, type_consommation, date, libelle, montant, commentaire, taux_tva, montant_ht, montant_tva, montant_ttc)
             VALUES
             (:id_fiche, :type_consommation, CURDATE(), :libelle, :montant, :commentaire, :taux_tva, :montant_ht, :montant_tva, :montant_ttc)'
        );

        $ok = $stmt->execute([
            'id_fiche' => $idFiche,
            'type_consommation' => 'hors_forfait',
            'libelle' => $libelle,
            'montant' => $montants['montant_ttc'],
            'commentaire' => $libelle,
            'taux_tva' => $tauxTva,
            'montant_ht' => $montants['montant_ht'],
            'montant_tva' => $montants['montant_tva'],
            'montant_ttc' => $montants['montant_ttc']
        ]);

        if ($ok) {
            Fiche::recalculerMontantTotal($idFiche);
        }

        return $ok;
    }

    public static function modifier(int $id, int $idFiche, string $libelle, float $montantTtc, float $tauxTva): bool
    {
        $montants = self::decomposerMontant($montantTtc, $tauxTva);

        $stmt = Base::connexion()->prepare(
            'UPDATE hors_forfaits
             SET libelle = :libelle,
                 montant = :montant,
                 commentaire = :commentaire,
                 taux_tva = :taux_tva,
                 montant_ht = :montant_ht,
                 montant_tva = :montant_tva,
                 montant_ttc = :montant_ttc,
                 date = CURDATE()
             WHERE id = :id
               AND id_fiche = :id_fiche'
        );
        $stmt->execute([
            'id' => $id,
            'id_fiche' => $idFiche,
            'libelle' => $libelle,
            'montant' => $montants['montant_ttc'],
            'commentaire' => $libelle,
            'taux_tva' => $tauxTva,
            'montant_ht' => $montants['montant_ht'],
            'montant_tva' => $montants['montant_tva'],
            'montant_ttc' => $montants['montant_ttc'],
        ]);

        $ok = $stmt->rowCount() > 0;
        if ($ok) {
            Fiche::recalculerMontantTotal($idFiche);
        }

        return $ok;
    }

    public static function supprimer(int $id, int $idFiche): bool
    {
        $stmt = Base::connexion()->prepare(
            'DELETE FROM hors_forfaits WHERE id = :id AND id_fiche = :id_fiche'
        );
        $stmt->execute([
            'id' => $id,
            'id_fiche' => $idFiche
        ]);

        $ok = $stmt->rowCount() > 0;
        if ($ok) {
            Fiche::recalculerMontantTotal($idFiche);
        }

        return $ok;
    }

    public static function totalTvaParFiche(int $idFiche): float
    {
        $stmt = Base::connexion()->prepare(
            'SELECT COALESCE(SUM(montant_tva), 0) FROM hors_forfaits WHERE id_fiche = :id_fiche'
        );
        $stmt->execute(['id_fiche' => $idFiche]);
        return (float) $stmt->fetchColumn();
    }

    public static function totalTtcParFiche(int $idFiche): float
    {
        $stmt = Base::connexion()->prepare(
            'SELECT COALESCE(SUM(montant_ttc), 0) FROM hors_forfaits WHERE id_fiche = :id_fiche'
        );
        $stmt->execute(['id_fiche' => $idFiche]);
        return (float) $stmt->fetchColumn();
    }
}
