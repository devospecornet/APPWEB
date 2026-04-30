<?php
require_once __DIR__ . '/../configuration/base.php';

class Fiche
{
    public static function toutesParUtilisateur(int $idUtilisateur, ?string $mois = null, ?string $statut = null): array
    {
        $sql = 'SELECT * FROM fiches_frais WHERE id_utilisateur = :id_utilisateur';
        $params = ['id_utilisateur' => $idUtilisateur];

        if ($mois !== null && $mois !== '') {
            $sql .= ' AND mois = :mois';
            $params['mois'] = $mois;
        }

        if ($statut !== null && $statut !== '') {
            $sql .= ' AND statut = :statut';
            $params['statut'] = $statut;
        }

        $sql .= ' ORDER BY date_modification DESC, id DESC';

        $stmt = Base::connexion()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function toutesAvecFiltres(?string $statut = null, ?string $mois = null): array
    {
        $sql = "SELECT f.*, u.nom, u.prenom, u.email, u.role
                FROM fiches_frais f
                INNER JOIN utilisateurs u ON u.id = f.id_utilisateur
                WHERE 1=1";
        $params = [];

        if ($statut !== null && $statut !== '') {
            $sql .= ' AND f.statut = :statut';
            $params['statut'] = $statut;
        }

        if ($mois !== null && $mois !== '') {
            $sql .= ' AND f.mois = :mois';
            $params['mois'] = $mois;
        }

        $sql .= ' ORDER BY f.date_modification DESC, f.id DESC';

        $stmt = Base::connexion()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    public static function toutesTransmises(): array
    {
        $sql = "SELECT f.*, u.nom, u.prenom, u.email
                FROM fiches_frais f
                INNER JOIN utilisateurs u ON u.id = f.id_utilisateur
                WHERE f.statut = 'transmise'
                ORDER BY f.date_modification DESC, f.id DESC";

        return Base::connexion()->query($sql)->fetchAll();
    }

    public static function trouverParId(int $id): ?array
    {
        $stmt = Base::connexion()->prepare(
            "SELECT f.*, u.nom, u.prenom, u.email, u.role
             FROM fiches_frais f
             INNER JOIN utilisateurs u ON u.id = f.id_utilisateur
             WHERE f.id = :id"
        );
        $stmt->execute(['id' => $id]);

        $fiche = $stmt->fetch();

        return $fiche ?: null;
    }

    public static function trouverParIdEtUtilisateur(int $id, int $idUtilisateur): ?array
    {
        $stmt = Base::connexion()->prepare(
            "SELECT * FROM fiches_frais
             WHERE id = :id
               AND id_utilisateur = :id_utilisateur"
        );
        $stmt->execute([
            'id' => $id,
            'id_utilisateur' => $idUtilisateur
        ]);

        $fiche = $stmt->fetch();

        return $fiche ?: null;
    }

    public static function ficheDuMoisExiste(int $idUtilisateur, string $mois, int $exclureId = 0): bool
    {
        $sql = "SELECT COUNT(*)
                FROM fiches_frais
                WHERE id_utilisateur = :id_utilisateur
                  AND mois = :mois";
        $params = [
            'id_utilisateur' => $idUtilisateur,
            'mois' => $mois
        ];

        if ($exclureId > 0) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exclureId;
        }

        $stmt = Base::connexion()->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    public static function genererNumero(): string
    {
        $maxId = (int) Base::connexion()
            ->query('SELECT COALESCE(MAX(id), 0) FROM fiches_frais')
            ->fetchColumn();

        return 'FF-' . str_pad((string) ($maxId + 1), 6, '0', STR_PAD_LEFT);
    }

    public static function tauxTvaAutorise(float $taux): bool
    {
        return in_array((int) round($taux), [0, 5, 10, 20], true);
    }

    public static function normaliserMontant(float $montant): float
    {
        return round(max(0, $montant), 2);
    }

    public static function calculerMontantTvaDepuisTtc(float $montantTtc, float $tauxTva): float
    {
        $montantTtc = self::normaliserMontant($montantTtc);

        if ($montantTtc <= 0 || $tauxTva <= 0) {
            return 0.0;
        }

        $montantHt = round($montantTtc / (1 + ($tauxTva / 100)), 2);

        return round($montantTtc - $montantHt, 2);
    }

    public static function totalForfaitDepuisChamps(array $fiche): float
    {
        return round(
            (float) ($fiche['frais_essence'] ?? 0) +
            (float) ($fiche['frais_petit_dejeuner'] ?? 0) +
            (float) ($fiche['frais_repas_midi'] ?? 0) +
            (float) ($fiche['frais_repas_soir'] ?? 0) +
            (float) ($fiche['frais_hotel'] ?? 0),
            2
        );
    }

    public static function totalTvaForfaitDepuisChamps(array $fiche): float
    {
        $montants = [
            'essence' => (float) ($fiche['frais_essence'] ?? 0),
            'petit_dejeuner' => (float) ($fiche['frais_petit_dejeuner'] ?? 0),
            'repas_midi' => (float) ($fiche['frais_repas_midi'] ?? 0),
            'repas_soir' => (float) ($fiche['frais_repas_soir'] ?? 0),
            'hotel' => (float) ($fiche['frais_hotel'] ?? 0),
        ];

        $taux = [
            'essence' => (float) ($fiche['taux_tva_essence'] ?? 0),
            'petit_dejeuner' => (float) ($fiche['taux_tva_petit_dejeuner'] ?? 0),
            'repas_midi' => (float) ($fiche['taux_tva_repas_midi'] ?? 0),
            'repas_soir' => (float) ($fiche['taux_tva_repas_soir'] ?? 0),
            'hotel' => (float) ($fiche['taux_tva_hotel'] ?? 0),
        ];

        $total = 0.0;
        foreach ($montants as $cle => $montant) {
            $total += self::calculerMontantTvaDepuisTtc($montant, $taux[$cle] ?? 0);
        }

        return round($total, 2);
    }

    public static function totalHorsForfait(int $idFiche): float
    {
        $stmt = Base::connexion()->prepare(
            "SELECT COALESCE(SUM(
                CASE
                    WHEN montant_ttc > 0 THEN montant_ttc
                    ELSE montant
                END
            ), 0)
            FROM hors_forfaits
            WHERE id_fiche = :id_fiche"
        );
        $stmt->execute(['id_fiche' => $idFiche]);

        return (float) $stmt->fetchColumn();
    }

