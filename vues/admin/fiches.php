<?php
$titrePage = 'Toutes les fiches';
require __DIR__ . '/../../squelette/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h2 mb-1">Vue globale des fiches frais</h1>
        <p class="text-secondary mb-0">Recherche, filtres, export CSV, pagination et statistiques mensuelles.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= e(url_avec_params(['export' => 'csv','page' => null])) ?>" class="btn btn-success btn-arrondi">Exporter CSV</a>
        <a href="<?= e(asset_url('admin.php')) ?>" class="btn btn-outline-secondary btn-arrondi">Retour</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="bloc-page p-3"><div class="small text-secondary">Total fiches</div><div class="display-6 fw-bold"><?= (int) ($statsGlobales['total_fiches'] ?? 0) ?></div></div></div>
    <div class="col-md-3"><div class="bloc-page p-3"><div class="small text-secondary">En validation</div><div class="display-6 fw-bold"><?= (int) ($statsGlobales['total_transmises'] ?? 0) ?></div></div></div>
    <div class="col-md-3"><div class="bloc-page p-3"><div class="small text-secondary">Validées</div><div class="display-6 fw-bold"><?= (int) ($statsGlobales['total_validees'] ?? 0) ?></div></div></div>
    <div class="col-md-3"><div class="bloc-page p-3"><div class="small text-secondary">Montant global</div><div class="h3 fw-bold mb-0"><?= number_format((float) ($statsGlobales['total_montant'] ?? 0), 2, ',', ' ') ?> €</div></div></div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="bloc-page p-4">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Statut</label><select class="form-select" name="statut"><option value="">Tous</option><?php foreach (['saisie','transmise','validee','refusee','remboursee'] as $s): ?><option value="<?= e($s) ?>" <?= ($statut ?? '') === $s ? 'selected' : '' ?>><?= e(badge_statut_libelle($s)) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Mois</label><input type="month" class="form-control" name="mois" value="<?= e($mois ?? '') ?>"></div>
                <div class="col-md-3"><label class="form-label">Recherche visiteur</label><input type="text" class="form-control" name="recherche" value="<?= e($recherche ?? '') ?>" placeholder="Nom, e-mail, numéro..."></div>
                <div class="col-md-2"><label class="form-label">Par page</label><select name="par_page" class="form-select"><?php foreach ([10,20,50] as $n): ?><option value="<?= $n ?>" <?= ($pagination['par_page'] ?? 10) == $n ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?></select></div>
                <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary btn-arrondi">OK</button></div>
            </form>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bloc-page p-4 h-100">
            <div class="small text-secondary">Statistiques mensuelles</div>
            <div class="fw-bold mb-2"><?= e(format_mois_fr($statsMensuelles['mois'] ?? '')) ?></div>
            <div>Total du mois : <strong><?= number_format((float) ($statsMensuelles['total_montant'] ?? 0), 2, ',', ' ') ?> €</strong></div>
            <div>TVA du forfait : <strong><?= number_format((float) ($statsMensuelles['total_tva_forfait'] ?? 0), 2, ',', ' ') ?> €</strong></div>
            <div>Validées : <strong><?= (int) ($statsMensuelles['nb_validees'] ?? 0) ?></strong></div>
            <div>Top visiteurs remboursés :</div>
            <ul class="small mb-0"><?php foreach (($statsMensuelles['top_visiteurs'] ?? []) as $tv): ?><li><?= e(trim(($tv['prenom'] ?? '') . ' ' . ($tv['nom'] ?? ''))) ?> — <?= number_format((float) $tv['montant_total'], 2, ',', ' ') ?> €</li><?php endforeach; ?></ul>
        </div>
    </div>
</div>

<div class="bloc-page overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light"><tr><th>Numéro</th><th>Visiteur</th><th>Mois</th><th>Date création</th><th>Montant TTC</th><th>Statut</th></tr></thead>
            <tbody>
            <?php if (empty($fiches)): ?>
                <tr><td colspan="6" class="text-center py-4 text-secondary">Aucune fiche trouvée.</td></tr>
            <?php else: foreach ($fiches as $fiche): ?>
                <tr>
                    <td class="fw-semibold"><?= e($fiche['numero_fiche']) ?></td>
                    <td><?= e(($fiche['prenom'] ?? '') . ' ' . ($fiche['nom'] ?? '')) ?><br><span class="small text-secondary"><?= e($fiche['email'] ?? '') ?></span></td>
                    <td><?= e(format_mois_fr($fiche['mois'])) ?></td>
                    <td><?= e(format_date_fr($fiche['date_creation'] ?? null, true)) ?></td>
                    <td><?= number_format((float) $fiche['montant_total'], 2, ',', ' ') ?> €</td>
                    <td><span class="badge-statut <?= e(badge_statut_classe($fiche['statut'])) ?>"><?= e(badge_statut_libelle($fiche['statut'])) ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($pagination['total_pages'] ?? 1) > 1): ?>
<nav class="mt-4"><ul class="pagination">
<?php for ($i=1; $i <= $pagination['total_pages']; $i++): ?>
<li class="page-item <?= $i === ($pagination['page'] ?? 1) ? 'active' : '' ?>"><a class="page-link" href="<?= e(url_avec_params(['page' => $i])) ?>"><?= $i ?></a></li>
<?php endfor; ?>
</ul></nav>
<?php endif; ?>
<?php require __DIR__ . '/../../squelette/footer.php'; ?>
