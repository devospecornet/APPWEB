<?php
$titrePage = 'Saisie complète de la fiche';
include __DIR__ . '/../../squelette/header.php';

$tauxPossibles = [0 => '0 %', 5 => '5 %', 10 => '10 %', 20 => '20 %'];
$champTva = static function (string $nom, string $label, $valeurActuelle) use ($tauxPossibles): void {
    ?>
    <div class="col-md-4">
        <label class="form-label"><?= e($label) ?></label>
        <select name="<?= e($nom) ?>" class="form-select" required>
            <?php foreach ($tauxPossibles as $valeur => $libelle): ?>
                <option value="<?= (int) $valeur ?>" <?= ((string) $valeurActuelle === (string) $valeur) ? 'selected' : '' ?>><?= e($libelle) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
};
?>
<div class="hero-futuriste mb-4">
    <div>
        <h1 class="display-6 fw-bold mb-2"><?= $fiche ? 'Édition complète de la fiche' : 'Nouvelle fiche de frais' ?></h1>
        <p class="mb-0 opacity-75">Le visiteur saisit toujours le montant TTC payé. La TVA est maintenant prise en compte sur le forfait classique et sur les hors forfaits.</p>
    </div>
    <a href="<?= APP_BASE_URL ?>/visiteur.php" class="btn btn-light btn-arrondi fw-semibold">Retour à mes fiches</a>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?= e($typeMessage) ?>"><?= e($message) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="bloc-page overflow-hidden mb-4">
            <div class="entete-carte-bleue">1. Saisir la fiche</div>
            <div class="p-4">
                <div class="info-plafond mb-4">
                    <h2 class="h6 fw-bold mb-2">Règle de saisie</h2>
                    <div class="small text-secondary">
                        Saisis le montant TTC réellement payé dans le forfait classique.<br>
                        Petit déjeuner : 12 € — Repas midi : 23 € — Repas soir : 23 € — Hôtel : 150 € — Essence : libre.<br>
                        Si le montant dépasse le plafond, saisis la partie autorisée ici puis ajoute le dépassement en hors forfait.
                    </div>
                </div>

                <form method="POST" class="row g-3 align-items-end">
                    <?= csrf_input() ?>
                    <input type="hidden" name="id_fiche" value="<?= (int) ($fiche['id'] ?? 0) ?>">

                    <div class="col-md-4">
                        <label class="form-label">Mois et année</label>
                        <input type="month" name="mois" class="form-control" value="<?= e($fiche['mois'] ?? '') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Essence TTC (€)</label>
                        <input type="number" step="0.01" min="0" name="frais_essence" class="form-control champ-sans-fleches" value="<?= $fiche ? e((string) $fiche['frais_essence']) : '' ?>">
                    </div>
                    <?php $champTva('taux_tva_essence', 'TVA essence', $fiche['taux_tva_essence'] ?? 0); ?>

                    <div class="col-md-4">
                        <label class="form-label">Petit déjeuner TTC (€)</label>
                        <input type="number" step="0.01" min="0" max="12" name="frais_petit_dejeuner" class="form-control champ-sans-fleches" value="<?= $fiche ? e((string) $fiche['frais_petit_dejeuner']) : '' ?>">
                    </div>
                    <?php $champTva('taux_tva_petit_dejeuner', 'TVA petit déjeuner', $fiche['taux_tva_petit_dejeuner'] ?? 0); ?>

                    <div class="col-md-4">
                        <label class="form-label">Repas midi TTC (€)</label>
                        <input type="number" step="0.01" min="0" max="23" name="frais_repas_midi" class="form-control champ-sans-fleches" value="<?= $fiche ? e((string) $fiche['frais_repas_midi']) : '' ?>">
                    </div>
                    <?php $champTva('taux_tva_repas_midi', 'TVA repas midi', $fiche['taux_tva_repas_midi'] ?? 0); ?>

                    <div class="col-md-4">
                        <label class="form-label">Repas soir TTC (€)</label>
                        <input type="number" step="0.01" min="0" max="23" name="frais_repas_soir" class="form-control champ-sans-fleches" value="<?= $fiche ? e((string) $fiche['frais_repas_soir']) : '' ?>">
                    </div>
                    <?php $champTva('taux_tva_repas_soir', 'TVA repas soir', $fiche['taux_tva_repas_soir'] ?? 0); ?>

                    <div class="col-md-4">
                        <label class="form-label">Nuitée hôtel TTC (€)</label>
                        <input type="number" step="0.01" min="0" max="150" name="frais_hotel" class="form-control champ-sans-fleches" value="<?= $fiche ? e((string) $fiche['frais_hotel']) : '' ?>">
                    </div>
                    <?php $champTva('taux_tva_hotel', 'TVA hôtel', $fiche['taux_tva_hotel'] ?? 0); ?>

                    <div class="col-12 d-grid">
                        <button type="submit" name="creer_ou_mettre_a_jour_fiche" class="btn btn-success btn-arrondi">
                            <?= $fiche ? 'Mettre à jour la fiche' : 'Créer la fiche' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <?php if ($fiche): ?>
            <div class="bloc-page overflow-hidden mb-4">
                <div class="entete-carte-bleue">2. Ajouter des hors forfaits</div>
                <div class="p-4">
                    <div class="alert alert-info">
                        Ici, tu saisis le libellé, le montant TTC réel du dépassement et le taux de TVA correspondant.
                    </div>

                    <?php if (in_array($fiche['statut'], ['saisie', 'refusee'], true)): ?>
                        <form method="POST" class="row g-3 align-items-end mb-4">
                    <?= csrf_input() ?>
                            <input type="hidden" name="id_fiche" value="<?= (int) $fiche['id'] ?>">

                            <div class="col-md-6">
                                <label class="form-label">Libellé du hors forfait</label>
                                <input type="text" name="libelle_hors_forfait" class="form-control" placeholder="Ex : Dépassement repas soir" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Montant TTC</label>
                                <input type="number" step="0.01" min="0" name="montant_hors_forfait" class="form-control champ-sans-fleches" value="">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">TVA</label>
                                <select name="taux_tva" class="form-select" required>
                                    <option value="">Choisir</option>
                                    <option value="5">5 %</option>
                                    <option value="10">10 %</option>
                                    <option value="20">20 %</option>
                                </select>
                            </div>

                            <div class="col-12 d-grid">
                                <button type="submit" name="ajouter_hors_forfait" class="btn btn-primary btn-arrondi">Ajouter le hors forfait</button>
                            </div>
                        </form>
                    <?php endif; ?>

                    <?php if (empty($horsForfaits)): ?>
                        <div class="alert alert-light border">Aucun hors forfait enregistré.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Libellé</th>
                                        <th>Montant TTC</th>
                                        <th>TVA</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($horsForfaits as $horsForfait): ?>
                                        <tr>
                                            <td><?= e($horsForfait['libelle']) ?></td>
                                            <td><?= number_format((float) $horsForfait['montant_ttc'], 2, ',', ' ') ?> €</td>
                                            <td><?= number_format((float) $horsForfait['taux_tva'], 0, ',', ' ') ?> %</td>
                                            <td class="text-end">
                                                <?php if (in_array($fiche['statut'], ['saisie', 'refusee'], true)): ?>
                                                    <form method="POST" onsubmit="return confirm('Supprimer ce hors forfait ?');">
                                                        <?= csrf_input() ?>
                                                        <input type="hidden" name="id_fiche" value="<?= (int) $fiche['id'] ?>">
                                                        <input type="hidden" name="id_hors_forfait" value="<?= (int) $horsForfait['id'] ?>">
                                                        <button type="submit" name="supprimer_hors_forfait" class="btn btn-outline-danger btn-sm btn-arrondi">Supprimer</button>
                                                    </form>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bloc-page overflow-hidden">
                <div class="entete-carte-bleue">3. Ajouter les justificatifs</div>
                <div class="p-4">
                    <div class="alert alert-info">
                        Ici, tu ajoutes uniquement le fichier justificatif.
                    </div>

                    <?php if (in_array($fiche['statut'], ['saisie', 'refusee'], true)): ?>
                        <form method="POST" enctype="multipart/form-data" class="mb-4">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id_fiche" value="<?= (int) $fiche['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Fichier justificatif</label>
                                <input type="file" name="justificatif" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                            </div>

                            <button type="submit" name="ajouter_justificatif" class="btn btn-primary btn-arrondi w-100">Ajouter le justificatif</button>
                        </form>
                    <?php endif; ?>

                    <?php if (empty($justificatifs)): ?>
                        <div class="alert alert-warning">Aucun justificatif ajouté.</div>
                    <?php else: ?>
                        <?php foreach ($justificatifs as $justificatif): ?>
                            <div class="border rounded p-3 mb-3">
                                <div class="fw-semibold"><?= e($justificatif['nom_reel']) ?></div>
                                <div class="small text-secondary mb-2"><?= e(format_date_fr($justificatif['date_envoi'], true)) ?></div>

                                <a href="<?= APP_BASE_URL ?>/stockage/justificatifs/<?= e($justificatif['nom_serveur']) ?>" target="_blank" class="btn btn-outline-primary btn-sm mt-2">Ouvrir</a>

                                <?php if (in_array($fiche['statut'], ['saisie', 'refusee'], true)): ?>
                                    <form method="POST" class="mt-2" onsubmit="return confirm('Supprimer ce justificatif ?');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id_fiche" value="<?= (int) $fiche['id'] ?>">
                                        <input type="hidden" name="id_justificatif" value="<?= (int) $justificatif['id'] ?>">
                                        <button type="submit" name="supprimer_justificatif" class="btn btn-outline-danger btn-sm btn-arrondi">Supprimer</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-lg-5">
        <div class="bloc-page p-4 sticky-top" style="top: 20px;">
            <h2 class="h4 fw-bold mb-4">Résumé</h2>

            <?php if (!$fiche): ?>
                <div class="alert alert-info mb-0">Crée d’abord la fiche pour afficher le récapitulatif.</div>
            <?php else: ?>
                <div class="resume-ligne">
                    <span>Forfait TTC</span>
                    <strong><?= number_format((float) $totalForfait, 2, ',', ' ') ?> €</strong>
                </div>

                <div class="resume-ligne">
                    <span>TVA du forfait</span>
                    <strong><?= number_format((float) $totalTvaForfait, 2, ',', ' ') ?> €</strong>
                </div>

                <div class="resume-ligne">
                    <span>Hors forfait TTC</span>
                    <strong><?= number_format((float) $totalHorsForfait, 2, ',', ' ') ?> €</strong>
                </div>

                <div class="resume-ligne">
                    <span>TVA hors forfait</span>
                    <strong><?= number_format((float) $totalTvaHorsForfait, 2, ',', ' ') ?> €</strong>
                </div>

                <div class="resume-ligne">
                    <span>TVA totale</span>
                    <strong><?= number_format((float) $totalTva, 2, ',', ' ') ?> €</strong>
                </div>

                <div class="resume-ligne resume-total">
                    <span>Total fiche TTC</span>
                    <strong><?= number_format((float) $fiche['montant_total'], 2, ',', ' ') ?> €</strong>
                </div>

                <div class="mt-4">
                    <?php if (Justificatif::compterParFiche((int) $fiche['id']) < 1): ?>
                        <div class="alert alert-warning mb-0">Ajoute au moins un justificatif avant l’envoi.</div>
                    <?php elseif (in_array($fiche['statut'], ['saisie', 'refusee'], true)): ?>
                        <div class="alert alert-success mb-0">Fiche prête à être envoyée.</div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">Fiche déjà transmise ou traitée.</div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($commentaires)): ?>
    <div class="bloc-page p-4 mt-4">
        <h2 class="h5 fw-bold mb-3">Historique des commentaires</h2>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($commentaires as $commentaire): ?>
                <div class="border rounded-4 p-3">
                    <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                        <strong><?= e(trim(($commentaire['prenom'] ?? '') . ' ' . ($commentaire['nom'] ?? '')) ?: ucfirst($commentaire['auteur_role'])) ?></strong>
                        <span class="small text-secondary"><?= e(format_date_fr($commentaire['date_creation'] ?? null, true)) ?> · <?= e($commentaire['type_commentaire']) ?></span>
                    </div>
                    <div><?= nl2br(e($commentaire['contenu'])) ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/../../squelette/footer.php'; ?>
