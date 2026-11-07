<?php use Core\Router; ?>
<div class="max-w-2xl mx-auto">
    <h1 class="font-display font-semibold text-2xl text-ink mb-6">Import complete</h1>

    <div class="card-surface p-6 mb-6">
        <h2 class="font-display font-semibold text-base text-ink mb-3"><?= count($created) ?> recruiter(s) created</h2>
        <?php if (!empty($created)): ?>
            <table class="w-full text-sm">
                <thead><tr class="text-left text-slateink border-b hairline"><th class="py-2 pr-3">Company</th><th class="py-2 pr-3">Email</th><th class="py-2 pr-3">One-time password</th></tr></thead>
                <tbody>
                <?php foreach ($created as $row): ?>
                    <tr class="border-b hairline">
                        <td class="py-2 pr-3"><?= e($row['company']) ?></td>
                        <td class="py-2 pr-3 font-mono"><?= e($row['email']) ?></td>
                        <td class="py-2 pr-3 font-mono"><?= e($row['password']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p class="field-hint mt-3">These passwords are shown once — an email attempt was also made to each address. All accounts are created <strong>approved</strong> (bulk_import source).</p>
        <?php else: ?>
            <p class="text-slateink text-sm">No new recruiters were created.</p>
        <?php endif; ?>
    </div>

    <?php if (!empty($skipped)): ?>
        <div class="card-surface p-6 mb-6">
            <h2 class="font-display font-semibold text-base text-ink mb-3">Skipped (email already registered)</h2>
            <ul class="text-sm text-slateink list-disc list-inside">
                <?php foreach ($skipped as $email): ?><li><?= e($email) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <a href="<?= Router::url('/admin/recruiters') ?>" class="btn-primary">Back to recruiters</a>
</div>
