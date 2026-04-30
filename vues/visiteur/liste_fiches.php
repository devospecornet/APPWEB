<?php
$titrePage = 'Mes fiches';
include __DIR__ . '/../../squelette/header.php';
?>
<div class="hero-futuriste mb-4">
    <div>
        <h1 class="display-6 fw-bold mb-2">Mes notes de frais</h1>
        <p class="mb-0 opacity-75">Crée une fiche, complète-la, puis transmets-la avec un commentaire visiteur si besoin.</p>
    </div>
    <a href="<?= APP_BASE_URL ?>/synthese.php" class="btn btn-light btn-arrondi fw-semibold">Nouvelle fiche</a>
</div>

<div class="bloc-page p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-5"><label class="form-label">Mois</label><input type="month" name="mois" class="form-control" value="<?= e($mois) ?>"></div>
        <div class="col-md-4"><label class="form-label">Statut</label><select name="statut" class="form-select"><option value="">Tous</option><option value="saisie" <?= $statut === 'saisie' ? 'selected' : '' ?>>Brouillon</option><option value="transmise" <?= $statut === 'transmise' ? 'selected' : '' ?>>Transmise</option><option value="validee" <?= $statut === 'validee' ? 'selected' : '' ?>>Validée</option><option value="refusee" <?= $statut === 'refusee' ? 'selected' : '' ?>>Refusée</option></select></div>
        <div class="col-md-3 d-grid"><button class="btn btn-primary btn-arrondi">Filtrer</button></div>
    </form>
</div>

<div class="bloc-page overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Numéro</th><th>Mois</th><th>Montant</th><th>Statut</th><th>Date</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php foreach ($fiches as $fiche): ?>
                    <tr>
                        <td class="fw-semibold"><?= e($fiche['numero_fiche']) ?></td>
                        <td><?= e(format_mois_fr($fiche['mois'])) ?></td>
                        <td><?= number_format((float) $fiche['montant_total'], 2, ',', ' ') ?> €</td>
                        <td><span class="badge-statut <?= e(badge_statut_classe($fiche['statut'])) ?>"><?= e(badge_statut_libelle($fiche['statut'])) ?></span></td>
                        <td><?= e(format_date_fr($fiche['date_modification'], true)) ?></td>
                        <td class="text-end">
                            <div class="d-flex justify-content-end gap-2 flex-wrap align-items-start">
                                <a href="<?= APP_BASE_URL ?>/synthese.php?id=<?= (int) $fiche['id'] ?>" class="btn btn-outline-secondary btn-sm btn-arrondi"><?= in_array($fiche['statut'], ['saisie', 'refusee'], true) ? 'Compléter' : 'Voir' ?></a>
                                <?php if (in_array($fiche['statut'], ['saisie', 'refusee'], true)): ?>
                                    <form method="POST" class="d-flex gap-2 flex-wrap justify-content-end align-items-start">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id_fiche" value="<?= (int) $fiche['id'] ?>">
                                        <textarea name="commentaire_visiteur" class="form-control form-control-sm" rows="2" style="min-width:220px" placeholder="Commentaire lors de l'envoi..."><?= e($fiche['commentaire_visiteur'] ?? '') ?></textarea>
                                        <button type="submit" name="transmettre_fiche" class="btn btn-primary btn-sm btn-arrondi">Envoyer</button>
                                    </form>
                                    <form method="POST" onsubmit="return confirm('Supprimer cette fiche ?');">
                                        <?= csrf_input() ?>
                                        <input type="hidden" name="id_fiche" value="<?= (int) $fiche['id'] ?>">
                                        <button type="submit" name="supprimer_fiche" class="btn btn-outline-danger btn-sm btn-arrondi">Supprimer</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-secondary small">Traitement en cours</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($fiches)): ?><tr><td colspan="6" class="text-center text-secondary py-4">Aucune fiche trouvée.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/../../squelette/footer.php'; ?>
