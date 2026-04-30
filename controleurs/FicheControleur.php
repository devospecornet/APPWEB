<?php
require_once __DIR__ . '/../configuration/config.php';
require_once __DIR__ . '/../configuration/securite.php';
require_once __DIR__ . '/../configuration/base.php';
require_once __DIR__ . '/../modeles/Fiche.php';
require_once __DIR__ . '/../modeles/LigneFrais.php';
require_once __DIR__ . '/../modeles/Justificatif.php';
require_once __DIR__ . '/../modeles/Journal.php';
require_once __DIR__ . '/../modeles/CommentaireFiche.php';
require_once __DIR__ . '/../modeles/Notification.php';

class FicheControleur
{
    private function extraireDonneesFicheDepuisPost(): array
    {
        return [
            'mois' => trim($_POST['mois'] ?? ''),
            'frais_essence' => max(0, (float) ($_POST['frais_essence'] ?? 0)),
            'frais_petit_dejeuner' => max(0, (float) ($_POST['frais_petit_dejeuner'] ?? 0)),
            'frais_repas_midi' => max(0, (float) ($_POST['frais_repas_midi'] ?? 0)),
            'frais_repas_soir' => max(0, (float) ($_POST['frais_repas_soir'] ?? 0)),
            'frais_hotel' => max(0, (float) ($_POST['frais_hotel'] ?? 0)),
            'taux_tva_essence' => (float) ($_POST['taux_tva_essence'] ?? 0),
            'taux_tva_petit_dejeuner' => (float) ($_POST['taux_tva_petit_dejeuner'] ?? 0),
            'taux_tva_repas_midi' => (float) ($_POST['taux_tva_repas_midi'] ?? 0),
            'taux_tva_repas_soir' => (float) ($_POST['taux_tva_repas_soir'] ?? 0),
            'taux_tva_hotel' => (float) ($_POST['taux_tva_hotel'] ?? 0),
        ];
    }

    private function validerDonneesFiche(array $donnees, int $idUtilisateur, int $idFiche = 0): array
    {
        if ($donnees['mois'] === '') {
            return ['ok' => false, 'message' => 'Le mois est obligatoire.'];
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $donnees['mois'])) {
            return ['ok' => false, 'message' => 'Le format du mois doit être YYYY-MM.'];
        }

        if ($donnees['frais_petit_dejeuner'] > 12) {
            return ['ok' => false, 'message' => 'Le petit déjeuner est plafonné à 12 €. Mets le dépassement en hors forfait.'];
        }

        if ($donnees['frais_repas_midi'] > 23) {
            return ['ok' => false, 'message' => 'Le repas du midi est plafonné à 23 €. Mets le dépassement en hors forfait.'];
        }

        if ($donnees['frais_repas_soir'] > 23) {
            return ['ok' => false, 'message' => 'Le repas du soir est plafonné à 23 €. Mets le dépassement en hors forfait.'];
        }

        if ($donnees['frais_hotel'] > 150) {
            return ['ok' => false, 'message' => 'La nuitée hôtel est plafonnée à 150 €. Mets le dépassement en hors forfait.'];
        }

        foreach ([
            'taux_tva_essence',
            'taux_tva_petit_dejeuner',
            'taux_tva_repas_midi',
            'taux_tva_repas_soir',
            'taux_tva_hotel',
        ] as $champTva) {
            if (!Fiche::tauxTvaAutorise((float) $donnees[$champTva])) {
                return ['ok' => false, 'message' => 'Un taux de TVA forfaitaire est invalide.'];
            }
        }

        if (Fiche::ficheDuMoisExiste($idUtilisateur, $donnees['mois'], $idFiche)) {
            return ['ok' => false, 'message' => 'Une fiche existe déjà pour ce mois.'];
        }

