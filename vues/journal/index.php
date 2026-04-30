<?php
$titrePage = 'Journalisation';
require __DIR__ . '/../../squelette/header.php';
$role = $_SESSION['utilisateur']['role'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h2 mb-1">Journal des actions</h1>
        <p class="text-secondary mb-0">Consultation <?= $role === 'administrateur' ? 'complète' : 'restreinte' ?> des événements importants.</p>
    </div>
    <a href="<?= e(url_avec_params(['export' => 'csv', 'page' => null])) ?>" class="btn btn-success btn-arrondi">Exporter CSV</a>
</div>

<div class="bloc-page p-4 mb-4">
    <form method="get" class="row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Action</label>
            <input type="text" class="form-control" name="action" value="<?= e($_GET['action'] ?? '') ?>" placeholder="Ex : VALIDATION_FICHE">
        </div>
        <div class="col-md-2">
            <label class="form-label">Rôle</label>
            <select class="form-select" name="role_utilisateur">
                <option value="">Tous</option>
                <?php foreach (['visiteur' => 'Visiteur', 'comptable' => 'Comptable', 'administrateur' => 'Administrateur'] as $val => $label): ?>
                    <option value="<?= e($val) ?>" <?= (($_GET['role_utilisateur'] ?? '') === $val) ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Utilisateur</label>
            <input type="text" class="form-control" name="utilisateur" value="<?= e($_GET['utilisateur'] ?? '') ?>" placeholder="Nom, prénom ou e-mail">
        </div>
        <div class="col-md-2">
            <label class="form-label">Du</label>
            <input type="date" class="form-control" name="date_debut" value="<?= e($_GET['date_debut'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Au</label>
            <input type="date" class="form-control" name="date_fin" value="<?= e($_GET['date_fin'] ?? '') ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Par page</label>
            <select class="form-select" name="par_page">
                <?php foreach ([10,20,50] as $n): ?>
                    <option value="<?= $n ?>" <?= ($pagination['par_page'] ?? 20) == $n ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-10 d-flex gap-2 flex-wrap">
            <button class="btn btn-primary btn-arrondi">Filtrer</button>
            <a href="<?= e(asset_url('journal.php')) ?>" class="btn btn-outline-secondary btn-arrondi">Réinitialiser</a>
        </div>
    </form>
</div>

<div class="bloc-page overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Action</th>
                    <th>Objet</th>
                    <th>Détails</th>
                    <th>Niveau</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr><td colspan="7" class="text-center py-4 text-secondary">Aucune entrée dans le journal.</td></tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                        <tr>
                            <td><?= e(format_date_fr($log['date_action'] ?? null, true)) ?></td>
                            <td><?= e(trim(($log['prenom'] ?? '') . ' ' . ($log['nom'] ?? ''))) ?><br><span class="small text-secondary"><?= e($log['email'] ?? '') ?></span></td>
                            <td><?= e($log['role_utilisateur']) ?></td>
                            <td><code><?= e($log['action']) ?></code></td>
                            <td><?= e($log['type_objet']) ?><?= !empty($log['id_objet']) ? ' #' . (int) $log['id_objet'] : '' ?></td>
                            <td><?= e($log['details']) ?></td>
                            <td><span class="badge <?= e(badge_niveau_classe($log['niveau'] ?? 'info')) ?>"><?= e($log['niveau'] ?? 'info') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($pagination['total_pages'] ?? 1) > 1): ?>
    <nav class="mt-4">
        <ul class="pagination">
            <?php for ($i = 1; $i <= $pagination['total_pages']; $i++): ?>
                <li class="page-item <?= $i === ($pagination['page'] ?? 1) ? 'active' : '' ?>">
                    <a class="page-link" href="<?= e(url_avec_params(['page' => $i])) ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>

<?php require __DIR__ . '/../../squelette/footer.php'; ?>