    public static function totalTvaHorsForfait(int $idFiche): float
    {
        $stmt = Base::connexion()->prepare(
            'SELECT COALESCE(SUM(montant_tva), 0) FROM hors_forfaits WHERE id_fiche = :id_fiche'
        );
        $stmt->execute(['id_fiche' => $idFiche]);

        return (float) $stmt->fetchColumn();
    }

    public static function totalTvaGlobale(array $fiche, int $idFiche): float
    {
        return round(self::totalTvaForfaitDepuisChamps($fiche) + self::totalTvaHorsForfait($idFiche), 2);
    }

    public static function recalculerMontantTotal(int $idFiche): void
    {
        $fiche = self::trouverParId($idFiche);

        if (!$fiche) {
            return;
        }

        $totalForfait = self::totalForfaitDepuisChamps($fiche);
        $totalHorsForfait = self::totalHorsForfait($idFiche);
        $total = round($totalForfait + $totalHorsForfait, 2);

        $stmt = Base::connexion()->prepare(
            "UPDATE fiches_frais
             SET montant_total = :montant_total
             WHERE id = :id"
        );
        $stmt->execute([
            'montant_total' => $total,
            'id' => $idFiche
        ]);
    }

    public static function ajouter(
        int $idUtilisateur,
        string $mois,
        float $fraisEssence,
        float $fraisPetitDejeuner,
        float $fraisRepasMidi,
        float $fraisRepasSoir,
        float $fraisHotel,
        float $tauxTvaEssence = 0,
        float $tauxTvaPetitDejeuner = 0,
        float $tauxTvaRepasMidi = 0,
        float $tauxTvaRepasSoir = 0,
        float $tauxTvaHotel = 0
    ): bool {
        $montantTotal = round($fraisEssence + $fraisPetitDejeuner + $fraisRepasMidi + $fraisRepasSoir + $fraisHotel, 2);

        $stmt = Base::connexion()->prepare(
            "INSERT INTO fiches_frais (
                numero_fiche,
                id_utilisateur,
                mois,
                frais_essence,
                frais_petit_dejeuner,
                frais_repas_midi,
                frais_repas_soir,
                frais_hotel,
                taux_tva_essence,
                taux_tva_petit_dejeuner,
                taux_tva_repas_midi,
                taux_tva_repas_soir,
                taux_tva_hotel,
                montant_total,
                statut,
                commentaire_visiteur
            ) VALUES (
                :numero_fiche,
                :id_utilisateur,
                :mois,
                :frais_essence,
                :frais_petit_dejeuner,
                :frais_repas_midi,
                :frais_repas_soir,
                :frais_hotel,
                :taux_tva_essence,
                :taux_tva_petit_dejeuner,
                :taux_tva_repas_midi,
                :taux_tva_repas_soir,
                :taux_tva_hotel,
                :montant_total,
                :statut,
                :commentaire_visiteur
            )"
        );

