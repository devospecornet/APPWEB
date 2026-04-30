<?php require_once __DIR__ . '/../../configuration/config.php'; ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - GSB Future</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= APP_BASE_URL ?>/ressources/css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-lg-10">
                <div class="card border-0 overflow-hidden" style="border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.08);">
                    <div class="row g-0">
                        <div class="col-lg-5 p-5 text-white" style="background: linear-gradient(135deg,#1769ff,#3d8bfd);">
                            <h1 class="display-6 fw-bold">GSB Future</h1>
                            <p class="mb-0">Application de gestion des fiches de frais.</p>
                        </div>
                        <div class="col-lg-7 p-4 p-md-5 bg-white">
                            <h2 class="fw-bold mb-4">Connexion</h2>

                            <?php if (!empty($message)): ?>
                                <div class="alert alert-danger"><?= e($message) ?></div>
                            <?php endif; ?>

                            <form method="POST">
                                <?= csrf_input() ?>
                                <div class="mb-3">
                                    <label class="form-label">Adresse e-mail</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Mot de passe</label>
                                    <input type="password" name="mot_de_passe" class="form-control" required>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                            </form>

                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>