<?php
require_once __DIR__ . '/../modeles/Fiche.php';
require_once __DIR__ . '/utils/AuthMiddleware.php';
require_once __DIR__ . '/utils/ReponseJson.php';

$utilisateurApi = AuthMiddleware::utilisateurConnecteApi();
$idUtilisateur = (int) $utilisateurApi['id_utilisateur'];
$roleUtilisateur = (string) ($utilisateurApi['role'] ?? '');
$methode = $_SERVER['REQUEST_METHOD'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];

$extraireDonnees = static function (array $source): array {
    return [
        'mois' => trim($source['mois'] ?? ''),
        'frais_essence' => max(0, (float) ($source['frais_essence'] ?? 0)),
        'frais_petit_dejeuner' => max(0, (float) ($source['frais_petit_dejeuner'] ?? 0)),
        'frais_repas_midi' => max(0, (float) ($source['frais_repas_midi'] ?? 0)),
        'frais_repas_soir' => max(0, (float) ($source['frais_repas_soir'] ?? 0)),
        'frais_hotel' => max(0, (float) ($source['frais_hotel'] ?? 0)),
        'taux_tva_essence' => (float) ($source['taux_tva_essence'] ?? 0),
        'taux_tva_petit_dejeuner' => (float) ($source['taux_tva_petit_dejeuner'] ?? 0),
        'taux_tva_repas_midi' => (float) ($source['taux_tva_repas_midi'] ?? 0),
        'taux_tva_repas_soir' => (float) ($source['taux_tva_repas_soir'] ?? 0),
        'taux_tva_hotel' => (float) ($source['taux_tva_hotel'] ?? 0),
    ];
};

$valider = static function (array $donnees, int $idUtilisateur, int $idFiche = 0): ?string {
    if ($donnees['mois'] === '') return 'Le mois est obligatoire.';
    if (!preg_match('/^\d{4}-\d{2}$/', $donnees['mois'])) return 'Le format du mois doit être YYYY-MM.';
    if ($donnees['frais_petit_dejeuner'] > 12) return 'Le petit déjeuner est plafonné à 12 €.';
    if ($donnees['frais_repas_midi'] > 23) return 'Le repas du midi est plafonné à 23 €.';
    if ($donnees['frais_repas_soir'] > 23) return 'Le repas du soir est plafonné à 23 €.';
    if ($donnees['frais_hotel'] > 150) return 'La nuitée hôtel est plafonnée à 150 €.';
    foreach (['taux_tva_essence', 'taux_tva_petit_dejeuner', 'taux_tva_repas_midi', 'taux_tva_repas_soir', 'taux_tva_hotel'] as $champ) {
        if (!Fiche::tauxTvaAutorise((float) $donnees[$champ])) return 'Un taux de TVA forfaitaire est invalide.';
    }
    if (Fiche::ficheDuMoisExiste($idUtilisateur, $donnees['mois'], $idFiche)) return 'Une fiche existe déjà pour ce mois.';
    return null;
};

if ($methode === 'GET') {
    $idFiche = (int) ($_GET['id'] ?? 0);
    if ($idFiche > 0) {
        $fiche = in_array($roleUtilisateur, ['comptable', 'administrateur'], true)
            ? Fiche::trouverParId($idFiche)
            : Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur);
        if (!$fiche) {
            ReponseJson::envoyer(['succes' => false, 'message' => 'Fiche introuvable.'], 404);
        }
        ReponseJson::envoyer([
            'succes' => true,
            'fiche' => $fiche,
            'tva_forfait' => Fiche::totalTvaForfaitDepuisChamps($fiche),
            'tva_hors_forfait' => Fiche::totalTvaHorsForfait($idFiche),
            'tva_totale' => Fiche::totalTvaGlobale($fiche, $idFiche),
        ]);
    }
    $mois = trim($_GET['mois'] ?? '');
    $statut = trim($_GET['statut'] ?? '');
    ReponseJson::envoyer([
        'succes' => true,
        'fiches' => Fiche::toutesParUtilisateur($idUtilisateur, $mois !== '' ? $mois : null, $statut !== '' ? $statut : null)
    ]);
}

if ($methode === 'POST') {
    if (!is_array($body)) ReponseJson::envoyer(['succes' => false, 'message' => 'Corps JSON invalide.'], 400);
    $action = trim((string) ($body['action'] ?? ''));
    if ($action === 'transmettre') {
        $idFiche = (int) ($body['id_fiche'] ?? $body['id'] ?? 0);
        if ($idFiche <= 0) ReponseJson::envoyer(['succes' => false, 'message' => 'Identifiant de fiche invalide.'], 400);
        $ok = Fiche::transmettre($idFiche, $idUtilisateur);
        ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Fiche transmise avec succès.' : 'Transmission impossible.'], $ok ? 200 : 400);
    }
    $donnees = $extraireDonnees($body);
    $erreur = $valider($donnees, $idUtilisateur);
    if ($erreur !== null) ReponseJson::envoyer(['succes' => false, 'message' => $erreur], 400);
    $ok = Fiche::ajouter($idUtilisateur, $donnees['mois'], $donnees['frais_essence'], $donnees['frais_petit_dejeuner'], $donnees['frais_repas_midi'], $donnees['frais_repas_soir'], $donnees['frais_hotel'], $donnees['taux_tva_essence'], $donnees['taux_tva_petit_dejeuner'], $donnees['taux_tva_repas_midi'], $donnees['taux_tva_repas_soir'], $donnees['taux_tva_hotel']);
    ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Fiche créée avec succès.' : 'Erreur lors de la création.'], $ok ? 201 : 500);
}

if ($methode === 'PUT') {
    if (!is_array($body)) ReponseJson::envoyer(['succes' => false, 'message' => 'Corps JSON invalide.'], 400);
    $idFiche = (int) ($body['id_fiche'] ?? $body['id'] ?? 0);
    $fiche = Fiche::trouverParIdEtUtilisateur($idFiche, $idUtilisateur);
    if (!$fiche) ReponseJson::envoyer(['succes' => false, 'message' => 'Fiche introuvable.'], 404);
    $donnees = $extraireDonnees($body);
    $erreur = $valider($donnees, $idUtilisateur, $idFiche);
    if ($erreur !== null) ReponseJson::envoyer(['succes' => false, 'message' => $erreur], 400);
    $ok = Fiche::modifier($idFiche, $idUtilisateur, $donnees);
    ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Fiche mise à jour.' : 'Mise à jour impossible.'], $ok ? 200 : 400);
}

if ($methode === 'DELETE') {
    $idFiche = (int) ($_GET['id'] ?? $body['id_fiche'] ?? $body['id'] ?? 0);
    if ($idFiche <= 0) ReponseJson::envoyer(['succes' => false, 'message' => 'Identifiant de fiche invalide.'], 400);
    $ok = Fiche::supprimer($idFiche, $idUtilisateur);
    ReponseJson::envoyer(['succes' => $ok, 'message' => $ok ? 'Fiche supprimée.' : 'Suppression impossible.'], $ok ? 200 : 400);
}

ReponseJson::envoyer(['succes' => false, 'message' => 'Méthode non autorisée.'], 405);
