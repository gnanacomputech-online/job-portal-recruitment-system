<?php use Core\Csrf; use Core\Router; ?>
<h1 class="font-display font-semibold text-2xl text-ink mb-6">My Applications</h1>

<div class="space-y-3">
    <?php foreach ($applications as $a): ?>
        <div class="card-surface p-5 flex items-center gap-5 flex-wrap">
            <div class="fit-ring" style="--pct: <?= (int) $a['match_score'] ?>; --ring-color: var(--<?= $a['match_score'] >= 80 ? 'mint' : ($a['match_score'] >= 60 ? 'indigo' : ($a['match_score'] >= 40 ? 'amber' : 'rose')) ?>);">
                <span><?= (int) $a['match_score'] ?></span>
            </div>
            <div class="flex-1 min-w-[180px]">
                <p class="font-display font-semibold text-ink"><?= e($a['job_title']) ?></p>
                <p class="text-sm text-slateink"><?= e($a['company_name']) ?> &middot; <?= e($a['location']) ?></p>
                <p class="text-xs text-slateink mt-1">Applied <?= time_ago($a['applied_at']) ?></p>
            </div>
            <span class="status-badge status-<?= e($a['status']) ?> capitalize"><?= e($a['status']) ?></span>
            <div class="flex gap-2">
                <a href="<?= Router::url('/candidate/applications/' . $a['id']) ?>" class="btn-secondary btn-sm">View breakdown</a>
                <?php if (in_array($a['status'], ['applied', 'shortlisted'], true)): ?>
                    <form method="post" action="<?= Router::url('/candidate/applications/' . $a['id'] . '/withdraw') ?>" onsubmit="return confirm('Withdraw this application?');">
                        <?= Csrf::field() ?>
                        <button type="submit" class="btn-danger btn-sm">Withdraw</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    <?php if (empty($applications)): ?>
        <div class="skeleton-empty p-10 text-center">You haven't applied to any jobs yet. <a href="<?= Router::url('/jobs') ?>" class="text-indigo hover:underline">Browse jobs &rarr;</a></div>
    <?php endif; ?>
</div>
