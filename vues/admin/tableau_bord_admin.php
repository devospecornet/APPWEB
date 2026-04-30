<?php
$titrePage = 'Administration';
require __DIR__ . '/../../squelette/header.php';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="mb-2">Administration</h1>
            <p class="text-muted mb-0">Gestion des utilisateurs, suivi global des fiches et accès aux journaux.</p>
            <div class="mt-3 d-flex gap-2 flex-wrap">
                <a href="<?= e(asset_url('admin_fiches.php')) ?>" class="btn btn-outline-primary btn-sm">Voir toutes les fiches</a>
                <a href="<?= e(asset_url('journal.php')) ?>" class="btn btn-outline-secondary btn-sm">Ouvrir le journal</a>
            </div>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($typeMessage) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Utilisateurs</div>
                    <div class="fs-3 fw-bold"><?= (int) $statsUtilisateurs['total'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Visiteurs</div>
                    <div class="fs-3 fw-bold"><?= (int) $statsUtilisateurs['visiteurs'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Comptables</div>
                    <div class="fs-3 fw-bold"><?= (int) $statsUtilisateurs['comptables'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Admins</div>
                    <div class="fs-3 fw-bold"><?= (int) $statsUtilisateurs['administrateurs'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Fiches</div>
                    <div class="fs-3 fw-bold"><?= (int) $statsFiches['total_fiches'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Montant global</div>
                    <div class="fs-5 fw-bold"><?= number_format((float) $statsFiches['total_montant'], 2, ',', ' ') ?> €</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong><?= $utilisateurEdition ? 'Modifier un utilisateur' : 'Créer un utilisateur' ?></strong>
                </div>
                <div class="card-body">
                    <?php if ($utilisateurEdition): ?>
                        <form method="post">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="modifier_utilisateur">
                            <input type="hidden" name="id_utilisateur" value="<?= (int) $utilisateurEdition['id'] ?>">

                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control"
                                    value="<?= htmlspecialchars($utilisateurEdition['nom']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control"
                                    value="<?= htmlspecialchars($utilisateurEdition['prenom']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Identifiant / E-mail</label>
                                <input type="email" name="email" class="form-control"
                                    value="<?= htmlspecialchars($utilisateurEdition['email']) ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rôle</label>
                                <select name="role" class="form-select" required>
                                    <option value="visiteur" <?= $utilisateurEdition['role'] === 'visiteur' ? 'selected' : '' ?>>Visiteur</option>
                                    <option value="comptable" <?= $utilisateurEdition['role'] === 'comptable' ? 'selected' : '' ?>>Comptable</option>
                                    <option value="administrateur" <?= $utilisateurEdition['role'] === 'administrateur' ? 'selected' : '' ?>>Administrateur</option>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="est_approuve" id="est_approuve_edit"
                                    <?= (int) $utilisateurEdition['est_approuve'] === 1 ? 'checked' : '' ?>>
                                <label class="form-check-label" for="est_approuve_edit">
                                    Compte approuvé
                                </label>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nouveau mot de passe (optionnel)</label>
                                <input type="password" name="mot_de_passe" class="form-control"
                                    placeholder="Laisser vide pour conserver le mot de passe actuel">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
                                <a href="admin.php" class="btn btn-outline-secondary">Annuler</a>
                            </div>
                        </form>
                    <?php else: ?>
                        <form method="post">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="creer_utilisateur">

                            <div class="mb-3">
                                <label class="form-label">Nom</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Identifiant / E-mail</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Mot de passe</label>
                                <input type="password" name="mot_de_passe" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Rôle</label>
                                <select name="role" class="form-select" required>
                                    <option value="visiteur">Visiteur</option>
                                    <option value="comptable">Comptable</option>
                                    <option value="administrateur">Administrateur</option>
                                </select>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="est_approuve" id="est_approuve_create" checked>
                                <label class="form-check-label" for="est_approuve_create">
                                    Compte approuvé
                                </label>
                            </div>

                            <button type="submit" class="btn btn-success">Créer le compte</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header">
                    <strong>Liste des utilisateurs</strong>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($utilisateurs)): ?>
                        <div class="p-3 text-muted">Aucun utilisateur trouvé.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nom complet</th>
                                        <th>Email / identifiant</th>
                                        <th>Rôle</th>
                                        <th>État</th>
                                        <th>Créé le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($utilisateurs as $u): ?>
                                        <tr>
                                            <td><?= (int) $u['id'] ?></td>
                                            <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                                            <td><?= htmlspecialchars($u['email']) ?></td>
                                            <td><?= htmlspecialchars($u['role']) ?></td>
                                            <td>
                                                <?php if ((int) $u['est_approuve'] === 1): ?>
                                                    <span class="badge bg-success">Approuvé</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Bloqué</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= e(format_date_fr($u['date_creation'] ?? null, true)) ?></td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2">
                                                    <a href="admin.php?edit=<?= (int) $u['id'] ?>" class="btn btn-sm btn-primary">
                                                        Modifier
                                                    </a>

                                                    <?php if ((int) $u['id'] !== (int) ($_SESSION['utilisateur']['id'] ?? 0)): ?>
                                                        <form method="post" onsubmit="return confirm('Supprimer cet utilisateur ?');">
                                                            <?= csrf_input() ?>
                                                            <input type="hidden" name="action" value="supprimer_utilisateur">
                                                            <input type="hidden" name="id_utilisateur" value="<?= (int) $u['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../../squelette/footer.php'; ?>