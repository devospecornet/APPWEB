<?php $titrePage = 'Accès API';
require __DIR__ . '/../../squelette/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h2 mb-1">Gestion des accès API</h1>
        <p class="text-secondary mb-0">Cette page permet de suivre les jetons utilisés pour les accès externes et l'application mobile.</p>
    </div>
    <form method="post">
        <?= csrf_input() ?>
        <button class="btn btn-warning btn-arrondi" name="purger_expire">Purger les expirés</button>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="bloc-page h-100">
            <div class="text-secondary small mb-1">Jetons enregistrés</div>
            <div class="display-6 fw-semibold"><?= (int) ($statsJetons['total'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bloc-page h-100">
            <div class="text-secondary small mb-1">Jetons actifs</div>
            <div class="display-6 fw-semibold"><?= (int) ($statsJetons['actifs'] ?? 0) ?></div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="bloc-page h-100">
            <div class="text-secondary small mb-1">Jetons expirés</div>
            <div class="display-6 fw-semibold"><?= (int) ($statsJetons['expires'] ?? 0) ?></div>
        </div>
    </div>
</div>

<div class="bloc-page mb-4">
    <p class="mb-0 text-secondary">
        Les jetons API sont créés lors d'une connexion via l'API. Ils ne sont pas générés lors d'une connexion classique sur l'application web.
    </p>
</div>

<div class="bloc-page overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Création</th>
                    <th>Expiration</th>
                    <th>Statut</th>
                    <th>Jeton</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jetons)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-secondary">Aucun jeton API enregistré pour le moment.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jetons as $jeton): ?>
                        <?php $estExpire = (int) ($jeton['est_expire'] ?? 0) === 1; ?>
                        <tr>
                            <td>
                                <?= e(trim($jeton['prenom'] . ' ' . $jeton['nom'])) ?><br>
                                <span class="small text-secondary"><?= e($jeton['email']) ?></span>
                            </td>
                            <td><?= e($jeton['role']) ?></td>
                            <td><?= e(format_date_fr($jeton['date_creation'], true)) ?></td>
                            <td><?= e(format_date_fr($jeton['date_expiration'], true)) ?></td>
                            <td>
                                <?php if ($estExpire): ?>
                                    <span class="badge text-bg-secondary">Expiré</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">Actif</span>
                                <?php endif; ?>
                            </td>
                            <td><code><?= e(substr($jeton['jeton'], 0, 18)) ?>...</code></td>
                            <td>
                                <form method="post" onsubmit="return confirm('Révoquer ce jeton API ?');">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id_jeton" value="<?= (int) $jeton['id'] ?>">
                                    <button class="btn btn-outline-danger btn-sm btn-arrondi" name="revoquer_jeton">Révoquer</button>
                                </form>
                            </td>
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