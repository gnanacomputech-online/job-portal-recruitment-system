<?php use Core\Router; use Core\View; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">My Dashboard</h1>

<div class="grid lg:grid-cols-3 gap-6 mb-8">
    <div class="card-surface p-6 lg:col-span-2">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-display font-semibold text-base text-ink">Profile completeness</h2>
            <span class="font-mono text-indigo font-semibold"><?= $completeness['percent'] ?>%</span>
        </div>
        <div class="meter-track mb-3"><div class="meter-fill" style="width: <?= $completeness['percent'] ?>%"></div></div>
        <?php if (!empty($completeness['missing'])): ?>
            <p class="text-sm text-slateink">Missing: <?= e(implode(', ', $completeness['missing'])) ?>. <a href="<?= Router::url('/candidate/profile') ?>" class="text-indigo hover:underline">Complete your profile &rarr;</a></p>
        <?php else: ?>
            <p class="text-sm text-mint">Your profile is complete!</p>
        <?php endif; ?>
    </div>
    <div class="card-surface p-6">
        <h2 class="font-display font-semibold text-base text-ink mb-3">My applications</h2>
        <div class="text-3xl font-mono text-ink"><?= $totalApplications ?></div>
        <div class="flex flex-wrap gap-1 mt-2">
            <?php foreach ($statusCounts as $status => $count): ?>
                <span class="status-badge status-<?= e($status) ?> capitalize"><?= e($status) ?>: <?= $count ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<h2 class="font-display font-semibold text-lg text-ink mb-4">Recommended for you</h2>
<div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">
    <?php foreach ($recommended as $job): ?>
        <?php View::renderBare('partials/job_card', ['job' => $job]); ?>
    <?php endforeach; ?>
    <?php if (empty($recommended)): ?><p class="text-slateink col-span-full">No open jobs right now.</p><?php endif; ?>
</div>

<h2 class="font-display font-semibold text-lg text-ink mb-4">Recent applications</h2>
<div class="card-surface overflow-x-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-slateink border-b hairline"><th class="py-3 px-4 font-medium">Job</th><th class="py-3 px-4 font-medium">Company</th><th class="py-3 px-4 font-medium">Match</th><th class="py-3 px-4 font-medium">Status</th><th class="py-3 px-4"></th></tr></thead>
        <tbody>
        <?php foreach ($applications as $a): ?>
            <tr class="border-b hairline">
                <td class="py-3 px-4 font-medium text-ink"><?= e($a['job_title']) ?></td>
                <td class="py-3 px-4 text-slateink"><?= e($a['company_name']) ?></td>
                <td class="py-3 px-4 font-mono"><?= (int) $a['match_score'] ?></td>
                <td class="py-3 px-4"><span class="status-badge status-<?= e($a['status']) ?> capitalize"><?= e($a['status']) ?></span></td>
                <td class="py-3 px-4 text-right"><a href="<?= Router::url('/candidate/applications/' . $a['id']) ?>" class="btn-secondary btn-sm">View</a></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($applications)): ?><tr><td colspan="5" class="py-8 text-center text-slateink">No applications yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</div>
