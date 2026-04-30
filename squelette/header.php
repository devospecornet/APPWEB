<?php
require_once __DIR__ . '/../configuration/config.php';

$utilisateurConnecte = $_SESSION['utilisateur'] ?? null;
$roleUtilisateur = $utilisateurConnecte['role'] ?? null;
$pageTitle = $titrePage ?? $pageTitle ?? 'GSB Future';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> - GSB Future</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous">

    <link rel="stylesheet" href="<?= htmlspecialchars(asset_url('ressources/css/style.css')) ?>?v=reset-header">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="<?= htmlspecialchars(asset_url('tableau_bord.php')) ?>">
                GSB Future
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarGSB" aria-controls="navbarGSB" aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarGSB">
                <?php if ($utilisateurConnecte): ?>
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars(asset_url('tableau_bord.php')) ?>">
                                Tableau de bord
                            </a>
                        </li>

                        <?php if ($roleUtilisateur === 'visiteur'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars(asset_url('visiteur.php')) ?>">
                                    Mes fiches
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars(asset_url('notifications.php')) ?>">
                                Notifications
                            </a>
                        </li>

                        <?php if ($roleUtilisateur === 'comptable'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars(asset_url('comptable.php')) ?>">
                                    Validation
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars(asset_url('journal.php')) ?>">
                                    Journal
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php if ($roleUtilisateur === 'administrateur'): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars(asset_url('admin.php')) ?>">
                                    Administration
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars(asset_url('admin_fiches.php')) ?>">
                                    Toutes les fiches
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars(asset_url('journal.php')) ?>">
                                    Journal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="<?= htmlspecialchars(asset_url('admin_jetons.php')) ?>">
                                    Accès API
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item ms-lg-3">
                            <span class="navbar-text">
                                Utilisateur :
                                <strong><?= htmlspecialchars(trim(($utilisateurConnecte['prenom'] ?? '') . ' ' . ($utilisateurConnecte['nom'] ?? ''))) ?></strong>
                                (<?= htmlspecialchars((string) $roleUtilisateur) ?>)
                            </span>
                        </li>

                        <li class="nav-item ms-lg-3">
                            <a class="btn btn-outline-secondary btn-sm" href="<?= htmlspecialchars(asset_url('deconnexion.php')) ?>">
                                Déconnexion
                            </a>
                        </li>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="container py-4">