        return $stmt->execute([
            'numero_fiche' => self::genererNumero(),
            'id_utilisateur' => $idUtilisateur,
            'mois' => $mois,
            'frais_essence' => self::normaliserMontant($fraisEssence),
            'frais_petit_dejeuner' => self::normaliserMontant($fraisPetitDejeuner),
            'frais_repas_midi' => self::normaliserMontant($fraisRepasMidi),
            'frais_repas_soir' => self::normaliserMontant($fraisRepasSoir),
            'frais_hotel' => self::normaliserMontant($fraisHotel),
            'taux_tva_essence' => $tauxTvaEssence,
            'taux_tva_petit_dejeuner' => $tauxTvaPetitDejeuner,
            'taux_tva_repas_midi' => $tauxTvaRepasMidi,
            'taux_tva_repas_soir' => $tauxTvaRepasSoir,
            'taux_tva_hotel' => $tauxTvaHotel,
            'montant_total' => $montantTotal,
            'statut' => 'saisie',
            'commentaire_visiteur' => ''
        ]);
    }

    public static function modifier(int $id, int $idUtilisateur, array $donnees): bool
    {
        $stmt = Base::connexion()->prepare(
            'UPDATE fiches_frais
             SET mois = :mois,
                 frais_essence = :frais_essence,
                 frais_petit_dejeuner = :frais_petit_dejeuner,
                 frais_repas_midi = :frais_repas_midi,
                 frais_repas_soir = :frais_repas_soir,
                 frais_hotel = :frais_hotel,
                 taux_tva_essence = :taux_tva_essence,
                 taux_tva_petit_dejeuner = :taux_tva_petit_dejeuner,
                 taux_tva_repas_midi = :taux_tva_repas_midi,
                 taux_tva_repas_soir = :taux_tva_repas_soir,
                 taux_tva_hotel = :taux_tva_hotel
             WHERE id = :id
               AND id_utilisateur = :id_utilisateur
               AND statut IN (\'saisie\', \'refusee\')'
        );

        $stmt->execute([
            'id' => $id,
            'id_utilisateur' => $idUtilisateur,
            'mois' => $donnees['mois'],
            'frais_essence' => self::normaliserMontant((float) ($donnees['frais_essence'] ?? 0)),
            'frais_petit_dejeuner' => self::normaliserMontant((float) ($donnees['frais_petit_dejeuner'] ?? 0)),
            'frais_repas_midi' => self::normaliserMontant((float) ($donnees['frais_repas_midi'] ?? 0)),
            'frais_repas_soir' => self::normaliserMontant((float) ($donnees['frais_repas_soir'] ?? 0)),
            'frais_hotel' => self::normaliserMontant((float) ($donnees['frais_hotel'] ?? 0)),
            'taux_tva_essence' => (float) ($donnees['taux_tva_essence'] ?? 0),
            'taux_tva_petit_dejeuner' => (float) ($donnees['taux_tva_petit_dejeuner'] ?? 0),
            'taux_tva_repas_midi' => (float) ($donnees['taux_tva_repas_midi'] ?? 0),
            'taux_tva_repas_soir' => (float) ($donnees['taux_tva_repas_soir'] ?? 0),
            'taux_tva_hotel' => (float) ($donnees['taux_tva_hotel'] ?? 0),
        ]);

        $ok = $stmt->rowCount() > 0;

        if ($ok) {
            self::recalculerMontantTotal($id);
        }

        return $ok;
    }

    public static function supprimer(int $id, int $idUtilisateur): bool
    {
        $stmt = Base::connexion()->prepare(
            "DELETE FROM fiches_frais
             WHERE id = :id
               AND id_utilisateur = :id_utilisateur
               AND statut IN ('saisie', 'refusee')"
        );
        $stmt->execute([
            'id' => $id,
            'id_utilisateur' => $idUtilisateur
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function supprimerParAdminOuComptable(int $id): bool
    {
        $stmt = Base::connexion()->prepare(
            "DELETE FROM fiches_frais
             WHERE id = :id
               AND statut = 'transmise'"
        );
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    public static function transmettre(int $id, int $idUtilisateur): bool
    {
        $stmt = Base::connexion()->prepare(
            "UPDATE fiches_frais
             SET statut = 'transmise'
             WHERE id = :id
               AND id_utilisateur = :id_utilisateur
               AND statut IN ('saisie', 'refusee')"
        );
        $stmt->execute([
            'id' => $id,
            'id_utilisateur' => $idUtilisateur
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function valider(int $id, string $commentaireComptable = ''): bool
    {
        $stmt = Base::connexion()->prepare(
            "UPDATE fiches_frais
             SET statut = 'validee',
                 commentaire_comptable = :commentaire_comptable
             WHERE id = :id
               AND statut = 'transmise'"
        );
        $stmt->execute([
            'id' => $id,
            'commentaire_comptable' => $commentaireComptable
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function refuser(int $id, string $commentaireComptable): bool
    {
        $stmt = Base::connexion()->prepare(
            "UPDATE fiches_frais
             SET statut = 'refusee',
                 commentaire_comptable = :commentaire_comptable
             WHERE id = :id
               AND statut = 'transmise'"
        );
        $stmt->execute([
            'id' => $id,
            'commentaire_comptable' => $commentaireComptable
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function horsForfaits(int $idFiche): array
    {
        $stmt = Base::connexion()->prepare(
            "SELECT id, id_fiche, type_consommation, date, libelle, montant, commentaire,
                    taux_tva, montant_ht, montant_tva, montant_ttc, date_ajout
             FROM hors_forfaits
             WHERE id_fiche = :id_fiche
             ORDER BY date DESC, id DESC"
        );
        $stmt->execute(['id_fiche' => $idFiche]);

        return $stmt->fetchAll();
    }

    public static function ajouterHorsForfait(
        int $idFiche,
        string $typeConsommation,
        string $date,
        string $libelle,
        float $montant,
        string $commentaire
    ): bool {
        $tauxTva = 20.00;
        $montantTtc = round($montant, 2);
        $montantHt = round($montantTtc / (1 + ($tauxTva / 100)), 2);
        $montantTva = round($montantTtc - $montantHt, 2);

        $stmt = Base::connexion()->prepare(
            "INSERT INTO hors_forfaits (
                id_fiche,
                type_consommation,
                date,
                libelle,
                montant,
                commentaire,
                taux_tva,
                montant_ht,
                montant_tva,
                montant_ttc
            ) VALUES (
                :id_fiche,
                :type_consommation,
                :date,
                :libelle,
                :montant,
                :commentaire,
                :taux_tva,
                :montant_ht,
                :montant_tva,
                :montant_ttc
            )"
        );

        $ok = $stmt->execute([
            'id_fiche' => $idFiche,
            'type_consommation' => $typeConsommation,
            'date' => $date,
            'libelle' => $libelle,
            'montant' => $montantTtc,
            'commentaire' => $commentaire,
            'taux_tva' => $tauxTva,
            'montant_ht' => $montantHt,
            'montant_tva' => $montantTva,
            'montant_ttc' => $montantTtc
        ]);

        if ($ok) {
            self::recalculerMontantTotal($idFiche);
        }

        return $ok;
    }

    public static function statistiquesGlobales(): array
    {
        $pdo = Base::connexion();

        $stats = [];
        $stats['total_fiches'] = (int) $pdo->query('SELECT COUNT(*) FROM fiches_frais')->fetchColumn();
        $stats['total_montant'] = (float) $pdo->query('SELECT COALESCE(SUM(montant_total), 0) FROM fiches_frais')->fetchColumn();
        $stats['total_transmises'] = (int) $pdo->query("SELECT COUNT(*) FROM fiches_frais WHERE statut = 'transmise'")->fetchColumn();
        $stats['total_validees'] = (int) $pdo->query("SELECT COUNT(*) FROM fiches_frais WHERE statut = 'validee'")->fetchColumn();
        $stats['total_refusees'] = (int) $pdo->query("SELECT COUNT(*) FROM fiches_frais WHERE statut = 'refusee'")->fetchColumn();
        $stats['total_saisie'] = (int) $pdo->query("SELECT COUNT(*) FROM fiches_frais WHERE statut = 'saisie'")->fetchColumn();
        $stats['montant_moyen'] = $stats['total_fiches'] > 0
            ? round($stats['total_montant'] / $stats['total_fiches'], 2)
            : 0.0;

        $stmt = $pdo->query(
            "SELECT mois, COUNT(*) AS nb_fiches, COALESCE(SUM(montant_total), 0) AS montant_total
             FROM fiches_frais
             GROUP BY mois
             ORDER BY mois DESC
             LIMIT 6"
        );
        $stats['par_mois'] = $stmt->fetchAll();

        return $stats;
    }

    public static function toutesNonValideesAdmin(): array
    {
        $sql = "SELECT f.*, u.nom, u.prenom, u.email, u.role
            FROM fiches_frais f
            INNER JOIN utilisateurs u ON u.id = f.id_utilisateur
            WHERE f.statut <> 'validee'
            ORDER BY f.date_modification DESC, f.id DESC";

        return Base::connexion()->query($sql)->fetchAll();
    }

    public static function validerParAdmin(int $id, string $commentaire = ''): bool
    {
        $stmt = Base::connexion()->prepare(
            "UPDATE fiches_frais
         SET statut = 'validee',
             commentaire_comptable = :commentaire
         WHERE id = :id
           AND statut <> 'validee'"
        );
        $stmt->execute([
            'id' => $id,
            'commentaire' => $commentaire
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function refuserParAdmin(int $id, string $commentaire): bool
    {
        $stmt = Base::connexion()->prepare(
            "UPDATE fiches_frais
         SET statut = 'refusee',
             commentaire_comptable = :commentaire
         WHERE id = :id
           AND statut <> 'validee'"
        );
        $stmt->execute([
            'id' => $id,
            'commentaire' => $commentaire
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function supprimerParAdmin(int $id): bool
    {
        $stmt = Base::connexion()->prepare(
            "DELETE FROM fiches_frais
         WHERE id = :id
           AND statut <> 'validee'"
        );
        $stmt->execute([
            'id' => $id
        ]);

        return $stmt->rowCount() > 0;
    }

    public static function toutesAvecFiltresPagines(?string $statut = null, ?string $mois = null, ?string $recherche = null, int $page = 1, int $parPage = 10): array
    {
        $baseSql = " FROM fiches_frais f INNER JOIN utilisateurs u ON u.id = f.id_utilisateur WHERE 1=1";
        $params = [];

        if ($statut !== null && $statut !== '') {
            $baseSql .= ' AND f.statut = :statut';
            $params['statut'] = $statut;
        }
        if ($mois !== null && $mois !== '') {
            $baseSql .= ' AND f.mois = :mois';
            $params['mois'] = $mois;
        }
        if ($recherche !== null && $recherche !== '') {
            $baseSql .= ' AND (u.nom LIKE :recherche OR u.prenom LIKE :recherche OR u.email LIKE :recherche OR f.numero_fiche LIKE :recherche)';
            $params['recherche'] = '%' . $recherche . '%';
        }

        $stmtCount = Base::connexion()->prepare('SELECT COUNT(*)' . $baseSql);
        $stmtCount->execute($params);
        $total = (int) $stmtCount->fetchColumn();
        $offset = max(0, ($page - 1) * $parPage);

        $sql = 'SELECT f.*, u.nom, u.prenom, u.email, u.role' . $baseSql . ' ORDER BY f.date_modification DESC, f.id DESC LIMIT :limite OFFSET :decalage';
        $stmt = Base::connexion()->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue(':' . $k, $v);
        }
        $stmt->bindValue(':limite', $parPage, PDO::PARAM_INT);
        $stmt->bindValue(':decalage', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return ['donnees' => $stmt->fetchAll(), 'total' => $total];
    }

    public static function statistiquesMensuelles(?string $mois = null): array
    {
        $mois = $mois !== null && $mois !== '' ? $mois : date('Y-m');
        $stmt = Base::connexion()->prepare(
            "SELECT
                COUNT(*) AS total_fiches,
                COALESCE(SUM(montant_total),0) AS total_montant,
                SUM(CASE WHEN statut='transmise' THEN 1 ELSE 0 END) AS nb_transmises,
                SUM(CASE WHEN statut='validee' THEN 1 ELSE 0 END) AS nb_validees,
                SUM(CASE WHEN statut='refusee' THEN 1 ELSE 0 END) AS nb_refusees,
                COALESCE(SUM(
                    (CASE WHEN taux_tva_essence > 0 THEN frais_essence - (frais_essence / (1 + taux_tva_essence/100)) ELSE 0 END) +
                    (CASE WHEN taux_tva_petit_dejeuner > 0 THEN frais_petit_dejeuner - (frais_petit_dejeuner / (1 + taux_tva_petit_dejeuner/100)) ELSE 0 END) +
                    (CASE WHEN taux_tva_repas_midi > 0 THEN frais_repas_midi - (frais_repas_midi / (1 + taux_tva_repas_midi/100)) ELSE 0 END) +
                    (CASE WHEN taux_tva_repas_soir > 0 THEN frais_repas_soir - (frais_repas_soir / (1 + taux_tva_repas_soir/100)) ELSE 0 END) +
                    (CASE WHEN taux_tva_hotel > 0 THEN frais_hotel - (frais_hotel / (1 + taux_tva_hotel/100)) ELSE 0 END)
                ),0) AS total_tva_forfait
             FROM fiches_frais WHERE mois = :mois"
        );
        $stmt->execute(['mois' => $mois]);
        $stats = $stmt->fetch() ?: [];

        $stmtTop = Base::connexion()->prepare(
            "SELECT u.nom, u.prenom, u.email, COALESCE(SUM(f.montant_total),0) AS montant_total
             FROM fiches_frais f INNER JOIN utilisateurs u ON u.id = f.id_utilisateur
             WHERE f.mois = :mois AND f.statut IN ('validee','remboursee')
             GROUP BY u.id, u.nom, u.prenom, u.email
             ORDER BY montant_total DESC LIMIT 5"
        );
        $stmtTop->execute(['mois' => $mois]);
        $stats['top_visiteurs'] = $stmtTop->fetchAll();
        $stats['mois'] = $mois;
        return $stats;
    }

}
