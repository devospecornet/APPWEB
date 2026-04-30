<?php
$titrePage = 'Tableau de bord';
include __DIR__ . '/../../squelette/header.php';
$utilisateur = $_SESSION['utilisateur'];
$role = $utilisateur['role'] ?? 'visiteur';

$badgeClasse = static function (string $statut): string {
    return match ($statut) {
        'transmise' => 'badge-transmise',
        'validee' => 'badge-validee',
        'refusee' => 'badge-refusee',
        default => 'badge-saisie'
    };
};
?>

<?php if ($role === 'visiteur'): ?>
    <section class="hero-futuriste mb-4">
        <div>
            <div class="section-label text-white-50 mb-2">Espace visiteur</div>
            <h1 class="display-5 fw-bold mb-2">Bonjour <?= e($utilisateur['prenom']) ?></h1>
            <p class="mb-0 opacity-75">Retrouve ici le suivi de tes fiches de frais et les actions en cours.</p>

            <div class="hero-kpi">
                <div class="hero-kpi__item">
                    <div class="hero-kpi__label">Mes fiches</div>
                    <div class="hero-kpi__value" data-count="<?= (int) ($dashboard['nb_fiches'] ?? 0) ?>">0</div>
                </div>
                <div class="hero-kpi__item">
                    <div class="hero-kpi__label">À compléter</div>
                    <div class="hero-kpi__value" data-count="<?= (int) ($dashboard['nb_saisie'] ?? 0) ?>">0</div>
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= e(asset_url('visiteur.php')) ?>" class="btn btn-light btn-arrondi fw-semibold">Ouvrir mes fiches</a>
            <a href="<?= e(asset_url('synthese.php')) ?>" class="btn btn-ghost btn-arrondi">Nouvelle fiche</a>
        </div>
    </section>

    <section class="dashboard-grid mb-4">
        <div class="span-4 glass-card carte-stat">
            <div class="stat-label">Montant cumulé</div>
            <div class="stat-value"><?= number_format((float) ($dashboard['montant_total'] ?? 0), 2, ',', ' ') ?> €</div>
            <div class="stat-sub">Ensemble de tes fiches enregistrées</div>
        </div>
        <div class="span-4 glass-card carte-stat">
            <div class="stat-label">Transmises</div>
            <div class="stat-value" data-count="<?= (int) ($dashboard['nb_transmises'] ?? 0) ?>">0</div>
            <div class="stat-sub">En attente du comptable</div>
        </div>
        <div class="span-4 glass-card carte-stat">
            <div class="stat-label">Rôle actif</div>
            <div class="stat-value"><?= e(ucfirst($role)) ?></div>
            <div class="stat-sub">Session en cours</div>
        </div>
    </section>

    <section class="bloc-page p-4 mb-4">
        <div class="section-title">
            <div>
                <div class="section-label">Activité récente</div>
                <h2 class="h3 fw-bold mb-0">Mes dernières fiches</h2>
            </div>
        </div>

        <?php if (!empty($dashboard['fiches_recentes'])): ?>
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Mois</th>
                            <th>Montant</th>
                            <th>Statut</th>
                            <th class="text-end">Accès</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard['fiches_recentes'] as $fiche): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($fiche['numero_fiche']) ?></td>
                                <td><?= e($fiche['mois']) ?></td>
                                <td><?= number_format((float) $fiche['montant_total'], 2, ',', ' ') ?> €</td>
                                <td><span class="badge-statut <?= $badgeClasse($fiche['statut']) ?>"><?= e($fiche['statut']) ?></span></td>
                                <td class="text-end">
                                    <a href="<?= e(asset_url('synthese.php')) ?>?id=<?= (int) $fiche['id'] ?>" class="btn btn-outline-primary btn-sm btn-arrondi">Ouvrir</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">Aucune fiche enregistrée pour le moment.</div>
        <?php endif; ?>
    </section>

    <section class="dashboard-grid">
        <div class="span-6 bloc-page p-4">
            <div class="section-title">
                <div>
                    <div class="section-label">Suivi de la fiche</div>
                    <h2 class="h4 fw-bold mb-0">Actions disponibles</h2>
                </div>
            </div>
            <div class="quick-list">
                <a class="quick-item text-decoration-none text-dark" href="<?= e(asset_url('synthese.php')) ?>">
                    <div>
                        <div class="fw-semibold">Créer une fiche</div>
                        <div class="text-secondary small">Créer une fiche de frais</div>
                    </div>
                    <span class="metric-chip">Nouveau</span>
                </a>
                <a class="quick-item text-decoration-none text-dark" href="<?= e(asset_url('visiteur.php')) ?>">
                    <div>
                        <div class="fw-semibold">Consulter mes statuts</div>
                        <div class="text-secondary small">Saisie, transmise, validée ou refusée</div>
                    </div>
                    <span class="metric-chip">Suivi</span>
                </a>
                <div class="quick-item">
                    <div>
                        <div class="fw-semibold">Justificatifs</div>
                        <div class="text-secondary small">Ajoute un justificatif avant de transmettre une fiche.</div>
                    </div>
                    <span class="badge-soft">Pièce utile</span>
                </div>
            </div>
        </div>

        <div class="span-6 surface-dark p-4">
            <div class="section-title">
                <div>
                    <div class="section-label text-primary">Vue d'ensemble</div>
                    <h2 class="h4 fw-bold mb-0 text-white">Répartition de mes fiches</h2>
                </div>
            </div>
            <div class="row g-3">
                <div class="col-6">
                    <div class="mini-stat bleu">
                        <div class="mini-label">Saisies</div>
                        <div class="mini-value" data-count="<?= (int) ($dashboard['nb_saisie'] ?? 0) ?>">0</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="mini-stat cyan">
                        <div class="mini-label">Transmises</div>
                        <div class="mini-value" data-count="<?= (int) ($dashboard['nb_transmises'] ?? 0) ?>">0</div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="mini-stat vert">
                        <div class="mini-label">Validées</div>
                        <div class="mini-value" data-count="<?= (int) ($dashboard['nb_validees'] ?? 0) ?>">0</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

