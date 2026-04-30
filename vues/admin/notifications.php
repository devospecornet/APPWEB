<?php $titrePage='Notifications'; require __DIR__ . '/../../squelette/header.php'; ?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h1 class="h2 mb-1">Notifications</h1>
        <p class="text-secondary mb-0">Retrouve ici les informations utiles sur les fiches, les comptes et les accès API.</p>
    </div>
    <?php if (!empty($notifications)): ?>
        <form method="post">
            <?= csrf_input() ?>
            <button class="btn btn-outline-secondary btn-arrondi" name="tout_marquer_lu">Tout marquer comme lu</button>
        </form>
    <?php endif; ?>
</div>
<div class="d-flex flex-column gap-3">
<?php foreach ($notifications as $notification): ?>
<div class="bloc-page p-3">
    <div class="d-flex justify-content-between gap-2 flex-wrap mb-2">
        <strong><?= e($notification['titre']) ?></strong>
        <span class="small text-secondary"><?= e(format_date_fr($notification['date_creation'], true)) ?></span>
    </div>
    <div class="mb-3"><?= e($notification['message']) ?></div>
    <?php if ((int) ($notification['est_lue'] ?? 0) === 0): ?>
    <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="id_notification" value="<?= (int) $notification['id'] ?>">
        <button class="btn btn-outline-primary btn-sm btn-arrondi" name="marquer_lue">Marquer comme lue</button>
    </form>
    <?php else: ?><span class="badge text-bg-secondary">Lue</span><?php endif; ?>
</div>
<?php endforeach; ?>
<?php if (empty($notifications)): ?><div class="bloc-page p-4 text-secondary">Aucune notification à afficher.</div><?php endif; ?>
</div>
<?php require __DIR__ . '/../../squelette/footer.php'; ?>