        return ['ok' => true, 'message' => ''];
    }

    public function tableauBord(): void
    {
        exigerConnexion(['visiteur', 'comptable', 'administrateur']);

        $utilisateur = $_SESSION['utilisateur'];
        $role = $utilisateur['role'] ?? '';
        $dashboard = [];

        if ($role === 'visiteur') {
            $fiches = Fiche::toutesParUtilisateur((int) $utilisateur['id']);
            $dashboard['fiches_recentes'] = array_slice($fiches, 0, 5);
            $dashboard['nb_fiches'] = count($fiches);
            $dashboard['nb_saisie'] = count(array_filter($fiches, fn(array $fiche): bool => $fiche['statut'] === 'saisie'));
            $dashboard['nb_transmises'] = count(array_filter($fiches, fn(array $fiche): bool => $fiche['statut'] === 'transmise'));
            $dashboard['nb_validees'] = count(array_filter($fiches, fn(array $fiche): bool => $fiche['statut'] === 'validee'));
            $dashboard['montant_total'] = array_sum(array_map(fn(array $fiche): float => (float) $fiche['montant_total'], $fiches));
        } elseif ($role === 'comptable') {
            $fichesTransmises = Fiche::toutesTransmises();
            $dashboard['fiches_recentes'] = array_slice($fichesTransmises, 0, 6);
            $dashboard['nb_a_traiter'] = count($fichesTransmises);
            $dashboard['montant_a_traiter'] = array_sum(array_map(fn(array $fiche): float => (float) $fiche['montant_total'], $fichesTransmises));
        } elseif ($role === 'administrateur') {
            $dashboard['stats_globales'] = Fiche::statistiquesGlobales();
            $dashboard['fiches_recentes'] = array_slice(Fiche::toutesAvecFiltres(), 0, 6);
        }

        require __DIR__ . '/../vues/visiteur/tableau_bord.php';
    }

    public function listeVisiteur(): void
    {
        exigerConnexion(['visiteur']);
        $idUtilisateur = (int) $_SESSION['utilisateur']['id'];

        $mois = trim($_GET['mois'] ?? '');
        $statut = trim($_GET['statut'] ?? '');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { verifierCsrfOuEchouer(); }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_fiche'])) {
            $idFiche = (int) ($_POST['id_fiche'] ?? 0);
            if (Fiche::supprimer($idFiche, $idUtilisateur)) {
                Journal::enregistrerDepuisSession('SUPPRESSION_FICHE', 'fiche_frais', $idFiche, "Suppression d'une fiche visiteur", 'warning');
            }
            header('Location: ' . asset_url('visiteur.php'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transmettre_fiche'])) {
            $idFiche = (int) ($_POST['id_fiche'] ?? 0);
            $commentaireVisiteur = trim($_POST['commentaire_visiteur'] ?? '');

            if (Justificatif::compterParFiche($idFiche) > 0) {
                if ($commentaireVisiteur !== '') {
                    Base::connexion()->prepare('UPDATE fiches_frais SET commentaire_visiteur = :commentaire WHERE id = :id AND id_utilisateur = :id_utilisateur')
                        ->execute(['commentaire' => $commentaireVisiteur, 'id' => $idFiche, 'id_utilisateur' => $idUtilisateur]);
                    CommentaireFiche::ajouter($idFiche, $idUtilisateur, 'visiteur', $commentaireVisiteur, 'envoi');
                }
                if (Fiche::transmettre($idFiche, $idUtilisateur)) {
                    Journal::enregistrerDepuisSession('TRANSMISSION_FICHE', 'fiche_frais', $idFiche, 'Fiche transmise au comptable');
                    Notification::ajouter((int) $idUtilisateur, 'Fiche transmise', 'Votre fiche a été transmise pour validation.', 'info');
                    Notification::ajouter(null, 'Fiche à traiter', 'Une nouvelle fiche transmise attend une validation comptable.', 'info');
                }
            }

            header('Location: ' . asset_url('visiteur.php'));
            exit;
        }

        $fiches = Fiche::toutesParUtilisateur(
            $idUtilisateur,
            $mois !== '' ? $mois : null,
            $statut !== '' ? $statut : null
        );

        require __DIR__ . '/../vues/visiteur/liste_fiches.php';
    }

    public function detailVisiteur(): void
    {
        exigerConnexion(['visiteur']);
        $idUtilisateur = (int) $_SESSION['utilisateur']['id'];
        $idFiche = (int) ($_GET['id'] ?? $_POST['id_fiche'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') { verifierCsrfOuEchouer(); }

        $message = '';
        $typeMessage = 'success';

        if (isset($_SESSION['message_flash']) && is_array($_SESSION['message_flash'])) {
            $message = (string) ($_SESSION['message_flash']['texte'] ?? '');
            $typeMessage = (string) ($_SESSION['message_flash']['type'] ?? 'info');
            unset($_SESSION['message_flash']);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_ou_mettre_a_jour_fiche'])) {
            $donnees = $this->extraireDonneesFicheDepuisPost();
            $validation = $this->validerDonneesFiche($donnees, $idUtilisateur, $idFiche);

            if (!$validation['ok']) {
                $message = $validation['message'];
                $typeMessage = 'danger';
            } else {
                if ($idFiche > 0) {
                    $ficheExistante = Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur);

                    if ($ficheExistante && in_array($ficheExistante['statut'], ['saisie', 'refusee'], true)) {
                        if (Fiche::modifier($idFiche, $idUtilisateur, $donnees)) {
                            Journal::enregistrerDepuisSession('MODIFICATION_FICHE', 'fiche_frais', $idFiche, 'Mise à jour de la fiche visiteur');
                            $message = 'Fiche mise à jour.';
                        }
                    }
                } else {
                    $creationOk = Fiche::ajouter(
                        $idUtilisateur,
                        $donnees['mois'],
                        $donnees['frais_essence'],
                        $donnees['frais_petit_dejeuner'],
                        $donnees['frais_repas_midi'],
                        $donnees['frais_repas_soir'],
                        $donnees['frais_hotel'],
                        $donnees['taux_tva_essence'],
                        $donnees['taux_tva_petit_dejeuner'],
                        $donnees['taux_tva_repas_midi'],
                        $donnees['taux_tva_repas_soir'],
                        $donnees['taux_tva_hotel']
                    );

                    $stmt = Base::connexion()->query('SELECT MAX(id) AS id_max FROM fiches_frais');
                    $idFiche = (int) $stmt->fetch()['id_max'];
                    if ($creationOk && $idFiche > 0) {
                        Journal::enregistrerDepuisSession('CREATION_FICHE', 'fiche_frais', $idFiche, "Création d'une nouvelle fiche visiteur");
                    }

                    header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
                    exit;
                }
            }
        }

        $fiche = $idFiche > 0 ? Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur) : null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_hors_forfait']) && $fiche) {
            $libelle = trim($_POST['libelle_hors_forfait'] ?? '');
            $montant = max(0, (float) ($_POST['montant_hors_forfait'] ?? 0));
            $tauxTva = (float) ($_POST['taux_tva'] ?? 0);

            if (
                in_array($fiche['statut'], ['saisie', 'refusee'], true) &&
                $libelle !== '' &&
                $montant > 0 &&
                LigneFrais::tauxTvaAutorise($tauxTva)
            ) {
if (LigneFrais::ajouter($idFiche, $libelle, $montant, $tauxTva)) {
                    Journal::enregistrerDepuisSession('AJOUT_HORS_FORFAIT', 'hors_forfait', $idFiche, "Ajout d'un hors forfait : " . $libelle);
                }
            }

            header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_hors_forfait']) && $fiche) {
            $idHf = (int) ($_POST['id_hors_forfait'] ?? 0);
if (LigneFrais::supprimer($idHf, $idFiche)) {
                Journal::enregistrerDepuisSession('SUPPRESSION_HORS_FORFAIT', 'hors_forfait', $idHf, "Suppression d'un hors forfait", 'warning');
            }

            header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_justificatif']) && $fiche) {
            if (!isset($_FILES['justificatif']) || $_FILES['justificatif']['error'] !== UPLOAD_ERR_OK) {
                $_SESSION['message_flash'] = ['type' => 'danger', 'texte' => "Impossible d'envoyer le justificatif. Vérifie le fichier sélectionné."];
                header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
                exit;
            }

            $nomReel = $_FILES['justificatif']['name'];
            $extension = strtolower(pathinfo($nomReel, PATHINFO_EXTENSION));

            if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                $_SESSION['message_flash'] = ['type' => 'danger', 'texte' => 'Format non autorisé. Utilise un fichier PDF, JPG, JPEG ou PNG.'];
                header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
                exit;
            }

            if (!is_dir(STOCKAGE_PATH)) {
                mkdir(STOCKAGE_PATH, 0777, true);
            }

            $nomServeur = uniqid('justif_', true) . '.' . $extension;
            $destination = STOCKAGE_PATH . '/' . $nomServeur;

            if (!move_uploaded_file($_FILES['justificatif']['tmp_name'], $destination)) {
                $_SESSION['message_flash'] = ['type' => 'danger', 'texte' => "Le justificatif n'a pas pu être enregistré sur le serveur."];
                header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
                exit;
            }

            Justificatif::ajouter(
                $idFiche,
                $nomReel,
                $nomServeur,
                $extension,
                0,
                0,
                0
            );
            Journal::enregistrerDepuisSession('AJOUT_JUSTIFICATIF', 'justificatif', $idFiche, "Ajout d'un justificatif");
            $_SESSION['message_flash'] = ['type' => 'success', 'texte' => 'Justificatif ajouté avec succès.'];

            header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_justificatif']) && $fiche) {
            $idJustificatif = (int) ($_POST['id_justificatif'] ?? 0);
            $justificatif = Justificatif::trouverParId($idJustificatif);

            if ($justificatif) {
                $chemin = STOCKAGE_PATH . '/' . $justificatif['nom_serveur'];
                if (is_file($chemin)) {
                    unlink($chemin);
                }
                Justificatif::supprimer($idJustificatif, $idFiche);
            }

            header('Location: ' . APP_BASE_URL . '/synthese.php?id=' . $idFiche);
            exit;
        }

        if ($fiche) {
            $fiche = Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur);
            $horsForfaits = LigneFrais::toutesParFiche($idFiche);
            $justificatifs = Justificatif::tousParFiche($idFiche);
            $totalForfait = Fiche::totalForfaitDepuisChamps($fiche);
            $totalHorsForfait = Fiche::totalHorsForfait($idFiche);
            $totalTvaForfait = Fiche::totalTvaForfaitDepuisChamps($fiche);
            $totalTvaHorsForfait = LigneFrais::totalTvaParFiche($idFiche);
            $totalTva = round($totalTvaForfait + $totalTvaHorsForfait, 2);
            $totalTtc = round($totalForfait + $totalHorsForfait, 2);
            $commentaires = CommentaireFiche::toutesParFiche($idFiche);
        } else {
            $horsForfaits = [];
            $justificatifs = [];
            $totalForfait = 0;
            $totalHorsForfait = 0;
            $totalTvaForfait = 0;
            $totalTvaHorsForfait = 0;
            $totalTva = 0;
            $totalTtc = 0;
            $commentaires = [];
        }

        require __DIR__ . '/../vues/visiteur/detail_fiche.php';
    }
}