<?php elseif ($role === 'comptable'): ?>
    <section class="hero-futuriste mb-4">
        <div>
            <div class="section-label text-white-50 mb-2">Espace comptable</div>
            <h1 class="display-5 fw-bold mb-2">Traitement des fiches</h1>
            <p class="mb-0 opacity-75">Consulte les fiches transmises et traite les demandes en attente.</p>
        </div>
        <a href="<?= e(asset_url('comptable.php')) ?>" class="btn btn-light btn-arrondi fw-semibold">Ouvrir les fiches à traiter</a>
    </section>

    <section class="dashboard-grid mb-4">
        <div class="span-6 glass-card carte-stat">
            <div class="stat-label">Fiches à traiter</div>
            <div class="stat-value" data-count="<?= (int) ($dashboard['nb_a_traiter'] ?? 0) ?>">0</div>
            <div class="stat-sub">File active de validation</div>
        </div>
        <div class="span-6 glass-card carte-stat">
            <div class="stat-label">Montant à traiter</div>
            <div class="stat-value"><?= number_format((float) ($dashboard['montant_a_traiter'] ?? 0), 2, ',', ' ') ?> €</div>
            <div class="stat-sub">Somme des fiches transmises</div>
        </div>
    </section>

    <section class="bloc-page p-4">
        <div class="section-title">
            <div>
                <div class="section-label">Dernières arrivées</div>
                <h2 class="h3 fw-bold mb-0">Fiches transmises récemment</h2>
            </div>
        </div>

        <?php if (!empty($dashboard['fiches_recentes'])): ?>
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Visiteur</th>
                            <th>Mois</th>
                            <th>Montant</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard['fiches_recentes'] as $fiche): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($fiche['numero_fiche']) ?></td>
                                <td><?= e($fiche['prenom'] . ' ' . $fiche['nom']) ?></td>
                                <td><?= e($fiche['mois']) ?></td>
                                <td><?= number_format((float) $fiche['montant_total'], 2, ',', ' ') ?> €</td>
                                <td class="text-end"><a href="<?= e(asset_url('comptable.php')) ?>" class="btn btn-outline-primary btn-sm btn-arrondi">Traiter</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">Aucune fiche transmise à traiter pour le moment.</div>
        <?php endif; ?>
    </section>

<?php else: ?>
    <?php $stats = $dashboard['stats_globales'] ?? []; ?>
    <section class="hero-futuriste mb-4">
        <div>
            <div class="section-label text-white-50 mb-2">Espace administrateur</div>
            <h1 class="display-5 fw-bold mb-2">Pilotage global de l’application</h1>
            <p class="mb-0 opacity-75">Supervision des utilisateurs, des montants et des statuts.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= e(asset_url('admin.php')) ?>" class="btn btn-light btn-arrondi fw-semibold">Ouvrir l’admin</a>
            <a href="<?= e(asset_url('comptable.php')) ?>" class="btn btn-ghost btn-arrondi">Vue comptable</a>
        </div>
    </section>

    <section class="dashboard-grid mb-4">
        <div class="span-3 glass-card carte-stat">
            <div class="stat-label">Total fiches</div>
            <div class="stat-value" data-count="<?= (int) ($stats['total_fiches'] ?? 0) ?>">0</div>
        </div>
        <div class="span-3 glass-card carte-stat">
            <div class="stat-label">Montant global</div>
            <div class="stat-value"><?= number_format((float) ($stats['total_montant'] ?? 0), 2, ',', ' ') ?> €</div>
        </div>
        <div class="span-3 glass-card carte-stat">
            <div class="stat-label">Transmises</div>
            <div class="stat-value" data-count="<?= (int) ($stats['total_transmises'] ?? 0) ?>">0</div>
        </div>
        <div class="span-3 glass-card carte-stat">
            <div class="stat-label">Validées</div>
            <div class="stat-value" data-count="<?= (int) ($stats['total_validees'] ?? 0) ?>">0</div>
        </div>
    </section>

    <section class="bloc-page p-4 mb-4">
        <div class="section-title">
            <div>
                <div class="section-label">Vue de contrôle</div>
                <h2 class="h3 fw-bold mb-0">Dernières fiches du système</h2>
            </div>
        </div>

        <?php if (!empty($dashboard['fiches_recentes'])): ?>
            <div class="table-responsive">
                <table class="table table-modern align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Numéro</th>
                            <th>Utilisateur</th>
                            <th>Mois</th>
                            <th>Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dashboard['fiches_recentes'] as $fiche): ?>
                            <tr>
                                <td class="fw-semibold"><?= e($fiche['numero_fiche']) ?></td>
                                <td><?= e($fiche['prenom'] . ' ' . $fiche['nom']) ?></td>
                                <td><?= e($fiche['mois']) ?></td>
                                <td><?= number_format((float) $fiche['montant_total'], 2, ',', ' ') ?> €</td>
                                <td><span class="badge-statut <?= $badgeClasse($fiche['statut']) ?>"><?= e($fiche['statut']) ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state">Aucune donnée disponible pour le moment.</div>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/../../squelette/footer.php'; ?>