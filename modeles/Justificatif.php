<?php
require_once __DIR__ . '/../configuration/base.php';

class Justificatif
{
    public static function tousParFiche(int $idFiche): array
    {
        $stmt = Base::connexion()->prepare('SELECT * FROM justificatifs WHERE id_fiche = :id_fiche ORDER BY date_envoi DESC');
        $stmt->execute(['id_fiche' => $idFiche]);
        return $stmt->fetchAll();
    }

    public static function trouverParId(int $id): ?array
    {
        $stmt = Base::connexion()->prepare('SELECT * FROM justificatifs WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $justificatif = $stmt->fetch();
        return $justificatif ?: null;
    }

    public static function ajouter(
        int $idFiche,
        string $nomReel,
        string $nomServeur,
        string $extension,
        float $ttc5,
        float $ttc10,
        float $ttc20
    ): bool {
        $contientTva = ($ttc5 + $ttc10 + $ttc20) > 0 ? 1 : 0;

        $stmt = Base::connexion()->prepare(
            'INSERT INTO justificatifs (
                id_fiche, nom_reel, nom_serveur, extension, contient_tva,
                montant_ttc_5, montant_ttc_10, montant_ttc_20
             ) VALUES (
                :id_fiche, :nom_reel, :nom_serveur, :extension, :contient_tva,
                :montant_ttc_5, :montant_ttc_10, :montant_ttc_20
             )'
        );

        return $stmt->execute([
            'id_fiche' => $idFiche,
            'nom_reel' => $nomReel,
            'nom_serveur' => $nomServeur,
            'extension' => $extension,
            'contient_tva' => $contientTva,
            'montant_ttc_5' => $ttc5,
            'montant_ttc_10' => $ttc10,
            'montant_ttc_20' => $ttc20
        ]);
    }

    public static function supprimer(int $id, int $idFiche): bool
    {
        $stmt = Base::connexion()->prepare('DELETE FROM justificatifs WHERE id = :id AND id_fiche = :id_fiche');
        $stmt->execute([
            'id' => $id,
            'id_fiche' => $idFiche
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function compterParFiche(int $idFiche): int
    {
        $stmt = Base::connexion()->prepare('SELECT COUNT(*) FROM justificatifs WHERE id_fiche = :id_fiche');
        $stmt->execute(['id_fiche' => $idFiche]);
        return (int) $stmt->fetchColumn();
    }

    public static function existeAvecTva(int $idFiche): bool
    {
        $stmt = Base::connexion()->prepare(
            'SELECT COUNT(*) FROM justificatifs
             WHERE id_fiche = :id_fiche
               AND (montant_ttc_5 > 0 OR montant_ttc_10 > 0 OR montant_ttc_20 > 0)'
        );
        $stmt->execute(['id_fiche' => $idFiche]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public static function totauxTVAParFiche(int $idFiche): array
    {
        $stmt = Base::connexion()->prepare(
            'SELECT
                COALESCE(SUM(montant_ttc_5), 0) AS total_ttc_5,
                COALESCE(SUM(montant_ttc_10), 0) AS total_ttc_10,
                COALESCE(SUM(montant_ttc_20), 0) AS total_ttc_20
             FROM justificatifs
             WHERE id_fiche = :id_fiche'
        );
        $stmt->execute(['id_fiche' => $idFiche]);
        $ligne = $stmt->fetch();

        $ttc5 = (float) ($ligne['total_ttc_5'] ?? 0);
        $ttc10 = (float) ($ligne['total_ttc_10'] ?? 0);
        $ttc20 = (float) ($ligne['total_ttc_20'] ?? 0);

        $ht5 = round($ttc5 / 1.05, 2);
        $ht10 = round($ttc10 / 1.10, 2);
        $ht20 = round($ttc20 / 1.20, 2);

        $tva5 = round($ttc5 - $ht5, 2);
        $tva10 = round($ttc10 - $ht10, 2);
        $tva20 = round($ttc20 - $ht20, 2);

        return [
            'ttc_5' => $ttc5,
            'ttc_10' => $ttc10,
            'ttc_20' => $ttc20,
            'ht_5' => $ht5,
            'ht_10' => $ht10,
            'ht_20' => $ht20,
            'tva_5' => $tva5,
            'tva_10' => $tva10,
            'tva_20' => $tva20,
            'ttc_total' => round($ttc5 + $ttc10 + $ttc20, 2),
            'ht_total' => round($ht5 + $ht10 + $ht20, 2),
            'tva_total' => round($tva5 + $tva10 + $tva20, 2),
        ];
    }
}
